<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireSecurityVerification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user requires security verification
            if (($user->require_pin_on_login || $user->require_pattern_on_login)) {
                // Check if verification has been done in this session
                if (!session('security_verified_at')) {
                    return redirect('/verify-security')->with('redirect_to', $request->path());
                }
                
                // Verify that security verification was done within last 30 minutes
                if (now()->diffInMinutes(session('security_verified_at')) > 30) {
                    session()->forget('security_verified_at');
                    return redirect('/verify-security')->with('redirect_to', $request->path());
                }
            }
        }
        
        return $next($request);
    }
}
