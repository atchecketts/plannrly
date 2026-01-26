<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::with('location')
            ->withCount('businessRoles')
            ->orderBy('name')
            ->paginate(15);

        return view('departments.index', compact('departments'));
    }

    public function mobile(): View
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::with('location')
            ->withCount('businessRoles')
            ->orderBy('name')
            ->get();

        $stats = [
            'active' => $departments->where('is_active', true)->count(),
            'inactive' => $departments->where('is_active', false)->count(),
        ];

        return view('departments.admin-mobile-index', compact('departments', 'stats'));
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
