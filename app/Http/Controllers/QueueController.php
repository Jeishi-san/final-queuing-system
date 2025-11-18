<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;

class QueueController extends Controller
{
    // List all queue items
    public function index()
    {
        $queues = Queue::with(['ticket', 'assignedUser'])->get();

        return response()->json($queues);
    }

    // List 5 queue items
    public function getQueueList()
    {
        $queues = Queue::with('ticket',) // eager load related ticket
            ->whereHas('ticket', function ($query) {
                $query->where('status', 'queued');
            })
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        return response()->json($queues);
    }

    // List queues with tickets "in progress"
    public function getInProgressQueues()
    {
        $queues = Queue::with(['ticket', 'assignedUser']) // eager load related ticket
            ->whereHas('ticket', function ($query) {
                $query->where('status', 'in progress');
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
}
