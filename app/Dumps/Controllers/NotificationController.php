<!-- <?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\DB;

// class NotificationController extends Controller
// {
    /**
     * Display all notifications for the authenticated user
     */
    // public function index()
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return redirect()->route('login');
    //     }

    //     // ✅ DEBUG: Log access to notifications page
    //     Log::debug('📄 Notifications index page accessed', [
    //         'user_id' => $user->id,
    //         'total_notifications' => $user->notifications()->count(),
    //         'unread_notifications' => $user->unreadNotifications()->count()
    //     ]);

    //     $notifications = $user->notifications()->latest()->paginate(20);

    //     return view('notifications.index', compact('notifications'));
    // }

    // /**
    //  * Mark all notifications as read
    //  */
    // public function markAsRead()
    // {
    //     // ✅ ADD DEBUG: Track who's calling this method
    //     $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
    //     $callers = array_slice($backtrace, 1, 4);

    //     Log::debug('🎯 markAsRead (ALL) CALLED - INVESTIGATING SOURCE', [
    //         'user_id' => Auth::id(),
    //         'callers' => array_map(function($caller) {
    //             return [
    //                 'file' => basename($caller['file'] ?? 'unknown'),
    //                 'line' => $caller['line'] ?? 'unknown',
    //                 'function' => $caller['function'] ?? 'unknown',
    //                 'class' => $caller['class'] ?? 'unknown'
    //             ];
    //         }, $callers),
    //         'request_url' => request()->fullUrl(),
    //         'request_method' => request()->method(),
    //         'ajax_request' => request()->ajax(),
    //         'referrer' => request()->header('referer')
    //     ]);

    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             if (request()->ajax() || request()->expectsJson()) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'User not authenticated.',
    //                 ], 401);
    //             }
    //             return redirect()->route('login');
    //         }

    //         $unreadCount = $user->unreadNotifications->count();

    //         Log::info('Marking all notifications as read', [
    //             'user_id' => $user->id,
    //             'unread_count' => $unreadCount,
    //             'affected_notifications' => $unreadCount
    //         ]);

    //         $user->unreadNotifications->markAsRead();

    //         if (request()->ajax() || request()->expectsJson()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'All notifications marked as read.',
    //                 'unread_count' => 0
    //             ]);
    //         }

    //         return back()->with('success', 'All notifications marked as read.');

    //     } catch (\Exception $e) {
    //         Log::error('Failed to mark all notifications as read: ' . $e->getMessage());

    //         if (request()->ajax() || request()->expectsJson()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to mark notifications as read.',
    //             ], 500);
    //         }

    //         return back()->with('error', 'Failed to mark notifications as read.');
    //     }
    // }

    // /**
    //  * Mark a specific notification as read
    //  */
    // public function markAsReadSingle($id)
    // {
    //     // ✅ ADD DEBUG: Track who's calling this method
    //     $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
    //     $callers = array_slice($backtrace, 1, 4);

    //     Log::debug('🎯 markAsReadSingle CALLED - INVESTIGATING SOURCE', [
    //         'notification_id' => $id,
    //         'user_id' => Auth::id(),
    //         'callers' => array_map(function($caller) {
    //             return [
    //                 'file' => basename($caller['file'] ?? 'unknown'),
    //                 'line' => $caller['line'] ?? 'unknown',
    //                 'function' => $caller['function'] ?? 'unknown',
    //                 'class' => $caller['class'] ?? 'unknown'
    //             ];
    //         }, $callers),
    //         'request_url' => request()->fullUrl(),
    //         'request_method' => request()->method(),
    //         'ajax_request' => request()->ajax(),
    //         'referrer' => request()->header('referer')
    //     ]);

    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             if (request()->ajax() || request()->expectsJson()) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'User not authenticated.',
    //                 ], 401);
    //             }
    //             return redirect()->route('login');
    //         }

    //         $notification = $user->notifications()->where('id', $id)->first();

    //         if ($notification) {
    //             Log::info('Marking single notification as read', [
    //                 'notification_id' => $id,
    //                 'user_id' => $user->id,
    //                 'notification_type' => $notification->type,
    //                 'was_read' => !is_null($notification->read_at)
    //             ]);

    //             $notification->markAsRead();

    //             if (request()->ajax() || request()->expectsJson()) {
    //                 return response()->json([
    //                     'success' => true,
    //                     'message' => 'Notification marked as read.',
    //                     'unread_count' => $user->unreadNotifications->count()
    //                 ]);
    //             }

    //             return back()->with('success', 'Notification marked as read.');
    //         }

    //         Log::warning('Notification not found for mark as read', [
    //             'notification_id' => $id,
    //             'user_id' => $user->id
    //         ]);

    //         if (request()->ajax() || request()->expectsJson()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Notification not found.',
    //             ], 404);
    //         }

    //         return back()->with('error', 'Notification not found.');

    //     } catch (\Exception $e) {
    //         Log::error('Failed to mark single notification as read: ' . $e->getMessage());

    //         if (request()->ajax() || request()->expectsJson()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to mark notification as read.',
    //             ], 500);
    //         }

    //         return back()->with('error', 'Failed to mark notification as read.');
    //     }
    // }

    // /**
    //  * Get unread notifications for real-time updates
    //  */
    // public function unread()
    // {
    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'User not authenticated.',
    //                 'count' => 0,
    //                 'notifications' => []
    //             ], 401);
    //         }

    //         // Get unread notifications
    //         $unreadNotifications = $user->unreadNotifications()
    //             ->latest()
    //             ->take(10)
    //             ->get();

    //         // ✅ DEBUG: Log unread notifications query
    //         Log::debug('🔔 Unread notifications fetched', [
    //             'user_id' => $user->id,
    //             'unread_count' => $unreadNotifications->count(),
    //             'total_unread_count' => $user->unreadNotifications()->count(),
    //             'notification_ids' => $unreadNotifications->pluck('id')->toArray(),
    //             'request_source' => request()->header('referer')
    //         ]);

    //         $notificationsData = $unreadNotifications->map(function ($notification) {
    //             return [
    //                 'id' => $notification->id,
    //                 'type' => $notification->type,
    //                 'data' => $notification->data,
    //                 'message' => $this->formatNotificationMessage($notification->data),
    //                 'created_at' => $notification->created_at->diffForHumans(),
    //                 'read_at' => $notification->read_at,
    //                 'ticket_id' => $this->extractTicketId($notification->data),
    //                 'ticket_title' => $this->extractTicketTitle($notification->data),
    //             ];
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'count' => $user->unreadNotifications()->count(),
    //             'notifications' => $notificationsData
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Failed to fetch unread notifications: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to load notifications.',
    //             'count' => 0,
    //             'notifications' => []
    //         ], 500);
    //     }
    // }

    // /**
    //  * Format notification message for display with standardized handling
    //  */
    // private function formatNotificationMessage($data, $type = null)
    // {
    //     if (!is_array($data)) {
    //         return 'Ticket updated';
    //     }

    //     // ✅ STANDARDIZED: Always use message if available
    //     if (isset($data['message']) && !empty($data['message'])) {
    //         return $data['message'];
    //     }

    //     // ✅ STANDARDIZED: Extract ticket information consistently
    //     $ticketNumber = $data['ticket_number'] ?? 'Unknown Ticket';
    //     $actionType = $data['action_type'] ?? $this->determineActionType($data);

    //     // ✅ STANDARDIZED: Generate consistent messages based on action type
    //     switch ($actionType) {
    //         case 'assigned':
    //             return "Ticket {$ticketNumber} has been assigned to you";
    //         case 'status_changed':
    //             $newStatus = $data['changes']['status']['to'] ?? 'updated';
    //             return "Ticket {$ticketNumber} status changed to " . ucfirst(str_replace('_', ' ', $newStatus));
    //         case 'updated':
    //         default:
    //             return "Ticket {$ticketNumber} has been updated";
    //     }
    // }

    // /**
    //  * Determine action type from notification data
    //  */
    // private function determineActionType($data)
    // {
    //     if (!isset($data['changes']) || !is_array($data['changes'])) {
    //         return 'updated';
    //     }

    //     $changeTypes = array_keys($data['changes']);

    //     if (in_array('it_personnel_id', $changeTypes)) {
    //         return 'assigned';
    //     } elseif (in_array('status', $changeTypes)) {
    //         return 'status_changed';
    //     } else {
    //         return 'updated';
    //     }
    // }

    // /**
    //  * Extract ticket ID from notification data consistently
    //  */
    // private function extractTicketId($data)
    // {
    //     return $data['ticket_id'] ?? null;
    // }

    // /**
    //  * Extract ticket title from notification data consistently
    //  */
    // private function extractTicketTitle($data)
    // {
    //     return $data['ticket_title'] ?? $data['ticket_number'] ?? 'Unknown Ticket';
    // }

    // /**
    //  * Clear all notifications
    //  */
    // public function clearAll()
    // {
    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             return redirect()->route('login');
    //         }

    //         $notificationCount = $user->notifications()->count();

    //         Log::info('Clearing all notifications', [
    //             'user_id' => $user->id,
    //             'total_notifications' => $notificationCount
    //         ]);

    //         $user->notifications()->delete();

    //         return back()->with('success', 'All notifications cleared.');

    //     } catch (\Exception $e) {
    //         Log::error('Failed to clear all notifications: ' . $e->getMessage());
    //         return back()->with('error', 'Failed to clear notifications.');
    //     }
    // }

    // /**
    //  * Delete a specific notification
    //  */
    // public function destroy($id)
    // {
    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             if (request()->ajax() || request()->expectsJson()) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'User not authenticated.',
    //                 ], 401);
    //             }
    //             return redirect()->route('login');
    //         }

    //         $notification = $user->notifications()->where('id', $id)->first();

    //         if ($notification) {
    //             Log::info('Deleting single notification', [
    //                 'notification_id' => $id,
    //                 'user_id' => $user->id,
    //                 'notification_type' => $notification->type
    //             ]);

    //             $notification->delete();

    //             if (request()->ajax() || request()->expectsJson()) {
    //                 return response()->json([
    //                     'success' => true,
    //                     'message' => 'Notification deleted.',
    //                     'unread_count' => $user->unreadNotifications->count()
    //                 ]);
    //             }

    //             return back()->with('success', 'Notification deleted.');
    //         }

    //         Log::warning('Notification not found for deletion', [
    //             'notification_id' => $id,
    //             'user_id' => $user->id
    //         ]);

    //         if (request()->ajax() || request()->expectsJson()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Notification not found.',
    //             ], 404);
    //         }

    //         return back()->with('error', 'Notification not found.');

    //     } catch (\Exception $e) {
    //         Log::error('Failed to delete notification: ' . $e->getMessage());

    //         if (request()->ajax() || request()->expectsJson()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to delete notification.',
    //             ], 500);
    //         }

    //         return back()->with('error', 'Failed to delete notification.');
    //     }
    // }

    // /**
    //  * Get notification statistics (optional)
    //  */
    // public function stats()
    // {
    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'User not authenticated.'
    //             ], 401);
    //         }

    //         $total = $user->notifications()->count();
    //         $unread = $user->unreadNotifications()->count();
    //         $read = $total - $unread;

    //         return response()->json([
    //             'success' => true,
    //             'stats' => [
    //                 'total' => $total,
    //                 'unread' => $unread,
    //                 'read' => $read
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Failed to fetch notification stats: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch notification statistics.'
    //         ], 500);
    //     }
    // }
//}
