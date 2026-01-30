<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $employee;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->location = Location::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);
        $this->businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->employee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);
    }

    public function test_employee_can_view_dashboard(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.employee');
    }

    public function test_dashboard_shows_upcoming_shifts(): void
    {
        $this->actingAs($this->employee);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('upcomingShifts');
        $response->assertSee('My Upcoming Shifts', false);
    }

    public function test_dashboard_shows_stats(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats');
    }

    public function test_dashboard_shows_my_leave_requests(): void
    {
        $this->actingAs($this->employee);

        $leaveType = LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);
        LeaveRequest::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(12),
            'total_days' => 3,
            'status' => LeaveRequestStatus::Requested,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('myLeaveRequests');
        $response->assertSee('My Leave Requests', false);
    }

    public function test_dashboard_shows_my_swap_requests(): void
    {
        $this->actingAs($this->employee);

        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $otherEmployee->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $otherEmployee->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('mySwapRequests');
        $response->assertSee('My Swap Requests', false);
    }

    public function test_dashboard_shows_next_shift_countdown(): void
    {
        $this->actingAs($this->employee);

        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->addDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('nextShift');
        $response->assertSee('Your Next Shift', false);
    }

    public function test_dashboard_shows_only_employee_own_shifts(): void
    {
        $this->actingAs($this->employee);

        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Employee's own shift
        $myShift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        // Other employee's shift
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $otherEmployee->id,
            'date' => today(),
            'start_time' => '10:00',
            'end_time' => '18:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('upcomingShifts', function ($shifts) use ($myShift) {
            return $shifts->count() === 1 && $shifts->first()->id === $myShift->id;
        });
    }
}
