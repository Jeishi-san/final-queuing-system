<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Register method called', $request->all());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:users',
                // ✅ FIX: Only allow emails ending in @concentrix.com (case-insensitive)
                'regex:/@concentrix\.com$/i',
            ],
            'password' => 'required|string|min:8|confirmed',
            // Allow for 'admin' and 'super_admin' to be potentially seeded or created via a separate admin panel, 
            // but restrict public registration to 'agent' and 'it_staff'.
            // NOTE: If you only register public accounts, keep 'in:agent,it_staff'. 
            // I'll assume you only want public registration for Agent/IT Staff.
            'role' => 'required|string|in:agent,it_staff', 

            // ✅ Employee ID is only required if role is 'it_staff'.
            'employee_id' => 'required_if:role,it_staff|nullable|string|unique:users',

            'department' => 'nullable|string',
            'contact_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Ensure default profile image exists in the public storage disk
            $defaultStoragePath = 'profile-images/profile.png';
            $defaultSourcePath = resource_path('assets/img/profile.png');

            try {
                if (File::exists($defaultSourcePath) && !Storage::disk('public')->exists($defaultStoragePath)) {
                    Storage::disk('public')->put($defaultStoragePath, File::get($defaultSourcePath));
                    Log::info('Default profile image copied to storage', ['to' => $defaultStoragePath]);
                }
            } catch (\Throwable $imgEx) {
                // Non-fatal: log but continue registration
                Log::warning('Unable to prepare default profile image', [
                    'error' => $imgEx->getMessage(),
                    'source' => $defaultSourcePath,
                    'target' => $defaultStoragePath,
                ]);
            }

            // Determine department: Agents = null, IT Staff = Input or Default
            $department = null;
            if ($request->role === 'it_staff') {
                $department = $request->department ?? 'IT Ops';
            }

            // Determine Employee ID: Agents = null
            // This ensures that even if the frontend sends an empty string "", it becomes NULL in the DB
            $employeeId = $request->role === 'it_staff' ? $request->employee_id : null;

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'employee_id' => $employeeId, 
                'department' => $department,
                'contact_number' => $request->contact_number,
                'account_status' => 'active',
                'email_verified_at' => now(), // Assume verification upon registration for internal users
                // Set default profile image for newly registered Agent/IT Staff
                'image' => $defaultStoragePath,
            ]);

            // Determine redirect URL based on role
            $redirectUrl = ($request->role === 'agent') ? '/queue' : '/dashboard';

            Log::info('User created successfully', ['user_id' => $user->id, 'role' => $request->role]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'redirect_url' => $redirectUrl
            ], 201);

        } catch (\Exception $e) {
            Log::error('User creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}