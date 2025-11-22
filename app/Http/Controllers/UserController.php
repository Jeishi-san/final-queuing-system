<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // List all users with pagination and relationships
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $users = User::withCount(['handledTickets as resolved_tickets_count' => function($query) {
                    $query->whereNotNull('resolved_at');
                }])
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Create a new user
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|max:50|unique:users',
            'role' => 'required|string|max:50',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'account_status' => 'nullable|in:active,inactive,on-leave',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $request->only([
                'name', 'email', 'employee_id', 'role', 
                'department', 'contact_number', 'account_status'
            ]);
            $userData['password'] = Hash::make($request->password);
            $userData['account_status'] = $userData['account_status'] ?? 'active';

            // Handle image upload if present
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('profile-images', 'public');
                $userData['image'] = $imagePath;
            }

            $user = User::create($userData);

            Log::info('User created successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show a single user with relationships
    public function show(User $user)
    {
        try {
            $user->load(['activityLogs' => function($query) {
                $query->latest()->limit(10);
            }]);

            return response()->json([
                'user' => $user,
                'stats' => [
                    'tickets_handled' => $user->tickets_handled,
                    'average_resolution_time' => $user->average_resolution_time,
                    'average_resolution_time_human' => $user->average_resolution_time_human,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update an existing user
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'employee_id' => 'sometimes|string|max:50|unique:users,employee_id,' . $user->id,
            'role' => 'sometimes|string|max:50',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'account_status' => 'nullable|in:active,inactive,on-leave',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only([
                'name', 'email', 'employee_id', 'role',
                'department', 'contact_number', 'account_status'
            ]);

            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Handle image upload if present
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($user->image) {
                    Storage::disk('public')->delete($user->image);
                }
                $imagePath = $request->file('image')->store('profile-images', 'public');
                $updateData['image'] = $imagePath;
            }

            $user->update($updateData);

            Log::info('User updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a user
    public function destroy(User $user)
    {
        try {
            // Check if user has related records before deleting
            $ticketCount = $user->handledTickets()->count();
            $activityCount = $user->activityLogs()->count();

            if ($ticketCount > 0 || $activityCount > 0) {
                return response()->json([
                    'message' => 'Cannot delete user. User has related tickets or activity logs.',
                    'tickets_count' => $ticketCount,
                    'activity_logs_count' => $activityCount
                ], 422);
            }

            // Delete profile image if exists
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            $user->delete();

            Log::info('User deleted successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get tickets handled by this user with pagination
    public function ticketsHandled(User $user, Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');

            $query = $user->handledTickets()
                ->with(['agent', 'component'])
                ->latest();

            if ($status) {
                $query->where('status', $status);
            }

            $tickets = $query->paginate($perPage);

            return response()->json([
                'tickets' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user tickets: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch user tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Calculate average resolution time for this user's tickets
    public function averageResolutionTime(User $user)
    {
        try {
            return response()->json([
                'average_resolution_time_minutes' => $user->average_resolution_time,
                'average_resolution_time_human' => $user->average_resolution_time_human,
                'tickets_handled' => $user->tickets_handled
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating resolution time: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to calculate resolution time',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get activity log of this user with pagination
    public function activityLog(User $user, Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);

            $activityLogs = $user->activityLogs()
                ->with(['ticket', 'agent'])
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
            Log::error('Error fetching user activity log: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch activity log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Bulk update user statuses
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'account_status' => 'required|in:active,inactive,on-leave'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updatedCount = User::whereIn('id', $request->user_ids)
                ->update(['account_status' => $request->account_status]);

            Log::info('Bulk status update completed', [
                'updated_count' => $updatedCount,
                'new_status' => $request->account_status
            ]);

            return response()->json([
                'message' => 'User statuses updated successfully',
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk status update: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update user statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Search users
    public function search(Request $request)
    {
        try {
            $query = User::query();

            if ($request->has('search')) {
                $searchTerm = $request->get('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                      ->orWhere('department', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->has('role')) {
                $query->where('role', $request->get('role'));
            }

            if ($request->has('account_status')) {
                $query->where('account_status', $request->get('account_status'));
            }

            $perPage = $request->get('per_page', 15);
            $users = $query->withCount(['handledTickets as resolved_tickets_count' => function($q) {
                    $q->whereNotNull('resolved_at');
                }])
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching users: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to search users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get current authenticated user profile
    public function getCurrentUserProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // Load user with recent activity and relationships
            $user->load(['activityLogs' => function($query) {
                $query->latest()->limit(10);
            }]);

            return response()->json([
                'user' => $user,
                'profile' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                    'role' => $user->role,
                    'department' => $user->department,
                    'contact_number' => $user->contact_number,
                    'account_status' => $user->account_status,
                    'image' => $user->image,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'stats' => [
                    'tickets_handled' => $user->tickets_handled ?? 0,
                    'average_resolution_time' => $user->average_resolution_time ?? 0,
                    'average_resolution_time_human' => $user->average_resolution_time_human ?? 'N/A',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching current user profile: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch user profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get current user's activity logs
    public function getCurrentUserActivityLogs(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $perPage = $request->get('per_page', 15);

            // Get activity logs for the current user
            $activityLogs = ActivityLog::where('user_id', $user->id)
                ->with(['ticket', 'agent'])
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
            Log::error('Error fetching current user activity logs: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}