<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Enums\TimeEntryStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveAllowance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\Tenant;
use App\Models\TimeEntry;
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

    public function test_dashboard_shows_today_shift(): void
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
        $response->assertViewHas('todayShift');
        $response->assertSee("Today's Shift", false);
    }

    public function test_dashboard_shows_no_shift_message_when_off(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('No Shift Today');
    }

    public function test_dashboard_shows_active_time_entry(): void
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

        TimeEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subHours(2),
            'status' => TimeEntryStatus::ClockedIn,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('activeTimeEntry');
        $response->assertSee('Clocked In');
    }

    public function test_dashboard_shows_week_summary(): void
    {
        $this->actingAs($this->employee);

        // Create shifts for this week
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->startOfWeek()->addDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('weekSummary');
        $response->assertSee('Scheduled');
        $response->assertSee('Worked');
        $response->assertSee('Shifts Left');
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
            'date' => now()->addDays(2),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('upcomingShifts');
        $response->assertSee('Upcoming Shifts');
    }

    public function test_dashboard_shows_leave_balances(): void
    {
        $this->actingAs($this->employee);

        $leaveType = LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);
        LeaveAllowance::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => now()->year,
            'total_days' => 25,
            'used_days' => 5,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('leaveBalances');
        $response->assertSee('Leave Balance');
    }

    public function test_dashboard_shows_pending_requests(): void
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
        $response->assertViewHas('pendingLeave');
        $response->assertSee('Pending Requests');
    }

    public function test_dashboard_shows_incoming_swap_requests(): void
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
        $response->assertViewHas('incomingSwaps', 1);
        $response->assertSee('Incoming Swap Request');
    }
}
