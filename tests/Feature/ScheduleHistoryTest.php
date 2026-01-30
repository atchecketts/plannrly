<?php

namespace Tests\Feature;

use App\Enums\ScheduleHistoryAction;
use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\ScheduleHistory;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

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
            'system_role' => SystemRole::Admin->value,
        ]);

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

    public function test_creating_shift_logs_history(): void
    {
        $this->actingAs($this->admin);

        $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->assertDatabaseHas('schedule_history', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'action' => ScheduleHistoryAction::Created->value,
        ]);

        $history = ScheduleHistory::first();
        $this->assertNotNull($history->new_values);
        $this->assertArrayHasKey('date', $history->new_values);
        $this->assertArrayHasKey('start_time', $history->new_values);
    }

    public function test_updating_shift_logs_history_with_changes(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Clear the creation history
        ScheduleHistory::query()->delete();

        $this->putJson(route('shifts.update', $shift), [
            'date' => '2024-01-16',
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);

        $this->assertDatabaseHas('schedule_history', [
            'tenant_id' => $this->tenant->id,
            'shift_id' => $shift->id,
            'user_id' => $this->admin->id,
            'action' => ScheduleHistoryAction::Updated->value,
        ]);

        $history = ScheduleHistory::where('action', ScheduleHistoryAction::Updated->value)->first();
        $this->assertNotNull($history->old_values);
        $this->assertNotNull($history->new_values);
    }

    public function test_deleting_shift_logs_history(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
        ]);

        // Clear the creation history
        ScheduleHistory::query()->delete();

        $this->deleteJson(route('shifts.destroy', $shift));

        $this->assertDatabaseHas('schedule_history', [
            'tenant_id' => $this->tenant->id,
            'shift_id' => $shift->id,
            'user_id' => $this->admin->id,
            'action' => ScheduleHistoryAction::Deleted->value,
        ]);

        $history = ScheduleHistory::where('action', ScheduleHistoryAction::Deleted->value)->first();
        $this->assertNotNull($history->old_values);
        $this->assertNull($history->new_values);
    }

    public function test_admin_can_view_schedule_history(): void
    {
        $this->actingAs($this->admin);

        // Create some history entries
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        ScheduleHistory::factory()->byUser($this->admin)->forShift($shift)->create();

        $response = $this->get(route('schedule.history'));

        $response->assertOk();
        $response->assertViewIs('schedule.history');
        $response->assertViewHas('history');
    }

    public function test_employee_cannot_view_schedule_history(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('schedule.history'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_shift_history(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        ScheduleHistory::factory()->byUser($this->admin)->forShift($shift)->create();

        $response = $this->get(route('schedule.history.shift', $shift));

        $response->assertOk();
        $response->assertViewIs('schedule.shift-history');
        $response->assertViewHas('shift');
        $response->assertViewHas('history');
    }

    public function test_history_filters_by_date_range(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        // Clear observer-created history to set up controlled test scenario
        ScheduleHistory::query()->delete();

        // Create old history (outside the filter range)
        ScheduleHistory::factory()->byUser($this->admin)->forShift($shift)->create([
            'created_at' => now()->subDays(60),
        ]);

        // Create recent history
        ScheduleHistory::factory()->byUser($this->admin)->forShift($shift)->create([
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->get(route('schedule.history', [
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $history = $response->viewData('history');
        $this->assertEquals(1, $history->count());
    }

    public function test_history_filters_by_action(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        // Clear observer-created history to set up controlled test scenario
        ScheduleHistory::query()->delete();

        ScheduleHistory::factory()->byUser($this->admin)->forShift($shift)->created()->create();
        ScheduleHistory::factory()->byUser($this->admin)->forShift($shift)->updated()->create();

        $response = $this->get(route('schedule.history', [
            'action' => 'created',
        ]));

        $response->assertOk();
        $history = $response->viewData('history');
        $this->assertEquals(1, $history->count());
        $this->assertEquals(ScheduleHistoryAction::Created, $history->first()->action);
    }

    public function test_schedule_history_model_get_change_summary(): void
    {
        $history = new ScheduleHistory([
            'action' => ScheduleHistoryAction::Updated,
            'old_values' => ['start_time' => '09:00', 'end_time' => '17:00'],
            'new_values' => ['start_time' => '10:00', 'end_time' => '18:00'],
        ]);

        $summary = $history->getChangeSummary();

        $this->assertContains('Start time changed', $summary);
        $this->assertContains('End time changed', $summary);
    }

    public function test_schedule_history_model_get_change_summary_for_created(): void
    {
        $history = new ScheduleHistory([
            'action' => ScheduleHistoryAction::Created,
            'old_values' => null,
            'new_values' => ['date' => '2024-01-15'],
        ]);

        $summary = $history->getChangeSummary();

        $this->assertEquals(['Shift created'], $summary);
    }

    public function test_schedule_history_model_get_change_summary_for_deleted(): void
    {
        $history = new ScheduleHistory([
            'action' => ScheduleHistoryAction::Deleted,
            'old_values' => ['date' => '2024-01-15'],
            'new_values' => null,
        ]);

        $summary = $history->getChangeSummary();

        $this->assertEquals(['Shift deleted'], $summary);
    }

    public function test_history_is_tenant_scoped(): void
    {
        // Create another tenant with history
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin->value,
        ]);
        $otherLocation = Location::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);
        $otherRole = BusinessRole::factory()->create([
            'tenant_id' => $otherTenant->id,
            'department_id' => $otherDepartment->id,
        ]);
        $otherShift = Shift::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
            'department_id' => $otherDepartment->id,
            'business_role_id' => $otherRole->id,
        ]);

        ScheduleHistory::factory()->create([
            'tenant_id' => $otherTenant->id,
            'shift_id' => $otherShift->id,
            'user_id' => $otherAdmin->id,
        ]);

        // Create history for our tenant
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);
        ScheduleHistory::factory()->byUser($this->admin)->forShift($shift)->create();

        $this->actingAs($this->admin);
        $response = $this->get(route('schedule.history'));

        $response->assertOk();
        $history = $response->viewData('history');

        // Should only see our tenant's history
        $this->assertEquals(1, $history->count());
        $this->assertEquals($this->tenant->id, $history->first()->tenant_id);
    }

    public function test_shift_has_history_relationship(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        // The observer should have created a history entry
        $this->assertCount(1, $shift->history);
        $this->assertInstanceOf(ScheduleHistory::class, $shift->history->first());
    }
}
