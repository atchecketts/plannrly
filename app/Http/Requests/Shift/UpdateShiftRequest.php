<?php

namespace App\Http\Requests\Shift;

use App\Enums\ShiftStatus;
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
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
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
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
