<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\FeatureAddon;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use App\Models\TenantFeatureAddon;
use App\Models\TenantSubscription;
use Illuminate\Support\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription for a tenant (typically called on tenant creation).
     */
    public function createSubscription(
        Tenant $tenant,
        SubscriptionPlan $plan = SubscriptionPlan::Basic,
        SubscriptionStatus $status = SubscriptionStatus::Trialing,
        BillingCycle $billingCycle = BillingCycle::Monthly
    ): TenantSubscription {
        return TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan' => $plan,
            'status' => $status,
            'billing_cycle' => $billingCycle,
            'current_period_start' => now(),
            'current_period_end' => $this->calculatePeriodEnd($billingCycle),
        ]);
    }

    /**
     * Change the tenant's subscription plan.
     */
    public function changePlan(Tenant $tenant, SubscriptionPlan $newPlan): TenantSubscription
    {
        $subscription = $tenant->subscription;

        if (! $subscription) {
            return $this->createSubscription($tenant, $newPlan, SubscriptionStatus::Active);
        }

        $subscription->update([
            'plan' => $newPlan,
        ]);

        return $subscription->fresh();
    }

    /**
     * Activate a subscription (e.g., after trial ends or payment processed).
     */
    public function activate(Tenant $tenant): TenantSubscription
    {
        $subscription = $tenant->subscription;

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now(),
            'current_period_end' => $this->calculatePeriodEnd($subscription->billing_cycle),
        ]);

        return $subscription->fresh();
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Tenant $tenant): TenantSubscription
    {
        $subscription = $tenant->subscription;

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return $subscription->fresh();
    }

    /**
     * Mark subscription as past due.
     */
    public function markPastDue(Tenant $tenant): TenantSubscription
    {
        $subscription = $tenant->subscription;

        $subscription->update([
            'status' => SubscriptionStatus::PastDue,
        ]);

        return $subscription->fresh();
    }

    /**
     * Add a feature add-on to a tenant.
     */
    public function addFeatureAddon(
        Tenant $tenant,
        FeatureAddon $feature,
        ?Carbon $expiresAt = null
    ): TenantFeatureAddon {
        return TenantFeatureAddon::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'feature' => $feature,
            ],
            [
                'enabled_at' => now(),
                'expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Remove a feature add-on from a tenant.
     */
    public function removeFeatureAddon(Tenant $tenant, FeatureAddon $feature): bool
    {
        return $tenant->featureAddons()
            ->where('feature', $feature)
            ->delete() > 0;
    }

    /**
     * Get all features available to a tenant (plan features + add-ons).
     *
     * @return array<FeatureAddon>
     */
    public function getAvailableFeatures(Tenant $tenant): array
    {
        $features = [];

        // Add plan features
        if ($tenant->subscription?->isAccessible()) {
            $features = $tenant->subscription->plan->includedFeatures();
        }

        // Add active add-on features
        $addonFeatures = $tenant->activeFeatureAddons()
            ->pluck('feature')
            ->all();

        return array_unique(array_merge($features, $addonFeatures));
    }

    /**
     * Get feature status for a tenant (for API responses).
     *
     * @return array<string, array{enabled: bool, source: string|null}>
     */
    public function getFeatureStatus(Tenant $tenant): array
    {
        $status = [];
        $subscription = $tenant->subscription;
        $planFeatures = $subscription?->isAccessible()
            ? $subscription->plan->includedFeatures()
            : [];

        foreach (FeatureAddon::cases() as $feature) {
            $inPlan = in_array($feature, $planFeatures, true);
            $addon = $tenant->activeFeatureAddons()
                ->where('feature', $feature)
                ->first();

            $enabled = $inPlan || $addon !== null;

            $status[$feature->value] = [
                'enabled' => $enabled,
                'source' => $enabled
                    ? ($inPlan ? 'plan' : 'addon')
                    : null,
                'expires_at' => $addon?->expires_at?->toIso8601String(),
            ];
        }

        return $status;
    }

    /**
     * Calculate the period end based on billing cycle.
     */
    protected function calculatePeriodEnd(BillingCycle $cycle): Carbon
    {
        return match ($cycle) {
            BillingCycle::Monthly => now()->addMonth(),
            BillingCycle::Annual => now()->addYear(),
        };
    }
}
