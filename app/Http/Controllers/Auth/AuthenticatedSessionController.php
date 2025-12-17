<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // ✅ Type Hinting this connects the validation
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\ActivityLog;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('vue.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Validation runs automatically because of 'LoginRequest' type hint above.

        $request->authenticate();

        $request->session()->regenerate();

        // LOG THE LOGIN ACTIVITY
        $this->logActivity($request->user()->id, 'User logged in');

        // 2. ✅ FIX: Role-Based Redirection
        // Agents go to the Queue page, everyone else (IT Staff, Admin, Super Admin) goes to Dashboard.
        $user = $request->user();
        if ($user->role === 'agent') {
            return redirect()->intended('/queue');
        }

        // Default redirect for IT Staff / Super Admin
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // LOG THE LOGOUT ACTIVITY
        if (Auth::check()) {
             $this->logActivity($request->user()->id, 'User logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }


    private function logActivity($userId, $action)
    {
        ActivityLog::create([
            'user_id' => $userId,
            'action'  => $action,
        ]);
    }

    public function checkSuperAdmin()
    {
        $user = Auth::user();
        return response()->json([
            'is_super_admin' => $user->role === 'super_admin'
        ]);
    }
}
