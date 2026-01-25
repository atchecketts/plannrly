<?php

namespace Database\Factories;

use App\Enums\RotaStatus;
use App\Models\Department;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rota>
 */
class RotaFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 week', '+1 week');
        $endDate = (clone $startDate)->modify('+6 days');

        return [
            'tenant_id' => Tenant::factory(),
            'location_id' => null,
            'department_id' => null,
            'name' => 'Week of ' . $startDate->format('M d, Y'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => RotaStatus::Draft,
            'published_at' => null,
            'published_by' => null,
            'created_by' => null,
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
            'tenant_id' => $location->tenant_id,
            'location_id' => $location->id,
        ]);
    }

    public function forDepartment(Department $department): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $department->tenant_id,
            'location_id' => $department->location_id,
            'department_id' => $department->id,
        ]);
    }

    public function published(User $publishedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RotaStatus::Published,
            'published_at' => now(),
            'published_by' => $publishedBy?->id,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RotaStatus::Archived,
        ]);
    }

    public function forDateRange($startDate, $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'name' => 'Week of ' . $startDate->format('M d, Y'),
        ]);
    }
}
