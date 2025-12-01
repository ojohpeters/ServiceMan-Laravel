<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     * 
     * MAJOR SECURITY: Blocks ALL access for unverified users except:
     * - Logout (to allow them to log out)
     * - Email verification routes (to verify email)
     * - Profile page (to see verification status)
     * 
     * This applies to BOTH CLIENTS and SERVICEMEN.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check authenticated users (skip guest users)
        if ($user && !$user->is_email_verified) {
            // Strictly allow ONLY these routes for unverified users
            $allowedRoutes = [
                'logout',
                'verification.verify',
                'profile',
                'profile.client',
                'profile.serviceman',
            ];

            $routeName = $request->route()?->getName();

            // Block ALL other routes - redirect to profile with warning
            if (!in_array($routeName, $allowedRoutes)) {
                // Check if this is an AJAX/API request
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Email verification required',
                        'message' => 'Please verify your email address to access this feature. Check your inbox for the verification link.',
                        'requires_verification' => true
                    ], 403);
                }

                // Redirect to profile page with warning
                return redirect()->route('profile')
                    ->with('error', 'Email verification required! Please verify your email address to access all features. Check your inbox for the verification link. If you didn\'t receive it, please contact support.');
            }
        }

        return $next($request);
    }
}
