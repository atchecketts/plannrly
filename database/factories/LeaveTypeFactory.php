<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['Annual Leave', 'Sick Leave', 'Personal Leave', 'Bereavement']),
            'color' => fake()->hexColor(),
            'requires_approval' => true,
            'affects_allowance' => true,
            'is_paid' => true,
            'is_active' => true,
        ];
    }

    public function systemDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => null,
        ]);
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Annual Leave',
            'color' => '#3B82F6',
            'requires_approval' => true,
            'affects_allowance' => true,
            'is_paid' => true,
        ]);
    }

    public function sick(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sick Leave',
            'color' => '#EF4444',
            'requires_approval' => true,
            'affects_allowance' => false,
            'is_paid' => true,
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Unpaid Leave',
            'color' => '#6B7280',
            'requires_approval' => true,
            'affects_allowance' => false,
            'is_paid' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
