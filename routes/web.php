<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketLogController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QueueController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('vue.home');
});

Route::get('/queue', function () {
    return view('vue.queue');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('vue.admin.dashboard');
    })->name('dashboard');

    Route::get('/dashboard/my-profile', function () {
        return view('vue.admin.dashboard');
    })->name('profile');

    // ✅ ADDED: Missing edit-profile route
    Route::get('/dashboard/edit-profile', function () {
        return view('vue.admin.dashboard');
    })->name('edit-profile');

    Route::get('/dashboard/queue-list', function () {
        return view('vue.admin.dashboard');
    })->name('queue.list');

    Route::get('/dashboard/tickets', function () {
        return view('vue.admin.dashboard');
    })->name('tickets');
});

// -------------------- USER ROUTES -------------------- //
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'getUsers']);                  // List all users
    Route::post('/', [UserController::class, 'store']);                 // Create new user
    Route::get('{user}', [UserController::class, 'show']);              // Show user details
    Route::put('{user}', [UserController::class, 'update']);            // Update user
    Route::delete('{user}', [UserController::class, 'destroy']);        // Delete user

    // Helper routes
    Route::get('{user}/tickets-handled', [UserController::class, 'ticketsHandled']);
    Route::get('{user}/average-resolution', [UserController::class, 'averageResolutionTime']);
    Route::get('{user}/activity-log', [UserController::class, 'activityLog']);
});

// -------------------- QUEUE ROUTES -------------------- //
Route::prefix('queues')->group(function () {
    Route::get('/', [QueueController::class, 'getQueueList']);               // List top 5 queue items
    Route::get('/list', [QueueController::class, 'index']);               // List all queue items
    Route::get('/inProgress', [QueueController::class, 'getInProgressQueues']);               // List queue items with in progress tickets
    Route::get('/waiting', [QueueController::class, 'getWaitingItems']);               // Get count of waiting tickets

    Route::post('/', [QueueController::class, 'store']);              // Add ticket to queue
    Route::get('{queue}', [QueueController::class, 'show']);          // Show queue item
    Route::put('{queue}', [QueueController::class, 'update']);        // Update queue item
    Route::delete('{queue}', [QueueController::class, 'destroy']);    // Remove queue item

    // Helper route
    Route::get('next-ticket', [QueueController::class, 'nextTicket']); // Get next ticket
});

// -------------------- TICKET ROUTES -------------------- //
Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index']);               // List all tickets
    Route::get('/queued', [TicketController::class, 'countQueuedTickets']);               // Count Queued Tickets

    Route::post('/', [TicketController::class, 'store']);              // Create ticket
    Route::get('{ticket}', [TicketController::class, 'show']);         // Show ticket
    Route::put('{ticket}', [TicketController::class, 'updateStatus']);       // Update ticket
    Route::delete('{ticket}', [TicketController::class, 'destroy']);   // Delete ticket

    // Helper routes
    Route::post('{ticket}/add-log', [TicketController::class, 'addLog']); // Add log to ticket
    Route::get('/{ticket}/logs', [TicketLogController::class, 'logsForTicket']);
    Route::get('status/{status}', [TicketController::class, 'filterByStatus']); // Filter by status
});

// // Public API Routes (for Vue components)
// Route::prefix('api')->group(function () {
//     // Authentication routes for LoginView.vue and RegisterView.vue
//     // Route::post('/login', [LoginController::class, 'login']); // You'll need a LoginController
//     // Route::post('/register', [RegisterController::class, 'register']); // You'll need a RegisterController
//     // Route::post('/logout', [LoginController::class, 'logout']);

//     // Public ticket submission (for AddTicket.vue)
//     Route::post('/tickets', [TicketController::class, 'store']);

//     // Queue data for QueueView.vue and AdminQueueView.vue
//     Route::get('/queue/tickets', [TicketController::class, 'getQueueTickets']);
//     Route::get('/queue/stats', [TicketController::class, 'getQueueStats']);
// });

// // Protected API Routes (require authentication)
// Route::middleware(['auth', 'verified'])->prefix('api')->group(function () {

//     // ==================== DASHBOARD ROUTES ====================
//     // For Dashboard.vue, Main.vue, Header.vue, Menu.vue
//     Route::get('/dashboard/stats', [TicketController::class, 'getDashboardStats']);
//     Route::get('/user/profile', [ProfileController::class, 'profile']); // For UserProfile.vue

//     // ==================== USER MANAGEMENT ROUTES ====================
//     // For admin user management
//     Route::apiResource('users', UserController::class);
//     Route::get('/users/{user}/tickets-handled', [UserController::class, 'getTicketsHandled']);
//     Route::get('/users/{user}/average-resolution-time', [UserController::class, 'getAverageResolutionTime']);
//     Route::get('/users/{user}/activity-log', [UserController::class, 'getActivityLog']);
//     Route::patch('/users/{user}/account-status', [ProfileController::class, 'updateAccountStatus']);

//     // ==================== TICKET ROUTES ====================
//     // For Ticket's.vue, AddTicket.vue, QueueList.vue
//     Route::apiResource('tickets', TicketController::class);
//     Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assignTicket']);
//     Route::post('/tickets/{ticket}/resolve', [TicketController::class, 'markAsResolved']);
//     Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen']);
//     Route::get('/tickets/queue/next-in-line', [TicketController::class, 'getNextInLine']); // For NextInLine.vue
//     Route::get('/tickets/in-progress', [TicketController::class, 'getInProgressTickets']); // For InProgress.vue

