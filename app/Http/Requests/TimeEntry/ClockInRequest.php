<?php

namespace App\Http\Requests\TimeEntry;

use App\Models\Shift;
use App\Models\TenantSettings;
use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'location' => ['nullable', 'array'],
            'location.lat' => ['required_with:location', 'numeric', 'between:-90,90'],
            'location.lng' => ['required_with:location', 'numeric', 'between:-180,180'],
        ];

        $settings = TenantSettings::where('tenant_id', $this->user()->tenant_id)->first();

        if ($settings?->require_gps_clock_in) {
            $rules['location'] = ['required', 'array'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shift_id.required' => 'Please select a shift to clock in to.',
            'shift_id.exists' => 'The selected shift does not exist.',
            'location.required' => 'GPS location is required to clock in.',
            'location.lat.required_with' => 'Latitude is required when providing location.',
            'location.lat.between' => 'Latitude must be between -90 and 90.',
            'location.lng.required_with' => 'Longitude is required when providing location.',
            'location.lng.between' => 'Longitude must be between -180 and 180.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $shift = Shift::find($this->input('shift_id'));

            if (! $shift) {
                return;
            }

            if ($shift->user_id !== $this->user()->id) {
                $validator->errors()->add('shift_id', 'You are not assigned to this shift.');
            }
        });
    }
}
