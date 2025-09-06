<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Homepage -> Ticket create form (Landing Page)
Route::get('/', [TicketController::class, 'create'])->name('tickets.create');

// Tickets
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::patch('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');

// Dashboard & Profile (authenticated users only)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard for IT Personnel
    Route::get('/dashboard', [TicketController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
