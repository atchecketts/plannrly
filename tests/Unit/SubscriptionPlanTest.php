<?php

namespace Tests\Unit;

use App\Enums\BillingCycle;
use App\Enums\FeatureAddon;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use PHPUnit\Framework\TestCase;

class SubscriptionPlanTest extends TestCase
{
    public function test_basic_plan_includes_no_features(): void
    {
        $plan = SubscriptionPlan::Basic;

        $this->assertEmpty($plan->includedFeatures());
        $this->assertFalse($plan->hasFeature(FeatureAddon::AiScheduling));
        $this->assertFalse($plan->hasFeature(FeatureAddon::AdvancedAnalytics));
        $this->assertFalse($plan->hasFeature(FeatureAddon::ApiAccess));
        $this->assertFalse($plan->hasFeature(FeatureAddon::PrioritySupport));
        $this->assertFalse($plan->hasFeature(FeatureAddon::TimeAttendance));
    }

    public function test_professional_plan_includes_ai_and_analytics(): void
    {
        $plan = SubscriptionPlan::Professional;

        $features = $plan->includedFeatures();
        $this->assertCount(2, $features);
        $this->assertTrue($plan->hasFeature(FeatureAddon::AiScheduling));
        $this->assertTrue($plan->hasFeature(FeatureAddon::AdvancedAnalytics));
        $this->assertFalse($plan->hasFeature(FeatureAddon::ApiAccess));
        $this->assertFalse($plan->hasFeature(FeatureAddon::PrioritySupport));
        $this->assertFalse($plan->hasFeature(FeatureAddon::TimeAttendance));
    }

    public function test_enterprise_plan_includes_all_features(): void
    {
        $plan = SubscriptionPlan::Enterprise;

        $features = $plan->includedFeatures();
        $this->assertCount(5, $features);
        $this->assertTrue($plan->hasFeature(FeatureAddon::AiScheduling));
        $this->assertTrue($plan->hasFeature(FeatureAddon::AdvancedAnalytics));
        $this->assertTrue($plan->hasFeature(FeatureAddon::ApiAccess));
        $this->assertTrue($plan->hasFeature(FeatureAddon::PrioritySupport));
        $this->assertTrue($plan->hasFeature(FeatureAddon::TimeAttendance));
    }

    public function test_plan_hierarchy_ordering(): void
    {
        $basic = SubscriptionPlan::Basic;
        $professional = SubscriptionPlan::Professional;
        $enterprise = SubscriptionPlan::Enterprise;

        $this->assertEquals(1, $basic->order());
        $this->assertEquals(2, $professional->order());
        $this->assertEquals(3, $enterprise->order());

        $this->assertTrue($professional->isHigherThan($basic));
        $this->assertTrue($enterprise->isHigherThan($professional));
        $this->assertTrue($enterprise->isHigherThan($basic));

        $this->assertTrue($basic->isLowerThan($professional));
        $this->assertTrue($professional->isLowerThan($enterprise));
        $this->assertTrue($basic->isLowerThan($enterprise));
    }

    public function test_plan_labels_and_descriptions(): void
    {
        $this->assertEquals('Basic', SubscriptionPlan::Basic->label());
        $this->assertEquals('Professional', SubscriptionPlan::Professional->label());
        $this->assertEquals('Enterprise', SubscriptionPlan::Enterprise->label());

        $this->assertNotEmpty(SubscriptionPlan::Basic->description());
        $this->assertNotEmpty(SubscriptionPlan::Professional->description());
        $this->assertNotEmpty(SubscriptionPlan::Enterprise->description());
    }

    public function test_plan_pricing(): void
    {
        $this->assertEquals(0, SubscriptionPlan::Basic->monthlyPrice());
        $this->assertEquals(49, SubscriptionPlan::Professional->monthlyPrice());
        $this->assertEquals(149, SubscriptionPlan::Enterprise->monthlyPrice());

        $this->assertEquals(0, SubscriptionPlan::Basic->annualPrice());
        $this->assertEquals(490, SubscriptionPlan::Professional->annualPrice());
        $this->assertEquals(1490, SubscriptionPlan::Enterprise->annualPrice());
    }

