<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Agent;
use App\Models\TeamLeader;
use App\Models\Component;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * 📊 Dashboard for IT personnel
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['agent','teamLeader','itPersonnel']);

        // 🔎 Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', "%{$request->search}%")
                  ->orWhere('issue_description', 'like', "%{$request->search}%");
            });
        }

        // 🔎 Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderByDesc('created_at')->paginate(10);

        return view('dashboard', [
            'tickets' => $tickets,
            'stats'   => $this->getStats(),
        ]);
    }

    /**
     * 📝 Public ticket submission form
     */
    public function create()
    {
        $stats   = $this->getStats();
        $tickets = Ticket::with(['agent','teamLeader','itPersonnel'])
                          ->latest()
                          ->paginate(10);

        return view('tickets.create', compact('stats','tickets'));
    }

    /**
     * 💾 Store new ticket
     */
    public function store(Request $request)
    {
        // ✅ Validate input
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

        // ✅ Use provided ticket number or auto-generate
        $ticketNumber = $request->filled('ticket_number')
            ? $request->ticket_number
            : 'T-' . time();

        // ✅ Resolve related entities
        $agentId  = $this->resolveAgent($request);
        $leaderId = $this->resolveTeamLeader($request);

        // ✅ Create ticket
        $ticket = Ticket::create([
            'ticket_number'     => $ticketNumber,
            'issue_description' => $validated['issue_description'],
            'status'            => 'pending',
            'agent_id'          => $agentId,
            'team_leader_id'    => $leaderId,
            'it_personnel_id'   => null,
        ]);

        // ✅ Attach component
        if ($request->filled('component_name')) {
            $component = Component::firstOrCreate(['name' => $request->component_name]);
            $ticket->components()->attach($component->id, ['quantity' => 1]);
        }

        // ✅ Log activity (null user_id for guest)
        ActivityLog::create([
            'user_id'      => Auth::check() ? Auth::id() : null,   // 👈 prevents null FK error
            'ticket_id'    => $ticket->id,
            'action'       => Auth::check()
                                 ? 'Created ticket'
                                 : 'Ticket submitted by agent',
            'performed_at' => now(),
        ]);

        // ✅ Response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully!',
                'ticket'  => $ticket->load(['agent','teamLeader','itPersonnel','components']),
                'stats'   => $this->getStats(),
            ]);
        }

        return redirect()->route('tickets.create')
                         ->with('success', 'Ticket submitted successfully!');
    }

    /**
     * 🔄 Update ticket
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'status'            => 'nullable|in:pending,in_progress,resolved',
            'it_personnel_id'   => 'nullable|exists:users,id',
            'agent_email'       => 'nullable|email|max:255',
            'agent_name'        => 'nullable|string|max:255',
            'team_leader_email' => 'nullable|email|max:255',
            'team_leader_name'  => 'nullable|string|max:255',
            'component_name'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // ✅ Update related entities
        if ($request->filled('agent_email')) {
            $ticket->agent_id = $this->resolveAgent($request);
        }

        if ($request->filled('team_leader_email')) {
            $ticket->team_leader_id = $this->resolveTeamLeader($request);
        }

        // ✅ Attach/update component
        if ($request->filled('component_name')) {
            $component = Component::firstOrCreate(['name' => $request->component_name]);
            $ticket->components()->syncWithoutDetaching([
                $component->id => ['quantity' => 1],
            ]);
        }

        // ✅ Assign IT personnel
        if ($request->filled('it_personnel_id')) {
            $ticket->it_personnel_id = $request->input('it_personnel_id');
        } elseif (Auth::check() && !$ticket->it_personnel_id) {
            $ticket->it_personnel_id = Auth::id();
        }

        // ✅ Update status
        if ($request->filled('status')) {
            $ticket->status = $validated['status'];
        }

        $ticket->save();

        // ✅ Log update
        ActivityLog::create([
            'user_id'      => Auth::check() ? Auth::id() : null,
            'ticket_id'    => $ticket->id,
            'action'       => "Updated ticket (status: {$ticket->status})",
            'performed_at' => now(),
        ]);

        return $request->expectsJson()
            ? response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully!',
                'ticket'  => $ticket->load(['agent','teamLeader','itPersonnel','components']),
                'stats'   => $this->getStats(),
            ])
            : redirect()->route('dashboard')->with('success', 'Ticket updated successfully.');
    }

    /**
     * 🪟 Assign modal
     */
    public function modalAssign(Ticket $ticket)
    {
        $users = User::all(); // optionally filter by role
        return view('tickets.assign', compact('ticket', 'users'));
    }

    /**
     * 📈 Panels for AJAX
     */
    public function panels(Request $request)
    {
        $stats = $this->getStats();
        $tickets = Ticket::with(['agent','teamLeader','itPersonnel'])
                          ->latest()
                          ->paginate(10);

        return $request->expectsJson()
            ? response()->json([
                'success' => true,
                'stats'   => $stats,
                'tickets' => $tickets,
            ])
            : view('tickets.panels', compact('stats', 'tickets'));
    }

    /**
     * 📊 Dashboard stats
     */
    private function getStats(): array
    {
        return [
            ['label'=>'Pending',     'count'=>Ticket::where('status','pending')->count(),     'color'=>'yellow','icon'=>'⏳'],
            ['label'=>'In Progress', 'count'=>Ticket::where('status','in_progress')->count(), 'color'=>'blue','icon'=>'🔄'],
            ['label'=>'Resolved',    'count'=>Ticket::where('status','resolved')->count(),    'color'=>'green','icon'=>'✅'],
        ];
    }

    /* ===== Helper methods ===== */

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
}
