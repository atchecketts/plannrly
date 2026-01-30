<?php

namespace App\Http\Requests\LeaveType;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'requires_approval' => ['boolean'],
            'affects_allowance' => ['boolean'],
            'is_paid' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a leave type name.',
            'name.max' => 'The leave type name cannot exceed 255 characters.',
            'color.required' => 'Please select a color.',
            'color.regex' => 'Please enter a valid hex color code (e.g., #FF5733).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_approval' => $this->boolean('requires_approval'),
            'affects_allowance' => $this->boolean('affects_allowance'),
            'is_paid' => $this->boolean('is_paid'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
