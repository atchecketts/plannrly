<?php

namespace App\Http\Requests\TenantSettings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantSettingsRequest extends FormRequest
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
            'enable_clock_in_out' => ['boolean'],
            'enable_shift_acknowledgement' => ['boolean'],
            'day_starts_at' => ['required', 'date_format:H:i'],
            'day_ends_at' => ['required', 'date_format:H:i'],
            'week_starts_on' => ['required', 'integer', 'min:0', 'max:6'],
            'timezone' => ['required', 'string', 'timezone'],
            'date_format' => ['required', 'string', Rule::in(['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'd.m.Y'])],
            'time_format' => ['required', 'string', Rule::in(['H:i', 'h:i A', 'g:i a'])],
            'missed_grace_minutes' => ['required', 'integer', 'min:0', 'max:60'],
            'notify_on_publish' => ['boolean'],
            'leave_carryover_mode' => ['required', 'string', Rule::in(['none', 'partial', 'full'])],
            'default_currency' => ['required', 'string', 'size:3'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'clock_in_grace_minutes' => ['nullable', 'integer', 'min:0', 'max:60'],
            'require_gps_clock_in' => ['boolean'],
            'auto_clock_out_enabled' => ['boolean'],
            'auto_clock_out_time' => ['nullable', 'date_format:H:i'],
            'overtime_threshold_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'require_manager_approval' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'day_starts_at.required' => 'Please specify when the day starts.',
            'day_starts_at.date_format' => 'The day start time must be in HH:MM format.',
            'day_ends_at.required' => 'Please specify when the day ends.',
            'day_ends_at.date_format' => 'The day end time must be in HH:MM format.',
            'timezone.required' => 'Please select a timezone.',
            'timezone.timezone' => 'Please select a valid timezone.',
            'leave_carryover_mode.required' => 'Please select a leave carryover mode.',
            'leave_carryover_mode.in' => 'Invalid leave carryover mode selected.',
            'default_currency.required' => 'Please select a default currency.',
            'default_currency.size' => 'The currency code must be exactly 3 characters.',
            'primary_color.required' => 'Please select a primary color.',
            'primary_color.regex' => 'Please enter a valid hex color code (e.g., #6366f1).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enable_clock_in_out' => $this->boolean('enable_clock_in_out'),
            'enable_shift_acknowledgement' => $this->boolean('enable_shift_acknowledgement'),
            'notify_on_publish' => $this->boolean('notify_on_publish'),
            'require_gps_clock_in' => $this->boolean('require_gps_clock_in'),
            'auto_clock_out_enabled' => $this->boolean('auto_clock_out_enabled'),
            'require_manager_approval' => $this->boolean('require_manager_approval'),
        ]);
    }
}
