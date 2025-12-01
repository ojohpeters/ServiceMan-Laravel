<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Category;
use App\Models\User;
use App\Models\AppNotification;
use App\Models\PriceNegotiation;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isClient()) {
            $query = ServiceRequest::where('client_id', $user->id)
                ->with(['serviceman', 'category', 'payments']);
                
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if ($request->filled('emergency')) {
                $query->where('is_emergency', $request->emergency);
            }
            
            $serviceRequests = $query->latest()->paginate(10);
        } elseif ($user->isServiceman()) {
            // Only show requests that have been actually assigned to serviceman (not PENDING_ADMIN_ASSIGNMENT)
            $assignedStatuses = ['ASSIGNED_TO_SERVICEMAN', 'SERVICEMAN_INSPECTED', 'AWAITING_CLIENT_APPROVAL', 'NEGOTIATING', 'AWAITING_PAYMENT', 'PAYMENT_CONFIRMED', 'IN_PROGRESS', 'WORK_COMPLETED', 'COMPLETED'];
            
            $query = ServiceRequest::where(function($q) use ($user) {
                    $q->where('serviceman_id', $user->id)
                      ->orWhere('backup_serviceman_id', $user->id);
                })
                ->whereIn('status', $assignedStatuses)
                ->with(['client', 'category', 'payments']);
                
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            
            $serviceRequests = $query->latest()->paginate(10);
        } else {
            // Admin view
            $query = ServiceRequest::with(['client', 'serviceman', 'category', 'payments']);
            
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if ($request->filled('emergency')) {
                $query->where('is_emergency', $request->emergency);
            }
            
            $serviceRequests = $query->latest()->paginate(10);
        }

        return view('service-requests.index', compact('serviceRequests'));
    }

    public function create(Request $request)
    {
        // Only clients can access the booking form
        if (!Auth::user()->isClient()) {
            return redirect()->route('dashboard')->with('error', 'Only clients can book services.');
        }
        
        $categories = Category::where('is_active', true)->get();
        $selectedServiceman = null;
        $selectedCategory = null;
        
        // If serviceman is pre-selected from category page
        if ($request->has('serviceman_id')) {
            $selectedServiceman = User::with(['servicemanProfile.category', 'ratingsReceived'])
                ->where('id', $request->serviceman_id)
                ->where('user_type', 'SERVICEMAN')
                ->first();
            
            if ($selectedServiceman && $selectedServiceman->servicemanProfile) {
                $selectedCategory = $selectedServiceman->servicemanProfile->category;
            }
        }
        
        return view('service-requests.create', compact('categories', 'selectedServiceman', 'selectedCategory'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only clients can create service requests
        if (!$user->isClient()) {
            abort(403, 'Only clients can book services.');
        }
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'serviceman_id' => 'required|exists:users,id',
            'booking_date' => 'required|date|after:today',
            'is_emergency' => 'boolean',
            'client_address' => 'required|string|max:500',
            'service_description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Verify serviceman exists and is available
        $serviceman = User::where('id', $request->serviceman_id)
            ->where('user_type', 'SERVICEMAN')
            ->whereHas('servicemanProfile', function($query) use ($request) {
                $query->where('category_id', $request->category_id)
                      ->where('is_available', true);
            })
            ->first();

        if (!$serviceman) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected serviceman is not available for this category.'
                ], 400);
            }
            return back()->withErrors(['serviceman_id' => 'Selected serviceman is not available for this category.'])->withInput();
        }
        
        // Check if emergency based on booking date (within 2 days from today)
        $bookingDate = Carbon::parse($request->booking_date);
        $today = Carbon::today();
        $daysUntilBooking = $today->diffInDays($bookingDate, false); // false = signed difference
        
        // Auto-flag as emergency if booking is within 2 days OR if manually flagged
        $autoFlaggedEmergency = $daysUntilBooking >= 0 && $daysUntilBooking <= 2;
        $isEmergency = $request->boolean('is_emergency') || $autoFlaggedEmergency;
        $bookingFee = $isEmergency ? 5000 : 2000;
        
        \Log::info("Booking Date Calculation:", [
            'booking_date' => $bookingDate->toDateString(),
            'today' => $today->toDateString(),
            'days_until_booking' => $daysUntilBooking,
            'auto_flagged_emergency' => $autoFlaggedEmergency,
            'is_emergency_checkbox' => $request->boolean('is_emergency'),
            'final_is_emergency' => $isEmergency,
            'booking_fee' => $bookingFee
        ]);
        
        // Store booking data in session to create service request after payment
        session([
            'pending_booking' => [
                'client_id' => $user->id,
                'serviceman_id' => $serviceman->id,
                'category_id' => $request->category_id,
                'booking_date' => $request->booking_date,
                'is_emergency' => $isEmergency,
                'auto_flagged_emergency' => $autoFlaggedEmergency,
                'client_address' => $request->client_address,
                'service_description' => $request->service_description,
                'serviceman_name' => $serviceman->full_name,
                'category_name' => $serviceman->servicemanProfile->category->name ?? 'Service',
                'booking_fee' => $bookingFee
            ]
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proceeding to payment...',
                'redirect_url' => route('service-requests.pay-booking-fee')
            ]);
        }

        // Redirect to payment page instead of creating request
        return redirect()->route('service-requests.pay-booking-fee')
            ->with('info', 'Please complete the booking fee payment to confirm your service request.');
    }

    public function showPaymentPage()
    {
        // Check if there's pending booking data
        $pendingBooking = session('pending_booking');
        
        if (!$pendingBooking) {
            return redirect()->route('services')->with('error', 'No pending booking found. Please start a new booking.');
        }

        return view('service-requests.payment', compact('pendingBooking'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['client', 'serviceman', 'category', 'payments', 'negotiations', 'rating']);
        
        // Check if user has access to this request
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // Admins can view all requests
        } elseif ($user->isClient()) {
            // Clients can only view their own requests
            if ($serviceRequest->client_id !== $user->id) {
                abort(403);
            }
        } elseif ($user->isServiceman()) {
            // Servicemen can only view requests that have been actually assigned to them
            $assignedStatuses = ['ASSIGNED_TO_SERVICEMAN', 'SERVICEMAN_INSPECTED', 'AWAITING_CLIENT_APPROVAL', 'NEGOTIATING', 'AWAITING_PAYMENT', 'PAYMENT_CONFIRMED', 'IN_PROGRESS', 'COMPLETED'];
            
            if (($serviceRequest->serviceman_id !== $user->id && $serviceRequest->backup_serviceman_id !== $user->id) ||
                !in_array($serviceRequest->status, $assignedStatuses)) {
                abort(403, 'You do not have access to this service request.');
            }
        } else {
            abort(403);
        }

        // Get status message for UI
        $statusMessage = $this->getStatusMessage($serviceRequest->status);
        $nextSteps = $this->getNextSteps($serviceRequest, $user);

        // Get available servicemen for this category (for admin assignment modal)
        $availableServicemen = [];
        if ($user->isAdmin() && $serviceRequest->category_id) {
            $availableServicemen = User::where('user_type', 'SERVICEMAN')
                ->whereHas('servicemanProfile', function($query) use ($serviceRequest) {
                    $query->where('category_id', $serviceRequest->category_id);
                })
                ->with('servicemanProfile')
                ->get()
                ->map(function($serviceman) {
                    return [
                        'id' => $serviceman->id,
                        'full_name' => $serviceman->full_name,
                        'experience_years' => $serviceman->servicemanProfile->experience_years ?? 0,
                        'rating' => $serviceman->servicemanProfile->rating ?? 0,
                        'is_available' => $serviceman->servicemanProfile->is_available ?? true,
                    ];
                });
        }

        return view('service-requests.show', compact('serviceRequest', 'statusMessage', 'nextSteps', 'availableServicemen'));
    }

    public function edit(ServiceRequest $serviceRequest)
    {
        // Only client can edit their own requests and only if pending
        if ($serviceRequest->client_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->status !== 'PENDING_ADMIN_ASSIGNMENT') {
            return back()->with('error', 'Cannot edit service request that is not pending.');
        }

        $categories = Category::where('is_active', true)->get();
        return view('service-requests.edit', compact('serviceRequest', 'categories'));
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        // Only client can edit their own requests
        if ($serviceRequest->client_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->status !== 'PENDING_ADMIN_ASSIGNMENT') {
            return back()->with('error', 'Cannot edit service request that is not pending.');
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'booking_date' => 'required|date|after:today',
            'is_emergency' => 'boolean',
            'client_address' => 'required|string|max:500',
            'service_description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Recalculate emergency status and booking fee
        $bookingDate = Carbon::parse($request->booking_date);
        $isEmergency = $request->has('is_emergency') || $bookingDate->diffInDays(Carbon::today()) <= 2;

        $serviceRequest->update([
            'category_id' => $request->category_id,
            'booking_date' => $request->booking_date,
            'is_emergency' => $isEmergency,
            'auto_flagged_emergency' => $bookingDate->diffInDays(Carbon::today()) <= 2,
            'initial_booking_fee' => $isEmergency ? 5000 : 2000,
            'client_address' => $request->client_address,
            'service_description' => $request->service_description,
        ]);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request updated successfully!');
    }

    public function submitEstimate(Request $request, ServiceRequest $serviceRequest)
    {
        // Only assigned serviceman can submit estimate
        if ($serviceRequest->serviceman_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'serviceman_estimated_cost' => 'required|numeric|min:0',
            'inspection_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $serviceRequest->update([
            'serviceman_estimated_cost' => $request->serviceman_estimated_cost,
            'status' => 'SERVICEMAN_INSPECTED',
            'inspection_completed_at' => now(),
        ]);

        // Notify ADMIN for review (not the client yet - admin adds markup first)
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'COST_ESTIMATE_SUBMITTED',
            'title' => '💰 Cost Estimate Submitted - Review Required',
            'message' => "Serviceman {$serviceRequest->serviceman->full_name} has submitted a cost estimate of ₦" . number_format($request->serviceman_estimated_cost) . " for service request #{$serviceRequest->id}. Please review and add your markup before notifying the client.",
            'is_read' => false,
        ]);

        return back()->with('success', 'Cost estimate submitted successfully! Admin will review and notify the client.');
    }

    public function markComplete(Request $request, ServiceRequest $serviceRequest)
    {
        // Only assigned serviceman can mark as complete
        if ($serviceRequest->serviceman_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'completion_notes' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update to WORK_COMPLETED status (admin will change to COMPLETED after notifying client)
        $serviceRequest->update([
            'status' => 'WORK_COMPLETED',
            'work_completed_at' => now(),
        ]);

        // Notify ADMIN ONLY - admin will then notify client professionally
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'WORK_COMPLETED',
            'title' => '✅ Work Completed - Action Required',
            'message' => "Serviceman {$serviceRequest->serviceman->full_name} has marked service request #{$serviceRequest->id} as completed. Notes: \"{$request->completion_notes}\". Please verify and notify client {$serviceRequest->client->full_name}.",
            'is_read' => false,
        ]);

        return back()->with('success', 'Work marked as completed! Admin will verify and notify the client.');
    }

    public function acceptCost(ServiceRequest $serviceRequest)
    {
        // Only client can accept cost
        if ($serviceRequest->client_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->status !== 'AWAITING_CLIENT_APPROVAL') {
            return back()->with('error', 'Invalid action for current status.');
        }

        $serviceRequest->update([
            'status' => 'AWAITING_PAYMENT',
        ]);

        // Notify serviceman that client accepted the cost
        AppNotification::create([
            'user_id' => $serviceRequest->serviceman_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'COST_APPROVED',
            'title' => '✅ Client Approved Cost Estimate',
            'message' => "Client {$serviceRequest->client->full_name} has approved the cost estimate of ₦" . number_format($serviceRequest->final_cost) . " for service request #{$serviceRequest->id}. Waiting for client payment to begin work.",
            'is_read' => false,
        ]);

        // Notify admin
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'COST_APPROVED',
            'title' => '💵 Cost Approved - Awaiting Payment',
            'message' => "Client {$serviceRequest->client->full_name} has approved the final cost of ₦" . number_format($serviceRequest->final_cost) . " for service request #{$serviceRequest->id}. Now awaiting payment.",
            'is_read' => false,
        ]);

        return back()->with('success', 'Cost accepted! Please proceed with final payment.');
    }


    public function submitRating(Request $request, ServiceRequest $serviceRequest)
    {
        // Only client can rate
        if ($serviceRequest->client_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->status !== 'COMPLETED') {
            return back()->with('error', 'Can only rate completed services.');
        }

        if ($serviceRequest->rating) {
            return back()->with('error', 'You have already rated this service.');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $rating = Rating::create([
            'service_request_id' => $serviceRequest->id,
            'serviceman_id' => $serviceRequest->serviceman_id,
            'client_id' => Auth::id(),
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        // Update serviceman's profile
        $serviceman = $serviceRequest->serviceman;
        if ($serviceman && $serviceman->servicemanProfile) {
            $profile = $serviceman->servicemanProfile;
            $totalRatings = $serviceman->ratingsReceived()->count();
            $averageRating = $serviceman->ratingsReceived()->avg('rating');
            
            $profile->update([
                'total_jobs_completed' => $totalRatings,
                'rating' => round($averageRating, 1),
            ]);
        }

        // Generate star display for notifications
        $stars = str_repeat('⭐', $request->rating) . str_repeat('☆', 5 - $request->rating);
        $reviewText = $request->review ? " Review: \"{$request->review}\"" : "";

        // Notify serviceman about the rating
        AppNotification::create([
            'user_id' => $serviceRequest->serviceman_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'RATING_RECEIVED',
            'title' => '⭐ New Rating Received',
            'message' => "Client {$serviceRequest->client->full_name} rated your work on service request #{$serviceRequest->id}: {$stars} ({$request->rating}/5).{$reviewText} Your new average rating is {$averageRating}/5.0.",
            'is_read' => false,
        ]);

        // Notify admin about the rating
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'RATING_SUBMITTED',
            'title' => '⭐ Rating Submitted - Service Request #' . $serviceRequest->id,
            'message' => "Client {$serviceRequest->client->full_name} rated serviceman {$serviceRequest->serviceman->full_name}: {$stars} ({$request->rating}/5) for service request #{$serviceRequest->id}.{$reviewText}",
            'is_read' => false,
        ]);

        return back()->with('success', 'Thank you for your rating! The serviceman has been notified.');
    }

    private function getStatusMessage($status)
    {
        $messages = [
            'PENDING_ADMIN_ASSIGNMENT' => 'Waiting for admin to assign a serviceman',
            'ASSIGNED_TO_SERVICEMAN' => 'Serviceman assigned, waiting for inspection',
            'SERVICEMAN_INSPECTED' => 'Inspection complete, reviewing cost estimate',
            'AWAITING_CLIENT_APPROVAL' => 'Please review and approve the final cost',
            'NEGOTIATING' => 'Price negotiation in progress',
            'AWAITING_PAYMENT' => 'Please complete final payment',
            'PAYMENT_CONFIRMED' => 'Payment confirmed, service will begin soon',
            'IN_PROGRESS' => 'Service in progress',
            'COMPLETED' => 'Service completed - please rate your experience',
            'CANCELLED' => 'Service request cancelled',
        ];

        return $messages[$status] ?? 'Unknown status';
    }

    private function getNextSteps(ServiceRequest $serviceRequest, $user)
    {
        $steps = [];

        if ($user->isClient()) {
            switch ($serviceRequest->status) {
                case 'PENDING_ADMIN_ASSIGNMENT':
                    $steps[] = 'Pay booking fee to proceed';
                    break;
                case 'AWAITING_CLIENT_APPROVAL':
                    $steps[] = 'Review and approve the cost estimate';
                    $steps[] = 'Or negotiate the price if needed';
                    break;
                case 'AWAITING_PAYMENT':
                    $steps[] = 'Complete final payment';
                    break;
                case 'COMPLETED':
                    $steps[] = 'Rate your experience with the serviceman';
                    break;
            }
        } elseif ($user->isServiceman()) {
            switch ($serviceRequest->status) {
                case 'ASSIGNED_TO_SERVICEMAN':
                    $steps[] = 'Visit the site and conduct inspection';
                    $steps[] = 'Submit cost estimate';
                    break;
                case 'IN_PROGRESS':
                    $steps[] = 'Complete the service work';
                    $steps[] = 'Mark as completed when done';
                    break;
            }
        } elseif ($user->isAdmin()) {
            switch ($serviceRequest->status) {
                case 'PENDING_ADMIN_ASSIGNMENT':
                    $steps[] = 'Assign a suitable serviceman';
                    break;
                case 'SERVICEMAN_INSPECTED':
                    $steps[] = 'Review serviceman estimate';
                    $steps[] = 'Set final cost with markup';
                    break;
                case 'NEGOTIATING':
                    $steps[] = 'Review negotiation proposals';
                    $steps[] = 'Accept or counter the offer';
                    break;
            }
        }

        return $steps;
    }

    public function createNegotiation(Request $request, ServiceRequest $serviceRequest)
    {
        \Log::info('=== CREATE NEGOTIATION CALLED ===');
        \Log::info('Service Request ID: ' . $serviceRequest->id);
        \Log::info('Client ID: ' . $serviceRequest->client_id);
        \Log::info('Auth User ID: ' . Auth::id());
        \Log::info('Current Status: ' . $serviceRequest->status);
        \Log::info('Request Data: ', $request->all());

        // Only client can create negotiation
        if ($serviceRequest->client_id !== Auth::id()) {
            \Log::error('Authorization failed: User is not the client');
            abort(403, 'You can only negotiate for your own service requests.');
        }

        // Only allow negotiation when awaiting client approval
        if ($serviceRequest->status !== 'AWAITING_CLIENT_APPROVAL') {
            \Log::error('Status check failed: ' . $serviceRequest->status);
            return back()->with('error', 'Negotiation can only be initiated when awaiting client approval.');
        }

        $validator = Validator::make($request->all(), [
            'proposed_amount' => 'required|numeric|min:1|max:' . $serviceRequest->final_cost,
            'negotiation_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed: ', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        \Log::info('Validation passed, creating negotiation...');

        // Create price negotiation record
        $negotiation = \App\Models\PriceNegotiation::create([
            'service_request_id' => $serviceRequest->id,
            'proposed_by' => Auth::id(),
            'proposed_amount' => $request->proposed_amount,
            'reason' => $request->negotiation_reason,
            'status' => 'PENDING',
        ]);

        // Update service request status
        $serviceRequest->update([
            'status' => 'NEGOTIATING',
        ]);

        // Calculate difference and percentage
        $currentCost = $serviceRequest->final_cost;
        $proposedCost = $request->proposed_amount;
        $difference = $currentCost - $proposedCost;
        $percentageReduction = round(($difference / $currentCost) * 100, 1);

        // Create notification for admin - PRIORITY
        \Log::info('Creating admin notification...');
        $adminNotification = AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => $serviceRequest->id,
            'type' => 'PRICE_NEGOTIATION_REQUESTED',
            'title' => '💬 Price Negotiation Request - Action Required',
            'message' => "Client {$serviceRequest->client->full_name} wants to negotiate service request #{$serviceRequest->id}. Current: ₦" . number_format($currentCost) . " → Proposed: ₦" . number_format($proposedCost) . " ({$percentageReduction}% reduction). Reason: \"{$request->negotiation_reason}\". Please review and respond.",
            'is_read' => false,
        ]);
        \Log::info('Admin notification created: ID = ' . $adminNotification->id);

        // Notify serviceman about negotiation
        if ($serviceRequest->serviceman_id) {
            AppNotification::create([
                'user_id' => $serviceRequest->serviceman_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'NEGOTIATION_STARTED',
                'title' => '💬 Client Requested Price Negotiation',
                'message' => "Client {$serviceRequest->client->full_name} is negotiating the price for service request #{$serviceRequest->id}. Proposed: ₦" . number_format($proposedCost) . " (down from ₦" . number_format($currentCost) . "). Admin will review and respond.",
                'is_read' => false,
            ]);
        }

        // Create notification for client - confirmation
        AppNotification::create([
            'user_id' => $serviceRequest->client_id,
            'service_request_id' => $serviceRequest->id,
            'type' => 'NEGOTIATION_SUBMITTED',
            'title' => '✅ Negotiation Request Submitted',
            'message' => "Your price negotiation request has been submitted successfully. Proposed amount: ₦" . number_format($request->proposed_amount) . " (current: ₦" . number_format($currentCost) . "). Our admin team will review and respond shortly.",
            'is_read' => false,
        ]);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Price negotiation request submitted successfully! Admin will review and respond shortly.');
    }
}