<?php

namespace App\Http\Controllers;

use App\Enums\ShiftStatus;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\ShiftPublishedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function show(Shift $shift): JsonResponse
    {
        $this->authorize('view', $shift);

        return response()->json([
            'success' => true,
            'shift' => $shift->load(['user', 'department', 'businessRole', 'location']),
        ]);
    }

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

    public function publish(Shift $shift): RedirectResponse|JsonResponse
    {
        $this->authorize('publish', $shift);

        $shift->update(['status' => ShiftStatus::Published]);

        // Send notification if shift is assigned and notifications are enabled
        $tenant = auth()->user()->tenant;
        $notifyOnPublish = $tenant->tenantSettings?->notify_on_publish ?? true;

        if ($notifyOnPublish && $shift->user) {
            $shift->load('location');
            $shift->user->notify(new ShiftPublishedNotification($shift));
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'shift' => $shift->fresh(['user', 'department', 'businessRole']),
                'message' => 'Shift published successfully.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Shift published successfully.');
    }
}
