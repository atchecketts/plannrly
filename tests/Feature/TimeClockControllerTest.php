<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Enums\TimeEntryStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeClockControllerTest extends TestCase
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

    public function test_employee_can_view_time_clock_page(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('time-clock.index'));

        $response->assertOk();
        $response->assertViewIs('time-clock.index');
        $response->assertViewHas('todayShift');
        $response->assertViewHas('activeTimeEntry');
        $response->assertViewHas('todayWorkedMinutes');
    }

    public function test_employee_can_clock_in(): void
    {
        $this->actingAs($this->employee);

        // Create today's shift
        Shift::factory()->create([
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

        $response = $this->post(route('time-clock.clock-in'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->employee->id,
            'status' => TimeEntryStatus::ClockedIn->value,
        ]);
    }

    public function test_employee_cannot_clock_in_without_shift(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('time-clock.clock-in'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $this->employee->id,
        ]);
    }

    public function test_employee_cannot_clock_in_twice(): void
    {
        $this->actingAs($this->employee);

        // Create today's shift
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

        // Create existing time entry
        TimeEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subHours(2),
            'status' => TimeEntryStatus::ClockedIn,
        ]);

        $response = $this->post(route('time-clock.clock-in'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You are already clocked in.');
    }

    public function test_employee_can_clock_out(): void
    {
        $this->actingAs($this->employee);

        // Create today's shift with active time entry
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

        $response = $this->post(route('time-clock.clock-out'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->employee->id,
            'status' => TimeEntryStatus::ClockedOut->value,
        ]);
    }

    public function test_employee_cannot_clock_out_when_not_clocked_in(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('time-clock.clock-out'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You are not clocked in.');
    }

    public function test_employee_can_start_break(): void
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

        $response = $this->post(route('time-clock.start-break'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->employee->id,
            'status' => TimeEntryStatus::OnBreak->value,
        ]);
    }

    public function test_employee_can_end_break(): void
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
            'break_start_at' => now()->subMinutes(15),
            'status' => TimeEntryStatus::OnBreak,
        ]);

        $response = $this->post(route('time-clock.end-break'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->employee->id,
            'status' => TimeEntryStatus::ClockedIn->value,
        ]);
    }

    public function test_time_clock_api_returns_json(): void
    {
        $this->actingAs($this->employee);

        // Create today's shift
        Shift::factory()->create([
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

        $response = $this->postJson(route('time-clock.clock-in'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_unauthenticated_user_cannot_access_time_clock(): void
    {
        $response = $this->get(route('time-clock.index'));

        $response->assertRedirect(route('login'));
    }
}
