<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserFilterDefault;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFilterDefaultsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->user->id,
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
    }

    public function test_can_save_filter_defaults(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.filter-defaults.store'), [
            'filter_context' => 'schedule',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_filter_defaults', [
            'user_id' => $this->user->id,
            'filter_context' => 'schedule',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);
    }

    public function test_can_save_filter_defaults_with_group_by(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.filter-defaults.store'), [
            'filter_context' => 'schedule',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'group_by' => 'role',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $filterDefault = UserFilterDefault::where('user_id', $this->user->id)
            ->where('filter_context', 'schedule')
            ->first();

        $this->assertNotNull($filterDefault);
        $this->assertEquals('role', $filterDefault->getFilter('group_by'));
    }

    public function test_can_retrieve_filter_defaults(): void
    {
        $this->actingAs($this->user);

        UserFilterDefault::create([
            'user_id' => $this->user->id,
            'filter_context' => 'schedule',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'additional_filters' => ['group_by' => 'role'],
        ]);

        $response = $this->getJson(route('user.filter-defaults.show', ['filter_context' => 'schedule']));

        $response->assertOk();
        $response->assertJson([
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'group_by' => 'role',
        ]);
    }

    public function test_returns_defaults_when_no_saved_preferences(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('user.filter-defaults.show', ['filter_context' => 'schedule']));

        $response->assertOk();
        $response->assertJson([
            'location_id' => null,
            'department_id' => null,
            'business_role_id' => null,
            'group_by' => 'department',
        ]);
    }

    public function test_can_update_existing_filter_defaults(): void
    {
        $this->actingAs($this->user);

        // Create initial defaults
        UserFilterDefault::create([
            'user_id' => $this->user->id,
            'filter_context' => 'schedule',
            'location_id' => $this->location->id,
            'additional_filters' => ['group_by' => 'department'],
        ]);

        // Update with new values
        $response = $this->postJson(route('user.filter-defaults.store'), [
            'filter_context' => 'schedule',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'group_by' => 'role',
        ]);

        $response->assertOk();

        $filterDefault = UserFilterDefault::where('user_id', $this->user->id)
            ->where('filter_context', 'schedule')
            ->first();

        $this->assertEquals($this->department->id, $filterDefault->department_id);
        $this->assertEquals('role', $filterDefault->getFilter('group_by'));
    }

    public function test_separate_defaults_for_different_contexts(): void
    {
        $this->actingAs($this->user);

        // Save week view defaults
        $this->postJson(route('user.filter-defaults.store'), [
            'filter_context' => 'schedule',
            'location_id' => $this->location->id,
            'group_by' => 'department',
        ]);

        // Save day view defaults
        $this->postJson(route('user.filter-defaults.store'), [
            'filter_context' => 'schedule_day',
            'location_id' => $this->location->id,
            'group_by' => 'role',
        ]);

        // Verify they are separate
        $weekDefaults = UserFilterDefault::where('user_id', $this->user->id)
            ->where('filter_context', 'schedule')
            ->first();

        $dayDefaults = UserFilterDefault::where('user_id', $this->user->id)
            ->where('filter_context', 'schedule_day')
            ->first();

        $this->assertEquals('department', $weekDefaults->getFilter('group_by'));
        $this->assertEquals('role', $dayDefaults->getFilter('group_by'));
    }

    public function test_group_by_validation(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.filter-defaults.store'), [
            'filter_context' => 'schedule',
            'group_by' => 'invalid_value',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['group_by']);
    }

    public function test_filter_context_required(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.filter-defaults.store'), [
            'location_id' => $this->location->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['filter_context']);
    }
}
