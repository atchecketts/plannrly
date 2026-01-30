<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffingRequirement\StoreStaffingRequirementRequest;
use App\Http\Requests\StaffingRequirement\UpdateStaffingRequirementRequest;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\StaffingRequirement;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffingRequirementController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'role' => 'business_role_id',
        'day' => 'day_of_week',
        'start_time' => 'start_time',
        'min' => 'min_employees',
        'status' => 'is_active',
    ];

    private const GROUPABLE_COLUMNS = ['role', 'day', 'status'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StaffingRequirement::class);

        $query = StaffingRequirement::with(['location', 'department', 'businessRole']);

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getStaffingRequirementGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'day', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $staffingRequirements = $query->get();
        } else {
            $staffingRequirements = $query->paginate(15)->withQueryString();
        }

        return view('staffing-requirements.index', compact('staffingRequirements', 'sortParams', 'allGroups'));
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function getStaffingRequirementGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return match ($group) {
            'role' => $this->getAllGroupValues($query, 'business_role_id', function ($roleId) {
                $role = BusinessRole::find($roleId);

                return [
                    'key' => 'role-'.$roleId,
                    'label' => $role?->name ?? 'Unknown Role',
                ];
            })->toArray(),
            'day' => $this->getAllGroupValues($query, 'day_of_week', fn ($day) => [
                'key' => 'day-'.$day,
                'label' => $days[$day] ?? 'Unknown',
            ])->toArray(),
            'status' => $this->getAllGroupValues($query, 'is_active', fn ($isActive) => [
                'key' => 'status-'.($isActive ? 'active' : 'inactive'),
                'label' => $isActive ? 'Active' : 'Inactive',
            ])->toArray(),
            default => [],
        };
    }

    public function create(): View
    {
        $this->authorize('create', StaffingRequirement::class);

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with('location')->active()->orderBy('name')->get();
        $businessRoles = BusinessRole::with('department')->active()->orderBy('name')->get();

        $daysOfWeek = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return view('staffing-requirements.create', compact('locations', 'departments', 'businessRoles', 'daysOfWeek'));
    }

    public function store(StoreStaffingRequirementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;

        StaffingRequirement::create($data);

        return redirect()
            ->route('staffing-requirements.index')
            ->with('success', 'Staffing requirement created successfully.');
    }

    public function edit(StaffingRequirement $staffingRequirement): View
    {
        $this->authorize('update', $staffingRequirement);

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with('location')->active()->orderBy('name')->get();
        $businessRoles = BusinessRole::with('department')->active()->orderBy('name')->get();

        $daysOfWeek = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return view('staffing-requirements.edit', compact('staffingRequirement', 'locations', 'departments', 'businessRoles', 'daysOfWeek'));
    }

    public function update(UpdateStaffingRequirementRequest $request, StaffingRequirement $staffingRequirement): RedirectResponse
    {
        $staffingRequirement->update($request->validated());

        return redirect()
            ->route('staffing-requirements.index')
            ->with('success', 'Staffing requirement updated successfully.');
    }

    public function destroy(StaffingRequirement $staffingRequirement): RedirectResponse
    {
        $this->authorize('delete', $staffingRequirement);

        $staffingRequirement->delete();

        return redirect()
            ->route('staffing-requirements.index')
            ->with('success', 'Staffing requirement deleted successfully.');
    }
}
