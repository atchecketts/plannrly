<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function unread(): JsonResponse
    {
        $user = auth()->user();

        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'notification',
                'message' => $notification->data['message'] ?? 'You have a new notification.',
                'created_at' => $notification->created_at->diffForHumans(),
                'data' => $notification->data,
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $user = auth()->user();

        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        $user->unreadNotifications->markAsRead();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'All notifications marked as read.');
    }

    public function destroy(string $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->delete();
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->back()
            ->with('success', 'Notification deleted.');
    }

    public function clearAll(): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        $user->notifications()->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->back()
            ->with('success', 'All notifications cleared.');
    }
}
