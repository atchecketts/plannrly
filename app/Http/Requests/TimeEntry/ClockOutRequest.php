<?php

namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;

class ClockOutRequest extends FormRequest
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
        return [
            'location' => ['nullable', 'array'],
            'location.lat' => ['required_with:location', 'numeric', 'between:-90,90'],
            'location.lng' => ['required_with:location', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location.lat.required_with' => 'Latitude is required when providing location.',
            'location.lat.between' => 'Latitude must be between -90 and 90.',
            'location.lng.required_with' => 'Longitude is required when providing location.',
            'location.lng.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}
