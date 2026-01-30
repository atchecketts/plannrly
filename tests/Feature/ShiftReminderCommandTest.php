<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\User;
use App\Notifications\ShiftReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ShiftReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $employee;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

    private TenantSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['is_active' => true]);
        $this->settings = $this->tenant->tenantSettings;
        $this->settings->update([
            'timezone' => 'UTC',
            'enable_shift_reminders' => true,
            'remind_day_before' => true,
            'remind_hours_before' => true,
            'remind_hours_before_value' => 1,
        ]);

        $this->employee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $this->location = Location::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $this->department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        $this->businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);

        $this->employee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);
    }

    public function test_sends_day_before_reminder(): void
    {
        Notification::fake();

        // Create shift for tomorrow
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay()->startOfDay(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->artisan('shifts:send-reminders')
            ->assertExitCode(0);

        Notification::assertSentTo($this->employee, ShiftReminderNotification::class, function ($notification) {
            return $notification->reminderType === 'day_before';
        });

        // Verify reminder_sent_at was updated
        $shift->refresh();
        $this->assertNotNull($shift->reminder_sent_at);
    }

    public function test_does_not_send_duplicate_day_before_reminder(): void
    {
        Notification::fake();

        // Create shift for tomorrow with reminder already sent
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay()->startOfDay(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'reminder_sent_at' => now()->subHour(),
        ]);

        $this->artisan('shifts:send-reminders')
            ->assertExitCode(0);

        Notification::assertNotSentTo($this->employee, ShiftReminderNotification::class);
    }

    public function test_does_not_send_reminder_for_draft_shifts(): void
    {
        Notification::fake();

        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Draft,
            'date' => now()->addDay()->startOfDay(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->artisan('shifts:send-reminders')
            ->assertExitCode(0);

        Notification::assertNotSentTo($this->employee, ShiftReminderNotification::class);
    }

    public function test_does_not_send_reminder_for_unassigned_shifts(): void
    {
        Notification::fake();

        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => null,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay()->startOfDay(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->artisan('shifts:send-reminders')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_does_not_send_reminder_when_disabled(): void
    {
        Notification::fake();

        $this->settings->update(['enable_shift_reminders' => false]);

        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay()->startOfDay(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->artisan('shifts:send-reminders')
            ->assertExitCode(0);

        Notification::assertNotSentTo($this->employee, ShiftReminderNotification::class);
    }

    public function test_does_not_send_reminder_for_inactive_tenant(): void
    {
        Notification::fake();

        $this->tenant->update(['is_active' => false]);

        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay()->startOfDay(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->artisan('shifts:send-reminders')
            ->assertExitCode(0);

        Notification::assertNotSentTo($this->employee, ShiftReminderNotification::class);
    }
}
