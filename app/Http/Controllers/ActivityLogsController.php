<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * ✅ INDEX - Display a listing of activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'ticket', 'agent'])
            ->orderBy('log_date', 'DESC');

        // Apply filters
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->ticket_id) {
            $query->where('ticket_id', $request->ticket_id);
        }

        if ($request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->start_date) {
            $query->where('log_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('log_date', '<=', $request->end_date);
        }

        $activityLogs = $query->paginate($request->get('per_page', 20));

        return response()->json($activityLogs);
    }

    /**
     * ✅ STORE - Create a new activity log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'ticket_id' => 'nullable|exists:tickets,id',
            'agent_id' => 'nullable|exists:agents,id',
            'action' => 'required|string|max:255',
            'details' => 'required|string',
            'log_date' => 'nullable|date',
        ]);

        $activityLog = ActivityLog::create([
            'user_id' => $validated['user_id'],
            'ticket_id' => $validated['ticket_id'],
            'agent_id' => $validated['agent_id'],
            'action' => $validated['action'],
            'details' => $validated['details'],
            'log_date' => $validated['log_date'] ?? now(),
        ]);

        $activityLog->load(['user', 'ticket', 'agent']);

        return response()->json($activityLog, 201);
    }

    /**
     * ✅ DESTROY - Remove the specified activity log
     */
    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();

        return response()->json([
            'message' => 'Activity log deleted successfully'
        ]);
    }
}