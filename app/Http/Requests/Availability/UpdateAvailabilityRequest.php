<?php

namespace App\Http\Requests\Availability;

use App\Enums\AvailabilityType;
use App\Enums\PreferenceLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $availability = $this->route('availability');

        return $availability->user_id === $this->user()->id
            || $this->user()->canManageUser($availability->user);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AvailabilityType::class)],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6', 'required_if:type,recurring'],
            'specific_date' => ['nullable', 'date', 'required_if:type,specific_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'is_available' => ['boolean'],
            'preference_level' => ['required', Rule::enum(PreferenceLevel::class)],
            'notes' => ['nullable', 'string', 'max:500'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'day_of_week.required_if' => 'Please select a day of the week for recurring availability.',
            'specific_date.required_if' => 'Please select a specific date.',
            'end_time.after' => 'The end time must be after the start time.',
            'effective_until.after_or_equal' => 'The effective until date must be on or after the effective from date.',
        ];
    }
}
