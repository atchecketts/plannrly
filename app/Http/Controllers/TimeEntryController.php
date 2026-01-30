<?php

namespace App\Http\Controllers;

use App\Enums\TimeEntryStatus;
use App\Http\Requests\TimeEntry\AdjustTimeEntryRequest;
use App\Http\Requests\TimeEntry\ClockInRequest;
use App\Http\Requests\TimeEntry\ClockOutRequest;
use App\Http\Requests\TimeEntry\StartBreakRequest;
use App\Models\Shift;
use App\Models\TenantSettings;
use App\Models\TimeEntry;
use App\Traits\HandlesSorting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    use HandlesSorting;

    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = TimeEntry::query()
            ->with(['user', 'shift.location', 'shift.department', 'shift.businessRole', 'approvedBy']);

        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('user_id') && ! $user->isEmployee()) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('clock_in_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('clock_in_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('pending_approval')) {
            $query->pendingApproval();
        }

        $sortableColumns = ['clock_in_at', 'clock_out_at', 'status', 'created_at'];
        $query = $this->applySorting($query, $request, $sortableColumns, 'clock_in_at', 'desc');

        $timeEntries = $query->paginate(15)->withQueryString();

        $settings = TenantSettings::where('tenant_id', $user->tenant_id)->first();

        return view('time-entries.index', compact('timeEntries', 'settings'));
    }

    public function show(TimeEntry $timeEntry): View
    {
        $this->authorize('view', $timeEntry);

        $timeEntry->load(['user', 'shift.location', 'shift.department', 'shift.businessRole', 'approvedBy']);

        return view('time-entries.show', compact('timeEntry'));
    }

    public function currentStatus(): JsonResponse
    {
        $user = auth()->user();

        $activeEntry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->active()
            ->with('shift')
            ->first();

        $todayShift = Shift::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereDoesntHave('timeEntry')
            ->first();

        return response()->json([
            'active_entry' => $activeEntry,
            'today_shift' => $todayShift,
            'is_clocked_in' => $activeEntry !== null,
            'is_on_break' => $activeEntry?->isOnBreak() ?? false,
        ]);
    }

    public function clockIn(ClockInRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $settings = TenantSettings::where('tenant_id', $user->tenant_id)->first();

        if (! $settings?->enable_clock_in_out) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Clock in/out is not enabled for your organization.'], 403);
            }

            return back()->with('error', 'Clock in/out is not enabled for your organization.');
        }

        $existingEntry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->active()
            ->exists();

        if ($existingEntry) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You are already clocked in.'], 422);
            }

            return back()->with('error', 'You are already clocked in.');
        }

        $shift = Shift::findOrFail($request->input('shift_id'));

        $location = $request->input('location');

        $timeEntry = TimeEntry::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'clock_in_at' => now(),
            'clock_in_location' => $location,
            'status' => TimeEntryStatus::ClockedIn,
        ]);

        $message = 'Successfully clocked in at '.now()->format('g:i A').'.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'time_entry' => [
                    'id' => $timeEntry->id,
                    'clock_in_at' => $timeEntry->clock_in_at->toIso8601String(),
                    'clock_in_timestamp' => $timeEntry->clock_in_at->timestamp,
                    'status' => $timeEntry->status->value,
                ],
            ]);
        }

        return redirect()
            ->route('time-entries.index')
            ->with('success', $message);
    }

    public function clockOut(ClockOutRequest $request, TimeEntry $timeEntry): RedirectResponse|JsonResponse
    {
        $this->authorize('clockOut', $timeEntry);

        if (! $timeEntry->isClockedIn() && ! $timeEntry->isOnBreak()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You are not currently clocked in.'], 422);
            }

            return back()->with('error', 'You are not currently clocked in.');
        }

        if ($timeEntry->isOnBreak()) {
            $timeEntry->endBreak();
        }

        $location = $request->input('location');
        $timeEntry->clockOut($location);

        $message = 'Successfully clocked out at '.now()->format('g:i A').'. Total worked: '.$timeEntry->total_worked_hours.' hours.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'time_entry' => [
                    'id' => $timeEntry->id,
                    'clock_out_at' => $timeEntry->clock_out_at->toIso8601String(),
                    'total_worked_hours' => $timeEntry->total_worked_hours,
                    'status' => $timeEntry->status->value,
                ],
            ]);
        }

        return redirect()
            ->route('time-entries.index')
            ->with('success', $message);
    }

    public function startBreak(StartBreakRequest $request, TimeEntry $timeEntry): RedirectResponse|JsonResponse
    {
        $this->authorize('startBreak', $timeEntry);

        if (! $timeEntry->isClockedIn()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You must be clocked in to start a break.'], 422);
            }

            return back()->with('error', 'You must be clocked in to start a break.');
        }

        $timeEntry->startBreak();

        $message = 'Break started at '.now()->format('g:i A').'.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'time_entry' => [
                    'id' => $timeEntry->id,
                    'break_start_at' => $timeEntry->break_start_at->toIso8601String(),
                    'status' => $timeEntry->status->value,
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    public function endBreak(Request $request, TimeEntry $timeEntry): RedirectResponse|JsonResponse
    {
        $this->authorize('endBreak', $timeEntry);

        if (! $timeEntry->isOnBreak()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You are not currently on break.'], 422);
            }

            return back()->with('error', 'You are not currently on break.');
        }

        $timeEntry->endBreak();

        $message = 'Break ended at '.now()->format('g:i A').'.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'time_entry' => [
                    'id' => $timeEntry->id,
                    'break_end_at' => $timeEntry->break_end_at->toIso8601String(),
                    'actual_break_minutes' => $timeEntry->actual_break_minutes,
                    'status' => $timeEntry->status->value,
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    public function adjust(AdjustTimeEntryRequest $request, TimeEntry $timeEntry): RedirectResponse
    {
        $this->authorize('adjust', $timeEntry);

        $updates = ['adjustment_reason' => $request->input('adjustment_reason')];

        if ($request->filled('clock_in_at')) {
            $updates['clock_in_at'] = $request->input('clock_in_at');
        }

        if ($request->filled('clock_out_at')) {
            $updates['clock_out_at'] = $request->input('clock_out_at');
        }

        if ($request->filled('actual_break_minutes')) {
            $updates['actual_break_minutes'] = $request->input('actual_break_minutes');
        }

        $timeEntry->update($updates);

        return redirect()
            ->route('time-entries.show', $timeEntry)
            ->with('success', 'Time entry adjusted successfully.');
    }

    public function approve(TimeEntry $timeEntry): RedirectResponse
    {
        $this->authorize('approve', $timeEntry);

        if ($timeEntry->isApproved()) {
            return back()->with('error', 'This time entry has already been approved.');
        }

        $timeEntry->approve(auth()->user());

        return back()->with('success', 'Time entry approved successfully.');
    }
}
