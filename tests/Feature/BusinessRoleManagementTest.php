<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private Location $location;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->location = Location::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);
    }

    public function test_admin_can_view_business_roles_list(): void
    {
        BusinessRole::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/business-roles');

        $response->assertStatus(200);
        $response->assertViewIs('business-roles.index');
        $response->assertViewHas('businessRoles');
    }

    public function test_admin_can_view_create_business_role_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/business-roles/create');

        $response->assertStatus(200);
        $response->assertViewIs('business-roles.create');
        $response->assertViewHas('departments');
    }

    public function test_admin_can_create_business_role(): void
    {
        $response = $this->actingAs($this->admin)->post('/business-roles', [
            'name' => 'New Business Role',
            'description' => 'Test description',
            'department_id' => $this->department->id,
            'color' => '#FF5733',
            'default_hourly_rate' => 15.50,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('business-roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('business_roles', [
            'name' => 'New Business Role',
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);
    }

    public function test_admin_can_update_business_role(): void
    {
        $businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->admin)->put("/business-roles/{$businessRole->id}", [
            'name' => 'Updated Business Role',
            'department_id' => $this->department->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('business-roles.index'));
        $response->assertSessionHas('success');

        $businessRole->refresh();
        $this->assertEquals('Updated Business Role', $businessRole->name);
    }

    public function test_admin_can_delete_business_role(): void
    {
        $businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/business-roles/{$businessRole->id}");

        $response->assertRedirect(route('business-roles.index'));
        $this->assertSoftDeleted('business_roles', ['id' => $businessRole->id]);
    }

    public function test_employee_cannot_create_business_role(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($employee)->post('/business-roles', [
            'name' => 'Unauthorized Role',
            'department_id' => $this->department->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_location_admin_cannot_create_business_role(): void
    {
        // Note: StoreBusinessRoleRequest only authorizes SuperAdmin, Admin, and DepartmentAdmin
        // LocationAdmin is not authorized to create business roles (form request authorization)
        $locationAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $locationAdmin->id,
            'system_role' => SystemRole::LocationAdmin->value,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($locationAdmin)->post('/business-roles', [
            'name' => 'Location Admin Role',
            'department_id' => $this->department->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_department_admin_can_create_business_role(): void
    {
        $departmentAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $departmentAdmin->id,
            'system_role' => SystemRole::DepartmentAdmin->value,
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($departmentAdmin)->post('/business-roles', [
            'name' => 'Department Admin Role',
            'department_id' => $this->department->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('business-roles.index'));
        $this->assertDatabaseHas('business_roles', [
            'name' => 'Department Admin Role',
        ]);
    }

    public function test_users_cannot_view_other_tenant_business_roles(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherLocation = Location::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);
        $otherRole = BusinessRole::factory()->create([
            'tenant_id' => $otherTenant->id,
            'department_id' => $otherDepartment->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/business-roles/{$otherRole->id}/edit");

        $response->assertStatus(404);
    }

    public function test_business_role_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/business-roles', [
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_business_role_requires_department(): void
    {
        $response = $this->actingAs($this->admin)->post('/business-roles', [
            'name' => 'No Department Role',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('department_id');
    }

    public function test_business_role_shows_user_count(): void
    {
        $businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $employee->businessRoles()->attach($businessRole->id, ['is_primary' => true]);

        $response = $this->actingAs($this->admin)->get('/business-roles');

        $response->assertStatus(200);
        $roles = $response->viewData('businessRoles');
        $role = $roles->firstWhere('id', $businessRole->id);
        $this->assertEquals(1, $role->users_count);
    }
}