    public function test_subscription_status_accessibility(): void
    {
        $this->assertTrue(SubscriptionStatus::Active->isAccessible());
        $this->assertTrue(SubscriptionStatus::PastDue->isAccessible());
        $this->assertTrue(SubscriptionStatus::Trialing->isAccessible());
        $this->assertFalse(SubscriptionStatus::Cancelled->isAccessible());
    }

    public function test_subscription_status_labels(): void
    {
        $this->assertEquals('Active', SubscriptionStatus::Active->label());
        $this->assertEquals('Past Due', SubscriptionStatus::PastDue->label());
        $this->assertEquals('Cancelled', SubscriptionStatus::Cancelled->label());
        $this->assertEquals('Trialing', SubscriptionStatus::Trialing->label());
    }

    public function test_billing_cycle_labels(): void
    {
        $this->assertEquals('Monthly', BillingCycle::Monthly->label());
        $this->assertEquals('Annual', BillingCycle::Annual->label());
    }

    public function test_billing_cycle_intervals(): void
    {
        $this->assertEquals(30, BillingCycle::Monthly->intervalInDays());
        $this->assertEquals(365, BillingCycle::Annual->intervalInDays());
    }

    public function test_feature_addon_labels_and_descriptions(): void
    {
        $this->assertEquals('AI Scheduling', FeatureAddon::AiScheduling->label());
        $this->assertEquals('Advanced Analytics', FeatureAddon::AdvancedAnalytics->label());
        $this->assertEquals('API Access', FeatureAddon::ApiAccess->label());
        $this->assertEquals('Priority Support', FeatureAddon::PrioritySupport->label());
        $this->assertEquals('Time & Attendance', FeatureAddon::TimeAttendance->label());

        $this->assertNotEmpty(FeatureAddon::AiScheduling->description());
        $this->assertNotEmpty(FeatureAddon::AdvancedAnalytics->description());
        $this->assertNotEmpty(FeatureAddon::ApiAccess->description());
        $this->assertNotEmpty(FeatureAddon::PrioritySupport->description());
        $this->assertNotEmpty(FeatureAddon::TimeAttendance->description());
    }

    public function test_feature_addon_pricing(): void
    {
        $this->assertEquals(19, FeatureAddon::AiScheduling->monthlyPrice());
        $this->assertEquals(14, FeatureAddon::AdvancedAnalytics->monthlyPrice());
        $this->assertEquals(29, FeatureAddon::ApiAccess->monthlyPrice());
        $this->assertEquals(49, FeatureAddon::PrioritySupport->monthlyPrice());
        $this->assertEquals(12, FeatureAddon::TimeAttendance->monthlyPrice());
    }

    public function test_basic_plan_purchasable_addons(): void
    {
        $plan = SubscriptionPlan::Basic;
        $addons = $plan->purchasableAddons();

        $this->assertCount(2, $addons);
        $this->assertTrue($plan->canPurchaseAddon(FeatureAddon::AiScheduling));
        $this->assertTrue($plan->canPurchaseAddon(FeatureAddon::AdvancedAnalytics));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::ApiAccess));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::PrioritySupport));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::TimeAttendance));
    }

    public function test_professional_plan_purchasable_addons(): void
    {
        $plan = SubscriptionPlan::Professional;
        $addons = $plan->purchasableAddons();

        $this->assertCount(3, $addons);
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::AiScheduling));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::AdvancedAnalytics));
        $this->assertTrue($plan->canPurchaseAddon(FeatureAddon::ApiAccess));
        $this->assertTrue($plan->canPurchaseAddon(FeatureAddon::PrioritySupport));
        $this->assertTrue($plan->canPurchaseAddon(FeatureAddon::TimeAttendance));
    }

    public function test_enterprise_plan_has_no_purchasable_addons(): void
    {
        $plan = SubscriptionPlan::Enterprise;
        $addons = $plan->purchasableAddons();

        $this->assertEmpty($addons);
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::AiScheduling));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::AdvancedAnalytics));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::ApiAccess));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::PrioritySupport));
        $this->assertFalse($plan->canPurchaseAddon(FeatureAddon::TimeAttendance));
    }
}
