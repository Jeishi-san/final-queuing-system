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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Notifications\TicketUpdatedNotification;

class TicketController extends Controller
{
    /**
     * 🎯 Dashboard / Main Ticket View
     */
    public function dashboard(Request $request)
    {
        $currentUser = Auth::user();
        
        $query = Ticket::with(['agent', 'teamLeader', 'itPersonnel', 'component'])
            ->orderBy('created_at', 'desc');

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

        $assignedTicketsCount = Ticket::where('it_personnel_id', $currentUser->id)->count();

        return view('dashboard', compact('tickets', 'stats', 'users', 'assignedTicketsCount', 'currentUser'));
    }

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
                      ->orWhereHas('agent', fn($a) => $a->where('email', 'like', "%{$search}%"))
                      ->orWhereHas('teamLeader', fn($a) => $a->where('email', 'like', "%{$search}%"))
                      ->orWhereHas('itPersonnel', fn($a) => $a->where('email', 'like', "%{$search}%"));
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('it_personnel_id')) {
                $query->where('it_personnel_id', $request->input('it_personnel_id'));
            }

            $tickets = $query->latest()->paginate(10)->withQueryString();
            
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
    public function ticketsStats(Request $request)
    {
        try {
            $stats = $this->getStats();
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
     * 🎯 Assign Ticket Modal
     */
    public function assign(Ticket $ticket)
    {
        try {
            $users = User::all();
            
            if (request()->ajax()) {
                return view('tickets.assign', compact('ticket', 'users'));
            }
            
            return redirect()->route('dashboard')->with('info', 'Please use the assign buttons in the dashboard to assign tickets.');
            
        } catch (\Exception $e) {
            Log::error('Error loading assign modal', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load assignment form: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('dashboard')->with('error', 'Failed to load assignment form.');
        }
    }

    /**
     * ⚙️ Update Ticket (Assign / Status / IT Personnel)
     */
    public function update(Request $request, Ticket $ticket)
    {
        Log::debug('🎯 === TICKET UPDATE PROCESS STARTED ===', [
            'ticket_id' => $ticket->id,
            'current_user_id' => Auth::id(),
            'request_data' => $request->all(),
            'current_ticket_state' => [
                'status' => $ticket->status,
                'it_personnel_id' => $ticket->it_personnel_id,
                'component_id' => $ticket->component_id
            ]
        ]);

        $notificationCount = 0;
        
        try {
            $validated = $request->validate([
                'it_personnel_id' => 'nullable|exists:users,id',
                'status' => 'required|string|in:pending,in_progress,resolved,overdue,cancelled',
                'component_id' => 'nullable|exists:components,id',
            ]);

            if (!isset($validated['component_id']) || $validated['component_id'] === null) {
                $validated['component_id'] = $ticket->component_id;
            }

            // Store old values (ORIGINAL VALUES - not formatted)
            $oldStatus = $ticket->status;
            $oldItPersonnelId = $ticket->it_personnel_id;
            $oldComponentId = $ticket->component_id;

            // Update ticket
            $ticket->update($validated);
            $ticket->refresh();

            $ticket->load(['itPersonnel', 'agent', 'teamLeader']);

        // ✅ FIXED: Track changes with ORIGINAL VALUES (not formatted)
        $changes = [];
        
        $statusChanged = $oldStatus !== $ticket->status;
        $itPersonnelChanged = $oldItPersonnelId != $ticket->it_personnel_id;
        $componentChanged = $oldComponentId != $ticket->component_id;

        Log::debug('🔍 Change detection results', [
            'status_changed' => $statusChanged,
            'it_personnel_changed' => $itPersonnelChanged,
            'component_changed' => $componentChanged,
            'old_it_personnel' => $oldItPersonnelId,
            'new_it_personnel' => $ticket->it_personnel_id,
            'old_status' => $oldStatus,
            'new_status' => $ticket->status,
        ]);

        if ($statusChanged) {
            $changes['status'] = [
                'from' => $oldStatus, // ✅ ORIGINAL value
                'to' => $ticket->status // ✅ ORIGINAL value
            ];
        }

        if ($itPersonnelChanged) {
            $changes['it_personnel_id'] = [
                'from' => $oldItPersonnelId, // ✅ ORIGINAL ID
                'to' => $ticket->it_personnel_id // ✅ ORIGINAL ID
            ];
        }

        if ($componentChanged) {
            $changes['component_id'] = [
                'from' => $oldComponentId, // ✅ ORIGINAL ID
                'to' => $ticket->component_id // ✅ ORIGINAL ID
            ];
        }

        // ✅ DEBUG: Log final changes
        Log::info('📝 Ticket Update - Final Changes', [
            'ticket_id' => $ticket->id,
            'changes_count' => count($changes),
            'changes' => $changes,
            'has_changes' => !empty($changes)
        ]);

            // ✅ Record activity log
            $this->logActivity($ticket, $oldStatus, $oldItPersonnelId);

            // ✅ FIXED: Notification logic with better debugging
            if (!empty($changes)) {
                Log::info('🚀 Starting notification process - changes confirmed', [
                    'ticket_id' => $ticket->id,
                    'changes_count' => count($changes)
                ]);
                
                $recipients = collect();
                $currentUser = Auth::user();

                // Always notify the assigned IT personnel if there are changes
                if ($ticket->it_personnel_id) {
                    $itPersonnel = User::find($ticket->it_personnel_id);
                    if ($itPersonnel && method_exists($itPersonnel, 'notify')) {
                        $recipients->push($itPersonnel);
                        Log::info('👨‍💻 Added assigned IT personnel to recipients', [
                            'it_personnel_id' => $itPersonnel->id,
                            'it_personnel_name' => $itPersonnel->name,
                            'reason' => 'assigned_to_ticket'
                        ]);
                    } else {
                        Log::warning('❌ IT personnel not found or cannot notify', [
                            'it_personnel_id' => $ticket->it_personnel_id,
                            'user_exists' => !is_null($itPersonnel),
                            'has_notify_method' => $itPersonnel ? method_exists($itPersonnel, 'notify') : false
                        ]);
                    }
                }

                // Also notify current user for testing (temporary)
                if ($currentUser && method_exists($currentUser, 'notify')) {
                    if (!$recipients->contains('id', $currentUser->id)) {
                        $recipients->push($currentUser);
                        Log::info('👤 Added current user to recipients for testing', [
                            'current_user_id' => $currentUser->id,
                            'current_user_name' => $currentUser->name
                        ]);
                    }
                }

                // ✅ DEBUG: Final recipients list
                Log::info('📋 Final recipients list', [
                    'total_recipients' => $recipients->count(),
                    'recipient_ids' => $recipients->pluck('id')->toArray(),
                    'recipient_names' => $recipients->pluck('name')->toArray(),
                    'current_user_in_recipients' => $recipients->contains('id', $currentUser->id)
                ]);

                // Send notifications
                if ($recipients->count() > 0) {
                    foreach ($recipients->unique('id') as $user) {
                        try {
                            // ✅ FIXED: Pass the current user as the third parameter
                            $user->notify(new TicketUpdatedNotification($ticket, $changes, $currentUser));
                            $notificationCount++;
                            
                            Log::info('✅ Notification sent successfully', [
                                'user_id' => $user->id,
                                'user_name' => $user->name,
                                'notification_number' => $notificationCount,
                                'changes_sent' => $changes
                            ]);

                            // ✅ DEBUG: Check if notification was stored in database
                            $userNotificationCount = $user->notifications()->count();
                            Log::debug('📊 Notification storage check', [
                                'user_id' => $user->id,
                                'total_notifications_after_send' => $userNotificationCount
                            ]);

                        } catch (\Exception $e) {
                            Log::error("❌ Failed to send notification to user {$user->id}: " . $e->getMessage(), [
                                'user_id' => $user->id,
                                'user_name' => $user->name,
                                'error' => $e->getMessage(),
                                'error_trace' => $e->getTraceAsString()
                            ]);
                        }
                    }

                    Log::info('🎉 Notification process completed', [
                        'ticket_id' => $ticket->id,
                        'notifications_sent' => $notificationCount,
                        'total_recipients' => $recipients->unique('id')->count()
                    ]);

                    // ✅ FINAL DEBUG: Check database for all notifications
                    $allNotificationsCount = \Illuminate\Notifications\DatabaseNotification::count();
                    Log::debug('🗃️ Final database notification count', [
                        'total_notifications_in_system' => $allNotificationsCount,
                        'expected_new_notifications' => $notificationCount
                    ]);

                } else {
                    Log::warning('⏭️ No recipients for notifications', [
                        'ticket_id' => $ticket->id,
                        'reason' => 'No valid recipients found',
                        'it_personnel_id' => $ticket->it_personnel_id,
                        'changes_present' => !empty($changes)
                    ]);
                }

            } else {
                Log::info('⏭️ No changes detected, skipping notifications', [
                    'ticket_id' => $ticket->id,
                    'reason' => 'No actual changes between old and new values'
                ]);
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket updated successfully!',
                    'ticket' => $ticket->load(['agent', 'teamLeader', 'itPersonnel', 'component']),
                    'changes' => $changes,
                    'notifications_sent' => $notificationCount
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Ticket updated successfully!');

        } catch (\Exception $e) {
            Log::error('💥 Ticket update failed: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update ticket: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update ticket. Please try again.');
        }
    }

    /**
     * 🧾 Format change values for notifications
     * NOTE: This method is now only used for display purposes, not for notification data
     */
    private function formatChangeValue(string $field, $val): string
    {
        if (is_null($val)) {
            return 'Not Set';
        }

        return match ($field) {
            'it_personnel_id' => $this->getUserName($val) ?? 'Not Assigned',
            'status' => ucfirst(str_replace('_', ' ', (string) $val)),
            'component_id' => $this->getComponentName($val) ?? 'Not Specified',
            default => (string) $val,
        };
    }

    private function getUserName(?int $userId): ?string
    {
        if (!$userId) return null;
        return User::find($userId)?->name;
    }

    private function getComponentName(?int $componentId): ?string
    {
        if (!$componentId) return null;
        return Component::find($componentId)?->name;
    }

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

    public function show(Ticket $ticket)
    {
        return redirect()->route('dashboard')->with([
            'highlight_ticket' => $ticket->id,
            'show_ticket_modal' => true
        ]);
    }

    private function logActivity($ticket, string $oldStatus, ?int $oldPersonnel)
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