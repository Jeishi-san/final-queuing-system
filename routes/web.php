<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| 🌐 Public Routes (Walk-In Agents)
|--------------------------------------------------------------------------
| Accessible to walk-in agents without authentication
*/
Route::get('/', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/panels', [TicketController::class, 'panels'])->name('tickets.panels');

// ✅ Consolidated AJAX ticket existence check
Route::get('/tickets/check', [TicketController::class, 'check'])->name('tickets.check');

/*
|--------------------------------------------------------------------------
| 🔐 Authenticated Routes (IT Personnel)
|--------------------------------------------------------------------------
| Require login and email verification
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 📊 Dashboard & dynamic components
    Route::get('/dashboard', [TicketController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/tickets-table', [TicketController::class, 'ticketsTable'])->name('dashboard.ticketsTable');

    // ✅ Tickets stats for AJAX refresh (must return partial Blade view with #statsPanel)
    Route::get('/dashboard/tickets-stats', [TicketController::class, 'ticketsStats'])->name('dashboard.ticketsStats');

    // 🔔 Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');

    // 🎟 Ticket Assignment & Update
    Route::get('/tickets/{ticket}/assign', [TicketController::class, 'modalAssign'])->name('tickets.assign');
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

    // 👤 User Profile
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
});

/*
|--------------------------------------------------------------------------
| 🔑 Authentication
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
