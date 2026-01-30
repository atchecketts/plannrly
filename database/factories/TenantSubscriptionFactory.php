<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantSubscription>
 */
class TenantSubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan' => SubscriptionPlan::Basic,
            'status' => SubscriptionStatus::Trialing,
            'billing_cycle' => BillingCycle::Monthly,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
            'cancelled_at' => null,
            'stripe_subscription_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Active,
        ]);
    }

    public function trialing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Trialing,
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::PastDue,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => SubscriptionPlan::Basic,
        ]);
    }

    public function professional(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => SubscriptionPlan::Professional,
        ]);
    }

    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => SubscriptionPlan::Enterprise,
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => BillingCycle::Annual,
            'current_period_end' => now()->addYear(),
        ]);
    }
}
