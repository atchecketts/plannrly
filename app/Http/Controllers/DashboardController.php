<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Enums\ShiftStatus;
use App\Models\LeaveRequest;
use App\Models\Shift;
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

        $stats = [
            'total_employees' => User::where('tenant_id', $tenantId)->count(),
            'active_employees' => User::where('tenant_id', $tenantId)->active()->count(),
            'on_duty_today' => Shift::where('tenant_id', $tenantId)
                ->whereDate('date', today())
                ->whereNotNull('user_id')
                ->where('status', ShiftStatus::Scheduled)
                ->count(),
            'pending_leave_requests' => LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', LeaveRequestStatus::Requested)
                ->count(),
            'unassigned_shifts' => Shift::where('tenant_id', $tenantId)
                ->whereNull('user_id')
                ->whereDate('date', '>=', today())
                ->count(),
        ];

        $todayShifts = Shift::with(['user', 'department', 'businessRole'])
            ->where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->orderBy('start_time')
            ->take(10)
            ->get();

        $pendingLeave = LeaveRequest::with(['user', 'leaveType'])
            ->where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Requested)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'todayShifts', 'pendingLeave'));
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

        $upcomingShifts = Shift::with(['location', 'department', 'businessRole'])
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $thisWeekHours = Shift::where('user_id', $user->id)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->get()
            ->sum('working_hours');

        $pendingLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', LeaveRequestStatus::Requested)
            ->count();

        return view('dashboard.employee', compact('upcomingShifts', 'thisWeekHours', 'pendingLeave'));
    }
}
