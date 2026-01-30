<?php

namespace App\Services;

use App\Enums\CoverageStatus;
use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\StaffingRequirement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CoverageAnalysisService
{
    /**
     * Analyze coverage for a date range.
     *
     * @return Collection<int, array{date: Carbon, coverage: Collection}>
     */
    public function analyzeCoverage(
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId = null,
        ?int $departmentId = null
    ): Collection {
        $results = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $results->push([
                'date' => $currentDate->copy(),
                'coverage' => $this->getDayCoverage($currentDate, $locationId, $departmentId),
            ]);
            $currentDate->addDay();
        }

        return $results;
    }

    /**
     * Get coverage analysis for a specific day.
     *
     * @return Collection<int, CoverageResult>
     */
    public function getDayCoverage(
        Carbon $date,
        ?int $locationId = null,
        ?int $departmentId = null
    ): Collection {
        $dayOfWeek = $date->dayOfWeek;

        // Get all active staffing requirements for this day
        $query = StaffingRequirement::active()
            ->forDayOfWeek($dayOfWeek)
            ->with('businessRole');

        if ($locationId !== null) {
            $query->forLocation($locationId);
        }

        if ($departmentId !== null) {
            $query->forDepartment($departmentId);
        }

        $requirements = $query->get();

        // Group requirements by business role and time slot
        $results = collect();

        foreach ($requirements as $requirement) {
            $scheduledCount = $this->countScheduledEmployees(
                $date,
                $requirement->start_time->format('H:i'),
                $requirement->end_time->format('H:i'),
                $requirement->business_role_id,
                $requirement->location_id,
                $requirement->department_id
            );

            $results->push(new CoverageResult(
                status: $this->determineStatus($scheduledCount, $requirement->min_employees, $requirement->max_employees),
                scheduled: $scheduledCount,
                minRequired: $requirement->min_employees,
                maxAllowed: $requirement->max_employees,
                businessRoleId: $requirement->business_role_id,
                businessRoleName: $requirement->businessRole->name,
                startTime: $requirement->start_time->format('H:i'),
                endTime: $requirement->end_time->format('H:i'),
                requirementId: $requirement->id
            ));
        }

        return $results;
    }

    /**
     * Get coverage for a specific time slot.
     */
    public function getTimeSlotCoverage(
        Carbon $date,
        string $startTime,
        string $endTime,
        int $businessRoleId,
        ?int $locationId = null,
        ?int $departmentId = null
    ): CoverageResult {
        $dayOfWeek = $date->dayOfWeek;

        // Find matching staffing requirement
        $query = StaffingRequirement::active()
            ->forDayOfWeek($dayOfWeek)
            ->forBusinessRole($businessRoleId)
            ->forTimeSlot($startTime, $endTime);

        if ($locationId !== null) {
            $query->forLocation($locationId);
        }

        if ($departmentId !== null) {
            $query->forDepartment($departmentId);
        }

        $requirement = $query->first();

        if (! $requirement) {
            return new CoverageResult(
                status: CoverageStatus::NoRequirement,
                scheduled: $this->countScheduledEmployees($date, $startTime, $endTime, $businessRoleId, $locationId, $departmentId),
                minRequired: 0,
                maxAllowed: null,
                businessRoleId: $businessRoleId,
                businessRoleName: null,
                startTime: $startTime,
                endTime: $endTime,
                requirementId: null
            );
        }

        $scheduledCount = $this->countScheduledEmployees(
            $date,
            $startTime,
            $endTime,
            $businessRoleId,
            $requirement->location_id,
            $requirement->department_id
        );

        return new CoverageResult(
            status: $this->determineStatus($scheduledCount, $requirement->min_employees, $requirement->max_employees),
            scheduled: $scheduledCount,
            minRequired: $requirement->min_employees,
            maxAllowed: $requirement->max_employees,
            businessRoleId: $businessRoleId,
            businessRoleName: $requirement->businessRole->name ?? null,
            startTime: $startTime,
            endTime: $endTime,
            requirementId: $requirement->id
        );
    }

    /**
     * Count scheduled employees for a given time slot.
     */
    public function countScheduledEmployees(
        Carbon $date,
        string $startTime,
        string $endTime,
        int $businessRoleId,
        ?int $locationId = null,
        ?int $departmentId = null
    ): int {
        $query = Shift::whereDate('date', $date)
            ->where('business_role_id', $businessRoleId)
            ->whereNotNull('user_id')
            ->whereIn('status', [ShiftStatus::Published, ShiftStatus::InProgress, ShiftStatus::Draft]);

        // Check for shifts that overlap with the time window
        $query->where(function ($q) use ($startTime, $endTime) {
            // Shift overlaps if it starts before end_time AND ends after start_time
            $q->whereRaw('TIME(start_time) < ?', [$endTime])
                ->whereRaw('TIME(end_time) > ?', [$startTime]);
        });

        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        }

        if ($departmentId !== null) {
            $query->where('department_id', $departmentId);
        }

        return $query->count();
    }

    /**
     * Get a summary of coverage issues for a date range.
     *
     * @return array{understaffed: int, overstaffed: int, adequate: int, total: int}
     */
    public function getCoverageSummary(
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId = null,
        ?int $departmentId = null
    ): array {
        $coverage = $this->analyzeCoverage($startDate, $endDate, $locationId, $departmentId);

        $summary = [
            'understaffed' => 0,
            'overstaffed' => 0,
            'adequate' => 0,
            'no_requirement' => 0,
            'total' => 0,
        ];

        foreach ($coverage as $day) {
            foreach ($day['coverage'] as $result) {
                $summary['total']++;
                match ($result->status) {
                    CoverageStatus::Understaffed => $summary['understaffed']++,
                    CoverageStatus::Overstaffed => $summary['overstaffed']++,
                    CoverageStatus::Adequate => $summary['adequate']++,
                    CoverageStatus::NoRequirement => $summary['no_requirement']++,
                };
            }
        }

        return $summary;
    }

    /**
     * Get all coverage issues (understaffed/overstaffed) for a date range.
     *
     * @return Collection<int, CoverageResult>
     */
    public function getCoverageIssues(
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId = null,
        ?int $departmentId = null
    ): Collection {
        $coverage = $this->analyzeCoverage($startDate, $endDate, $locationId, $departmentId);

        return $coverage->flatMap(function ($day) {
            return $day['coverage']->filter(fn (CoverageResult $result) => $result->status->hasProblem())
                ->map(function (CoverageResult $result) use ($day) {
                    return $result->withDate($day['date']);
                });
        });
    }

    /**
     * Determine the coverage status based on scheduled count and requirements.
     */
    private function determineStatus(int $scheduled, int $minRequired, ?int $maxAllowed): CoverageStatus
    {
        if ($scheduled < $minRequired) {
            return CoverageStatus::Understaffed;
        }

        if ($maxAllowed !== null && $scheduled > $maxAllowed) {
            return CoverageStatus::Overstaffed;
        }

        return CoverageStatus::Adequate;
    }
}

