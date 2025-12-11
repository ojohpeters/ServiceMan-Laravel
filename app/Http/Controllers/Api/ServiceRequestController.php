<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ServiceRequest::forUser($user);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_emergency')) {
            $query->where('is_emergency', $request->boolean('is_emergency'));
        }

        $serviceRequests = $query->with([
            'client', 'serviceman', 'backupServiceman', 'category', 'payments'
        ])->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($serviceRequests);
    }

    public function show($id)
    {
        $serviceRequest = ServiceRequest::with([
            'client', 'serviceman', 'backupServiceman', 'category', 
            'payments', 'negotiations', 'rating'
        ])->findOrFail($id);

        $user = Auth::user();
        
        // Check if user has access to this service request
        if (!$this->canAccessServiceRequest($serviceRequest, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json($serviceRequest);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'serviceman_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'booking_date' => 'required|date|after:today',
            'is_emergency' => 'boolean',
            'client_address' => 'required|string',
            'service_description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Check if serviceman is available
        $serviceman = \App\Models\User::findOrFail($request->serviceman_id);
        if (!$serviceman->isServiceman() || !$serviceman->servicemanProfile->is_available) {
            return response()->json(['error' => 'Serviceman is not available'], 400);
        }

        $bookingDate = Carbon::parse($request->booking_date);
        
        // Check if serviceman is busy on the booking date
        if ($serviceman->isBusyOnDate($bookingDate)) {
            $dateFormatted = $bookingDate->format('l, F j, Y');
            return response()->json([
                'error' => "⚠️ WARNING: This serviceman is marked as BUSY/UNAVAILABLE on {$dateFormatted}. Please select a different date.",
                'busy_date' => $bookingDate->format('Y-m-d')
            ], 400);
        }
        
        $isEmergency = $request->boolean('is_emergency') || $bookingDate->diffInDays(Carbon::today()) < 2;
        $autoFlaggedEmergency = $bookingDate->diffInDays(Carbon::today()) < 2;

        $serviceRequest = ServiceRequest::create([
            'client_id' => $user->id,
            'serviceman_id' => $request->serviceman_id,
            'category_id' => $request->category_id,
            'booking_date' => $bookingDate,
            'is_emergency' => $isEmergency,
            'auto_flagged_emergency' => $autoFlaggedEmergency,
            'status' => 'PENDING_ADMIN_ASSIGNMENT',
            'initial_booking_fee' => $isEmergency ? 5000 : 2000,
            'client_address' => $request->client_address,
            'service_description' => $request->service_description
        ]);

        // Load relationships for response
        $serviceRequest->load(['client', 'serviceman', 'category']);

        return response()->json($serviceRequest, 201);
    }

    public function update(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $user = Auth::user();
        
        // Check if user has permission to update this service request
        if (!$this->canModifyServiceRequest($serviceRequest, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:' . implode(',', array_keys(ServiceRequest::STATUS_CHOICES)),
            'serviceman_estimated_cost' => 'nullable|numeric|min:0',
            'admin_markup_percentage' => 'nullable|numeric|min:0|max:100',
            'final_cost' => 'nullable|numeric|min:0',
            'is_emergency' => 'boolean',
            'backup_serviceman_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $updateData = $request->only([
            'status', 'serviceman_estimated_cost', 'admin_markup_percentage', 
            'final_cost', 'is_emergency', 'backup_serviceman_id'
        ]);

        // Auto-calculate final cost if serviceman estimated cost and markup are provided
        if ($request->has('serviceman_estimated_cost') && $request->has('admin_markup_percentage')) {
            $updateData['final_cost'] = $request->serviceman_estimated_cost * (1 + $request->admin_markup_percentage / 100);
        }

        $serviceRequest->update($updateData);

        // Send notifications based on status changes
        $this->handleStatusChangeNotifications($serviceRequest, $user);

        $serviceRequest->load(['client', 'serviceman', 'backupServiceman', 'category']);

        return response()->json($serviceRequest);
    }

    public function submitEstimate(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $user = Auth::user();
        
        if (!$user->isServiceman() || $serviceRequest->serviceman_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'estimated_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceRequest->update([
            'serviceman_estimated_cost' => $request->estimated_cost,
            'status' => 'SERVICEMAN_INSPECTED',
            'inspection_completed_at' => now()
        ]);

        // Notify admin
        $this->notifyAdmin('COST_ESTIMATE_READY', 
            'Cost Estimate Ready', 
            "Serviceman has submitted cost estimate for service request #{$serviceRequest->id}",
            $serviceRequest
        );

        return response()->json(['message' => 'Estimate submitted successfully']);
    }

    public function markComplete(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $user = Auth::user();
        
        if (!$user->isServiceman() || $serviceRequest->serviceman_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'completion_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceRequest->update([
            'status' => 'COMPLETED',
            'work_completed_at' => now()
        ]);

        // Notify admin and client
        $this->notifyAdmin('JOB_COMPLETED', 
            'Job Completed', 
            "Service request #{$serviceRequest->id} has been completed by serviceman",
            $serviceRequest
        );

        $this->notifyClient($serviceRequest->client, 'JOB_COMPLETED',
            'Job Completed',
            "Your service request #{$serviceRequest->id} has been completed",
            $serviceRequest
        );

        return response()->json(['message' => 'Service request marked as complete']);
    }

    private function canAccessServiceRequest($serviceRequest, $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isClient() && $serviceRequest->client_id === $user->id) {
            return true;
        }

        if ($user->isServiceman() && 
            ($serviceRequest->serviceman_id === $user->id || 
             $serviceRequest->backup_serviceman_id === $user->id)) {
            return true;
        }

        return false;
    }

    private function canModifyServiceRequest($serviceRequest, $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isServiceman() && $serviceRequest->serviceman_id === $user->id) {
            return true;
        }

        return false;
    }

    private function handleStatusChangeNotifications($serviceRequest, $user)
    {
        switch ($serviceRequest->status) {
            case 'ASSIGNED_TO_SERVICEMAN':
                // Notify primary serviceman
                if ($serviceRequest->serviceman) {
                    $this->notifyServiceman($serviceRequest->serviceman, 'SERVICE_ASSIGNED',
                        'Service Assigned',
                        "You have been assigned to service request #{$serviceRequest->id}",
                        $serviceRequest
                    );
                }

                // Notify backup serviceman
                if ($serviceRequest->backup_serviceman_id) {
                    $this->notifyServiceman($serviceRequest->backupServiceman, 'BACKUP_OPPORTUNITY',
                        'Backup Opportunity',
                        "You are assigned as backup for service request #{$serviceRequest->id}",
                        $serviceRequest
                    );
                }
                break;

            case 'AWAITING_CLIENT_APPROVAL':
                $this->notifyClient($serviceRequest->client, 'COST_ESTIMATE_READY',
                    'Cost Estimate Ready',
                    "Cost estimate is ready for service request #{$serviceRequest->id}",
                    $serviceRequest
                );
                break;
        }
    }

    private function notifyAdmin($type, $title, $message, $serviceRequest)
    {
        $admins = \App\Models\User::where('user_type', 'ADMIN')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'notification_type' => $type,
                'title' => $title,
                'message' => $message,
                'service_request_id' => $serviceRequest->id
            ]);
        }
    }

    private function notifyServiceman($serviceman, $type, $title, $message, $serviceRequest)
    {
        Notification::create([
            'user_id' => $serviceman->id,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'service_request_id' => $serviceRequest->id
        ]);
    }

    private function notifyClient($client, $type, $title, $message, $serviceRequest)
    {
        Notification::create([
            'user_id' => $client->id,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'service_request_id' => $serviceRequest->id
        ]);
    }
}