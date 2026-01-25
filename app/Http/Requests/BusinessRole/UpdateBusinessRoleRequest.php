<?php

namespace App\Http\Requests\BusinessRole;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $businessRole = $this->route('businessRole');

        return $this->user()->canManageDepartment($businessRole->department);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'default_hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a role name.',
            'color.required' => 'Please select a color.',
            'color.regex' => 'Please enter a valid hex color code.',
            'default_hourly_rate.numeric' => 'The hourly rate must be a number.',
            'default_hourly_rate.min' => 'The hourly rate cannot be negative.',
        ];
    }
}
