<?php

namespace App\Http\Controllers;

use App\Enums\ShiftStatus;
use App\Enums\SwapRequestStatus;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use App\Notifications\SwapRequestNotification;
use App\Notifications\SwapRequestResponseNotification;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftSwapController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'status' => 'status',
        'requested' => 'created_at',
    ];

    private const GROUPABLE_COLUMNS = ['status', 'requested'];

    public function index(Request $request): View
    {
        $user = auth()->user();
        $status = $request->query('status');

        $query = ShiftSwapRequest::with([
            'requestingUser',
            'targetUser',
            'requestingShift.businessRole',
            'requestingShift.department',
            'targetShift.businessRole',
        ]);

        if (! ($user->isSuperAdmin() || $user->isAdmin() || $user->isLocationAdmin() || $user->isDepartmentAdmin())) {
            $query->forUser($user->id);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getShiftSwapGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'requested', 'desc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $swapRequests = $query->get();
        } else {
            $swapRequests = $query->paginate(15)->withQueryString();
        }

        $counts = $this->getStatusCounts($user);

        // Get user's upcoming shifts for the "Request Swap" button
        $myUpcomingShifts = Shift::with(['businessRole', 'department'])
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Published)
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->get();

        return view('shift-swaps.index', compact('swapRequests', 'status', 'counts', 'myUpcomingShifts', 'sortParams', 'allGroups'));
    }

    private function getShiftSwapGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'status' => collect(SwapRequestStatus::cases())->map(fn ($status) => [
                'key' => 'status-'.$status->value,
                'label' => $status->label(),
            ])->toArray(),
            'requested' => $this->getAllGroupValues($query, 'created_at', fn ($date) => [
                'key' => 'requested-'.$date->format('Y-m-d'),
                'label' => $date->format('M d, Y'),
            ])->unique('key')->values()->toArray(),
            default => [],
        };
    }

    protected function getStatusCounts(User $user): array
    {
        $baseQuery = ShiftSwapRequest::query();

        if (! ($user->isSuperAdmin() || $user->isAdmin() || $user->isLocationAdmin() || $user->isDepartmentAdmin())) {
            $baseQuery->forUser($user->id);
        }

        return [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', SwapRequestStatus::Pending)->count(),
            'accepted' => (clone $baseQuery)->where('status', SwapRequestStatus::Accepted)->count(),
            'rejected' => (clone $baseQuery)->where('status', SwapRequestStatus::Rejected)->count(),
        ];
    }

    public function create(Shift $shift): View
    {
        $this->authorize('create', ShiftSwapRequest::class);

        $eligibleUsers = User::whereHas('businessRoles', function ($query) use ($shift) {
            $query->where('business_role_id', $shift->business_role_id);
        })
            ->where('tenant_id', $shift->tenant_id)
            ->where('id', '!=', auth()->id())
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $eligibleShifts = Shift::with('user')
            ->where('tenant_id', $shift->tenant_id)
            ->where('business_role_id', $shift->business_role_id)
            ->where('user_id', '!=', auth()->id())
            ->whereNotNull('user_id')
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->get();

        return view('shift-swaps.create', compact('shift', 'eligibleUsers', 'eligibleShifts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'requesting_shift_id' => ['required', 'exists:shifts,id'],
            'target_user_id' => ['required', 'exists:users,id'],
            'target_shift_id' => ['nullable', 'exists:shifts,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $shift = Shift::findOrFail($request->input('requesting_shift_id'));

        if ($shift->user_id !== auth()->id()) {
            abort(403, 'You can only request swaps for your own shifts.');
        }

        // Verify target user has the same business role
        $targetUser = User::findOrFail($request->input('target_user_id'));

        if ($targetUser->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Target user must be from the same organization.');
        }

        $hasMatchingRole = $targetUser->businessRoles()
            ->where('business_role_id', $shift->business_role_id)
            ->exists();

        if (! $hasMatchingRole) {
            return back()
                ->withInput()
                ->withErrors(['target_user_id' => 'The selected employee does not have the required role for this shift.']);
        }

        // Verify target shift belongs to target user and has same role (if provided)
        if ($request->filled('target_shift_id')) {
            $targetShift = Shift::findOrFail($request->input('target_shift_id'));

            if ($targetShift->user_id !== $targetUser->id) {
                return back()
                    ->withInput()
                    ->withErrors(['target_shift_id' => 'The selected shift does not belong to the target employee.']);
            }

            if ($targetShift->business_role_id !== $shift->business_role_id) {
                return back()
                    ->withInput()
                    ->withErrors(['target_shift_id' => 'The target shift must have the same role as your shift.']);
            }
        }

        $swapRequest = ShiftSwapRequest::create([
            'tenant_id' => auth()->user()->tenant_id,
            'requesting_user_id' => auth()->id(),
            'target_user_id' => $request->input('target_user_id'),
            'requesting_shift_id' => $request->input('requesting_shift_id'),
            'target_shift_id' => $request->input('target_shift_id'),
            'reason' => $request->input('reason'),
            'status' => SwapRequestStatus::Pending,
        ]);

        // Notify target user of the swap request
        $swapRequest->load(['requestingUser', 'targetUser', 'requestingShift.location']);
        $targetUser->notify(new SwapRequestNotification($swapRequest));

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap request sent.');
    }

    public function accept(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('respond', $swapRequest);

        $swapRequest->accept();

        // Notify the requester that their swap was accepted
        $swapRequest->load(['requestingUser', 'targetUser', 'requestingShift']);
        $swapRequest->requestingUser->notify(new SwapRequestResponseNotification($swapRequest, 'accepted'));

        $requiresAdminApproval = auth()->user()->tenant?->tenantSettings?->require_admin_approval_for_swaps ?? true;
        $message = $requiresAdminApproval
            ? 'Swap request accepted. Awaiting admin approval.'
            : 'Swap request accepted. Shifts have been swapped.';

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', $message);
    }

    public function reject(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('respond', $swapRequest);

        $swapRequest->reject();

        // Notify the requester that their swap was rejected
        $swapRequest->load(['requestingUser', 'targetUser', 'requestingShift']);
        $swapRequest->requestingUser->notify(new SwapRequestResponseNotification($swapRequest, 'rejected'));

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap request rejected.');
    }

    public function cancel(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('cancel', $swapRequest);

        $swapRequest->cancel();

        // Notify the target user that the swap was cancelled
        $swapRequest->load(['requestingUser', 'targetUser', 'requestingShift']);
        if ($swapRequest->targetUser) {
            $swapRequest->targetUser->notify(new SwapRequestResponseNotification($swapRequest, 'cancelled'));
        }

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap request cancelled.');
    }

    public function approve(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('adminApprove', $swapRequest);

        $swapRequest->adminApprove(auth()->user());

        // Notify both users that the swap was approved and executed
        $swapRequest->load(['requestingUser', 'targetUser', 'requestingShift']);
        $swapRequest->requestingUser->notify(new SwapRequestResponseNotification($swapRequest, 'admin_approved'));
        if ($swapRequest->targetUser) {
            $swapRequest->targetUser->notify(new SwapRequestResponseNotification($swapRequest, 'admin_approved'));
        }

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap approved and executed.');
    }
}
