<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Show the dashboard with tickets list + stats.
     */
// DashboardController or wherever you load tickets
public function index(Request $request)
{
    $query = Ticket::query();

    if ($request->filled('search')) {
        $query->where('ticket_number', 'like', "%{$request->search}%")
            ->orWhere('issue_description', 'like', "%{$request->search}%");
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $tickets = $query->orderBy('created_at', 'desc')->paginate(10); // ✅ 10 per page

    return view('dashboard', [
        'tickets' => $tickets,
        'stats'   => $this->getStats(), // ✅ unified format
    ]);
}



    /**
     * Show create ticket form (Landing Page).
     */
    public function create()
    {
        $stats   = $this->getStats();
        $tickets = Ticket::latest()->get();

        return view('tickets.create', compact('stats', 'tickets'));
    }

    /**
     * Store a new ticket (supports optional manual ticket number).
     */
public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'ticket_number'      => 'required|string|unique:tickets,ticket_number',
            'issue_description'  => 'required|string',
            'agent_name'         => 'nullable|string|max:255',
            'agent_email'        => 'nullable|email|max:255',
            'team_leader_name'   => 'nullable|string|max:255',
            'team_leader_email'  => 'nullable|email|max:255',
            'component_name'     => 'nullable|string|max:255',
            'it_personnel_name'  => 'nullable|string|max:255',
        ]);
    } catch (ValidationException $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        throw $e; // default Laravel error for non-JSON requests
    }

    $ticket = Ticket::create($validated);

    // ✅ JSON response (for API/fetch calls)
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully',
            'ticket'  => $ticket,
            'stats'   => $this->getStats(),
        ]);
    }

    // ✅ Default redirect (web form submission)
    return redirect()
        ->route('dashboard')
        ->with('success', 'Ticket created successfully.');
}

    /**
     * Update ticket (works for both AJAX and normal requests).
     */
public function update(Request $request, Ticket $ticket)
{
    try {
        $validated = $request->validate([
            'ticket_number'      => 'nullable|string|unique:tickets,ticket_number,' . $ticket->id,
            'issue_description'  => 'sometimes|string',
            'status'             => 'required|in:pending,in_progress,resolved',
            'agent_name'         => 'nullable|string|max:255',
            'agent_email'        => 'nullable|email|max:255',
            'team_leader_name'   => 'nullable|string|max:255',
            'team_leader_email'  => 'nullable|email|max:255',
            'component_name'     => 'nullable|string|max:255',
            'it_personnel_name'  => 'nullable|string|max:255',
        ]);
    } catch (ValidationException $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        throw $e; // fallback to default Laravel behavior
    }

    $ticket->update($validated);

    // ✅ JSON response (when Accept: application/json present)
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'ticket'  => [
                'id'                => $ticket->id,
                'ticket_number'     => $ticket->ticket_number,
                'issue_description' => $ticket->issue_description,
                'status'            => $ticket->status, // keep raw, let frontend format
                'agent_name'        => $ticket->agent_name,
                'team_leader_name'  => $ticket->team_leader_name,
                'it_personnel_name' => $ticket->it_personnel_name,
            ],
            'stats' => $this->getStats(),
        ]);
    }

    // ✅ Default web redirect (non-AJAX requests)
    return redirect()
        ->route('dashboard')
        ->with('success', 'Ticket updated successfully.');
}



    /**
     * Panels partial (AJAX refresh for stats + tickets).
     * ✅ Agents (unauthenticated) → stats only
     * ✅ IT Personnel (authenticated) → stats + ticket list
     */
public function panels(Request $request)
{
    $stats = $this->getStats();

    // If frontend expects JSON → return JSON (used in fetch refresh)
    if ($request->expectsJson()) {
        $tickets = Auth::check()
            ? Ticket::latest()->paginate(10) // keep pagination consistent
            : null;

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'tickets' => $tickets,
        ]);
    }

    // Fallback for non-AJAX (direct Blade render)
    if (Auth::check()) {
        $tickets = Ticket::latest()->paginate(10);
        return view('tickets.panels', compact('stats', 'tickets'));
    }

    return view('tickets.stats', compact('stats'));
}
    
    /**
     * Reusable stats function (returns decorated + raw counts).
     */
    private function getStats()
    {
        return [
            [
                'label' => 'Pending',
                'count' => Ticket::where('status', 'pending')->count(),
                'color' => 'yellow',
                'icon'  => '⏳',
            ],
            [
                'label' => 'In Progress',
                'count' => Ticket::where('status', 'in_progress')->count(),
                'color' => 'blue',
                'icon'  => '🔄',
            ],
            [
                'label' => 'Resolved',
                'count' => Ticket::where('status', 'resolved')->count(),
                'color' => 'green',
                'icon'  => '✅',
            ],
        ];
    }
}
