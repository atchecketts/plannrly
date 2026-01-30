<?php

namespace App\Http\Controllers;

use App\Models\ScheduleHistory;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Only admins can view schedule history
        if ($user->isEmployee()) {
            abort(403, 'You do not have permission to view schedule history.');
        }

        $query = ScheduleHistory::with(['shift.user', 'shift.department', 'shift.businessRole', 'user'])
            ->orderBy('created_at', 'desc');

        // Date range filter
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->query('start_date'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->query('end_date'))->endOfDay()
            : now()->endOfDay();

        $query->whereBetween('created_at', [$startDate, $endDate]);

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        // User filter (who made the change)
        if ($request->filled('changed_by')) {
            $query->where('user_id', $request->query('changed_by'));
        }

        $history = $query->paginate(50)->withQueryString();

        // Get users for filter dropdown (users who have made changes)
        $changers = ScheduleHistory::select('user_id')
            ->distinct()
            ->with('user:id,first_name,last_name')
            ->get()
            ->pluck('user')
            ->filter();

        return view('schedule.history', compact(
            'history',
            'startDate',
            'endDate',
            'changers'
        ));
    }

    public function shift(Shift $shift): View
    {
        $user = auth()->user();

        // Only admins can view schedule history
        if ($user->isEmployee()) {
            abort(403, 'You do not have permission to view schedule history.');
        }

        $this->authorize('view', $shift);

        $history = ScheduleHistory::with(['user'])
            ->forShift($shift->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('schedule.shift-history', compact('shift', 'history'));
    }
}
