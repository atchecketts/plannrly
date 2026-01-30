<?php

namespace App\Services;

use App\Enums\TimeEntryStatus;
use App\Models\Department;
use App\Models\Shift;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceReportService
{
    /**
     * Get attendance rate: (shifts worked / shifts scheduled) x 100.
     *
     * @return array{rate: float, worked: int, scheduled: int}
     */
    public function getAttendanceRate(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array {
        $scheduledQuery = Shift::query()
            ->forDateRange($startDate, $endDate)
            ->assigned();

        $workedQuery = TimeEntry::query()
            ->whereNotNull('clock_in_at')
            ->where('status', '!=', TimeEntryStatus::Missed)
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()]);

        if ($departmentId !== null) {
            $scheduledQuery->where('department_id', $departmentId);
            $workedQuery->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $scheduledQuery->where('location_id', $locationId);
            $workedQuery->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($userId !== null) {
            $scheduledQuery->where('user_id', $userId);
            $workedQuery->where('user_id', $userId);
        }

        $scheduled = $scheduledQuery->count();
        $worked = $workedQuery->count();

        return [
            'rate' => $scheduled > 0 ? round(($worked / $scheduled) * 100, 1) : 0,
            'worked' => $worked,
            'scheduled' => $scheduled,
        ];
    }

    /**
     * Get punctuality rate: (on-time arrivals / total arrivals) x 100.
     *
     * @return array{rate: float, on_time: int, late: int, early: int, total: int}
     */
    public function getPunctualityRate(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array {
        $query = TimeEntry::query()
            ->with('shift')
            ->whereNotNull('clock_in_at')
            ->whereNotNull('shift_id')
            ->where('status', '!=', TimeEntryStatus::Missed)
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()]);

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $entries = $query->get();

        $onTime = 0;
        $late = 0;
        $early = 0;

        foreach ($entries as $entry) {
            if ($entry->is_late) {
                $late++;
            } elseif ($entry->clock_in_variance_minutes < -5) {
                $early++;
            } else {
                $onTime++;
            }
        }

        $total = $entries->count();

        return [
            'rate' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
            'on_time' => $onTime,
            'late' => $late,
            'early' => $early,
            'total' => $total,
        ];
    }

    /**
     * Get overtime hours: sum of positive variances.
     *
     * @return array{hours: float, minutes: int, entries: int}
     */
    public function getOvertimeHours(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array {
        $query = TimeEntry::query()
            ->with('shift')
            ->whereNotNull('clock_in_at')
            ->whereNotNull('clock_out_at')
            ->whereNotNull('shift_id')
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()]);

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $entries = $query->get();

        $totalMinutes = 0;
        $overtimeEntries = 0;

        foreach ($entries as $entry) {
            $variance = $entry->variance_minutes;
            if ($variance !== null && $variance > 0) {
                $totalMinutes += $variance;
                $overtimeEntries++;
            }
        }

        return [
            'hours' => round($totalMinutes / 60, 2),
            'minutes' => $totalMinutes,
            'entries' => $overtimeEntries,
        ];
    }

    /**
     * Get undertime hours: sum of negative variances.
     *
     * @return array{hours: float, minutes: int, entries: int}
     */
    public function getUndertimeHours(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array {
        $query = TimeEntry::query()
            ->with('shift')
            ->whereNotNull('clock_in_at')
            ->whereNotNull('clock_out_at')
            ->whereNotNull('shift_id')
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()]);

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $entries = $query->get();

        $totalMinutes = 0;
        $undertimeEntries = 0;

        foreach ($entries as $entry) {
            $variance = $entry->variance_minutes;
            if ($variance !== null && $variance < 0) {
                $totalMinutes += abs($variance);
                $undertimeEntries++;
            }
        }

        return [
            'hours' => round($totalMinutes / 60, 2),
            'minutes' => $totalMinutes,
            'entries' => $undertimeEntries,
        ];
    }

    /**
     * Get missed shifts (no-shows and missed status).
     *
     * @return array{count: int, entries: Collection}
     */
    public function getMissedShifts(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array {
        $query = TimeEntry::query()
            ->with(['user', 'shift.businessRole', 'shift.department', 'shift.location'])
            ->where('status', TimeEntryStatus::Missed)
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()]);

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $entries = $query->get();

        return [
            'count' => $entries->count(),
            'entries' => $entries,
        ];
    }

    /**
     * Get employee summary for the given period.
     *
     * @return array<string, mixed>
     */
    public function getEmployeeSummary(
        int $employeeId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $user = User::find($employeeId);

        if (! $user) {
            return [];
        }

        $attendance = $this->getAttendanceRate($startDate, $endDate, null, null, $employeeId);
        $punctuality = $this->getPunctualityRate($startDate, $endDate, null, null, $employeeId);
        $overtime = $this->getOvertimeHours($startDate, $endDate, null, null, $employeeId);
        $undertime = $this->getUndertimeHours($startDate, $endDate, null, null, $employeeId);
        $missed = $this->getMissedShifts($startDate, $endDate, null, null, $employeeId);

        $totalWorked = $this->getTotalHoursWorked($startDate, $endDate, null, null, $employeeId);
        $totalScheduled = $this->getTotalScheduledHours($startDate, $endDate, null, null, $employeeId);

        return [
            'user' => $user,
            'attendance' => $attendance,
            'punctuality' => $punctuality,
            'overtime' => $overtime,
            'undertime' => $undertime,
            'missed_shifts' => $missed,
            'total_worked_hours' => $totalWorked,
            'total_scheduled_hours' => $totalScheduled,
            'average_variance_minutes' => $this->getAverageVariance($startDate, $endDate, null, null, $employeeId),
        ];
    }

    /**
     * Get department summary for the given period.
     *
     * @return array<string, mixed>
     */
    public function getDepartmentSummary(
        int $departmentId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $department = Department::find($departmentId);

        if (! $department) {
            return [];
        }

        $attendance = $this->getAttendanceRate($startDate, $endDate, $departmentId);
        $punctuality = $this->getPunctualityRate($startDate, $endDate, $departmentId);
        $overtime = $this->getOvertimeHours($startDate, $endDate, $departmentId);
        $undertime = $this->getUndertimeHours($startDate, $endDate, $departmentId);
        $missed = $this->getMissedShifts($startDate, $endDate, $departmentId);

        $totalWorked = $this->getTotalHoursWorked($startDate, $endDate, $departmentId);
        $totalScheduled = $this->getTotalScheduledHours($startDate, $endDate, $departmentId);

        return [
            'department' => $department,
            'attendance' => $attendance,
            'punctuality' => $punctuality,
            'overtime' => $overtime,
            'undertime' => $undertime,
            'missed_shifts' => $missed,
            'total_worked_hours' => $totalWorked,
            'total_scheduled_hours' => $totalScheduled,
            'average_variance_minutes' => $this->getAverageVariance($startDate, $endDate, $departmentId),
        ];
    }

    /**
     * Generate punctuality report data.
     *
     * @return array<string, mixed>
     */
    public function generatePunctualityReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null
    ): array {
        $query = TimeEntry::query()
            ->with(['user', 'shift.businessRole', 'shift.department', 'shift.location'])
            ->whereNotNull('clock_in_at')
            ->whereNotNull('shift_id')
            ->where('status', '!=', TimeEntryStatus::Missed)
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()])
            ->orderBy('clock_in_at');

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        $entries = $query->get();

        // Group by user
        $byUser = $entries->groupBy('user_id')->map(function ($userEntries) {
            $onTime = $userEntries->filter(fn ($e) => ! $e->is_late && $e->clock_in_variance_minutes >= -5)->count();
            $late = $userEntries->filter(fn ($e) => $e->is_late)->count();
            $early = $userEntries->filter(fn ($e) => $e->clock_in_variance_minutes < -5)->count();
            $total = $userEntries->count();
            $user = $userEntries->first()->user;

            return [
                'user' => $user,
                'on_time' => $onTime,
                'late' => $late,
                'early' => $early,
                'total' => $total,
                'punctuality_rate' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
                'average_late_minutes' => $late > 0 ? round($userEntries->filter(fn ($e) => $e->is_late)->avg('clock_in_variance_minutes')) : 0,
            ];
        });

        return [
            'entries' => $entries,
            'by_user' => $byUser->sortByDesc('punctuality_rate')->values(),
            'summary' => $this->getPunctualityRate($startDate, $endDate, $departmentId, $locationId),
        ];
    }

    /**
     * Generate hours worked report data.
     *
     * @return array<string, mixed>
     */
    public function generateHoursWorkedReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null
    ): array {
        $query = TimeEntry::query()
            ->with(['user', 'shift.businessRole', 'shift.department', 'shift.location'])
            ->whereNotNull('clock_in_at')
            ->whereNotNull('clock_out_at')
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()])
            ->orderBy('clock_in_at');

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        $entries = $query->get();

        // Group by user
        $byUser = $entries->groupBy('user_id')->map(function ($userEntries) {
            $scheduledMinutes = $userEntries->sum(fn ($e) => $e->scheduled_duration_minutes ?? 0);
            $actualMinutes = $userEntries->sum(fn ($e) => $e->total_worked_minutes ?? 0);
            $user = $userEntries->first()->user;

            return [
                'user' => $user,
                'scheduled_hours' => round($scheduledMinutes / 60, 2),
                'actual_hours' => round($actualMinutes / 60, 2),
                'variance_minutes' => $actualMinutes - $scheduledMinutes,
                'variance_hours' => round(($actualMinutes - $scheduledMinutes) / 60, 2),
                'entry_count' => $userEntries->count(),
            ];
        });

        $totalScheduled = $this->getTotalScheduledHours($startDate, $endDate, $departmentId, $locationId);
        $totalWorked = $this->getTotalHoursWorked($startDate, $endDate, $departmentId, $locationId);

        return [
            'entries' => $entries,
            'by_user' => $byUser->sortByDesc('actual_hours')->values(),
            'summary' => [
                'total_scheduled_hours' => $totalScheduled,
                'total_worked_hours' => $totalWorked,
                'variance_hours' => round($totalWorked - $totalScheduled, 2),
            ],
        ];
    }

    /**
     * Generate overtime report data.
     *
     * @return array<string, mixed>
     */
    public function generateOvertimeReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null
    ): array {
        $query = TimeEntry::query()
            ->with(['user', 'shift.businessRole', 'shift.department', 'shift.location'])
            ->whereNotNull('clock_in_at')
            ->whereNotNull('clock_out_at')
            ->whereNotNull('shift_id')
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()])
            ->orderBy('clock_in_at');

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        $entries = $query->get();

        // Only entries with overtime
        $overtimeEntries = $entries->filter(fn ($e) => $e->is_overtime);

        // Group by user
        $byUser = $overtimeEntries->groupBy('user_id')->map(function ($userEntries) {
            $overtimeMinutes = $userEntries->sum(fn ($e) => max(0, $e->variance_minutes ?? 0));
            $user = $userEntries->first()->user;

            return [
                'user' => $user,
                'overtime_hours' => round($overtimeMinutes / 60, 2),
                'overtime_minutes' => $overtimeMinutes,
                'overtime_entries' => $userEntries->count(),
            ];
        });

        return [
            'entries' => $overtimeEntries,
            'by_user' => $byUser->sortByDesc('overtime_hours')->values(),
            'summary' => $this->getOvertimeHours($startDate, $endDate, $departmentId, $locationId),
        ];
    }

    /**
     * Generate absence report data.
     *
     * @return array<string, mixed>
     */
    public function generateAbsenceReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null
    ): array {
        $missed = $this->getMissedShifts($startDate, $endDate, $departmentId, $locationId);

        // Group by user
        $byUser = $missed['entries']->groupBy('user_id')->map(function ($userEntries) {
            $user = $userEntries->first()->user;

            return [
                'user' => $user,
                'missed_count' => $userEntries->count(),
                'entries' => $userEntries,
            ];
        });

        return [
            'entries' => $missed['entries'],
            'by_user' => $byUser->sortByDesc('missed_count')->values(),
            'summary' => [
                'total_missed' => $missed['count'],
            ],
        ];
    }

    /**
     * Generate attendance summary (dashboard metrics).
     *
     * @return array<string, mixed>
     */
    public function generateAttendanceSummary(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null
    ): array {
        return [
            'attendance' => $this->getAttendanceRate($startDate, $endDate, $departmentId, $locationId),
            'punctuality' => $this->getPunctualityRate($startDate, $endDate, $departmentId, $locationId),
            'overtime' => $this->getOvertimeHours($startDate, $endDate, $departmentId, $locationId),
            'undertime' => $this->getUndertimeHours($startDate, $endDate, $departmentId, $locationId),
            'missed_shifts' => $this->getMissedShifts($startDate, $endDate, $departmentId, $locationId)['count'],
            'total_hours_worked' => $this->getTotalHoursWorked($startDate, $endDate, $departmentId, $locationId),
            'total_scheduled_hours' => $this->getTotalScheduledHours($startDate, $endDate, $departmentId, $locationId),
            'average_variance_minutes' => $this->getAverageVariance($startDate, $endDate, $departmentId, $locationId),
        ];
    }

    /**
     * Get total hours worked for the period.
     */
    public function getTotalHoursWorked(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): float {
        $query = TimeEntry::query()
            ->whereNotNull('clock_in_at')
            ->whereNotNull('clock_out_at')
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()]);

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $entries = $query->get();
        $totalMinutes = $entries->sum(fn ($e) => $e->total_worked_minutes ?? 0);

        return round($totalMinutes / 60, 2);
    }

    /**
     * Get total scheduled hours for the period.
     */
    public function getTotalScheduledHours(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): float {
        $query = Shift::query()
            ->forDateRange($startDate, $endDate)
            ->assigned();

        if ($departmentId !== null) {
            $query->where('department_id', $departmentId);
        }

        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $shifts = $query->get();
        $totalMinutes = $shifts->sum(fn ($s) => $s->working_minutes);

        return round($totalMinutes / 60, 2);
    }

    /**
     * Get average variance in minutes for the period.
     */
    public function getAverageVariance(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): ?float {
        $query = TimeEntry::query()
            ->with('shift')
            ->whereNotNull('clock_in_at')
            ->whereNotNull('clock_out_at')
            ->whereNotNull('shift_id')
            ->whereBetween('clock_in_at', [$startDate, $endDate->endOfDay()]);

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $entries = $query->get();

        if ($entries->isEmpty()) {
            return null;
        }

        $total = 0;
        $count = 0;

        foreach ($entries as $entry) {
            $variance = $entry->variance_minutes;
            if ($variance !== null) {
                $total += $variance;
                $count++;
            }
        }

        return $count > 0 ? round($total / $count, 1) : null;
    }

    /**
     * Export report data to CSV format.
     *
     * @return string CSV content
     */
    public function exportToCsv(string $reportType, array $reportData): string
    {
        $output = '';

        switch ($reportType) {
            case 'punctuality':
                $output = $this->exportPunctualityToCsv($reportData);
                break;
            case 'hours':
                $output = $this->exportHoursWorkedToCsv($reportData);
                break;
            case 'overtime':
                $output = $this->exportOvertimeToCsv($reportData);
                break;
            case 'absence':
                $output = $this->exportAbsenceToCsv($reportData);
                break;
        }

        return $output;
    }

    protected function exportPunctualityToCsv(array $data): string
    {
        $lines = [];
        $lines[] = 'Employee,On Time,Late,Early,Total,Punctuality Rate,Avg Late Minutes';

        foreach ($data['by_user'] as $row) {
            $lines[] = implode(',', [
                '"'.$row['user']->full_name.'"',
                $row['on_time'],
                $row['late'],
                $row['early'],
                $row['total'],
                $row['punctuality_rate'].'%',
                $row['average_late_minutes'],
            ]);
        }

        return implode("\n", $lines);
    }

    protected function exportHoursWorkedToCsv(array $data): string
    {
        $lines = [];
        $lines[] = 'Employee,Scheduled Hours,Actual Hours,Variance Hours,Entries';

        foreach ($data['by_user'] as $row) {
            $lines[] = implode(',', [
                '"'.$row['user']->full_name.'"',
                $row['scheduled_hours'],
                $row['actual_hours'],
                $row['variance_hours'],
                $row['entry_count'],
            ]);
        }

        return implode("\n", $lines);
    }

    protected function exportOvertimeToCsv(array $data): string
    {
        $lines = [];
        $lines[] = 'Employee,Overtime Hours,Overtime Entries';

        foreach ($data['by_user'] as $row) {
            $lines[] = implode(',', [
                '"'.$row['user']->full_name.'"',
                $row['overtime_hours'],
                $row['overtime_entries'],
            ]);
        }

        return implode("\n", $lines);
    }

    protected function exportAbsenceToCsv(array $data): string
    {
        $lines = [];
        $lines[] = 'Employee,Missed Shifts';

        foreach ($data['by_user'] as $row) {
            $lines[] = implode(',', [
                '"'.$row['user']->full_name.'"',
                $row['missed_count'],
            ]);
        }

        return implode("\n", $lines);
    }
}
