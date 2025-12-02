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
                // Allow access to public pages, pending verification page, logout, and profile edit
                $allowedRoutes = [
                    // Public pages - accessible to everyone
                    'home',
                    'about',
                    'faq',
                    'contact',
                    'contact.submit',
                    'services',
                    'services.category',
                    'servicemen.show',
                    // Public API routes
                    'api.categories.servicemen',
                    'api.skills.common',
                    // Authentication and profile routes
                    'pending-verification',
                    'logout',
                    'profile',
                    'profile.client',
                    'profile.serviceman',
                    'profile.update',
                    'profile.client.update',
                    'profile.serviceman.update',
                    // Email verification routes
                    'verification.verify',
                    'verification.resend',
                    'verification.send',
                    // Service request viewing
                    'service-requests.index',
                    'service-requests.show',
                ];
                
                $routeName = $request->route()?->getName();
                
                if (!in_array($routeName, $allowedRoutes)) {
                    return redirect()->route('pending-verification');
                }
            }
        }

        return $next($request);
    }
}
