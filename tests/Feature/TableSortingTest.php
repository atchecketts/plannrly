<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableSortingTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);

        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);
    }

    public function test_users_index_returns_sort_params(): void
    {
        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
    }

    public function test_users_index_sorts_by_name_ascending(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Charlie']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Alice']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Bob']);

        $response = $this->actingAs($this->admin)->get('/users?sort=name&direction=asc');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $names = $users->pluck('first_name')->values();

        $this->assertEquals('Admin', $names[0]);
        $this->assertEquals('Alice', $names[1]);
        $this->assertEquals('Bob', $names[2]);
        $this->assertEquals('Charlie', $names[3]);
    }

    public function test_users_index_sorts_by_name_descending(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Charlie']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Alice']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Bob']);

        $response = $this->actingAs($this->admin)->get('/users?sort=name&direction=desc');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $names = $users->pluck('first_name')->values();

        $this->assertEquals('Charlie', $names[0]);
        $this->assertEquals('Bob', $names[1]);
        $this->assertEquals('Alice', $names[2]);
        $this->assertEquals('Admin', $names[3]);
    }

    public function test_users_index_sorts_by_email(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'zeta@example.com']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'alpha@example.com']);

        $response = $this->actingAs($this->admin)->get('/users?sort=email&direction=asc');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $emails = $users->pluck('email')->values();

        $this->assertTrue($emails[0] < $emails[1]);
    }

    public function test_invalid_sort_column_falls_back_to_default(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Charlie']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Alice']);

        $response = $this->actingAs($this->admin)->get('/users?sort=invalid_column&direction=asc');

        $response->assertStatus(200);
        $sortParams = $response->viewData('sortParams');
        $this->assertNull($sortParams['sort']);
    }

    public function test_invalid_direction_falls_back_to_asc(): void
    {
        $response = $this->actingAs($this->admin)->get('/users?sort=name&direction=invalid');

        $response->assertStatus(200);
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('asc', $sortParams['direction']);
    }

    public function test_sort_persists_through_pagination(): void
    {
        User::factory()->count(20)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->admin)->get('/users?sort=name&direction=desc&page=2');

        $response->assertStatus(200);
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('name', $sortParams['sort']);
        $this->assertEquals('desc', $sortParams['direction']);
    }

    public function test_group_by_status_works(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => false]);

        $response = $this->actingAs($this->admin)->get('/users?group=status');

        $response->assertStatus(200);
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('status', $sortParams['group']);
    }

    public function test_non_groupable_column_is_rejected(): void
    {
        // 'invalid_column' is not in the sortable/groupable columns list
        $response = $this->actingAs($this->admin)->get('/users?group=invalid_column');

        $response->assertStatus(200);
        $sortParams = $response->viewData('sortParams');
        $this->assertNull($sortParams['group']);
    }

    public function test_sort_and_group_can_be_combined(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true, 'first_name' => 'Zach']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => false, 'first_name' => 'Alice']);

        $response = $this->actingAs($this->admin)->get('/users?sort=name&direction=asc&group=status');

        $response->assertStatus(200);
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('name', $sortParams['sort']);
        $this->assertEquals('status', $sortParams['group']);
    }

    public function test_default_sort_when_no_params(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Zach']);
        User::factory()->create(['tenant_id' => $this->tenant->id, 'first_name' => 'Alice']);

        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $names = $users->pluck('first_name')->values();

        $this->assertEquals('Admin', $names[0]);
        $this->assertEquals('Alice', $names[1]);
        $this->assertEquals('Zach', $names[2]);
    }

    public function test_locations_index_has_sort_params(): void
    {
        Location::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->admin)->get('/locations?sort=name&direction=asc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('name', $sortParams['sort']);
    }

    public function test_locations_can_group_by_city(): void
    {
        $response = $this->actingAs($this->admin)->get('/locations?group=city');

        $response->assertStatus(200);
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('city', $sortParams['group']);
    }

    public function test_departments_index_has_sort_params(): void
    {
        $location = Location::factory()->create(['tenant_id' => $this->tenant->id]);
        Department::factory()->create(['tenant_id' => $this->tenant->id, 'location_id' => $location->id]);

        $response = $this->actingAs($this->admin)->get('/departments?sort=name&direction=desc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('name', $sortParams['sort']);
        $this->assertEquals('desc', $sortParams['direction']);
    }

    public function test_business_roles_index_has_sort_params(): void
    {
        $location = Location::factory()->create(['tenant_id' => $this->tenant->id]);
        $department = Department::factory()->create(['tenant_id' => $this->tenant->id, 'location_id' => $location->id]);
        BusinessRole::factory()->create(['tenant_id' => $this->tenant->id, 'department_id' => $department->id]);

        $response = $this->actingAs($this->admin)->get('/business-roles?sort=hourly_rate&direction=desc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('hourly_rate', $sortParams['sort']);
    }

    public function test_leave_types_index_has_sort_params(): void
    {
        LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->admin)->get('/leave-types?sort=requests&direction=asc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('requests', $sortParams['sort']);
    }

    public function test_leave_requests_index_has_sort_params(): void
    {
        $response = $this->actingAs($this->admin)->get('/leave-requests?sort=start_date&direction=asc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('start_date', $sortParams['sort']);
    }

    public function test_leave_allowances_index_has_sort_params(): void
    {
        $response = $this->actingAs($this->admin)->get('/leave-allowances?sort=total&direction=desc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('total', $sortParams['sort']);
    }

    public function test_shift_swaps_index_has_sort_params(): void
    {
        $response = $this->actingAs($this->admin)->get('/shift-swaps?sort=requested&direction=desc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('requested', $sortParams['sort']);
    }

    public function test_super_admin_tenants_index_has_sort_params(): void
    {
        $superAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $superAdmin->id,
            'system_role' => SystemRole::SuperAdmin->value,
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/tenants?sort=organization&direction=asc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('organization', $sortParams['sort']);
    }

    public function test_super_admin_users_index_has_sort_params(): void
    {
        $superAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $superAdmin->id,
            'system_role' => SystemRole::SuperAdmin->value,
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/users?sort=last_login&direction=desc');

        $response->assertStatus(200);
        $response->assertViewHas('sortParams');
        $sortParams = $response->viewData('sortParams');
        $this->assertEquals('last_login', $sortParams['sort']);
    }
}
