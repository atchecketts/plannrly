<?php

namespace App\Http\Controllers;

use App\Enums\ShiftStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\ShiftPublishedNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        // Determine the week to display (default to current week)
        $startDate = $request->query('start')
            ? Carbon::parse($request->query('start'))->startOfWeek()
            : now()->startOfWeek();

        $endDate = $startDate->copy()->endOfWeek();

        // Build the week dates array
        $weekDates = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $weekDates[] = $currentDate->copy();
            $currentDate->addDay();
        }

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with(['location', 'businessRoles'])->active()->orderBy('name')->get();
        $businessRoles = BusinessRole::with('department')->active()->orderBy('name')->get();

        $user = auth()->user();

        // Get shifts for this date range (tenant-scoped automatically)
        $shifts = Shift::with(['user', 'department', 'businessRole'])
            ->visibleToUser($user)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Count draft shifts for the Publish button (only for admins)
        $draftShiftsCount = 0;
        if (! $user->isEmployee()) {
            $draftShiftsCount = Shift::draft()
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
        }

        // Get all active users with their roles and leave requests (only users with business roles)
        $users = User::with(['businessRoles.department', 'leaveRequests' => function ($query) use ($startDate, $endDate) {
            $query->where('status', 'approved')
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate);
        }])
            ->whereHas('businessRoles')
            ->active()
            ->get();

        // Group users by their primary department
        $usersByDepartment = $users->groupBy(function ($user) {
            $primaryRole = $user->businessRoles->where('pivot.is_primary', true)->first();

            return $primaryRole?->department_id ?? 0;
        });

        // Group users by their primary role
        $usersByRole = $users->groupBy(function ($user) {
            $primaryRole = $user->businessRoles->where('pivot.is_primary', true)->first();

            return $primaryRole?->id ?? 0;
        });

        // Create a shifts lookup for quick access: [user_id][date_string] => [shifts]
        $shiftsLookup = [];
        foreach ($shifts as $shift) {
            if ($shift->user_id) {
                $dateStr = $shift->date->format('Y-m-d');
                if (! isset($shiftsLookup[$shift->user_id][$dateStr])) {
                    $shiftsLookup[$shift->user_id][$dateStr] = [];
                }
                $shiftsLookup[$shift->user_id][$dateStr][] = $shift;
            }
        }

        // Create unassigned shifts lookup: [date_string] => [shifts]
        $unassignedShiftsLookup = [];
        foreach ($shifts as $shift) {
            if (! $shift->user_id) {
                $dateStr = $shift->date->format('Y-m-d');
                if (! isset($unassignedShiftsLookup[$dateStr])) {
                    $unassignedShiftsLookup[$dateStr] = [];
                }
                $unassignedShiftsLookup[$dateStr][] = $shift;
            }
        }

        // Create leave lookup: [user_id][date_string] => leaveRequest
        $leaveLookup = [];
        foreach ($users as $user) {
            foreach ($user->leaveRequests as $leave) {
                $leaveDate = $leave->start_date->copy();
                while ($leaveDate <= $leave->end_date) {
                    $leaveLookup[$user->id][$leaveDate->format('Y-m-d')] = $leave;
                    $leaveDate->addDay();
                }
            }
        }

        // Calculate weekly hours per user
        $userWeeklyHours = [];
        foreach ($shifts as $shift) {
            if ($shift->user_id) {
                $userWeeklyHours[$shift->user_id] = ($userWeeklyHours[$shift->user_id] ?? 0) + $shift->duration_hours;
            }
        }

        // Calculate stats
        $totalShifts = $shifts->count();
        $totalHours = $shifts->sum(fn ($shift) => $shift->duration_hours);
        $assignedShifts = $shifts->whereNotNull('user_id')->count();
        $unassignedShifts = $shifts->whereNull('user_id')->count();

        // Get grouping preference (department or role)
        $groupBy = $request->query('group_by', 'department');

        return view('schedule.index', compact(
            'startDate',
            'endDate',
            'weekDates',
            'locations',
            'departments',
            'businessRoles',
            'users',
            'usersByDepartment',
            'usersByRole',
            'shiftsLookup',
            'unassignedShiftsLookup',
            'leaveLookup',
            'shifts',
            'userWeeklyHours',
            'totalShifts',
            'totalHours',
            'assignedShifts',
            'unassignedShifts',
            'draftShiftsCount',
            'groupBy'
        ));
    }

    public function day(Request $request): View
    {
        $selectedDate = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : now();

        $user = auth()->user();
        $tenant = $user->tenant;
        $tenantSettings = $tenant->tenantSettings;

        // Get day start/end from tenant settings (default 6:00 - 22:00)
        $dayStartHour = $tenantSettings?->day_starts_at?->hour ?? 6;
        $dayEndHour = $tenantSettings?->day_ends_at?->hour ?? 22;

        // Build hours array
        $hours = [];
        for ($h = $dayStartHour; $h <= $dayEndHour; $h++) {
            $hours[] = $h;
        }

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with(['location', 'businessRoles'])->active()->orderBy('name')->get();
        $businessRoles = BusinessRole::with('department')->active()->orderBy('name')->get();

        // Get shifts for this date (tenant-scoped automatically)
        $shifts = Shift::with(['user', 'department', 'businessRole'])
            ->visibleToUser($user)
            ->whereDate('date', $selectedDate)
            ->get();

        // Count draft shifts for the Publish button (only for admins)
        $draftShiftsCount = 0;
        if (! $user->isEmployee()) {
            $draftShiftsCount = Shift::draft()
                ->whereDate('date', $selectedDate)
                ->count();
        }

        // Get all active users with their roles and leave requests
        $users = User::with(['businessRoles.department', 'leaveRequests' => function ($query) use ($selectedDate) {
            $query->where('status', 'approved')
                ->where('start_date', '<=', $selectedDate)
                ->where('end_date', '>=', $selectedDate);
        }])
            ->whereHas('businessRoles')
            ->active()
            ->get();

        // Group users by their primary department
        $usersByDepartment = $users->groupBy(function ($user) {
            $primaryRole = $user->businessRoles->where('pivot.is_primary', true)->first();

            return $primaryRole?->department_id ?? 0;
        });

        // Group users by their primary role
        $usersByRole = $users->groupBy(function ($user) {
            $primaryRole = $user->businessRoles->where('pivot.is_primary', true)->first();

            return $primaryRole?->id ?? 0;
        });

        // Get grouping preference (department or role)
        $groupBy = $request->query('group_by', 'department');

        // Create a shifts lookup: [user_id] => [shifts]
        $shiftsLookup = [];
        foreach ($shifts as $shift) {
            if ($shift->user_id) {
                if (! isset($shiftsLookup[$shift->user_id])) {
                    $shiftsLookup[$shift->user_id] = [];
                }
                $shiftsLookup[$shift->user_id][] = $shift;
            }
        }

        // Create unassigned shifts array
        $unassignedShifts = $shifts->whereNull('user_id')->values();

        // Create leave lookup: [user_id] => leaveRequest
        $leaveLookup = [];
        foreach ($users as $u) {
            foreach ($u->leaveRequests as $leave) {
                $leaveLookup[$u->id] = $leave;
            }
        }

        // Calculate daily hours per user
        $userDailyHours = [];
        foreach ($shifts as $shift) {
            if ($shift->user_id) {
                $userDailyHours[$shift->user_id] = ($userDailyHours[$shift->user_id] ?? 0) + $shift->duration_hours;
            }
        }

        // Calculate stats
        $totalShifts = $shifts->count();
        $totalHours = $shifts->sum(fn ($shift) => $shift->duration_hours);
        $assignedShiftsCount = $shifts->whereNotNull('user_id')->count();
        $unassignedShiftsCount = $shifts->whereNull('user_id')->count();

        return view('schedule.day', compact(
            'selectedDate',
            'hours',
            'dayStartHour',
            'dayEndHour',
            'locations',
            'departments',
            'businessRoles',
            'users',
            'usersByDepartment',
            'usersByRole',
            'shiftsLookup',
            'unassignedShifts',
            'leaveLookup',
            'shifts',
            'userDailyHours',
            'totalShifts',
            'totalHours',
            'assignedShiftsCount',
            'unassignedShiftsCount',
            'draftShiftsCount',
            'groupBy'
        ));
    }

    public function draftCount(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->isEmployee()) {
            return response()->json(['count' => 0]);
        }

        $startDate = $request->query('start_date')
            ? Carbon::parse($request->query('start_date'))
            : now()->startOfWeek();

        $endDate = $request->query('end_date')
            ? Carbon::parse($request->query('end_date'))
            : $startDate->copy()->endOfWeek();

        $query = Shift::draft()->whereBetween('date', [$startDate, $endDate]);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        if ($request->filled('business_role_id')) {
            $query->where('business_role_id', $request->query('business_role_id'));
        }

        return response()->json(['count' => $query->count()]);
    }

    public function publishAll(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->isEmployee()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->startOfWeek();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : $startDate->copy()->endOfWeek();

        $query = Shift::draft()
            ->with(['user', 'location'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('business_role_id')) {
            $query->where('business_role_id', $request->input('business_role_id'));
        }

        $shifts = $query->get();
        $publishedCount = 0;
        $tenant = $user->tenant;
        $notifyOnPublish = $tenant->tenantSettings?->notify_on_publish ?? true;

        foreach ($shifts as $shift) {
            $shift->update(['status' => ShiftStatus::Published]);
            $publishedCount++;

            // Send notification if shift is assigned and notifications are enabled
            if ($notifyOnPublish && $shift->user) {
                $shift->user->notify(new ShiftPublishedNotification($shift));
            }
        }

        return response()->json([
            'success' => true,
            'published_count' => $publishedCount,
            'message' => "{$publishedCount} shift(s) published successfully.",
        ]);
    }
}
