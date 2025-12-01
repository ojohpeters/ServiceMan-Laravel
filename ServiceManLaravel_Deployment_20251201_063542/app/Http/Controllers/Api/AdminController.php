<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\User;
use App\Models\Category;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            return $next($request);
        });
    }

    public function getPendingAssignments()
    {
        $pendingRequests = ServiceRequest::where('status', 'PENDING_ADMIN_ASSIGNMENT')
            ->with(['client', 'category', 'payments'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($pendingRequests);
    }

    public function assignServiceman(Request $request, $serviceRequestId)
    {
        $validator = Validator::make($request->all(), [
            'serviceman_id' => 'required|exists:users,id',
            'backup_serviceman_id' => 'nullable|exists:users,id',
            'is_emergency' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        $serviceman = User::findOrFail($request->serviceman_id);

        // Validate serviceman
        if (!$serviceman->isServiceman()) {
            return response()->json(['error' => 'User is not a serviceman'], 400);
        }

        if (!$serviceman->servicemanProfile->is_available) {
            return response()->json(['error' => 'Serviceman is not available'], 400);
        }

        // Update service request
        $serviceRequest->update([
            'serviceman_id' => $request->serviceman_id,
            'backup_serviceman_id' => $request->backup_serviceman_id,
            'status' => 'ASSIGNED_TO_SERVICEMAN',
            'is_emergency' => $request->boolean('is_emergency', $serviceRequest->is_emergency)
        ]);

        // Send notifications
        $this->notifyServicemanAssignment($serviceRequest);

        $serviceRequest->load(['client', 'serviceman', 'backupServiceman', 'category']);

        return response()->json($serviceRequest);
    }

    public function getPricingReview()
    {
        $pendingPricing = ServiceRequest::where('status', 'SERVICEMAN_INSPECTED')
            ->with(['client', 'serviceman', 'category'])
            ->orderBy('inspection_completed_at', 'asc')
            ->get();

        return response()->json($pendingPricing);
    }

    public function updateFinalCost(Request $request, $serviceRequestId)
    {
        $validator = Validator::make($request->all(), [
            'final_cost' => 'required|numeric|min:0',
            'admin_markup_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);

        $serviceRequest->update([
            'final_cost' => $request->final_cost,
            'admin_markup_percentage' => $request->admin_markup_percentage,
            'status' => 'AWAITING_CLIENT_APPROVAL'
        ]);

        // Notify client
        $this->notifyClient($serviceRequest->client, 'COST_ESTIMATE_READY',
            'Cost Estimate Ready',
            "Cost estimate is ready for service request #{$serviceRequest->id}. Amount: ₦{$request->final_cost}",
            $serviceRequest
        );

        $serviceRequest->load(['client', 'serviceman', 'category']);

        return response()->json($serviceRequest);
    }

    public function getRevenueAnalytics(Request $request)
    {
        $period = $request->get('period', 'month'); // month, year, all
        $query = Payment::successful();

        switch ($period) {
            case 'month':
                $query->whereMonth('paid_at', now()->month)
                      ->whereYear('paid_at', now()->year);
                break;
            case 'year':
                $query->whereYear('paid_at', now()->year);
                break;
            // 'all' - no additional filters
        }

        $totalRevenue = $query->sum('amount');
        $transactionCount = $query->count();

        // Monthly breakdown for the current year
        $monthlyData = Payment::successful()
            ->whereYear('paid_at', now()->year)
            ->select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'total_revenue' => $totalRevenue,
            'transaction_count' => $transactionCount,
            'period' => $period,
            'monthly_breakdown' => $monthlyData
        ]);
    }

    public function getTopServicemen()
    {
        $topServicemen = User::where('user_type', 'SERVICEMAN')
            ->with('servicemanProfile.category')
            ->whereHas('servicemanProfile', function ($query) {
                $query->where('total_jobs_completed', '>', 0);
            })
            ->get()
            ->sortByDesc(function ($user) {
                return $user->servicemanProfile->rating * $user->servicemanProfile->total_jobs_completed;
            })
            ->take(10)
            ->values()
            ->map(function ($serviceman) {
                $profile = $serviceman->servicemanProfile;
                return [
                    'id' => $serviceman->id,
                    'full_name' => $serviceman->getFullNameAttribute(),
                    'email' => $serviceman->email,
                    'category' => $profile->category,
                    'rating' => $profile->rating,
                    'total_jobs_completed' => $profile->total_jobs_completed,
                    'years_of_experience' => $profile->years_of_experience,
                    'is_available' => $profile->is_available
                ];
            });

        return response()->json($topServicemen);
    }

    public function getTopCategories()
    {
        $topCategories = Category::withCount('serviceRequests')
            ->orderBy('service_requests_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'request_count' => $category->service_requests_count,
                    'active_servicemen' => $category->servicemen()->available()->count()
                ];
            });

        return response()->json($topCategories);
    }

    public function getServiceRequestStats()
    {
        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_assignment' => ServiceRequest::where('status', 'PENDING_ADMIN_ASSIGNMENT')->count(),
            'in_progress' => ServiceRequest::whereIn('status', ['ASSIGNED_TO_SERVICEMAN', 'SERVICEMAN_INSPECTED', 'AWAITING_CLIENT_APPROVAL', 'NEGOTIATING', 'AWAITING_PAYMENT', 'PAYMENT_CONFIRMED', 'IN_PROGRESS'])->count(),
            'completed' => ServiceRequest::where('status', 'COMPLETED')->count(),
            'cancelled' => ServiceRequest::where('status', 'CANCELLED')->count(),
            'emergency_requests' => ServiceRequest::where('is_emergency', true)->count()
        ];

        return response()->json($stats);
    }

    public function getUserStats()
    {
        $stats = [
            'total_users' => User::count(),
            'clients' => User::where('user_type', 'CLIENT')->count(),
            'servicemen' => User::where('user_type', 'SERVICEMAN')->count(),
            'admins' => User::where('user_type', 'ADMIN')->count(),
            'verified_users' => User::where('is_email_verified', true)->count(),
            'active_servicemen' => User::where('user_type', 'SERVICEMAN')
                ->whereHas('servicemanProfile', function ($query) {
                    $query->where('is_available', true);
                })->count()
        ];

        return response()->json($stats);
    }

    public function getRecentActivity()
    {
        $recentRequests = ServiceRequest::with(['client', 'serviceman', 'category'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $recentPayments = Payment::with(['serviceRequest.client'])
            ->orderBy('paid_at', 'desc')
            ->take(10)
            ->get();

        $recentRatings = Rating::with(['serviceRequest', 'client', 'serviceman'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'recent_requests' => $recentRequests,
            'recent_payments' => $recentPayments,
            'recent_ratings' => $recentRatings
        ]);
    }

    private function notifyServicemanAssignment($serviceRequest)
    {
        // Notify primary serviceman
        if ($serviceRequest->serviceman) {
            \App\Models\Notification::create([
                'user_id' => $serviceRequest->serviceman->id,
                'notification_type' => 'SERVICE_ASSIGNED',
                'title' => 'Service Request Assigned',
                'message' => "You have been assigned to service request #{$serviceRequest->id}. Emergency: " . ($serviceRequest->is_emergency ? 'Yes' : 'No'),
                'service_request_id' => $serviceRequest->id
            ]);
        }

        // Notify backup serviceman
        if ($serviceRequest->backup_serviceman_id) {
            \App\Models\Notification::create([
                'user_id' => $serviceRequest->backup_serviceman_id,
                'notification_type' => 'BACKUP_OPPORTUNITY',
                'title' => 'Backup Opportunity',
                'message' => "You are assigned as backup for service request #{$serviceRequest->id}",
                'service_request_id' => $serviceRequest->id
            ]);
        }
    }

    private function notifyClient($client, $type, $title, $message, $serviceRequest)
    {
        \App\Models\Notification::create([
            'user_id' => $client->id,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'service_request_id' => $serviceRequest->id
        ]);
    }
}