<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessRole\StoreBusinessRoleRequest;
use App\Http\Requests\BusinessRole\UpdateBusinessRoleRequest;
use App\Models\BusinessRole;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessRoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', BusinessRole::class);

        $businessRoles = BusinessRole::with('department.location')
            ->withCount('users')
            ->orderBy('name')
            ->paginate(15);

        return view('business-roles.index', compact('businessRoles'));
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
