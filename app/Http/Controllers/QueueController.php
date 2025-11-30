<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\TicketLog;
use App\Models\ActivityLog;

class QueueController extends Controller
{
    // List queue items by filters
    public function index(Request $request)
    {
        $query = Queue::with(['ticket', 'assignedUser']);

        // Filter by queue_number
        if ($request->queue_number) {
            $query->where('queue_number', 'LIKE', '%'.$request->queue_number.'%')
                    ->whereNull('deleted_at');
        }

        // Filter by ticket_number
        if ($request->ticket_number) {
            $query->whereHas('ticket', function ($q) use ($request) {
                $q->where('ticket_number', 'LIKE', '%'.$request->ticket_number.'%')
                    ->whereNull('deleted_at');
            });
        }

        // Filter by assigned staff
        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to)
                    ->whereNull('deleted_at');
        }

        // Filter by status
        if ($request->status) {
            $query->whereHas('ticket', function ($q) use ($request) {
                $q->where('status', $request->status)
                    ->whereNull('deleted_at');
            });
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

        return $query->orderBy('queue_number')->get();
    }

    // List 5 queue items
    public function getQueueList()
    {
        $queues = Queue::with('ticket',) // eager load related ticket
            ->whereHas('ticket', function ($query) {
                $query->where('status', 'queued')
                        ->whereNull('deleted_at');
            })
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        return response()->json($queues);
    }

    // Get count of waiting tickets
    public function getWaitingItems()
    {
        $queues = Queue::with('ticket',) // eager load related ticket
            ->whereHas('ticket', function ($query) {
                $query->where('status', 'queued')
                        ->whereNull('deleted_at');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($queues);
    }

    // List queues with tickets "in progress"
    public function getInProgressQueues()
    {
        $queues = Queue::with(['ticket', 'assignedUser']) // eager load related ticket
            ->whereHas('ticket', function ($query) {
                $query->where('status', 'in progress')
                        ->whereNull('deleted_at');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($queues);
    }

    // Add a ticket to queue
    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $queue = Queue::enqueue($request->ticket_id);

        if($request->assigned_to){
            $queue->update(['assigned_to' => $request->assigned_to]);
        }

        return response()->json($queue, 201);
    }

    // Show a single queue item
    public function show(Queue $queue)
    {
        return response()->json($queue);
    }

    // Update a queue item
    public function update(Request $request, Queue $queue)
    {
        $queue->update($request->only('assigned_to'));
        return response()->json($queue);
    }

    // Remove a queue item
    public function destroy(Queue $queue)
    {
        $queue->dequeue();
        return response()->json(['message' => 'Queue item removed']);
    }

    // Get the next ticket in the queue
    public function nextTicket()
    {
        return response()->json(Queue::nextTicket());
    }

    public static function generateQueueNumber()
    {
        $lastQueue = Queue::orderBy('queue_number', 'desc')->first();

        // Extract the number part (remove "DAM")
        if ($lastQueue && str_starts_with($lastQueue->queue_number, 'DAM')) {
            $num = intval(substr($lastQueue->queue_number, 3)) + 1;
        } else {
            $num = 0;
        }

        // Format back to DAM000000 style
        return 'DAM' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    public function deleteByTicket($ticketId)
    {
        $queue = Queue::where('ticket_id', $ticketId)->first();

        if ($queue) {
            $queue->delete(); // soft delete
        }

        return response()->json(['message' => 'Queue deleted if it existed.']);
    }
}
