<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Models\Location;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'name' => 'name',
        'city' => 'city',
        'departments' => 'departments_count',
        'status' => 'is_active',
    ];

    private const GROUPABLE_COLUMNS = ['name', 'city', 'departments', 'status'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Location::class);

        $query = Location::with('departments')
            ->withCount('departments');

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getLocationGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'name', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $locations = $query->get();
        } else {
            $locations = $query->paginate(15)->withQueryString();
        }

        return view('locations.index', compact('locations', 'sortParams', 'allGroups'));
    }

    private function getLocationGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'name' => $this->getAllGroupValues($query, 'name', fn ($name) => [
                'key' => 'name-'.strtoupper(substr($name, 0, 1)),
                'label' => strtoupper(substr($name, 0, 1)),
            ])->unique('key')->values()->toArray(),
            'city' => $this->getAllGroupValues($query, 'city', fn ($city) => [
                'key' => 'city-'.\Str::slug($city ?: 'no-city'),
                'label' => $city ?: 'No City',
            ])->toArray(),
            'departments' => $this->getAllGroupValues($query, 'departments_count', fn ($count) => [
                'key' => 'departments-'.$count,
                'label' => $count.' '.str('Department')->plural($count),
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
        $this->authorize('create', Location::class);

        return view('locations.create');
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $location = Location::create($request->validated());

        return redirect()
            ->route('locations.show', $location)
            ->with('success', 'Location created successfully.');
    }

    public function show(Location $location): View
    {
        $this->authorize('view', $location);

        $location->load(['departments.businessRoles']);

        return view('locations.show', compact('location'));
    }

    public function edit(Location $location): View
    {
        $this->authorize('update', $location);

        return view('locations.edit', compact('location'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()
            ->route('locations.show', $location)
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $this->authorize('delete', $location);

        $location->delete();

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
