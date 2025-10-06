<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Agent;
use App\Models\TeamLeader;
use App\Models\Component;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * 📊 IT Dashboard
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['agent', 'teamLeader', 'itPersonnel', 'component']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', "%{$request->search}%")
                  ->orWhere('issue_description', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(10);
        $stats   = $this->getStats();

        return view('dashboard', compact('tickets', 'stats'));
    }

    /**
     * 📝 Public ticket submission page
     */
    public function create()
    {
        $stats   = $this->getStats();
        $tickets = Ticket::with(['agent', 'teamLeader', 'itPersonnel', 'component'])
            ->latest()
            ->paginate(10);

        return view('tickets.create', compact('stats', 'tickets'));
    }

    /**
     * 💾 Store a new ticket
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_number'     => 'nullable|string|max:50|unique:tickets,ticket_number',
            'issue_description' => 'required|string',
            'agent_name'        => 'required|string|max:255',
            'agent_email'       => 'required|email|max:255',
            'team_leader_name'  => 'nullable|string|max:255',
            'team_leader_email' => 'nullable|email|max:255',
            'component_name'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $ticketNo  = $validated['ticket_number'] ?? 'T-' . time();

        $agentId     = $this->resolveAgent($request);
        $leaderId    = $this->resolveTeamLeader($request);
        $componentId = $this->resolveComponent($request);

        $ticket = Ticket::create([
            'ticket_number'     => $ticketNo,
            'issue_description' => $validated['issue_description'],
            'status'            => 'pending',
            'agent_id'          => $agentId,
            'team_leader_id'    => $leaderId,
            'it_personnel_id'   => null,
            'component_id'      => $componentId,
        ]);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'ticket_id'    => $ticket->id,
            'action'       => Auth::check() ? 'Created ticket' : 'Ticket submitted by agent',
            'performed_at' => now(),
        ]);

        return $request->expectsJson()
            ? response()->json([
                'success' => true,
                'message' => 'Ticket submitted successfully!',
                'stats'   => $this->getStats(),
            ])
            : redirect()->route('tickets.create')->with('success', 'Ticket submitted successfully!');
    }

    /**
     * 🔄 Update ticket (assignment & status)
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'status'            => 'nullable|in:pending,in_progress,resolved',
            'it_personnel_id'   => 'nullable|exists:users,id',
            'agent_name'        => 'nullable|string|max:255',
            'agent_email'       => 'nullable|email|max:255',
            'team_leader_name'  => 'nullable|string|max:255',
            'team_leader_email' => 'nullable|email|max:255',
            'component_name'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        if ($request->filled('agent_email')) {
            $ticket->agent_id = $this->resolveAgent($request);
        }

        if ($request->filled('team_leader_email')) {
            $ticket->team_leader_id = $this->resolveTeamLeader($request);
        }

        if ($request->filled('component_name')) {
            $ticket->component_id = $this->resolveComponent($request);
        }

        if ($request->filled('it_personnel_id')) {
            $ticket->it_personnel_id = $request->it_personnel_id;
        } elseif (Auth::check() && !$ticket->it_personnel_id) {
            $ticket->it_personnel_id = Auth::id();
        }

        if ($request->filled('status')) {
            $ticket->status = $request->status;
        }

        $ticket->save();

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'ticket_id'    => $ticket->id,
            'action'       => "Updated ticket (status: {$ticket->status})",
            'performed_at' => now(),
        ]);

        // 🔑 Always return JSON to prevent full-page redirect
        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully!',
            'stats'   => $this->getStats(),
        ]);
    }

    /**
     * 🪟 Assign / Edit modal
     */
    public function modalAssign(Ticket $ticket)
    {
        $ticket->load(['agent', 'teamLeader', 'itPersonnel', 'component']);
        $users = User::all();

        return view('tickets.assign', compact('ticket', 'users'));
    }

    /**
     * 📈 AJAX endpoint for refreshing dashboard
     */
    public function panels()
    {
        $stats   = $this->getStats();
        $tickets = Ticket::with(['agent', 'teamLeader', 'itPersonnel', 'component'])
            ->latest()
            ->paginate(10);

        return view('tickets.panels', compact('stats', 'tickets'));
    }

    /**
     * 📊 Dashboard statistics
     */
    private function getStats(): array
    {
        return [
            ['label' => 'Pending',     'count' => Ticket::where('status', 'pending')->count(),     'color' => 'yellow', 'icon' => '⏳'],
            ['label' => 'In Progress', 'count' => Ticket::where('status', 'in_progress')->count(), 'color' => 'blue',   'icon' => '🔄'],
            ['label' => 'Resolved',    'count' => Ticket::where('status', 'resolved')->count(),    'color' => 'green',  'icon' => '✅'],
        ];
    }

    /* ===== Relationship helpers ===== */

    private function resolveAgent(Request $request): ?int
    {
        if (!$request->filled('agent_email')) return null;

        $agent = Agent::firstOrCreate(
            ['email' => $request->agent_email],
            ['name'  => $request->agent_name ?? 'Unknown Agent']
        );

        return $agent->id;
    }

    private function resolveTeamLeader(Request $request): ?int
    {
        if (!$request->filled('team_leader_email')) return null;

        $leader = TeamLeader::firstOrCreate(
            ['email' => $request->team_leader_email],
            ['name'  => $request->team_leader_name ?? 'Unknown Leader']
        );

        return $leader->id;
    }

    private function resolveComponent(Request $request): ?int
    {
        if (!$request->filled('component_name')) return null;

        $component = Component::firstOrCreate(['name' => $request->component_name]);

        return $component->id;
    }
}
