<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewLeaveRequestRequest extends FormRequest
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
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Please select an action.',
            'action.in' => 'Invalid action selected.',
        ];
    }

    public function isApproval(): bool
    {
        return $this->input('action') === 'approve';
    }

    public function isRejection(): bool
    {
        return $this->input('action') === 'reject';
    }
}
