<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessRole>
 */
class BusinessRoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'department_id' => Department::factory(),
            'name' => fake()->randomElement([
                'Cashier', 'Supervisor', 'Manager', 'Team Lead', 'Associate',
                'Senior Associate', 'Specialist', 'Coordinator', 'Analyst', 'Technician',
            ]),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->hexColor(),
            'default_hourly_rate' => fake()->randomFloat(2, 10, 50),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forDepartment(Department $department): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $department->tenant_id,
            'department_id' => $department->id,
        ]);
    }

    public function withRate(float $rate): static
    {
        return $this->state(fn (array $attributes) => [
            'default_hourly_rate' => $rate,
        ]);
    }
}
