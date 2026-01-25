<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftOverlapValidationTest extends TestCase
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

    public function test_can_create_shift_without_overlap(): void
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
        ]);
    }

    public function test_cannot_create_overlapping_shift(): void
    {
        $this->actingAs($this->admin);

        // Create existing shift 09:00 - 17:00
        $existingShift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Verify the existing shift was created
        $this->assertDatabaseCount('shifts', 1);
        $this->assertEquals($this->employee->id, $existingShift->user_id);
        $this->assertEquals($this->tenant->id, $existingShift->tenant_id);

        // Try to create overlapping shift 14:00 - 20:00
        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '14:00',
            'end_time' => '20:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }

    public function test_can_create_non_overlapping_shift_same_day(): void
    {
        $this->actingAs($this->admin);

        // Create existing shift 09:00 - 13:00
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '13:00',
        ]);

        // Create non-overlapping shift 14:00 - 18:00 (split shift)
        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '14:00',
            'end_time' => '18:00',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_adjacent_shifts(): void
    {
        $this->actingAs($this->admin);

        // Create existing shift 09:00 - 13:00
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '13:00',
        ]);

        // Create adjacent shift starting exactly when the other ends
        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '13:00',
            'end_time' => '17:00',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_cannot_update_shift_to_overlap(): void
    {
        $this->actingAs($this->admin);

        // Create two non-overlapping shifts
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '13:00',
        ]);

        $shiftToUpdate = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '14:00',
            'end_time' => '18:00',
        ]);

        // Try to update second shift to overlap with first
        $response = $this->putJson(route('shifts.update', $shiftToUpdate), [
            'date' => '2024-01-15',
            'start_time' => '12:00',
            'end_time' => '16:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }

    public function test_can_update_shift_without_changing_times(): void
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
            'notes' => null,
        ]);

        // Update notes without changing times (should not trigger overlap validation)
        $response = $this->putJson(route('shifts.update', $shift), [
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'notes' => 'Updated notes',
        ]);

        $response->assertSuccessful();
    }

    public function test_unassigned_shift_has_no_overlap_check(): void
    {
        $this->actingAs($this->admin);

        // Create existing shift for employee
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Create unassigned shift at same time (should be allowed)
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
        $response->assertJson(['success' => true]);
    }

    public function test_overlap_detection_with_overnight_shift(): void
    {
        $this->actingAs($this->admin);

        // Create overnight shift 22:00 - 06:00 (next day)
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '22:00',
            'end_time' => '06:00',
        ]);

        // Try to create shift that overlaps with overnight shift (23:00 - 02:00)
        $response = $this->postJson(route('shifts.store'), [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => '2024-01-15',
            'start_time' => '23:00',
            'end_time' => '02:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }
}
