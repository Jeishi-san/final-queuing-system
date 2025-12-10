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
use App\Http\Controllers\Auth\RegisterController; 

// -------------------- PUBLIC ROUTES -------------------- //
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        // Smart Redirect based on Role
        if ($user->role === 'agent') {
            return redirect('/queue');
        }
        return redirect('/dashboard');
    }
    return view('vue.home');
});

// -------------------- PROTECTED ROUTES -------------------- //
Route::middleware(['auth', 'verified'])->group(function () {

    // ✅ FIXED: Updated path to match your folder structure (views/vue/auth/queue.blade.php)
    Route::get('/queue', function () {
        return view('vue.queue'); // Assuming 'vue.auth.queue' is the correct view path
    })->name('queue');

    Route::get('/dashboard', function () {
        return view('vue.admin.dashboard');
    })->name('dashboard');

    Route::get('/dashboard/my-profile', function () {
        return view('vue.admin.dashboard');
    })->name('profile');

    Route::get('/dashboard/edit-profile', function () {
        return view('vue.admin.dashboard');
    })->name('edit-profile');

    Route::get('/dashboard/queue-list', function () {
        return view('vue.admin.dashboard');
    })->name('queue.list');

    Route::get('/dashboard/tickets', function () {
        return view('vue.admin.dashboard');
    })->name('tickets');

    Route::get('/dashboard/notifications', function () {
        return view('vue.admin.dashboard');
    })->name('notifications.page');

});

// -------------------- API CONTROLLERS -------------------- //
Route::middleware(['auth'])->group(function () {
    
    // ✅ FIX: Add the missing route for the current user's activity log (404 Error)
    // The frontend calls: /user/activity-logs
    Route::get('/user/activity-logs', [UserController::class, 'getCurrentUserActivityLogs']);
    
    // User Routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'getUsers']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('{user}', [UserController::class, 'show']);
        Route::put('{user}', [UserController::class, 'update']);
        Route::delete('{user}', [UserController::class, 'destroy']);

        Route::get('{user}/tickets-handled', [UserController::class, 'ticketsHandled']);
        Route::get('{user}/average-resolution', [UserController::class, 'averageResolutionTime']);
        Route::get('{user}/activity-log', [UserController::class, 'activityLog']);
    });

    // Queue Routes
    Route::prefix('queues')->group(function () {
        Route::get('/', [QueueController::class, 'getQueueList']);
        Route::get('/list', [QueueController::class, 'index']);
        Route::get('/inProgress', [QueueController::class, 'getInProgressQueues']);
        Route::get('/waiting', [QueueController::class, 'getWaitingItems']);

        Route::post('/', [QueueController::class, 'store']);
        Route::get('{queue}', [QueueController::class, 'show']);
        Route::put('{queue}', [QueueController::class, 'update']);
        Route::delete('{queue}', [QueueController::class, 'destroy']);
        Route::delete('/by-ticket/{ticket_id}', [QueueController::class, 'deleteByTicket']); 
        Route::get('next-ticket', [QueueController::class, 'nextTicket']);
    });

    // Ticket Routes
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/queued', [TicketController::class, 'countQueuedTickets']);

        Route::post('/', [TicketController::class, 'store']);
        Route::get('{ticket}', [TicketController::class, 'show']);
        Route::put('{ticket}', [TicketController::class, 'updateStatus']);
        Route::delete('{ticket}', [TicketController::class, 'destroy']);

        Route::post('{ticket}/add-log', [TicketController::class, 'addLog']);
        Route::get('/{ticket}/logs', [TicketLogController::class, 'logsForTicket']);
        Route::get('status/{status}', [TicketController::class, 'filterByStatus']);
    });
});

// 1. Load Default Routes First
require __DIR__ . '/auth.php';

// -------------------- CUSTOM REGISTRATION OVERRIDES -------------------- //
// ⚠️ These MUST be at the very bottom to override 'auth.php'

// Fixes "GET method not supported": Defines how to show the page
Route::get('/register', function () {
    return view('vue.auth.register'); 
})->middleware('guest')->name('register');

// Fixes "Employee ID required": Points to YOUR custom controller
Route::post('/register', [RegisterController::class, 'register'])
    ->middleware('guest');