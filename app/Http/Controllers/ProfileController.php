<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $user->load(['roleAssignments.location', 'roleAssignments.department', 'businessRoles.department', 'employmentDetails', 'availability']);

        return view('profile.index', compact('user'));
    }

    public function edit(): View
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        return redirect()
            ->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    public function editPassword(): View
    {
        return view('profile.change-password');
    }

    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $user->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('profile.index')
            ->with('success', 'Password changed successfully.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = auth()->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar_path' => $path]);

        return redirect()
            ->route('profile.index')
            ->with('success', 'Avatar updated successfully.');
    }

    public function deleteAvatar(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return redirect()
            ->route('profile.index')
            ->with('success', 'Avatar removed successfully.');
    }
}
