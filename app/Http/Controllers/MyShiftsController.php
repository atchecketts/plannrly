<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyShiftsController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $startDate = $request->query('start', now()->startOfWeek()->format('Y-m-d'));
        $endDate = now()->parse($startDate)->endOfWeek()->format('Y-m-d');

        $shifts = Shift::with(['location', 'department', 'businessRole'])
            ->visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($shift) => $shift->date->format('Y-m-d'));

        $weekStart = now()->parse($startDate);
        $weekEnd = now()->parse($endDate);

        // Calculate week stats
        $totalHours = Shift::visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->sum('working_hours');

        $shiftCount = Shift::visibleToUser($user)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        return view('my-shifts.index', [
            'shifts' => $shifts,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'totalHours' => round($totalHours, 1),
            'shiftCount' => $shiftCount,
        ]);
    }
}
