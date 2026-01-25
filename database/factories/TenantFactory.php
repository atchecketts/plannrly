<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'logo_path' => null,
            'settings' => [
                'timezone' => fake()->timezone(),
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
            ],
            'is_active' => true,
            'trial_ends_at' => null,
        ];
    }

    public function onTrial(int $days = 14): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function plannrly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Plannrly',
            'slug' => 'plannrly',
            'email' => 'admin@plannrly.com',
            'is_active' => true,
        ]);
    }
}
