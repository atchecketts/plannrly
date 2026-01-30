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

class ClockWidgetAjaxTest extends TestCase
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

    public function test_clock_in_returns_json_for_ajax_request(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'time_entry' => [
                    'id',
                    'clock_in_at',
                    'clock_in_timestamp',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'status' => TimeEntryStatus::ClockedIn->value,
        ]);
    }

    public function test_clock_in_returns_json_error_when_already_clocked_in(): void
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
            ->postJson(route('time-entries.clock-in'), [
                'shift_id' => $secondShift->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'You are already clocked in.',
            ]);
    }

    public function test_clock_in_returns_json_error_when_feature_disabled(): void
    {
        $this->settings->update(['enable_clock_in_out' => false]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Clock in/out is not enabled for your organization.',
            ]);
    }

    public function test_clock_out_returns_json_for_ajax_request(): void
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
            ->postJson(route('time-entries.clock-out', $timeEntry));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'time_entry' => [
                    'id',
                    'clock_out_at',
                    'total_worked_hours',
                    'status',
                ],
            ]);

        $timeEntry->refresh();
        $this->assertTrue($timeEntry->isClockedOut());
    }

    public function test_start_break_returns_json_for_ajax_request(): void
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
            ->postJson(route('time-entries.start-break', $timeEntry));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'time_entry' => [
                    'id',
                    'break_start_at',
                    'status',
                ],
            ]);

        $timeEntry->refresh();
        $this->assertTrue($timeEntry->isOnBreak());
    }

    public function test_end_break_returns_json_for_ajax_request(): void
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
            ->postJson(route('time-entries.end-break', $timeEntry));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'time_entry' => [
                    'id',
                    'break_end_at',
                    'actual_break_minutes',
                    'status',
                ],
            ]);

        $timeEntry->refresh();
        $this->assertTrue($timeEntry->isClockedIn());
    }

    public function test_clock_in_with_gps_returns_json(): void
    {
        $this->settings->update(['require_gps_clock_in' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson(route('time-entries.clock-in'), [
                'shift_id' => $shift->id,
                'location' => [
                    'lat' => 51.5074,
                    'lng' => -0.1278,
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $timeEntry = TimeEntry::where('shift_id', $shift->id)->first();
        $this->assertNotNull($timeEntry->clock_in_location);
        $this->assertEquals(51.5074, $timeEntry->clock_in_location['lat']);
    }
}
