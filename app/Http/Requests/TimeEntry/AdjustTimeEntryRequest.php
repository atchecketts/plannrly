<?php

namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;

class AdjustTimeEntryRequest extends FormRequest
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
            'adjustment_reason' => ['required', 'string', 'min:10', 'max:1000'],
            'clock_in_at' => ['nullable', 'date'],
            'clock_out_at' => ['nullable', 'date', 'after:clock_in_at'],
            'actual_break_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'adjustment_reason.required' => 'Please provide a reason for the adjustment.',
            'adjustment_reason.min' => 'The adjustment reason must be at least 10 characters.',
            'adjustment_reason.max' => 'The adjustment reason cannot exceed 1000 characters.',
            'clock_in_at.date' => 'The clock in time must be a valid date and time.',
            'clock_out_at.date' => 'The clock out time must be a valid date and time.',
            'clock_out_at.after' => 'The clock out time must be after the clock in time.',
            'actual_break_minutes.integer' => 'The break duration must be a whole number.',
            'actual_break_minutes.min' => 'The break duration cannot be negative.',
            'actual_break_minutes.max' => 'The break duration cannot exceed 8 hours.',
        ];
    }
}
