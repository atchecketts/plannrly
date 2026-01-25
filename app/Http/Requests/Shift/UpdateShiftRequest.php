<?php

namespace App\Http\Requests\Shift;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $shift = $this->route('shift');

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->isLocationAdmin($shift->location_id)) {
            return true;
        }

        return $user->isDepartmentAdmin($shift->department_id);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shift = $this->route('shift');

        return [
            'location_id' => ['sometimes', 'exists:locations,id'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'business_role_id' => ['sometimes', 'exists:business_roles,id'],
            'user_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($shift) {
                    if ($value === null) {
                        return; // Unassigning is always allowed
                    }

                    $targetUser = User::find($value);
                    $roleId = $this->input('business_role_id', $shift->business_role_id);

                    if ($targetUser && ! $targetUser->businessRoles()->where('business_role_id', $roleId)->exists()) {
                        $fail('The selected employee does not have the required role for this shift.');
                    }

                    // Check for overlapping shifts
                    $date = $this->input('date', $shift->date);
                    $startTime = $this->input('start_time', $shift->start_time->format('H:i'));
                    $endTime = $this->input('end_time', $shift->end_time->format('H:i'));

                    if ($this->hasOverlappingShift($value, $date, $startTime, $endTime, $shift->id)) {
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Please select a date.',
            'start_time.required' => 'Please enter a start time.',
            'end_time.required' => 'Please enter an end time.',
        ];
    }

    /**
     * Configure the validator instance to check for overlaps when date/time changes.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Skip if there are already validation errors
            if ($validator->errors()->any()) {
                return;
            }

            $shift = $this->route('shift');

            // Only check if user_id is not being changed and shift is assigned
            if (! $this->has('user_id') && $shift->user_id) {
                $date = $this->input('date', $shift->date);
                $startTime = $this->input('start_time', $shift->start_time->format('H:i'));
                $endTime = $this->input('end_time', $shift->end_time->format('H:i'));

                if ($this->hasOverlappingShift($shift->user_id, $date, $startTime, $endTime, $shift->id)) {
                    $validator->errors()->add('user_id', 'This shift overlaps with another shift for this employee.');
                }
            }
        });
    }

    /**
     * Check if a shift overlaps with existing shifts for the user.
     */
    protected function hasOverlappingShift(int $userId, string $date, string $startTime, string $endTime, ?int $excludeShiftId = null): bool
    {
        $query = Shift::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('tenant_id', $this->user()->tenant_id);

        if ($excludeShiftId) {
            $query->where('id', '!=', $excludeShiftId);
        }

        $existingShifts = $query->get();

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
