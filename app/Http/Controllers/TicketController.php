<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{   
    // Show ticket creation form
    public function create()
    {
        $pendingTickets    = Ticket::where('status', 'pending')->latest()->get();
        $inProgressTickets = Ticket::where('status', 'in_progress')->latest('updated_at')->get();
        $resolvedTickets   = Ticket::where('status', 'resolved')->latest('updated_at')->get();

        return view('tickets.create', compact(
            'pendingTickets',
            'inProgressTickets',
            'resolvedTickets'
        ));
    }

    // Store new ticket
    public function store(Request $request)
    {
        $request->validate([
            'ticket_number'     => 'required|string|max:50|unique:tickets,ticket_number',
            'agent_name'        => 'required|string|max:255',
            'agent_email'       => 'required|email|max:255',
            'team_leader_name'  => 'required|string|max:255',
            'component'         => 'required|string|max:255',
            'issue_description' => 'required|string',
            'it_personnel_name' => 'nullable|string|max:255',
        ]);

        Ticket::create($request->only([
            'ticket_number',
            'agent_name',
            'agent_email',
            'team_leader_name',
            'component',
            'issue_description',
            'it_personnel_name',
        ]));

        return redirect()->route('tickets.create')->with('success', 'Ticket submitted successfully.');
    }

    // Show dashboard
    public function index(Request $request) 
    {
        $status = $request->get('status');

        $query = Ticket::query();

        if ($status) {
            $query->where('status', $status);
        }

        $tickets = $query->latest()->get();

        return view('dashboard', [
            'tickets'          => $tickets,
            'pendingCount'     => Ticket::where('status', 'pending')->count(),
            'inProgressCount'  => Ticket::where('status', 'in_progress')->count(),
            'resolvedCount'    => Ticket::where('status', 'resolved')->count(),
        ]);
    }

    // Update ticket (status or IT personnel assignment)
    public function update(Request $request, $id)
    {
        $request->validate([
            'status'             => 'required|string',
            'it_personnel_name'  => 'nullable|string|max:255',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status'            => $request->status,
            'it_personnel_name' => $request->it_personnel_name,
        ]);

        return redirect()->route('dashboard', ['status' => $request->status])
                         ->with('success', 'Ticket updated successfully!');
    }
}
