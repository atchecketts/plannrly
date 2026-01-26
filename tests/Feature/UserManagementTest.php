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

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

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
        $this->businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        User::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');
    }

    public function test_admin_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/users/create');

        $response->assertStatus(200);
        $response->assertViewIs('users.create');
        $response->assertViewHas('locations');
        $response->assertViewHas('departments');
        $response->assertViewHas('businessRoles');
        $response->assertViewHas('systemRoles');
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'first_name' => 'New',
            'last_name' => 'Employee',
            'email' => 'new.employee@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'system_role' => SystemRole::Employee->value,
            'business_role_ids' => [$this->businessRole->id],
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'first_name' => 'New',
            'last_name' => 'Employee',
            'email' => 'new.employee@example.com',
            'tenant_id' => $this->tenant->id,
        ]);

        $user = User::where('email', 'new.employee@example.com')->first();
        $this->assertDatabaseHas('user_role_assignments', [
            'user_id' => $user->id,
            'system_role' => SystemRole::Employee->value,
        ]);
    }

    public function test_admin_can_view_user_details(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->admin)->get("/users/{$employee->id}");

        $response->assertStatus(200);
        $response->assertViewIs('users.show');
        $response->assertViewHas('user');
    }

    public function test_admin_can_update_user(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($this->admin)->put("/users/{$employee->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $employee->email,
            'system_role' => SystemRole::Employee->value,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('users.show', $employee));
        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertEquals('Updated', $employee->first_name);
        $this->assertEquals('Name', $employee->last_name);
    }

    public function test_admin_can_delete_user(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->admin)->delete("/users/{$employee->id}");

        $response->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', ['id' => $employee->id]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete("/users/{$this->admin->id}");

        $response->assertStatus(403);
    }

    public function test_employee_cannot_view_users_list(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($employee)->get('/users');

        $response->assertStatus(403);
    }

    public function test_employee_cannot_create_user(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($employee)->post('/users', [
            'first_name' => 'Unauthorized',
            'last_name' => 'User',
            'email' => 'unauthorized@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'system_role' => SystemRole::Employee->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_location_admin_can_create_user(): void
    {
        $locationAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $locationAdmin->id,
            'system_role' => SystemRole::LocationAdmin->value,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($locationAdmin)->post('/users', [
            'first_name' => 'Location',
            'last_name' => 'Employee',
            'email' => 'location.employee@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'system_role' => SystemRole::Employee->value,
            'business_role_ids' => [$this->businessRole->id],
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'location.employee@example.com',
        ]);
    }

    public function test_users_cannot_view_other_tenant_users(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $response = $this->actingAs($this->admin)->get("/users/{$otherUser->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_be_assigned_multiple_business_roles(): void
    {
        $anotherRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->admin)->post('/users', [
            'first_name' => 'Multi',
            'last_name' => 'Role',
            'email' => 'multi.role@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'system_role' => SystemRole::Employee->value,
            'business_role_ids' => [$this->businessRole->id, $anotherRole->id],
            'primary_business_role_id' => $this->businessRole->id,
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'multi.role@example.com')->first();
        $this->assertCount(2, $user->businessRoles);
    }

    public function test_user_requires_first_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'system_role' => SystemRole::Employee->value,
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_user_requires_last_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'system_role' => SystemRole::Employee->value,
        ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_user_requires_unique_email_in_tenant(): void
    {
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'existing@example.com',
        ]);

        $response = $this->actingAs($this->admin)->post('/users', [
            'first_name' => 'Duplicate',
            'last_name' => 'Email',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'system_role' => SystemRole::Employee->value,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_user_password(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($this->admin)->put("/users/{$employee->id}", [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'system_role' => SystemRole::Employee->value,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('users.show', $employee));
    }

    public function test_admin_can_deactivate_user(): void
    {
        $employee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        UserRoleAssignment::create([
            'user_id' => $employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $response = $this->actingAs($this->admin)->put("/users/{$employee->id}", [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'system_role' => SystemRole::Employee->value,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('users.show', $employee));

        $employee->refresh();
        $this->assertFalse($employee->is_active);
    }
}
