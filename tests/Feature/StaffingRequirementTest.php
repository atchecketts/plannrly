<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\StaffingRequirement;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffingRequirementTest extends TestCase
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

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee->value,
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
    }

    public function test_admin_can_view_staffing_requirements_index(): void
    {
        $this->actingAs($this->admin);

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
        ]);

        $response = $this->get(route('staffing-requirements.index'));

        $response->assertOk();
        $response->assertViewIs('staffing-requirements.index');
    }

    public function test_employee_cannot_view_staffing_requirements_index(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('staffing-requirements.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('staffing-requirements.create'));

        $response->assertOk();
        $response->assertViewIs('staffing-requirements.create');
        $response->assertViewHas('businessRoles');
        $response->assertViewHas('daysOfWeek');
    }

    public function test_admin_can_create_staffing_requirement(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('staffing-requirements.store'), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 2,
            'max_employees' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('staffing-requirements.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('staffing_requirements', [
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
            'min_employees' => 2,
            'max_employees' => 5,
        ]);
    }

    public function test_admin_can_create_staffing_requirement_without_max(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('staffing-requirements.store'), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 3,
            'max_employees' => null,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('staffing-requirements.index'));

        $this->assertDatabaseHas('staffing_requirements', [
            'tenant_id' => $this->tenant->id,
            'min_employees' => 3,
            'max_employees' => null,
        ]);
    }

    public function test_employee_cannot_create_staffing_requirement(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('staffing-requirements.store'), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 2,
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_requires_business_role(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('staffing-requirements.store'), [
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 2,
        ]);

        $response->assertSessionHasErrors('business_role_id');
    }

    public function test_validation_requires_valid_day_of_week(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('staffing-requirements.store'), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 7, // Invalid: should be 0-6
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 2,
        ]);

        $response->assertSessionHasErrors('day_of_week');
    }

    public function test_validation_end_time_must_be_after_start_time(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('staffing-requirements.store'), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
            'start_time' => '17:00',
            'end_time' => '09:00', // Before start time
            'min_employees' => 2,
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_validation_max_employees_must_be_gte_min(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('staffing-requirements.store'), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 5,
            'max_employees' => 3, // Less than min
        ]);

        $response->assertSessionHasErrors('max_employees');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $this->actingAs($this->admin);

        $requirement = StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $response = $this->get(route('staffing-requirements.edit', $requirement));

        $response->assertOk();
        $response->assertViewIs('staffing-requirements.edit');
        $response->assertViewHas('staffingRequirement');
    }

    public function test_admin_can_update_staffing_requirement(): void
    {
        $this->actingAs($this->admin);

        $requirement = StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'min_employees' => 1,
        ]);

        $response = $this->put(route('staffing-requirements.update', $requirement), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 2,
            'start_time' => '08:00',
            'end_time' => '16:00',
            'min_employees' => 4,
            'max_employees' => 8,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('staffing-requirements.index'));
        $response->assertSessionHas('success');

        $requirement->refresh();
        $this->assertEquals(4, $requirement->min_employees);
        $this->assertEquals(8, $requirement->max_employees);
        $this->assertEquals(2, $requirement->day_of_week);
    }

    public function test_admin_can_delete_staffing_requirement(): void
    {
        $this->actingAs($this->admin);

        $requirement = StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $response = $this->delete(route('staffing-requirements.destroy', $requirement));

        $response->assertRedirect(route('staffing-requirements.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('staffing_requirements', ['id' => $requirement->id]);
    }

    public function test_employee_cannot_delete_staffing_requirement(): void
    {
        $this->actingAs($this->employee);

        $requirement = StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $response = $this->delete(route('staffing-requirements.destroy', $requirement));

        $response->assertStatus(403);
    }

    public function test_admin_from_different_tenant_cannot_update(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->actingAs($otherAdmin);

        $requirement = StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $response = $this->put(route('staffing-requirements.update', $requirement), [
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 99,
        ]);

        // Returns 404 (not 403) because tenant scoping hides resources from other tenants
        $response->assertStatus(404);
    }

    public function test_tenant_scoping_filters_requirements(): void
    {
        $this->actingAs($this->admin);

        // Create requirement for current tenant
        $ownRequirement = StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        // Create requirement for other tenant
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
        $otherRequirement = StaffingRequirement::factory()->create([
            'tenant_id' => $otherTenant->id,
            'business_role_id' => $otherRole->id,
        ]);

        $response = $this->get(route('staffing-requirements.index'));

        $response->assertOk();
        $response->assertViewHas('staffingRequirements', function ($requirements) use ($ownRequirement, $otherRequirement) {
            $ids = $requirements instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $requirements->pluck('id')
                : $requirements->pluck('id');

            return $ids->contains($ownRequirement->id) && ! $ids->contains($otherRequirement->id);
        });
    }

    public function test_index_shows_requirement_details(): void
    {
        $this->actingAs($this->admin);

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 1, // Monday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 2,
            'max_employees' => 5,
        ]);

        $response = $this->get(route('staffing-requirements.index'));

        $response->assertOk();
        $response->assertSee($this->businessRole->name);
        $response->assertSee('Monday');
        $response->assertSee('09:00 - 17:00');
        $response->assertSee('2 - 5');
    }
}
