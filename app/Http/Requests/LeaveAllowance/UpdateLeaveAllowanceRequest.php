<?php

namespace App\Http\Requests\LeaveAllowance;

use App\Models\LeaveAllowance;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveAllowanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $leaveAllowance = $this->route('leave_allowance');

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $leaveAllowance instanceof LeaveAllowance) {
            return false;
        }

        return $user->isAdmin() && $user->tenant_id === $leaveAllowance->tenant_id;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'total_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'carried_over_days' => ['nullable', 'numeric', 'min:0', 'max:365'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'total_days.required' => 'Please enter the total days allowance.',
            'total_days.numeric' => 'The total days must be a number.',
            'total_days.min' => 'The total days cannot be negative.',
            'carried_over_days.numeric' => 'The carried over days must be a number.',
            'carried_over_days.min' => 'The carried over days cannot be negative.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'carried_over_days' => $this->input('carried_over_days', 0),
        ]);
    }
}
