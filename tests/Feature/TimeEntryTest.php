<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private TenantSettings $settings;

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
        $this->settings->update(['enable_clock_in_out' => true]);
    }

    public function test_admin_can_view_all_time_entries(): void
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

        $response = $this->actingAs($this->admin)
            ->get(route('time-entries.index'));

        $response->assertStatus(200);
        $response->assertSee($this->employee->full_name);
    }

    public function test_admin_can_view_employee_time_entry(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('time-entries.show', $timeEntry));

        $response->assertStatus(200);
        $response->assertSee('Time Entry Details');
    }

    public function test_admin_can_adjust_time_entry(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now(),
        ]);

        $newClockIn = now()->subHours(9)->format('Y-m-d\TH:i');
        $newClockOut = now()->format('Y-m-d\TH:i');

        $response = $this->actingAs($this->admin)
            ->put(route('time-entries.adjust', $timeEntry), [
                'adjustment_reason' => 'Employee forgot to clock in on time, adjusting based on manager observation.',
                'clock_in_at' => $newClockIn,
                'clock_out_at' => $newClockOut,
                'actual_break_minutes' => 45,
            ]);

        $response->assertRedirect(route('time-entries.show', $timeEntry));
        $response->assertSessionHas('success');

        $timeEntry->refresh();
        $this->assertEquals(45, $timeEntry->actual_break_minutes);
        $this->assertNotNull($timeEntry->adjustment_reason);
    }

    public function test_adjustment_requires_minimum_reason_length(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('time-entries.adjust', $timeEntry), [
                'adjustment_reason' => 'Short',
            ]);

        $response->assertSessionHasErrors(['adjustment_reason']);
    }

    public function test_admin_can_approve_time_entry(): void
    {
        $this->settings->update(['require_manager_approval' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->pendingApproval()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('time-entries.approve', $timeEntry));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $timeEntry->refresh();
        $this->assertTrue($timeEntry->isApproved());
        $this->assertEquals($this->admin->id, $timeEntry->approved_by);
    }

    public function test_employee_cannot_approve_time_entry(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->pendingApproval()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('time-entries.approve', $timeEntry));

        $response->assertStatus(403);
    }

    public function test_employee_cannot_adjust_time_entry(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->put(route('time-entries.adjust', $timeEntry), [
                'adjustment_reason' => 'Employee trying to adjust their own time entry.',
            ]);

        $response->assertStatus(403);
    }

    public function test_tenant_isolation_prevents_cross_tenant_access(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherShift = Shift::factory()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $otherUser->id,
            'date' => today(),
        ]);

        $otherTimeEntry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $otherUser->id,
            'shift_id' => $otherShift->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('time-entries.show', $otherTimeEntry));

        // Tenant scope filters out the entry, so it returns 404 (not found) rather than 403 (forbidden)
        // This is the correct security behavior - the entry doesn't exist for this tenant
        $response->assertStatus(404);
    }

    public function test_cannot_approve_already_approved_entry(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->approved($this->admin)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('time-entries.approve', $timeEntry));

        $response->assertStatus(403);
    }

    public function test_time_entry_requires_approval_when_setting_enabled(): void
    {
        $this->settings->update(['require_manager_approval' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->pendingApproval()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $this->assertTrue($timeEntry->requiresApproval());
    }

    public function test_time_entry_does_not_require_approval_when_setting_disabled(): void
    {
        $this->settings->update(['require_manager_approval' => false]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $timeEntry = TimeEntry::factory()->pendingApproval()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
        ]);

        $this->assertFalse($timeEntry->requiresApproval());
    }

    public function test_filter_pending_approval_time_entries(): void
    {
        $this->settings->update(['require_manager_approval' => true]);

        $shift1 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $shift2 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today()->subDay(),
        ]);

        TimeEntry::factory()->pendingApproval()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift1->id,
        ]);

        TimeEntry::factory()->approved($this->admin)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift2->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('time-entries.index', ['pending_approval' => 1]));

        $response->assertStatus(200);
    }
}
