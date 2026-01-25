<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Enums\SystemRole;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $employee;

    protected User $admin;

    protected LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->employee = User::factory()->forTenant($this->tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);

        $this->admin = User::factory()->forTenant($this->tenant)->create();
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->leaveType = LeaveType::factory()->forTenant($this->tenant)->create();
    }

    public function test_employee_can_create_leave_request(): void
    {
        $response = $this->actingAs($this->employee)->post('/leave-requests', [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(9)->format('Y-m-d'),
            'reason' => 'Family vacation',
            'submit' => false,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'status' => LeaveRequestStatus::Draft->value,
        ]);
    }

    public function test_employee_can_submit_leave_request_for_approval(): void
    {
        $response = $this->actingAs($this->employee)->post('/leave-requests', [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(9)->format('Y-m-d'),
            'reason' => 'Family vacation',
            'submit' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->employee->id,
            'status' => LeaveRequestStatus::Requested->value,
        ]);
    }

    public function test_admin_can_approve_leave_request(): void
    {
        $leaveRequest = LeaveRequest::factory()
            ->forUser($this->employee)
            ->requested()
            ->create(['leave_type_id' => $this->leaveType->id]);

        $response = $this->actingAs($this->admin)->post("/leave-requests/{$leaveRequest->id}/review", [
            'action' => 'approve',
            'review_notes' => 'Approved. Enjoy your vacation!',
        ]);

        $response->assertRedirect();

        $leaveRequest->refresh();
        $this->assertEquals(LeaveRequestStatus::Approved, $leaveRequest->status);
        $this->assertEquals($this->admin->id, $leaveRequest->reviewed_by);
    }

    public function test_admin_can_reject_leave_request(): void
    {
        $leaveRequest = LeaveRequest::factory()
            ->forUser($this->employee)
            ->requested()
            ->create(['leave_type_id' => $this->leaveType->id]);

        $response = $this->actingAs($this->admin)->post("/leave-requests/{$leaveRequest->id}/review", [
            'action' => 'reject',
            'review_notes' => 'Not enough coverage during this period.',
        ]);

        $response->assertRedirect();

        $leaveRequest->refresh();
        $this->assertEquals(LeaveRequestStatus::Rejected, $leaveRequest->status);
        $this->assertNotNull($leaveRequest->review_notes);
    }

    public function test_employee_cannot_approve_their_own_request(): void
    {
        $leaveRequest = LeaveRequest::factory()
            ->forUser($this->employee)
            ->requested()
            ->create(['leave_type_id' => $this->leaveType->id]);

        $response = $this->actingAs($this->employee)->post("/leave-requests/{$leaveRequest->id}/review", [
            'action' => 'approve',
        ]);

        $response->assertStatus(403);
    }

    public function test_total_days_calculated_correctly(): void
    {
        $response = $this->actingAs($this->employee)->post('/leave-requests', [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-03',
            'submit' => false,
        ]);

        $leaveRequest = LeaveRequest::where('user_id', $this->employee->id)->first();

        $this->assertEquals(3, $leaveRequest->total_days);
    }

    public function test_half_days_reduce_total(): void
    {
        $response = $this->actingAs($this->employee)->post('/leave-requests', [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-03',
            'start_half_day' => true,
            'end_half_day' => true,
            'submit' => false,
        ]);

        $leaveRequest = LeaveRequest::where('user_id', $this->employee->id)->first();

        $this->assertEquals(2, $leaveRequest->total_days);
    }
}
