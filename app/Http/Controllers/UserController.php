<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // List all users
    public function index()
    {
        return response()->json(User::all());
    }

    // Create a new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'employee_id' => 'required|unique:users',
            'role' => 'required|string',
            'department' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'account_status' => 'nullable|in:active,inactive,on-leave'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json($user, 201);
    }

    // Show a single user
    public function show(User $user)
    {
        return response()->json($user);
    }

    // Update an existing user
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'employee_id' => 'sometimes|unique:users,employee_id,' . $user->id,
            'role' => 'sometimes|string',
            'department' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'account_status' => 'nullable|in:active,inactive,on-leave'
        ]);

        if(isset($validated['password'])){
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    // Delete a user
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    // Get tickets handled by this user
    public function ticketsHandled(User $user)
    {
        return response()->json($user->ticketsHandled()->get());
    }

    // Calculate average resolution time for this user's tickets
    public function averageResolutionTime(User $user)
    {
        $tickets = $user->ticketsHandled()->whereNotNull('resolved_at')->get();
        if ($tickets->isEmpty()) return response()->json(['average_resolution_time' => 0]);

        $totalSeconds = $tickets->sum(fn($ticket) => strtotime($ticket->resolved_at) - strtotime($ticket->created_at));
        $avg = $totalSeconds / $tickets->count();

        return response()->json(['average_resolution_time' => $avg]);
    }

    // Get activity log of this user
    public function activityLog(User $user)
    {
        return response()->json($user->activityLogs()->latest()->get());
    }
}
