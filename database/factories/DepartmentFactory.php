<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'location_id' => Location::factory(),
            'name' => fake()->randomElement([
                'Front Desk', 'Kitchen', 'Sales', 'Support', 'Warehouse',
                'Customer Service', 'Administration', 'HR', 'IT', 'Marketing',
            ]),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forLocation(Location $location): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $location->tenant_id,
            'location_id' => $location->id,
        ]);
    }
}
