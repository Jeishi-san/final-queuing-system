<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Register method called', $request->all());
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|unique:users',
            'role' => 'required|string|max:50', // ✅ FIXED: Accept any string
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
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'employee_id' => $request->employee_id,
                'role' => $request->role,
                'department' => $request->department ?? 'IT Ops',
                'contact_number' => $request->contact_number,
                'account_status' => 'active',
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'redirect_url' => '/dashboard'
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