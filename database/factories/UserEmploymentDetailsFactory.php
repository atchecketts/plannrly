<?php

namespace Database\Factories;

use App\Enums\EmploymentStatus;
use App\Enums\PayType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserEmploymentDetails>
 */
class UserEmploymentDetailsFactory extends Factory
{
    public function definition(): array
    {
        $payType = fake()->randomElement(PayType::cases());

        return [
            'user_id' => User::factory(),
            'employment_start_date' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'employment_end_date' => null,
            'final_working_date' => null,
            'probation_end_date' => null,
            'employment_status' => EmploymentStatus::Active,
            'pay_type' => $payType,
            'base_hourly_rate' => $payType === PayType::Hourly ? fake()->randomFloat(2, 10, 50) : null,
            'annual_salary' => $payType === PayType::Salaried ? fake()->randomFloat(2, 25000, 100000) : null,
            'currency' => 'GBP',
            'target_hours_per_week' => fake()->randomFloat(2, 20, 40),
            'min_hours_per_week' => fake()->randomFloat(2, 10, 20),
            'max_hours_per_week' => fake()->randomFloat(2, 40, 48),
            'overtime_eligible' => fake()->boolean(30),
            'notes' => null,
        ];
    }

    public function hourly(): static
    {
        return $this->state(fn (array $attributes) => [
            'pay_type' => PayType::Hourly,
            'base_hourly_rate' => fake()->randomFloat(2, 10, 50),
            'annual_salary' => null,
        ]);
    }

    public function salaried(): static
    {
        return $this->state(fn (array $attributes) => [
            'pay_type' => PayType::Salaried,
            'base_hourly_rate' => null,
            'annual_salary' => fake()->randomFloat(2, 25000, 100000),
        ]);
    }

    public function onProbation(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_start_date' => now()->subMonths(2),
            'probation_end_date' => now()->addMonth(),
        ]);
    }

    public function onNoticePeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => EmploymentStatus::NoticePeriod,
            'final_working_date' => now()->addMonth(),
        ]);
    }

    public function leavingSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => EmploymentStatus::NoticePeriod,
            'final_working_date' => now()->addWeeks(2),
        ]);
    }

    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => EmploymentStatus::Terminated,
            'final_working_date' => now()->subWeek(),
        ]);
    }

    public function onLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => EmploymentStatus::OnLeave,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => EmploymentStatus::Suspended,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function overtimeEligible(): static
    {
        return $this->state(fn (array $attributes) => [
            'overtime_eligible' => true,
        ]);
    }
}
