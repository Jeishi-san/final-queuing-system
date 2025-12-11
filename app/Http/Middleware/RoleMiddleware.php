<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  The allowed roles (e.g., 'admin', 'it_staff')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if the user is authenticated
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // 2. Check if the user's role is in the list of allowed roles
        if (!in_array($user->role, $roles)) {
            
            // If the user is an Agent trying to access a protected page, redirect them to their home.
            if ($user->role === 'agent') {
                return redirect('/queue');
            }
            
            // For general unauthorized access, return a 403 Forbidden response.
            return response()->json(['message' => 'Unauthorized: Permission denied.'], 403);
        }

        // 3. Role is allowed, continue the request
        return $next($request);
    }
}