/**
 * Value object representing a coverage analysis result.
 */
class CoverageResult
{
    public function __construct(
        public readonly CoverageStatus $status,
        public readonly int $scheduled,
        public readonly int $minRequired,
        public readonly ?int $maxAllowed,
        public readonly int $businessRoleId,
        public readonly ?string $businessRoleName,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly ?int $requirementId,
        public readonly ?Carbon $date = null,
    ) {}

    /**
     * Get a human-readable message describing the coverage status.
     */
    public function getMessage(): string
    {
        $role = $this->businessRoleName ?? 'Role';
        $timeWindow = "{$this->startTime} - {$this->endTime}";

        return match ($this->status) {
            CoverageStatus::Understaffed => "{$role} ({$timeWindow}): {$this->scheduled}/{$this->minRequired} employees - understaffed by ".($this->minRequired - $this->scheduled),
            CoverageStatus::Overstaffed => "{$role} ({$timeWindow}): {$this->scheduled}/{$this->maxAllowed} max - overstaffed by ".($this->scheduled - $this->maxAllowed),
            CoverageStatus::Adequate => "{$role} ({$timeWindow}): {$this->scheduled} employees - adequate coverage",
            CoverageStatus::NoRequirement => "{$role} ({$timeWindow}): {$this->scheduled} employees - no requirement defined",
        };
    }

    /**
     * Get a short status label.
     */
    public function getShortLabel(): string
    {
        return match ($this->status) {
            CoverageStatus::Understaffed => '-'.($this->minRequired - $this->scheduled),
            CoverageStatus::Overstaffed => '+'.($this->scheduled - $this->maxAllowed),
            CoverageStatus::Adequate => 'OK',
            CoverageStatus::NoRequirement => '-',
        };
    }

    /**
     * Get the shortage or surplus count.
     */
    public function getDifference(): int
    {
        return match ($this->status) {
            CoverageStatus::Understaffed => $this->scheduled - $this->minRequired,
            CoverageStatus::Overstaffed => $this->scheduled - $this->maxAllowed,
            default => 0,
        };
    }

    /**
     * Create a new instance with a date attached.
     */
    public function withDate(Carbon $date): self
    {
        return new self(
            status: $this->status,
            scheduled: $this->scheduled,
            minRequired: $this->minRequired,
            maxAllowed: $this->maxAllowed,
            businessRoleId: $this->businessRoleId,
            businessRoleName: $this->businessRoleName,
            startTime: $this->startTime,
            endTime: $this->endTime,
            requirementId: $this->requirementId,
            date: $date,
        );
    }
}
