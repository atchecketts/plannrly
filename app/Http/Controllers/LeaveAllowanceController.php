<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveAllowance\StoreLeaveAllowanceRequest;
use App\Http\Requests\LeaveAllowance\UpdateLeaveAllowanceRequest;
use App\Models\LeaveAllowance;
use App\Models\LeaveType;
use App\Models\User;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveAllowanceController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'total' => 'total_days',
        'used' => 'used_days',
        'carried_over' => 'carried_over_days',
    ];

    private const GROUPABLE_COLUMNS = ['total', 'used', 'carried_over'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveAllowance::class);

        $year = $request->query('year', now()->year);
        $leaveTypeId = $request->query('leave_type');

        $query = LeaveAllowance::with(['user', 'leaveType'])
            ->where('year', $year);

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getLeaveAllowanceGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'user_id', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $allowances = $query->get();
        } else {
            $allowances = $query->paginate(15)->withQueryString();
        }

        $leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('name')
            ->get();

        $years = range(now()->year + 1, now()->year - 5);

        return view('leave-allowances.index', compact('allowances', 'leaveTypes', 'year', 'leaveTypeId', 'years', 'sortParams', 'allGroups'));
    }

    private function getLeaveAllowanceGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'total' => $this->getAllGroupValues($query, 'total_days', fn ($days) => [
                'key' => 'total-'.$days,
                'label' => number_format($days, 1).' Total Days',
            ])->toArray(),
            'used' => $this->getAllGroupValues($query, 'used_days', fn ($days) => [
                'key' => 'used-'.$days,
                'label' => number_format($days, 1).' Used Days',
            ])->toArray(),
            'carried_over' => $this->getAllGroupValues($query, 'carried_over_days', fn ($days) => [
                'key' => 'carried_over-'.$days,
                'label' => number_format($days, 1).' Carried Over',
            ])->toArray(),
            default => [],
        };
    }

    public function create(): View
    {
        $this->authorize('create', LeaveAllowance::class);

        $users = User::orderBy('first_name')->orderBy('last_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)
            ->where('affects_allowance', true)
            ->orderBy('name')
            ->get();
        $years = range(now()->year + 1, now()->year - 2);

        return view('leave-allowances.create', compact('users', 'leaveTypes', 'years'));
    }

    public function store(StoreLeaveAllowanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['used_days'] = 0;

        LeaveAllowance::create($data);

        return redirect()
            ->route('leave-allowances.index', ['year' => $data['year']])
            ->with('success', 'Leave allowance created successfully.');
    }

    public function edit(LeaveAllowance $leaveAllowance): View
    {
        $this->authorize('update', $leaveAllowance);

        $leaveAllowance->load(['user', 'leaveType']);

        return view('leave-allowances.edit', compact('leaveAllowance'));
    }

    public function update(UpdateLeaveAllowanceRequest $request, LeaveAllowance $leaveAllowance): RedirectResponse
    {
        $leaveAllowance->update($request->validated());

        return redirect()
            ->route('leave-allowances.index', ['year' => $leaveAllowance->year])
            ->with('success', 'Leave allowance updated successfully.');
    }

    public function destroy(LeaveAllowance $leaveAllowance): RedirectResponse
    {
        $this->authorize('delete', $leaveAllowance);

        $year = $leaveAllowance->year;

        if ($leaveAllowance->used_days > 0) {
            return redirect()
                ->route('leave-allowances.index', ['year' => $year])
                ->with('error', 'Cannot delete allowance that has been used.');
        }

        $leaveAllowance->delete();

        return redirect()
            ->route('leave-allowances.index', ['year' => $year])
            ->with('success', 'Leave allowance deleted successfully.');
    }
}
