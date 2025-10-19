<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Agent;
use App\Models\TeamLeader;
use App\Models\Component;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\TicketSubmitted;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketResolvedOrOverdue;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * 🎯 Dashboard / Main Ticket View
     */
    public function dashboard(Request $request)
    {
        $query = Ticket::with(['agent', 'teamLeader', 'itPersonnel', 'component'])
            ->orderBy('created_at', 'desc');

        // ✅ Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('issue_description', 'like', "%{$request->search}%")
                  ->orWhere('ticket_number', 'like', "%{$request->search}%")
                  ->orWhereHas('agent', fn($a) => $a->where('email', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('it_personnel_id')) {
            $query->where('it_personnel_id', $request->it_personnel_id);
        }

        $tickets = $query->paginate(10);
        $stats = $this->getStats();
        $users = User::all();

        return view('dashboard', compact('tickets', 'stats', 'users'));
    }

    /**
     * 🧾 AJAX: Tickets Table Partial
     */
 /**
 * 🧾 AJAX: Tickets Table Partial
 */
public function ticketsTable(Request $request)
{
    try {
        $query = Ticket::with(['agent', 'teamLeader', 'itPersonnel', 'component']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('issue_description', 'like', "%{$search}%")
                  ->orWhereHas('agent', fn($a) => $a->where('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('it_personnel_id')) {
            $query->where('it_personnel_id', $request->input('it_personnel_id'));
        }

        $tickets = $query->latest()->paginate(10);
        
        // ✅ REMOVE ->render() - just return the view
        return view('tickets.tables', compact('tickets'));

    } catch (\Throwable $e) {
        Log::error('Error loading tickets table', [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
        ]);

        return response('Failed to load tickets table.', 500);
    }
}
    /**
     * 📊 Dashboard Stats Partial (AJAX)
     */
/**
 * 📊 Dashboard Stats Partial (AJAX)
 */
public function ticketsStats(Request $request)
{
    try {
        $stats = $this->getStats();
        // ✅ REMOVE ->render() here too
        return view('tickets.stats', compact('stats'));
    } catch (\Throwable $e) {
        Log::error('Error loading dashboard stats', [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
        ]);

        return response('Failed to load dashboard stats.', 500);
    }
}
    /**
     * 🧩 Stats for Dashboard
     */
    private function getStats(): array
    {
        $counts = Ticket::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $overdue = Ticket::where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subDay())
            ->count();

        return [
            'total' => array_sum($counts),
            'pending' => $counts['pending'] ?? 0,
            'in_progress' => $counts['in_progress'] ?? 0,
            'resolved' => $counts['resolved'] ?? 0,
            'overdue' => $overdue,
        ];
    }

    /**
     * 🎯 Assign Ticket Modal - FIXED METHOD NAME
     */
public function assign(Ticket $ticket)
{
    $users = User::all();
    
    // Return modal HTML for AJAX requests
    if (request()->ajax()) {
        return view('tickets.assign', compact('ticket', 'users'));
    }
    
    // For direct URL access, redirect back to dashboard
    return redirect()->route('dashboard')->with('info', 'Please use the assign buttons in the dashboard to assign tickets.');
}

/**
 * ⚙️ Update Ticket (Assign / Resolve) - FIXED PARAMETER
 */
public function update(Request $request, Ticket $ticket)
{
    $validated = $request->validate([
        'user_id' => 'nullable|exists:users,id', // Changed from it_personnel_id to user_id
        'status' => 'required|string|in:pending,in_progress,resolved',
    ]);

    $oldStatus = $ticket->status;
    $oldPersonnel = $ticket->user_id; // Changed from it_personnel_id

    $ticket->update([
        'user_id' => $validated['user_id'] ?? null, // Changed from it_personnel_id
        'status' => $validated['status'],
        'resolved_at' => $validated['status'] === 'resolved' ? now() : null,
    ]);

    // ✅ Automatically record to activity_logs for profile tracking
    $this->logActivity($ticket, $oldStatus, $oldPersonnel);
    $this->handleTicketUpdateNotifications($ticket);

    if ($request->ajax()) {
        return response()->json([
            'success' => true, 
            'message' => 'Ticket updated successfully!', 
            'ticket' => $ticket
        ]);
    }

    return redirect()->route('dashboard')->with('success', 'Ticket updated successfully!');
}

    /**
     * 🧾 Ticket Creation Page (Guest)
     */
    public function create()
    {
        $stats = $this->getStats();

        $pendingTickets = Ticket::where('status', 'pending')->latest()->get();
        $inProgressTickets = Ticket::where('status', 'in_progress')->latest()->get();
        $resolvedTickets = Ticket::where('status', 'resolved')->latest()->get();

        return view('tickets.create', [
            'stats' => $stats,
            'pendingTickets' => $pendingTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedTickets' => $resolvedTickets,
        ]);
    }

    /**
     * 📨 Store New Ticket
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ticket_number' => 'nullable|string|max:50',
                'agent_name' => 'required|string|max:255',
                'agent_email' => 'nullable|email|max:255',
                'team_leader_name' => 'nullable|string|max:255',
                'team_leader_email' => 'nullable|email|max:255',
                'component_name' => 'required|string|max:255',
                'issue_description' => 'required|string|max:1000',
            ]);

            $ticketNumber = strtoupper(trim($validated['ticket_number'] ?? ''));
            if (empty($ticketNumber)) {
                $ticketNumber = 'T-' . strtoupper(Str::random(8));
            }

            if (Ticket::where('ticket_number', $ticketNumber)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "Ticket number {$ticketNumber} already exists.",
                ], 409);
            }

            $agentEmail = $this->detectRealGmail($validated['agent_email'] ?? null)
                ? $validated['agent_email'] : null;
            $leaderEmail = $this->detectRealGmail($validated['team_leader_email'] ?? null)
                ? $validated['team_leader_email'] : null;

            $agent = $this->resolveAgent($validated['agent_name'], $agentEmail);
            $teamLeader = !empty($validated['team_leader_name'])
                ? $this->resolveTeamLeader($validated['team_leader_name'], $leaderEmail)
                : null;

            $component = $this->resolveComponent($validated['component_name']);

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'agent_id' => $agent->id,
                'team_leader_id' => $teamLeader?->id,
                'component_id' => $component->id,
                'issue_description' => $validated['issue_description'],
                'status' => 'pending',
            ]);

            Notification::send(User::all(), new TicketSubmitted($ticket));

            return response()->json([
                'success' => true,
                'ticket' => $ticket,
                'message' => "Ticket {$ticket->ticket_number} created successfully!",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Ticket submission failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error. Please try again later.',
            ], 500);
        }
    }

    private function logActivity(Ticket $ticket, string $oldStatus, ?int $oldPersonnel)
    {
        $userId = Auth::user()?->id;
        $action = null;

        if ($ticket->status === 'in_progress' && $ticket->it_personnel_id !== $oldPersonnel) {
            $assignedTo = $ticket->itPersonnel?->name ?? 'Unassigned';
            $action = "Ticket {$ticket->ticket_number} assigned to {$assignedTo}.";
        } elseif ($ticket->status === 'resolved') {
            $resolvedBy = $ticket->itPersonnel?->name ?? 'Unknown personnel';
            $action = "Ticket {$ticket->ticket_number} resolved by {$resolvedBy}.";
        }

        if ($action) {
            ActivityLog::create([
                'user_id' => $userId,
                'ticket_id' => $ticket->id,
                'action' => $action,
                'performed_at' => now(),
            ]);

            Log::info("Activity logged: {$action}", [
                'user_id' => $userId,
                'ticket_id' => $ticket->id,
            ]);
        }
    }

    private function handleTicketUpdateNotifications(Ticket $ticket)
    {
        $users = Cache::remember('it_users', now()->addMinutes(2), fn() => User::all());

        if ($ticket->status === 'resolved') {
            Notification::send($users, new TicketResolvedOrOverdue($ticket, 'resolved_team'));
        } elseif ($ticket->status === 'in_progress') {
            Notification::send($users, new TicketAssigned($ticket));
        } elseif ($ticket->status === 'pending' && $ticket->created_at->lt(now()->subDay())) {
            Notification::send($users, new TicketResolvedOrOverdue($ticket, 'overdue_team'));
        }
    }

    private function detectRealGmail(?string $email): bool
    {
        return $email && preg_match('/^[a-z0-9._%+-]+@gmail\.com$/', strtolower(trim($email)));
    }

    private static $cache = ['agent' => [], 'leader' => [], 'component' => []];

    private function resolveAgent(string $name, ?string $email = null)
    {
        $name = trim($name);
        return self::$cache['agent'][$name] ??= Agent::firstOrCreate(
            ['name' => $name],
            ['email' => $this->resolveEmail($email, $name)]
        );
    }

    private function resolveTeamLeader(string $name, ?string $email = null)
    {
        $name = trim($name);
        return self::$cache['leader'][$name] ??= TeamLeader::firstOrCreate(
            ['name' => $name],
            ['email' => $this->resolveEmail($email, $name)]
        );
    }

    private function resolveComponent(string $name)
    {
        $name = trim($name);
        return self::$cache['component'][$name] ??= Component::firstOrCreate(['name' => $name]);
    }

    private function resolveEmail(?string $email, string $name): string
    {
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower(trim($email));
        }

        $slug = strtolower(Str::slug($name, '.'));
        $random = strtolower(Str::random(4));
        return "{$slug}.{$random}@gmail.com";
    }

    public function check(Request $request)
    {
        $ticketNumber = $request->query('ticket_number');
        $exists = Ticket::where('ticket_number', $ticketNumber)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * 🎛️ Ticket Panels (Public Display)
     */
    public function panels()
    {
        $pendingTickets = Ticket::where('status', 'pending')->latest()->get();
        $inProgressTickets = Ticket::where('status', 'in_progress')->latest()->get();
        $resolvedTickets = Ticket::where('status', 'resolved')->latest()->get();

        return view('tickets.panels', compact(
            'pendingTickets', 
            'inProgressTickets', 
            'resolvedTickets'
        ));
    }
}