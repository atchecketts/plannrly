<?php

namespace App\Http\Requests\Shift;

use App\Enums\ShiftStatus;
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
}
