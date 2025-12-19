<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\Queue;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    // List ticket items by filters
 // app/Http/Controllers/TicketController.php (index method)

    public function index(Request $request)
    {
        $query = Ticket::query();
        $user = Auth::user();

        // 🔑 SUPER ADMIN FEATURE: Bypasses role-based data filtering if logged in as super_admin.
        if ($user && $user->role !== 'super_admin') {
            // Apply your specific filtering for non-Super Admins (IT Staff, Agents) here.
            // If your IT staff/Agents should ONLY see certain tickets, add that logic here.

            // Example: Only show tickets not resolved/cancelled to non-Super Admins
            // $query->whereNotIn('status', ['resolved', 'cancelled']);
        }

        // Filter by ticket_number
        if ($request->ticket_number) {
            $query->where('ticket_number', 'LIKE', '%'.$request->ticket_number.'%');
        }

        // Filter by holder_name
        if ($request->holder_name) {
            $query->where('holder_name', 'LIKE', '%'.$request->holder_name.'%');
        }

        // Filter by holder_email
        if ($request->holder_email) {
            $query->where('holder_email', 'LIKE', '%'.$request->holder_email.'%');
        }

        // Filter by issue
        if ($request->issue) {
            $query->where('issue', 'LIKE', '%'.$request->issue.'%');
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->start_date) {
            $query->whereDate('updated_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('updated_at', '<=', $request->end_date);
        }

        // Filter by next in line queued tickets
        if ($request->nextQueued) {
            $query->where('status', 'queued');
            return $query->orderBy('created_at', 'asc')->take(5)->get();
        }

        // Default return
        return $query->orderBy('created_at', 'asc')->orderBy('status')->get();
    }

    // Create a new ticket (Guest or User)
    public function store(Request $request)
    {
        // 1. Validation: Allow nullable fields so the empty form passes validation
        $validated = $request->validate([
            'holder_name'   => 'nullable|string',
            'holder_email'  => 'nullable|email',
            'ticket_number' => [
                'required',
                'string',
                Rule::unique('tickets', 'ticket_number'),
            ],
            'issue'         => 'nullable|string'
        ]);

        $currentUser = auth('web')->user();

        // 2. Prepare Data & Auto-Fill Logic
        $data = $validated;

        // Auto-fill Name: Use Agent's name if logged in, otherwise "Walk-in User"
        if (empty($data['holder_name'])) {
            $data['holder_name'] = $currentUser ? $currentUser->name : 'Walk-in User';
        }

        // Auto-fill Email: Use Agent's email if logged in, otherwise a dummy email
        // This satisfies the database "NOT NULL" requirement
        if (empty($data['holder_email'])) {
            $data['holder_email'] = $currentUser ? $currentUser->email : 'walk-in@system.local';
        }

        $data['status'] = 'pending approval'; // Default status

        // 3. Create Ticket
        $ticket = Ticket::create($data);

        $updaterName = $currentUser ? $currentUser->name : 'Guest';

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $currentUser ? $currentUser->id : null,
            'action'    => 'Ticket created by ' . $updaterName,
        ]);

        // Notify Admins
        $this->notifyAdmins($ticket, ['is_new' => true], $currentUser);

        return response()->json($ticket, 201);
    }

    // Show a single ticket (including soft-deleted)
    public function show($id)
    {
        $ticket = Ticket::withTrashed()->findOrFail($id);
        return response()->json($ticket);
    }

    // Update an existing ticket
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'holder_name' => 'sometimes|string',
            'holder_email' => 'sometimes|email',
            'ticket_number' => 'sometimes|string|unique:tickets,ticket_number,' . $ticket->id,
            'issue' => 'sometimes|string',
            'status' => 'sometimes|in:pending approval,queued,in progress,on hold,resolved,cancelled',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $originalAssignee = $ticket->assigned_to;

        $ticket->update($validated);

        // Notify if assignment changed
        if (isset($validated['assigned_to']) && $originalAssignee != $validated['assigned_to']) {
            $changes = [
                'it_personnel_id' => ['from' => $originalAssignee, 'to' => $ticket->assigned_to]
            ];
            $this->notifyRelevantUsers($ticket, $changes, auth('web')->user());
        }

        return response()->json($ticket);
    }

    // Delete a ticket
    public function destroy(Ticket $ticket)
    {
        // Log before deleting so we have the ID history
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth('web')->id(),
            'action'    => 'Ticket has been deleted'
        ]);

        $this->logActivity(
            auth('web')->id(),
            "Deleted ticket #{$ticket->ticket_number}"
        );

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted']);
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            // Ensure user is authenticated for web route updates
            $currentUser = auth('web')->user();
            if (!$currentUser) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $validated = $request->validate([
                'status' => 'required|string',
            ]);

            $ticket = Ticket::findOrFail($id);
            $oldStatus = $ticket->status;

            $ticketLog_message = "";

            // Only process if status is actually different
            if ($ticket->status != $validated['status']) {

                if ($validated['status'] === 'queued') {
                    $this->addTicketToQueue($ticket);
                    $ticket->assigned_to = $currentUser->id;
                    $ticketLog_message = "Ticket validated and added to queue";
                } elseif ($validated['status'] === 'in progress') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket is being processed";
                } elseif ($validated['status'] === 'resolved') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been resolved";
                } elseif ($validated['status'] === 'on hold') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been put on hold";
                } elseif ($validated['status'] === 'cancelled') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been cancelled";
                } elseif ($validated['status'] === 'dequeued') {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket has been dequeued";
                } else {
                    $this->updateAssignedUser($ticket);
                    $ticketLog_message = "Ticket status changed to " . $validated['status'];
                }

                TicketLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $currentUser ? $currentUser->id : null,
                    'action'    => $ticketLog_message
                ]);

                // Log user activity
                $this->logActivity(
                    $currentUser ? $currentUser->id : null,
                    "Updated status of ticket #{$ticket->ticket_number} to {$validated['status']}"
                );

                // Save status
                $ticket->status = $validated['status'];
                $ticket->save();

                // Notification logic
                $changes = [
                    'status' => ['from' => $oldStatus, 'to' => $ticket->status]
                ];

                $this->notifyAdmins($ticket, $changes, $currentUser);

                return response()->json(['message' => 'Ticket status updated successfully']);
            }

            return response()->json(['message' => 'No changes made to ticket status']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update ticket: ' . $e->getMessage()], 500);
        }
    }

    // --- Helpers ---

    private function notifyAdmins($ticket, $changes, $updater)
    {
        try {
            $recipients = User::all();
            if ($recipients->count() > 0) {
                Notification::send($recipients, new TicketUpdatedNotification($ticket, $changes, $updater));
                Log::info('Notification sent to ' . $recipients->count() . ' users.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify users', ['error' => $e->getMessage()]);
        }
    }

    private function notifyRelevantUsers($ticket, $changes, $updater)
    {
        $this->notifyAdmins($ticket, $changes, $updater);

        if ($ticket->assigned_to) {
            $assignee = User::find($ticket->assigned_to);
            if ($assignee && (!$updater || $assignee->id !== $updater->id)) {
                $assignee->notify(new TicketUpdatedNotification($ticket, $changes, $updater));
            }
        }
    }

    public function addTicketToQueue(Ticket $ticket)
    {
        if (Queue::where('ticket_id', $ticket->id)->exists()) {
            return response()->json(['message' => 'Queue already exists']);
        }

        // Generate queue number using numeric MAX to avoid string lexicographic issues
        // Example: MAX('10','9') as strings returns '9'; casting fixes this.
        $latestQueue = DB::table('queues')->max(DB::raw('CAST(queue_number AS UNSIGNED)'));
        $nextQueueNum = ($latestQueue ?? 0) + 1;

        $queue = Queue::create([
            'ticket_id'    => $ticket->id,
            'assigned_to'  => auth('web')->id(),
            'queue_number' => (string) $nextQueueNum,
        ]);

        return $queue;
    }

    private function updateAssignedUser(Ticket $ticket)
    {
        $userId = auth('web')->id();
        if (!$userId) {
            return; // avoid null FK writes
        }

        $queue = Queue::where('ticket_id', $ticket->id)->first();
        if ($queue) {
            $queue->assigned_to = $userId;
            $queue->save();
        }

        $saveTicket = Ticket::where('id', $ticket->id)->first();
        if ($saveTicket) {
            $saveTicket->assigned_to = $userId;
            $saveTicket->save();
        }
    }

    private function logActivity($userId, $action)
    {
        if ($userId) {
            ActivityLog::create([
                'user_id' => $userId,
                'action'  => $action,
            ]);
        }
    }

    public function countQueuedTickets()
    {
        $queued = Ticket::where('status', 'queued')->count();
        $inProgress = Ticket::where('status', 'in progress')->count();
        $resolved = Ticket::where('status', 'resolved')->count();
        return response()->json(['queued_tickets' => $queued + $inProgress, 'waiting' => $queued, 'resolved_tickets' => $resolved]);
    }

    public function filterByStatus($status) { return response()->json(Ticket::where('status', $status)->get()); }

    public function getMySubmittedTickets(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            // Find tickets where the holder_email matches the logged-in user's email
            // We order by latest creation date.
            $myTickets = Ticket::where('holder_email', $user->email)
                                ->orderBy('created_at', 'desc')
                                ->get();

            return response()->json($myTickets);

        } catch (\Exception $e) {
            Log::error('Error fetching agent submitted tickets: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch tickets'], 500);
        }
    }

    /**
     * Return ticket summary for dashboard charts
     */
    public function summary(Request $request)
    {
        $userEmail = $request->query('clientEmail');

        // Count tickets by status
        $rawStatusCounts = Ticket::select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')   // key: status, value: count
            ->toArray();

        // Ensure all statuses exist even if count is 0
        $allStatuses = [
            'pending approval',
            'queued',
            'in progress',
            'on hold',
            'resolved',
            'cancelled',
            'dequeued'
        ];

        $statusCounts = [];
        foreach ($allStatuses as $status) {
            $statusCounts[$status] = $rawStatusCounts[$status] ?? 0;
        }

        // Count tickets by current staff
        $rawMineCounts = Ticket::where('assigned_to', Auth::id())
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')   // key: status, value: count
            ->toArray();

        $mineCounts = [];
        foreach ($allStatuses as $status) {
            $mineCounts[$status] = $rawMineCounts[$status] ?? 0;
        }

        // Count tickets by client email
        $rawClientCounts = Ticket::where('holder_email', $userEmail)
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')   // key: status, value: count
            ->toArray();

        $clientCounts = [];
        foreach ($allStatuses as $status) {
            $clientCounts[$status] = $rawClientCounts[$status] ?? 0;
        }

        foreach ($allStatuses as $status) {
            if (!isset($clientCounts[$status])) {
                $clientCounts[$status] = 0;
            }
        }

        $allStaff = User::where('role', 'it_staff')
            ->select('id', 'name', 'email')
            ->get();

        $rawStaffCounts = Ticket::whereNotNull('assigned_to')
            ->select('assigned_to')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('assigned_to')
            ->pluck('count', 'assigned_to'); // key = staff_id

        $staffCounts = $allStaff->map(function ($user) use ($rawStaffCounts) {
            return [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'count' => $rawStaffCounts[$user->id] ?? 0, // ← zero if none
            ];
        });

        return response()->json([
            'status_counts' => $statusCounts,
            'mine_counts' => $mineCounts,
            'client' => $clientCounts,
            'staff' => $staffCounts,
            'waiting' => Ticket::where('status', 'queued')->count(), // optional for your waiting count
        ]);
    }


}
