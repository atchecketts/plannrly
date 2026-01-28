<?php

namespace Tests\Feature;

use App\Enums\AvailabilityType;
use App\Enums\PreferenceLevel;
use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserAvailability;
use App\Models\UserRoleAssignment;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private AvailabilityService $availabilityService;

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

        $this->availabilityService = app(AvailabilityService::class);
    }

    public function test_employee_can_view_own_availability(): void
    {
        $this->actingAs($this->employee);

        UserAvailability::factory()->forUser($this->employee)->recurring()->forDayOfWeek(1)->create();

        $response = $this->get(route('availability.index'));

        $response->assertOk();
        $response->assertViewIs('availability.index');
    }

    public function test_employee_can_view_availability_edit_form(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('availability.edit'));

        $response->assertOk();
        $response->assertViewIs('availability.edit');
    }

    public function test_employee_can_create_recurring_availability(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('availability.store'), [
            'type' => AvailabilityType::Recurring->value,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'preference_level' => PreferenceLevel::Available->value,
        ]);

        $response->assertRedirect(route('availability.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_availability', [
            'user_id' => $this->employee->id,
            'type' => 'recurring',
            'day_of_week' => 1,
            'preference_level' => 'available',
        ]);
    }

    public function test_employee_can_create_specific_date_exception(): void
    {
        $this->actingAs($this->employee);

        $specificDate = now()->addWeek()->format('Y-m-d');

        $response = $this->post(route('availability.store'), [
            'type' => AvailabilityType::SpecificDate->value,
            'specific_date' => $specificDate,
            'preference_level' => PreferenceLevel::Unavailable->value,
            'notes' => 'Doctor appointment',
        ]);

        $response->assertRedirect(route('availability.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_availability', [
            'user_id' => $this->employee->id,
            'type' => 'specific_date',
            'specific_date' => $specificDate,
            'preference_level' => 'unavailable',
            'notes' => 'Doctor appointment',
        ]);
    }

    public function test_employee_can_delete_availability(): void
    {
        $this->actingAs($this->employee);

        $availability = UserAvailability::factory()->forUser($this->employee)->create();

        $response = $this->delete(route('availability.destroy', $availability));

        $response->assertRedirect(route('availability.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('user_availability', ['id' => $availability->id]);
    }

    public function test_employee_cannot_delete_other_users_availability(): void
    {
        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $availability = UserAvailability::factory()->forUser($otherEmployee)->create();

        $this->actingAs($this->employee);

        $response = $this->delete(route('availability.destroy', $availability));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_employee_availability(): void
    {
        $this->actingAs($this->admin);

        UserAvailability::factory()->forUser($this->employee)->recurring()->create();

        $response = $this->get(route('users.availability', $this->employee));

        $response->assertOk();
        $response->assertViewIs('availability.show');
        $response->assertViewHas('user');
    }

    public function test_availability_service_returns_correct_status_for_date(): void
    {
        UserAvailability::factory()->forUser($this->employee)->recurring()
            ->forDayOfWeek(1)
            ->unavailable()
            ->create();

        $monday = Carbon::parse('next Monday');
        $availability = $this->availabilityService->getAvailabilityAt($this->employee, $monday);

        $this->assertFalse($availability['is_available']);
        $this->assertEquals(PreferenceLevel::Unavailable, $availability['preference_level']);
    }

    public function test_specific_date_overrides_recurring_rule(): void
    {
        UserAvailability::factory()->forUser($this->employee)->recurring()
            ->forDayOfWeek(1)
            ->available()
            ->create();

        $specificMonday = Carbon::parse('next Monday');

        UserAvailability::factory()->forUser($this->employee)
            ->specificDate($specificMonday)
            ->unavailable()
            ->create();

        $availability = $this->availabilityService->getAvailabilityAt($this->employee, $specificMonday);

        $this->assertFalse($availability['is_available']);
        $this->assertEquals(PreferenceLevel::Unavailable, $availability['preference_level']);
    }

    public function test_effective_date_range_is_respected(): void
    {
        $futureDate = now()->addMonth();

        UserAvailability::factory()->forUser($this->employee)->recurring()
            ->forDayOfWeek(Carbon::MONDAY)
            ->unavailable()
            ->effectiveFrom($futureDate)
            ->create();

        $nextMonday = Carbon::parse('next Monday');
        $availability = $this->availabilityService->getAvailabilityAt($this->employee, $nextMonday);

        $this->assertTrue($availability['is_available']);
    }

    public function test_preference_levels_are_correctly_stored(): void
    {
        $this->actingAs($this->employee);

        $this->post(route('availability.store'), [
            'type' => AvailabilityType::Recurring->value,
            'day_of_week' => 2,
            'preference_level' => PreferenceLevel::Preferred->value,
        ]);

        $this->assertDatabaseHas('user_availability', [
            'user_id' => $this->employee->id,
            'preference_level' => 'preferred',
        ]);
    }

    public function test_is_available_for_shift_returns_correct_value(): void
    {
        UserAvailability::factory()->forUser($this->employee)->recurring()
            ->forDayOfWeek(1)
            ->available()
            ->state(['start_time' => '09:00', 'end_time' => '17:00'])
            ->create();

        $monday = Carbon::parse('next Monday');

        $isAvailable = $this->availabilityService->isAvailableForShift(
            $this->employee,
            $monday,
            '09:00',
            '17:00'
        );

        $this->assertTrue($isAvailable);
    }

    public function test_has_conflict_returns_true_for_unavailable_time(): void
    {
        UserAvailability::factory()->forUser($this->employee)->recurring()
            ->forDayOfWeek(1)
            ->unavailable()
            ->create();

        $monday = Carbon::parse('next Monday');

        $hasConflict = $this->availabilityService->hasConflict(
            $this->employee,
            $monday,
            '09:00',
            '17:00'
        );

        $this->assertTrue($hasConflict);
    }

    public function test_validation_requires_day_of_week_for_recurring(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('availability.store'), [
            'type' => AvailabilityType::Recurring->value,
            'preference_level' => PreferenceLevel::Available->value,
        ]);

        $response->assertSessionHasErrors('day_of_week');
    }

    public function test_validation_requires_specific_date_for_specific_date_type(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('availability.store'), [
            'type' => AvailabilityType::SpecificDate->value,
            'preference_level' => PreferenceLevel::Available->value,
        ]);

        $response->assertSessionHasErrors('specific_date');
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('availability.store'), [
            'type' => AvailabilityType::Recurring->value,
            'day_of_week' => 1,
            'start_time' => '17:00',
            'end_time' => '09:00',
            'preference_level' => PreferenceLevel::Available->value,
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_weekly_summary_returns_correct_data(): void
    {
        UserAvailability::factory()->forUser($this->employee)->recurring()
            ->forDayOfWeek(1)
            ->preferred()
            ->state(['start_time' => '09:00', 'end_time' => '17:00'])
            ->create();

        $summary = $this->availabilityService->getWeeklySummary($this->employee);

        $this->assertArrayHasKey(1, $summary);
        $this->assertEquals('Monday', $summary[1]['day']);
        $this->assertNotEmpty($summary[1]['slots']);
    }

    public function test_guest_cannot_access_availability(): void
    {
        $response = $this->get(route('availability.index'));

        $response->assertRedirect(route('login'));
    }
}
