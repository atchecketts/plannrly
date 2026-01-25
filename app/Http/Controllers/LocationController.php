<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Location::class);

        $locations = Location::with('departments')
            ->withCount('departments')
            ->orderBy('name')
            ->paginate(15);

        return view('locations.index', compact('locations'));
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
