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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('vue.home');
});

Route::get('/queue', function () {
    return view('vue.queue');
});

Route::middleware(['auth', 'verified'])->group(function () {

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

    // ✅ Notification Page Route
    Route::get('/dashboard/notifications', function () {
        return view('vue.admin.dashboard');
    })->name('notifications.page');
    
});

// -------------------- USER ROUTES -------------------- //
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

// -------------------- QUEUE ROUTES -------------------- //
Route::prefix('queues')->group(function () {
    Route::get('/', [QueueController::class, 'getQueueList']);             
    Route::get('/list', [QueueController::class, 'index']);             
    Route::get('/inProgress', [QueueController::class, 'getInProgressQueues']);             
    Route::get('/waiting', [QueueController::class, 'getWaitingItems']);             

    Route::post('/', [QueueController::class, 'store']);              
    Route::get('{queue}', [QueueController::class, 'show']);          
    Route::put('{queue}', [QueueController::class, 'update']);        
    Route::delete('{queue}', [QueueController::class, 'destroy']);    

    Route::get('next-ticket', [QueueController::class, 'nextTicket']); 
});

// -------------------- TICKET ROUTES -------------------- //
Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index']);             
    Route::get('/queued', [TicketController::class, 'countQueuedTickets']);             


    Route::post('/', [TicketController::class, 'store']);              
    Route::get('{ticket}', [TicketController::class, 'show']);         
    Route::put('{ticket}', [TicketController::class, 'updateStatus']);       
    Route::delete('{ticket}', [TicketController::class, 'destroy']);   

    Route::post('/', [TicketController::class, 'store']);              // Create ticket
    Route::get('{ticket}', [TicketController::class, 'show']);         // Show ticket
    Route::put('{ticket}', [TicketController::class, 'updateStatus']);       // Update ticket


    Route::post('{ticket}/add-log', [TicketController::class, 'addLog']); 
    Route::get('/{ticket}/logs', [TicketLogController::class, 'logsForTicket']);
    Route::get('status/{status}', [TicketController::class, 'filterByStatus']); 
});

require __DIR__ . '/auth.php';