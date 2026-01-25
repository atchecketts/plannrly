<?php

namespace App\Http\Requests\Shift;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'business_role_id' => ['required', 'exists:business_roles,id'],
            'user_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value === null) {
                        return; // Unassigning is always allowed
                    }

                    $targetUser = User::find($value);
                    $roleId = $this->input('business_role_id');

                    if ($targetUser && $roleId && ! $targetUser->businessRoles()->where('business_role_id', $roleId)->exists()) {
                        $fail('The selected employee does not have the required role for this shift.');
                    }

                    // Check for overlapping shifts
                    $date = $this->input('date');
                    $startTime = $this->input('start_time');
                    $endTime = $this->input('end_time');

                    if ($date && $startTime && $endTime && $this->hasOverlappingShift($value, $date, $startTime, $endTime)) {
                        $fail('This shift overlaps with another shift for this employee.');
                    }
                },
            ],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($value === $this->input('start_time')) {
                        $fail('The start time and end time cannot be the same.');
                    }
                },
            ],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(ShiftStatus::class)],
            'is_recurring' => ['boolean'],
            'recurrence_rule' => ['nullable', 'array'],
            'recurrence_rule.frequency' => ['required_if:is_recurring,true', 'in:daily,weekly,monthly'],
            'recurrence_rule.interval' => ['nullable', 'integer', 'min:1'],
            'recurrence_rule.days_of_week' => ['nullable', 'array'],
            'recurrence_rule.end_date' => ['nullable', 'date', 'after:date'],
            'recurrence_rule.end_after_occurrences' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location_id.required' => 'Please select a location.',
            'department_id.required' => 'Please select a department.',
            'business_role_id.required' => 'Please select a role.',
            'date.required' => 'Please select a date.',
            'start_time.required' => 'Please enter a start time.',
            'end_time.required' => 'Please enter an end time.',
        ];
    }

    /**
     * Check if a shift overlaps with existing shifts for the user.
     */
    protected function hasOverlappingShift(int $userId, string $date, string $startTime, string $endTime): bool
    {
        $existingShifts = Shift::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('tenant_id', $this->user()->tenant_id)
            ->get();

        foreach ($existingShifts as $existing) {
            if ($this->shiftsOverlap(
                $startTime,
                $endTime,
                $existing->start_time->format('H:i'),
                $existing->end_time->format('H:i')
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if two time ranges overlap.
     */
    protected function shiftsOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        // Convert times to minutes for easier comparison
        $start1Min = $this->timeToMinutes($start1);
        $end1Min = $this->timeToMinutes($end1);
        $start2Min = $this->timeToMinutes($start2);
        $end2Min = $this->timeToMinutes($end2);

        // Handle overnight shifts (end time < start time means it goes past midnight)
        if ($end1Min <= $start1Min) {
            $end1Min += 1440; // Add 24 hours
        }
        if ($end2Min <= $start2Min) {
            $end2Min += 1440; // Add 24 hours
        }

        // Check for overlap: two ranges overlap if one starts before the other ends
        return $start1Min < $end2Min && $start2Min < $end1Min;
    }

    /**
     * Convert time string (H:i) to minutes since midnight.
     */
    protected function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);

        return (int) $parts[0] * 60 + (int) $parts[1];
    }
}