//     // ==================== PROFILE ROUTES ====================
//     // For UserProfile.vue
//     Route::get('/profile', [ProfileController::class, 'profile']);
//     Route::put('/profile', [ProfileController::class, 'update']);
//     Route::get('/profile/activity-log', [ProfileController::class, 'getActivityLog']);
//     Route::get('/profile/ticket-stats', [ProfileController::class, 'getTicketStats']);

//     // ==================== ACTIVITY LOG ROUTES ====================
//     // For activity tracking
//     Route::apiResource('activity-logs', ActivityLogController::class);
//     Route::delete('/activity-logs/bulk-destroy', [ActivityLogController::class, 'bulkDestroy']);
//     Route::delete('/activity-logs/clear-old', [ActivityLogController::class, 'clearOldLogs']);

//     // ==================== NOTIFICATION ROUTES ====================
//     // For notification system
//     // Route::get('/notifications', [NotificationController::class, 'index']);
//     // Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
//     // Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
// });

// // Vue Route Handlers (SPA routes)
// Route::middleware(['auth', 'verified'])->group(function () {

//     // Main dashboard layout - all admin routes point to the same Vue component
//     Route::get('/dashboard', function () {
//         return view('vue.admin.dashboard');
//     });

//     Route::get('/dashboard/my-profile', function () {
//         return view('vue.admin.dashboard'); // Handled by UserProfile.vue
//     });

//     Route::get('/dashboard/queue-list', function () {
//         return view('vue.admin.dashboard'); // Handled by QueueList.vue and AdminQueueView.vue
//     });

//     Route::get('/dashboard/tickets', function () {
//         return view('vue.admin.dashboard'); // Handled by Ticket's.vue
//     });

//     Route::get('/dashboard/users', function () {
//         return view('vue.admin.dashboard'); // For user management
//     });

//     Route::get('/dashboard/activity-logs', function () {
//         return view('vue.admin.dashboard'); // For activity logs
//     });

//     // Additional routes for other Vue components
//     Route::get('/dashboard/add-ticket', function () {
//         return view('vue.admin.dashboard'); // Handled by AddTicket.vue
//     });

//     Route::get('/dashboard/tools', function () {
//         return view('vue.admin.dashboard'); // Handled by tools components
//     });
// });

// // Fallback route for Vue Router (must be last)
// Route::get('/{any}', function () {
//     return view('vue.admin.dashboard');
// })->where('any', '.*');


/*
|--------------------------------------------------------------------------
| 🌐 Public Routes (Walk-In Agents)
|--------------------------------------------------------------------------
| Accessible to walk-in agents without authentication.
| These routes render the public-facing ticket creation panel.
*/
// Route::get('/', [TicketController::class, 'create'])->name('tickets.create');
// Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
// Route::get('/tickets/panels', [TicketController::class, 'panels'])->name('tickets.panels');

// // ✅ AJAX endpoint to check if a ticket already exists
// Route::get('/tickets/check', [TicketController::class, 'check'])->name('tickets.check');

/*
|--------------------------------------------------------------------------
| 🔐 Authenticated Routes (IT Personnel)
|--------------------------------------------------------------------------
| Protected routes that require login and email verification.
*/
// Route::middleware(['auth', 'verified'])->group(function () {

//     /*
//     |--------------------------------------------------------------------------
//     | 📊 Dashboard (Main View + AJAX Partials)
//     |--------------------------------------------------------------------------
//     */
//     Route::get('/dashboard', [TicketController::class, 'dashboard'])->name('dashboard');
//     Route::get('/dashboard/tickets-table', [TicketController::class, 'ticketsTable'])->name('dashboard.ticketsTable');
//     Route::get('/dashboard/tickets-stats', [TicketController::class, 'ticketsStats'])->name('dashboard.ticketsStats');

//     /*
//     |--------------------------------------------------------------------------
//     | 🎟 Ticket Management Routes
//     |--------------------------------------------------------------------------
//     */
//     Route::get('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
//     Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
//     Route::post('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
//     Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

//     /*
//     |--------------------------------------------------------------------------
//     | 🔔 Notification Routes
//     |--------------------------------------------------------------------------
//     */
//     Route::prefix('notifications')->group(function () {
//         Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
//         Route::post('/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
//         Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsReadSingle'])->name('notifications.markAsReadSingle');
//         Route::delete('/clear', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
//         Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
//         Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
//         Route::get('/stats', [NotificationController::class, 'stats'])->name('notifications.stats');
//     });

//     /*
//     |--------------------------------------------------------------------------
//     | 🧪 Test Route: Notifications Debug
//     |--------------------------------------------------------------------------
//     */
//     Route::get('/test-notifications-db', function () {
//         $user = Auth::user();

//         $notifications = DB::table('notifications')
//             ->where('notifiable_type', get_class($user))
//             ->where('notifiable_id', $user->id)
//             ->get();

//         return response()->json([
//             'user' => $user->only(['id', 'name', 'email']),
//             'notifications_count' => $notifications->count(),
//             'notifications' => $notifications->map(fn($n) => [
//                 'id' => $n->id,
//                 'type' => $n->type,
//                 'data' => json_decode($n->data, true),
//                 'read_at' => $n->read_at,
//                 'created_at' => $n->created_at
//             ])
//         ]);
//     });

//     /*
//     |--------------------------------------------------------------------------
//     | 👤 User Profile Management
//     |--------------------------------------------------------------------------
//     */
//     Route::prefix('profile')->group(function () {
//         // ✅ Profile overview (with activity + stats)
//         Route::get('/', [ProfileController::class, 'profile'])->name('profile');

//         // ✅ Edit profile form (name + photo)
//         Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');

//         // ✅ Update profile data (POST form submission)
//         Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
//     });
// });

/*
|--------------------------------------------------------------------------
| 🔑 Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
