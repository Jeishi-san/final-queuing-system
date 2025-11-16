<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'employee_id' => 'required|string|unique:users,employee_id',
            'role' => 'required|string',
            'department' => 'required|string',
            'contact_number' => 'required|string',
            'image' => 'nullable|string',
            'account_status' => 'sometimes|in:active,inactive,on-leave'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'employee_id' => $request->employee_id,
            'role' => $request->role,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'image' => $request->image,
            'account_status' => $request->account_status ?? 'active',
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'employee_id' => 'sometimes|string|unique:users,employee_id,' . $user->id,
            'role' => 'sometimes|string',
            'department' => 'sometimes|string',
            'contact_number' => 'sometimes|string',
            'image' => 'nullable|string',
            'account_status' => 'sometimes|in:active,inactive,on-leave'
        ]);

        $updateData = $request->only([
            'name', 'email', 'employee_id', 'role', 
            'department', 'contact_number', 'image', 'account_status'
        ]);

        if ($request->has('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        return response()->json($user);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get tickets handled by user
     */
    public function getTicketsHandled(User $user)
    {
        $ticketsHandled = $user->tickets_handled;
        
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'tickets_handled' => $ticketsHandled
        ]);
    }

    /**
     * Get average resolution time for user
     */
    public function getAverageResolutionTime(User $user)
    {
        $averageResolutionTime = $user->average_resolution_time;
        
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'average_resolution_time_minutes' => $averageResolutionTime,
            'average_resolution_time_human' => $this->formatMinutesToHuman($averageResolutionTime)
        ]);
    }

    /**
     * Get activity log for user
     */
    public function getActivityLog(User $user)
    {
        $activityLogs = $user->activityLogs()
            ->with('ticket')
            ->orderBy('log_date', 'desc')
            ->get();

        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'activity_logs' => $activityLogs
        ]);
    }

    /**
     * Helper method to format minutes to human readable time
     */
    private function formatMinutesToHuman($minutes)
    {
        if ($minutes == 0) {
            return '0 minutes';
        }

        $days = floor($minutes / (24 * 60));
        $hours = floor(($minutes % (24 * 60)) / 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . ' day' . ($days > 1 ? 's' : '');
        if ($hours > 0) $parts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($mins > 0) $parts[] = $mins . ' minute' . ($mins > 1 ? 's' : '');

        return implode(', ', $parts);
    }
}