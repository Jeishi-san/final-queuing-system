<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Queue;
use App\Http\Controllers\QueueController;

class TicketController extends Controller
{
    // List ticket items by filters
    public function index(Request $request)
    {
        $query = Ticket::query();

        // Filter by ticket_number
        if ($request->ticket_number) {
            $query->where('ticket_number', 'LIKE', '%'.$request->ticket_number.'%');
        }

        // Filter by holder_name
        if ($request->holder_name) {
            $query->where('holder_name', 'LIKE', '%'.$request->holder_name.'%');
        }

        // Filter by holder_email
        if ($request->holder_email) {
            $query->where('holder_email', 'LIKE', '%'.$request->holder_email.'%');
        }

        // Filter by issue
        if ($request->issue) {
            $query->where('issue', 'LIKE', '%'.$request->issue.'%');
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->start_date) {
            $query->whereDate('updated_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('updated_at', '<=', $request->end_date);
        }

        return $query->orderBy('ticket_number')->get();
    }

    // Create a new ticket
    public function store(Request $request)
    {
        $validated = $request->validate([
            'holder_name' => 'required|string',
            'holder_email' => 'required|email',
            'ticket_number' => 'required|string|unique:tickets',
            'issue' => 'required|string',
            'status' => 'nullable|in:pending approval,queued,in progress,on hold,resolved,cancelled'
        ]);

        $ticket = Ticket::create($validated);
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

        $ticket->update($validated);
        return response()->json($ticket);
    }

    // Delete a ticket
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted']);
    }

    // Add a ticket log
    public function addLog(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string',
            'details' => 'required|string'
        ]);

        $log = $ticket->addLog($validated['user_id'], $validated['action'], $validated['details']);
        return response()->json($log, 201);
    }

    // Filter tickets by status
    public function filterByStatus($status)
    {
        return response()->json(Ticket::status($status)->get());
    }

    public function addTicketToQueue(Ticket $ticket)
    {
        //Prevent duplicate queue entries
        if (Queue::where('ticket_id', $ticket->id)->exists()) {
            return;
        }

        Queue::create([
            'ticket_id'   => $ticket->id,
            'assigned_to' => auth('web')->id(),
            'queue_number' => QueueController::generateQueueNumber(),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);
        $newStatus = $request->status;

        $ticket->status = $newStatus;
        $ticket->save();

        // When status changes *to* queued → add to queue
        if ($newStatus === 'queued') {
            $this->addTicketToQueue($ticket);
        } else {
            // Update assigned_to in the queue table
            $queue = Queue::where('ticket_id', $ticket->id)->first();

            if ($queue) {
                $queue->assigned_to = auth('web')->id(); // who is updating the status
                $queue->save();
            }
        }

        return response()->json([
            'message' => 'Status updated',
            'ticket' => $ticket
        ]);
    }

    public function countQueuedTickets()
    {
        $queued = Ticket::where('status', 'queued')->count();

        $inProgress = Ticket::where('status', 'in progress')->count();

        $count = $queued + $inProgress;

        $resolved = Ticket::where('status', 'resolved')->count();

        return response()->json(['queued_tickets' => $count, 'waiting'=> $queued, 'resolved_tickets' => $resolved]);
    }

}
