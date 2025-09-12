<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Agent;
use App\Models\TeamLeader;
use App\Models\Component;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Show the ticket creation form.
     */
    public function create()
    {
        return view('tickets.create', [
            'pendingTickets'    => Ticket::where('status', 'pending')->latest()->get(),
            'inProgressTickets' => Ticket::where('status', 'in_progress')->latest('updated_at')->get(),
            'resolvedTickets'   => Ticket::where('status', 'resolved')->latest('updated_at')->get(),
            'agent'             => Agent::first(),
            'teamLeader'        => TeamLeader::first(),
            'component'         => Component::first(),
        ]);
    }   

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_number'      => 'required|string|max:255|unique:tickets,ticket_number',
            'agent_name'         => 'required|string|max:255',
            'agent_email'        => 'required|email|max:255',
            'team_leader_name'   => 'required|string|max:255',
            'team_leader_email'  => 'required|email|max:255',
            'component_name'     => 'required|string|max:255',
            'issue_description'  => 'required|string',
        ]);

        // 🔹 Ensure Agent exists or create
        $agent = Agent::firstOrCreate(
            ['email' => $validated['agent_email']],
            ['name' => $validated['agent_name']]
        );

        // 🔹 Ensure Team Leader exists or create
        $teamLeader = TeamLeader::firstOrCreate(
            ['email' => $validated['team_leader_email']],
            ['name'  => $validated['team_leader_name']]
        );

        // 🔹 Ensure Component exists or create
        $component = Component::firstOrCreate(
            ['name' => $validated['component_name']]
        );

        // 🔹 Create the Ticket
        Ticket::create([
            'ticket_number'     => $validated['ticket_number'],
            'agent_id'          => $agent->id,
            'team_leader_id'    => $teamLeader->id,
            'component_id'      => $component->id,
            'issue_description' => $validated['issue_description'],
            'status'            => 'pending',
        ]);

        return redirect()
            ->route('tickets.create')
            ->with('success', 'Ticket created successfully!');
    }

    /**
     * Show the ticket dashboard.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Ticket::with(['agent', 'teamLeader', 'component']);

        if ($status) {
            $query->where('status', $status);
        }

        return view('dashboard', [
            'tickets'         => $query->latest()->get(),
            'pendingCount'    => Ticket::where('status', 'pending')->count(),
            'inProgressCount' => Ticket::where('status', 'in_progress')->count(),
            'resolvedCount'   => Ticket::where('status', 'resolved')->count(),
        ]);
    }

    /**
     * Update a ticket status or assign IT personnel.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status'            => 'required|string|in:pending,in_progress,resolved',
            'it_personnel_name' => 'nullable|string|max:255',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update($validated);

        return redirect()
            ->route('dashboard', ['status' => $validated['status']])
            ->with('success', 'Ticket updated successfully!');
    }
}
