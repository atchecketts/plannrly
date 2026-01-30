<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveCalendarViewTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private Location $location;

    private Department $department;

    private BusinessRole $role;

    private LeaveType $annualLeave;

    private LeaveType $sickLeave;

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
        $this->role = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->employee->businessRoles()->attach($this->role->id, ['is_primary' => true]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $this->annualLeave = LeaveType::factory()->forTenant($this->tenant)->annual()->create();
        $this->sickLeave = LeaveType::factory()->forTenant($this->tenant)->sick()->create();
    }

    public function test_week_schedule_shows_approved_leave_in_lookup(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addDays(2);

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $endDate)
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
        $leaveLookup = $response->viewData('leaveLookup');

        $this->assertArrayHasKey($this->employee->id, $leaveLookup);
        $this->assertArrayHasKey($startDate->format('Y-m-d'), $leaveLookup[$this->employee->id]);
    }

    public function test_week_schedule_does_not_show_draft_leave(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addDays(2);

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $endDate)
            ->draft()
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
        $leaveLookup = $response->viewData('leaveLookup');

        $this->assertEmpty($leaveLookup);
    }

    public function test_week_schedule_leave_lookup_includes_leave_type(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addDays(2);

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $endDate)
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->sickLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
        $leaveLookup = $response->viewData('leaveLookup');

        $leave = $leaveLookup[$this->employee->id][$startDate->format('Y-m-d')];
        $this->assertTrue($leave->relationLoaded('leaveType'));
        $this->assertEquals($this->sickLeave->id, $leave->leaveType->id);
        $this->assertEquals('#EF4444', $leave->leaveType->color);
    }

    public function test_day_schedule_shows_approved_leave_in_lookup(): void
    {
        $this->actingAs($this->admin);

        $date = Carbon::now();

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($date, $date)
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.day', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
        $leaveLookup = $response->viewData('leaveLookup');

        $this->assertArrayHasKey($this->employee->id, $leaveLookup);
    }

    public function test_day_schedule_leave_lookup_includes_leave_type(): void
    {
        $this->actingAs($this->admin);

        $date = Carbon::now();

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($date, $date)
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.day', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
        $leaveLookup = $response->viewData('leaveLookup');

        $leave = $leaveLookup[$this->employee->id];
        $this->assertTrue($leave->relationLoaded('leaveType'));
        $this->assertEquals($this->annualLeave->id, $leave->leaveType->id);
    }

    public function test_leave_request_half_day_flags_are_accessible(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addDays(2);

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $endDate)
            ->startHalfDay()
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
        $leaveLookup = $response->viewData('leaveLookup');

        $leave = $leaveLookup[$this->employee->id][$startDate->format('Y-m-d')];
        $this->assertTrue($leave->start_half_day);
        $this->assertFalse($leave->end_half_day);
    }

    public function test_leave_request_end_half_day_flags_are_accessible(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addDays(2);

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $endDate)
            ->endHalfDay()
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
        $leaveLookup = $response->viewData('leaveLookup');

        $leave = $leaveLookup[$this->employee->id][$endDate->format('Y-m-d')];
        $this->assertFalse($leave->start_half_day);
        $this->assertTrue($leave->end_half_day);
    }

    public function test_multiple_leave_types_have_different_colors(): void
    {
        $this->actingAs($this->admin);

        $this->assertNotEquals($this->annualLeave->color, $this->sickLeave->color);
        $this->assertEquals('#3B82F6', $this->annualLeave->color);
        $this->assertEquals('#EF4444', $this->sickLeave->color);
    }

    public function test_week_schedule_renders_without_errors_with_leave(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addDays(4);

        // Create various leave types
        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $endDate)
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
        $response->assertViewHas('leaveLookup');
    }

    public function test_day_schedule_renders_without_errors_with_leave(): void
    {
        $this->actingAs($this->admin);

        $date = Carbon::now();

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($date, $date)
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->sickLeave->id]);

        $response = $this->get(route('schedule.day', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
        $response->assertViewHas('leaveLookup');
    }

    public function test_week_schedule_renders_with_half_day_start_leave(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $startDate)
            ->startHalfDay()
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
    }

    public function test_week_schedule_renders_with_half_day_end_leave(): void
    {
        $this->actingAs($this->admin);

        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addDays(2);

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($startDate, $endDate)
            ->endHalfDay()
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.index', ['start' => $startDate->format('Y-m-d')]));

        $response->assertOk();
    }

    public function test_day_schedule_renders_with_half_day_pm_leave(): void
    {
        $this->actingAs($this->admin);

        $date = Carbon::now();

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($date, $date)
            ->startHalfDay()
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.day', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
    }

    public function test_day_schedule_renders_with_half_day_am_leave(): void
    {
        $this->actingAs($this->admin);

        $date = Carbon::now();

        LeaveRequest::factory()
            ->forUser($this->employee)
            ->forDateRange($date, $date)
            ->endHalfDay()
            ->approved($this->admin)
            ->create(['leave_type_id' => $this->annualLeave->id]);

        $response = $this->get(route('schedule.day', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
    }
}
