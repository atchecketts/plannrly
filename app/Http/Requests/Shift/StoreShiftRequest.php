<?php

namespace App\Http\Requests\Shift;

use App\Enums\ShiftStatus;
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
            'rota_id' => ['required', 'exists:rotas,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'business_role_id' => ['required', 'exists:business_roles,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
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
            'rota_id.required' => 'Please select a rota.',
            'location_id.required' => 'Please select a location.',
            'department_id.required' => 'Please select a department.',
            'business_role_id.required' => 'Please select a role.',
            'date.required' => 'Please select a date.',
            'start_time.required' => 'Please enter a start time.',
            'end_time.required' => 'Please enter an end time.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
