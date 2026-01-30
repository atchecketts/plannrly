<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'organization' => 'name',
        'users' => 'users_count',
        'status' => 'is_active',
        'created' => 'created_at',
    ];

    private const GROUPABLE_COLUMNS = ['organization', 'users', 'status', 'created'];

    public function index(Request $request): View
    {
        $query = Tenant::query()
            ->withCount('users')
            ->with('tenantSettings');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getTenantGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'created', 'desc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $tenants = $query->get();
        } else {
            $tenants = $query->paginate(15)->withQueryString();
        }

        return view('super-admin.tenants.index', compact('tenants', 'sortParams', 'allGroups'));
    }

    /**
     * Get all unique group values for tenants.
     */
    private function getTenantGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'organization' => $this->getAllGroupValues(
                $query,
                'name',
                fn ($name) => [
                    'key' => 'organization-'.strtoupper(substr($name, 0, 1)),
                    'label' => strtoupper(substr($name, 0, 1)),
                ]
            )->unique('key')->values()->toArray(),
            'users' => $this->getAllGroupValues(
                $query,
                'users_count',
                fn ($count) => [
                    'key' => 'users-'.$count,
                    'label' => $count.' '.str('User')->plural($count),
                ]
            )->toArray(),
            'status' => $this->getAllGroupValues(
                $query,
                'is_active',
                fn ($isActive) => [
                    'key' => 'status-'.($isActive ? 'active' : 'inactive'),
                    'label' => $isActive ? 'Active' : 'Inactive',
                ]
            )->toArray(),
            'created' => $this->getAllGroupValues(
                $query,
                'created_at',
                fn ($date) => [
                    'key' => 'created-'.$date->format('Y-m-d'),
                    'label' => $date->format('M d, Y'),
                ]
            )->unique('key')->values()->toArray(),
            default => [],
        };
    }

    public function show(Tenant $tenant): View
    {
        $tenant->loadCount(['users', 'locations', 'departments', 'businessRoles']);
        $tenant->load(['tenantSettings', 'users' => fn ($q) => $q->latest()->take(10)]);

        $stats = [
            'total_users' => $tenant->users_count,
            'active_users' => $tenant->users()->where('is_active', true)->count(),
            'total_locations' => $tenant->locations_count,
            'total_departments' => $tenant->departments_count,
            'total_business_roles' => $tenant->business_roles_count,
        ];

        return view('super-admin.tenants.show', compact('tenant', 'stats'));
    }

    public function edit(Tenant $tenant): View
    {
        return view('super-admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $tenant->update($validated);

        return redirect()->route('super-admin.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    public function toggleStatus(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['is_active' => ! $tenant->is_active]);

        $status = $tenant->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Tenant {$status} successfully.");
    }
}
