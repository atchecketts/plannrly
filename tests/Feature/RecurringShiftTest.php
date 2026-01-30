<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\RecurringShiftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringShiftTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

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
    }

    public function test_can_create_recurring_shift_with_weekly_frequency(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/shifts', [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->next('Monday')->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 30,
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => 'weekly',
                'interval' => 1,
                'days_of_week' => [1], // Monday
                'end_after_occurrences' => 4,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Should create parent + 3 children = 4 total shifts (4 occurrences)
        $this->assertEquals(4, Shift::count());

        // Verify parent shift
        $parentShift = Shift::whereNull('parent_shift_id')->first();
        $this->assertTrue($parentShift->is_recurring);
        $this->assertNotNull($parentShift->recurrence_rule);

        // Verify child shifts
        $childShifts = Shift::whereNotNull('parent_shift_id')->get();
        $this->assertCount(3, $childShifts);

        foreach ($childShifts as $child) {
            $this->assertFalse($child->is_recurring);
            $this->assertEquals($parentShift->id, $child->parent_shift_id);
        }
    }

    public function test_can_create_recurring_shift_with_daily_frequency(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/shifts', [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => 'daily',
                'interval' => 1,
                'end_after_occurrences' => 5,
            ],
        ]);

        $response->assertStatus(200);

        // Should create 5 shifts (1 parent + 4 children)
        $this->assertEquals(5, Shift::count());
    }

    public function test_can_create_recurring_shift_with_monthly_frequency(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/shifts', [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => 'monthly',
                'interval' => 1,
                'end_after_occurrences' => 3,
            ],
        ]);

        $response->assertStatus(200);

        // Should create 3 shifts (1 parent + 2 children)
        $this->assertEquals(3, Shift::count());
    }

    public function test_can_edit_single_recurring_instance(): void
    {
        $this->actingAs($this->admin);

        // Create a recurring shift
        $parentShift = Shift::factory()->recurring('weekly', 1, [1])->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->next('Monday'),
        ]);

        // Create child shifts
        $service = app(RecurringShiftService::class);
        $service->setGenerationWeeks(4);
        $service->generateInstances($parentShift);

        $childShift = Shift::where('parent_shift_id', $parentShift->id)->first();

        // Edit single instance
        $response = $this->putJson("/shifts/{$childShift->id}", [
            'start_time' => '10:00',
            'end_time' => '18:00',
            'edit_scope' => 'single',
        ]);

        $response->assertStatus(200);

        // Child should be detached from parent
        $childShift->refresh();
        $this->assertNull($childShift->parent_shift_id);
        $this->assertEquals('10:00', $childShift->start_time->format('H:i'));
    }

    public function test_can_edit_all_future_recurring_instances(): void
    {
        $this->actingAs($this->admin);

        // Create a recurring shift with children
        $parentShift = Shift::factory()->recurring('weekly', 1, [1])->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->next('Monday'),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Create child shifts
        $service = app(RecurringShiftService::class);
        $service->setGenerationWeeks(4);
        $childShifts = $service->generateInstances($parentShift);

        // Edit future instances
        $response = $this->putJson("/shifts/{$parentShift->id}", [
            'start_time' => '08:00',
            'end_time' => '16:00',
            'edit_scope' => 'future',
        ]);

        $response->assertStatus(200);

        // Verify all future children were updated
        $updatedChildren = Shift::where('parent_shift_id', $parentShift->id)
            ->where('date', '>=', Carbon::today())
            ->get();

        foreach ($updatedChildren as $child) {
            $this->assertEquals('08:00', $child->start_time->format('H:i'));
            $this->assertEquals('16:00', $child->end_time->format('H:i'));
        }
    }

    public function test_can_delete_single_recurring_instance(): void
    {
        $this->actingAs($this->admin);

        // Create a recurring shift with children
        $parentShift = Shift::factory()->recurring('weekly', 1, [1])->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->next('Monday'),
        ]);

        $service = app(RecurringShiftService::class);
        $service->setGenerationWeeks(4);
        $service->generateInstances($parentShift);

        $initialCount = Shift::count();
        $childShift = Shift::where('parent_shift_id', $parentShift->id)->first();

        // Delete single instance
        $response = $this->deleteJson("/shifts/{$childShift->id}?delete_scope=single");

        $response->assertStatus(200);

        // Only one shift should be deleted
        $this->assertEquals($initialCount - 1, Shift::count());
        $this->assertNull(Shift::find($childShift->id));
    }

    public function test_can_delete_all_future_recurring_instances(): void
    {
        $this->actingAs($this->admin);

        // Create a recurring shift with children
        $parentShift = Shift::factory()->recurring('weekly', 1, [1])->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->next('Monday'),
        ]);

        $service = app(RecurringShiftService::class);
        $service->setGenerationWeeks(4);
        $service->generateInstances($parentShift);

        // Delete all future instances (from parent)
        $response = $this->deleteJson("/shifts/{$parentShift->id}?delete_scope=future");

        $response->assertStatus(200);

        // All shifts should be deleted (parent and future children)
        $this->assertEquals(0, Shift::where('id', $parentShift->id)->orWhere('parent_shift_id', $parentShift->id)->count());
    }

    public function test_shift_model_helper_methods(): void
    {
        $parentShift = Shift::factory()->recurring('weekly', 1, [1])->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today(),
        ]);

        $childShift = Shift::factory()->childOf($parentShift)->create([
            'date' => Carbon::today()->addWeek(),
        ]);

        // Test isRecurringParent
        $this->assertTrue($parentShift->isRecurringParent());
        $this->assertFalse($childShift->isRecurringParent());

        // Test isRecurringChild
        $this->assertFalse($parentShift->isRecurringChild());
        $this->assertTrue($childShift->isRecurringChild());

        // Test hasChildren
        $this->assertTrue($parentShift->hasChildren());
        $this->assertFalse($childShift->hasChildren());

        // Test getFutureChildren
        $futureChildren = $parentShift->getFutureChildren();
        $this->assertCount(1, $futureChildren);
    }

    public function test_recurring_shift_service_generates_correct_dates_for_weekly_on_multiple_days(): void
    {
        $parentShift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->next('Monday'),
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => 'weekly',
                'interval' => 1,
                'days_of_week' => [1, 3, 5], // Mon, Wed, Fri
                'end_after_occurrences' => 6,
            ],
        ]);

        $service = app(RecurringShiftService::class);
        $occurrences = $service->calculateOccurrenceDates($parentShift);

        // Should have 6 occurrences
        $this->assertCount(6, $occurrences);

        // Verify days of week
        foreach ($occurrences as $date) {
            $this->assertContains($date->dayOfWeek, [1, 3, 5]);
        }
    }

    public function test_extend_recurring_shifts_command(): void
    {
        $this->actingAs($this->admin);

        // Create a recurring shift with "never" ending (no end_date or end_after_occurrences)
        $parentShift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->subWeeks(10),
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => 'weekly',
                'interval' => 1,
                'days_of_week' => [Carbon::today()->dayOfWeek],
                // No end_date or end_after_occurrences = "never" ending
            ],
        ]);

        // Create only one child shift that's about to end (within extension threshold)
        Shift::factory()->childOf($parentShift)->create([
            'date' => Carbon::today()->addWeeks(2),
        ]);

        $initialCount = Shift::count();

        // Run the extend command
        $this->artisan('shifts:extend-recurring')
            ->expectsOutput('Extending recurring shifts...')
            ->assertExitCode(0);

        // Should have created more shifts (extending the window)
        $this->assertGreaterThan($initialCount, Shift::count());
    }

    public function test_recurrence_frequency_label_attribute(): void
    {
        // Daily
        $dailyShift = Shift::factory()->recurring('daily', 1)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);
        $this->assertEquals('Daily', $dailyShift->recurrence_frequency_label);

        // Weekly
        $weeklyShift = Shift::factory()->recurring('weekly', 1, [1, 3])->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);
        $this->assertStringContains('Weekly on Mon, Wed', $weeklyShift->recurrence_frequency_label);

        // Every 2 weeks
        $biweeklyShift = Shift::factory()->recurring('weekly', 2, [5])->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);
        $this->assertStringContains('Every 2 weeks', $biweeklyShift->recurrence_frequency_label);
    }

    public function test_non_recurring_shift_has_no_frequency_label(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
        ]);

        $this->assertNull($shift->recurrence_frequency_label);
    }

    public function test_validation_rules_for_recurrence(): void
    {
        $this->actingAs($this->admin);

        // Missing frequency when is_recurring is true
        $response = $this->postJson('/shifts', [
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_recurring' => true,
            'recurrence_rule' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['recurrence_rule.frequency']);
    }

    /**
     * Helper method to assert string contains substring.
     */
    protected function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'."
        );
    }
}
