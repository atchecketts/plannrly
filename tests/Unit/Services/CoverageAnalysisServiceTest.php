<?php

namespace Tests\Unit\Services;

use App\Enums\CoverageStatus;
use App\Enums\ShiftStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\StaffingRequirement;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CoverageAnalysisService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverageAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    private CoverageAnalysisService $service;

    private Tenant $tenant;

    private Location $location;

    private Department $department;

    private BusinessRole $businessRole;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CoverageAnalysisService;

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
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Authenticate user for tenant scoping
        $this->actingAs($this->user);
    }

    public function test_returns_no_requirement_when_none_defined(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday

        $result = $this->service->getTimeSlotCoverage(
            $date,
            '09:00',
            '17:00',
            $this->businessRole->id
        );

        $this->assertEquals(CoverageStatus::NoRequirement, $result->status);
    }

    public function test_returns_adequate_when_min_met(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday (day 3)

        // Create requirement for Wednesday
        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 2,
            'max_employees' => 5,
        ]);

        // Create 2 shifts (meets minimum)
        Shift::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->user->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $result = $this->service->getTimeSlotCoverage(
            $date,
            '09:00',
            '17:00',
            $this->businessRole->id
        );

        $this->assertEquals(CoverageStatus::Adequate, $result->status);
        $this->assertEquals(2, $result->scheduled);
    }

    public function test_returns_understaffed_when_below_min(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 3,
            'max_employees' => null,
        ]);

        // Create only 1 shift (below minimum of 3)
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->user->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $result = $this->service->getTimeSlotCoverage(
            $date,
            '09:00',
            '17:00',
            $this->businessRole->id
        );

        $this->assertEquals(CoverageStatus::Understaffed, $result->status);
        $this->assertEquals(1, $result->scheduled);
        $this->assertEquals(3, $result->minRequired);
    }

    public function test_returns_overstaffed_when_above_max(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 1,
            'max_employees' => 2,
        ]);

        // Create 4 shifts (above maximum of 2)
        Shift::factory()->count(4)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->user->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $result = $this->service->getTimeSlotCoverage(
            $date,
            '09:00',
            '17:00',
            $this->businessRole->id
        );

        $this->assertEquals(CoverageStatus::Overstaffed, $result->status);
        $this->assertEquals(4, $result->scheduled);
        $this->assertEquals(2, $result->maxAllowed);
    }

    public function test_no_max_means_no_overstaffing(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 1,
            'max_employees' => null, // No max
        ]);

        // Create 10 shifts
        Shift::factory()->count(10)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->user->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $result = $this->service->getTimeSlotCoverage(
            $date,
            '09:00',
            '17:00',
            $this->businessRole->id
        );

        $this->assertEquals(CoverageStatus::Adequate, $result->status);
    }

    public function test_only_counts_assigned_shifts(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 2,
            'max_employees' => null,
        ]);

        // Create 1 assigned shift
        Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->user->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        // Create 3 unassigned shifts (should not be counted)
        Shift::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => null, // Unassigned
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => ShiftStatus::Published,
        ]);

        $result = $this->service->getTimeSlotCoverage(
            $date,
            '09:00',
            '17:00',
            $this->businessRole->id
        );

        $this->assertEquals(CoverageStatus::Understaffed, $result->status);
        $this->assertEquals(1, $result->scheduled); // Only the assigned shift
    }

    public function test_get_day_coverage_returns_all_requirements(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday

        // Create 2 requirements for Wednesday
        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3,
            'start_time' => '06:00',
            'end_time' => '14:00',
            'min_employees' => 2,
        ]);

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3,
            'start_time' => '14:00',
            'end_time' => '22:00',
            'min_employees' => 3,
        ]);

        $coverage = $this->service->getDayCoverage($date);

        $this->assertCount(2, $coverage);
    }

    public function test_get_coverage_summary_aggregates_correctly(): void
    {
        $startDate = Carbon::parse('2026-01-26'); // Monday
        $endDate = Carbon::parse('2026-01-30'); // Friday

        // Create requirements for weekdays
        foreach ([1, 2, 3, 4, 5] as $day) {
            StaffingRequirement::factory()->create([
                'tenant_id' => $this->tenant->id,
                'business_role_id' => $this->businessRole->id,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'min_employees' => 2,
            ]);
        }

        // No shifts created, so all should be understaffed
        $summary = $this->service->getCoverageSummary($startDate, $endDate);

        $this->assertEquals(5, $summary['understaffed']);
        $this->assertEquals(0, $summary['adequate']);
        $this->assertEquals(0, $summary['overstaffed']);
        $this->assertEquals(5, $summary['total']);
    }

    public function test_inactive_requirements_are_ignored(): void
    {
        $date = Carbon::parse('2026-01-28'); // Wednesday

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 5,
            'is_active' => false, // Inactive
        ]);

        $coverage = $this->service->getDayCoverage($date);

        $this->assertCount(0, $coverage);
    }

    public function test_coverage_result_message_generation(): void
    {
        $date = Carbon::parse('2026-01-28');

        StaffingRequirement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'business_role_id' => $this->businessRole->id,
            'day_of_week' => 3,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'min_employees' => 3,
        ]);

        $result = $this->service->getTimeSlotCoverage(
            $date,
            '09:00',
            '17:00',
            $this->businessRole->id
        );

        $message = $result->getMessage();

        $this->assertStringContainsString('understaffed', $message);
        $this->assertStringContainsString('09:00', $message);
        $this->assertStringContainsString('17:00', $message);
    }
}
