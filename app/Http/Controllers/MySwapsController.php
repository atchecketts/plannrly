<?php

namespace App\Http\Controllers;

use App\Enums\SwapRequestStatus;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MySwapsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Outgoing requests (I requested)
        $outgoingRequests = ShiftSwapRequest::with([
            'requestingShift.department',
            'requestingShift.businessRole',
            'targetShift.user',
            'targetShift.department',
        ])
            ->where('requesting_user_id', $user->id)
            ->latest()
            ->get();

        // Incoming requests (someone wants my shift or offered me theirs)
        $incomingRequests = ShiftSwapRequest::with([
            'requestingShift.user',
            'requestingShift.department',
            'requestingShift.businessRole',
            'targetShift',
        ])
            ->where('target_user_id', $user->id)
            ->where('status', SwapRequestStatus::Pending)
            ->latest()
            ->get();

        return view('my-swaps.index', [
            'outgoingRequests' => $outgoingRequests,
            'incomingRequests' => $incomingRequests,
        ]);
    }

    public function create(Shift $shift): View
    {
        $user = auth()->user();

        // Verify the shift belongs to the user
        if ($shift->user_id !== $user->id) {
            abort(403);
        }

        // Get available employees who could take this shift
        $availableUsers = User::where('tenant_id', $user->tenant_id)
            ->where('id', '!=', $user->id)
            ->whereHas('businessRoles', function ($query) use ($shift) {
                $query->where('business_role_id', $shift->business_role_id);
            })
            ->active()
            ->get();

        return view('my-swaps.create', [
            'shift' => $shift,
            'availableUsers' => $availableUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'requesting_shift_id' => ['required', 'exists:shifts,id'],
            'target_user_id' => ['nullable', 'exists:users,id'],
            'target_shift_id' => ['nullable', 'exists:shifts,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify the requesting shift belongs to the user
        $shift = Shift::findOrFail($validated['requesting_shift_id']);
        if ($shift->user_id !== $user->id) {
            abort(403);
        }

        ShiftSwapRequest::create([
            'tenant_id' => $user->tenant_id,
            'requesting_user_id' => $user->id,
            'requesting_shift_id' => $validated['requesting_shift_id'],
            'target_user_id' => $validated['target_user_id'] ?? null,
            'target_shift_id' => $validated['target_shift_id'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'status' => SwapRequestStatus::Pending,
        ]);

        return redirect()->route('my-swaps.index')
            ->with('success', 'Swap request submitted successfully.');
    }
}
