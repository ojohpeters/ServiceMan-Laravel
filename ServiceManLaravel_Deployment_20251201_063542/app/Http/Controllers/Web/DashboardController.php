<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isClient()) {
            return $this->clientDashboard();
        } elseif ($user->isServiceman()) {
            return $this->servicemanDashboard();
        }

        return redirect('/');
    }

    private function adminDashboard()
    {
        $stats = [
            'totalUsers' => \App\Models\User::count(),
            'totalServiceRequests' => ServiceRequest::count(),
            'pendingRequests' => ServiceRequest::where('status', 'PENDING_ADMIN_ASSIGNMENT')->count(),
            'completedRequests' => ServiceRequest::where('status', 'COMPLETED')->count(),
            'total_servicemen' => \App\Models\User::where('user_type', 'SERVICEMAN')->count(),
            'total_clients' => \App\Models\User::where('user_type', 'CLIENT')->count(),
        ];

        $recentRequests = ServiceRequest::with(['client', 'serviceman', 'category'])
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = \App\Models\User::with(['servicemanProfile', 'clientProfile'])
            ->latest()
            ->take(10)
            ->get();

        $pendingNegotiations = collect([]);
        if (class_exists('App\Models\PriceNegotiation')) {
            $pendingNegotiations = \App\Models\PriceNegotiation::with(['serviceRequest.client', 'proposedBy'])
                ->where('status', 'PENDING')
                ->latest()
                ->take(5)
                ->get();
        }

        $pendingCategoryRequests = \App\Models\CategoryRequest::with(['serviceman'])
            ->where('status', 'PENDING')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentRequests', 'recentUsers', 'pendingNegotiations', 'pendingCategoryRequests'));
    }

    private function clientDashboard()
    {
        $user = Auth::user();
        
        $stats = [
            'totalRequests' => $user->clientRequests()->count(),
            'pendingRequests' => $user->clientRequests()->where('status', 'PENDING_ADMIN_ASSIGNMENT')->count(),
            'inProgressRequests' => $user->clientRequests()->where('status', 'IN_PROGRESS')->count(),
            'completedRequests' => $user->clientRequests()->where('status', 'COMPLETED')->count(),
        ];

        // Calculate spending
        $totalSpent = $user->clientRequests()
            ->whereIn('status', ['COMPLETED'])
            ->sum('final_cost');
        
        $thisMonthSpent = $user->clientRequests()
            ->whereIn('status', ['COMPLETED'])
            ->whereMonth('work_completed_at', now()->month)
            ->whereYear('work_completed_at', now()->year)
            ->sum('final_cost');

        $stats['totalSpent'] = $totalSpent;
        $stats['thisMonthSpent'] = $thisMonthSpent;

        $recentRequests = $user->clientRequests()
            ->with(['serviceman', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $notifications = \App\Models\AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.client', compact('stats', 'recentRequests', 'notifications'));
    }

    private function servicemanDashboard()
    {
        $user = Auth::user();
        
        // Only count requests that are actually assigned to the serviceman (not pending admin assignment)
        $assignedStatuses = ['ASSIGNED_TO_SERVICEMAN', 'SERVICEMAN_INSPECTED', 'AWAITING_CLIENT_APPROVAL', 'NEGOTIATING', 'AWAITING_PAYMENT', 'PAYMENT_CONFIRMED', 'IN_PROGRESS', 'WORK_COMPLETED', 'COMPLETED'];
        
        $stats = [
            'totalRequests' => $user->servicemanRequests()->whereIn('status', $assignedStatuses)->count(),
            'pendingRequests' => $user->servicemanRequests()->where('status', 'ASSIGNED_TO_SERVICEMAN')->count(),
            'inProgressRequests' => $user->servicemanRequests()->where('status', 'IN_PROGRESS')->count(),
            'completedRequests' => $user->servicemanRequests()->where('status', 'COMPLETED')->count(),
        ];

        // Calculate earnings (serviceman gets their estimate, not the final cost with markup)
        $totalEarnings = $user->servicemanRequests()
            ->where('status', 'COMPLETED')
            ->sum('serviceman_estimated_cost');
        
        $thisMonthEarnings = $user->servicemanRequests()
            ->where('status', 'COMPLETED')
            ->whereMonth('work_completed_at', now()->month)
            ->whereYear('work_completed_at', now()->year)
            ->sum('serviceman_estimated_cost');

        $stats['totalEarnings'] = $totalEarnings;
        $stats['thisMonthEarnings'] = $thisMonthEarnings;
        $stats['averageRating'] = $user->servicemanProfile->rating ?? 0;
        $stats['totalJobsCompleted'] = $user->servicemanProfile->total_jobs_completed ?? 0;

        // Only show requests that have been assigned to this serviceman (exclude PENDING_ADMIN_ASSIGNMENT)
        $recentRequests = $user->servicemanRequests()
            ->whereIn('status', $assignedStatuses)
            ->with(['client', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $notifications = \App\Models\AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        $profile = $user->servicemanProfile;

        return view('dashboard.serviceman', compact('stats', 'recentRequests', 'notifications', 'profile'));
    }
}

