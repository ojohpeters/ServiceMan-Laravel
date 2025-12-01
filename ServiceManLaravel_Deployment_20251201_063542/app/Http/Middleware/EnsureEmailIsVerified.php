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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_email_verified) {
            // Allow access to profile and email verification routes
            $allowedRoutes = [
                'profile',
                'profile.client',
                'profile.serviceman',
                'profile.client.update',
                'profile.serviceman.update',
                'verification.verify',
                'logout',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('profile')
                    ->with('warning', 'Please verify your email address to access all features. Check your inbox for the verification link.');
            }
        }

        return $next($request);
    }
}
