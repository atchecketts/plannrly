<?php

namespace App\Http\Requests\Employment;

use App\Enums\EmploymentStatus;
use App\Enums\PayType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmploymentDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $targetUser = $this->route('user');

        return $user->canManageUser($targetUser);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employment_start_date' => ['nullable', 'date'],
            'employment_end_date' => ['nullable', 'date', 'after_or_equal:employment_start_date'],
            'final_working_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date'],
            'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
            'pay_type' => ['required', Rule::enum(PayType::class)],
            'base_hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'annual_salary' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'target_hours_per_week' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'min_hours_per_week' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'max_hours_per_week' => ['nullable', 'numeric', 'min:0', 'max:168', 'gte:min_hours_per_week'],
            'overtime_eligible' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employment_end_date.after_or_equal' => 'The employment end date must be on or after the start date.',
            'max_hours_per_week.gte' => 'The maximum hours must be greater than or equal to the minimum hours.',
            'currency.size' => 'The currency code must be exactly 3 characters (e.g., GBP, USD, EUR).',
        ];
    }
}
