<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ShiftControllerTest extends TestCase
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

    public function test_admin_can_create_shift(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('shifts', [
            'user_id' => $this->employee->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_admin_can_create_unassigned_shift(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => null,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('shifts', [
            'user_id' => null,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_admin_can_view_shift_details(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->getJson(route('shifts.show', $shift));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('shift.id', $shift->id);
    }

    public function test_admin_can_update_shift(): void
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

        $response = $this->putJson(route('shifts.update', $shift), [
            'date' => '2024-01-16',
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $shift->refresh();
        $this->assertEquals('2024-01-16', $shift->date->format('Y-m-d'));
    }

    public function test_admin_can_delete_shift(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $response = $this->deleteJson(route('shifts.destroy', $shift));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('shifts', ['id' => $shift->id]);
    }

    public function test_admin_can_assign_shift_to_employee(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => null,
        ]);

        $response = $this->postJson(route('shifts.assign', $shift), [
            'user_id' => $this->employee->id,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $shift->refresh();
        $this->assertEquals($this->employee->id, $shift->user_id);
    }

    public function test_admin_can_unassign_shift(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->postJson(route('shifts.assign', $shift), [
            'user_id' => null,
        ]);

        $response->assertOk();

        $shift->refresh();
        $this->assertNull($shift->user_id);
    }

    public function test_admin_can_get_available_users_for_shift(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $response = $this->getJson(route('shifts.available-users', $shift));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $this->employee->id]);
    }

    public function test_admin_can_publish_shift(): void
    {
        Notification::fake();

        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'status' => ShiftStatus::Draft,
        ]);

        $response = $this->postJson(route('shifts.publish', $shift));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $shift->refresh();
        $this->assertEquals(ShiftStatus::Published, $shift->status);
    }

    public function test_shift_creates_as_draft_by_default(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertOk();

        $shift = Shift::latest()->first();
        $this->assertEquals(ShiftStatus::Draft, $shift->status);
    }

    public function test_shift_records_created_by_user(): void
    {
        $this->actingAs($this->admin);

        $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $shift = Shift::latest()->first();
        $this->assertEquals($this->admin->id, $shift->created_by);
    }

    public function test_users_cannot_view_other_tenant_shifts(): void
    {
        $this->actingAs($this->admin);

        $otherTenant = Tenant::factory()->create();
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

        $response = $this->getJson(route('shifts.show', $otherShift));

        $response->assertStatus(404);
    }

    public function test_shift_with_break_duration(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 30,
        ]);

        $response->assertOk();

        $shift = Shift::latest()->first();
        $this->assertEquals(30, $shift->break_duration_minutes);
        $this->assertEquals(450, $shift->working_minutes); // 8 hours - 30 min break
    }

    public function test_shift_with_notes(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'notes' => 'Special instructions for this shift',
        ]);

        $response->assertOk();

        $shift = Shift::latest()->first();
        $this->assertEquals('Special instructions for this shift', $shift->notes);
    }
}
