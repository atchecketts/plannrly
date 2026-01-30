<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_only_see_their_own_tenant_locations(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $location1 = Location::factory()->forTenant($tenant1)->create(['name' => 'Tenant 1 Location']);
        $location2 = Location::factory()->forTenant($tenant2)->create(['name' => 'Tenant 2 Location']);

        $user = User::factory()->forTenant($tenant1)->create();
        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $response = $this->actingAs($user)->get('/locations');

        $response->assertStatus(200);
        $response->assertSee('Tenant 1 Location');
        $response->assertDontSee('Tenant 2 Location');
    }

    public function test_users_cannot_access_other_tenant_locations(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $location = Location::factory()->forTenant($tenant2)->create();

        $user = User::factory()->forTenant($tenant1)->create();
        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $response = $this->actingAs($user)->get("/locations/{$location->id}");

        $response->assertStatus(404);
    }

    public function test_tenant_scope_automatically_filters_queries(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        Location::factory()->forTenant($tenant1)->count(3)->create();
        Location::factory()->forTenant($tenant2)->count(5)->create();

        $user = User::factory()->forTenant($tenant1)->create();

        $this->actingAs($user);

        $locations = Location::all();

        $this->assertCount(3, $locations);
    }

    public function test_creating_records_automatically_assigns_tenant_id(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->actingAs($user);

        $location = Location::create([
            'name' => 'New Location',
            'timezone' => 'UTC',
        ]);

        $this->assertEquals($tenant->id, $location->tenant_id);
    }

    public function test_users_can_only_see_their_tenant_users(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->forTenant($tenant1)->create(['first_name' => 'John', 'last_name' => 'Tenant1']);
        $user2 = User::factory()->forTenant($tenant2)->create(['first_name' => 'Jane', 'last_name' => 'Tenant2']);

        UserRoleAssignment::create([
            'user_id' => $user1->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $response = $this->actingAs($user1)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('John Tenant1');
        $response->assertDontSee('Jane Tenant2');
    }
}
