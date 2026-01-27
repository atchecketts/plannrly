<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->superAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->superAdmin->id,
            'system_role' => SystemRole::SuperAdmin->value,
        ]);
    }

    public function test_super_admin_can_view_tenants_list(): void
    {
        Tenant::factory()->count(5)->create();

        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.tenants.index'));

        $response->assertOk();
        $response->assertViewIs('super-admin.tenants.index');
        $response->assertViewHas('tenants');
    }

    public function test_non_super_admin_cannot_view_tenants_list(): void
    {
        $regularUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($regularUser)->get(route('super-admin.tenants.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_can_search_tenants(): void
    {
        Tenant::factory()->create(['name' => 'Test Company']);
        Tenant::factory()->create(['name' => 'Other Business']);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.tenants.index', ['search' => 'Test']));

        $response->assertOk();
        $response->assertSee('Test Company');
        $response->assertDontSee('Other Business');
    }

    public function test_super_admin_can_filter_tenants_by_status(): void
    {
        Tenant::factory()->create(['name' => 'Active Tenant', 'is_active' => true]);
        Tenant::factory()->create(['name' => 'Inactive Tenant', 'is_active' => false]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.tenants.index', ['status' => 'active']));

        $response->assertOk();
        $response->assertSee('Active Tenant');
        $response->assertDontSee('Inactive Tenant');
    }

    public function test_super_admin_can_view_tenant_details(): void
    {
        $tenant = Tenant::factory()->create();
        User::factory()->count(3)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.tenants.show', $tenant));

        $response->assertOk();
        $response->assertViewIs('super-admin.tenants.show');
        $response->assertViewHas('tenant');
        $response->assertViewHas('stats');
    }

    public function test_super_admin_can_view_edit_tenant_form(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.tenants.edit', $tenant));

        $response->assertOk();
        $response->assertViewIs('super-admin.tenants.edit');
        $response->assertViewHas('tenant');
    }

    public function test_super_admin_can_update_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->put(route('super-admin.tenants.update', $tenant), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '123-456-7890',
                'address' => '123 Main St',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('super-admin.tenants.show', $tenant));
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_super_admin_can_toggle_tenant_status(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.tenants.toggle-status', $tenant));

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'is_active' => false,
        ]);
    }
}
