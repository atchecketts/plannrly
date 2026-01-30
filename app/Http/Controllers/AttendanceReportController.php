<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Services\AttendanceReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $this->authorize('viewReports', User::class);

        [$startDate, $endDate] = $this->getDateRange($request);
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->input('location_id') : null;

        $summary = $this->reportService->generateAttendanceSummary(
            $startDate,
            $endDate,
            $departmentId,
            $locationId
        );

        $departments = Department::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
        $locations = Location::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        return view('reports.attendance.index', compact(
            'summary',
            'startDate',
            'endDate',
            'departments',
            'locations',
            'departmentId',
            'locationId'
        ));
    }

    public function punctuality(Request $request): View
    {
        $user = auth()->user();
        $this->authorize('viewReports', User::class);

        [$startDate, $endDate] = $this->getDateRange($request);
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->input('location_id') : null;

        $report = $this->reportService->generatePunctualityReport(
            $startDate,
            $endDate,
            $departmentId,
            $locationId
        );

        $departments = Department::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
        $locations = Location::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        return view('reports.attendance.punctuality', compact(
            'report',
            'startDate',
            'endDate',
            'departments',
            'locations',
            'departmentId',
            'locationId'
        ));
    }

    public function hours(Request $request): View
    {
        $user = auth()->user();
        $this->authorize('viewReports', User::class);

        [$startDate, $endDate] = $this->getDateRange($request);
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->input('location_id') : null;

        $report = $this->reportService->generateHoursWorkedReport(
            $startDate,
            $endDate,
            $departmentId,
            $locationId
        );

        $departments = Department::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
        $locations = Location::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        return view('reports.attendance.hours', compact(
            'report',
            'startDate',
            'endDate',
            'departments',
            'locations',
            'departmentId',
            'locationId'
        ));
    }

    public function overtime(Request $request): View
    {
        $user = auth()->user();
        $this->authorize('viewReports', User::class);

        [$startDate, $endDate] = $this->getDateRange($request);
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->input('location_id') : null;

        $report = $this->reportService->generateOvertimeReport(
            $startDate,
            $endDate,
            $departmentId,
            $locationId
        );

        $departments = Department::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
        $locations = Location::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        return view('reports.attendance.overtime', compact(
            'report',
            'startDate',
            'endDate',
            'departments',
            'locations',
            'departmentId',
            'locationId'
        ));
    }

    public function absence(Request $request): View
    {
        $user = auth()->user();
        $this->authorize('viewReports', User::class);

        [$startDate, $endDate] = $this->getDateRange($request);
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->input('location_id') : null;

        $report = $this->reportService->generateAbsenceReport(
            $startDate,
            $endDate,
            $departmentId,
            $locationId
        );

        $departments = Department::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
        $locations = Location::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        return view('reports.attendance.absence', compact(
            'report',
            'startDate',
            'endDate',
            'departments',
            'locations',
            'departmentId',
            'locationId'
        ));
    }

    public function employee(Request $request, User $user): View
    {
        $this->authorize('viewReports', User::class);

        // Ensure user is from same tenant
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        [$startDate, $endDate] = $this->getDateRange($request);

        $report = $this->reportService->getEmployeeSummary(
            $user->id,
            $startDate,
            $endDate
        );

        return view('reports.attendance.employee', compact(
            'report',
            'user',
            'startDate',
            'endDate'
        ));
    }

    public function department(Request $request, Department $department): View
    {
        $this->authorize('viewReports', User::class);

        // Ensure department is from same tenant
        if ($department->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        [$startDate, $endDate] = $this->getDateRange($request);

        $report = $this->reportService->getDepartmentSummary(
            $department->id,
            $startDate,
            $endDate
        );

        return view('reports.attendance.department', compact(
            'report',
            'department',
            'startDate',
            'endDate'
        ));
    }

    public function export(Request $request, string $type): Response
    {
        $this->authorize('viewReports', User::class);

        [$startDate, $endDate] = $this->getDateRange($request);
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->input('location_id') : null;

        $reportData = match ($type) {
            'punctuality' => $this->reportService->generatePunctualityReport($startDate, $endDate, $departmentId, $locationId),
            'hours' => $this->reportService->generateHoursWorkedReport($startDate, $endDate, $departmentId, $locationId),
            'overtime' => $this->reportService->generateOvertimeReport($startDate, $endDate, $departmentId, $locationId),
            'absence' => $this->reportService->generateAbsenceReport($startDate, $endDate, $departmentId, $locationId),
            default => abort(404),
        };

        $csv = $this->reportService->exportToCsv($type, $reportData);
        $filename = "attendance-{$type}-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.csv";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get date range from request, defaulting to current month.
     *
     * @return array{Carbon, Carbon}
     */
    protected function getDateRange(Request $request): array
    {
        if ($request->filled('start_date') && $request->filled('end_date')) {
            return [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay(),
            ];
        }

        // Default to current month
        return [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ];
    }
}
