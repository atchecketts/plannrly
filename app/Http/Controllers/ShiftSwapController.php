<?php

namespace App\Http\Controllers;

use App\Enums\SwapRequestStatus;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftSwapController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isLocationAdmin() || $user->isDepartmentAdmin()) {
            $swapRequests = ShiftSwapRequest::with([
                'requestingUser',
                'targetUser',
                'requestingShift.businessRole',
                'targetShift.businessRole',
            ])
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            $swapRequests = ShiftSwapRequest::with([
                'requestingUser',
                'targetUser',
                'requestingShift.businessRole',
                'targetShift.businessRole',
            ])
                ->forUser($user->id)
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('shift-swaps.index', compact('swapRequests'));
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

        ShiftSwapRequest::create([
            'tenant_id' => auth()->user()->tenant_id,
            'requesting_user_id' => auth()->id(),
            'target_user_id' => $request->input('target_user_id'),
            'requesting_shift_id' => $request->input('requesting_shift_id'),
            'target_shift_id' => $request->input('target_shift_id'),
            'reason' => $request->input('reason'),
            'status' => SwapRequestStatus::Pending,
        ]);

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap request sent.');
    }

    public function accept(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('respond', $swapRequest);

        $swapRequest->accept();

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap request accepted. Awaiting admin approval.');
    }

    public function reject(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('respond', $swapRequest);

        $swapRequest->reject();

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap request rejected.');
    }

    public function cancel(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('cancel', $swapRequest);

        $swapRequest->cancel();

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap request cancelled.');
    }

    public function approve(ShiftSwapRequest $swapRequest): RedirectResponse
    {
        $this->authorize('adminApprove', $swapRequest);

        $swapRequest->adminApprove(auth()->user());

        return redirect()
            ->route('shift-swaps.index')
            ->with('success', 'Swap approved and executed.');
    }
}
