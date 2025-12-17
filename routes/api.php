<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TicketLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication Route
Route::post('/register', [RegisterController::class, 'register']);

// Secure Routes (Require Login)
Route::middleware('auth:sanctum')->group(function () {
    
    // Get Current User Info (Crucial for Login.vue role check)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // -------------------- USER PROFILE ROUTES -------------------- //
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getCurrentUserProfile']);
        Route::get('/activity-logs', [UserController::class, 'getCurrentUserActivityLogs']);
    });

    // -------------------- NOTIFICATION ROUTES -------------------- //
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);           
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']); 
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead']); 
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);   
        Route::delete('/{id}', [NotificationController::class, 'destroy']);  
    });

    // -------------------- USER MANAGEMENT ROUTES -------------------- //
    Route::prefix('users')->group(function () {
        
        // ✅ NEW: Reporting / List Routes (Must be defined BEFORE {user} wildcard)
        Route::get('/it-staff', [UserController::class, 'getITStaffList']);
        Route::post('/it-staff/activity', [UserController::class, 'getITStaffActivityByEmail']);
        
        Route::get('/clients', [UserController::class, 'getClientList']);
        Route::post('/clients/tickets', [UserController::class, 'getClientTicketsByEmail']);

        // Standard CRUD Routes
        Route::get('/', [UserController::class, 'index']);                  
        Route::post('/', [UserController::class, 'store']);                 
        Route::get('{user}', [UserController::class, 'show']);              
        Route::put('{user}', [UserController::class, 'update']);             
        Route::delete('{user}', [UserController::class, 'destroy']);        

        Route::get('{user}/tickets-handled', [UserController::class, 'ticketsHandled']);
        Route::get('{user}/average-resolution', [UserController::class, 'averageResolutionTime']);
        Route::get('{user}/activity-log', [UserController::class, 'activityLog']);
    });

    // -------------------- TICKET ROUTES -------------------- //
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);               
        Route::post('/', [TicketController::class, 'store']);              
        Route::get('{ticket}', [TicketController::class, 'show']);         
        Route::put('{ticket}', [TicketController::class, 'update']);       
        Route::delete('{ticket}', [TicketController::class, 'destroy']);   

        Route::post('{ticket}/add-log', [TicketController::class, 'addLog']); 
        Route::get('status/{status}', [TicketController::class, 'filterByStatus']); 
    });

    // -------------------- QUEUE ROUTES -------------------- //
    Route::prefix('queues')->group(function () {
        Route::get('/', [QueueController::class, 'index']);               
        Route::post('/', [QueueController::class, 'store']);              
        Route::get('{queue}', [QueueController::class, 'show']);          
        Route::put('{queue}', [QueueController::class, 'update']);        
        Route::delete('{queue}', [QueueController::class, 'destroy']);    

        Route::get('next-ticket', [QueueController::class, 'nextTicket']); 
    });

    // -------------------- ACTIVITY LOG ROUTES -------------------- //
    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);          
        Route::post('/', [ActivityLogController::class, 'store']);         
        Route::get('{activityLog}', [ActivityLogController::class, 'show']); 
        Route::delete('{activityLog}', [ActivityLogController::class, 'destroy']); 

        Route::get('user/{userId}', [ActivityLogController::class, 'logsByUser']);   
        Route::get('ticket/{ticketId}', [ActivityLogController::class, 'logsByTicket']); 
    });

    // -------------------- TICKET LOG ROUTES -------------------- //
    Route::prefix('ticket-logs')->group(function () {
        Route::get('/', [TicketLogController::class, 'index']);            
        Route::post('/', [TicketLogController::class, 'store']);           
        Route::get('{ticketLog}', [TicketLogController::class, 'show']);   
        Route::delete('{ticketLog}', [TicketLogController::class, 'destroy']); 

        Route::get('ticket/{ticketId}', [TicketLogController::class, 'logsForTicket']); 
    });
});