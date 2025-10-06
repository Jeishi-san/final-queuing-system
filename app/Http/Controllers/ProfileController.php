<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    /**
     * Show the logged-in user's profile with their activity logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function profile(Request $request): View
    {
        // ✅ Get the currently logged-in user
        $user = $request->user();

        // ✅ Fetch activity logs for this user
        // - Eager-load related ticket and ticket’s IT personnel
        // - Order primarily by performed_at (when action happened)
        // - Fallback to created_at for older logs
        // - Paginate for better performance
        $logs = ActivityLog::query()
            ->with([
                // 🔥 UPDATED to match your actual column names
                'ticket:id,ticket_number,issue_description,status,it_personnel_id',
                'ticket.itPersonnel:id,name,email',
            ])
            ->where('user_id', $user->id)
            ->orderByDesc('performed_at')
            ->orderByDesc('created_at')
            ->paginate(10);

        // ✅ Return the profile view
        return view('profile.profile', [
            'user' => $user,
            'logs' => $logs,
        ]);
    }
}
