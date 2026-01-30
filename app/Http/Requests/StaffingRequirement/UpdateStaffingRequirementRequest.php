<?php

namespace App\Http\Requests\StaffingRequirement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffingRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_role_id' => ['required', 'exists:business_roles,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'min_employees' => ['required', 'integer', 'min:0'],
            'max_employees' => ['nullable', 'integer', 'min:0', 'gte:min_employees'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'business_role_id.required' => 'Please select a business role.',
            'business_role_id.exists' => 'The selected business role does not exist.',
            'day_of_week.required' => 'Please select a day of the week.',
            'day_of_week.min' => 'Invalid day of the week selected.',
            'day_of_week.max' => 'Invalid day of the week selected.',
            'start_time.required' => 'Please enter a start time.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.required' => 'Please enter an end time.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            'min_employees.required' => 'Please enter the minimum number of employees.',
            'min_employees.min' => 'Minimum employees cannot be negative.',
            'max_employees.gte' => 'Maximum employees must be greater than or equal to minimum employees.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);

        // Convert empty strings to null for optional fields
        if ($this->location_id === '') {
            $this->merge(['location_id' => null]);
        }

        if ($this->department_id === '') {
            $this->merge(['department_id' => null]);
        }

        if ($this->max_employees === '' || $this->max_employees === '0') {
            $this->merge(['max_employees' => null]);
        }
    }
}
