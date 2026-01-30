<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Location;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'name' => 'name',
        'roles' => 'business_roles_count',
        'status' => 'is_active',
    ];

    private const GROUPABLE_COLUMNS = ['name', 'roles', 'status'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $query = Department::with('location')
            ->withCount('businessRoles');

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getDepartmentGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'name', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $departments = $query->get();
        } else {
            $departments = $query->paginate(15)->withQueryString();
        }

        return view('departments.index', compact('departments', 'sortParams', 'allGroups'));
    }

    private function getDepartmentGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'name' => $this->getAllGroupValues($query, 'name', fn ($name) => [
                'key' => 'name-'.strtoupper(substr($name, 0, 1)),
                'label' => strtoupper(substr($name, 0, 1)),
            ])->unique('key')->values()->toArray(),
            'roles' => $this->getAllGroupValues($query, 'business_roles_count', fn ($count) => [
                'key' => 'roles-'.$count,
                'label' => $count.' '.str('Role')->plural($count),
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
        $this->authorize('create', Department::class);

        $locations = Location::active()->orderBy('name')->get();

        return view('departments.create', compact('locations'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;

        $department = Department::create($data);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        $locations = Location::active()->orderBy('name')->get();

        return view('departments.edit', compact('department', 'locations'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
