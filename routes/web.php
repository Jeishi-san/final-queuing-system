<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Agents – No Login Required)
|--------------------------------------------------------------------------
|
| Agents or walk-in users submit tickets without login.
| They may also see live stats panels if you expose them.
|
*/

// Landing page → Ticket submission form
Route::get('/', [TicketController::class, 'create'])
    ->name('tickets.create');

// Submit a new ticket
Route::post('/tickets', [TicketController::class, 'store'])
    ->name('tickets.store');

// Optional: live stats / ticket list panel for AJAX
Route::get('/tickets/panels', [TicketController::class, 'panels'])
    ->name('tickets.panels');



/*
|--------------------------------------------------------------------------
| Authenticated Routes (IT Personnel Only)
|--------------------------------------------------------------------------
|
| IT Personnel must log in to see dashboard, assign/update tickets,
| and view their own profile & activity logs.
|
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /** 📊 Dashboard for IT Personnel */
    Route::get('/dashboard', [TicketController::class, 'index'])
        ->name('dashboard');

    /** 🔄 Update ticket (status / IT personnel assignment) */
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])
        ->name('tickets.update');

    /** 🪟 Modal for assigning IT personnel (loaded by AJAX) */
    Route::get('/tickets/{ticket}/assign', [TicketController::class, 'modalAssign'])
        ->name('tickets.assign');

    /** 👤 Profile page for logged-in IT Personnel */
    Route::get('/profile', [ProfileController::class, 'profile'])
        ->name('profile');
});



/*
|--------------------------------------------------------------------------
| Authentication Scaffolding (e.g. Breeze / Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
