<?php

namespace App\Http\Controllers;

use App\Enums\AvailabilityType;
use App\Enums\PreferenceLevel;
use App\Http\Requests\Availability\StoreAvailabilityRequest;
use App\Http\Requests\Availability\UpdateAvailabilityRequest;
use App\Models\User;
use App\Models\UserAvailability;
use App\Services\AvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $availabilityRules = UserAvailability::where('user_id', $user->id)
            ->orderBy('type')
            ->orderBy('day_of_week')
            ->orderBy('specific_date')
            ->orderBy('start_time')
            ->get();

        $weeklySummary = $this->availabilityService->getWeeklySummary($user);

        return view('availability.index', compact('availabilityRules', 'weeklySummary'));
    }

    public function edit(): View
    {
        $user = auth()->user();
        $availabilityRules = UserAvailability::where('user_id', $user->id)
            ->orderBy('type')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $availabilityTypes = AvailabilityType::cases();
        $preferenceLevels = PreferenceLevel::cases();
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('availability.edit', compact('availabilityRules', 'availabilityTypes', 'preferenceLevels', 'days'));
    }

    public function store(StoreAvailabilityRequest $request): RedirectResponse
    {
        $data = $request->validated();

        UserAvailability::create([
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'day_of_week' => $data['day_of_week'] ?? null,
            'specific_date' => $data['specific_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'is_available' => $data['is_available'] ?? true,
            'preference_level' => $data['preference_level'],
            'notes' => $data['notes'] ?? null,
            'effective_from' => $data['effective_from'] ?? null,
            'effective_until' => $data['effective_until'] ?? null,
        ]);

        return redirect()
            ->route('availability.index')
            ->with('success', 'Availability rule added successfully.');
    }

    public function update(UpdateAvailabilityRequest $request, UserAvailability $availability): RedirectResponse
    {
        $data = $request->validated();

        $availability->update([
            'type' => $data['type'],
            'day_of_week' => $data['day_of_week'] ?? null,
            'specific_date' => $data['specific_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'is_available' => $data['is_available'] ?? true,
            'preference_level' => $data['preference_level'],
            'notes' => $data['notes'] ?? null,
            'effective_from' => $data['effective_from'] ?? null,
            'effective_until' => $data['effective_until'] ?? null,
        ]);

        return redirect()
            ->route('availability.index')
            ->with('success', 'Availability rule updated successfully.');
    }

    public function destroy(UserAvailability $availability): RedirectResponse
    {
        if ($availability->user_id !== auth()->id() && ! auth()->user()->canManageUser($availability->user)) {
            abort(403);
        }

        $availability->delete();

        return redirect()
            ->route('availability.index')
            ->with('success', 'Availability rule deleted successfully.');
    }

    /**
     * Admin view of a specific user's availability.
     */
    public function show(User $user): View
    {
        if (! auth()->user()->canManageUser($user)) {
            abort(403);
        }

        $availabilityRules = UserAvailability::where('user_id', $user->id)
            ->orderBy('type')
            ->orderBy('day_of_week')
            ->orderBy('specific_date')
            ->orderBy('start_time')
            ->get();

        $weeklySummary = $this->availabilityService->getWeeklySummary($user);

        return view('availability.show', compact('user', 'availabilityRules', 'weeklySummary'));
    }
}
