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

// -------------------- PROTECTED IT STAFF/ADMIN/SUPER ADMIN ROUTES -------------------- //
// 🔑 FIX: Use Role Middleware to protect IT Staff views/functions, including 'super_admin'.
Route::middleware(['auth', 'verified', 'role:it_staff,admin,super_admin'])->group(function () {

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

    Route::get('/dashboard/users', function () {
        return view('vue.admin.dashboard');
    })->name('users');

    Route::get('/dashboard/notifications', function () {
        return view('vue.admin.dashboard');
    })->name('notifications.page');

});

// -------------------- AGENT ONLY/GENERAL AUTH ROUTES -------------------- //
// This route is NOT protected by the specific IT staff role middleware.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/queue', function () {
        return view('vue.queue');
    })->name('queue');
});


// -------------------- API CONTROLLERS -------------------- //
Route::middleware(['auth'])->group(function () {

    // API Routes that grant full access (IT Staff, Admin, Super Admin)
    Route::get('/user/activity-logs', [UserController::class, 'getCurrentUserActivityLogs']);
    Route::get('/agent/submitted-tickets', [TicketController::class, 'getMySubmittedTickets']);

    // User Routes
    Route::prefix('users')->group(function () {
        
        // ✅ NEW: Reporting / List Routes (Must be defined BEFORE {user} wildcard)
        Route::get('/it-staff', [UserController::class, 'getITStaffList']);
        Route::post('/it-staff/activity', [UserController::class, 'getITStaffActivityByEmail']);
        
        Route::get('/clients', [UserController::class, 'getClientList']);
        Route::post('/clients/tickets', [UserController::class, 'getClientTicketsByEmail']);

        // Standard CRUD
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
        Route::get('/summary', [TicketController::class, 'summary']);

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