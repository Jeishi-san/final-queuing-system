<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    /**
     * Display the logged-in user's profile and their activity logs.
     */
    public function profile(Request $request): View
    {
        $user = $request->user();

        // ✅ Fetch logs for this user and eager-load related ticket + ticket's IT personnel
        $logs = ActivityLog::with(['ticket.itPersonnel'])
            ->where('user_id', $user->id)
            ->orderByDesc('performed_at')   // Prefer actual action time
            ->orderByDesc('created_at')     // Fallback if performed_at is null
            ->paginate(10);

        return view('profile.profile', compact('user', 'logs'));
    }
}