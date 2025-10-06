<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;

/*
|--------------------------------------------------------------------------
| 🌐 Public Routes (Walk-In Agents)
|--------------------------------------------------------------------------
| Agents can submit new tickets without login.
| Also includes public-facing panels for live stats (optional).
|--------------------------------------------------------------------------
*/

// 🟢 Landing page → ticket submission form
Route::get('/', [TicketController::class, 'create'])
    ->name('tickets.create');

// 🟢 Submit a new ticket
Route::post('/tickets', [TicketController::class, 'store'])
    ->name('tickets.store');

// 🟢 Optional: public AJAX panel for live stats (e.g. landing page display)
Route::get('/tickets/panels', [TicketController::class, 'panels'])
    ->name('tickets.panels');



/*
|--------------------------------------------------------------------------
| 🔐 Authenticated Routes (IT Personnel)
|--------------------------------------------------------------------------
| Require login & email verification.
| IT staff can access dashboard, assign/update tickets, etc.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /** 📊 Dashboard → Shows stats + tickets list */
    Route::get('/dashboard', [TicketController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | 🟢 AJAX Routes for Partial Refresh
    |--------------------------------------------------------------------------
    | These return only Blade partials (not full layout) to support
    | dynamic updates via fetch() in the dashboard.
    */
    Route::get('/dashboard/panels', [TicketController::class, 'panels'])
        ->name('dashboard.panels');

    Route::get('/dashboard/tickets-tables', [TicketController::class, 'tickets.tables'])
        ->name('dashboard.ticketsTable');

    /*
    |--------------------------------------------------------------------------
    | 🎟 Ticket Assignment & Update
    |--------------------------------------------------------------------------
    */
    // Load modal (assign IT personnel) → AJAX
    Route::get('/tickets/{ticket}/assign', [TicketController::class, 'modalAssign'])
        ->name('tickets.assign');

    // Update ticket (assign IT personnel, change status, etc.) via AJAX
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])
        ->name('tickets.update');

    /*
    |--------------------------------------------------------------------------
    | 👤 Profile Management
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'profile'])
        ->name('profile');
});



/*
|--------------------------------------------------------------------------
| 🔑 Authentication Scaffolding
|--------------------------------------------------------------------------
| Includes login, registration, password reset, etc.
| (Provided by Breeze, Jetstream, or Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
