<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Enums\TimeEntryStatus;
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
        $stats = [
            'total_tenants' => \App\Models\Tenant::count(),
            'active_tenants' => \App\Models\Tenant::where('is_active', true)->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_tenants_this_month' => \App\Models\Tenant::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentTenants = \App\Models\Tenant::withCount('users')
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = User::with('tenant')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.super-admin', compact('stats', 'recentTenants', 'recentUsers'));
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
            'missed_shifts_today' => TimeEntry::where('tenant_id', $tenantId)
                ->where('status', TimeEntryStatus::Missed)
                ->whereHas('shift', function ($query) use ($locationId, $departmentId) {
                    $query->whereDate('date', today());
                    if ($locationId) {
                        $query->where('location_id', $locationId);
                    }
                    if ($departmentId) {
                        $query->where('department_id', $departmentId);
                    }
                })
                ->count(),
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
        $tenantId = $user->tenant_id;

        // Get upcoming shifts for this employee (next 7 days)
        $upcomingShifts = Shift::with(['department', 'businessRole', 'location'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', today())
            ->whereDate('date', '<=', today()->addDays(7))
            ->where('status', ShiftStatus::Published)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Get next shift for countdown
        $nextShift = Shift::with(['department', 'businessRole', 'location'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Published)
            ->where(function ($query) {
                $query->whereDate('date', '>', today())
                    ->orWhere(function ($q) {
                        $q->whereDate('date', today())
                            ->whereTime('start_time', '>', now()->format('H:i:s'));
                    });
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();

        // Get employee's pending leave requests
        $myLeaveRequests = LeaveRequest::with('leaveType')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->whereIn('status', [LeaveRequestStatus::Draft, LeaveRequestStatus::Requested])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Get employee's swap requests (both sent and received)
        $mySwapRequests = ShiftSwapRequest::with([
            'requestingUser',
            'targetUser',
            'requestingShift.businessRole',
            'targetShift.businessRole',
        ])
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($user) {
                $query->where('requesting_user_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->whereIn('status', [SwapRequestStatus::Pending, SwapRequestStatus::Accepted])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Calculate hours this week for employee
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $hoursThisWeek = Shift::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get()
            ->sum('working_hours');

        // Count missed shifts for this employee (this week)
        $missedShiftsCount = TimeEntry::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('status', TimeEntryStatus::Missed)
            ->whereHas('shift', function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date', [$weekStart, $weekEnd]);
            })
            ->count();

        $stats = [
            'upcoming_shifts' => $upcomingShifts->count(),
            'hours_this_week' => round($hoursThisWeek),
            'pending_leave' => $myLeaveRequests->count(),
            'pending_swaps' => $mySwapRequests->count(),
            'missed_shifts' => $missedShiftsCount,
        ];

        return view('dashboard.employee', compact(
            'upcomingShifts',
            'nextShift',
            'myLeaveRequests',
            'mySwapRequests',
            'stats'
        ));
    }
}
