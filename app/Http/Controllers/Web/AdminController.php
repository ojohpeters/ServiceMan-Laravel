<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Category;
use App\Models\AppNotification;
use App\Models\PriceNegotiation;
use App\Models\CategoryRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function dashboard()
    {
        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_assignment' => ServiceRequest::where('status', 'PENDING_ADMIN_ASSIGNMENT')->count(),
            'in_progress' => ServiceRequest::where('status', 'IN_PROGRESS')->count(),
            'completed' => ServiceRequest::where('status', 'COMPLETED')->count(),
            'emergency_requests' => ServiceRequest::where('is_emergency', true)->count(),
            'total_revenue' => \App\Models\Payment::where('status', 'SUCCESSFUL')
                ->whereIn('payment_type', ['INITIAL_BOOKING', 'FINAL_PAYMENT'])
                ->sum('amount'),
            'total_users' => User::count(),
            'total_servicemen' => User::where('user_type', 'SERVICEMAN')->count(),
            'total_clients' => User::where('user_type', 'CLIENT')->count(),
        ];

        $recentRequests = ServiceRequest::with(['client', 'serviceman', 'category'])
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::with(['servicemanProfile', 'clientProfile'])
            ->latest()
            ->limit(10)
            ->get();

        $pendingNegotiations = collect([]);
        if (class_exists('App\Models\PriceNegotiation')) {
            $pendingNegotiations = PriceNegotiation::with(['serviceRequest.client', 'proposedBy'])
                ->where('status', 'PENDING')
                ->latest()
                ->limit(5)
                ->get();
        }

        $pendingCategoryRequests = CategoryRequest::with(['serviceman'])
            ->where('status', 'PENDING')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRequests', 'recentUsers', 'pendingNegotiations', 'pendingCategoryRequests'));
    }

    public function serviceRequests(Request $request)
    {
        $query = ServiceRequest::with(['client', 'serviceman', 'backupServiceman', 'category', 'payments']);

        // Filter by status
        if ($request->filled('status')) {
            $status = trim($request->status);
            if ($status !== '') {
                $query->where('status', $status);
            }
        }

        // Filter by category - cast to integer for proper comparison
        if ($request->filled('category')) {
            $categoryId = trim($request->category);
            if ($categoryId !== '' && is_numeric($categoryId)) {
                $query->where('category_id', (int) $categoryId);
            }
        }

        // Filter by payment status
        if ($request->filled('paid')) {
            $paid = trim($request->paid);
            if ($paid === '1') {
                // Paid: Has a successful INITIAL_BOOKING payment
                $query->whereHas('payments', function($q) {
                    $q->where('payment_type', 'INITIAL_BOOKING')
                      ->where('status', 'SUCCESSFUL');
                });
            } elseif ($paid === '0') {
                // Unpaid: No successful INITIAL_BOOKING payment
                $query->whereDoesntHave('payments', function($paymentQuery) {
                    $paymentQuery->where('payment_type', 'INITIAL_BOOKING')
                                 ->where('status', 'SUCCESSFUL');
                });
            }
        }

        $serviceRequests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.service-requests', compact('serviceRequests'));
    }

    public function pendingServicemen()
    {
        // Get servicemen without assigned categories
        $pendingServicemen = User::where('user_type', 'SERVICEMAN')
            ->whereHas('servicemanProfile', function($query) {
                $query->whereNull('category_id');
            })
            ->with('servicemanProfile')
            ->latest()
            ->paginate(20);

        $categories = Category::where('is_active', true)->get();

        return view('admin.pending-servicemen', compact('pendingServicemen', 'categories'));
    }

    public function assignCategory(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$user->isServiceman() || !$user->servicemanProfile) {
            return back()->with('error', 'User is not a serviceman or has no profile.');
        }

        $category = Category::findOrFail($request->category_id);

        $user->servicemanProfile->update([
            'category_id' => $request->category_id,
            'is_available' => true,
        ]);

        // Notify serviceman
        $this->notificationService->notifyServiceman(
            $user,
            'CATEGORY_ASSIGNED',
            '🎉 Category Assigned - Profile Activated',
            "Admin has reviewed your profile and assigned you to the '{$category->name}' category. Your profile is now active and you can start receiving service requests!",
            null
        );

        return back()->with('success', "Category '{$category->name}' assigned to {$user->full_name} successfully!");
    }

    public function customServiceRequests()
    {
        $query = \App\Models\CustomServiceRequest::with(['serviceman', 'category', 'reviewer']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $customRequests = $query->latest()->paginate(20)->withQueryString();

        $categories = Category::where('is_active', true)->get();

        return view('admin.custom-service-requests', compact('customRequests', 'categories'));
    }

    public function handleCustomServiceRequest(Request $request, \App\Models\CustomServiceRequest $customServiceRequest)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'category_id' => 'required_if:action,approve|nullable|exists:categories,id',
            'admin_response' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($customServiceRequest->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        if ($request->action === 'approve') {
            $category = Category::findOrFail($request->category_id);
            
            $customServiceRequest->update([
                'status' => 'APPROVED',
                'category_id' => $request->category_id,
                'admin_response' => $request->admin_response,
                'approved_at' => now(),
                'reviewed_by' => Auth::id(),
            ]);

            // Notify serviceman
            $this->notificationService->notifyServiceman(
                $customServiceRequest->serviceman,
                'CUSTOM_SERVICE_APPROVED',
                '🎉 Custom Service Request Approved!',
                "Great news! Your custom service request for '{$customServiceRequest->service_name}' has been approved. It has been added as '{$category->name}' category.\n\nAdmin's Message: {$request->admin_response}\n\nYou can now apply for this category on your profile to start receiving requests!",
                null
            );

            return back()->with('success', "Custom service approved and serviceman has been notified!");
            
        } elseif ($request->action === 'reject') {
            $customServiceRequest->update([
                'status' => 'REJECTED',
                'admin_response' => $request->admin_response,
                'rejected_at' => now(),
                'reviewed_by' => Auth::id(),
            ]);

            // Notify serviceman with rejection reason
            $this->notificationService->notifyServiceman(
                $customServiceRequest->serviceman,
                'CUSTOM_SERVICE_REJECTED',
                '❌ Custom Service Request Not Approved',
                "We've reviewed your custom service request for '{$customServiceRequest->service_name}'.\n\nUnfortunately, we cannot add this service at this time.\n\nReason: {$request->admin_response}\n\nYou can submit a new request or contact support for more information.",
                null
            );

            return back()->with('success', "Custom service request rejected and serviceman has been notified with the reason.");
        }
    }

    public function assignBackupServiceman(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'backup_serviceman_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $backupServiceman = User::findOrFail($request->backup_serviceman_id);
        
        if (!$backupServiceman->isServiceman()) {
            return back()->with('error', 'Selected user is not a serviceman.');
        }

        // Check if backup serviceman is available and matches category
        if ($backupServiceman->servicemanProfile && 
            $backupServiceman->servicemanProfile->category_id !== $serviceRequest->category_id) {
            return back()->with('error', 'Backup serviceman does not match the service category.');
        }

        if ($backupServiceman->servicemanProfile && !$backupServiceman->servicemanProfile->is_available) {
            return back()->with('error', 'Backup serviceman is not currently available.');
        }

        // Check if trying to assign the same serviceman as primary
        if ($serviceRequest->serviceman_id === $backupServiceman->id) {
            return back()->with('error', 'Cannot assign the same serviceman as both primary and backup.');
        }

        $serviceRequest->update([
            'backup_serviceman_id' => $backupServiceman->id,
        ]);

        // Create notification for backup serviceman
        $this->notificationService->notifyServiceman(
            $backupServiceman,
            'BACKUP_ASSIGNMENT',
            'Backup Assignment',
            "You have been assigned as backup serviceman for service request #{$serviceRequest->id}. You may be called if the primary serviceman is unavailable.",
            $serviceRequest
        );

        // Create notification for client
        $this->notificationService->notifyClient(
            $serviceRequest->client,
            'BACKUP_ASSIGNED',
            'Backup Serviceman Assigned',
            "A backup serviceman has been assigned to your request #{$serviceRequest->id} to ensure service continuity.",
            $serviceRequest
        );

        return back()->with('success', 'Backup serviceman assigned successfully!');
    }

    public function setFinalCost(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'final_cost' => 'required|numeric|min:0',
            'admin_markup_percentage' => 'nullable|numeric|min:0|max:50',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($serviceRequest->status !== 'SERVICEMAN_INSPECTED') {
            return back()->with('error', 'Can only set final cost after serviceman inspection.');
        }

        \Log::info('Setting final cost', [
            'service_request_id' => $serviceRequest->id,
            'serviceman_estimate' => $serviceRequest->serviceman_estimated_cost,
            'final_cost' => $request->final_cost,
            'markup_percentage' => $request->admin_markup_percentage,
        ]);

        $serviceRequest->update([
            'final_cost' => $request->final_cost,
            'admin_markup_percentage' => $request->admin_markup_percentage ?? 10,
            'status' => 'AWAITING_CLIENT_APPROVAL',
        ]);

        // Create notification for client
        $servicemanEstimate = $serviceRequest->serviceman_estimated_cost;
        $finalCost = $request->final_cost;
        $notesText = $request->admin_notes ? "\n\nAdmin Notes: " . $request->admin_notes : '';
        
        $this->notificationService->notifyClient(
            $serviceRequest->client,
            'COST_ESTIMATE_READY',
            '💰 Final Cost Ready for Approval',
            "The final cost for service request #{$serviceRequest->id} is ready:\n\n" .
            "Serviceman Estimate: ₦" . number_format($servicemanEstimate) . "\n" .
            "Final Cost (with admin fee): ₦" . number_format($finalCost) . "\n\n" .
            "Please review and approve to proceed with payment.{$notesText}",
            $serviceRequest
        );

        // Notify serviceman that cost has been set
        $this->notificationService->notifyServiceman(
            $serviceRequest->serviceman,
            'COST_APPROVED_BY_ADMIN',
            '✅ Your Cost Estimate Approved',
            "Admin has approved your estimate of ₦" . number_format($servicemanEstimate) . " for service request #{$serviceRequest->id}. Final cost to client: ₦" . number_format($finalCost) . ". Waiting for client approval.",
            $serviceRequest
        );

        return back()->with('success', 'Final cost set successfully! Client has been notified.');
    }


    public function categories()
    {
        $categories = Category::withCount('servicemen')->latest()->paginate(15);
        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string|max:1000',
            'icon_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon_url' => $request->icon_url,
            'is_active' => true,
        ]);

        return back()->with('success', 'Category created successfully!');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'icon_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon_url' => $request->icon_url,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Category updated successfully!');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->serviceRequests()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing service requests.');
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully!');
    }

    public function users()
    {
        $users = User::with(['clientProfile', 'servicemanProfile.category'])
            ->latest()
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function servicemen(Request $request)
    {
        $query = User::where('user_type', 'SERVICEMAN')
            ->with(['servicemanProfile.category', 'ratingsReceived']);

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('servicemanProfile', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            if ($request->approval_status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->approval_status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        // Filter by availability
        if ($request->filled('availability')) {
            $availability = $request->availability === 'available';
            $query->whereHas('servicemanProfile', function($q) use ($availability) {
                $q->where('is_available', $availability);
            });
        }

        // Filter by email verification
        if ($request->filled('verified')) {
            $verified = $request->verified === 'yes';
            $query->where('is_email_verified', $verified);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  // Also search in skills
                  ->orWhereHas('servicemanProfile', function($sq) use ($search) {
                      $sq->where('skills', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by skills (separate from general search)
        if ($request->filled('skill')) {
            $skill = $request->skill;
            $query->whereHas('servicemanProfile', function($q) use ($skill) {
                $q->where('skills', 'like', "%{$skill}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'ranking');
        switch ($sortBy) {
            case 'ranking':
                // Sort by ranking within category (rating desc, then by jobs completed)
                $query->join('serviceman_profiles', 'users.id', '=', 'serviceman_profiles.user_id')
                      ->orderBy('serviceman_profiles.category_id')
                      ->orderByDesc('serviceman_profiles.rating')
                      ->orderByDesc('serviceman_profiles.total_jobs_completed')
                      ->select('users.*');
                break;
            case 'rating':
                $query->join('serviceman_profiles', 'users.id', '=', 'serviceman_profiles.user_id')
                      ->orderByDesc('serviceman_profiles.rating')
                      ->select('users.*');
                break;
            case 'jobs':
                $query->join('serviceman_profiles', 'users.id', '=', 'serviceman_profiles.user_id')
                      ->orderByDesc('serviceman_profiles.total_jobs_completed')
                      ->select('users.*');
                break;
            case 'name':
                $query->orderBy('first_name')->orderBy('last_name');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $servicemen = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total' => User::where('user_type', 'SERVICEMAN')->count(),
            'approved' => User::where('user_type', 'SERVICEMAN')->where('is_approved', true)->count(),
            'pending_approval' => User::where('user_type', 'SERVICEMAN')->where('is_approved', false)->count(),
            'available' => User::where('user_type', 'SERVICEMAN')
                ->whereHas('servicemanProfile', function($q) {
                    $q->where('is_available', true);
                })->count(),
            'verified' => User::where('user_type', 'SERVICEMAN')->where('is_email_verified', true)->count(),
        ];

        // Get categories for filter dropdown
        $categories = \App\Models\Category::orderBy('name')->get();

        // Get all unique skills from servicemen profiles (cache for performance)
        $allSkills = \Cache::remember('servicemen_skills', 3600, function () {
            return \App\Models\ServicemanProfile::whereNotNull('skills')
                ->where('skills', '!=', '')
                ->pluck('skills')
                ->flatMap(function($skills) {
                    return explode(',', $skills);
                })
                ->map(function($skill) {
                    return trim($skill);
                })
                ->filter()
                ->unique()
                ->sort()
                ->values();
        });

        return view('admin.servicemen', compact('servicemen', 'stats', 'categories', 'allSkills'));
    }

    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month'); // month, year, all
        
        // Revenue Analytics - Include both INITIAL_BOOKING and FINAL_PAYMENT
        $revenueQuery = \App\Models\Payment::where('status', 'SUCCESSFUL')
            ->whereIn('payment_type', ['INITIAL_BOOKING', 'FINAL_PAYMENT']);
        $totalRevenueQuery = \App\Models\Payment::where('status', 'SUCCESSFUL')
            ->whereIn('payment_type', ['INITIAL_BOOKING', 'FINAL_PAYMENT']);
        
        switch ($period) {
            case 'month':
                $revenueQuery->whereMonth('paid_at', now()->month)
                            ->whereYear('paid_at', now()->year);
                break;
            case 'year':
                $revenueQuery->whereYear('paid_at', now()->year);
                break;
            // 'all' - no filter
        }
        
        $revenue = $revenueQuery->sum('amount');
        $totalRevenue = $totalRevenueQuery->sum('amount');
        
        // Transaction counts
        $transactionsCount = $revenueQuery->count();
        $totalTransactions = $totalRevenueQuery->count();
        
        // Service Request Statistics
        $serviceRequestStats = [
            'total' => \App\Models\ServiceRequest::count(),
            'pending_assignment' => \App\Models\ServiceRequest::where('status', 'PENDING_ADMIN_ASSIGNMENT')->count(),
            'in_progress' => \App\Models\ServiceRequest::whereIn('status', [
                'ASSIGNED_TO_SERVICEMAN', 'SERVICEMAN_INSPECTED', 'AWAITING_CLIENT_APPROVAL', 
                'NEGOTIATING', 'AWAITING_PAYMENT', 'PAYMENT_CONFIRMED', 'IN_PROGRESS'
            ])->count(),
            'completed' => \App\Models\ServiceRequest::where('status', 'COMPLETED')->count(),
            'cancelled' => \App\Models\ServiceRequest::where('status', 'CANCELLED')->count(),
            'emergency' => \App\Models\ServiceRequest::where('is_emergency', true)->count(),
        ];
        
        $completionRate = $serviceRequestStats['total'] > 0 
            ? round(($serviceRequestStats['completed'] / $serviceRequestStats['total']) * 100, 1)
            : 0;
        
        // User Statistics
        $userStats = [
            'total_users' => User::count(),
            'clients' => User::where('user_type', 'CLIENT')->count(),
            'servicemen' => User::where('user_type', 'SERVICEMAN')->count(),
            'admins' => User::where('user_type', 'ADMIN')->count(),
            'verified_users' => User::where('is_email_verified', true)->count(),
            'approved_servicemen' => User::where('user_type', 'SERVICEMAN')
                ->where('is_approved', true)->count(),
            'active_servicemen' => User::where('user_type', 'SERVICEMAN')
                ->whereHas('servicemanProfile', function($q) {
                    $q->where('is_available', true);
                })->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];
        
        // Rating Statistics
        $ratingStats = [
            'average' => round(\DB::table('ratings')->avg('rating') ?? 0, 2),
            'total_ratings' => \DB::table('ratings')->count(),
            'five_star' => \DB::table('ratings')->where('rating', 5)->count(),
            'four_star' => \DB::table('ratings')->where('rating', 4)->count(),
            'three_star' => \DB::table('ratings')->where('rating', 3)->count(),
            'two_star' => \DB::table('ratings')->where('rating', 2)->count(),
            'one_star' => \DB::table('ratings')->where('rating', 1)->count(),
        ];
        
        // Monthly Revenue Chart Data (Last 12 months) - Include both booking fees and final payments
        $monthlyRevenueData = \App\Models\Payment::where('status', 'SUCCESSFUL')
            ->whereIn('payment_type', ['INITIAL_BOOKING', 'FINAL_PAYMENT'])
            ->whereYear('paid_at', '>=', now()->subMonths(11)->year)
            ->selectRaw('YEAR(paid_at) as year, MONTH(paid_at) as month, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->format('M Y'),
                    'revenue' => (float)$item->revenue,
                    'transactions' => $item->transactions,
                ];
            });
        
        // Category Performance
        $categoryStats = Category::withCount([
            'serviceRequests as total_requests',
            'serviceRequests as completed_requests' => function($query) {
                $query->where('status', 'COMPLETED');
            },
        ])
        ->withCount('servicemen as active_servicemen_count')
        ->get()
        ->map(function($category) {
            // Calculate revenue for this category - Include both booking fees and final payments
            $revenue = \App\Models\ServiceRequest::where('service_requests.category_id', $category->id)
                ->where('service_requests.status', 'COMPLETED')
                ->join('payments', 'service_requests.id', '=', 'payments.service_request_id')
                ->where('payments.status', 'SUCCESSFUL')
                ->whereIn('payments.payment_type', ['INITIAL_BOOKING', 'FINAL_PAYMENT'])
                ->sum('payments.amount');
            
            $category->revenue = (float)$revenue;
            return $category;
        })
        ->sortByDesc('total_requests');
        
        // Top Performing Servicemen (by revenue or jobs completed)
        $topServicemen = User::where('user_type', 'SERVICEMAN')
            ->with(['servicemanProfile.category'])
            ->whereHas('servicemanProfile', function($q) {
                $q->where('total_jobs_completed', '>', 0);
            })
            ->get()
            ->map(function($serviceman) {
                $profile = $serviceman->servicemanProfile;
                // Calculate total revenue from completed requests - Include both booking fees and final payments
                $revenue = \App\Models\ServiceRequest::where('service_requests.serviceman_id', $serviceman->id)
                    ->where('service_requests.status', 'COMPLETED')
                    ->join('payments', function($join) {
                        $join->on('service_requests.id', '=', 'payments.service_request_id')
                             ->where('payments.status', '=', 'SUCCESSFUL')
                             ->whereIn('payments.payment_type', ['INITIAL_BOOKING', 'FINAL_PAYMENT']);
                    })
                    ->sum('payments.amount');
                
                return [
                    'id' => $serviceman->id,
                    'name' => $serviceman->full_name,
                    'category' => $profile->category->name ?? 'N/A',
                    'rating' => round($profile->rating ?? 0, 2),
                    'jobs_completed' => $profile->total_jobs_completed ?? 0,
                    'revenue' => (float)($revenue ?? 0),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();
        
        // Status Distribution for Chart
        $statusDistribution = [
            'Pending Assignment' => \App\Models\ServiceRequest::where('status', 'PENDING_ADMIN_ASSIGNMENT')->count(),
            'In Progress' => $serviceRequestStats['in_progress'],
            'Completed' => $serviceRequestStats['completed'],
            'Cancelled' => $serviceRequestStats['cancelled'],
        ];
        
        // Recent Activity (Last 20 activities)
        $recentActivity = \App\Models\AppNotification::with(['serviceRequest', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'timestamp' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        return view('admin.analytics', compact(
            'period',
            'revenue',
            'totalRevenue',
            'transactionsCount',
            'totalTransactions',
            'serviceRequestStats',
            'userStats',
            'ratingStats',
            'completionRate',
            'monthlyRevenueData',
            'categoryStats',
            'topServicemen',
            'statusDistribution',
            'recentActivity'
        ));
    }

    public function handleNegotiation(Request $request, \App\Models\PriceNegotiation $negotiation)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:accept,reject,counter',
            'admin_response' => 'nullable|string|max:1000',
            'counter_amount' => 'required_if:action,counter|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $serviceRequest = $negotiation->serviceRequest;

        if ($request->action === 'accept') {
            // Accept the client's proposed amount
            $serviceRequest->update([
                'final_cost' => $negotiation->proposed_amount,
                'status' => 'AWAITING_PAYMENT',
            ]);

            $negotiation->update([
                'status' => 'ACCEPTED',
                'admin_response' => $request->admin_response,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Notify client
            $this->notificationService->notifyClient(
                $serviceRequest->client,
                'NEGOTIATION_ACCEPTED',
                'Negotiation Accepted',
                "Your price negotiation has been accepted! Final cost: ₦{$negotiation->proposed_amount}. You can now proceed with payment.",
                $serviceRequest
            );

            return back()->with('success', 'Negotiation accepted. Client has been notified.');

        } elseif ($request->action === 'reject') {
            // Reject the negotiation, revert to original price
            $serviceRequest->update([
                'status' => 'AWAITING_CLIENT_APPROVAL',
            ]);

            $negotiation->update([
                'status' => 'REJECTED',
                'admin_response' => $request->admin_response,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Notify client
            $this->notificationService->notifyClient(
                $serviceRequest->client,
                'NEGOTIATION_REJECTED',
                'Negotiation Rejected',
                "Your price negotiation has been rejected. The original cost of ₦{$serviceRequest->final_cost} still applies. " . ($request->admin_response ?? 'Please contact admin for more information.'),
                $serviceRequest
            );

            return back()->with('success', 'Negotiation rejected. Client has been notified.');

        } elseif ($request->action === 'counter') {
            // Counter with a new amount
            $serviceRequest->update([
                'final_cost' => $request->counter_amount,
                'status' => 'AWAITING_CLIENT_APPROVAL',
            ]);

            $negotiation->update([
                'status' => 'COUNTERED',
                'admin_response' => $request->admin_response,
                'counter_amount' => $request->counter_amount,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Notify client
            $this->notificationService->notifyClient(
                $serviceRequest->client,
                'NEGOTIATION_COUNTERED',
                'Counter Offer Received',
                "Admin has made a counter offer: ₦{$request->counter_amount}. " . ($request->admin_response ?? 'Please review and respond.'),
                $serviceRequest
            );

            return back()->with('success', 'Counter offer sent. Client has been notified.');
        }
    }

    public function assignServiceman(Request $request, ServiceRequest $serviceRequest)
    {
        \Log::info('=== ASSIGN SERVICEMAN CALLED ===');
        \Log::info('Request ID: ' . $serviceRequest->id);
        \Log::info('Request Data: ', $request->all());
        \Log::info('Current Status: ' . $serviceRequest->status);
        
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:assign,reject',
            'backup_serviceman_id' => 'nullable|exists:users,id',
            'message' => 'nullable|string|max:500',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed: ', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }
        
        \Log::info('Validation passed');

        if ($serviceRequest->status !== 'PENDING_ADMIN_ASSIGNMENT') {
            return back()->with('error', 'This service request is not pending assignment.');
        }

        if ($request->action === 'assign') {
            // Validate backup serviceman if provided
            if ($request->backup_serviceman_id) {
                $backupServiceman = User::where('id', $request->backup_serviceman_id)
                    ->where('user_type', 'SERVICEMAN')
                    ->whereHas('servicemanProfile', function($query) use ($serviceRequest) {
                        $query->where('category_id', $serviceRequest->category_id)
                              ->where('is_available', true);
                    })
                    ->first();

                if (!$backupServiceman) {
                    return back()->with('error', 'Selected backup serviceman is not available for this category.');
                }
                
                // Ensure backup is different from primary
                if ($request->backup_serviceman_id == $serviceRequest->serviceman_id) {
                    return back()->with('error', 'Backup serviceman cannot be the same as the primary serviceman.');
                }
            }

            // Update service request status and backup serviceman
            \Log::info('Updating service request...');
            $updated = $serviceRequest->update([
                'backup_serviceman_id' => $request->backup_serviceman_id,
                'status' => 'ASSIGNED_TO_SERVICEMAN',
            ]);
            \Log::info('Update result: ' . ($updated ? 'SUCCESS' : 'FAILED'));
            
            // Refresh the service request to load relationships
            $serviceRequest->refresh();
            $serviceRequest->load(['serviceman', 'backupServiceman', 'client', 'category']);
            
            \Log::info('New Status: ' . $serviceRequest->status);
            \Log::info('Backup Serviceman ID: ' . ($serviceRequest->backup_serviceman_id ?? 'null'));
            \Log::info('Backup Serviceman loaded: ' . ($serviceRequest->backupServiceman ? 'YES' : 'NO'));

            // Notify primary serviceman (if exists)
            \Log::info('Creating notifications...');
            $clientName = $serviceRequest->client->full_name ?? 'Client';
            $clientPhone = $serviceRequest->client->phone_number ?? 'N/A';
            $clientAddress = $serviceRequest->client_address ?? $serviceRequest->location ?? 'N/A';
            
            // Notify primary serviceman if assigned
            if ($serviceRequest->serviceman) {
                \Log::info('Notifying primary serviceman: ' . $serviceRequest->serviceman->id);
                $this->notificationService->notifyServiceman(
                    $serviceRequest->serviceman,
                    'SERVICE_ASSIGNED',
                    '🎉 New Service Request Assigned!',
                    "You have been assigned service request #{$serviceRequest->id} by {$clientName}. Service: {$serviceRequest->category->name}. Contact client: {$clientPhone}. Location: {$clientAddress}",
                    $serviceRequest
                );
            }

            // Notify backup serviceman if selected - fetch directly to ensure we have the user
            if ($request->backup_serviceman_id) {
                $backupServicemanUser = User::find($request->backup_serviceman_id);
                if ($backupServicemanUser && $backupServicemanUser->isServiceman()) {
                    \Log::info('Notifying backup serviceman: ' . $backupServicemanUser->id);
                    $primaryName = $serviceRequest->serviceman ? $serviceRequest->serviceman->full_name : 'Primary serviceman';
                    $this->notificationService->notifyServiceman(
                        $backupServicemanUser,
                        'BACKUP_SERVICE_ASSIGNED',
                        '🛡️ Backup Service Assignment',
                        "You have been assigned as backup/standby serviceman for service request #{$serviceRequest->id}. " . ($serviceRequest->serviceman ? "Please be ready to assist if the primary serviceman ({$primaryName}) becomes unavailable." : "Please wait for primary serviceman assignment."),
                        $serviceRequest
                    );
                    \Log::info('Backup serviceman notification sent successfully');
                } else {
                    \Log::warning('Backup serviceman not found or not a serviceman: ' . $request->backup_serviceman_id);
                }
            }

            // Notify client
            $backupInfo = $request->backup_serviceman_id ? " A backup serviceman has also been assigned for reliability." : "";
            if ($serviceRequest->serviceman) {
                $this->notificationService->notifyClient(
                    $serviceRequest->client,
                    'SERVICEMAN_ASSIGNED',
                    '✅ Serviceman Assigned to Your Request',
                    "{$serviceRequest->serviceman->full_name} has been assigned to your service request #{$serviceRequest->id}. They will contact you shortly.{$backupInfo}",
                    $serviceRequest
                );
            } else {
                // Only backup assigned, notify client about backup assignment
                $this->notificationService->notifyClient(
                    $serviceRequest->client,
                    'BACKUP_SERVICEMAN_ASSIGNED',
                    '✅ Backup Serviceman Assigned',
                    "A backup serviceman has been assigned to your service request #{$serviceRequest->id}. A primary serviceman will be assigned shortly.",
                    $serviceRequest
                );
            }

            $successMessage = $request->backup_serviceman_id 
                ? 'Service request assigned successfully! Both primary and backup servicemen have been notified.'
                : 'Service request assigned successfully! The serviceman has been notified.';
            
            \Log::info('Assignment completed successfully');
            \Log::info('=== END ASSIGN SERVICEMAN ===');
                
            return redirect()->route('service-requests.show', $serviceRequest)->with('success', $successMessage);

        } elseif ($request->action === 'reject') {
            // Reject the request
            $serviceRequest->update([
                'status' => 'CANCELLED',
            ]);

            // Notify client with detailed rejection reason
            $this->notificationService->notifyClient(
                $serviceRequest->client,
                'REQUEST_REJECTED',
                '❌ Service Request Rejected',
                "Your service request #{$serviceRequest->id} for {$serviceRequest->category->name} has been rejected.\n\nReason: {$request->rejection_reason}\n\nIf you have questions, please contact our support team.",
                $serviceRequest
            );

            return back()->with('success', 'Service request rejected and client has been notified with the reason.');
        }
    }

    public function changeServiceman(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'new_serviceman_id' => 'required|exists:users,id',
            'backup_serviceman_id' => 'nullable|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $newServiceman = User::findOrFail($request->new_serviceman_id);
        
        if (!$newServiceman->isServiceman()) {
            return back()->with('error', 'Selected user is not a serviceman.');
        }

        // Validate new serviceman is available and matches category
        if (!$newServiceman->servicemanProfile || 
            $newServiceman->servicemanProfile->category_id !== $serviceRequest->category_id) {
            return back()->with('error', 'New serviceman does not match the service category.');
        }

        if (!$newServiceman->servicemanProfile->is_available) {
            return back()->with('error', 'New serviceman is not currently available.');
        }

        // Cannot assign same serviceman
        if ($serviceRequest->serviceman_id === $newServiceman->id) {
            return back()->with('error', 'This serviceman is already assigned to this request.');
        }

        // Get old serviceman info BEFORE updating (so we can notify them)
        $oldServicemanId = $serviceRequest->serviceman_id;
        $oldServiceman = $oldServicemanId ? User::find($oldServicemanId) : null;
        $oldServicemanName = $oldServiceman ? $oldServiceman->full_name : 'Unassigned';
        
        // Validate backup if provided
        $backupServiceman = null;
        if ($request->backup_serviceman_id) {
            $backupServiceman = User::findOrFail($request->backup_serviceman_id);
            
            if (!$backupServiceman->isServiceman()) {
                return back()->with('error', 'Selected backup user is not a serviceman.');
            }

            if (!$backupServiceman->servicemanProfile || 
                $backupServiceman->servicemanProfile->category_id !== $serviceRequest->category_id) {
                return back()->with('error', 'Backup serviceman does not match the service category.');
            }

            if (!$backupServiceman->servicemanProfile->is_available) {
                return back()->with('error', 'Backup serviceman is not currently available.');
            }

            if ($newServiceman->id === $backupServiceman->id) {
                return back()->with('error', 'Backup serviceman cannot be the same as the primary serviceman.');
            }
        }

        // Update service request
        $updateData = [
            'serviceman_id' => $newServiceman->id,
            'backup_serviceman_id' => $request->backup_serviceman_id,
            'status' => 'ASSIGNED_TO_SERVICEMAN',
            'accepted_at' => null, // Reset acceptance since it's a new assignment
        ];

        $serviceRequest->update($updateData);
        
        // Refresh to load new relationships and clear cache
        $serviceRequest->refresh();
        $serviceRequest->load(['client', 'category', 'serviceman', 'backupServiceman']);

        // Notify old serviceman if exists (sends email + creates notification)
        // Use the saved oldServiceman reference, not the relationship which is now null
        if ($oldServicemanId && $oldServiceman) {
            \Log::info('Notifying old serviceman about removal', [
                'old_serviceman_id' => $oldServicemanId,
                'old_serviceman_name' => $oldServicemanName,
                'email' => $oldServiceman->email,
            ]);
            
            $this->notificationService->notifyServiceman(
                $oldServiceman,
                'ASSIGNMENT_REMOVED',
                '⚠️ Service Assignment Changed',
                "You have been removed from service request #{$serviceRequest->id}. Admin has reassigned this request to another serviceman." . ($request->reassignment_reason ?? $request->reason ?? "" ? " Reason: " . ($request->reassignment_reason ?? $request->reason ?? '') : ""),
                $serviceRequest,
                ['reason' => $request->reassignment_reason ?? $request->reason ?? 'Admin reassignment']
            );
            
            \Log::info('Old serviceman notification sent');
        } else {
            \Log::warning('Old serviceman not found for notification', [
                'old_serviceman_id' => $oldServicemanId,
                'service_request_id' => $serviceRequest->id,
            ]);
        }

        // Notify new serviceman (sends email + creates notification)
        $clientName = $serviceRequest->client->full_name ?? 'Client';
        $clientPhone = $serviceRequest->client->phone_number ?? 'N/A';
        $clientAddress = $serviceRequest->client_address ?? $serviceRequest->location ?? 'N/A';
        
        \Log::info('Notifying new serviceman about assignment', [
            'new_serviceman_id' => $newServiceman->id,
            'new_serviceman_name' => $newServiceman->full_name,
            'email' => $newServiceman->email,
        ]);
        
        $this->notificationService->notifyServiceman(
            $newServiceman,
            'SERVICE_ASSIGNED',
            '🎉 Service Request Assigned',
            "You have been assigned service request #{$serviceRequest->id} by admin. Service: {$serviceRequest->category->name}. Contact client: {$clientName} at {$clientPhone}. Location: {$clientAddress}",
            $serviceRequest,
            ['client_name' => $clientName, 'client_phone' => $clientPhone, 'client_address' => $clientAddress, 'admin_assigned' => true]
        );
        
        \Log::info('New serviceman notification sent');

        // Notify backup serviceman if assigned (sends email + creates notification)
        if ($backupServiceman) {
            $this->notificationService->notifyServiceman(
                $backupServiceman,
                'BACKUP_SERVICE_ASSIGNED',
                '🛡️ Backup Service Assignment',
                "You have been assigned as backup/standby serviceman for service request #{$serviceRequest->id}. Please be ready to assist if the primary serviceman ({$newServiceman->full_name}) becomes unavailable.",
                $serviceRequest,
                ['primary_serviceman_name' => $newServiceman->full_name]
            );
        }

        // Notify client about serviceman change (sends email + creates notification)
        $backupInfo = $backupServiceman ? " A backup serviceman has also been assigned for reliability." : "";
        $this->notificationService->notifyClient(
            $serviceRequest->client,
            'SERVICEMAN_CHANGED',
            '🔄 Serviceman Changed - Please Check Dashboard',
            "Due to some reasons, the serviceman assigned to your service request #{$serviceRequest->id} has been changed. Your new serviceman is {$newServiceman->full_name}. Please check your dashboard for updated details. The new serviceman will contact you shortly.{$backupInfo}",
            $serviceRequest,
            ['old_serviceman_name' => $oldServicemanName, 'new_serviceman_name' => $newServiceman->full_name, 'has_backup' => $backupServiceman !== null]
        );

        // Notify admin (for logging) (sends email + creates notification)
        $this->notificationService->notifyAdmins(
            'SERVICEMAN_REASSIGNED',
            '👤 Serviceman Reassigned by Admin',
            "Admin " . Auth::user()->full_name . " has reassigned service request #{$serviceRequest->id} from {$oldServicemanName} to {$newServiceman->full_name}." . ($request->reason ? " Reason: {$request->reason}" : ""),
            $serviceRequest,
            ['old_serviceman_name' => $oldServicemanName, 'new_serviceman_name' => $newServiceman->full_name, 'reason' => $request->reason ?? 'Admin reassignment', 'admin_name' => Auth::user()->full_name]
        );

        $successMessage = "Serviceman successfully changed from {$oldServicemanName} to {$newServiceman->full_name}.";
        if ($backupServiceman) {
            $successMessage .= " Backup serviceman also assigned.";
        }
        
        return redirect()->route('service-requests.show', $serviceRequest)->with('success', $successMessage);
    }

    public function submitCostEstimate(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'estimated_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($serviceRequest->status !== 'ASSIGNED_TO_SERVICEMAN') {
            return back()->with('error', 'This service request is not assigned to a serviceman.');
        }

        // Update service request with serviceman's estimate - DON'T calculate final cost yet
        $serviceRequest->update([
            'serviceman_estimated_cost' => $request->estimated_cost,
            'status' => 'SERVICEMAN_INSPECTED',
        ]);

        // ONLY notify admin - client should NOT be notified yet
        $this->notificationService->notifyAdmins(
            'COST_ESTIMATE_SUBMITTED',
            'Cost Estimate Submitted - Review Required',
            "Serviceman {$serviceRequest->serviceman->full_name} has submitted cost estimate of ₦{$request->estimated_cost} for service request #{$serviceRequest->id}. Please review and add your markup before notifying the client.",
            $serviceRequest
        );

        return back()->with('success', 'Cost estimate submitted successfully! Admin will review and notify the client.');
    }

    public function approveCostEstimate(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'admin_markup_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($serviceRequest->status !== 'SERVICEMAN_INSPECTED') {
            return back()->with('error', 'This service request has not been inspected by the serviceman.');
        }

        // Update admin markup if provided
        if ($request->has('admin_markup_percentage')) {
            $serviceRequest->update([
                'admin_markup_percentage' => $request->admin_markup_percentage,
            ]);
        }

        // Calculate final cost with admin markup
        $adminMarkup = $serviceRequest->admin_markup_percentage / 100;
        $finalCost = $serviceRequest->serviceman_estimated_cost + ($serviceRequest->serviceman_estimated_cost * $adminMarkup);
        
        $serviceRequest->update([
            'final_cost' => $finalCost,
            'status' => 'AWAITING_CLIENT_APPROVAL',
        ]);

        // NOW notify the client with the final cost
        $this->notificationService->notifyClient(
            $serviceRequest->client,
            'COST_ESTIMATE_READY',
            'Cost Estimate Ready',
            "Cost estimate for service request #{$serviceRequest->id} is ready: ₦{$finalCost}. Please review and approve to proceed.",
            $serviceRequest
        );

        // Notify admin that client has been notified
        $this->notificationService->notifyAdmins(
            'CLIENT_NOTIFIED_OF_COST',
            'Client Notified of Cost',
            "Client has been notified of the final cost (₦{$finalCost}) for service request #{$serviceRequest->id}. Waiting for client approval.",
            $serviceRequest
        );

        return back()->with('success', 'Cost estimate approved and client notified! Final cost: ₦' . number_format($finalCost));
    }

    public function markWorkCompleted(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->status !== 'WORK_COMPLETED') {
            return back()->with('error', 'Serviceman must mark work as completed first.');
        }

        // Update service request status to COMPLETED
        $serviceRequest->update([
            'status' => 'COMPLETED',
            'work_completed_at' => now(),
        ]);

        // Send rating request notification to client
        $this->notificationService->notifyClient(
            $serviceRequest->client,
            'RATING_REQUEST',
            'Rate Your Experience',
            "Service request #{$serviceRequest->id} has been completed by {$serviceRequest->serviceman->full_name}. Please rate your experience.",
            $serviceRequest,
            [
                'action_url' => route('service-requests.show', $serviceRequest->id),
                'action_text' => 'Rate Service'
            ]
        );

        // Send email notification to client for rating
        try {
            $client = $serviceRequest->client;
            $serviceman = $serviceRequest->serviceman;
            $ratingUrl = route('service-requests.show', $serviceRequest->id) . '#rating-section';
            
            \Mail::raw(
                "Dear {$client->full_name},\n\n" .
                "Your service request #{$serviceRequest->id} has been completed by {$serviceman->full_name}.\n\n" .
                "We would love to hear about your experience! Please take a moment to rate the service.\n\n" .
                "Click here to rate: {$ratingUrl}\n\n" .
                "Thank you for using ServiceMan!\n\n" .
                "Best regards,\nServiceMan Team",
                function ($message) use ($client) {
                    $message->to($client->email)
                           ->subject('Rate Your Service Experience - ServiceMan');
                }
            );
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send rating request email: ' . $e->getMessage());
        }

        return back()->with('success', 'Work marked as completed! Rating request has been sent to the client.');
    }

    public function categoryRequests()
    {
        $categoryRequests = CategoryRequest::with(['serviceman', 'processedBy'])
            ->latest()
            ->paginate(15);

        return view('admin.category-requests', compact('categoryRequests'));
    }

    public function handleCategoryRequest(Request $request, CategoryRequest $categoryRequest)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($categoryRequest->status !== 'PENDING') {
            return back()->with('error', 'This category request has already been processed.');
        }

        if ($request->action === 'approve') {
            // Check if category already exists
            $existingCategory = Category::where('name', $categoryRequest->category_name)->first();
            
            if (!$existingCategory) {
                // Create new category
                $categoryDescription = $categoryRequest->description ?? "Category requested by {$categoryRequest->serviceman->full_name}";
                $category = Category::create([
                    'name' => $categoryRequest->category_name,
                    'description' => $categoryDescription,
                    'is_active' => true,
                ]);
            } else {
                $category = $existingCategory;
            }

            // Update serviceman's profile with the category
            $servicemanProfile = $categoryRequest->serviceman->servicemanProfile;
            if ($servicemanProfile) {
                $servicemanProfile->update([
                    'category_id' => $category->id,
                ]);
            }

            // Update category request
            $categoryRequest->update([
                'status' => 'APPROVED',
                'admin_notes' => $request->admin_notes,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Notify serviceman
            $this->notificationService->notifyServiceman(
                $categoryRequest->serviceman,
                'CATEGORY_REQUEST_APPROVED',
                'Category Request Approved',
                "Your category request for '{$categoryRequest->category_name}' has been approved. You can now accept jobs in this category.",
                null
            );

            return back()->with('success', 'Category request approved successfully!');

        } elseif ($request->action === 'reject') {
            // Update category request
            $categoryRequest->update([
                'status' => 'REJECTED',
                'admin_notes' => $request->admin_notes,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Notify serviceman
            $this->notificationService->notifyServiceman(
                $categoryRequest->serviceman,
                'CATEGORY_REQUEST_REJECTED',
                'Category Request Rejected',
                "Your category request for '{$categoryRequest->category_name}' has been rejected. " . ($request->admin_notes ?? 'Please contact admin for more information.'),
                null
            );

            return back()->with('success', 'Category request rejected.');
        }
    }

    public function notifyServicemanToStart(ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->status !== 'PAYMENT_CONFIRMED') {
            return back()->with('error', 'Service request payment has not been confirmed yet.');
        }

        // Update status to IN_PROGRESS
        $serviceRequest->update([
            'status' => 'IN_PROGRESS',
            'work_started_at' => now(),
        ]);

        // Set primary serviceman as BUSY
        $serviceRequest->serviceman->servicemanProfile->update([
            'is_available' => false,
        ]);

        // Notify primary serviceman
        $this->notificationService->notifyServiceman(
            $serviceRequest->serviceman,
            'START_WORK',
            '🚀 Payment Confirmed - Begin Work Now',
            "The final payment of ₦" . number_format($serviceRequest->final_cost) . " for service request #{$serviceRequest->id} has been received and confirmed by admin. You are cleared to begin work immediately. Client: {$serviceRequest->client->full_name}, Contact: " . ($serviceRequest->client->clientProfile->phone_number ?? 'See request details') . ", Location: {$serviceRequest->location}",
            $serviceRequest
        );

        // Notify backup serviceman if exists
        if ($serviceRequest->backup_serviceman_id && $serviceRequest->backupServiceman) {
            $this->notificationService->notifyServiceman(
                $serviceRequest->backupServiceman,
                'WORK_STARTED',
                '📢 Work Started - Standby',
                "Payment confirmed for service request #{$serviceRequest->id}. Primary serviceman {$serviceRequest->serviceman->full_name} has been notified to begin work. Please standby in case of any issues.",
                $serviceRequest
            );
        }

        return back()->with('success', 'Serviceman has been notified to begin work! Status updated to IN PROGRESS.');
    }

    public function confirmCompletion(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->status !== 'WORK_COMPLETED') {
            return back()->with('error', 'Work has not been marked as completed by serviceman yet.');
        }

        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update status to COMPLETED
        $serviceRequest->update([
            'status' => 'COMPLETED',
            'admin_completion_notes' => $request->admin_notes,
            'admin_verified_at' => now(),
        ]);

        // Set serviceman back to AVAILABLE
        $serviceRequest->serviceman->servicemanProfile->update([
            'is_available' => true,
        ]);

        // Notify client - work verified and completed
        $this->notificationService->notifyClient(
            $serviceRequest->client,
            'WORK_VERIFIED_COMPLETED',
            '✅ Work Completed & Verified',
            "Your service request #{$serviceRequest->id} has been completed by {$serviceRequest->serviceman->full_name} and verified by our admin team. Serviceman's notes: \"{$serviceRequest->completion_notes}\". Please rate your experience.",
            $serviceRequest,
            [
                'action_url' => route('service-requests.show', $serviceRequest->id),
                'action_text' => 'Rate Service'
            ]
        );

        // Notify serviceman - work verified
        $this->notificationService->notifyServiceman(
            $serviceRequest->serviceman,
            'WORK_VERIFIED',
            '✅ Work Verified by Admin',
            "Your completed work for service request #{$serviceRequest->id} has been verified and approved by admin. Client {$serviceRequest->client->full_name} has been notified. Great job!",
            $serviceRequest
        );

        return back()->with('success', 'Work completion verified! Client and serviceman have been notified.');
    }

    /**
     * Create a new admin user
     */
    public function createAdmin(Request $request)
    {
        \Log::info('=== CREATE ADMIN REQUEST ===');
        \Log::info('Request Data: ', $request->all());

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed: ', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        try {
            $admin = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($request->password),
                'user_type' => 'ADMIN',
                'is_email_verified' => true, // Auto-verify admin emails
            ]);

            \Log::info('Admin created successfully: ' . $admin->id);

            // Send notification to the new admin
            $this->notificationService->notifyUser(
                $admin,
                'ADMIN_ACCOUNT_CREATED',
                '🎉 Welcome to ServiceMan Admin',
                "Your admin account has been created successfully. You now have full access to manage the platform. Email: {$admin->email}",
                null
            );

            // Notify the current admin
            $this->notificationService->notifyUser(
                Auth::user(),
                'ADMIN_CREATED',
                '✅ New Admin Created',
                "You have successfully created a new admin account for {$admin->full_name} ({$admin->email}).",
                null
            );

            return redirect()->route('admin.users')->with('success', "Admin account created successfully for {$admin->full_name}!");
        } catch (\Exception $e) {
            \Log::error('Failed to create admin: ' . $e->getMessage());
            return back()->with('error', 'Failed to create admin account. Please try again.')->withInput();
        }
    }

    public function pendingServicemenApproval(Request $request)
    {
        $pendingServicemen = User::where('user_type', 'SERVICEMAN')
            ->where('is_approved', false)
            ->with(['servicemanProfile.category'])
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.pending-servicemen-approval', compact('pendingServicemen'));
    }

    public function approveServiceman(Request $request, User $user)
    {
        if ($user->user_type !== 'SERVICEMAN') {
            return back()->with('error', 'Only servicemen can be approved.');
        }

        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Notify serviceman (email and database)
        $this->notificationService->notifyServiceman(
            $user,
            'ACCOUNT_APPROVED',
            '✅ Account Approved!',
            "Congratulations! Your serviceman account has been approved by admin. You can now login and start accepting jobs.\n\nLogin at: " . url('/login') . "\n\nThank you for joining ServiceMan!",
            null
        );

        return back()->with('success', "Serviceman {$user->full_name} has been approved!");
    }

    public function rejectServiceman(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($user->user_type !== 'SERVICEMAN') {
            return back()->with('error', 'Only servicemen can be rejected.');
        }

        // Notify serviceman before deleting (email and database)
        $this->notificationService->notifyServiceman(
            $user,
            'ACCOUNT_REJECTED',
            '❌ Account Rejected',
            "We regret to inform you that your serviceman account registration has been rejected.\n\nReason: {$request->rejection_reason}\n\nIf you have any questions, please contact us.",
            null
        );

        // Delete the user account
        $userName = $user->full_name;
        $user->delete();

        return back()->with('success', "Serviceman {$userName} has been rejected and removed.");
    }

    public function revokeApproval(User $user)
    {
        if ($user->user_type !== 'SERVICEMAN') {
            return back()->with('error', 'Only servicemen approval can be revoked.');
        }

        if (!$user->is_approved) {
            return back()->with('info', 'This serviceman is already not approved.');
        }

        $user->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        // Notify serviceman (email and database)
        $this->notificationService->notifyServiceman(
            $user,
            'ACCOUNT_APPROVAL_REVOKED',
            '⚠️ Account Approval Revoked',
            "Your serviceman account approval has been revoked by admin. You will no longer be able to login or accept jobs. Please contact support for more information.\n\nIf you believe this is an error, please contact support.",
            null
        );

        return back()->with('success', "Approval for {$user->full_name} has been revoked. They can no longer login.");
    }

    public function testimonials()
    {
        $testimonials = \App\Models\Rating::with(['client', 'serviceman', 'serviceRequest.category'])
            ->whereNotNull('review')
            ->where('review', '!=', '')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.testimonials', compact('testimonials'));
    }

    public function toggleTestimonialFeatured(Request $request, \App\Models\Rating $rating)
    {
        $rating->update([
            'is_featured' => !$rating->is_featured
        ]);

        $status = $rating->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Testimonial has been {$status} successfully.");
    }
}