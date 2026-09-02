<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $lastActivity = $user->last_activity_at;
            $sessionTimeout = $user->session_timeout_hours ?? 72;
            
            // Check if session has expired
            if ($lastActivity && now()->diffInHours($lastActivity) > $sessionTimeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect('/account')->with('session_expired', 'Your session has expired. Please login again.');
            }
            
            // Update last activity time
            $user->update(['last_activity_at' => now()]);
        }
        
        return $next($request);
    }
}
