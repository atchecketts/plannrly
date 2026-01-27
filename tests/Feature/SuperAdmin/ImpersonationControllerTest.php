<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationControllerTest extends TestCase
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

    public function test_super_admin_can_impersonate_regular_user(): void
    {
        $regularUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.impersonate.start', $regularUser));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($regularUser);
        $this->assertEquals($this->superAdmin->id, session('impersonator_id'));
    }

    public function test_super_admin_cannot_impersonate_another_super_admin(): void
    {
        $anotherSuperAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $anotherSuperAdmin->id,
            'system_role' => SystemRole::SuperAdmin->value,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.impersonate.start', $anotherSuperAdmin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    public function test_non_super_admin_cannot_impersonate(): void
    {
        $regularUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $targetUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($regularUser)
            ->post(route('super-admin.impersonate.start', $targetUser));

        $response->assertForbidden();
    }

    public function test_can_stop_impersonating(): void
    {
        $regularUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Start impersonation
        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.impersonate.start', $regularUser));

        // Stop impersonation
        $response = $this->actingAs($regularUser)
            ->withSession([
                'impersonator_id' => $this->superAdmin->id,
                'impersonator_name' => $this->superAdmin->full_name,
            ])
            ->post(route('impersonate.stop'));

        $response->assertRedirect(route('super-admin.users.index'));
        $this->assertAuthenticatedAs($this->superAdmin);
        $this->assertNull(session('impersonator_id'));
    }

    public function test_cannot_stop_impersonating_when_not_impersonating(): void
    {
        $regularUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($regularUser)
            ->post(route('impersonate.stop'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_impersonation_banner_shows_when_impersonating(): void
    {
        $regularUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($regularUser)
            ->withSession([
                'impersonator_id' => $this->superAdmin->id,
                'impersonator_name' => $this->superAdmin->full_name,
            ])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('You are impersonating');
        $response->assertSee('Stop Impersonating');
    }
}
