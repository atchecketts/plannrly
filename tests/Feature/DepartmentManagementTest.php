<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private Location $location;

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
    }

    public function test_admin_can_view_departments_list(): void
    {
        Department::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/departments');

        $response->assertStatus(200);
        $response->assertViewIs('departments.index');
        $response->assertViewHas('departments');
    }

    public function test_admin_can_view_create_department_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/departments/create');

        $response->assertStatus(200);
        $response->assertViewIs('departments.create');
        $response->assertViewHas('locations');
    }

    public function test_admin_can_create_department(): void
    {
        $response = $this->actingAs($this->admin)->post('/departments', [
            'name' => 'New Department',
            'description' => 'Test description',
            'location_id' => $this->location->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'name' => 'New Department',
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);
    }

    public function test_admin_can_update_department(): void
    {
        $department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)->put("/departments/{$department->id}", [
            'name' => 'Updated Department',
            'location_id' => $this->location->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success');

        $department->refresh();
        $this->assertEquals('Updated Department', $department->name);
    }

    public function test_admin_can_delete_department(): void
    {
        $department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/departments/{$department->id}");

        $response->assertRedirect(route('departments.index'));
        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }

    public function test_employee_cannot_view_departments(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($employee)->get('/departments');

        $response->assertStatus(403);
    }

    public function test_employee_cannot_create_department(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($employee)->post('/departments', [
            'name' => 'Unauthorized Department',
            'location_id' => $this->location->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_location_admin_can_create_department(): void
    {
        $locationAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $locationAdmin->id,
            'system_role' => SystemRole::LocationAdmin->value,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($locationAdmin)->post('/departments', [
            'name' => 'Location Admin Department',
            'location_id' => $this->location->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('departments.index'));
        $this->assertDatabaseHas('departments', [
            'name' => 'Location Admin Department',
        ]);
    }

    public function test_department_admin_cannot_create_department(): void
    {
        $department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $departmentAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $departmentAdmin->id,
            'system_role' => SystemRole::DepartmentAdmin->value,
            'department_id' => $department->id,
        ]);

        $response = $this->actingAs($departmentAdmin)->post('/departments', [
            'name' => 'Unauthorized Department',
            'location_id' => $this->location->id,
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_users_cannot_view_other_tenant_departments(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherLocation = Location::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/departments/{$otherDepartment->id}/edit");

        $response->assertStatus(404);
    }

    public function test_department_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/departments', [
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_department_requires_location(): void
    {
        $response = $this->actingAs($this->admin)->post('/departments', [
            'name' => 'No Location Department',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('location_id');
    }
}
