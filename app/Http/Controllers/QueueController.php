<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;

class QueueController extends Controller
{
    // List all queue items
    public function index()
    {
        return response()->json(Queue::all());
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
}
