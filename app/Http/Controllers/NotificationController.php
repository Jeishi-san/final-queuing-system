<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the logged-in user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Fetch all notifications (latest first, limited to 50 for performance)
        $notifications = $user->notifications()->latest()->take(50)->get();

        // Count unread notifications
        $unreadCount = $user->unreadNotifications()->count();

        // ✅ AJAX support (for navigation or dashboard polling)
        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'title' => $n->data['title'] ?? 'Notification',
                        'message' => $n->data['message'] ?? '',
                        'ticket_number' => $n->data['ticket_number'] ?? null,
                        'ticket_id' => $n->data['ticket_id'] ?? null,
                        'created_at' => $n->created_at,
                        'read_at' => $n->read_at,
                    ];
                }),
                'unread_count' => $unreadCount,
            ]);
        }

        // ✅ Blade view
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $notificationId): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Clear all notifications for the logged-in user.
     */
    public function clear(): JsonResponse
    {
        $user = Auth::user();

        $user->notifications()->delete();

        return response()->json(['success' => true]);
    }
}
