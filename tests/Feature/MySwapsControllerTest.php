<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySwapsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $employee;

    private User $otherEmployee;

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

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->employee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);

        $this->otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->otherEmployee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);
    }

    public function test_employee_can_view_swap_requests(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('my-swaps.index'));

        $response->assertOk();
        $response->assertViewIs('my-swaps.index');
        $response->assertViewHas('outgoingRequests');
        $response->assertViewHas('incomingRequests');
    }

    public function test_employee_sees_their_outgoing_requests(): void
    {
        $this->actingAs($this->employee);

        // Create a shift for employee
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        // Create outgoing swap request
        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->otherEmployee->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->get(route('my-swaps.index'));

        $response->assertOk();
        $response->assertViewHas('outgoingRequests', fn ($requests) => $requests->count() === 1);
    }

    public function test_employee_sees_incoming_requests(): void
    {
        $this->actingAs($this->employee);

        // Create a shift for other employee
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->otherEmployee->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        // Create incoming swap request targeting employee
        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->otherEmployee->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->get(route('my-swaps.index'));

        $response->assertOk();
        $response->assertViewHas('incomingRequests', fn ($requests) => $requests->count() === 1);
    }

    public function test_employee_can_view_create_swap_form(): void
    {
        $this->actingAs($this->employee);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('my-swaps.create', $shift));

        $response->assertOk();
        $response->assertViewIs('my-swaps.create');
        $response->assertViewHas('shift');
        $response->assertViewHas('availableUsers');
    }

    public function test_employee_cannot_create_swap_for_others_shift(): void
    {
        $this->actingAs($this->employee);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->otherEmployee->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('my-swaps.create', $shift));

        $response->assertForbidden();
    }

    public function test_employee_can_create_swap_request(): void
    {
        $this->actingAs($this->employee);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->post(route('my-swaps.store'), [
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->otherEmployee->id,
            'reason' => 'Need to attend a doctor appointment',
        ]);

        $response->assertRedirect(route('my-swaps.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('shift_swap_requests', [
            'requesting_user_id' => $this->employee->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->otherEmployee->id,
        ]);
    }

    public function test_employee_cannot_create_swap_for_others_shift_via_post(): void
    {
        $this->actingAs($this->employee);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->otherEmployee->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->post(route('my-swaps.store'), [
            'requesting_shift_id' => $shift->id,
            'reason' => 'Trying to swap someone else\'s shift',
        ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_my_swaps(): void
    {
        $response = $this->get(route('my-swaps.index'));

        $response->assertRedirect(route('login'));
    }
}
