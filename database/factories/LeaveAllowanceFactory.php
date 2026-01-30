<?php

namespace Database\Factories;

use App\Models\LeaveType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveAllowance>
 */
class LeaveAllowanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'year' => now()->year,
            'total_days' => fake()->randomElement([20, 25, 28, 30]),
            'used_days' => 0,
            'carried_over_days' => 0,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
        ]);
    }

    public function forLeaveType(LeaveType $leaveType): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type_id' => $leaveType->id,
        ]);
    }

    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
        ]);
    }

    public function withUsedDays(float $days): static
    {
        return $this->state(fn (array $attributes) => [
            'used_days' => $days,
        ]);
    }

    public function withCarriedOver(float $days): static
    {
        return $this->state(fn (array $attributes) => [
            'carried_over_days' => $days,
        ]);
    }
}
