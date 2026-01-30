<?php

namespace Database\Factories;

use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StaffingRequirement>
 */
class StaffingRequirementFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->numberBetween(6, 14);
        $minEmployees = fake()->numberBetween(1, 5);
        $hasMax = fake()->boolean(70);

        return [
            'tenant_id' => Tenant::factory(),
            'location_id' => null,
            'department_id' => null,
            'business_role_id' => BusinessRole::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:00', $startHour + fake()->randomElement([4, 6, 8])),
            'min_employees' => $minEmployees,
            'max_employees' => $hasMax ? $minEmployees + fake()->numberBetween(1, 5) : null,
            'is_active' => true,
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function forLocation(Location $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location_id' => $location->id,
        ]);
    }

    public function forDepartment(Department $department): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => $department->id,
        ]);
    }

    public function forBusinessRole(BusinessRole $businessRole): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $businessRole->tenant_id,
            'business_role_id' => $businessRole->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function weekday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => fake()->numberBetween(1, 5), // Mon-Fri
        ]);
    }

    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => fake()->randomElement([0, 6]), // Sun, Sat
        ]);
    }

    public function forDayOfWeek(int $dayOfWeek): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $dayOfWeek,
        ]);
    }

    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '06:00',
            'end_time' => '14:00',
        ]);
    }

    public function afternoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '14:00',
            'end_time' => '22:00',
        ]);
    }

    public function withMinMax(int $min, ?int $max = null): static
    {
        return $this->state(fn (array $attributes) => [
            'min_employees' => $min,
            'max_employees' => $max,
        ]);
    }
}
