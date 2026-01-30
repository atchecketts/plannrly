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

class ShiftSwapViewsTest extends TestCase
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

    public function test_admin_can_view_swap_index(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('shift-swaps.index'));

        $response->assertOk();
        $response->assertViewIs('shift-swaps.index');
        $response->assertViewHas('swapRequests');
        $response->assertViewHas('counts');
    }

    public function test_employee_can_view_swap_index(): void
    {
        $this->actingAs($this->employee1);

        $response = $this->get(route('shift-swaps.index'));

        $response->assertOk();
        $response->assertViewIs('shift-swaps.index');
    }

    public function test_index_shows_status_tabs(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('shift-swaps.index'));

        $response->assertOk();
        $response->assertSee('All');
        $response->assertSee('Pending');
        $response->assertSee('Accepted');
        $response->assertSee('Rejected');
    }

    public function test_index_filters_by_pending_status(): void
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

        $pendingSwap = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $acceptedSwap = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee2->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee1->id,
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $response = $this->get(route('shift-swaps.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertViewHas('swapRequests', function ($swapRequests) use ($pendingSwap, $acceptedSwap) {
            return $swapRequests->contains($pendingSwap) && ! $swapRequests->contains($acceptedSwap);
        });
    }

    public function test_index_filters_by_accepted_status(): void
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

        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $acceptedSwap = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee2->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee1->id,
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $response = $this->get(route('shift-swaps.index', ['status' => 'accepted']));

        $response->assertOk();
        $response->assertViewHas('swapRequests', function ($swapRequests) use ($acceptedSwap) {
            return $swapRequests->contains($acceptedSwap) && $swapRequests->count() === 1;
        });
    }

    public function test_index_shows_correct_counts(): void
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

        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee2->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee1->id,
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee2->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee1->id,
            'status' => SwapRequestStatus::Rejected,
            'responded_at' => now(),
        ]);

        $response = $this->get(route('shift-swaps.index'));

        $response->assertOk();
        $response->assertViewHas('counts', function ($counts) {
            return $counts['all'] === 4
                && $counts['pending'] === 2
                && $counts['accepted'] === 1
                && $counts['rejected'] === 1;
        });
    }

    public function test_employee_only_sees_their_own_swaps(): void
    {
        $employee3 = User::factory()->create(['tenant_id' => $this->tenant->id]);

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

        $mySwap = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $otherSwap = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee2->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $employee3->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $response = $this->get(route('shift-swaps.index'));

        $response->assertOk();
        $response->assertViewHas('swapRequests', function ($swapRequests) use ($mySwap, $otherSwap) {
            return $swapRequests->contains($mySwap) && ! $swapRequests->contains($otherSwap);
        });
    }

    public function test_employee_can_view_create_swap_form(): void
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

        $response = $this->get(route('shift-swaps.create', $shift));

        $response->assertOk();
        $response->assertViewIs('shift-swaps.create');
        $response->assertViewHas('shift');
        $response->assertViewHas('eligibleUsers');
        $response->assertViewHas('eligibleShifts');
    }

    public function test_create_form_shows_shift_details(): void
    {
        $this->actingAs($this->employee1);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('shift-swaps.create', $shift));

        $response->assertOk();
        $response->assertSee('Your Shift');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }

    public function test_create_form_shows_eligible_users(): void
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

        $response = $this->get(route('shift-swaps.create', $shift));

        $response->assertOk();
        $response->assertViewHas('eligibleUsers', function ($users) {
            return $users->contains($this->employee2)
                && ! $users->contains($this->employee1);
        });
    }

    public function test_index_shows_awaiting_admin_badge_for_accepted_swaps(): void
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

        ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee1->id,
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $response = $this->get(route('shift-swaps.index'));

        $response->assertOk();
        $response->assertSee('Awaiting Admin');
    }

    public function test_employee_can_give_away_shift_without_receiving_one_back(): void
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
            'target_shift_id' => null,
            'reason' => 'Need to take the day off',
        ]);

        $response->assertRedirect(route('shift-swaps.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_swap_requests', [
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $this->employee2->id,
            'target_shift_id' => null,
            'status' => SwapRequestStatus::Pending,
        ]);
    }

    public function test_cannot_swap_to_user_without_same_business_role(): void
    {
        $this->actingAs($this->employee1);

        // Create another business role
        $differentRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        // Create a user with a different business role
        $differentRoleUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $differentRoleUser->businessRoles()->attach($differentRole->id, ['is_primary' => true]);

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
            'target_user_id' => $differentRoleUser->id,
            'reason' => 'Test',
        ]);

        $response->assertSessionHasErrors('target_user_id');
        $this->assertDatabaseMissing('shift_swap_requests', [
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $differentRoleUser->id,
        ]);
    }

    public function test_cannot_swap_own_shift_to_someone_else(): void
    {
        $this->actingAs($this->employee1);

        // Employee2's shift
        $otherShift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee2->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->post(route('shift-swaps.store'), [
            'requesting_shift_id' => $otherShift->id,
            'target_user_id' => $this->employee2->id,
            'reason' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_create_form_only_shows_users_with_same_role(): void
    {
        $this->actingAs($this->employee1);

        // Create another business role
        $differentRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
        ]);

        // Create a user with a different business role
        $differentRoleUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $differentRoleUser->businessRoles()->attach($differentRole->id, ['is_primary' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee1->id,
            'date' => now()->addDays(3),
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('shift-swaps.create', $shift));

        $response->assertOk();
        $response->assertViewHas('eligibleUsers', function ($users) use ($differentRoleUser) {
            return $users->contains($this->employee2)
                && ! $users->contains($differentRoleUser)
                && ! $users->contains($this->employee1);
        });
    }
}
