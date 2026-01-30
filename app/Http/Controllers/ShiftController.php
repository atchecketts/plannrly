<?php

namespace App\Http\Controllers;

use App\Enums\ShiftStatus;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\ShiftChangedNotification;
use App\Notifications\ShiftPublishedNotification;
use App\Services\RecurringShiftService;
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

    public function store(StoreShiftRequest $request, RecurringShiftService $recurringService): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by'] = auth()->id();

        $shift = Shift::create($data);

        // Generate recurring instances if this is a recurring shift
        $childShifts = collect();
        if ($shift->is_recurring && ! empty($shift->recurrence_rule)) {
            $childShifts = $recurringService->generateInstances($shift);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'shift' => $shift->load(['user', 'department', 'businessRole']),
                'child_shifts' => $childShifts->map(fn ($s) => $s->load(['user', 'department', 'businessRole'])),
                'child_count' => $childShifts->count(),
            ]);
        }

        $message = 'Shift created successfully.';
        if ($childShifts->count() > 0) {
            $message .= " {$childShifts->count()} recurring shifts also created.";
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    public function update(UpdateShiftRequest $request, Shift $shift, RecurringShiftService $recurringService): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $originalUser = $shift->user;
        $wasPublished = $shift->status === ShiftStatus::Published;
        $editScope = $data['edit_scope'] ?? 'single';
        unset($data['edit_scope']);

        // Handle recurring child shifts
        if ($shift->isRecurringChild() && $editScope === 'single') {
            // Detach this instance from the series for individual editing
            $recurringService->detachFromParent($shift);
        }

        // If shift is published and movement fields are changing, revert to draft
        if ($shift->status === ShiftStatus::Published) {
            $movementFields = ['date', 'start_time', 'end_time', 'user_id'];
            $isMoving = false;

            foreach ($movementFields as $field) {
                if (array_key_exists($field, $data)) {
                    $currentValue = $shift->{$field};
                    $newValue = $data[$field];

                    // Normalize for comparison (handle Carbon objects)
                    if ($currentValue instanceof \Carbon\Carbon) {
                        $currentValue = $currentValue->format($field === 'date' ? 'Y-m-d' : 'H:i');
                    }

                    if ($currentValue != $newValue) {
                        $isMoving = true;
                        break;
                    }
                }
            }

            if ($isMoving) {
                $data['status'] = ShiftStatus::Draft;
            }
        }

        // For recurring parents with "future" scope, also update children
        $updatedChildCount = 0;
        if ($editScope === 'future' && ($shift->isRecurringParent() || $shift->isRecurringChild())) {
            $parentShift = $shift->isRecurringParent() ? $shift : $shift->parentShift;

            if ($parentShift) {
                // Only update fields that should propagate to children
                $childUpdateData = array_intersect_key($data, array_flip([
                    'start_time',
                    'end_time',
                    'break_duration_minutes',
                    'notes',
                    'business_role_id',
                    'user_id',
                ]));

                if (! empty($childUpdateData)) {
                    $updatedChildCount = $recurringService->updateFutureInstances($parentShift, $childUpdateData);
                }
            }
        }

        $shift->update($data);

        // Notify user if a published shift was modified
        if ($wasPublished && $originalUser) {
            $shift->load('location');
            $originalUser->notify(new ShiftChangedNotification($shift, 'updated'));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'shift' => $shift->fresh(['user', 'department', 'businessRole']),
                'updated_children' => $updatedChildCount,
            ]);
        }

        $message = 'Shift updated successfully.';
        if ($updatedChildCount > 0) {
            $message .= " {$updatedChildCount} future shifts also updated.";
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    public function destroy(Request $request, Shift $shift, RecurringShiftService $recurringService): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $shift);

        $deleteScope = $request->input('delete_scope', 'single');

        // Notify assigned user if shift was published
        $assignedUser = $shift->user;
        $wasPublished = $shift->status === ShiftStatus::Published;

        // Keep shift data for notification before deleting
        if ($wasPublished && $assignedUser) {
            $shift->load('location');
            $assignedUser->notify(new ShiftChangedNotification($shift, 'deleted'));
        }

        $deletedChildCount = 0;

        // Handle delete scope for recurring shifts
        if ($deleteScope === 'future') {
            if ($shift->isRecurringParent()) {
                // Delete parent and all future children
                $deletedChildCount = $recurringService->deleteFutureInstances($shift);
                $shift->delete();
            } elseif ($shift->isRecurringChild()) {
                // Delete this shift and all future siblings
                $parentShift = $shift->parentShift;
                if ($parentShift) {
                    // Delete future children (including this one)
                    $deletedChildCount = $parentShift->childShifts()
                        ->where('date', '>=', $shift->date)
                        ->delete();
                }
            } else {
                $shift->delete();
            }
        } else {
            // Single delete - if child, detach first
            if ($shift->isRecurringChild()) {
                $recurringService->detachFromParent($shift);
            }
            $shift->delete();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'deleted_children' => $deletedChildCount,
            ]);
        }

        $message = 'Shift deleted successfully.';
        if ($deletedChildCount > 0) {
            $message .= " {$deletedChildCount} future shifts also deleted.";
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    public function assign(Request $request, Shift $shift): RedirectResponse|JsonResponse
    {
        $this->authorize('assign', $shift);

        $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $data = ['user_id' => $request->input('user_id')];

        // If shift is published and user is changing, revert to draft
        if ($shift->status === ShiftStatus::Published && $shift->user_id != $data['user_id']) {
            $data['status'] = ShiftStatus::Draft;
        }

        $shift->update($data);

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
