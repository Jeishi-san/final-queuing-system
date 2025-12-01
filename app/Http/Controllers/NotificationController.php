<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications (Paginated).
     */
    public function index()
    {
        $user = Auth::user();
        
        // Fetch notifications
        $notifications = $user->notifications()->latest()->paginate(20);

        // Format the data for Vue
        $formattedNotifications = $notifications->getCollection()->transform(function ($notification) {
            return $this->formatNotification($notification);
        });

        // Replace collection with formatted data
        $notifications->setCollection($formattedNotifications);

        return response()->json($notifications);
    }

    /**
     * Get the count of unread notifications (For the Bell Icon).
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count()
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification read',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Mark ALL notifications as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All marked as read',
            'unread_count' => 0
        ]);
    }

    /**
     * Remove the specified notification.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
            return response()->json([
                'success' => true, 
                'message' => 'Notification deleted',
                'unread_count' => $user->unreadNotifications()->count()
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    /**
     * Helper: Format notification data for the frontend.
     */
    private function formatNotification($notification)
    {
        // Safely access data
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at->diffForHumans(),
            // Custom Logic: Extract message based on your data structure
            'message' => $data['message'] ?? 'New Notification',
            'ticket_id' => $data['ticket_id'] ?? null,
            'ticket_number' => $data['ticket_number'] ?? null,
            'changes' => $data['changes'] ?? [],
        ];
    }
}