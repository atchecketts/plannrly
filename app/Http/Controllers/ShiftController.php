<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Rota;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function store(StoreShiftRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by'] = auth()->id();

        $shift = Shift::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'shift' => $shift->load(['user', 'department', 'businessRole']),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Shift created successfully.');
    }

    public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse|JsonResponse
    {
        $shift->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'shift' => $shift->fresh(['user', 'department', 'businessRole']),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $shift);

        $shift->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->back()
            ->with('success', 'Shift deleted successfully.');
    }

    public function assign(Request $request, Shift $shift): RedirectResponse|JsonResponse
    {
        $this->authorize('assign', $shift);

        $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $shift->update(['user_id' => $request->input('user_id')]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'shift' => $shift->fresh(['user', 'department', 'businessRole']),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Shift assigned successfully.');
    }

    public function availableUsers(Shift $shift): JsonResponse
    {
        $users = User::whereHas('businessRoles', function ($query) use ($shift) {
            $query->where('business_role_id', $shift->business_role_id);
        })
            ->where('tenant_id', $shift->tenant_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($users);
    }

    public function forRota(Rota $rota, Request $request): JsonResponse
    {
        $shifts = Shift::with(['user', 'department', 'businessRole'])
            ->where('rota_id', $rota->id)
            ->when($request->input('location_id'), fn ($q, $v) => $q->where('location_id', $v))
            ->when($request->input('department_id'), fn ($q, $v) => $q->where('department_id', $v))
            ->when($request->input('business_role_id'), fn ($q, $v) => $q->where('business_role_id', $v))
            ->when($request->input('user_id'), fn ($q, $v) => $q->where('user_id', $v))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json($shifts);
    }
}
