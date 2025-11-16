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
     * ✅ Show the logged-in user's profile with activity logs and ticket stats.
     */
    public function profile(Request $request): View
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // ✅ Ticket statistics (only tickets assigned to this user)
        $ticketStats = [
            'total' => Ticket::where('it_personnel_id', $user->id)->count(),
            'pending' => Ticket::where('it_personnel_id', $user->id)
                ->where('status', 'Pending')->count(),
            'in_progress' => Ticket::where('it_personnel_id', $user->id)
                ->where('status', 'In Progress')->count(),
            'resolved' => Ticket::where('it_personnel_id', $user->id)
                ->whereIn('status', ['Resolved', 'Closed'])->count(),
        ];

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
            ->when($request->search, fn($q, $v) => $q->where('action', 'like', "%$v%"))
            ->orderByRaw('COALESCE(performed_at, created_at) DESC')
            ->paginate($request->get('per_page', 10));

        return view('profile.profile', [
            'user' => $user,
            'logs' => $logs,
            'ticketStats' => $ticketStats,
        ]);
    }

    /**
     * ✅ Log a ticket assignment activity.
     */
    public function logAssignment(int $ticketId, Request $request): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

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
        $user = Auth::user();

        if (!$user) {
            return;
        }

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

    /**
     * ✅ Show the profile edit form.
     */
    public function edit(): View
    {
        $user = Auth::user();

        return view('profile.edit', ['user' => $user]);
    }

    /**
     * ✅ Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        // 🖼️ Handle image upload
        if ($request->hasFile('profile_picture')) {
            $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validated['profile_picture'] = $imagePath;
        }

        //$user->update($validated);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile updated successfully!');
    }
}
