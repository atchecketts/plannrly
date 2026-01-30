<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Enums\SystemRole;
use App\Enums\TimeEntryStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Notifications\MissedShiftNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MissedShiftDetectionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private TenantSettings $settings;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

    protected function setUp(): void
    {
        parent::setUp();

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
            'missed_grace_minutes' => 15,
            'timezone' => 'UTC',
        ]);

        // Create related entities in the same tenant
        $this->location = Location::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);
        $this->businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);
    }

    public function test_command_creates_missed_time_entry_for_shift_past_grace_period(): void
    {
        Notification::fake();

        // Create a shift that started 30 minutes ago (past 15 min grace)
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(7)->subMinutes(30),
            'status' => ShiftStatus::Published,
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        $this->assertDatabaseHas('time_entries', [
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'status' => TimeEntryStatus::Missed->value,
        ]);
    }

    public function test_command_does_not_create_entry_for_shift_within_grace_period(): void
    {
        // Create a shift that started 5 minutes ago (within 15 min grace)
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHours(8)->subMinutes(5),
            'status' => ShiftStatus::Published,
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        $this->assertDatabaseMissing('time_entries', [
            'shift_id' => $shift->id,
        ]);
    }

    public function test_command_does_not_create_entry_for_shift_with_existing_time_entry(): void
    {
        // Create a shift that started 30 minutes ago
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(7)->subMinutes(30),
            'status' => ShiftStatus::Published,
        ]);

        // Employee already clocked in
        TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subMinutes(25),
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        // Should only have the one existing entry
        $this->assertEquals(1, TimeEntry::where('shift_id', $shift->id)->count());
    }

    public function test_command_does_not_process_tenants_without_clock_in_enabled(): void
    {
        $this->settings->update(['enable_clock_in_out' => false]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(7)->subMinutes(30),
            'status' => ShiftStatus::Published,
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        $this->assertDatabaseMissing('time_entries', [
            'shift_id' => $shift->id,
        ]);
    }

    public function test_command_sends_notification_to_admin(): void
    {
        Notification::fake();

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(7)->subMinutes(30),
            'status' => ShiftStatus::Published,
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        Notification::assertSentTo($this->admin, MissedShiftNotification::class);
    }

    public function test_command_does_not_process_unassigned_shifts(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => null, // Unassigned
            'date' => today(),
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(7)->subMinutes(30),
            'status' => ShiftStatus::Published,
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        $this->assertDatabaseMissing('time_entries', [
            'shift_id' => $shift->id,
        ]);
    }

    public function test_command_does_not_process_draft_shifts(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(7)->subMinutes(30),
            'status' => ShiftStatus::Draft,
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        $this->assertDatabaseMissing('time_entries', [
            'shift_id' => $shift->id,
        ]);
    }

    public function test_time_entry_is_missed_method_works(): void
    {
        $entry = TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'status' => TimeEntryStatus::Missed,
        ]);

        $this->assertTrue($entry->isMissed());
    }

    public function test_missed_scope_returns_missed_entries(): void
    {
        TimeEntry::factory()->clockedIn()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'status' => TimeEntryStatus::Missed,
        ]);

        $missed = TimeEntry::missed()->get();

        $this->assertCount(1, $missed);
        $this->assertEquals(TimeEntryStatus::Missed, $missed->first()->status);
    }

    public function test_admin_dashboard_shows_missed_shifts_count(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'status' => TimeEntryStatus::Missed,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return ($stats['missed_shifts_today'] ?? 0) === 1;
        });
    }

    public function test_employee_dashboard_shows_missed_shifts_count(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'status' => TimeEntryStatus::Missed,
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return ($stats['missed_shifts'] ?? 0) === 1;
        });
    }

    public function test_tenant_isolation_for_missed_shifts(): void
    {
        Notification::fake();

        $otherTenant = Tenant::factory()->create();
        TenantSettings::where('tenant_id', $otherTenant->id)->update([
            'enable_clock_in_out' => true,
            'timezone' => 'UTC',
        ]);

        $otherEmployee = User::factory()->create(['tenant_id' => $otherTenant->id]);

        // Create proper related models in the other tenant
        $otherLocation = Location::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);
        $otherBusinessRole = BusinessRole::factory()->create([
            'tenant_id' => $otherTenant->id,
            'department_id' => $otherDepartment->id,
        ]);

        // Create missed shift in other tenant
        $shift = Shift::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
            'department_id' => $otherDepartment->id,
            'business_role_id' => $otherBusinessRole->id,
            'user_id' => $otherEmployee->id,
            'date' => today(),
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(7)->subMinutes(30),
            'status' => ShiftStatus::Published,
        ]);

        $this->artisan('attendance:detect-missed-shifts')
            ->assertSuccessful();

        // Entry should be for other tenant
        $entry = TimeEntry::where('shift_id', $shift->id)->first();
        $this->assertNotNull($entry);
        $this->assertEquals($otherTenant->id, $entry->tenant_id);

        // Our admin should NOT have been notified
        Notification::assertNotSentTo($this->admin, MissedShiftNotification::class);
    }
}
