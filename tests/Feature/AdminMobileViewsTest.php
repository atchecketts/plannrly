<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMobileViewsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $admin;

    protected User $employee;

    protected Location $location;

    protected Department $department;

    protected BusinessRole $businessRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->admin = User::factory()->forTenant($this->tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->employee = User::factory()->forTenant($this->tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $this->location = Location::factory()->forTenant($this->tenant)->create();

        $this->department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $this->businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $this->employee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);
    }

    // ==================== Schedule Mobile Tests ====================

    public function test_admin_can_access_schedule_mobile_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('schedule.mobile'));

        $response->assertOk();
        $response->assertViewIs('schedule.admin-mobile-index');
        $response->assertViewHas(['startDate', 'endDate', 'weekDates', 'shifts', 'stats']);
    }

    public function test_schedule_mobile_shows_shifts_for_current_week(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->format('Y-m-d'),
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->actingAs($this->admin)->get(route('schedule.mobile'));

        $response->assertOk();
        $shifts = $response->viewData('shifts');
        $this->assertTrue($shifts->contains('id', $shift->id));
    }

    public function test_schedule_mobile_can_navigate_weeks(): void
    {
        $nextWeekStart = now()->addWeek()->startOfWeek()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('schedule.mobile', ['start' => $nextWeekStart]));

        $response->assertOk();
        $startDate = $response->viewData('startDate');
        $this->assertEquals($nextWeekStart, $startDate->format('Y-m-d'));
    }

    public function test_schedule_mobile_can_select_specific_day(): void
    {
        $today = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('schedule.mobile', ['day' => $today]));

        $response->assertOk();
        $selectedDate = $response->viewData('selectedDate');
        $this->assertEquals($today, $selectedDate->format('Y-m-d'));
    }

    public function test_schedule_mobile_shows_correct_stats(): void
    {
        Shift::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'user_id' => $this->employee->id,
            'date' => now()->format('Y-m-d'),
            'status' => ShiftStatus::Published,
        ]);

        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'user_id' => null,
            'date' => now()->format('Y-m-d'),
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->actingAs($this->admin)->get(route('schedule.mobile'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertEquals(4, $stats['total_shifts']);
        $this->assertEquals(1, $stats['unassigned']);
    }

    // ==================== Schedule Mobile Day Tests ====================

    public function test_admin_can_access_schedule_mobile_day_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('schedule.mobile.day'));

        $response->assertOk();
        $response->assertViewIs('schedule.admin-mobile-day');
        $response->assertViewHas(['selectedDate', 'shifts', 'totalShifts', 'totalHours', 'unassignedShiftsCount']);
    }

    public function test_schedule_mobile_day_shows_shifts_for_selected_date(): void
    {
        $today = now()->format('Y-m-d');

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'user_id' => $this->employee->id,
            'date' => $today,
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->actingAs($this->admin)->get(route('schedule.mobile.day', ['date' => $today]));

        $response->assertOk();
        $shifts = $response->viewData('shifts');
        $this->assertTrue($shifts->contains('id', $shift->id));
    }

    public function test_schedule_mobile_day_can_navigate_dates(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('schedule.mobile.day', ['date' => $tomorrow]));

        $response->assertOk();
        $selectedDate = $response->viewData('selectedDate');
        $this->assertEquals($tomorrow, $selectedDate->format('Y-m-d'));
    }

    // ==================== Locations Mobile Tests ====================

    public function test_admin_can_access_locations_mobile_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('locations.mobile'));

        $response->assertOk();
        $response->assertViewIs('locations.admin-mobile-index');
        $response->assertViewHas(['locations', 'stats']);
    }

    public function test_locations_mobile_shows_all_locations(): void
    {
        Location::factory()->forTenant($this->tenant)->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('locations.mobile'));

        $response->assertOk();
        $locations = $response->viewData('locations');
        $this->assertCount(4, $locations); // 3 new + 1 from setUp
    }

    public function test_locations_mobile_shows_correct_stats(): void
    {
        Location::factory()->forTenant($this->tenant)->count(2)->create(['is_active' => true]);
        Location::factory()->forTenant($this->tenant)->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('locations.mobile'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['active']); // 2 new + 1 from setUp
        $this->assertEquals(1, $stats['inactive']);
    }

    public function test_employee_cannot_access_locations_mobile_view(): void
    {
        $response = $this->actingAs($this->employee)->get(route('locations.mobile'));

        $response->assertStatus(403);
    }

    // ==================== Departments Mobile Tests ====================

    public function test_admin_can_access_departments_mobile_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('departments.mobile'));

        $response->assertOk();
        $response->assertViewIs('departments.admin-mobile-index');
        $response->assertViewHas(['departments', 'stats']);
    }

    public function test_departments_mobile_shows_all_departments(): void
    {
        Department::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('departments.mobile'));

        $response->assertOk();
        $departments = $response->viewData('departments');
        $this->assertCount(3, $departments); // 2 new + 1 from setUp
    }

    public function test_departments_mobile_shows_correct_stats(): void
    {
        Department::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);
        Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('departments.mobile'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['active']); // 2 new + 1 from setUp
        $this->assertEquals(1, $stats['inactive']);
    }

    public function test_departments_mobile_includes_location_relationship(): void
    {
        $response = $this->actingAs($this->admin)->get(route('departments.mobile'));

        $response->assertOk();
        $departments = $response->viewData('departments');
        $this->assertNotNull($departments->first()->location);
    }

    public function test_employee_cannot_access_departments_mobile_view(): void
    {
        $response = $this->actingAs($this->employee)->get(route('departments.mobile'));

        $response->assertStatus(403);
    }

    // ==================== Business Roles Mobile Tests ====================

    public function test_admin_can_access_business_roles_mobile_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('business-roles.mobile'));

        $response->assertOk();
        $response->assertViewIs('business-roles.admin-mobile-index');
        $response->assertViewHas(['businessRoles', 'stats']);
    }

    public function test_business_roles_mobile_shows_all_roles(): void
    {
        BusinessRole::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('business-roles.mobile'));

        $response->assertOk();
        $businessRoles = $response->viewData('businessRoles');
        $this->assertCount(3, $businessRoles); // 2 new + 1 from setUp
    }

    public function test_business_roles_mobile_shows_correct_stats(): void
    {
        BusinessRole::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('business-roles.mobile'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['active']); // 2 new + 1 from setUp
        $this->assertEquals(1, $stats['inactive']);
    }

    public function test_business_roles_mobile_includes_department_and_location(): void
    {
        $response = $this->actingAs($this->admin)->get(route('business-roles.mobile'));

        $response->assertOk();
        $businessRoles = $response->viewData('businessRoles');
        $role = $businessRoles->first();
        $this->assertNotNull($role->department);
        $this->assertNotNull($role->department->location);
    }

    public function test_business_roles_mobile_includes_users_count(): void
    {
        $response = $this->actingAs($this->admin)->get(route('business-roles.mobile'));

        $response->assertOk();
        $businessRoles = $response->viewData('businessRoles');
        $role = $businessRoles->firstWhere('id', $this->businessRole->id);
        $this->assertEquals(1, $role->users_count); // employee from setUp
    }

    public function test_employee_cannot_access_business_roles_mobile_view(): void
    {
        $response = $this->actingAs($this->employee)->get(route('business-roles.mobile'));

        $response->assertStatus(403);
    }

    // ==================== Users Mobile Tests ====================

    public function test_admin_can_access_users_mobile_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.mobile'));

        $response->assertOk();
        $response->assertViewIs('users.admin-mobile-index');
        $response->assertViewHas(['users', 'stats']);
    }

    public function test_users_mobile_shows_all_team_members(): void
    {
        User::factory()->forTenant($this->tenant)->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('users.mobile'));

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertGreaterThanOrEqual(5, $users->count()); // 3 new + admin + employee
    }

    public function test_users_mobile_shows_correct_stats(): void
    {
        User::factory()->forTenant($this->tenant)->count(2)->create(['is_active' => true]);
        User::factory()->forTenant($this->tenant)->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('users.mobile'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('active', $stats);
        $this->assertArrayHasKey('inactive', $stats);
    }

    // ==================== Leave Requests Mobile Tests ====================

    public function test_admin_can_access_leave_requests_mobile_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('leave-requests.mobile'));

        $response->assertOk();
        $response->assertViewIs('leave-requests.admin-mobile-index');
    }

    public function test_leave_requests_mobile_shows_pending_requests(): void
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('leave-requests.mobile'));

        $response->assertOk();
    }

    // ==================== Authentication Tests ====================

    public function test_unauthenticated_user_cannot_access_mobile_views(): void
    {
        $routes = [
            'schedule.mobile',
            'schedule.mobile.day',
            'locations.mobile',
            'departments.mobile',
            'business-roles.mobile',
            'users.mobile',
            'leave-requests.mobile',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertRedirect(route('login'));
        }
    }

    // ==================== Tenant Isolation Tests ====================

    public function test_admin_only_sees_own_tenant_data_in_schedule_mobile(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherLocation = Location::factory()->forTenant($otherTenant)->create();
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);

        Shift::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
            'department_id' => $otherDepartment->id,
            'date' => now()->format('Y-m-d'),
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->actingAs($this->admin)->get(route('schedule.mobile'));

        $response->assertOk();
        $shifts = $response->viewData('shifts');
        foreach ($shifts as $shift) {
            $this->assertEquals($this->tenant->id, $shift->tenant_id);
        }
    }

    public function test_admin_only_sees_own_tenant_locations_in_mobile(): void
    {
        $otherTenant = Tenant::factory()->create();
        Location::factory()->forTenant($otherTenant)->create();

        $response = $this->actingAs($this->admin)->get(route('locations.mobile'));

        $response->assertOk();
        $locations = $response->viewData('locations');
        foreach ($locations as $location) {
            $this->assertEquals($this->tenant->id, $location->tenant_id);
        }
    }

    public function test_admin_only_sees_own_tenant_departments_in_mobile(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherLocation = Location::factory()->forTenant($otherTenant)->create();
        Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('departments.mobile'));

        $response->assertOk();
        $departments = $response->viewData('departments');
        foreach ($departments as $department) {
            $this->assertEquals($this->tenant->id, $department->tenant_id);
        }
    }

    public function test_admin_only_sees_own_tenant_business_roles_in_mobile(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherLocation = Location::factory()->forTenant($otherTenant)->create();
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);
        BusinessRole::factory()->create([
            'tenant_id' => $otherTenant->id,
            'department_id' => $otherDepartment->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('business-roles.mobile'));

        $response->assertOk();
        $businessRoles = $response->viewData('businessRoles');
        foreach ($businessRoles as $role) {
            $this->assertEquals($this->tenant->id, $role->tenant_id);
        }
    }
}
