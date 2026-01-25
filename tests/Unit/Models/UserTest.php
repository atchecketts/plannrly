<?php

namespace Tests\Unit\Models;

use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Location;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_full_name_attribute(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_has_initials_attribute(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('JD', $user->initials);
    }

    public function test_user_belongs_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();

        $this->assertInstanceOf(Tenant::class, $user->tenant);
        $this->assertEquals($tenant->id, $user->tenant->id);
    }

    public function test_is_super_admin_returns_true_for_super_admin(): void
    {
        $user = User::factory()->create();
        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::SuperAdmin->value,
        ]);

        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_is_admin_returns_true_for_admin(): void
    {
        $user = User::factory()->create();
        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_is_location_admin_with_specific_location(): void
    {
        $tenant = Tenant::factory()->create();
        $location = Location::factory()->forTenant($tenant)->create();
        $user = User::factory()->forTenant($tenant)->create();

        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::LocationAdmin->value,
            'location_id' => $location->id,
        ]);

        $this->assertTrue($user->isLocationAdmin($location->id));
        $this->assertTrue($user->isLocationAdmin()); // Should return true without specific location
    }

    public function test_get_highest_role_returns_correct_role(): void
    {
        $user = User::factory()->create();

        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $this->assertEquals(SystemRole::Admin, $user->getHighestRole());
    }

    public function test_can_manage_location_for_admin(): void
    {
        $tenant = Tenant::factory()->create();
        $location = Location::factory()->forTenant($tenant)->create();
        $user = User::factory()->forTenant($tenant)->create();

        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->assertTrue($user->canManageLocation($location));
    }

    public function test_can_manage_department_for_department_admin(): void
    {
        $tenant = Tenant::factory()->create();
        $location = Location::factory()->forTenant($tenant)->create();
        $department = Department::factory()->forLocation($location)->create();
        $user = User::factory()->forTenant($tenant)->create();

        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => SystemRole::DepartmentAdmin->value,
            'department_id' => $department->id,
        ]);

        $this->assertTrue($user->canManageDepartment($department));
    }

    public function test_active_scope_filters_inactive_users(): void
    {
        $tenant = Tenant::factory()->create();
        User::factory()->forTenant($tenant)->count(3)->create(['is_active' => true]);
        User::factory()->forTenant($tenant)->count(2)->create(['is_active' => false]);

        $this->assertEquals(3, User::where('tenant_id', $tenant->id)->active()->count());
    }
}
