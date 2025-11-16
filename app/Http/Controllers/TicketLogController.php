<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketLog;

class TicketLogController extends Controller
{
    // List all ticket logs
    public function index()
    {
        return response()->json(TicketLog::latest()->get());
    }

    // Create a ticket log
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string',
            'details' => 'required|string'
        ]);

        $log = TicketLog::add($validated['ticket_id'], $validated['user_id'], $validated['action'], $validated['details']);
        return response()->json($log, 201);
    }

    // Show a single ticket log
    public function show(TicketLog $ticketLog)
    {
        return response()->json($ticketLog);
    }

    // Delete a ticket log
    public function destroy(TicketLog $ticketLog)
    {
        $ticketLog->delete();
        return response()->json(['message' => 'Ticket log deleted']);
    }

    // Get logs for a specific ticket
    public function logsForTicket($ticketId)
    {
        return response()->json(TicketLog::forTicket($ticketId)->latest()->get());
    }
}
