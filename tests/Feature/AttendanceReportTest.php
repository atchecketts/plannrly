<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Enums\TimeEntryStatus;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AttendanceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $admin;

    protected User $employee;

    protected Department $department;

    protected Location $location;

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
        $this->department = Department::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->location = Location::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_admin_can_access_attendance_reports_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.index'));

        $response->assertOk();
        $response->assertViewIs('reports.attendance.index');
        $response->assertViewHas('summary');
    }

    public function test_employee_cannot_access_attendance_reports(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('reports.attendance.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_punctuality_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.punctuality'));

        $response->assertOk();
        $response->assertViewIs('reports.attendance.punctuality');
        $response->assertViewHas('report');
    }

    public function test_admin_can_access_hours_worked_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.hours'));

        $response->assertOk();
        $response->assertViewIs('reports.attendance.hours');
        $response->assertViewHas('report');
    }

    public function test_admin_can_access_overtime_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.overtime'));

        $response->assertOk();
        $response->assertViewIs('reports.attendance.overtime');
        $response->assertViewHas('report');
    }

    public function test_admin_can_access_absence_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.absence'));

        $response->assertOk();
        $response->assertViewIs('reports.attendance.absence');
        $response->assertViewHas('report');
    }

    public function test_admin_can_view_employee_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.employee', $this->employee));

        $response->assertOk();
        $response->assertViewIs('reports.attendance.employee');
        $response->assertViewHas('report');
    }

    public function test_admin_can_view_department_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.department', $this->department));

        $response->assertOk();
        $response->assertViewIs('reports.attendance.department');
        $response->assertViewHas('report');
    }

    public function test_admin_cannot_view_employee_from_other_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherEmployee = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherEmployee->id,
            'system_role' => SystemRole::Employee,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.employee', $otherEmployee));

        $response->assertForbidden();
    }

    public function test_reports_respect_date_range_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.index', [
                'start_date' => now()->subMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertOk();
    }

    public function test_reports_respect_department_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.index', [
                'department_id' => $this->department->id,
            ]));

        $response->assertOk();
    }

    public function test_reports_respect_location_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.index', [
                'location_id' => $this->location->id,
            ]));

        $response->assertOk();
    }

    public function test_can_export_punctuality_report_to_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.export', 'punctuality'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_can_export_hours_report_to_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.export', 'hours'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_can_export_overtime_report_to_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.export', 'overtime'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_can_export_absence_report_to_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.attendance.export', 'absence'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_attendance_report_service_calculates_attendance_rate(): void
    {
        // Create scheduled shifts
        $shift1 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $shift2 = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(2),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Create time entry for one shift (worked)
        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift1->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getAttendanceRate(
            now()->subWeek(),
            now(),
            null,
            null,
            $this->employee->id
        );

        $this->assertEquals(2, $result['scheduled']);
        $this->assertEquals(1, $result['worked']);
        $this->assertEquals(50.0, $result['rate']);
    }

    public function test_attendance_report_service_calculates_punctuality_rate(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // On-time clock in
        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getPunctualityRate(
            now()->subWeek(),
            now(),
            null,
            null,
            $this->employee->id
        );

        $this->assertEquals(1, $result['total']);
        $this->assertEquals(1, $result['on_time']);
        $this->assertEquals(100.0, $result['rate']);
    }

    public function test_attendance_report_service_detects_late_arrivals(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Late clock in (30 minutes late)
        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 30),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getPunctualityRate(
            now()->subWeek(),
            now(),
            null,
            null,
            $this->employee->id
        );

        $this->assertEquals(1, $result['total']);
        $this->assertEquals(1, $result['late']);
        $this->assertEquals(0.0, $result['rate']);
    }

    public function test_attendance_report_service_calculates_overtime(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 0,
        ]);

        // Worked 9 hours (1 hour overtime on 8-hour shift)
        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(18, 0),
            'actual_break_minutes' => 0,
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getOvertimeHours(
            now()->subWeek(),
            now(),
            null,
            null,
            $this->employee->id
        );

        $this->assertEquals(1, $result['entries']);
        $this->assertEquals(1.0, $result['hours']); // 60 minutes = 1 hour
    }

    public function test_attendance_report_service_counts_missed_shifts(): void
    {
        // Create a missed shift entry
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => null,
            'clock_out_at' => null,
            'status' => TimeEntryStatus::Missed,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getMissedShifts(
            now()->subWeek(),
            now(),
            null,
            null,
            $this->employee->id
        );

        $this->assertEquals(1, $result['count']);
    }

    public function test_attendance_report_service_calculates_total_hours_worked(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // 8 hours worked, 30 minutes break = 7.5 net hours
        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'actual_break_minutes' => 30,
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getTotalHoursWorked(
            now()->subWeek(),
            now(),
            null,
            null,
            $this->employee->id
        );

        $this->assertEquals(7.5, $result);
    }

    public function test_attendance_report_service_generates_employee_summary(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getEmployeeSummary(
            $this->employee->id,
            now()->subWeek(),
            now()
        );

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('attendance', $result);
        $this->assertArrayHasKey('punctuality', $result);
        $this->assertArrayHasKey('overtime', $result);
        $this->assertArrayHasKey('undertime', $result);
        $this->assertArrayHasKey('missed_shifts', $result);
    }

    public function test_attendance_report_service_generates_department_summary(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->getDepartmentSummary(
            $this->department->id,
            now()->subWeek(),
            now()
        );

        $this->assertArrayHasKey('department', $result);
        $this->assertArrayHasKey('attendance', $result);
        $this->assertArrayHasKey('punctuality', $result);
        $this->assertArrayHasKey('overtime', $result);
    }

    public function test_attendance_report_service_generates_punctuality_report(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $result = $service->generatePunctualityReport(
            now()->subWeek(),
            now()
        );

        $this->assertArrayHasKey('entries', $result);
        $this->assertArrayHasKey('by_user', $result);
        $this->assertArrayHasKey('summary', $result);
    }

    public function test_attendance_report_service_exports_to_csv(): void
    {
        $shift = Shift::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'location_id' => $this->location->id,
            'date' => now()->subDays(1),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        TimeEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now()->subDays(1)->setTime(9, 0),
            'clock_out_at' => now()->subDays(1)->setTime(17, 0),
            'status' => TimeEntryStatus::ClockedOut,
        ]);

        $service = new AttendanceReportService;
        $reportData = $service->generatePunctualityReport(now()->subWeek(), now());
        $csv = $service->exportToCsv('punctuality', $reportData);

        $this->assertStringContainsString('Employee,On Time,Late,Early,Total,Punctuality Rate,Avg Late Minutes', $csv);
    }
}
