<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        $locationId = $this->input('location_id');

        return $locationId && $user->isLocationAdmin($locationId);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location_id.required' => 'Please select a location.',
            'location_id.exists' => 'The selected location does not exist.',
            'name.required' => 'Please enter a department name.',
            'color.required' => 'Please select a color.',
            'color.regex' => 'Please enter a valid hex color code.',
        ];
    }
}
