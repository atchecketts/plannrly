<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Enums\TimeEntryStatus;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInOutTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $employee;

    private TenantSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee,
        ]);

        $this->settings = TenantSettings::where('tenant_id', $this->tenant->id)->first();
        $this->settings->update(['enable_clock_in_out' => true]);
    }

    public function test_employee_can_clock_in_to_assigned_shift(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
            ]);

        $response->assertRedirect(route('time-entries.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'status' => TimeEntryStatus::ClockedIn->value,
        ]);
    }

    public function test_employee_cannot_clock_in_when_already_clocked_in(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $secondShift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.clock-in'), [
                'shift_id' => $secondShift->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You are already clocked in.');
    }

    public function test_employee_cannot_clock_in_to_unassigned_shift(): void
    {
        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherEmployee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
            ]);

        $response->assertSessionHasErrors(['shift_id']);
    }

    public function test_employee_cannot_clock_in_when_feature_disabled(): void
    {
        $this->settings->update(['enable_clock_in_out' => false]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Clock in/out is not enabled for your organization.');
    }

    public function test_employee_can_clock_out(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.clock-out', $timeEntry));

        $response->assertRedirect(route('time-entries.index'));
        $response->assertSessionHas('success');

        $timeEntry->refresh();
        $this->assertTrue($timeEntry->isClockedOut());
        $this->assertNotNull($timeEntry->clock_out_at);
    }

    public function test_employee_can_start_break(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.start-break', $timeEntry));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $timeEntry->refresh();
        $this->assertTrue($timeEntry->isOnBreak());
        $this->assertNotNull($timeEntry->break_start_at);
    }

    public function test_employee_can_end_break(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->onBreak()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.end-break', $timeEntry));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $timeEntry->refresh();
        $this->assertTrue($timeEntry->isClockedIn());
        $this->assertNotNull($timeEntry->break_end_at);
    }

    public function test_gps_required_validation_when_setting_enabled(): void
    {
        $this->settings->update(['require_gps_clock_in' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
            ]);

        $response->assertSessionHasErrors(['location']);
    }

    public function test_clock_in_with_gps_location(): void
    {
        $this->settings->update(['require_gps_clock_in' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
                'location' => [
                    'lat' => 51.5074,
                    'lng' => -0.1278,
                ],
            ]);

        $response->assertRedirect(route('time-entries.index'));

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $timeEntry = TimeEntry::where('shift_id', $shift->id)->first();
        $this->assertNotNull($timeEntry->clock_in_location);
        $this->assertEquals(51.5074, $timeEntry->clock_in_location['lat']);
    }

    public function test_employee_can_view_own_time_entries(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('time-entries.index'));

        $response->assertStatus(200);
        $response->assertSee('Time Entries');
    }

    public function test_employee_cannot_view_other_employees_time_entry(): void
    {
        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherEmployee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherEmployee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('time-entries.show', $timeEntry));

        $response->assertStatus(403);
    }
}
