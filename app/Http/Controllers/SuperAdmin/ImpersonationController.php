<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ImpersonationController extends Controller
{
    public function start(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Only super administrators can impersonate users.');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot impersonate another super administrator.');
        }

        session()->put('impersonator_id', $currentUser->id);
        session()->put('impersonator_name', $currentUser->full_name);

        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', "Now impersonating {$user->full_name}. Click 'Stop Impersonating' in the header to return.");
    }

    public function stop(): RedirectResponse
    {
        $impersonatorId = session()->get('impersonator_id');

        if (! $impersonatorId) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not impersonating anyone.');
        }

        $impersonator = User::find($impersonatorId);

        if (! $impersonator) {
            session()->forget(['impersonator_id', 'impersonator_name']);

            return redirect()->route('login')
                ->with('error', 'Original user not found. Please log in again.');
        }

        session()->forget(['impersonator_id', 'impersonator_name']);

        auth()->login($impersonator);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'Stopped impersonating. Welcome back!');
    }
}
