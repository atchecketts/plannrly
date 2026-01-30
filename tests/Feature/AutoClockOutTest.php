<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Enums\TimeEntryStatus;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoClockOutTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private TenantSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();

        // Feature 3.6 Auto Clock-Out is deferred - skip these tests until implemented
        $this->markTestSkipped('Auto Clock-Out feature (Phase 3.6) is deferred.');

        $this->tenant = Tenant::factory()->create();

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin,
        ]);

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee,
        ]);

        $this->settings = TenantSettings::where('tenant_id', $this->tenant->id)->first();
        $this->settings->update([
            'enable_clock_in_out' => true,
            'auto_clock_out_enabled' => true,
            'auto_clock_out_time' => '23:00:00',
        ]);
    }

    public function test_command_auto_clocks_out_active_entries_past_configured_time(): void
    {
        // Freeze time to 23:30
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        // Create an active clock-in entry
        $entry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry->refresh();

        $this->assertEquals(TimeEntryStatus::AutoClockedOut, $entry->status);
        $this->assertNotNull($entry->clock_out_at);
        $this->assertEquals('23:00:00', $entry->clock_out_at->format('H:i:s'));
        $this->assertStringContainsString('Automatically clocked out', $entry->notes);

        Carbon::setTestNow();
    }

    public function test_command_does_not_run_before_configured_time(): void
    {
        // Freeze time to 22:30 (before 23:00 auto clock-out time)
        Carbon::setTestNow(Carbon::today()->setTime(22, 30));

        $entry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry->refresh();

        // Entry should still be clocked in
        $this->assertEquals(TimeEntryStatus::ClockedIn, $entry->status);
        $this->assertNull($entry->clock_out_at);

        Carbon::setTestNow();
    }

    public function test_command_does_not_process_tenants_without_auto_clock_out_enabled(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        $this->settings->update(['auto_clock_out_enabled' => false]);

        $entry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry->refresh();

        $this->assertEquals(TimeEntryStatus::ClockedIn, $entry->status);
        $this->assertNull($entry->clock_out_at);

        Carbon::setTestNow();
    }

    public function test_command_does_not_process_tenants_without_clock_in_enabled(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        $this->settings->update([
            'enable_clock_in_out' => false,
            'auto_clock_out_enabled' => true,
        ]);

        $entry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry->refresh();

        $this->assertEquals(TimeEntryStatus::ClockedIn, $entry->status);
        $this->assertNull($entry->clock_out_at);

        Carbon::setTestNow();
    }

    public function test_command_also_ends_active_break(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        $entry = TimeEntry::factory()->onBreak()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
            'break_start_at' => Carbon::today()->setTime(22, 45),
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry->refresh();

        $this->assertEquals(TimeEntryStatus::AutoClockedOut, $entry->status);
        $this->assertNotNull($entry->break_end_at);
        $this->assertEquals('23:00:00', $entry->break_end_at->format('H:i:s'));

        Carbon::setTestNow();
    }

    public function test_command_does_not_affect_already_clocked_out_entries(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        $entry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
            'clock_out_at' => Carbon::today()->setTime(17, 0),
        ]);

        $originalClockOut = $entry->clock_out_at->format('H:i:s');

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry->refresh();

        $this->assertEquals(TimeEntryStatus::ClockedOut, $entry->status);
        $this->assertEquals($originalClockOut, $entry->clock_out_at->format('H:i:s'));

        Carbon::setTestNow();
    }

    public function test_command_processes_multiple_entries(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        $entry1 = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $anotherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $entry2 = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $anotherEmployee->id,
            'clock_in_at' => Carbon::today()->setTime(10, 0),
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry1->refresh();
        $entry2->refresh();

        $this->assertEquals(TimeEntryStatus::AutoClockedOut, $entry1->status);
        $this->assertEquals(TimeEntryStatus::AutoClockedOut, $entry2->status);

        Carbon::setTestNow();
    }

    public function test_tenant_isolation_for_auto_clock_out(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        // Create another tenant without auto clock-out enabled
        $otherTenant = Tenant::factory()->create();
        TenantSettings::where('tenant_id', $otherTenant->id)->update([
            'enable_clock_in_out' => true,
            'auto_clock_out_enabled' => false,
        ]);

        $otherEmployee = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $ourEntry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $theirEntry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $otherEmployee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $ourEntry->refresh();
        $theirEntry->refresh();

        // Our entry should be auto clocked out
        $this->assertEquals(TimeEntryStatus::AutoClockedOut, $ourEntry->status);

        // Their entry should still be clocked in
        $this->assertEquals(TimeEntryStatus::ClockedIn, $theirEntry->status);

        Carbon::setTestNow();
    }

    public function test_auto_clocked_out_status_has_correct_label(): void
    {
        $this->assertEquals('Auto Clocked Out', TimeEntryStatus::AutoClockedOut->label());
    }

    public function test_auto_clocked_out_status_has_correct_color(): void
    {
        $this->assertEquals('orange', TimeEntryStatus::AutoClockedOut->color());
    }

    public function test_auto_clocked_out_is_not_active(): void
    {
        $this->assertFalse(TimeEntryStatus::AutoClockedOut->isActive());
    }

    public function test_appends_note_to_existing_notes(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(23, 30));

        $entry = TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'clock_in_at' => Carbon::today()->setTime(9, 0),
            'notes' => 'Employee note: Running late today.',
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertSuccessful();

        $entry->refresh();

        $this->assertStringContainsString('Employee note: Running late today.', $entry->notes);
        $this->assertStringContainsString('Automatically clocked out', $entry->notes);

        Carbon::setTestNow();
    }
}
