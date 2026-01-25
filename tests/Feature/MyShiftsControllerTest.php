<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyShiftsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $employee;

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
    }

    public function test_employee_can_view_their_shifts(): void
    {
        $this->actingAs($this->employee);

        // Create a published shift for this week
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->startOfWeek()->addDays(2),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('my-shifts.index'));

        $response->assertOk();
        $response->assertViewIs('my-shifts.index');
        $response->assertViewHas('shifts');
        $response->assertViewHas('totalHours');
        $response->assertViewHas('shiftCount');
    }

    public function test_employee_can_navigate_between_weeks(): void
    {
        $this->actingAs($this->employee);

        $nextWeekStart = now()->startOfWeek()->addWeek()->format('Y-m-d');

        $response = $this->get(route('my-shifts.index', ['start' => $nextWeekStart]));

        $response->assertOk();
        $response->assertViewHas('weekStart', fn ($date) => $date->format('Y-m-d') === $nextWeekStart);
    }

    public function test_employee_cannot_see_draft_shifts(): void
    {
        $this->actingAs($this->employee);

        // Create a draft shift
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => now()->startOfWeek()->addDays(2),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Draft,
        ]);

        $response = $this->get(route('my-shifts.index'));

        $response->assertOk();
        $response->assertViewHas('shiftCount', 0);
    }

    public function test_employee_cannot_see_other_employees_shifts(): void
    {
        $this->actingAs($this->employee);

        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create a shift for another employee
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $otherEmployee->id,
            'date' => now()->startOfWeek()->addDays(2),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $response = $this->get(route('my-shifts.index'));

        $response->assertOk();
        $response->assertViewHas('shiftCount', 0);
    }

    public function test_unauthenticated_user_cannot_access_my_shifts(): void
    {
        $response = $this->get(route('my-shifts.index'));

        $response->assertRedirect(route('login'));
    }
}
