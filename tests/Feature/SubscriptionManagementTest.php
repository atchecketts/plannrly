<?php

namespace Tests\Feature;

use App\Enums\FeatureAddon;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\TenantFeatureAddon;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);
    }

    public function test_subscription_is_created_on_tenant_creation(): void
    {
        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan' => 'basic',
            'status' => 'trialing',
        ]);
    }

    public function test_tenant_has_subscription_relationship(): void
    {
        $subscription = $this->tenant->subscription;

        $this->assertNotNull($subscription);
        $this->assertInstanceOf(TenantSubscription::class, $subscription);
        $this->assertEquals(SubscriptionPlan::Basic, $subscription->plan);
        $this->assertEquals(SubscriptionStatus::Trialing, $subscription->status);
    }

    public function test_admin_can_view_subscription_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('subscription.index'));

        $response->assertOk();
        $response->assertViewIs('subscription.index');
        $response->assertViewHas('subscription');
        $response->assertViewHas('plans');
        $response->assertViewHas('features');
    }

    public function test_employee_cannot_view_subscription_page(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('subscription.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_upgrade_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('subscription.upgrade'));

        $response->assertOk();
        $response->assertViewIs('subscription.upgrade');
        $response->assertViewHas('currentPlan');
        $response->assertViewHas('plans');
        $response->assertViewHas('features');
    }

    public function test_employee_cannot_view_upgrade_page(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('subscription.upgrade'));

        $response->assertStatus(403);
    }

    public function test_basic_plan_tenant_does_not_have_premium_features(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Basic]);

        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::AiScheduling));
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::AdvancedAnalytics));
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::ApiAccess));
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::PrioritySupport));
    }

    public function test_professional_plan_tenant_has_ai_and_analytics(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Professional]);
        $this->tenant->refresh();

        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AiScheduling));
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AdvancedAnalytics));
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::ApiAccess));
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::PrioritySupport));
    }

    public function test_enterprise_plan_tenant_has_all_features(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Enterprise]);
        $this->tenant->refresh();

        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AiScheduling));
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AdvancedAnalytics));
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::ApiAccess));
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::PrioritySupport));
    }

    public function test_addon_feature_grants_access(): void
    {
        // Basic plan with no features
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Basic]);
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::ApiAccess));

        // Add the API Access addon
        TenantFeatureAddon::factory()
            ->apiAccess()
            ->create(['tenant_id' => $this->tenant->id]);

        $this->tenant->refresh();
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::ApiAccess));
    }

    public function test_expired_addon_does_not_grant_access(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Basic]);

        TenantFeatureAddon::factory()
            ->apiAccess()
            ->expired()
            ->create(['tenant_id' => $this->tenant->id]);

        $this->tenant->refresh();
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::ApiAccess));
    }

    public function test_cancelled_subscription_does_not_grant_plan_features(): void
    {
        $this->tenant->subscription->update([
            'plan' => SubscriptionPlan::Enterprise,
            'status' => SubscriptionStatus::Cancelled,
        ]);
        $this->tenant->refresh();

        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::AiScheduling));
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::AdvancedAnalytics));
    }

    public function test_past_due_subscription_still_grants_features(): void
    {
        $this->tenant->subscription->update([
            'plan' => SubscriptionPlan::Professional,
            'status' => SubscriptionStatus::PastDue,
        ]);
        $this->tenant->refresh();

        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AiScheduling));
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AdvancedAnalytics));
    }

    public function test_trialing_subscription_grants_features(): void
    {
        $this->tenant->subscription->update([
            'plan' => SubscriptionPlan::Professional,
            'status' => SubscriptionStatus::Trialing,
        ]);
        $this->tenant->refresh();

        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AiScheduling));
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::AdvancedAnalytics));
    }

    public function test_feature_api_returns_status(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Professional]);
        $this->actingAs($this->admin);

        $response = $this->getJson(route('api.features.status'));

        $response->assertOk();
        $response->assertJsonStructure([
            'plan',
            'status',
            'features' => [
                'ai_scheduling' => ['enabled', 'source'],
                'advanced_analytics' => ['enabled', 'source'],
                'api_access' => ['enabled', 'source'],
                'priority_support' => ['enabled', 'source'],
            ],
        ]);

        $data = $response->json();
        $this->assertTrue($data['features']['ai_scheduling']['enabled']);
        $this->assertEquals('plan', $data['features']['ai_scheduling']['source']);
        $this->assertFalse($data['features']['api_access']['enabled']);
    }

    public function test_feature_check_api_returns_correct_status(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Professional]);
        $this->actingAs($this->admin);

        // Check feature that's included
        $response = $this->getJson(route('api.features.check', 'ai_scheduling'));
        $response->assertOk();
        $response->assertJson([
            'feature' => 'ai_scheduling',
            'enabled' => true,
            'label' => 'AI Scheduling',
        ]);

        // Check feature that's not included
        $response = $this->getJson(route('api.features.check', 'api_access'));
        $response->assertOk();
        $response->assertJson([
            'feature' => 'api_access',
            'enabled' => false,
        ]);
    }

    public function test_feature_check_api_returns_error_for_unknown_feature(): void
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('api.features.check', 'unknown_feature'));

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Unknown feature']);
    }

    public function test_tenant_convenience_methods(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Enterprise]);
        $this->tenant->refresh();

        $this->assertTrue($this->tenant->hasAIScheduling());
        $this->assertTrue($this->tenant->hasAdvancedAnalytics());
        $this->assertTrue($this->tenant->hasApiAccess());
        $this->assertTrue($this->tenant->hasPrioritySupport());

        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Basic]);
        $this->tenant->refresh();

        $this->assertFalse($this->tenant->hasAIScheduling());
        $this->assertFalse($this->tenant->hasAdvancedAnalytics());
        $this->assertFalse($this->tenant->hasApiAccess());
        $this->assertFalse($this->tenant->hasPrioritySupport());
    }

    public function test_has_plan_method(): void
    {
        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Professional]);
        $this->tenant->refresh();

        $this->assertTrue($this->tenant->hasPlan(SubscriptionPlan::Basic));
        $this->assertTrue($this->tenant->hasPlan(SubscriptionPlan::Professional));
        $this->assertFalse($this->tenant->hasPlan(SubscriptionPlan::Enterprise));
    }

    public function test_subscription_service_creates_subscription(): void
    {
        $newTenant = Tenant::factory()->create();
        // Observer creates basic subscription, so delete it first
        $newTenant->subscription()->delete();

        $service = new SubscriptionService;
        $subscription = $service->createSubscription(
            $newTenant,
            SubscriptionPlan::Professional,
            SubscriptionStatus::Active
        );

        $this->assertEquals(SubscriptionPlan::Professional, $subscription->plan);
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
    }

    public function test_subscription_service_changes_plan(): void
    {
        $service = new SubscriptionService;
        $subscription = $service->changePlan($this->tenant, SubscriptionPlan::Enterprise);

        $this->assertEquals(SubscriptionPlan::Enterprise, $subscription->plan);
    }

    public function test_subscription_service_adds_feature_addon(): void
    {
        $service = new SubscriptionService;
        $addon = $service->addFeatureAddon($this->tenant, FeatureAddon::ApiAccess);

        $this->assertEquals(FeatureAddon::ApiAccess, $addon->feature);
        $this->assertTrue($addon->isActive());
        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::ApiAccess));
    }

    public function test_subscription_service_removes_feature_addon(): void
    {
        TenantFeatureAddon::factory()
            ->apiAccess()
            ->create(['tenant_id' => $this->tenant->id]);

        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::ApiAccess));

        $service = new SubscriptionService;
        $result = $service->removeFeatureAddon($this->tenant, FeatureAddon::ApiAccess);

        $this->assertTrue($result);
        $this->tenant->refresh();
        $this->assertFalse($this->tenant->hasFeature(FeatureAddon::ApiAccess));
    }

    public function test_different_tenants_have_separate_subscriptions(): void
    {
        $otherTenant = Tenant::factory()->create();

        $this->tenant->subscription->update(['plan' => SubscriptionPlan::Enterprise]);
        $otherTenant->subscription->update(['plan' => SubscriptionPlan::Basic]);

        $this->assertEquals(SubscriptionPlan::Enterprise, $this->tenant->fresh()->subscription->plan);
        $this->assertEquals(SubscriptionPlan::Basic, $otherTenant->fresh()->subscription->plan);
    }

    public function test_addon_feature_does_not_affect_other_tenants(): void
    {
        $otherTenant = Tenant::factory()->create();

        TenantFeatureAddon::factory()
            ->apiAccess()
            ->create(['tenant_id' => $this->tenant->id]);

        $this->assertTrue($this->tenant->hasFeature(FeatureAddon::ApiAccess));
        $this->assertFalse($otherTenant->hasFeature(FeatureAddon::ApiAccess));
    }
}
