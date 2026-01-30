<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class PasteShiftsRequest extends FormRequest
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
        $tenantId = $this->user()->tenant_id;

        return [
            'shifts' => ['required', 'array', 'min:1'],
            'shifts.*.start_time' => ['required', 'date_format:H:i'],
            'shifts.*.end_time' => ['required', 'date_format:H:i'],
            'shifts.*.business_role_id' => ['required', "exists:business_roles,id,tenant_id,{$tenantId}"],
            'shifts.*.department_id' => ['required', "exists:departments,id,tenant_id,{$tenantId}"],
            'shifts.*.location_id' => ['required', "exists:locations,id,tenant_id,{$tenantId}"],
            'shifts.*.break_duration_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'shifts.*.notes' => ['nullable', 'string', 'max:1000'],
            'target_date' => ['required', 'date'],
            'target_user_id' => ['nullable', "exists:users,id,tenant_id,{$tenantId}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shifts.required' => 'No shifts to paste.',
            'shifts.min' => 'No shifts to paste.',
            'target_date.required' => 'Please select a target date.',
            'target_date.date' => 'Invalid target date format.',
        ];
    }

    /**
     * Configure the validator instance for additional validation.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $this->validatePermissions($validator);
            $this->validateRoleCompatibility($validator);
            $this->validateOverlaps($validator);
        });
    }

    /**
     * Validate user has permission to create shifts in the locations/departments.
     */
    protected function validatePermissions(\Illuminate\Validation\Validator $validator): void
    {
        $user = $this->user();
        $shifts = $this->input('shifts', []);

        foreach ($shifts as $index => $shift) {
            $canCreate = $user->isSuperAdmin()
                || $user->isAdmin()
                || $user->isLocationAdmin($shift['location_id'] ?? null)
                || $user->isDepartmentAdmin($shift['department_id'] ?? null);

            if (! $canCreate) {
                $validator->errors()->add(
                    "shifts.{$index}",
                    'You do not have permission to create shifts in this location/department.'
                );
            }
        }
    }

    /**
     * Validate role compatibility if target user is specified.
     */
    protected function validateRoleCompatibility(\Illuminate\Validation\Validator $validator): void
    {
        $targetUserId = $this->input('target_user_id');
        if (! $targetUserId) {
            return;
        }

        $targetUser = User::find($targetUserId);
        if (! $targetUser) {
            return;
        }

        $userRoleIds = $targetUser->businessRoles()->pluck('business_role_id')->toArray();
        $shifts = $this->input('shifts', []);

        foreach ($shifts as $index => $shift) {
            $roleId = $shift['business_role_id'] ?? null;
            if ($roleId && ! in_array($roleId, $userRoleIds)) {
                $validator->errors()->add(
                    "shifts.{$index}.business_role_id",
                    'The target employee does not have the required role for this shift.'
                );
            }
        }
    }

    /**
     * Validate shifts don't overlap with existing shifts.
     */
    protected function validateOverlaps(\Illuminate\Validation\Validator $validator): void
    {
        $targetUserId = $this->input('target_user_id');
        if (! $targetUserId) {
            return;
        }

        $targetDate = $this->input('target_date');
        $shifts = $this->input('shifts', []);
        $tenantId = $this->user()->tenant_id;

        // Get existing shifts for this user on target date
        $existingShifts = Shift::withoutGlobalScopes()
            ->where('user_id', $targetUserId)
            ->whereDate('date', $targetDate)
            ->where('tenant_id', $tenantId)
            ->get();

        foreach ($shifts as $index => $shift) {
            $newStart = $shift['start_time'] ?? '09:00';
            $newEnd = $shift['end_time'] ?? '17:00';

            foreach ($existingShifts as $existing) {
                if ($this->shiftsOverlap(
                    $newStart,
                    $newEnd,
                    $existing->start_time->format('H:i'),
                    $existing->end_time->format('H:i')
                )) {
                    $validator->errors()->add(
                        "shifts.{$index}",
                        "Shift {$newStart}-{$newEnd} overlaps with an existing shift."
                    );
                    break;
                }
            }
        }

        // Also check for overlaps between the pasted shifts themselves
        for ($i = 0; $i < count($shifts); $i++) {
            for ($j = $i + 1; $j < count($shifts); $j++) {
                if ($this->shiftsOverlap(
                    $shifts[$i]['start_time'] ?? '09:00',
                    $shifts[$i]['end_time'] ?? '17:00',
                    $shifts[$j]['start_time'] ?? '09:00',
                    $shifts[$j]['end_time'] ?? '17:00'
                )) {
                    $validator->errors()->add(
                        "shifts.{$j}",
                        'Multiple pasted shifts overlap with each other.'
                    );
                }
            }
        }
    }

    /**
     * Check if two time ranges overlap.
     */
    protected function shiftsOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $start1Min = $this->timeToMinutes($start1);
        $end1Min = $this->timeToMinutes($end1);
        $start2Min = $this->timeToMinutes($start2);
        $end2Min = $this->timeToMinutes($end2);

        if ($end1Min <= $start1Min) {
            $end1Min += 1440;
        }
        if ($end2Min <= $start2Min) {
            $end2Min += 1440;
        }

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
