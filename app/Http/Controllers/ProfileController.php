<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use App\Models\Ticket;

class ProfileController extends Controller
{
    /**
     * Show the logged-in user's profile with their activity logs.
     */
    public function profile(Request $request): View
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $itPersonnelRelation = 'itPersonnel';

        // ✅ Fetch all logs related to this user
        $logs = ActivityLog::query()
            ->with([
                'ticket' => function ($query) use ($itPersonnelRelation) {
                    $query->select([
                        'id',
                        'ticket_number',
                        'issue_description',
                        'status',
                        'it_personnel_id'
                    ])->with([
                        $itPersonnelRelation => function ($query) {
                            $query->select('id', 'name', 'email');
                        }
                    ]);
                },
            ])
            ->where('user_id', $user->id)
            ->orderByRaw('COALESCE(performed_at, created_at) DESC')
            ->paginate($request->get('per_page', 10));

        return view('profile.profile', [
            'user' => $user,
            'logs' => $logs,
        ]);
    }

    /**
     * ✅ Log a ticket assignment activity.
     */
    public function logAssignment(int $ticketId, Request $request): void
    {
        $user = $request->user();

        if (!$user) return;

        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            ActivityLog::create([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'action' => "Assigned ticket #{$ticket->ticket_number} to IT personnel",
                'performed_at' => now(),
            ]);
        }
    }

    /**
     * ✅ Log a ticket update activity.
     */
    public function logUpdate(int $ticketId, Request $request): void
    {
        $user = $request->user();

        if (!$user) return;

        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            ActivityLog::create([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'action' => "Updated ticket #{$ticket->ticket_number} (status: {$ticket->status})",
                'performed_at' => now(),
            ]);
        }
    }
}
