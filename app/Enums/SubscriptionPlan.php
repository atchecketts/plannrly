<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case Basic = 'basic';
    case Professional = 'professional';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Basic',
            self::Professional => 'Professional',
            self::Enterprise => 'Enterprise',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Basic => 'Essential features for small teams',
            self::Professional => 'Advanced features for growing businesses',
            self::Enterprise => 'Full feature set for large organizations',
        };
    }

    /**
     * @return array<FeatureAddon>
     */
    public function includedFeatures(): array
    {
        return match ($this) {
            self::Basic => [],
            self::Professional => [
                FeatureAddon::AiScheduling,
                FeatureAddon::AdvancedAnalytics,
            ],
            self::Enterprise => [
                FeatureAddon::AiScheduling,
                FeatureAddon::AdvancedAnalytics,
                FeatureAddon::ApiAccess,
                FeatureAddon::PrioritySupport,
                FeatureAddon::TimeAttendance,
            ],
        };
    }

    public function hasFeature(FeatureAddon $feature): bool
    {
        return in_array($feature, $this->includedFeatures(), true);
    }

    /**
     * Get addons that can be purchased for this plan (excludes already included features).
     *
     * @return array<FeatureAddon>
     */
    public function purchasableAddons(): array
    {
        return match ($this) {
            self::Basic => [
                FeatureAddon::AiScheduling,
                FeatureAddon::AdvancedAnalytics,
            ],
            self::Professional => [
                FeatureAddon::ApiAccess,
                FeatureAddon::PrioritySupport,
                FeatureAddon::TimeAttendance,
            ],
            self::Enterprise => [],
        };
    }

    public function canPurchaseAddon(FeatureAddon $addon): bool
    {
        return in_array($addon, $this->purchasableAddons(), true);
    }

    public function monthlyPrice(): int
    {
        return match ($this) {
            self::Basic => 0,
            self::Professional => 49,
            self::Enterprise => 149,
        };
    }

    public function annualPrice(): int
    {
        return match ($this) {
            self::Basic => 0,
            self::Professional => 490,
            self::Enterprise => 1490,
        };
    }

    /**
     * Get the order/hierarchy of plans (higher = more features).
     */
    public function order(): int
    {
        return match ($this) {
            self::Basic => 1,
            self::Professional => 2,
            self::Enterprise => 3,
        };
    }

    public function isHigherThan(self $other): bool
    {
        return $this->order() > $other->order();
    }

    public function isLowerThan(self $other): bool
    {
        return $this->order() < $other->order();
    }
}
