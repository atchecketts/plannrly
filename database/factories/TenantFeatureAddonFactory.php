<?php

namespace Database\Factories;

use App\Enums\FeatureAddon;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantFeatureAddon>
 */
class TenantFeatureAddonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'feature' => fake()->randomElement(FeatureAddon::cases()),
            'enabled_at' => now(),
            'expires_at' => null,
            'stripe_subscription_item_id' => null,
        ];
    }

    public function aiScheduling(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature' => FeatureAddon::AiScheduling,
        ]);
    }

    public function advancedAnalytics(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature' => FeatureAddon::AdvancedAnalytics,
        ]);
    }

    public function apiAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature' => FeatureAddon::ApiAccess,
        ]);
    }

    public function prioritySupport(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature' => FeatureAddon::PrioritySupport,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function expiresIn(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays($days),
        ]);
    }
}
