<?php

use Illuminate\Support\Facades\Route;
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
    | Main dashboard view loads once. AJAX calls fetch tables/stats separately.
    */
    Route::get('/dashboard', [TicketController::class, 'dashboard'])->name('dashboard');

    // ✅ AJAX: Tickets Table Partial (for live search/pagination)
    Route::get('/dashboard/tickets-table', [TicketController::class, 'ticketsTable'])
        ->name('dashboard.ticketsTable');

    // ✅ AJAX: Dashboard Stats Partial
    Route::get('/dashboard/tickets-stats', [TicketController::class, 'ticketsStats'])
        ->name('dashboard.ticketsStats');

    /*
    |--------------------------------------------------------------------------
    | 🔔 Notifications
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');

    /*
    |--------------------------------------------------------------------------
    | 🎟 Ticket Assignment & Update
    |--------------------------------------------------------------------------
    */
    // ✅ FIXED: Changed from modalAssign to assign to match JavaScript expectations
    Route::get('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    
    // ✅ FIXED: Ensure this route exists for form submissions
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

    /*
    |--------------------------------------------------------------------------
    | 👤 User Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
});

/*
|--------------------------------------------------------------------------
| 🔑 Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';