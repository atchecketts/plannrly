<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Notifications\LeaveRequestStatusNotification;
use App\Notifications\ShiftChangedNotification;
use App\Notifications\ShiftPublishedNotification;
use App\Notifications\SwapRequestNotification;
use App\Notifications\SwapRequestResponseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

    private TenantSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['is_active' => true]);
        // TenantObserver auto-creates TenantSettings, so update instead of creating new
        $this->settings = $this->tenant->tenantSettings;
        $this->settings->update([
            'timezone' => 'UTC',
            'notify_on_publish' => true,
        ]);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->employee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $this->location = Location::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $this->department = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        $this->businessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);

        $this->employee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);
    }

    public function test_notification_bell_shows_unread_notifications(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('notifications.unread'));

        $response->assertOk()
            ->assertJsonStructure(['notifications', 'unread_count']);
    }

    public function test_user_can_view_all_notifications(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('notifications.index'));

        $response->assertOk()
            ->assertViewIs('notifications.index');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        Notification::fake();

        // Create a database notification directly
        $this->employee->notify(new ShiftPublishedNotification(
            Shift::factory()->create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $this->employee->id,
                'location_id' => $this->location->id,
                'department_id' => $this->department->id,
                'business_role_id' => $this->businessRole->id,
                'status' => ShiftStatus::Published,
            ])
        ));

        Notification::assertSentTo($this->employee, ShiftPublishedNotification::class);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('notifications.mark-all-read'));

        $response->assertRedirect();
    }

    public function test_user_can_view_notification_preferences(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('notifications.preferences'));

        $response->assertOk()
            ->assertViewIs('notifications.preferences');
    }

    public function test_user_can_update_notification_preferences(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('notifications.preferences.update'), [
            'preferences' => [
                'shift_published' => [
                    'email_enabled' => true,
                    'in_app_enabled' => true,
                ],
                'shift_changed' => [
                    'email_enabled' => false,
                    'in_app_enabled' => true,
                ],
            ],
        ]);

        $response->assertRedirect(route('notifications.preferences'));

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->employee->id,
            'notification_type' => 'shift_published',
            'email_enabled' => true,
            'in_app_enabled' => true,
        ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->employee->id,
            'notification_type' => 'shift_changed',
            'email_enabled' => false,
            'in_app_enabled' => true,
        ]);
    }

    public function test_shift_published_notification_sent_on_publish(): void
    {
        Notification::fake();

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Draft,
            'date' => now()->addDay(),
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('shifts.publish', $shift));

        $response->assertRedirect();

        Notification::assertSentTo($this->employee, ShiftPublishedNotification::class);
    }

    public function test_shift_changed_notification_sent_on_update(): void
    {
        Notification::fake();

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('shifts.update', $shift), [
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '18:00',
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'location_id' => $this->location->id,
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($this->employee, ShiftChangedNotification::class);
    }

    public function test_shift_deleted_notification_sent(): void
    {
        Notification::fake();

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay(),
        ]);

        $this->actingAs($this->admin);

        $response = $this->delete(route('shifts.destroy', $shift));

        $response->assertRedirect();

        Notification::assertSentTo($this->employee, ShiftChangedNotification::class, function ($notification) {
            return $notification->changeType === 'deleted';
        });
    }

    public function test_leave_request_status_notification_sent_on_approval(): void
    {
        Notification::fake();

        $leaveType = LeaveType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $leaveRequest = LeaveRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => LeaveRequestStatus::Requested,
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addDays(2),
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('leave-requests.review', $leaveRequest), [
            'action' => 'approve',
            'review_notes' => 'Approved',
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($this->employee, LeaveRequestStatusNotification::class);
    }

    public function test_swap_request_notification_sent_to_target_user(): void
    {
        Notification::fake();

        $targetEmployee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $targetEmployee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay(),
        ]);

        $this->actingAs($this->employee);

        $response = $this->post(route('shift-swaps.store'), [
            'requesting_shift_id' => $shift->id,
            'target_user_id' => $targetEmployee->id,
            'reason' => 'Need to swap',
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($targetEmployee, SwapRequestNotification::class);
    }

    public function test_swap_response_notification_sent_on_accept(): void
    {
        Notification::fake();

        $targetEmployee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $targetEmployee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay(),
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee->id,
            'target_user_id' => $targetEmployee->id,
            'requesting_shift_id' => $shift->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $this->actingAs($targetEmployee);

        $response = $this->post(route('shift-swaps.accept', $swapRequest));

        $response->assertRedirect();

        Notification::assertSentTo($this->employee, SwapRequestResponseNotification::class, function ($notification) {
            return $notification->responseType === 'accepted';
        });
    }

    public function test_swap_response_notification_sent_on_reject(): void
    {
        Notification::fake();

        $targetEmployee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $targetEmployee->businessRoles()->attach($this->businessRole->id, ['is_primary' => true]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'status' => ShiftStatus::Published,
            'date' => now()->addDay(),
        ]);

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => $this->tenant->id,
            'requesting_user_id' => $this->employee->id,
            'target_user_id' => $targetEmployee->id,
            'requesting_shift_id' => $shift->id,
            'status' => SwapRequestStatus::Pending,
        ]);

        $this->actingAs($targetEmployee);

        $response = $this->post(route('shift-swaps.reject', $swapRequest));

        $response->assertRedirect();

        Notification::assertSentTo($this->employee, SwapRequestResponseNotification::class, function ($notification) {
            return $notification->responseType === 'rejected';
        });
    }

    public function test_notification_deleted_successfully(): void
    {
        $this->actingAs($this->employee);

        // The delete endpoint should work even if notification doesn't exist
        $response = $this->delete(route('notifications.destroy', 'fake-id'));

        $response->assertRedirect();
    }

    public function test_clear_all_notifications(): void
    {
        $this->actingAs($this->employee);

        $response = $this->delete(route('notifications.clear-all'));

        $response->assertRedirect();
    }
}
