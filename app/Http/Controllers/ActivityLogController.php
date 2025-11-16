<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    // List all activity logs
    public function index()
    {
        return response()->json(ActivityLog::latest()->get());
    }

    // Create a new activity log
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ticket_id' => 'nullable|exists:tickets,id',
            'action' => 'required|string',
            'details' => 'required|string'
        ]);

        $log = ActivityLog::create($validated);
        return response()->json($log, 201);
    }

    // Show a single activity log
    public function show(ActivityLog $activityLog)
    {
        return response()->json($activityLog);
    }

    // Delete an activity log
    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();
        return response()->json(['message' => 'Activity log deleted']);
    }

    // Get logs for a specific user
    public function logsByUser($userId)
    {
        return response()->json(ActivityLog::byUser($userId)->get());
    }

    // Get logs for a specific ticket
    public function logsByTicket($ticketId)
    {
        return response()->json(ActivityLog::byTicket($ticketId)->get());
    }
}
