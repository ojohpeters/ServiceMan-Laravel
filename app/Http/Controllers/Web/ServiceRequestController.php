<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Category;
use App\Models\User;
use App\Models\PriceNegotiation;
use App\Models\Rating;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
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
        
        // Check if serviceman is busy on the booking date
        if ($serviceman->isBusyOnDate($bookingDate)) {
            $dateFormatted = $bookingDate->format('l, F j, Y');
            $errorMessage = "⚠️ WARNING: This serviceman is marked as BUSY/UNAVAILABLE on {$dateFormatted}. Please select a different date to proceed with booking.";
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'busy_date' => $bookingDate->format('Y-m-d')
                ], 400);
            }
            return back()->withErrors([
                'booking_date' => $errorMessage
            ])->withInput();
        }
        
        // Also check if serviceman is available today (for current status)
        if ($serviceman->isBusyOnDate(Carbon::today())) {
            // Log warning but don't block booking for future dates
            \Log::info("Serviceman {$serviceman->id} is busy today but booking is for future date {$bookingDate->format('Y-m-d')}");
        }
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
            // Clients can only view their own requests - use loose comparison to handle type mismatches
            if ((int)$serviceRequest->client_id !== (int)$user->id) {
                \Log::warning('Access denied to service request', [
                    'service_request_id' => $serviceRequest->id,
                    'client_id' => $serviceRequest->client_id,
                    'user_id' => $user->id,
                    'client_id_type' => gettype($serviceRequest->client_id),
                    'user_id_type' => gettype($user->id),
                ]);
                abort(403, 'You do not have access to this service request.');
            }
        } elseif ($user->isServiceman()) {
            // Servicemen can only view requests that have been actually assigned to them
            $assignedStatuses = ['ASSIGNED_TO_SERVICEMAN', 'SERVICEMAN_INSPECTED', 'AWAITING_CLIENT_APPROVAL', 'NEGOTIATING', 'AWAITING_PAYMENT', 'PAYMENT_CONFIRMED', 'IN_PROGRESS', 'WORK_COMPLETED', 'COMPLETED'];
            
            if (((int)$serviceRequest->serviceman_id !== (int)$user->id && (int)$serviceRequest->backup_serviceman_id !== (int)$user->id) ||
                !in_array($serviceRequest->status, $assignedStatuses)) {
                abort(403, 'You do not have access to this service request.');
            }
        } else {
            abort(403, 'Access denied.');
        }

        // Ensure all relationships are loaded fresh
        $serviceRequest->load(['client', 'serviceman', 'backupServiceman', 'category', 'payments', 'rating']);
        
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

    public function acceptAssignment(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
        // Only assigned serviceman (primary or backup who is now primary) can accept
        $isPrimaryServiceman = $serviceRequest->serviceman_id === $user->id;
        $isBackupServiceman = $serviceRequest->backup_serviceman_id === $user->id;
        
        if (!$isPrimaryServiceman && !$isBackupServiceman) {
            abort(403, 'You are not assigned to this service request.');
        }

        if ($serviceRequest->status !== 'ASSIGNED_TO_SERVICEMAN') {
            return back()->with('error', 'Invalid action for current status.');
        }

        // If backup serviceman is accepting and no primary exists, promote them to primary
        if ($isBackupServiceman && !$serviceRequest->serviceman_id) {
            $serviceRequest->update([
                'serviceman_id' => $user->id,
                'backup_serviceman_id' => null,
                'accepted_at' => now(),
            ]);
        } else {
            // Mark as accepted
            $serviceRequest->update([
                'accepted_at' => now(),
            ]);
        }

        // Refresh to get updated relationships
        $serviceRequest->refresh();

        // Notify admin (sends email + creates notification)
        $this->notificationService->notifyAdmins(
            'ASSIGNMENT_ACCEPTED',
            'Assignment Accepted',
            "Serviceman {$user->full_name} has accepted service request #{$serviceRequest->id}.",
            $serviceRequest,
            ['serviceman_name' => $user->full_name]
        );

        return back()->with('success', 'Assignment accepted successfully!');
    }

    public function declineAssignment(Request $request, ServiceRequest $serviceRequest)
    {
        // Only assigned serviceman can decline
        if ($serviceRequest->serviceman_id !== Auth::id()) {
            abort(403);
        }

        if ($serviceRequest->status !== 'ASSIGNED_TO_SERVICEMAN') {
            return back()->with('error', 'Invalid action for current status.');
        }

        $validator = Validator::make($request->all(), [
            'decline_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $wasAccepted = $serviceRequest->accepted_at !== null;
        $oldServicemanName = $serviceRequest->serviceman->full_name;
        $servicemanProfile = $serviceRequest->serviceman->servicemanProfile;
        $oldServicemanId = $serviceRequest->serviceman_id;
        $backupServiceman = $serviceRequest->backupServiceman;

        // If serviceman had accepted before, apply rating penalty
        if ($wasAccepted && $servicemanProfile) {
            // Apply 0.2 rating penalty before removing serviceman
            $servicemanProfile->applyRatingPenalty(0.2);
            
            $serviceRequest->update([
                'was_declined_after_acceptance' => true,
            ]);
        }

        // Check if backup serviceman exists and is available - auto-assign them
        if ($backupServiceman && $backupServiceman->servicemanProfile && $backupServiceman->servicemanProfile->is_available) {
            // Auto-assign backup serviceman as primary
            $serviceRequest->update([
                'serviceman_id' => $backupServiceman->id,
                'backup_serviceman_id' => null, // Remove backup assignment
                'status' => 'ASSIGNED_TO_SERVICEMAN',
                'accepted_at' => null, // Reset acceptance since it's a new assignment
            ]);
            
            // Refresh to ensure relationships are updated
            $serviceRequest->refresh();

            // Notify admin (sends email + creates notification)
            $message = $wasAccepted 
                ? "Serviceman {$oldServicemanName} declined service request #{$serviceRequest->id} AFTER accepting it. A rating penalty has been applied. Backup serviceman {$backupServiceman->full_name} has been automatically assigned. Reason: " . ($request->decline_reason ?? 'No reason provided')
                : "Serviceman {$oldServicemanName} declined service request #{$serviceRequest->id}. Backup serviceman {$backupServiceman->full_name} has been automatically assigned. Reason: " . ($request->decline_reason ?? 'No reason provided');

            $this->notificationService->notifyAdmins(
                'ASSIGNMENT_DECLINED_WITH_AUTO_ASSIGN',
                $wasAccepted ? 'Assignment Declined - Backup Auto-Assigned' : 'Assignment Declined - Backup Auto-Assigned',
                $message,
                $serviceRequest,
                ['old_serviceman_name' => $oldServicemanName, 'new_serviceman_name' => $backupServiceman->full_name, 'was_accepted' => $wasAccepted, 'decline_reason' => $request->decline_reason ?? 'No reason provided']
            );

            // Notify original serviceman about decline and penalty (sends email + creates notification)
            $this->notificationService->notifyServiceman(
                Auth::user(),
                'ASSIGNMENT_DECLINED',
                '❌ Assignment Declined & Reassigned',
                "You have declined service request #{$serviceRequest->id}. It has been reassigned to {$backupServiceman->full_name}. " . ($wasAccepted ? 'A rating penalty of 0.2 has been applied to your profile.' : ''),
                $serviceRequest,
                ['decline_reason' => $request->decline_reason ?? 'No reason provided']
            );

            // Notify new serviceman (backup now primary) (sends email + creates notification)
            $clientName = $serviceRequest->client->full_name ?? 'Client';
            $clientPhone = $serviceRequest->client->phone_number ?? 'N/A';
            $clientAddress = $serviceRequest->client_address ?? $serviceRequest->location ?? 'N/A';
            
            $this->notificationService->notifyServiceman(
                $backupServiceman,
                'SERVICE_ASSIGNED_FROM_BACKUP',
                '🎉 New Service Request Assigned (from Backup)',
                "You have been assigned service request #{$serviceRequest->id} as the primary serviceman, replacing {$oldServicemanName}. Please review the request and proceed with inspection. Service: {$serviceRequest->category->name}. Contact client: {$clientName} at {$clientPhone}. Location: {$clientAddress}",
                $serviceRequest,
                ['client_name' => $clientName, 'client_phone' => $clientPhone, 'client_address' => $clientAddress, 'auto_assigned' => true, 'old_serviceman_name' => $oldServicemanName]
            );

            // Notify client about serviceman change (sends email + creates notification)
            $this->notificationService->notifyClient(
                $serviceRequest->client,
                'SERVICEMAN_CHANGED',
                '🔄 Serviceman Changed - Please Check Dashboard',
                "Due to some reasons, the serviceman assigned to your service request #{$serviceRequest->id} has been changed. Your new serviceman is {$backupServiceman->full_name}. Please check your dashboard for updated details. The new serviceman will contact you shortly.",
                $serviceRequest,
                ['old_serviceman_name' => $oldServicemanName, 'new_serviceman_name' => $backupServiceman->full_name]
            );

            return redirect()->route('dashboard')->with('success', 'Assignment declined. Backup serviceman ' . $backupServiceman->full_name . ' has been automatically assigned. ' . ($wasAccepted ? 'A rating penalty has been applied to the previous serviceman.' : ''));
        } else {
            // No backup serviceman available - reset to pending admin assignment
            $serviceRequest->update([
                'serviceman_id' => null,
                'backup_serviceman_id' => null,
                'status' => 'PENDING_ADMIN_ASSIGNMENT',
            ]);

            // Notify original serviceman about decline and penalty (sends email + creates notification)
            $this->notificationService->notifyServiceman(
                Auth::user(),
                'ASSIGNMENT_DECLINED',
                '❌ Assignment Declined',
                "You have declined service request #{$serviceRequest->id}. It has been reverted to pending admin assignment. " . ($wasAccepted ? 'A rating penalty of 0.2 has been applied to your profile.' : ''),
                $serviceRequest,
                ['decline_reason' => $request->decline_reason ?? 'No reason provided']
            );

            // Notify admin (sends email + creates notification)
            $message = $wasAccepted 
                ? "Serviceman {$oldServicemanName} has declined service request #{$serviceRequest->id} AFTER accepting it. A rating penalty has been applied. No backup serviceman available. Manual assignment required. Reason: " . ($request->decline_reason ?? 'No reason provided')
                : "Serviceman {$oldServicemanName} has declined service request #{$serviceRequest->id}. No backup serviceman available. Manual assignment required. Reason: " . ($request->decline_reason ?? 'No reason provided');

            $this->notificationService->notifyAdmins(
                'ASSIGNMENT_DECLINED_NO_BACKUP',
                $wasAccepted ? 'Assignment Declined After Acceptance' : 'Assignment Declined',
                $message,
                $serviceRequest,
                ['serviceman_name' => $oldServicemanName, 'was_accepted' => $wasAccepted, 'decline_reason' => $request->decline_reason ?? 'No reason provided']
            );

            // Notify client that serviceman declined and admin will reassign
            $this->notificationService->notifyClient(
                $serviceRequest->client,
                'SERVICEMAN_DECLINED',
                '⚠️ Serviceman Declined Your Request',
                "Unfortunately, {$oldServicemanName} has declined your service request #{$serviceRequest->id}. Our admin team is working to assign a new professional and will notify you shortly.",
                $serviceRequest,
                ['serviceman_name' => $oldServicemanName]
            );

            return redirect()->route('dashboard')->with('success', 'Assignment declined. ' . ($wasAccepted ? 'A rating penalty has been applied. ' : '') . 'Admin will assign a new serviceman.');
        }
    }

    public function submitEstimate(Request $request, ServiceRequest $serviceRequest)
    {
        // Only assigned serviceman (primary or backup) can submit estimate
        $user = Auth::user();
        if (!$user->isServiceman()) {
            abort(403, 'Only servicemen can submit estimates.');
        }
        
        $isPrimary = (int)$serviceRequest->serviceman_id === (int)$user->id;
        $isBackup = $serviceRequest->backup_serviceman_id && (int)$serviceRequest->backup_serviceman_id === (int)$user->id;
        
        if (!$isPrimary && !$isBackup) {
            abort(403, 'You are not assigned to this service request.');
        }

        $validator = Validator::make($request->all(), [
            'serviceman_estimated_cost' => 'required|numeric|min:0',
            'inspection_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // If backup serviceman is submitting and there's no primary, promote backup to primary
        if ($isBackup && !$serviceRequest->serviceman_id) {
            $serviceRequest->update([
                'serviceman_id' => $user->id,
                'backup_serviceman_id' => null,
            ]);
        }

        // Mark as accepted if not already accepted (first interaction)
        $updates = [
            'serviceman_estimated_cost' => $request->serviceman_estimated_cost,
            'status' => 'SERVICEMAN_INSPECTED',
            'inspection_completed_at' => now(),
        ];

        if (!$serviceRequest->accepted_at) {
            $updates['accepted_at'] = now();
        }

        $serviceRequest->update($updates);

        // Refresh to get updated relationships
        $serviceRequest->refresh();
        $serviceRequest->load(['serviceman', 'backupServiceman']);

        // Get serviceman name - use the user submitting (could be primary or backup)
        $servicemanName = $user->full_name;
        $servicemanType = $isBackup ? 'Backup serviceman' : 'Serviceman';

        // Notify ADMIN for review (sends email + creates notification)
        $this->notificationService->notifyAdmins(
            'COST_ESTIMATE_SUBMITTED',
            '💰 Cost Estimate Submitted - Review Required',
            "{$servicemanType} {$servicemanName} has submitted a cost estimate of ₦" . number_format($request->serviceman_estimated_cost) . " for service request #{$serviceRequest->id}. Please review and add your markup before notifying the client.",
            $serviceRequest,
            ['serviceman_name' => $servicemanName, 'estimated_cost' => $request->serviceman_estimated_cost, 'is_backup' => $isBackup]
        );

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

        // Notify ADMIN (sends email + creates notification)
        $this->notificationService->notifyAdmins(
            'WORK_COMPLETED',
            '✅ Work Completed - Action Required',
            "Serviceman {$serviceRequest->serviceman->full_name} has marked service request #{$serviceRequest->id} as completed. Notes: \"{$request->completion_notes}\". Please verify and notify client {$serviceRequest->client->full_name}.",
            $serviceRequest,
            ['serviceman_name' => $serviceRequest->serviceman->full_name, 'client_name' => $serviceRequest->client->full_name, 'completion_notes' => $request->completion_notes]
        );

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

        // Notify serviceman (sends email + creates notification)
        $this->notificationService->notifyServiceman(
            $serviceRequest->serviceman,
            'COST_APPROVED',
            '✅ Client Approved Cost Estimate',
            "Client {$serviceRequest->client->full_name} has approved the cost estimate of ₦" . number_format($serviceRequest->final_cost) . " for service request #{$serviceRequest->id}. Waiting for client payment to begin work.",
            $serviceRequest,
            ['client_name' => $serviceRequest->client->full_name, 'final_cost' => $serviceRequest->final_cost]
        );

        // Notify admin (sends email + creates notification)
        $this->notificationService->notifyAdmins(
            'COST_APPROVED',
            '💵 Cost Approved - Awaiting Payment',
            "Client {$serviceRequest->client->full_name} has approved the final cost of ₦" . number_format($serviceRequest->final_cost) . " for service request #{$serviceRequest->id}. Now awaiting payment.",
            $serviceRequest,
            ['client_name' => $serviceRequest->client->full_name, 'final_cost' => $serviceRequest->final_cost]
        );

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

        // Update serviceman's profile - use updateRating to handle penalties
        $serviceman = $serviceRequest->serviceman;
        $averageRating = null;
        if ($serviceman && $serviceman->servicemanProfile) {
            $profile = $serviceman->servicemanProfile;
            // Use updateRating method which handles penalty deduction
            $profile->updateRating($request->rating);
            // Refresh to get updated rating
            $profile->refresh();
            $averageRating = $profile->rating ?? 0;
        }

        // Generate star display for notifications
        $stars = str_repeat('⭐', $request->rating) . str_repeat('☆', 5 - $request->rating);
        $reviewText = $request->review ? " Review: \"{$request->review}\"" : "";
        
        // Format average rating for display (handle negative ratings)
        $averageRatingDisplay = $averageRating !== null ? number_format($averageRating, 1) : '0.0';

        // Notify serviceman (sends email + creates notification)
        $this->notificationService->notifyServiceman(
            $serviceRequest->serviceman,
            'RATING_RECEIVED',
            '⭐ New Rating Received',
            "Client {$serviceRequest->client->full_name} rated your work on service request #{$serviceRequest->id}: {$stars} ({$request->rating}/5).{$reviewText} Your new average rating is {$averageRatingDisplay}/5.0.",
            $serviceRequest,
            ['client_name' => $serviceRequest->client->full_name, 'rating' => $request->rating, 'review' => $request->review, 'average_rating' => $averageRatingDisplay]
        );

        // Notify admin (sends email + creates notification)
        $this->notificationService->notifyAdmins(
            'RATING_SUBMITTED',
            '⭐ Rating Submitted - Service Request #' . $serviceRequest->id,
            "Client {$serviceRequest->client->full_name} rated serviceman {$serviceRequest->serviceman->full_name}: {$stars} ({$request->rating}/5) for service request #{$serviceRequest->id}.{$reviewText}",
            $serviceRequest,
            ['client_name' => $serviceRequest->client->full_name, 'serviceman_name' => $serviceRequest->serviceman->full_name, 'rating' => $request->rating, 'review' => $request->review]
        );

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

        // Notify admin (sends email + creates notification)
        $this->notificationService->notifyAdmins(
            'PRICE_NEGOTIATION_REQUESTED',
            '💬 Price Negotiation Request - Action Required',
            "Client {$serviceRequest->client->full_name} wants to negotiate service request #{$serviceRequest->id}. Current: ₦" . number_format($currentCost) . " → Proposed: ₦" . number_format($proposedCost) . " ({$percentageReduction}% reduction). Reason: \"{$request->negotiation_reason}\". Please review and respond.",
            $serviceRequest,
            ['client_name' => $serviceRequest->client->full_name, 'current_cost' => $currentCost, 'proposed_cost' => $proposedCost, 'percentage_reduction' => $percentageReduction, 'reason' => $request->negotiation_reason]
        );

        // Notify serviceman about negotiation (sends email + creates notification)
        if ($serviceRequest->serviceman_id) {
            $this->notificationService->notifyServiceman(
                $serviceRequest->serviceman,
                'NEGOTIATION_STARTED',
                '💬 Client Requested Price Negotiation',
                "Client {$serviceRequest->client->full_name} is negotiating the price for service request #{$serviceRequest->id}. Proposed: ₦" . number_format($proposedCost) . " (down from ₦" . number_format($currentCost) . "). Admin will review and respond.",
                $serviceRequest,
                ['client_name' => $serviceRequest->client->full_name, 'current_cost' => $currentCost, 'proposed_cost' => $proposedCost]
            );
        }

        // Notify client - confirmation (sends email + creates notification)
        $this->notificationService->notifyClient(
            $serviceRequest->client,
            'NEGOTIATION_SUBMITTED',
            '✅ Negotiation Request Submitted',
            "Your price negotiation request has been submitted successfully. Proposed amount: ₦" . number_format($request->proposed_amount) . " (current: ₦" . number_format($currentCost) . "). Our admin team will review and respond shortly.",
            $serviceRequest,
            ['proposed_amount' => $request->proposed_amount, 'current_cost' => $currentCost]
        );

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Price negotiation request submitted successfully! Admin will review and respond shortly.');
    }
}