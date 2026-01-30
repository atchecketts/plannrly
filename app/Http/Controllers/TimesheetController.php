<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\TimesheetExportService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TimesheetController extends Controller
{
    public function __construct(
        protected TimesheetExportService $exportService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $settings = TenantSettings::where('tenant_id', $user->tenant_id)->first();

        // Date range - default to current week
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Build query
        $query = TimeEntry::query()
            ->with(['user', 'shift.businessRole', 'shift.department', 'shift.location', 'approvedBy'])
            ->whereBetween('clock_in_at', [$weekStart, $weekEnd])
            ->orderBy('clock_in_at');

        // Employee can only see their own timesheets
        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        }

        // Filters for admin
        if (! $user->isEmployee()) {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->filled('department_id')) {
                $query->whereHas('shift', function ($q) use ($request) {
                    $q->where('department_id', $request->input('department_id'));
                });
            }
        }

        $timeEntries = $query->get();

        // Group by user for admin view
        $groupedByUser = $timeEntries->groupBy('user_id');

        // Calculate totals per user
        $userTotals = $this->calculateUserTotals($groupedByUser);

        // Get employees for filter dropdown
        $employees = $user->isEmployee() ? collect() : User::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Get departments for filter
        $departments = $user->isEmployee() ? collect() : Department::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();

        // Generate week days for header
        $weekDays = $this->getWeekDays($weekStart);

        // Previous/Next week links
        $prevWeek = $weekStart->copy()->subWeek();
        $nextWeek = $weekStart->copy()->addWeek();

        return view('timesheets.index', compact(
            'timeEntries',
            'groupedByUser',
            'userTotals',
            'employees',
            'departments',
            'weekStart',
            'weekEnd',
            'weekDays',
            'prevWeek',
            'nextWeek',
            'settings'
        ));
    }

    public function employee(Request $request): View
    {
        $user = auth()->user();
        $settings = TenantSettings::where('tenant_id', $user->tenant_id)->first();

        // Date range - default to current week
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get time entries for the user
        $timeEntries = TimeEntry::query()
            ->where('user_id', $user->id)
            ->with(['shift.businessRole', 'shift.department', 'shift.location', 'approvedBy'])
            ->whereBetween('clock_in_at', [$weekStart, $weekEnd])
            ->orderBy('clock_in_at')
            ->get();

        // Group entries by date
        $entriesByDate = $timeEntries->groupBy(fn ($entry) => $entry->clock_in_at->format('Y-m-d'));

        // Calculate weekly totals
        $weeklyTotals = $this->calculateWeeklyTotals($timeEntries);

        // Generate week days for display
        $weekDays = $this->getWeekDays($weekStart);

        // Previous/Next week links
        $prevWeek = $weekStart->copy()->subWeek();
        $nextWeek = $weekStart->copy()->addWeek();

        return view('timesheets.employee', compact(
            'timeEntries',
            'entriesByDate',
            'weeklyTotals',
            'weekStart',
            'weekEnd',
            'weekDays',
            'prevWeek',
            'nextWeek',
            'settings'
        ));
    }

    public function approveMultiple(Request $request): RedirectResponse
    {
        $this->authorize('approveMultiple', TimeEntry::class);

        $entryIds = $request->input('entry_ids', []);

        if (empty($entryIds)) {
            return back()->with('error', 'No entries selected for approval.');
        }

        $user = auth()->user();

        $entries = TimeEntry::whereIn('id', $entryIds)
            ->where('tenant_id', $user->tenant_id)
            ->whereNull('approved_at')
            ->get();

        $approved = 0;
        foreach ($entries as $entry) {
            $entry->approve($user);
            $approved++;
        }

        return back()->with('success', "{$approved} time entries approved successfully.");
    }

    /**
     * Calculate totals per user from grouped entries.
     */
    protected function calculateUserTotals(Collection $groupedByUser): array
    {
        $totals = [];

        foreach ($groupedByUser as $userId => $entries) {
            $scheduledMinutes = 0;
            $actualMinutes = 0;
            $breakMinutes = 0;
            $pendingApproval = 0;

            foreach ($entries as $entry) {
                $scheduledMinutes += $entry->scheduled_duration_minutes ?? 0;
                $actualMinutes += $entry->total_worked_minutes ?? 0;
                $breakMinutes += $entry->actual_break_minutes ?? 0;

                if ($entry->requiresApproval() && ! $entry->isApproved()) {
                    $pendingApproval++;
                }
            }

            $totals[$userId] = [
                'scheduled_hours' => round($scheduledMinutes / 60, 2),
                'actual_hours' => round($actualMinutes / 60, 2),
                'break_hours' => round($breakMinutes / 60, 2),
                'variance_minutes' => $actualMinutes - $scheduledMinutes,
                'entry_count' => $entries->count(),
                'pending_approval' => $pendingApproval,
            ];
        }

        return $totals;
    }

    /**
     * Calculate weekly totals for a single user.
     */
    protected function calculateWeeklyTotals(Collection $entries): array
    {
        $scheduledMinutes = 0;
        $actualMinutes = 0;
        $breakMinutes = 0;
        $pendingApproval = 0;
        $approved = 0;

        foreach ($entries as $entry) {
            $scheduledMinutes += $entry->scheduled_duration_minutes ?? 0;
            $actualMinutes += $entry->total_worked_minutes ?? 0;
            $breakMinutes += $entry->actual_break_minutes ?? 0;

            if ($entry->isApproved()) {
                $approved++;
            } elseif ($entry->requiresApproval()) {
                $pendingApproval++;
            }
        }

        return [
            'scheduled_hours' => round($scheduledMinutes / 60, 2),
            'actual_hours' => round($actualMinutes / 60, 2),
            'break_hours' => round($breakMinutes / 60, 2),
            'variance_minutes' => $actualMinutes - $scheduledMinutes,
            'entry_count' => $entries->count(),
            'pending_approval' => $pendingApproval,
            'approved' => $approved,
        ];
    }

    /**
     * Get week days as array for display.
     *
     * @return array<Carbon>
     */
    protected function getWeekDays(Carbon $weekStart): array
    {
        $days = [];
        $period = CarbonPeriod::create($weekStart, $weekStart->copy()->endOfWeek());

        foreach ($period as $date) {
            $days[] = $date;
        }

        return $days;
    }

    /**
     * Export timesheets to CSV format.
     */
    public function export(Request $request): Response
    {
        $user = auth()->user();

        // Employees can only export their own data
        $userId = $user->isEmployee() ? $user->id : ($request->filled('user_id') ? (int) $request->input('user_id') : null);
        $departmentId = $user->isEmployee() ? null : ($request->filled('department_id') ? (int) $request->input('department_id') : null);
        $locationId = $user->isEmployee() ? null : ($request->filled('location_id') ? (int) $request->input('location_id') : null);

        // Date range
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $entries = $this->exportService->getExportData(
            $weekStart,
            $weekEnd,
            $userId,
            $departmentId,
            $locationId
        );

        $csv = $this->exportService->exportToCsv($entries);
        $filename = "timesheet-{$weekStart->format('Y-m-d')}-to-{$weekEnd->format('Y-m-d')}.csv";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export timesheets to payroll-friendly CSV format.
     */
    public function exportPayroll(Request $request): Response
    {
        $user = auth()->user();

        // Only admins can export payroll data
        if ($user->isEmployee()) {
            abort(403);
        }

        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->input('location_id') : null;

        // Date range
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $entries = $this->exportService->getExportData(
            $weekStart,
            $weekEnd,
            $userId,
            $departmentId,
            $locationId
        );

        $csv = $this->exportService->exportToPayrollCsv($entries);
        $filename = "payroll-{$weekStart->format('Y-m-d')}-to-{$weekEnd->format('Y-m-d')}.csv";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
