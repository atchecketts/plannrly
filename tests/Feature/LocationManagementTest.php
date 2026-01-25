<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->admin = User::factory()->forTenant($this->tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);
    }

    public function test_admin_can_view_locations_list(): void
    {
        Location::factory()->forTenant($this->tenant)->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/locations');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_location(): void
    {
        $response = $this->actingAs($this->admin)->post('/locations', [
            'name' => 'New Location',
            'address_line_1' => '123 Main St',
            'city' => 'Test City',
            'state' => 'TS',
            'postal_code' => '12345',
            'country' => 'USA',
            'timezone' => 'America/New_York',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('locations', [
            'name' => 'New Location',
            'tenant_id' => $this->tenant->id,
            'city' => 'Test City',
        ]);
    }

    public function test_admin_can_update_location(): void
    {
        $location = Location::factory()->forTenant($this->tenant)->create();

        $response = $this->actingAs($this->admin)->put("/locations/{$location->id}", [
            'name' => 'Updated Location',
            'timezone' => 'America/Chicago',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $location->refresh();
        $this->assertEquals('Updated Location', $location->name);
        $this->assertEquals('America/Chicago', $location->timezone);
    }

    public function test_admin_can_delete_location(): void
    {
        $location = Location::factory()->forTenant($this->tenant)->create();

        $response = $this->actingAs($this->admin)->delete("/locations/{$location->id}");

        $response->assertRedirect('/locations');
        $this->assertSoftDeleted('locations', ['id' => $location->id]);
    }

    public function test_employee_cannot_create_location(): void
    {
        $employee = User::factory()->forTenant($this->tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($employee)->post('/locations', [
            'name' => 'New Location',
            'timezone' => 'UTC',
        ]);

        $response->assertStatus(403);
    }

    public function test_location_admin_can_view_assigned_location(): void
    {
        $location = Location::factory()->forTenant($this->tenant)->create();

        $locationAdmin = User::factory()->forTenant($this->tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $locationAdmin->id,
            'system_role' => SystemRole::LocationAdmin->value,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAs($locationAdmin)->get("/locations/{$location->id}");

        $response->assertStatus(200);
    }
}
