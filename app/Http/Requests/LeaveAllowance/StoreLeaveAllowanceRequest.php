<?php

namespace App\Http\Requests\LeaveAllowance;

use App\Models\LeaveAllowance;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveAllowanceRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
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
            'user_id.required' => 'Please select an employee.',
            'user_id.exists' => 'The selected employee does not exist.',
            'leave_type_id.required' => 'Please select a leave type.',
            'leave_type_id.exists' => 'The selected leave type does not exist.',
            'year.required' => 'Please select a year.',
            'year.integer' => 'The year must be a valid number.',
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->input('user_id');
            $leaveTypeId = $this->input('leave_type_id');
            $year = $this->input('year');
            $tenantId = $this->user()->tenant_id;

            $exists = LeaveAllowance::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'user_id',
                    'This employee already has an allowance for this leave type and year.'
                );
            }
        });
    }
}
