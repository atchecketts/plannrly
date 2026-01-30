<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\TimesheetExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetExportTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    private TenantSettings $settings;

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
            'system_role' => SystemRole::Admin,
        ]);

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee,
        ]);

        $this->settings = TenantSettings::where('tenant_id', $this->tenant->id)->first();
        $this->settings->update([
            'enable_clock_in_out' => true,
            'timezone' => 'UTC',
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

    public function test_admin_can_export_timesheets_as_csv(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => today()->setTime(9, 5),
            'clock_out_at' => today()->setTime(17, 10),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.export', ['week_start' => today()->startOfWeek()->format('Y-m-d')]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="timesheet-'.today()->startOfWeek()->format('Y-m-d').'-to-'.today()->endOfWeek()->format('Y-m-d').'.csv"');

        // Check CSV contains header
        $this->assertStringContainsString('Employee Name', $response->getContent());
        $this->assertStringContainsString('Scheduled Start', $response->getContent());
        $this->assertStringContainsString('Actual Start', $response->getContent());
    }

    public function test_employee_can_export_their_own_timesheets(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.export', ['week_start' => today()->startOfWeek()->format('Y-m-d')]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Check CSV contains employee's data
        $this->assertStringContainsString($this->employee->full_name, $response->getContent());
    }

    public function test_employee_cannot_export_other_users_timesheets(): void
    {
        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $otherEmployee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherEmployee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
        ]);

        // Employee tries to export with user_id filter
        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.export', [
                'week_start' => today()->startOfWeek()->format('Y-m-d'),
                'user_id' => $otherEmployee->id,
            ]));

        $response->assertOk();

        // Should only contain their own data (which is none), not the other employee's
        $this->assertStringNotContainsString($otherEmployee->full_name, $response->getContent());
    }

    public function test_admin_can_export_payroll_csv(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.export.payroll', ['week_start' => today()->startOfWeek()->format('Y-m-d')]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="payroll-'.today()->startOfWeek()->format('Y-m-d').'-to-'.today()->endOfWeek()->format('Y-m-d').'.csv"');

        // Check payroll CSV format headers
        $this->assertStringContainsString('Employee ID', $response->getContent());
        $this->assertStringContainsString('Hours Worked', $response->getContent());
        $this->assertStringContainsString('Overtime Hours', $response->getContent());
    }

    public function test_employee_cannot_export_payroll_csv(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('timesheets.export.payroll'));

        $response->assertForbidden();
    }

    public function test_export_filters_by_department(): void
    {
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
        ]);

        $shift1 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
        ]);

        $otherBusinessRole = BusinessRole::factory()->create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $otherDepartment->id,
        ]);

        $shift2 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $otherDepartment->id,
            'business_role_id' => $otherBusinessRole->id,
            'user_id' => $this->admin->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift1->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'shift_id' => $shift2->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.export', [
                'week_start' => today()->startOfWeek()->format('Y-m-d'),
                'department_id' => $this->department->id,
            ]));

        $response->assertOk();

        // Should contain first department employee
        $this->assertStringContainsString($this->employee->full_name, $response->getContent());
        // Should NOT contain other department employee
        $this->assertStringNotContainsString($this->admin->full_name, $response->getContent());
    }

    public function test_export_service_generates_correct_csv_format(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $entry = TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => today()->setTime(9, 5),
            'clock_out_at' => today()->setTime(17, 10),
            'actual_break_minutes' => 30,
            'notes' => 'Test note',
        ]);

        $service = app(TimesheetExportService::class);
        $entries = $service->getExportData(today()->startOfWeek(), today()->endOfWeek());
        $csv = $service->exportToCsv($entries);

        // Check CSV has correct structure
        $lines = explode("\n", trim($csv));
        $this->assertCount(2, $lines); // Header + 1 data row

        // Check header
        $header = str_getcsv($lines[0]);
        $this->assertContains('Employee Name', $header);
        $this->assertContains('Date', $header);
        $this->assertContains('Status', $header);

        // Check data row
        $dataRow = str_getcsv($lines[1]);
        $this->assertContains($this->employee->full_name, $dataRow);
        $this->assertContains('Test note', $dataRow);
    }

    public function test_export_service_calculates_summary_correctly(): void
    {
        $shift1 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->employee->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 30,
        ]);

        $shift2 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'business_role_id' => $this->businessRole->id,
            'user_id' => $this->admin->id,
            'date' => today(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 30,
        ]);

        // Employee worked 8 hours
        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift1->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
            'actual_break_minutes' => 30,
        ]);

        // Admin worked 9 hours (1 hour overtime based on variance)
        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'shift_id' => $shift2->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(18, 0),
            'actual_break_minutes' => 30,
        ]);

        $service = app(TimesheetExportService::class);
        $entries = $service->getExportData(today()->startOfWeek(), today()->endOfWeek());
        $summary = $service->getExportSummary($entries);

        $this->assertEquals(2, $summary['total_entries']);
        $this->assertEquals(2, $summary['total_employees']);
        $this->assertGreaterThan(0, $summary['total_hours']);
    }

    public function test_export_empty_week_returns_headers_only(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.export', ['week_start' => today()->startOfWeek()->format('Y-m-d')]));

        $response->assertOk();

        // Should still have headers
        $this->assertStringContainsString('Employee Name', $response->getContent());

        // Count lines - should be just header
        $lines = explode("\n", trim($response->getContent()));
        $this->assertCount(1, $lines);
    }

    public function test_tenant_isolation_for_exports(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherEmployee = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $otherLocation = Location::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
        ]);
        $otherBusinessRole = BusinessRole::factory()->create([
            'tenant_id' => $otherTenant->id,
            'department_id' => $otherDepartment->id,
        ]);

        $otherShift = Shift::factory()->create([
            'tenant_id' => $otherTenant->id,
            'location_id' => $otherLocation->id,
            'department_id' => $otherDepartment->id,
            'business_role_id' => $otherBusinessRole->id,
            'user_id' => $otherEmployee->id,
            'date' => today(),
        ]);

        TimeEntry::factory()->clockedOut()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $otherEmployee->id,
            'shift_id' => $otherShift->id,
            'clock_in_at' => today()->setTime(9, 0),
            'clock_out_at' => today()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('timesheets.export', ['week_start' => today()->startOfWeek()->format('Y-m-d')]));

        $response->assertOk();

        // Should NOT contain other tenant's data
        $this->assertStringNotContainsString($otherEmployee->full_name, $response->getContent());
    }
}
