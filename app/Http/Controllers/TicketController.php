<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\Queue;
use App\Models\ActivityLog;
use App\Models\User;
use App\Http\Controllers\QueueController;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    // List ticket items by filters
    public function index(Request $request)
    {
        $query = Ticket::query();

        // Filter by ticket_number
        if ($request->ticket_number) {
            $query->where('ticket_number', 'LIKE', '%'.$request->ticket_number.'%')
                    ->whereNull('deleted_at');
        }

        // Filter by holder_name
        if ($request->holder_name) {
            $query->where('holder_name', 'LIKE', '%'.$request->holder_name.'%')
                    ->whereNull('deleted_at');
        }

        // Filter by holder_email
        if ($request->holder_email) {
            $query->where('holder_email', 'LIKE', '%'.$request->holder_email.'%')
                    ->whereNull('deleted_at');
        }

        // Filter by issue
        if ($request->issue) {
            $query->where('issue', 'LIKE', '%'.$request->issue.'%')
                    ->whereNull('deleted_at');
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status)
                    ->whereNull('deleted_at');
        }

        // Filter by date range
        if ($request->start_date) {
            $query->whereDate('updated_at', '>=', $request->start_date)
                    ->whereNull('deleted_at');
        }
        if ($request->end_date) {
            $query->whereDate('updated_at', '<=', $request->end_date)
                    ->whereNull('deleted_at');
        }

        // Filter by next in line queued tickets
        if ($request->nextQueued) {
            $query->where('status', 'queued')
                    ->whereNull('deleted_at');
            return $query->orderBy('created_at', 'asc')->take(5)->get();
        }

        return $query->orderBy('created_at', 'asc')->orderBy('status')->get();
    }

    // Create a new ticket (Guest or User)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'holder_name' => 'required|string',
            'holder_email' => 'required|email',
            'ticket_number' => [
                'required',
                'string',
                Rule::unique('tickets', 'ticket_number')->withoutTrashed(),
            ],
            'issue' => 'required|string'
        ]);

        $ticket = Ticket::create($validated);

        // Determine if guest or logged-in user
        $user = auth('web')->user();
        $updaterName = $user ? $user->name : 'Guest';

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user ? $user->id : null,
            'action'    => 'Ticket created by ' . $updaterName,
        ]);

        // ✅ NOTIFY ADMINS: Send 'is_new' flag
        $this->notifyAdmins($ticket, ['is_new' => true], $user);

        return response()->json($ticket, 201);
    }

    // Show a single ticket
    public function show(Ticket $ticket)
    {
        return response()->json($ticket);
    }

    // Update an existing ticket
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'holder_name' => 'sometimes|string',
            'holder_email' => 'sometimes|email',
            'ticket_number' => 'sometimes|string|unique:tickets,ticket_number,' . $ticket->id,
            'issue' => 'sometimes|string',
            'status' => 'sometimes|in:pending approval,queued,in progress,on hold,resolved,cancelled',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $originalAssignee = $ticket->assigned_to;

        $ticket->update($validated);

        // Notify if assignment changed
        if (isset($validated['assigned_to']) && $originalAssignee != $validated['assigned_to']) {
            $changes = [
                'it_personnel_id' => ['from' => $originalAssignee, 'to' => $ticket->assigned_to]
            ];
            // Notify Admins and relevant users
            $this->notifyRelevantUsers($ticket, $changes, auth('web')->user());
        }

        return response()->json($ticket);
    }

    // Delete a ticket
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        // Log the status change
        TicketLog::add(
            $ticket->id,
            auth('web')->id(),
            'Ticket has been deleted'
        );

        // Log user activity
        $this->logActivity(
            auth('web')->id(),
            "Deleted ticket #{$ticket->ticket_number}"
        );

        return response()->json(['message' => 'Ticket deleted']);
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string',
            ]);

            $ticket = Ticket::findOrFail($id);
            $oldStatus = $ticket->status;
            $currentUser = auth('web')->user();

            $ticketLog_message = "";

            // Only process if status is actually different
            if ($ticket->status != $validated['status']) {

                if ($validated['status'] === 'queued') {
                    $this->addTicketToQueue($ticket);
                    $ticketLog_message = "Ticket validated and added to queue";
                } elseif ($validated['status'] === 'in progress') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket is being processed";
                } elseif ($validated['status'] === 'resolved') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been resolved";
                } elseif ($validated['status'] === 'on hold') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been put on hold";
                } elseif ($validated['status'] === 'cancelled') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been cancelled";
                } elseif ($validated['status'] === 'dequeued') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been dequeued";
                } else {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket status changed to " . $validated['status'];
                }

                // Log the status change
                TicketLog::add(
                    $ticket->id,
                    $currentUser ? $currentUser->id : null,
                    $ticketLog_message
                );

                // Log user activity
                $this->logActivity(
                    $currentUser ? $currentUser->id : null,
                    "Updated status of ticket #{$ticket->ticket_number} to {$validated['status']}"
                );

                // Save status
                $ticket->status = $validated['status'];
                $ticket->save();

                // ✅ SEND NOTIFICATION
                $changes = [
                    'status' => ['from' => $oldStatus, 'to' => $ticket->status]
                ];

                // Call the function to notify ALL users
                $this->notifyAdmins($ticket, $changes, $currentUser);

                /*  Comment by Joshua Icalina
                    I dont understand this code:
                        if ($ticket->user_id) {...}

                    'tickets' table has no 'user_id' column.
                    necessary ba ning i-add pa dre nga di mani magamit, at least, sa current structure sa system?
                */

                // Notify Ticket Owner (if they have a user account)
                if ($ticket->user_id) {
                    $owner = User::find($ticket->user_id);
                    // Don't notify owner if they are the one making the change
                    if ($owner && (!$currentUser || $owner->id !== $currentUser->id)) {
                        $owner->notify(new TicketUpdatedNotification($ticket, $changes, $currentUser));
                    }
                }

                return response()->json(['message' => 'Ticket status updated successfully']);
            }

            return response()->json(['message' => 'No changes made to ticket status']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update ticket: ' . $e->getMessage()], 500);
        }
    }

    // --- Helpers ---

    private function notifyAdmins($ticket, $changes, $updater)
    {
        try {
            // Get ALL users
            $recipients = User::all();

            // ⚠️ UNCOMMENT BELOW IN PRODUCTION
            /*
            if ($updater) {
                $recipients = $recipients->reject(function ($user) use ($updater) {
                    return $user->id === $updater->id;
                });
            }
            */

            if ($recipients->count() > 0) {
                Notification::send($recipients, new TicketUpdatedNotification($ticket, $changes, $updater));
                Log::info('Notification sent to ' . $recipients->count() . ' users.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify users', ['error' => $e->getMessage()]);
        }
    }

    private function notifyRelevantUsers($ticket, $changes, $updater)
    {
        $this->notifyAdmins($ticket, $changes, $updater);

        if ($ticket->assigned_to) {
            $assignee = User::find($ticket->assigned_to);
            if ($assignee && (!$updater || $assignee->id !== $updater->id)) {
                $assignee->notify(new TicketUpdatedNotification($ticket, $changes, $updater));
            }
        }
    }


    public function addTicketToQueue(Ticket $ticket)
    {
        //Prevent duplicate queue entries
        if (Queue::where('ticket_id', $ticket->id)->exists()) {
            return response()->json(['message' => 'Queue already exists']);
        }
        $queue = Queue::create([
            'ticket_id'   => $ticket->id,
            'assigned_to' => auth('web')->id(),
            'queue_number' => QueueController::generateQueueNumber(),
        ]);

        return $queue;
    }

    private function updateAssignedUser(Ticket $ticket)
    {
        $queue = Queue::where('ticket_id', $ticket->id)->first();
        if ($queue) {
            $queue->assigned_to = auth('web')->id();
            $queue->save();
        }
    }

    private function logActivity($userId, $action)
    {
        if ($userId) {
            ActivityLog::create([
                'user_id' => $userId,
                'action'  => $action,
            ]);
        }
    }

    public function countQueuedTickets()
    {
        $queued = Ticket::where('status', 'queued')->count();
        $inProgress = Ticket::where('status', 'in progress')->count();
        $resolved = Ticket::where('status', 'resolved')->count();
        return response()->json(['queued_tickets' => $queued + $inProgress, 'waiting' => $queued, 'resolved_tickets' => $resolved]);
    }

    // Filter tickets by status
    public function filterByStatus($status) { return response()->json(Ticket::status($status)->get()); }
}
