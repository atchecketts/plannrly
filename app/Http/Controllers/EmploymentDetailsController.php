<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Enums\PayType;
use App\Http\Requests\Employment\UpdateEmploymentDetailsRequest;
use App\Models\User;
use App\Models\UserBusinessRole;
use App\Models\UserEmploymentDetails;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmploymentDetailsController extends Controller
{
    public function edit(User $user): View
    {
        $this->authorize('update', $user->employmentDetails ?? new UserEmploymentDetails(['user_id' => $user->id]));

        $user->load(['employmentDetails', 'userBusinessRoles.businessRole']);

        $employmentStatuses = EmploymentStatus::cases();
        $payTypes = PayType::cases();

        return view('users.employment', compact('user', 'employmentStatuses', 'payTypes'));
    }

    public function update(UpdateEmploymentDetailsRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        UserEmploymentDetails::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employment_start_date' => $data['employment_start_date'] ?? null,
                'employment_end_date' => $data['employment_end_date'] ?? null,
                'final_working_date' => $data['final_working_date'] ?? null,
                'probation_end_date' => $data['probation_end_date'] ?? null,
                'employment_status' => $data['employment_status'],
                'pay_type' => $data['pay_type'],
                'base_hourly_rate' => $data['base_hourly_rate'] ?? null,
                'annual_salary' => $data['annual_salary'] ?? null,
                'currency' => $data['currency'],
                'target_hours_per_week' => $data['target_hours_per_week'] ?? null,
                'min_hours_per_week' => $data['min_hours_per_week'] ?? null,
                'max_hours_per_week' => $data['max_hours_per_week'] ?? null,
                'overtime_eligible' => $data['overtime_eligible'] ?? false,
                'notes' => $data['notes'] ?? null,
            ]
        );

        if (! empty($data['role_rates'])) {
            foreach ($data['role_rates'] as $roleId => $rate) {
                UserBusinessRole::query()
                    ->where('user_id', $user->id)
                    ->where('business_role_id', $roleId)
                    ->update(['hourly_rate' => $rate !== '' ? $rate : null]);
            }
        }

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Employment details updated successfully.');
    }
}
