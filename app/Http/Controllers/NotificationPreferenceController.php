<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    private const NOTIFICATION_TYPES = [
        'shift_published' => [
            'label' => 'New Shift Scheduled',
            'description' => 'When you are assigned to a new shift',
        ],
        'shift_changed' => [
            'label' => 'Shift Changes',
            'description' => 'When your shift is updated, cancelled, or reassigned',
        ],
        'shift_reminder' => [
            'label' => 'Shift Reminders',
            'description' => 'Reminders before your shifts start',
        ],
        'leave_request_status' => [
            'label' => 'Leave Request Status',
            'description' => 'When your leave request is approved or rejected',
        ],
        'swap_request' => [
            'label' => 'Swap Requests',
            'description' => 'When someone requests to swap shifts with you',
        ],
        'swap_request_response' => [
            'label' => 'Swap Request Responses',
            'description' => 'When your swap request is accepted, rejected, or approved',
        ],
        'missed_shift' => [
            'label' => 'Missed Shift Alerts',
            'description' => 'When you miss a scheduled shift',
        ],
    ];

    public function index(): View
    {
        $user = auth()->user();

        $preferences = $user->notificationPreferences()
            ->get()
            ->keyBy('notification_type');

        $notificationTypes = [];
        foreach (self::NOTIFICATION_TYPES as $type => $config) {
            $preference = $preferences->get($type);

            $notificationTypes[$type] = [
                'label' => $config['label'],
                'description' => $config['description'],
                'email_enabled' => $preference?->email_enabled ?? true,
                'in_app_enabled' => $preference?->in_app_enabled ?? true,
            ];
        }

        return view('notifications.preferences', compact('notificationTypes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.email_enabled' => ['sometimes', 'boolean'],
            'preferences.*.in_app_enabled' => ['sometimes', 'boolean'],
        ]);

        foreach ($validated['preferences'] as $type => $settings) {
            if (! array_key_exists($type, self::NOTIFICATION_TYPES)) {
                continue;
            }

            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_type' => $type,
                ],
                [
                    'email_enabled' => $settings['email_enabled'] ?? false,
                    'in_app_enabled' => $settings['in_app_enabled'] ?? false,
                    'push_enabled' => false, // Not implemented yet
                ]
            );
        }

        return redirect()
            ->route('notifications.preferences')
            ->with('success', 'Notification preferences updated.');
    }
}
