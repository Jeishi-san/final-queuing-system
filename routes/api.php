<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TicketLogController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes handle all CRUD and helper methods for Users, Tickets,
| Queues, Activity Logs, and Ticket Logs. All responses are JSON.
|
*/

// -------------------- USER PROFILE ROUTES -------------------- //
// ✅ ADDED: Routes for current user profile (these were missing)
Route::prefix('user')->group(function () {
    Route::get('/profile', [UserController::class, 'getCurrentUserProfile']);
    Route::get('/activity-logs', [UserController::class, 'getCurrentUserActivityLogs']);
});

// -------------------- USER ROUTES -------------------- //
Route::prefix('users')->group(function () {

    // ✅ CHANGED: Using '/create-account' instead of '/register' to avoid conflicts
    Route::post('/create-account', [RegisterController::class, 'register']);

    Route::get('/', [UserController::class, 'index']);                  // List all users
    Route::post('/', [UserController::class, 'store']);                 // Create new user (admin)
    Route::get('{user}', [UserController::class, 'show']);              // Show user details
    Route::put('{user}', [UserController::class, 'update']);            // Update user
    Route::delete('{user}', [UserController::class, 'destroy']);        // Delete user

    // Helper routes
    Route::get('{user}/tickets-handled', [UserController::class, 'ticketsHandled']);
    Route::get('{user}/average-resolution', [UserController::class, 'averageResolutionTime']);
    Route::get('{user}/activity-log', [UserController::class, 'activityLog']);

});

// -------------------- TICKET ROUTES -------------------- //
Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index']);               // List all tickets
    Route::post('/', [TicketController::class, 'store']);              // Create ticket
    Route::get('{ticket}', [TicketController::class, 'show']);         // Show ticket
    Route::put('{ticket}', [TicketController::class, 'update']);       // Update ticket
    Route::delete('{ticket}', [TicketController::class, 'destroy']);   // Delete ticket

    // Helper routes
    Route::post('{ticket}/add-log', [TicketController::class, 'addLog']); // Add log to ticket
    Route::get('status/{status}', [TicketController::class, 'filterByStatus']); // Filter by status
});

// -------------------- QUEUE ROUTES -------------------- //
Route::prefix('queues')->group(function () {
    Route::get('/', [QueueController::class, 'index']);               // List all queue items
    Route::post('/', [QueueController::class, 'store']);              // Add ticket to queue
    Route::get('{queue}', [QueueController::class, 'show']);          // Show queue item
    Route::put('{queue}', [QueueController::class, 'update']);        // Update queue item
    Route::delete('{queue}', [QueueController::class, 'destroy']);    // Remove queue item

    // Helper route
    Route::get('next-ticket', [QueueController::class, 'nextTicket']); // Get next ticket
});

// -------------------- ACTIVITY LOG ROUTES -------------------- //
Route::prefix('activity-logs')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index']);          // List all activity logs
    Route::post('/', [ActivityLogController::class, 'store']);         // Create activity log
    Route::get('{activityLog}', [ActivityLogController::class, 'show']); // Show activity log
    Route::delete('{activityLog}', [ActivityLogController::class, 'destroy']); // Delete activity log

    // Helper routes
    Route::get('user/{userId}', [ActivityLogController::class, 'logsByUser']);   // Logs by user
    Route::get('ticket/{ticketId}', [ActivityLogController::class, 'logsByTicket']); // Logs by ticket
    
    // ✅ NEW: Activity Log chat routes
    Route::post('chat/session/start', [ActivityLogController::class, 'startChatSession']);
    Route::post('chat/session/{session}/end', [ActivityLogController::class, 'endChatSession']);
    Route::get('chat/session/{session}/messages', [ActivityLogController::class, 'getChatSessionMessages']);
    Route::get('chat/sessions/user/{userId}', [ActivityLogController::class, 'getUserChatSessions']);
    Route::get('chat/sessions/ticket/{ticketId}', [ActivityLogController::class, 'getTicketChatSessions']);
});

// -------------------- TICKET LOG ROUTES -------------------- //
Route::prefix('ticket-logs')->group(function () {
    Route::get('/', [TicketLogController::class, 'index']);            // List all ticket logs
    Route::post('/', [TicketLogController::class, 'store']);           // Create ticket log
    Route::get('{ticketLog}', [TicketLogController::class, 'show']);   // Show ticket log
    Route::delete('{ticketLog}', [TicketLogController::class, 'destroy']); // Delete ticket log

    // Helper route
    Route::get('ticket/{ticketId}', [TicketLogController::class, 'logsForTicket']); // Logs for a ticket
});