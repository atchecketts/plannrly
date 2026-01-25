<?php

namespace App\Http\Requests\User;

use App\Enums\SystemRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $targetUser = $this->route('user');

        if ($user->id === $targetUser->id) {
            return true;
        }

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
        $targetUser = $this->route('user');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')
                    ->where(fn ($query) => $query->where('tenant_id', $this->user()->tenant_id))
                    ->ignore($targetUser->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
            'system_role' => ['sometimes', Rule::enum(SystemRole::class)],
            'location_id' => ['nullable', 'exists:locations,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'business_role_ids' => ['nullable', 'array'],
            'business_role_ids.*' => ['exists:business_roles,id'],
            'primary_business_role_id' => ['nullable', 'exists:business_roles,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter a first name.',
            'last_name.required' => 'Please enter a last name.',
            'email.required' => 'Please enter an email address.',
            'email.unique' => 'This email address is already in use.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
