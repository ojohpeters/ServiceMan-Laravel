<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Category;
use App\Models\AppNotification;
use App\Models\PriceNegotiation;
use App\Models\CategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_assignment' => ServiceRequest::where('status', 'PENDING_ADMIN_ASSIGNMENT')->count(),
            'in_progress' => ServiceRequest::where('status', 'IN_PROGRESS')->count(),
            'completed' => ServiceRequest::where('status', 'COMPLETED')->count(),
            'emergency_requests' => ServiceRequest::where('is_emergency', true)->count(),
            'total_revenue' => ServiceRequest::where('status', 'COMPLETED')->sum('final_cost'),
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
        AppNotification::create([
            'user_id' => $user->id,
            'service_request_id' => null,
            'type' => 'CATEGORY_ASSIGNED',
            'title' => '🎉 Category Assigned - Profile Activated',
            'message' => "Admin has reviewed your profile and assigned you to the '{$category->name}' category. Your profile is now active and you can start receiving service requests!",
            'is_read' => false,
        ]);

        return back()->with('success', "Category '{$category->name}' assigned to {$user->full_name} successfully!");
    }

    public function customServiceRequests()
    {
        $customRequests = \App\Models\CustomServiceRequest::with(['serviceman', 'category', 'reviewer'])
            ->latest()
            ->paginate(20);

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
            AppNotification::create([
                'user_id' => $customServiceRequest->serviceman_id,
                'service_request_id' => null,
                'type' => 'CUSTOM_SERVICE_APPROVED',
                'title' => '🎉 Custom Service Request Approved!',
                'message' => "Great news! Your custom service request for '{$customServiceRequest->service_name}' has been approved. It has been added as '{$category->name}' category.\n\nAdmin's Message: {$request->admin_response}\n\nYou can now apply for this category on your profile to start receiving requests!",
                'is_read' => false,
            ]);

            return back()->with('success', "Custom service approved and serviceman has been notified!");
            
        } elseif ($request->action === 'reject') {
            $customServiceRequest->update([
                'status' => 'REJECTED',
                'admin_response' => $request->admin_response,
                'rejected_at' => now(),
                'reviewed_by' => Auth::id(),
            ]);

            // Notify serviceman with rejection reason
            AppNotification::create([
                'user_id' => $customServiceRequest->serviceman_id,
                'service_request_id' => null,
                'type' => 'CUSTOM_SERVICE_REJECTED',
                'title' => '❌ Custom Service Request Not Approved',
                'message' => "We've reviewed your custom service request for '{$customServiceRequest->service_name}'.\n\nUnfortunately, we cannot add this service at this time.\n\nReason: {$request->admin_response}\n\nYou can submit a new request or contact support for more information.",
                'is_read' => false,
            ]);

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
        AppNotification::create([
            'user_id' => $backupServiceman->id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'BACKUP_ASSIGNMENT',
            'title' => 'Backup Assignment',
            'message' => "You have been assigned as backup serviceman for service request #{$serviceRequest->id}. You may be called if the primary serviceman is unavailable.",
            'is_read' => false,
        ]);

        // Create notification for client
        AppNotification::create([
            'user_id' => $serviceRequest->client_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'BACKUP_ASSIGNED',
            'title' => 'Backup Serviceman Assigned',
            'message' => "A backup serviceman has been assigned to your request #{$serviceRequest->id} to ensure service continuity.",
            'is_read' => false,
        ]);

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
        
        AppNotification::create([
            'user_id' => $serviceRequest->client_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'COST_ESTIMATE_READY',
            'title' => '💰 Final Cost Ready for Approval',
            'message' => "The final cost for service request #{$serviceRequest->id} is ready:\n\n" .
                        "Serviceman Estimate: ₦" . number_format($servicemanEstimate) . "\n" .
                        "Final Cost (with admin fee): ₦" . number_format($finalCost) . "\n\n" .
                        "Please review and approve to proceed with payment.{$notesText}",
            'is_read' => false,
        ]);

        // Notify serviceman that cost has been set
        AppNotification::create([
            'user_id' => $serviceRequest->serviceman_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'COST_APPROVED_BY_ADMIN',
            'title' => '✅ Your Cost Estimate Approved',
            'message' => "Admin has approved your estimate of ₦" . number_format($servicemanEstimate) . " for service request #{$serviceRequest->id}. Final cost to client: ₦" . number_format($finalCost) . ". Waiting for client approval.",
            'is_read' => false,
        ]);

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
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'rating':
                $query->whereHas('servicemanProfile', function($q) {
                    $q->orderBy('rating', 'desc');
                });
                break;
            case 'jobs':
                $query->whereHas('servicemanProfile', function($q) {
                    $q->orderBy('total_jobs_completed', 'desc');
                });
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

        return view('admin.servicemen', compact('servicemen', 'stats', 'categories'));
    }

    public function analytics()
    {
        $analytics = [
            'monthly_revenue' => ServiceRequest::where('status', 'COMPLETED')
                ->whereMonth('work_completed_at', now()->month)
                ->sum('final_cost'),
            'total_servicemen' => User::where('user_type', 'SERVICEMAN')->count(),
            'total_clients' => User::where('user_type', 'CLIENT')->count(),
            'average_rating' => \DB::table('ratings')->avg('rating'),
            'completion_rate' => ServiceRequest::where('status', 'COMPLETED')->count() / 
                               max(ServiceRequest::count(), 1) * 100,
        ];

        $categoryStats = Category::withCount(['serviceRequests' => function($query) {
            $query->where('status', 'COMPLETED');
        }])->get();

        $monthlyStats = ServiceRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(final_cost) as revenue')
            ->where('status', 'COMPLETED')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->get();

        return view('admin.analytics', compact('analytics', 'categoryStats', 'monthlyStats'));
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
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'NEGOTIATION_ACCEPTED',
                'title' => 'Negotiation Accepted',
                'message' => "Your price negotiation has been accepted! Final cost: ₦{$negotiation->proposed_amount}. You can now proceed with payment.",
                'is_read' => false,
            ]);

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
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'NEGOTIATION_REJECTED',
                'title' => 'Negotiation Rejected',
                'message' => "Your price negotiation has been rejected. The original cost of ₦{$serviceRequest->final_cost} still applies. " . ($request->admin_response ?? 'Please contact admin for more information.'),
                'is_read' => false,
            ]);

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
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'NEGOTIATION_COUNTERED',
                'title' => 'Counter Offer Received',
                'message' => "Admin has made a counter offer: ₦{$request->counter_amount}. " . ($request->admin_response ?? 'Please review and respond.'),
                'is_read' => false,
            ]);

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
            \Log::info('New Status: ' . $serviceRequest->fresh()->status);

            // Notify primary serviceman
            \Log::info('Creating notifications...');
            $clientName = $serviceRequest->client->full_name ?? 'Client';
            $clientPhone = $serviceRequest->client->phone_number ?? 'N/A';
            $clientAddress = $serviceRequest->client_address ?? $serviceRequest->location ?? 'N/A';
            
            AppNotification::create([
                'user_id' => $serviceRequest->serviceman_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'SERVICE_ASSIGNED',
                'title' => '🎉 New Service Request Assigned!',
                'message' => "You have been assigned service request #{$serviceRequest->id} by {$clientName}. Service: {$serviceRequest->category->name}. Contact client: {$clientPhone}. Location: {$clientAddress}",
                'is_read' => false,
            ]);

            // Notify backup serviceman if selected
            if ($request->backup_serviceman_id) {
                AppNotification::create([
                    'user_id' => $request->backup_serviceman_id,
                    'service_request_id' => $serviceRequest->id,
                    'type' => 'BACKUP_SERVICE_ASSIGNED',
                    'title' => '🛡️ Backup Service Assignment',
                    'message' => "You have been assigned as backup/standby serviceman for service request #{$serviceRequest->id}. Please be ready to assist if the primary serviceman ({$serviceRequest->serviceman->full_name}) becomes unavailable.",
                    'is_read' => false,
                ]);
            }

            // Notify client
            $backupInfo = $request->backup_serviceman_id ? " A backup serviceman has also been assigned for reliability." : "";
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'SERVICEMAN_ASSIGNED',
                'title' => '✅ Serviceman Assigned to Your Request',
                'message' => "{$serviceRequest->serviceman->full_name} has been assigned to your service request #{$serviceRequest->id}. They will contact you shortly.{$backupInfo}",
                'is_read' => false,
            ]);

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
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'REQUEST_REJECTED',
                'title' => '❌ Service Request Rejected',
                'message' => "Your service request #{$serviceRequest->id} for {$serviceRequest->category->name} has been rejected.\n\nReason: {$request->rejection_reason}\n\nIf you have questions, please contact our support team.",
                'is_read' => false,
            ]);

            return back()->with('success', 'Service request rejected and client has been notified with the reason.');
        }
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
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'COST_ESTIMATE_SUBMITTED',
            'title' => 'Cost Estimate Submitted - Review Required',
            'message' => "Serviceman {$serviceRequest->serviceman->full_name} has submitted cost estimate of ₦{$request->estimated_cost} for service request #{$serviceRequest->id}. Please review and add your markup before notifying the client.",
            'is_read' => false,
        ]);

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
        AppNotification::create([
            'user_id' => $serviceRequest->client_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'COST_ESTIMATE_READY',
            'title' => 'Cost Estimate Ready',
            'message' => "Cost estimate for service request #{$serviceRequest->id} is ready: ₦{$finalCost}. Please review and approve to proceed.",
            'is_read' => false,
        ]);

        // Notify admin that client has been notified
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'CLIENT_NOTIFIED_OF_COST',
            'title' => 'Client Notified of Cost',
            'message' => "Client has been notified of the final cost (₦{$finalCost}) for service request #{$serviceRequest->id}. Waiting for client approval.",
            'is_read' => false,
        ]);

        return back()->with('success', 'Cost estimate approved and client notified! Final cost: ₦' . number_format($finalCost));
    }

    public function markWorkCompleted(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->status !== 'IN_PROGRESS') {
            return back()->with('error', 'This service request is not in progress.');
        }

        // Update service request status
        $serviceRequest->update([
            'status' => 'COMPLETED',
            'work_completed_at' => now(),
        ]);

        // Notify admin
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'WORK_COMPLETED',
            'title' => 'Work Completed',
            'message' => "Serviceman {$serviceRequest->serviceman->full_name} has completed work for service request #{$serviceRequest->id}. Please contact the client to confirm completion.",
            'is_read' => false,
        ]);

        // Notify client
        AppNotification::create([
            'user_id' => $serviceRequest->client_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'SERVICE_COMPLETED',
            'title' => 'Service Completed',
            'message' => "Your service request #{$serviceRequest->id} has been completed by {$serviceRequest->serviceman->full_name}. Please rate your experience.",
            'is_read' => false,
        ]);

        return back()->with('success', 'Work marked as completed successfully!');
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
            AppNotification::create([
                'user_id' => $categoryRequest->serviceman_id,
                'service_request_id' => null,
                'type' => 'CATEGORY_REQUEST_APPROVED',
                'title' => 'Category Request Approved',
                'message' => "Your category request for '{$categoryRequest->category_name}' has been approved. You can now accept jobs in this category.",
                'is_read' => false,
            ]);

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
            AppNotification::create([
                'user_id' => $categoryRequest->serviceman_id,
                'service_request_id' => null,
                'type' => 'CATEGORY_REQUEST_REJECTED',
                'title' => 'Category Request Rejected',
                'message' => "Your category request for '{$categoryRequest->category_name}' has been rejected. " . ($request->admin_notes ?? 'Please contact admin for more information.'),
                'is_read' => false,
            ]);

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
        AppNotification::create([
            'user_id' => $serviceRequest->serviceman_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'START_WORK',
            'title' => '🚀 Payment Confirmed - Begin Work Now',
            'message' => "The final payment of ₦" . number_format($serviceRequest->final_cost) . " for service request #{$serviceRequest->id} has been received and confirmed by admin. You are cleared to begin work immediately. Client: {$serviceRequest->client->full_name}, Contact: " . ($serviceRequest->client->clientProfile->phone_number ?? 'See request details') . ", Location: {$serviceRequest->location}",
            'is_read' => false,
        ]);

        // Notify backup serviceman if exists
        if ($serviceRequest->backup_serviceman_id) {
            AppNotification::create([
                'user_id' => $serviceRequest->backup_serviceman_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'WORK_STARTED',
                'title' => '📢 Work Started - Standby',
                'message' => "Payment confirmed for service request #{$serviceRequest->id}. Primary serviceman {$serviceRequest->serviceman->full_name} has been notified to begin work. Please standby in case of any issues.",
                'is_read' => false,
            ]);
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
        AppNotification::create([
            'user_id' => $serviceRequest->client_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'WORK_VERIFIED_COMPLETED',
            'title' => '✅ Work Completed & Verified',
            'message' => "Your service request #{$serviceRequest->id} has been completed by {$serviceRequest->serviceman->full_name} and verified by our admin team. Serviceman's notes: \"{$serviceRequest->completion_notes}\". Please rate your experience.",
            'is_read' => false,
        ]);

        // Notify serviceman - work verified
        AppNotification::create([
            'user_id' => $serviceRequest->serviceman_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'WORK_VERIFIED',
            'title' => '✅ Work Verified by Admin',
            'message' => "Your completed work for service request #{$serviceRequest->id} has been verified and approved by admin. Client {$serviceRequest->client->full_name} has been notified. Great job!",
            'is_read' => false,
        ]);

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
            AppNotification::create([
                'user_id' => $admin->id,
                'type' => 'ADMIN_ACCOUNT_CREATED',
                'title' => '🎉 Welcome to ServiceMan Admin',
                'message' => "Your admin account has been created successfully. You now have full access to manage the platform. Email: {$admin->email}",
                'is_read' => false,
            ]);

            // Notify the current admin
            AppNotification::create([
                'user_id' => Auth::id(),
                'type' => 'ADMIN_CREATED',
                'title' => '✅ New Admin Created',
                'message' => "You have successfully created a new admin account for {$admin->full_name} ({$admin->email}).",
                'is_read' => false,
            ]);

            return redirect()->route('admin.users')->with('success', "Admin account created successfully for {$admin->full_name}!");
        } catch (\Exception $e) {
            \Log::error('Failed to create admin: ' . $e->getMessage());
            return back()->with('error', 'Failed to create admin account. Please try again.')->withInput();
        }
    }

    public function pendingServicemenApproval()
    {
        $pendingServicemen = User::where('user_type', 'SERVICEMAN')
            ->where('is_approved', false)
            ->with('servicemanProfile.category')
            ->latest()
            ->get();

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

        // Notify serviceman
        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'ACCOUNT_APPROVED',
            'title' => '✅ Account Approved!',
            'message' => "Congratulations! Your serviceman account has been approved by admin. You can now login and start accepting jobs.",
            'is_read' => false,
        ]);

        // Send email notification
        try {
            \Mail::raw(
                "Dear {$user->full_name},\n\nYour serviceman account has been approved! You can now login and start accepting service requests.\n\nLogin at: " . url('/login') . "\n\nThank you for joining ServiceMan!",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('ServiceMan Account Approved');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send approval email: ' . $e->getMessage());
        }

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

        // Notify serviceman before deleting
        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'ACCOUNT_REJECTED',
            'title' => '❌ Account Rejected',
            'message' => "Your serviceman account registration has been rejected. Reason: {$request->rejection_reason}",
            'is_read' => false,
        ]);

        // Send email notification
        try {
            \Mail::raw(
                "Dear {$user->full_name},\n\nWe regret to inform you that your serviceman account registration has been rejected.\n\nReason: {$request->rejection_reason}\n\nIf you have any questions, please contact us.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('ServiceMan Account Rejected');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send rejection email: ' . $e->getMessage());
        }

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

        // Notify serviceman
        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'ACCOUNT_APPROVAL_REVOKED',
            'title' => '⚠️ Account Approval Revoked',
            'message' => "Your serviceman account approval has been revoked by admin. You will no longer be able to login or accept jobs. Please contact support for more information.",
            'is_read' => false,
        ]);

        // Send email notification
        try {
            \Mail::raw(
                "Dear {$user->full_name},\n\nYour serviceman account approval has been revoked. You will no longer be able to login or accept service requests.\n\nIf you believe this is an error, please contact support.\n\nThank you.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('ServiceMan Account Approval Revoked');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send approval revocation email: ' . $e->getMessage());
        }

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