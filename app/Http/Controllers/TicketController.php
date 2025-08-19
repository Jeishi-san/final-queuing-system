<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Show list of tickets (for IT Personnel only)
     */
    public function index()
    {
        $tickets = [];

        if (Auth::check()) {
            $tickets = Ticket::with('itPersonnel')->latest()->get();
        }

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show ticket submission form (homepage)
     */
    public function create()
    {
        // No need to pass agents/teamLeaders/components anymore
        return view('tickets.create');
    }

    /**
     * Store new ticket
     */
public function store(Request $request)
{
    // ✅ Validate inputs
    $validated = $request->validate([
        'ticket_number' => 'required|string|max:50|unique:tickets,ticket_number',
        'agent_name' => 'required|string|max:255',
        'agent_email' => 'required|email|max:255',
        'team_leader_name' => 'required|string|max:255',
        'component' => 'required|string|max:255',
        'issue_description' => 'required|string',
    ]);

    try {
        // ✅ Create ticket (agent enters ticket number directly)
        $ticket = Ticket::create([
            'ticket_number' => $validated['ticket_number'], // 🔹 User entered
            'agent_name' => $validated['agent_name'],
            'agent_email' => $validated['agent_email'],
            'team_leader_name' => $validated['team_leader_name'],
            'component' => $validated['component'],
            'issue_description' => $validated['issue_description'],
            'status' => 'Pending', // default status
        ]);

        return redirect()->back()->with('success', 'Ticket submitted successfully. Ticket Number: ' . $ticket->ticket_number);

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to submit ticket: ' . $e->getMessage());
    }
}

    /**
     * Show a single ticket (IT Personnel only)
     */
    public function show(Ticket $ticket)
    {
        return view('tickets.show', compact('ticket'));
    }

    /**
     * Update a ticket (status / assigned IT Personnel)
     */
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $ticket->update([
            'status' => $request->status,
            'it_personnel_id' => Auth::id(),
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully!');
    }


       public function dashboard()
    {
        $tickets = Ticket::where('status', 'pending')->latest()->get();
        return view('dashboard', compact('tickets'));
    }

    public function updateStatus($id)
{
    $ticket = Ticket::findOrFail($id);
    $ticket->status = 'resolved';
    $ticket->save();

    return redirect()->route('dashboard')->with('success', 'Ticket marked as resolved ✅');
}


    /**
     * Delete a ticket (IT Personnel only)
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully!');
    }
}
