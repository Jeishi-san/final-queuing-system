<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

// Make homepage go directly to the ticket form
Route::get('/', [TicketController::class, 'create'])->name('tickets.create');

// Ticket routes
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

// Dashboard (IT Personnel only after login)
Route::get('/dashboard', function () {
    $tickets = \App\Models\Ticket::where('status', 'pending')->get();
    return view('dashboard', compact('tickets'));
})->middleware(['auth', 'verified'])->name('dashboard');


// Profile routes (IT Personnel)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [TicketController::class, 'dashboard'])->name('dashboard');
});

Route::patch('/tickets/{ticket}/resolve', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');

Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

require __DIR__.'/auth.php';
