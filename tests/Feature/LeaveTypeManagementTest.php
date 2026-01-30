<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

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
    }

    public function test_admin_can_view_leave_types_index(): void
    {
        $this->actingAs($this->admin);

        LeaveType::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Annual Leave']);
        LeaveType::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Sick Leave']);

        $response = $this->get(route('leave-types.index'));

        $response->assertOk();
        $response->assertViewIs('leave-types.index');
        $response->assertSee('Annual Leave');
        $response->assertSee('Sick Leave');
    }

    public function test_employee_cannot_view_leave_types_index(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('leave-types.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_create_leave_type_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('leave-types.create'));

        $response->assertOk();
        $response->assertViewIs('leave-types.create');
    }

    public function test_admin_can_create_leave_type(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('leave-types.store'), [
            'name' => 'Annual Leave',
            'color' => '#4CAF50',
            'requires_approval' => true,
            'affects_allowance' => true,
            'is_paid' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('leave-types.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_types', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Annual Leave',
            'color' => '#4CAF50',
            'requires_approval' => true,
            'affects_allowance' => true,
            'is_paid' => true,
            'is_active' => true,
        ]);
    }

    public function test_leave_type_requires_name(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('leave-types.store'), [
            'color' => '#4CAF50',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_leave_type_requires_valid_color(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('leave-types.store'), [
            'name' => 'Annual Leave',
            'color' => 'invalid-color',
        ]);

        $response->assertSessionHasErrors('color');
    }

    public function test_admin_can_view_edit_leave_type_form(): void
    {
        $this->actingAs($this->admin);

        $leaveType = LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->get(route('leave-types.edit', $leaveType));

        $response->assertOk();
        $response->assertViewIs('leave-types.edit');
        $response->assertViewHas('leaveType');
    }

    public function test_admin_can_update_leave_type(): void
    {
        $this->actingAs($this->admin);

        $leaveType = LeaveType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Old Name',
        ]);

        $response = $this->put(route('leave-types.update', $leaveType), [
            'name' => 'Updated Name',
            'color' => '#FF5733',
            'requires_approval' => false,
            'affects_allowance' => false,
            'is_paid' => false,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('leave-types.index'));
        $response->assertSessionHas('success');

        $leaveType->refresh();
        $this->assertEquals('Updated Name', $leaveType->name);
        $this->assertEquals('#FF5733', $leaveType->color);
        $this->assertFalse($leaveType->requires_approval);
    }

    public function test_admin_can_delete_leave_type_without_requests(): void
    {
        $this->actingAs($this->admin);

        $leaveType = LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->delete(route('leave-types.destroy', $leaveType));

        $response->assertRedirect(route('leave-types.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('leave_types', ['id' => $leaveType->id]);
    }

    public function test_admin_cannot_delete_leave_type_with_requests(): void
    {
        $this->actingAs($this->admin);

        $leaveType = LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);

        LeaveRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
        ]);

        $response = $this->delete(route('leave-types.destroy', $leaveType));

        $response->assertRedirect(route('leave-types.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('leave_types', ['id' => $leaveType->id, 'deleted_at' => null]);
    }

    public function test_index_shows_leave_type_properties(): void
    {
        $this->actingAs($this->admin);

        LeaveType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Sick Leave',
            'requires_approval' => true,
            'affects_allowance' => false,
            'is_paid' => true,
        ]);

        $response = $this->get(route('leave-types.index'));

        $response->assertOk();
        $response->assertSee('Approval Required');
        $response->assertSee('Paid');
    }

    public function test_index_shows_leave_request_count(): void
    {
        $this->actingAs($this->admin);

        $leaveType = LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);

        LeaveRequest::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
        ]);

        $response = $this->get(route('leave-types.index'));

        $response->assertOk();
        $response->assertViewHas('leaveTypes', function ($leaveTypes) {
            return $leaveTypes->first()->leave_requests_count === 3;
        });
    }

    public function test_admin_from_different_tenant_cannot_update_leave_type(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->actingAs($otherAdmin);

        $leaveType = LeaveType::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->put(route('leave-types.update', $leaveType), [
            'name' => 'Hacked Name',
            'color' => '#FF0000',
        ]);

        $response->assertStatus(403);
    }
}
