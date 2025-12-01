<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureServicemanApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if ($request->user()) {
            // If user is a serviceman and not approved
            if ($request->user()->user_type === 'SERVICEMAN' && !$request->user()->is_approved) {
                // Allow access to pending verification page, logout, and profile edit
                $allowedRoutes = ['pending-verification', 'logout', 'profile.serviceman', 'profile.serviceman.update'];
                
                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    return redirect()->route('pending-verification');
                }
            }
        }

        return $next($request);
    }
}
