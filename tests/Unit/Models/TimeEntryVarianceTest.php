<?php

namespace Tests\Unit\Models;

use App\Enums\ShiftStatus;
use App\Enums\TimeEntryStatus;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryVarianceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $employee;

    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create a shift from 9:00 AM to 5:00 PM (8 hours, 480 minutes working time with 30min break = 450min)
        $this->shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'break_duration_minutes' => 30,
            'status' => ShiftStatus::Published,
        ]);
    }

    public function test_scheduled_duration_minutes_returns_shift_working_minutes(): void
    {
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
        ]);

        // 8 hours (480 min) - 30 min break = 450 min
        $this->assertEquals(450, $timeEntry->scheduled_duration_minutes);
    }

    public function test_clock_in_variance_on_time(): void
    {
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0), // Exactly on time
        ]);

        $this->assertEquals(0, $timeEntry->clock_in_variance_minutes);
        $this->assertFalse($timeEntry->is_late);
        $this->assertEquals('green', $timeEntry->clock_in_status['color']);
        $this->assertEquals('On time', $timeEntry->clock_in_status['label']);
    }

    public function test_clock_in_variance_late_arrival(): void
    {
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 20), // 20 minutes late
        ]);

        $this->assertEquals(20, $timeEntry->clock_in_variance_minutes);
        $this->assertTrue($timeEntry->is_late);
        $this->assertEquals('red', $timeEntry->clock_in_status['color']);
        $this->assertStringContainsString('late', $timeEntry->clock_in_status['label']);
    }

    public function test_clock_in_variance_minor_late_within_grace(): void
    {
        // Default grace period is 15 minutes
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 10), // 10 minutes late (within grace)
        ]);

        $this->assertEquals(10, $timeEntry->clock_in_variance_minutes);
        $this->assertFalse($timeEntry->is_late); // Within grace period
        $this->assertEquals('green', $timeEntry->clock_in_status['color']);
    }

    public function test_clock_in_variance_early_arrival(): void
    {
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(8, 45), // 15 minutes early
        ]);

        $this->assertEquals(-15, $timeEntry->clock_in_variance_minutes);
        $this->assertFalse($timeEntry->is_late);
        $this->assertEquals('blue', $timeEntry->clock_in_status['color']);
        $this->assertStringContainsString('early', $timeEntry->clock_in_status['label']);
    }

    public function test_clock_out_variance_on_time(): void
    {
        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0), // Exactly on time
        ]);

        $this->assertEquals(0, $timeEntry->clock_out_variance_minutes);
        $this->assertFalse($timeEntry->is_early_departure);
        $this->assertEquals('green', $timeEntry->clock_out_status['color']);
    }

    public function test_clock_out_variance_early_departure(): void
    {
        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(16, 30), // 30 minutes early
        ]);

        $this->assertEquals(-30, $timeEntry->clock_out_variance_minutes);
        $this->assertTrue($timeEntry->is_early_departure);
        $this->assertEquals('red', $timeEntry->clock_out_status['color']);
    }

    public function test_clock_out_variance_overtime(): void
    {
        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(18, 0), // 1 hour overtime
        ]);

        $this->assertEquals(60, $timeEntry->clock_out_variance_minutes);
        $this->assertFalse($timeEntry->is_early_departure);
        $this->assertEquals('orange', $timeEntry->clock_out_status['color']);
        $this->assertStringContainsString('overtime', $timeEntry->clock_out_status['label']);
    }

    public function test_total_variance_overtime(): void
    {
        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(18, 0), // 9 hours total
            'actual_break_minutes' => 30,
        ]);

        // Worked: 9h - 30m break = 510 minutes
        // Scheduled: 450 minutes
        // Variance: +60 minutes
        $this->assertEquals(510, $timeEntry->total_worked_minutes);
        $this->assertEquals(60, $timeEntry->variance_minutes);
        $this->assertTrue($timeEntry->is_overtime);
    }

    public function test_total_variance_undertime(): void
    {
        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(16, 0), // 7 hours total
            'actual_break_minutes' => 30,
        ]);

        // Worked: 7h - 30m break = 390 minutes
        // Scheduled: 450 minutes
        // Variance: -60 minutes
        $this->assertEquals(390, $timeEntry->total_worked_minutes);
        $this->assertEquals(-60, $timeEntry->variance_minutes);
        $this->assertFalse($timeEntry->is_overtime);
    }

    public function test_is_no_show_when_shift_passed_without_clock_in(): void
    {
        // Freeze time to after the shift has ended
        Carbon::setTestNow(today()->setTime(18, 0));

        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => null,
            'status' => TimeEntryStatus::ClockedIn, // Created but never clocked in
        ]);

        $this->assertTrue($timeEntry->is_no_show);

        Carbon::setTestNow(); // Reset time
    }

    public function test_is_not_no_show_when_clocked_in(): void
    {
        $timeEntry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0),
        ]);

        $this->assertFalse($timeEntry->is_no_show);
    }

    public function test_format_variance_positive(): void
    {
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
        ]);

        $this->assertEquals('+30m', $timeEntry->formatVariance(30));
        $this->assertEquals('+1h 30m', $timeEntry->formatVariance(90));
        $this->assertEquals('+2h 0m', $timeEntry->formatVariance(120));
    }

    public function test_format_variance_negative(): void
    {
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
        ]);

        $this->assertEquals('-30m', $timeEntry->formatVariance(-30));
        $this->assertEquals('-1h 30m', $timeEntry->formatVariance(-90));
    }

    public function test_format_variance_zero(): void
    {
        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
        ]);

        $this->assertEquals('0m', $timeEntry->formatVariance(0));
    }

    public function test_variance_status_color_coding(): void
    {
        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
            'actual_break_minutes' => 30,
        ]);

        // On target (within 5 minutes)
        $this->assertEquals('green', $timeEntry->variance_status['color']);
    }

    public function test_grace_period_uses_tenant_settings(): void
    {
        // Update tenant settings with custom grace period
        $settings = TenantSettings::where('tenant_id', $this->tenant->id)->first();
        $settings->update(['clock_in_grace_minutes' => 5]);

        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => today()->setTime(9, 10), // 10 minutes late
        ]);

        // With 5 minute grace, 10 minutes late is considered late
        $this->assertTrue($timeEntry->is_late);
    }
}
