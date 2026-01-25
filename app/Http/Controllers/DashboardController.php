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
        $stats = [
            'total_tenants' => \App\Models\Tenant::count(),
            'active_tenants' => \App\Models\Tenant::active()->count(),
            'total_users' => User::count(),
            'new_tenants_this_month' => \App\Models\Tenant::whereMonth('created_at', now()->month)->count(),
        ];

        $recentTenants = \App\Models\Tenant::latest()->take(5)->get();

        return view('dashboard.super-admin', compact('stats', 'recentTenants'));
    }

    protected function adminDashboard(): View
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // Calculate hours scheduled this week
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $hoursThisWeek = Shift::where('tenant_id', $tenantId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->whereNotNull('user_id')
            ->get()
            ->sum('working_hours');

        // Count employees on leave today
        $onLeaveToday = LeaveRequest::where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Approved)
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->count();

        $stats = [
            'total_employees' => User::where('tenant_id', $tenantId)->count(),
            'active_employees' => User::where('tenant_id', $tenantId)->active()->count(),
            'on_duty_today' => Shift::where('tenant_id', $tenantId)
                ->whereDate('date', today())
                ->whereNotNull('user_id')
                ->where('status', ShiftStatus::Published)
                ->count(),
            'on_leave_today' => $onLeaveToday,
            'hours_this_week' => round($hoursThisWeek),
            'pending_leave_requests' => LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', LeaveRequestStatus::Requested)
                ->count(),
            'unassigned_shifts' => Shift::where('tenant_id', $tenantId)
                ->whereNull('user_id')
                ->whereDate('date', '>=', today())
                ->whereDate('date', '<=', $weekEnd)
                ->count(),
            'pending_shift_swaps' => ShiftSwapRequest::whereHas('requestingShift', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', SwapRequestStatus::Pending)->count(),
        ];

        $todayShifts = Shift::with(['user', 'department', 'businessRole', 'location'])
            ->where('tenant_id', $tenantId)
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
            ->whereHas('requestingShift', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
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
        return view('dashboard.location-admin');
    }

    protected function departmentAdminDashboard(): View
    {
        return view('dashboard.department-admin');
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
