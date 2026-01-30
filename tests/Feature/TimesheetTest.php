<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Department;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetTest extends TestCase
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

    public function test_admin_can_view_timesheets_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.index'));

        $response->assertOk();
        $response->assertViewIs('timesheets.index');
        $response->assertViewHas('weekStart');
        $response->assertViewHas('weekEnd');
    }

    public function test_employee_can_view_their_own_timesheet(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.employee'));

        $response->assertOk();
        $response->assertViewIs('timesheets.employee');
        $response->assertViewHas('weeklyTotals');
    }

    public function test_admin_sees_all_employee_timesheets(): void
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
            'clock_in_at' => now()->startOfWeek()->setTime(9, 0),
            'clock_out_at' => now()->startOfWeek()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.index'));

        $response->assertOk();
        $response->assertViewHas('groupedByUser', function ($grouped) {
            return $grouped->has($this->employee->id);
        });
    }

    public function test_employee_only_sees_own_timesheet_in_index(): void
    {
        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherEmployee->id,
            'system_role' => SystemRole::Employee,
        ]);

        // Create time entry for other employee
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherEmployee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherEmployee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->startOfWeek()->setTime(9, 0),
            'clock_out_at' => now()->startOfWeek()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.index'));

        $response->assertOk();
        $response->assertViewHas('groupedByUser', function ($grouped) use ($otherEmployee) {
            return ! $grouped->has($otherEmployee->id);
        });
    }

    public function test_admin_can_filter_by_employee(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.index', ['user_id' => $this->employee->id]));

        $response->assertOk();
    }

    public function test_admin_can_filter_by_department(): void
    {
        $department = Department::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.index', ['department_id' => $department->id]));

        $response->assertOk();
    }

    public function test_admin_can_navigate_to_previous_week(): void
    {
        $prevWeek = now()->subWeek()->startOfWeek()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.index', ['week_start' => $prevWeek]));

        $response->assertOk();
        $response->assertViewHas('weekStart', function ($weekStart) use ($prevWeek) {
            return $weekStart->format('Y-m-d') === $prevWeek;
        });
    }

    public function test_employee_can_navigate_to_previous_week(): void
    {
        $prevWeek = now()->subWeek()->startOfWeek()->format('Y-m-d');

        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.employee', ['week_start' => $prevWeek]));

        $response->assertOk();
        $response->assertViewHas('weekStart', function ($weekStart) use ($prevWeek) {
            return $weekStart->format('Y-m-d') === $prevWeek;
        });
    }

    public function test_admin_can_approve_multiple_entries(): void
    {
        $this->settings->update(['require_manager_approval' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $entry1 = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->startOfWeek()->setTime(9, 0),
            'clock_out_at' => now()->startOfWeek()->setTime(17, 0),
        ]);

        $entry2 = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->startOfWeek()->addDay()->setTime(9, 0),
            'clock_out_at' => now()->startOfWeek()->addDay()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('timesheets.approve-multiple'), [
                'entry_ids' => [$entry1->id, $entry2->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertNotNull($entry1->fresh()->approved_at);
        $this->assertNotNull($entry2->fresh()->approved_at);
    }

    public function test_employee_cannot_approve_entries(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $entry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('timesheets.approve-multiple'), [
                'entry_ids' => [$entry->id],
            ]);

        $response->assertForbidden();
    }

    public function test_approve_multiple_with_no_entries_selected(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('timesheets.approve-multiple'), [
                'entry_ids' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_weekly_totals_are_calculated_correctly(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => now()->startOfWeek(),
            'start_time' => now()->startOfWeek()->setTime(9, 0),
            'end_time' => now()->startOfWeek()->setTime(17, 0),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->startOfWeek()->setTime(9, 0),
            'clock_out_at' => now()->startOfWeek()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.employee'));

        $response->assertOk();
        $response->assertViewHas('weeklyTotals', function ($totals) {
            return $totals['entry_count'] === 1 && $totals['actual_hours'] > 0;
        });
    }

    public function test_timesheet_shows_pending_approval_count(): void
    {
        $this->settings->update(['require_manager_approval' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => now()->startOfWeek(),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->startOfWeek()->setTime(9, 0),
            'clock_out_at' => now()->startOfWeek()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.employee'));

        $response->assertOk();
        $response->assertViewHas('weeklyTotals', function ($totals) {
            return $totals['pending_approval'] === 1;
        });
    }

    public function test_cannot_access_other_tenant_timesheets(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin,
        ]);

        // Create time entry in first tenant
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(17, 0),
        ]);

        // Other tenant admin should not see this tenant's entries
        $response = $this->actingAs($otherAdmin)
            ->get(route('timesheets.index'));

        $response->assertOk();
        $response->assertViewHas('timeEntries', function ($entries) {
            return $entries->isEmpty();
        });
    }
}
