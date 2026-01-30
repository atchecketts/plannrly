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
use Tests\TestCase;

class ShiftCopyPasteTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private User $departmentAdmin;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->location = Location::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);
        $this->businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);
        $this->employee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);

        $this->departmentAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->departmentAdmin->id,
            'system_role' => SystemRole::DepartmentAdmin->value,
            'department_id' => $this->department->id,
        ]);
    }

    public function test_admin_can_paste_single_shift(): void
    {
        $this->actingAs($this->admin);

        $targetDate = now()->addDay()->format('Y-m-d');

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                    'break_duration_minutes' => 30,
                    'notes' => 'Test shift',
                ],
            ],
            'target_date' => $targetDate,
            'target_user_id' => $this->employee->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'created_count' => 1,
            ]);

        $this->assertDatabaseHas('shifts', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date' => $targetDate.' 00:00:00',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Draft->value,
        ]);
    }

    public function test_pasted_shifts_are_created_as_draft(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '10:00',
                    'end_time' => '18:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDays(2)->format('Y-m-d'),
            'target_user_id' => null,
        ]);

        $response->assertOk();

        $shift = Shift::latest()->first();
        $this->assertEquals(ShiftStatus::Draft, $shift->status);
    }

    public function test_paste_multiple_shifts_for_day_copy(): void
    {
        $this->actingAs($this->admin);

        $targetDate = now()->addDays(3)->format('Y-m-d');

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '06:00',
                    'end_time' => '14:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
                [
                    'start_time' => '14:00',
                    'end_time' => '22:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => $targetDate,
            'target_user_id' => null,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'created_count' => 2,
            ]);

        $this->assertDatabaseCount('shifts', 2);
    }

    public function test_paste_validates_role_compatibility(): void
    {
        $this->actingAs($this->admin);

        // Create a different role that the employee doesn't have
        $otherRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $otherRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => $this->employee->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_paste_detects_overlapping_shifts(): void
    {
        $this->actingAs($this->admin);

        $targetDate = now()->addDay()->format('Y-m-d');

        // Create an existing shift
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => $targetDate,
            'start_time' => '10:00',
            'end_time' => '16:00',
        ]);

        // Try to paste an overlapping shift
        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => $targetDate,
            'target_user_id' => $this->employee->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('shifts', 1); // Only the original shift exists
    }

    public function test_paste_to_different_date_works(): void
    {
        $this->actingAs($this->admin);

        $sourceDate = now()->format('Y-m-d');
        $targetDate = now()->addWeek()->format('Y-m-d');

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '08:00',
                    'end_time' => '16:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => $targetDate,
            'target_user_id' => $this->employee->id,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('shifts', [
            'date' => $targetDate.' 00:00:00',
            'user_id' => $this->employee->id,
        ]);
    }

    public function test_paste_to_different_user_works(): void
    {
        $this->actingAs($this->admin);

        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherEmployee->id,
            'system_role' => SystemRole::Employee->value,
        ]);
        $otherEmployee->businessRoles()->attach($this->businessRole->id);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => $otherEmployee->id,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('shifts', [
            'user_id' => $otherEmployee->id,
        ]);
    }

    public function test_employee_cannot_paste_shifts(): void
    {
        $this->actingAs($this->employee);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => $this->employee->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_department_admin_can_paste_to_their_department(): void
    {
        $this->actingAs($this->departmentAdmin);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => $this->employee->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('shifts', 1);
    }

    public function test_department_admin_cannot_paste_to_other_department(): void
    {
        $this->actingAs($this->departmentAdmin);

        $otherDepartment = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $otherRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $otherDepartment->id,
        ]);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $otherRole->id,
                    'department_id' => $otherDepartment->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => null,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_tenant_isolation_enforced(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $otherLocation = Location::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherDept = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);
        $otherRole = BusinessRole::factory()->create([
            'tenant_id' => $otherTenant->id,
            'department_id' => $otherDept->id,
        ]);

        $this->actingAs($otherAdmin);

        // Try to paste a shift using the original tenant's IDs
        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => null,
        ]);

        $response->assertStatus(422);
    }

    public function test_paste_unassigned_shift(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => null,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('shifts', [
            'user_id' => null,
            'status' => ShiftStatus::Draft->value,
        ]);
    }

    public function test_paste_requires_at_least_one_shift(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shifts']);
    }

    public function test_paste_requires_target_date(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_user_id' => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_date']);
    }

    public function test_paste_validates_shift_times(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => 'invalid',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shifts.0.start_time']);
    }

    public function test_paste_detects_overlaps_between_pasted_shifts(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('shifts.paste'), [
            'shifts' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
                [
                    'start_time' => '10:00',
                    'end_time' => '18:00',
                    'business_role_id' => $this->businessRole->id,
                    'department_id' => $this->department->id,
                    'location_id' => $this->location->id,
                ],
            ],
            'target_date' => now()->addDay()->format('Y-m-d'),
            'target_user_id' => $this->employee->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('shifts', 0);
    }
}
