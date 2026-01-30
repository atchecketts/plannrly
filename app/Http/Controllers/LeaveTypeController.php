<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveType\StoreLeaveTypeRequest;
use App\Http\Requests\LeaveType\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'name' => 'name',
        'requests' => 'leave_requests_count',
        'status' => 'is_active',
    ];

    private const GROUPABLE_COLUMNS = ['name', 'requests', 'status'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveType::class);

        $query = LeaveType::withCount('leaveRequests');

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getLeaveTypeGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'name', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $leaveTypes = $query->get();
        } else {
            $leaveTypes = $query->paginate(15)->withQueryString();
        }

        return view('leave-types.index', compact('leaveTypes', 'sortParams', 'allGroups'));
    }

    private function getLeaveTypeGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'name' => $this->getAllGroupValues($query, 'name', fn ($name) => [
                'key' => 'name-'.strtoupper(substr($name, 0, 1)),
                'label' => strtoupper(substr($name, 0, 1)),
            ])->unique('key')->values()->toArray(),
            'requests' => $this->getAllGroupValues($query, 'leave_requests_count', fn ($count) => [
                'key' => 'requests-'.$count,
                'label' => $count.' '.str('Request')->plural($count),
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
        $this->authorize('create', LeaveType::class);

        return view('leave-types.create');
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;

        LeaveType::create($data);

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    public function edit(LeaveType $leaveType): View
    {
        $this->authorize('update', $leaveType);

        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update($request->validated());

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('delete', $leaveType);

        if ($leaveType->leaveRequests()->exists()) {
            return redirect()
                ->route('leave-types.index')
                ->with('error', 'Cannot delete leave type that has existing leave requests.');
        }

        $leaveType->delete();

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave type deleted successfully.');
    }
}
