<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Models\LeaveAllowance;
use App\Models\LeaveRequest;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isLocationAdmin()) {
            return $this->locationAdminDashboard();
        }

        if ($user->isDepartmentAdmin()) {
            return $this->departmentAdminDashboard();
        }

        return $this->employeeDashboard();
    }

    protected function superAdminDashboard(): View
    {
        // Super admins use the same mobile dashboard as regular admins
        // They see all data for their tenant without filtering
        return $this->adminDashboard();
    }

    protected function adminDashboard(?int $locationId = null, ?int $departmentId = null): View
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // Calculate hours scheduled this week
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        // Build base query with optional location/department filtering
        $shiftsQuery = Shift::where('tenant_id', $tenantId);
        if ($locationId) {
            $shiftsQuery->where('location_id', $locationId);
        }
        if ($departmentId) {
            $shiftsQuery->where('department_id', $departmentId);
        }

        $hoursThisWeek = (clone $shiftsQuery)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->whereNotNull('user_id')
            ->get()
            ->sum('working_hours');

        // Count employees on leave today
        $leaveQuery = LeaveRequest::where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Approved)
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today());
        $onLeaveToday = $leaveQuery->count();

        // Employee count based on scope
        $employeeQuery = User::where('tenant_id', $tenantId);
        if ($locationId || $departmentId) {
            $employeeQuery->whereHas('businessRoles', function ($q) use ($locationId, $departmentId) {
                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                } elseif ($locationId) {
                    $q->whereHas('department', fn ($d) => $d->where('location_id', $locationId));
                }
            });
        }

        $stats = [
            'total_employees' => (clone $employeeQuery)->count(),
            'active_employees' => (clone $employeeQuery)->active()->count(),
            'on_duty_today' => (clone $shiftsQuery)
                ->whereDate('date', today())
                ->whereNotNull('user_id')
                ->where('status', ShiftStatus::Published)
                ->count(),
            'on_leave_today' => $onLeaveToday,
            'hours_this_week' => round($hoursThisWeek),
            'total_shifts_this_week' => (clone $shiftsQuery)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->count(),
            'pending_leave_requests' => LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', LeaveRequestStatus::Requested)
                ->count(),
            'unassigned_shifts' => (clone $shiftsQuery)
                ->whereNull('user_id')
                ->whereDate('date', '>=', today())
                ->whereDate('date', '<=', $weekEnd)
                ->count(),
            'pending_shift_swaps' => ShiftSwapRequest::whereHas('requestingShift', function ($query) use ($tenantId, $locationId, $departmentId) {
                $query->where('tenant_id', $tenantId);
                if ($locationId) {
                    $query->where('location_id', $locationId);
                }
                if ($departmentId) {
                    $query->where('department_id', $departmentId);
                }
            })->where('status', SwapRequestStatus::Pending)->count(),
        ];

        $todayShifts = Shift::with(['user', 'department', 'businessRole', 'location'])
            ->where('tenant_id', $tenantId)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->whereDate('date', today())
            ->orderBy('start_time')
            ->get();

        $pendingLeave = LeaveRequest::with(['user', 'leaveType'])
            ->where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Requested)
            ->latest()
            ->take(5)
            ->get();

        $pendingSwaps = ShiftSwapRequest::with([
            'requestingShift.user',
            'requestingShift.department',
            'requestingShift.businessRole',
            'targetShift.user',
        ])
            ->whereHas('requestingShift', function ($query) use ($tenantId, $locationId, $departmentId) {
                $query->where('tenant_id', $tenantId);
                if ($locationId) {
                    $query->where('location_id', $locationId);
                }
                if ($departmentId) {
                    $query->where('department_id', $departmentId);
                }
            })
            ->where('status', SwapRequestStatus::Pending)
            ->latest()
            ->take(5)
            ->get();

        // Get shifts grouped by department for timeline view
        $shiftsByDepartment = $todayShifts->groupBy('department_id');

        return view('dashboard.admin', compact('stats', 'todayShifts', 'pendingLeave', 'pendingSwaps', 'shiftsByDepartment'));
    }

    protected function locationAdminDashboard(): View
    {
        $user = auth()->user();

        // Get the location(s) this user manages
        $locationIds = $user->roleAssignments()
            ->where('system_role', \App\Enums\SystemRole::LocationAdmin->value)
            ->whereNotNull('location_id')
            ->pluck('location_id');

        $locationId = $locationIds->first();

        return $this->adminDashboard(locationId: $locationId);
    }

    protected function departmentAdminDashboard(): View
    {
        $user = auth()->user();

        // Get the department(s) this user manages
        $departmentIds = $user->roleAssignments()
            ->where('system_role', \App\Enums\SystemRole::DepartmentAdmin->value)
            ->whereNotNull('department_id')
            ->pluck('department_id');

        $departmentId = $departmentIds->first();

        return $this->adminDashboard(departmentId: $departmentId);
    }

    protected function employeeDashboard(): View
    {
        $user = auth()->user();
        $user->load(['businessRoles.department']);

        // Today's shift with time entry
        $todayShift = Shift::with(['location', 'department', 'businessRole', 'timeEntry'])
            ->visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        // Active time entry (if clocked in)
        $activeTimeEntry = TimeEntry::where('user_id', $user->id)
            ->active()
            ->first();

        // Week summary
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $weekShifts = Shift::visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $scheduledHours = $weekShifts->sum('working_hours');

        $workedMinutes = TimeEntry::where('user_id', $user->id)
            ->whereBetween('clock_in_at', [$weekStart, $weekEnd])
            ->get()
            ->sum('total_worked_minutes');
        $workedHours = round($workedMinutes / 60, 1);

        $shiftsRemaining = $weekShifts->filter(fn ($shift) => $shift->date >= today())->count();

        // Upcoming shifts (excluding today)
        $upcomingShifts = Shift::with(['location', 'department', 'businessRole'])
            ->visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereDate('date', '>', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Leave balances for current year
        $leaveBalances = LeaveAllowance::with('leaveType')
            ->where('user_id', $user->id)
            ->forYear(now()->year)
            ->get();

        // Pending requests
        $pendingLeave = LeaveRequest::with('leaveType')
            ->where('user_id', $user->id)
            ->where('status', LeaveRequestStatus::Requested)
            ->latest()
            ->take(3)
            ->get();

        $pendingSwaps = ShiftSwapRequest::with(['requestingShift.department', 'requestingShift.businessRole'])
            ->where('requesting_user_id', $user->id)
            ->where('status', SwapRequestStatus::Pending)
            ->latest()
            ->take(3)
            ->get();

        // Incoming swap requests
        $incomingSwaps = ShiftSwapRequest::with(['requestingShift.user', 'requestingShift.department'])
            ->where('target_user_id', $user->id)
            ->where('status', SwapRequestStatus::Pending)
            ->count();

        return view('dashboard.employee', [
            'user' => $user,
            'todayShift' => $todayShift,
            'activeTimeEntry' => $activeTimeEntry,
            'weekSummary' => [
                'scheduled_hours' => round($scheduledHours, 1),
                'worked_hours' => $workedHours,
                'shifts_remaining' => $shiftsRemaining,
            ],
            'upcomingShifts' => $upcomingShifts,
            'leaveBalances' => $leaveBalances,
            'pendingLeave' => $pendingLeave,
            'pendingSwaps' => $pendingSwaps,
            'incomingSwaps' => $incomingSwaps,
        ]);
    }
}
