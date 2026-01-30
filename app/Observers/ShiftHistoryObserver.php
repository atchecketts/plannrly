<?php

namespace App\Observers;

use App\Enums\ScheduleHistoryAction;
use App\Models\ScheduleHistory;
use App\Models\Shift;

class ShiftHistoryObserver
{
    /**
     * Fields to track for history.
     *
     * @var array<int, string>
     */
    protected array $trackedFields = [
        'user_id',
        'location_id',
        'department_id',
        'business_role_id',
        'date',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'notes',
        'status',
    ];

    public function created(Shift $shift): void
    {
        $this->logHistory($shift, ScheduleHistoryAction::Created, null, $this->getTrackedValues($shift));
    }

    public function updated(Shift $shift): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($this->trackedFields as $field) {
            if ($shift->isDirty($field)) {
                $oldValues[$field] = $this->formatValue($shift->getOriginal($field));
                $newValues[$field] = $this->formatValue($shift->$field);
            }
        }

        // Only log if there are meaningful changes
        if (! empty($newValues)) {
            $this->logHistory($shift, ScheduleHistoryAction::Updated, $oldValues, $newValues);
        }
    }

    public function deleted(Shift $shift): void
    {
        $this->logHistory($shift, ScheduleHistoryAction::Deleted, $this->getTrackedValues($shift), null);
    }

    /**
     * Get tracked field values from a shift.
     *
     * @return array<string, mixed>
     */
    protected function getTrackedValues(Shift $shift): array
    {
        $values = [];
        foreach ($this->trackedFields as $field) {
            $values[$field] = $this->formatValue($shift->$field);
        }

        return $values;
    }

    /**
     * Format a value for storage.
     */
    protected function formatValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    /**
     * Log the history entry.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected function logHistory(Shift $shift, ScheduleHistoryAction $action, ?array $oldValues, ?array $newValues): void
    {
        $userId = auth()->id();

        // Skip if no authenticated user (e.g., system operations without auth context)
        if (! $userId) {
            return;
        }

        ScheduleHistory::create([
            'tenant_id' => $shift->tenant_id,
            'shift_id' => $shift->id,
            'user_id' => $userId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
