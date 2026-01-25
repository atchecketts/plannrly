<?php

namespace App\Http\Controllers;

use App\Enums\TimeEntryStatus;
use App\Models\Shift;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeClockController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Get today's shift for the user
        $todayShift = Shift::with(['location', 'department', 'businessRole', 'timeEntry'])
            ->visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        // Get active time entry (if any)
        $activeTimeEntry = TimeEntry::where('user_id', $user->id)
            ->active()
            ->first();

        // Calculate today's stats
        $todayWorkedMinutes = TimeEntry::where('user_id', $user->id)
            ->whereDate('clock_in_at', today())
            ->get()
            ->sum('total_worked_minutes');

        return view('time-clock.index', [
            'todayShift' => $todayShift,
            'activeTimeEntry' => $activeTimeEntry,
            'todayWorkedMinutes' => $todayWorkedMinutes,
        ]);
    }

    public function clockIn(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // Check if already clocked in
        $existingEntry = TimeEntry::where('user_id', $user->id)
            ->active()
            ->first();

        if ($existingEntry) {
            return $this->respondWithError('You are already clocked in.', $request);
        }

        // Find today's shift
        $shift = Shift::visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        if (! $shift) {
            return $this->respondWithError('No shift found for today.', $request);
        }

        // Create time entry
        $timeEntry = TimeEntry::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now(),
            'clock_in_location' => $request->input('location'),
            'status' => TimeEntryStatus::ClockedIn,
        ]);

        return $this->respondWithSuccess('Clocked in successfully.', $request, [
            'time_entry' => $timeEntry,
        ]);
    }

    public function clockOut(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $timeEntry = TimeEntry::where('user_id', $user->id)
            ->active()
            ->first();

        if (! $timeEntry) {
            return $this->respondWithError('You are not clocked in.', $request);
        }

        $timeEntry->clockOut($request->input('location'));

        return $this->respondWithSuccess('Clocked out successfully.', $request, [
            'time_entry' => $timeEntry->fresh(),
        ]);
    }

    public function startBreak(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $timeEntry = TimeEntry::where('user_id', $user->id)
            ->where('status', TimeEntryStatus::ClockedIn)
            ->first();

        if (! $timeEntry) {
            return $this->respondWithError('You must be clocked in to start a break.', $request);
        }

        $timeEntry->startBreak();

        return $this->respondWithSuccess('Break started.', $request, [
            'time_entry' => $timeEntry->fresh(),
        ]);
    }

    public function endBreak(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $timeEntry = TimeEntry::where('user_id', $user->id)
            ->where('status', TimeEntryStatus::OnBreak)
            ->first();

        if (! $timeEntry) {
            return $this->respondWithError('You are not on break.', $request);
        }

        $timeEntry->endBreak();

        return $this->respondWithSuccess('Break ended.', $request, [
            'time_entry' => $timeEntry->fresh(),
        ]);
    }

    protected function respondWithSuccess(string $message, Request $request, array $data = []): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                ...$data,
            ]);
        }

        return back()->with('success', $message);
    }

    protected function respondWithError(string $message, Request $request): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with('error', $message);
    }
}
