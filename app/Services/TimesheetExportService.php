<?php

namespace App\Services;

use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimesheetExportService
{
    /**
     * Get timesheet data for export.
     *
     * @return Collection<int, TimeEntry>
     */
    public function getExportData(
        Carbon $startDate,
        Carbon $endDate,
        ?int $userId = null,
        ?int $departmentId = null,
        ?int $locationId = null
    ): Collection {
        $query = TimeEntry::query()
            ->with(['user', 'shift.businessRole', 'shift.department', 'shift.location', 'approvedBy'])
            ->whereBetween('clock_in_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('clock_in_at');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($departmentId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($locationId !== null) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        return $query->get();
    }

    /**
     * Export timesheet data to CSV format.
     */
    public function exportToCsv(Collection $entries): string
    {
        $rows = [];

        // Header row
        $rows[] = [
            'Employee Name',
            'Employee Email',
            'Date',
            'Scheduled Start',
            'Scheduled End',
            'Actual Start',
            'Actual End',
            'Scheduled Hours',
            'Actual Hours',
            'Variance (minutes)',
            'Break Time (minutes)',
            'Department',
            'Location',
            'Role',
            'Status',
            'Approved By',
            'Approved At',
            'Notes',
        ];

        foreach ($entries as $entry) {
            $rows[] = $this->formatEntryRow($entry);
        }

        return $this->arrayToCsv($rows);
    }

    /**
     * Export timesheet data to payroll format (simplified CSV for payroll systems).
     */
    public function exportToPayrollCsv(Collection $entries): string
    {
        $rows = [];

        // Header row - simplified for payroll
        $rows[] = [
            'Employee ID',
            'Employee Name',
            'Date',
            'Hours Worked',
            'Overtime Hours',
            'Break Hours',
            'Status',
        ];

        foreach ($entries as $entry) {
            $rows[] = $this->formatPayrollRow($entry);
        }

        return $this->arrayToCsv($rows);
    }

    /**
     * Get summary statistics for the export period.
     *
     * @return array{total_entries: int, total_hours: float, total_overtime: float, total_employees: int}
     */
    public function getExportSummary(Collection $entries): array
    {
        $totalMinutes = 0;
        $overtimeMinutes = 0;

        foreach ($entries as $entry) {
            $totalMinutes += $entry->total_worked_minutes ?? 0;
            $variance = $entry->variance_minutes ?? 0;
            if ($variance > 0) {
                $overtimeMinutes += $variance;
            }
        }

        return [
            'total_entries' => $entries->count(),
            'total_hours' => round($totalMinutes / 60, 2),
            'total_overtime' => round($overtimeMinutes / 60, 2),
            'total_employees' => $entries->pluck('user_id')->unique()->count(),
        ];
    }

    /**
     * Format a time entry as a CSV row.
     *
     * @return array<int, mixed>
     */
    protected function formatEntryRow(TimeEntry $entry): array
    {
        $shift = $entry->shift;

        return [
            $entry->user?->full_name ?? 'Unknown',
            $entry->user?->email ?? '',
            $entry->clock_in_at?->format('Y-m-d') ?? '',
            $shift?->start_time?->format('H:i') ?? '',
            $shift?->end_time?->format('H:i') ?? '',
            $entry->clock_in_at?->format('H:i') ?? '',
            $entry->clock_out_at?->format('H:i') ?? '',
            $shift ? round($shift->working_minutes / 60, 2) : '',
            $entry->total_worked_hours ?? '',
            $entry->variance_minutes ?? '',
            $entry->actual_break_minutes ?? 0,
            $shift?->department?->name ?? '',
            $shift?->location?->name ?? '',
            $shift?->businessRole?->name ?? '',
            $entry->status->label(),
            $entry->approvedBy?->full_name ?? '',
            $entry->approved_at?->format('Y-m-d H:i') ?? '',
            $entry->notes ?? '',
        ];
    }

    /**
     * Format a time entry as a payroll CSV row.
     *
     * @return array<int, mixed>
     */
    protected function formatPayrollRow(TimeEntry $entry): array
    {
        $workedMinutes = $entry->total_worked_minutes ?? 0;
        $varianceMinutes = $entry->variance_minutes ?? 0;
        $overtimeMinutes = $varianceMinutes > 0 ? $varianceMinutes : 0;
        $regularMinutes = $workedMinutes - $overtimeMinutes;

        return [
            $entry->user_id,
            $entry->user?->full_name ?? 'Unknown',
            $entry->clock_in_at?->format('Y-m-d') ?? '',
            round($regularMinutes / 60, 2),
            round($overtimeMinutes / 60, 2),
            round(($entry->actual_break_minutes ?? 0) / 60, 2),
            $entry->status->label(),
        ];
    }

    /**
     * Convert array to CSV string.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    protected function arrayToCsv(array $rows): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
