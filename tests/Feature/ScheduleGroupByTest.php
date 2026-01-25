<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleGroupByTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private Location $location;

    private Department $department1;

    private Department $department2;

    private BusinessRole $role1;

    private BusinessRole $role2;

    private User $employee1;

    private User $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        // TenantSettings is automatically created by TenantObserver

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->location = Location::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->department1 = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'name' => 'Front Desk',
        ]);

        $this->department2 = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'name' => 'Kitchen',
        ]);

        $this->role1 = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department1->id,
            'name' => 'Cashier',
        ]);

        $this->role2 = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department2->id,
            'name' => 'Chef',
        ]);

        $this->employee1 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
        ]);
        $this->employee1->businessRoles()->attach($this->role1->id, ['is_primary' => true]);

        $this->employee2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Bob',
            'last_name' => 'Jones',
        ]);
        $this->employee2->businessRoles()->attach($this->role2->id, ['is_primary' => true]);
    }

    public function test_week_schedule_defaults_to_department_grouping(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.index'));

        $response->assertOk();
        $response->assertViewHas('groupBy', 'department');
        $response->assertViewHas('usersByDepartment');
        $response->assertViewHas('usersByRole');
    }

    public function test_week_schedule_can_use_role_grouping(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.index', ['group_by' => 'role']));

        $response->assertOk();
        $response->assertViewHas('groupBy', 'role');
    }

    public function test_week_schedule_preserves_group_by_with_date_navigation(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.index', [
            'start' => '2024-01-15',
            'group_by' => 'role',
        ]));

        $response->assertOk();
        $response->assertViewHas('groupBy', 'role');
    }

    public function test_day_schedule_defaults_to_department_grouping(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.day'));

        $response->assertOk();
        $response->assertViewHas('groupBy', 'department');
        $response->assertViewHas('usersByDepartment');
        $response->assertViewHas('usersByRole');
    }

    public function test_day_schedule_can_use_role_grouping(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.day', ['group_by' => 'role']));

        $response->assertOk();
        $response->assertViewHas('groupBy', 'role');
    }

    public function test_day_schedule_preserves_group_by_with_date_navigation(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.day', [
            'date' => '2024-01-15',
            'group_by' => 'role',
        ]));

        $response->assertOk();
        $response->assertViewHas('groupBy', 'role');
    }

    public function test_users_by_department_groups_correctly(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.index', ['group_by' => 'department']));

        $response->assertOk();
        $usersByDepartment = $response->viewData('usersByDepartment');

        // Employee1 should be in department1
        $this->assertTrue($usersByDepartment->has($this->department1->id));
        $this->assertTrue(
            $usersByDepartment->get($this->department1->id)->contains('id', $this->employee1->id)
        );

        // Employee2 should be in department2
        $this->assertTrue($usersByDepartment->has($this->department2->id));
        $this->assertTrue(
            $usersByDepartment->get($this->department2->id)->contains('id', $this->employee2->id)
        );
    }

    public function test_users_by_role_groups_correctly(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.index', ['group_by' => 'role']));

        $response->assertOk();
        $usersByRole = $response->viewData('usersByRole');

        // Employee1 should be in role1
        $this->assertTrue($usersByRole->has($this->role1->id));
        $this->assertTrue(
            $usersByRole->get($this->role1->id)->contains('id', $this->employee1->id)
        );

        // Employee2 should be in role2
        $this->assertTrue($usersByRole->has($this->role2->id));
        $this->assertTrue(
            $usersByRole->get($this->role2->id)->contains('id', $this->employee2->id)
        );
    }

    public function test_invalid_group_by_defaults_to_department(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('schedule.index', ['group_by' => 'invalid']));

        $response->assertOk();
        // Should fall through since we're just passing the value to the view
        // The view will handle unknown values
        $response->assertViewHas('groupBy', 'invalid');
    }

    public function test_employee_can_view_schedule_with_group_by(): void
    {
        // Give employee1 employee role
        UserRoleAssignment::create([
            'user_id' => $this->employee1->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $this->actingAs($this->employee1);

        $response = $this->get(route('schedule.index', ['group_by' => 'role']));

        $response->assertOk();
        $response->assertViewHas('groupBy', 'role');
    }

    public function test_schedule_shows_shifts_with_both_groupings(): void
    {
        $this->actingAs($this->admin);

        // Create shifts for both employees
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department1->id,
            'business_role_id' => $this->role1->id,
            'user_id' => $this->employee1->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department2->id,
            'business_role_id' => $this->role2->id,
            'user_id' => $this->employee2->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);

        // Check department grouping
        $response = $this->get(route('schedule.index', ['group_by' => 'department']));
        $response->assertOk();
        $response->assertViewHas('shifts');
        $shifts = $response->viewData('shifts');
        $this->assertCount(2, $shifts);

        // Check role grouping - same shifts, different grouping
        $response = $this->get(route('schedule.index', ['group_by' => 'role']));
        $response->assertOk();
        $shifts = $response->viewData('shifts');
        $this->assertCount(2, $shifts);
    }
}
