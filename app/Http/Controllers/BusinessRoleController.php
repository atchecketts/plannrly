<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessRole\StoreBusinessRoleRequest;
use App\Http\Requests\BusinessRole\UpdateBusinessRoleRequest;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessRoleController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'name' => 'name',
        'hourly_rate' => 'default_hourly_rate',
        'users' => 'users_count',
        'status' => 'is_active',
    ];

    private const GROUPABLE_COLUMNS = ['name', 'hourly_rate', 'users', 'status'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BusinessRole::class);

        $query = BusinessRole::with('department.location')
            ->withCount('users');

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getBusinessRoleGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'name', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $businessRoles = $query->get();
        } else {
            $businessRoles = $query->paginate(15)->withQueryString();
        }

        return view('business-roles.index', compact('businessRoles', 'sortParams', 'allGroups'));
    }

    private function getBusinessRoleGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'name' => $this->getAllGroupValues($query, 'name', fn ($name) => [
                'key' => 'name-'.strtoupper(substr($name, 0, 1)),
                'label' => strtoupper(substr($name, 0, 1)),
            ])->unique('key')->values()->toArray(),
            'hourly_rate' => $this->getAllGroupValues($query, 'default_hourly_rate', fn ($rate) => [
                'key' => 'hourly_rate-'.($rate ?? 'none'),
                'label' => $rate ? '$'.number_format($rate, 2) : 'No Rate',
            ])->toArray(),
            'users' => $this->getAllGroupValues($query, 'users_count', fn ($count) => [
                'key' => 'users-'.$count,
                'label' => $count.' '.str('User')->plural($count),
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
        $this->authorize('create', BusinessRole::class);

        $departments = Department::with('location')
            ->active()
            ->orderBy('name')
            ->get();

        return view('business-roles.create', compact('departments'));
    }

    public function store(StoreBusinessRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;

        BusinessRole::create($data);

        return redirect()
            ->route('business-roles.index')
            ->with('success', 'Business role created successfully.');
    }

    public function edit(BusinessRole $businessRole): View
    {
        $this->authorize('update', $businessRole);

        $departments = Department::with('location')
            ->active()
            ->orderBy('name')
            ->get();

        return view('business-roles.edit', compact('businessRole', 'departments'));
    }

    public function update(UpdateBusinessRoleRequest $request, BusinessRole $businessRole): RedirectResponse
    {
        $businessRole->update($request->validated());

        return redirect()
            ->route('business-roles.index')
            ->with('success', 'Business role updated successfully.');
    }

    public function destroy(BusinessRole $businessRole): RedirectResponse
    {
        $this->authorize('delete', $businessRole);

        $businessRole->delete();

        return redirect()
            ->route('business-roles.index')
            ->with('success', 'Business role deleted successfully.');
    }
}
