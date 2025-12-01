<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceNegotiation;
use App\Models\ServiceRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NegotiationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = PriceNegotiation::with(['serviceRequest', 'proposedBy']);

        // Filter by service request if provided
        if ($request->has('request_id')) {
            $query->where('service_request_id', $request->request_id);
        }

        // Filter by user's service requests
        if ($user->isClient()) {
            $query->whereHas('serviceRequest', function ($q) use ($user) {
                $q->where('client_id', $user->id);
            });
        } elseif ($user->isServiceman()) {
            $query->whereHas('serviceRequest', function ($q) use ($user) {
                $q->where('serviceman_id', $user->id);
            });
        } elseif (!$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $negotiations = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($negotiations);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'proposed_amount' => 'required|numeric|min:0',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);
        $user = Auth::user();

        // Check if user can negotiate for this service request
        if (!$this->canNegotiate($serviceRequest, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if service request is in negotiable status
        if (!in_array($serviceRequest->status, ['AWAITING_CLIENT_APPROVAL', 'NEGOTIATING'])) {
            return response()->json(['error' => 'Service request is not in negotiable status'], 400);
        }

        $negotiation = PriceNegotiation::create([
            'service_request_id' => $serviceRequest->id,
            'proposed_by' => $user->id,
            'proposed_amount' => $request->proposed_amount,
            'message' => $request->message,
            'status' => 'PENDING'
        ]);

        // Update service request status to negotiating
        $serviceRequest->update(['status' => 'NEGOTIATING']);

        // Send notifications
        $this->notifyNegotiationParticipants($negotiation, $user);

        $negotiation->load(['serviceRequest', 'proposedBy']);

        return response()->json($negotiation, 201);
    }

    public function show($id)
    {
        $negotiation = PriceNegotiation::with(['serviceRequest', 'proposedBy'])->findOrFail($id);
        $user = Auth::user();

        // Check if user has access to this negotiation
        if (!$this->canAccessNegotiation($negotiation, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json($negotiation);
    }

    public function accept(Request $request, $id)
    {
        $negotiation = PriceNegotiation::findOrFail($id);
        $user = Auth::user();

        // Check if user can accept this negotiation
        if (!$this->canAcceptNegotiation($negotiation, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $negotiation->accept();

        // Update service request with negotiated amount
        $serviceRequest = $negotiation->serviceRequest;
        $serviceRequest->update([
            'final_cost' => $negotiation->proposed_amount,
            'status' => 'AWAITING_PAYMENT'
        ]);

        // Send notifications
        $this->notifyNegotiationAccepted($negotiation, $user);

        return response()->json(['message' => 'Negotiation accepted successfully']);
    }

    public function reject(Request $request, $id)
    {
        $negotiation = PriceNegotiation::findOrFail($id);
        $user = Auth::user();

        // Check if user can reject this negotiation
        if (!$this->canAcceptNegotiation($negotiation, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $negotiation->reject();

        // Send notifications
        $this->notifyNegotiationRejected($negotiation, $user);

        return response()->json(['message' => 'Negotiation rejected']);
    }

    public function counter(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'proposed_amount' => 'required|numeric|min:0',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $originalNegotiation = PriceNegotiation::findOrFail($id);
        $user = Auth::user();

        // Check if user can counter this negotiation
        if (!$this->canAcceptNegotiation($originalNegotiation, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Create counter negotiation
        $counterNegotiation = PriceNegotiation::create([
            'service_request_id' => $originalNegotiation->service_request_id,
            'proposed_by' => $user->id,
            'proposed_amount' => $request->proposed_amount,
            'message' => $request->message,
            'status' => 'COUNTERED'
        ]);

        // Update original negotiation status
        $originalNegotiation->counter();

        // Send notifications
        $this->notifyNegotiationCountered($counterNegotiation, $originalNegotiation, $user);

        $counterNegotiation->load(['serviceRequest', 'proposedBy']);

        return response()->json($counterNegotiation);
    }

    private function canNegotiate($serviceRequest, $user)
    {
        if ($user->isClient() && $serviceRequest->client_id === $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    private function canAccessNegotiation($negotiation, $user)
    {
        $serviceRequest = $negotiation->serviceRequest;

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isClient() && $serviceRequest->client_id === $user->id) {
            return true;
        }

        if ($user->isServiceman() && $serviceRequest->serviceman_id === $user->id) {
            return true;
        }

        return false;
    }

    private function canAcceptNegotiation($negotiation, $user)
    {
        $serviceRequest = $negotiation->serviceRequest;

        // Only client and admin can accept/reject/counter negotiations
        if ($user->isClient() && $serviceRequest->client_id === $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    private function notifyNegotiationParticipants($negotiation, $proposedBy)
    {
        $serviceRequest = $negotiation->serviceRequest;

        // Notify the other party (client or admin)
        if ($proposedBy->isClient()) {
            // Client proposed, notify admin
            $this->notifyAdmin('NEGOTIATION_UPDATE',
                'New Negotiation Proposal',
                "Client has proposed a new amount for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        } else {
            // Admin proposed, notify client
            $this->notifyClient($serviceRequest->client, 'NEGOTIATION_UPDATE',
                'Negotiation Proposal',
                "Admin has proposed a new amount for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        }
    }

    private function notifyNegotiationAccepted($negotiation, $acceptedBy)
    {
        $serviceRequest = $negotiation->serviceRequest;

        if ($acceptedBy->isClient()) {
            // Client accepted, notify admin
            $this->notifyAdmin('NEGOTIATION_UPDATE',
                'Negotiation Accepted',
                "Client has accepted the negotiation for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        } else {
            // Admin accepted, notify client
            $this->notifyClient($serviceRequest->client, 'NEGOTIATION_UPDATE',
                'Negotiation Accepted',
                "Admin has accepted your negotiation for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        }
    }

    private function notifyNegotiationRejected($negotiation, $rejectedBy)
    {
        $serviceRequest = $negotiation->serviceRequest;

        if ($rejectedBy->isClient()) {
            // Client rejected, notify admin
            $this->notifyAdmin('NEGOTIATION_UPDATE',
                'Negotiation Rejected',
                "Client has rejected the negotiation for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        } else {
            // Admin rejected, notify client
            $this->notifyClient($serviceRequest->client, 'NEGOTIATION_UPDATE',
                'Negotiation Rejected',
                "Admin has rejected your negotiation for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        }
    }

    private function notifyNegotiationCountered($counterNegotiation, $originalNegotiation, $counteredBy)
    {
        $serviceRequest = $counterNegotiation->serviceRequest;

        if ($counteredBy->isClient()) {
            // Client countered, notify admin
            $this->notifyAdmin('NEGOTIATION_UPDATE',
                'Negotiation Countered',
                "Client has countered the negotiation for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        } else {
            // Admin countered, notify client
            $this->notifyClient($serviceRequest->client, 'NEGOTIATION_UPDATE',
                'Negotiation Countered',
                "Admin has countered your negotiation for service request #{$serviceRequest->id}",
                $serviceRequest
            );
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