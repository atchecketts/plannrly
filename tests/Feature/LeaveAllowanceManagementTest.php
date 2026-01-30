<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\LeaveAllowance;
use App\Models\LeaveType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveAllowanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private LeaveType $leaveType;

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

        $this->leaveType = LeaveType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'affects_allowance' => true,
        ]);
    }

    public function test_admin_can_view_leave_allowances_index(): void
    {
        $this->actingAs($this->admin);

        LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 25,
        ]);

        $response = $this->get(route('leave-allowances.index'));

        $response->assertOk();
        $response->assertViewIs('leave-allowances.index');
    }

    public function test_employee_cannot_view_leave_allowances_index(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('leave-allowances.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_filter_allowances_by_year(): void
    {
        $this->actingAs($this->admin);

        LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => 2025,
            'total_days' => 25,
        ]);

        LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => 2024,
            'total_days' => 20,
        ]);

        $response = $this->get(route('leave-allowances.index', ['year' => 2025]));

        $response->assertOk();
        $response->assertViewHas('allowances', function ($allowances) {
            return $allowances->count() === 1 && $allowances->first()->year === 2025;
        });
    }

    public function test_admin_can_view_create_leave_allowance_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('leave-allowances.create'));

        $response->assertOk();
        $response->assertViewIs('leave-allowances.create');
        $response->assertViewHas('users');
        $response->assertViewHas('leaveTypes');
    }

    public function test_admin_can_create_leave_allowance(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('leave-allowances.store'), [
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 25,
            'carried_over_days' => 5,
        ]);

        $response->assertRedirect(route('leave-allowances.index', ['year' => now()->year]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_allowances', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 25,
            'used_days' => 0,
            'carried_over_days' => 5,
        ]);
    }

    public function test_leave_allowance_requires_user(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('leave-allowances.store'), [
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 25,
        ]);

        $response->assertSessionHasErrors('user_id');
    }

    public function test_leave_allowance_requires_leave_type(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('leave-allowances.store'), [
            'user_id' => $this->employee->id,
            'year' => now()->year,
            'total_days' => 25,
        ]);

        $response->assertSessionHasErrors('leave_type_id');
    }

    public function test_cannot_create_duplicate_allowance(): void
    {
        $this->actingAs($this->admin);

        LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
        ]);

        $response = $this->post(route('leave-allowances.store'), [
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 25,
        ]);

        $response->assertSessionHasErrors('user_id');
    }

    public function test_admin_can_view_edit_leave_allowance_form(): void
    {
        $this->actingAs($this->admin);

        $allowance = LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $response = $this->get(route('leave-allowances.edit', $allowance));

        $response->assertOk();
        $response->assertViewIs('leave-allowances.edit');
        $response->assertViewHas('leaveAllowance');
    }

    public function test_admin_can_update_leave_allowance(): void
    {
        $this->actingAs($this->admin);

        $allowance = LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'total_days' => 20,
            'carried_over_days' => 0,
        ]);

        $response = $this->put(route('leave-allowances.update', $allowance), [
            'total_days' => 30,
            'carried_over_days' => 5,
        ]);

        $response->assertRedirect(route('leave-allowances.index', ['year' => $allowance->year]));
        $response->assertSessionHas('success');

        $allowance->refresh();
        $this->assertEquals(30, $allowance->total_days);
        $this->assertEquals(5, $allowance->carried_over_days);
    }

    public function test_admin_can_delete_unused_leave_allowance(): void
    {
        $this->actingAs($this->admin);

        $allowance = LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'used_days' => 0,
        ]);

        $response = $this->delete(route('leave-allowances.destroy', $allowance));

        $response->assertRedirect(route('leave-allowances.index', ['year' => $allowance->year]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('leave_allowances', ['id' => $allowance->id]);
    }

    public function test_admin_cannot_delete_used_leave_allowance(): void
    {
        $this->actingAs($this->admin);

        $allowance = LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'used_days' => 5,
        ]);

        $response = $this->delete(route('leave-allowances.destroy', $allowance));

        $response->assertRedirect(route('leave-allowances.index', ['year' => $allowance->year]));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('leave_allowances', ['id' => $allowance->id]);
    }

    public function test_admin_from_different_tenant_cannot_access_leave_allowance(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->actingAs($otherAdmin);

        $allowance = LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        // Tenant scoping via BelongsToTenant trait returns 404 for records from other tenants
        $response = $this->put(route('leave-allowances.update', $allowance), [
            'total_days' => 100,
        ]);

        $response->assertStatus(404);
    }

    public function test_index_shows_remaining_days(): void
    {
        $this->actingAs($this->admin);

        LeaveAllowance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 25,
            'used_days' => 10,
            'carried_over_days' => 5,
        ]);

        $response = $this->get(route('leave-allowances.index'));

        $response->assertOk();
        $response->assertViewHas('allowances', function ($allowances) {
            $allowance = $allowances->first();

            return $allowance->remaining_days == 20; // 25 + 5 - 10
        });
    }
}
