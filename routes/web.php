<?php 

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| 🌐 Public Routes (Walk-In Agents)
|--------------------------------------------------------------------------
| Accessible to walk-in agents without authentication.
| These routes render the public-facing ticket creation panel.
*/
Route::get('/', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/panels', [TicketController::class, 'panels'])->name('tickets.panels');

// ✅ AJAX endpoint to check if a ticket already exists
Route::get('/tickets/check', [TicketController::class, 'check'])->name('tickets.check');

/*
|--------------------------------------------------------------------------
| 🔐 Authenticated Routes (IT Personnel)
|--------------------------------------------------------------------------
| Protected routes that require login and email verification.
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 📊 Dashboard (Main View + AJAX Partials)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [TicketController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/tickets-table', [TicketController::class, 'ticketsTable'])->name('dashboard.ticketsTable');
    Route::get('/dashboard/tickets-stats', [TicketController::class, 'ticketsStats'])->name('dashboard.ticketsStats');

    /*
    |--------------------------------------------------------------------------
    | 🎟 Ticket Management Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    /*
    |--------------------------------------------------------------------------
    | 🔔 Notification Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsReadSingle'])->name('notifications.markAsReadSingle');
        Route::delete('/clear', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::get('/stats', [NotificationController::class, 'stats'])->name('notifications.stats');
    });

    /*
    |--------------------------------------------------------------------------
    | 🧪 Test Route: Notifications Debug
    |--------------------------------------------------------------------------
    */
    Route::get('/test-notifications-db', function () {
        $user = Auth::user();

        $notifications = DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->get();

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'notifications_count' => $notifications->count(),
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'data' => json_decode($n->data, true),
                'read_at' => $n->read_at,
                'created_at' => $n->created_at
            ])
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | 👤 User Profile Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        // ✅ Profile overview (with activity + stats)
        Route::get('/', [ProfileController::class, 'profile'])->name('profile');

        // ✅ Edit profile form (name + photo)
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');

        // ✅ Update profile data (POST form submission)
        Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
    });
});

/*
|--------------------------------------------------------------------------
| 🔑 Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
