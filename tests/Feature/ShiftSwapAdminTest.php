<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftSwapAdminTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee1;

    private User $employee2;

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

        $this->employee1 = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->employee1->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);

        $this->employee2 = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->employee2->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);
    }

    public function test_admin_can_approve_accepted_swap_request(): void
    {
        $this->actingAs($this->admin);

        $shift1 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $shift2 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee2->id,
            'date' => now()->addDays(4),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift1->id,
            'target_user_id' => $this->employee2->id,
            'target_shift_id' => $shift2->id,
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $response = $this->post(route('shift-swaps.approve', $swapRequest));

        $response->assertRedirect(route('shift-swaps.index'));
        $response->assertSessionHas('success');

        $swapRequest->refresh();
        $this->assertEquals($this->admin->id, $swapRequest->approved_by);
        $this->assertNotNull($swapRequest->approved_at);

        // Verify shifts were swapped
        $shift1->refresh();
        $shift2->refresh();
        $this->assertEquals($this->employee2->id, $shift1->user_id);
        $this->assertEquals($this->employee1->id, $shift2->user_id);
    }

    public function test_admin_can_approve_one_way_swap(): void
    {
        $this->actingAs($this->admin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'target_shift_id' => null, // One-way swap
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $response = $this->post(route('shift-swaps.approve', $swapRequest));

        $response->assertRedirect(route('shift-swaps.index'));

        // Verify shift was reassigned
        $shift->refresh();
        $this->assertEquals($this->employee2->id, $shift->user_id);
    }

    public function test_target_employee_can_accept_swap_request(): void
    {
        $this->actingAs($this->employee2);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->post(route('shift-swaps.accept', $swapRequest));

        $response->assertRedirect(route('shift-swaps.index'));
        $response->assertSessionHas('success');

        $swapRequest->refresh();
        $this->assertEquals(SwapRequestStatus::Accepted, $swapRequest->status);
        $this->assertNotNull($swapRequest->responded_at);
    }

    public function test_target_employee_can_reject_swap_request(): void
    {
        $this->actingAs($this->employee2);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->post(route('shift-swaps.reject', $swapRequest));

        $response->assertRedirect(route('shift-swaps.index'));
        $response->assertSessionHas('success');

        $swapRequest->refresh();
        $this->assertEquals(SwapRequestStatus::Rejected, $swapRequest->status);
    }

    public function test_requester_can_cancel_swap_request(): void
    {
        $this->actingAs($this->employee1);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->post(route('shift-swaps.cancel', $swapRequest));

        $response->assertRedirect(route('shift-swaps.index'));
        $response->assertSessionHas('success');

        $swapRequest->refresh();
        $this->assertEquals(SwapRequestStatus::Cancelled, $swapRequest->status);
    }

    public function test_non_target_employee_cannot_accept_swap(): void
    {
        $employee3 = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($employee3);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->post(route('shift-swaps.accept', $swapRequest));

        $response->assertStatus(403);
    }

    public function test_employee_cannot_admin_approve_swap(): void
    {
        $this->actingAs($this->employee1);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $response = $this->post(route('shift-swaps.approve', $swapRequest));

        $response->assertStatus(403);
    }

    public function test_location_admin_can_approve_swap_in_their_location(): void
    {
        $locationAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $locationAdmin->id,
            'system_role' => SystemRole::LocationAdmin->value,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($locationAdmin);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $response = $this->post(route('shift-swaps.approve', $swapRequest));

        $response->assertRedirect(route('shift-swaps.index'));
        $response->assertSessionHas('success');
    }

    public function test_swap_request_with_reason(): void
    {
        $this->actingAs($this->employee1);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->post(route('shift-swaps.store'), [
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'reason' => 'I have a doctor appointment that day',
        ]);

        $response->assertRedirect(route('shift-swaps.index'));

        $this->assertDatabaseHas('shift_swap_requests', [
            'requesting_user_id' => $this->employee1->id,
            'reason' => 'I have a doctor appointment that day',
        ]);
    }
}
