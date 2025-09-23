<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Agents - No Login Required)
|--------------------------------------------------------------------------
*/

// Landing Page -> Ticket create form (unauthenticated agents)
Route::get('/', [TicketController::class, 'create'])->name('tickets.create');

// Store new ticket
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

// Live stats (for agents and dashboard) - JSON response
Route::get('/tickets/panelstats', [TicketController::class, 'statsPartial'])->name('tickets.panels');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (IT Personnel Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard for IT Personnel
    Route::get('/dashboard', [TicketController::class, 'index'])->name('dashboard');

    // Ticket updates (AJAX)
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

    // Floating panel modal routes (only IT should access)
    Route::get('/tickets/{ticket}/status', [TicketController::class, 'modalStatus']);
    Route::get('/tickets/{ticket}/assign', [TicketController::class, 'modalAssign']);
    Route::get('/tickets/{ticket}/view', [TicketController::class, 'modalView']);

    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Auth Scaffolding (Laravel Breeze/Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
