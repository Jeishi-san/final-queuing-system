<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'action' => 'required|string'
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

    public function getUserActivityLogs(Request $request)
{
    try {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);

        $activityLogs = $user->activityLogs()
            ->with(['ticket' => function($query) {
                $query->select('id', 'ticket_number', 'title');
            }])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'activity_logs' => $activityLogs->items(),
            'pagination' => [
                'current_page' => $activityLogs->currentPage(),
                'last_page' => $activityLogs->lastPage(),
                'per_page' => $activityLogs->perPage(),
                'total' => $activityLogs->total(),
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching user activity logs: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to fetch activity logs',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
