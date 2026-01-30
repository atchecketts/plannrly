<?php

namespace App\Http\Controllers;

use App\Enums\ShiftStatus;
use App\Http\Requests\Shift\PasteShiftsRequest;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;

class ShiftCopyController extends Controller
{
    /**
     * Paste copied shifts to a new target date/user.
     */
    public function paste(PasteShiftsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $shifts = $data['shifts'];
        $targetDate = $data['target_date'];
        $targetUserId = $data['target_user_id'] ?? null;
        $tenantId = auth()->user()->tenant_id;
        $createdBy = auth()->id();

        $createdShifts = [];
        $errors = [];

        foreach ($shifts as $index => $shiftData) {
            try {
                $shift = Shift::create([
                    'tenant_id' => $tenantId,
                    'location_id' => $shiftData['location_id'],
                    'department_id' => $shiftData['department_id'],
                    'business_role_id' => $shiftData['business_role_id'],
                    'user_id' => $targetUserId,
                    'date' => $targetDate,
                    'start_time' => $shiftData['start_time'],
                    'end_time' => $shiftData['end_time'],
                    'break_duration_minutes' => $shiftData['break_duration_minutes'] ?? 0,
                    'notes' => $shiftData['notes'] ?? null,
                    'status' => ShiftStatus::Draft,
                    'created_by' => $createdBy,
                ]);

                $createdShifts[] = $shift->load(['user', 'department', 'businessRole', 'location']);
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'message' => 'Failed to create shift: '.$e->getMessage(),
                ];
            }
        }

        if (count($errors) > 0 && count($createdShifts) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to paste shifts.',
                'errors' => $errors,
            ], 422);
        }

        $message = count($createdShifts) === 1
            ? '1 shift pasted successfully.'
            : count($createdShifts).' shifts pasted successfully.';

        if (count($errors) > 0) {
            $message .= ' '.count($errors).' shift(s) failed.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'shifts' => $createdShifts,
            'errors' => $errors,
            'created_count' => count($createdShifts),
        ]);
    }
}
