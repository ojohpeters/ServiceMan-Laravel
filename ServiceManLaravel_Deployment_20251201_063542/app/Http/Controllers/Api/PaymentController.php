<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private $paystackSecretKey;
    private $paystackPublicKey;
    private $paystackWebhookSecret;

    public function __construct()
    {
        $this->paystackSecretKey = config('services.paystack.secret_key');
        $this->paystackPublicKey = config('services.paystack.public_key');
        $this->paystackWebhookSecret = config('services.paystack.webhook_secret');
    }

    public function initialize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'payment_type' => 'required|in:INITIAL_BOOKING,FINAL_PAYMENT',
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);
        $user = Auth::user();

        // Verify user has access to this service request
        if (!$this->canAccessServiceRequest($serviceRequest, $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Validate payment amount
        if (!$this->validatePaymentAmount($serviceRequest, $request->payment_type, $request->amount)) {
            return response()->json(['error' => 'Invalid payment amount'], 400);
        }

        $reference = $this->generateReference($serviceRequest->id, $request->payment_type);
        $callbackUrl = config('app.frontend_url') . '/payment/callback';

        // Initialize Paystack payment
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.paystack.co/transaction/initialize', [
            'amount' => $request->amount * 100, // Convert to kobo
            'email' => $user->email,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'service_request_id' => $serviceRequest->id,
                'payment_type' => $request->payment_type,
                'user_id' => $user->id
            ]
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to initialize payment'], 500);
        }

        $paystackData = $response->json();

        // Create payment record
        $payment = Payment::create([
            'service_request_id' => $serviceRequest->id,
            'payment_type' => $request->payment_type,
            'amount' => $request->amount,
            'paystack_reference' => $reference,
            'paystack_access_code' => $paystackData['data']['access_code'],
            'status' => 'PENDING',
            'metadata' => [
                'client_info' => [
                    'name' => $user->getFullNameAttribute(),
                    'email' => $user->email,
                    'phone' => $user->clientProfile->phone_number ?? $user->servicemanProfile->phone_number ?? ''
                ]
            ]
        ]);

        return response()->json([
            'payment' => $payment,
            'authorization_url' => $paystackData['data']['authorization_url'],
            'access_code' => $paystackData['data']['access_code'],
            'reference' => $reference
        ], 201);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $reference = $request->reference;
        $payment = Payment::where('paystack_reference', $reference)->firstOrFail();

        // Verify with Paystack
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey
        ])->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to verify payment'], 500);
        }

        $paystackData = $response->json();

        if ($paystackData['status'] === true && $paystackData['data']['status'] === 'success') {
            $payment->markAsSuccessful();
            
            // Update service request status based on payment type
            $this->handlePaymentSuccess($payment);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified successfully',
                'data' => [
                    'reference' => $reference,
                    'amount' => $payment->amount,
                    'status' => $payment->status
                ]
            ]);
        } else {
            $payment->markAsFailed();
            
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment verification failed',
                'data' => [
                    'reference' => $reference,
                    'status' => $payment->status
                ]
            ]);
        }
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();

        // Verify signature
        if (!$this->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $event = json_decode($payload, true);

        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $payment = Payment::where('paystack_reference', $reference)->first();

            if ($payment && $payment->status === 'PENDING') {
                $payment->markAsSuccessful();
                
                // Update service request status
                $this->handlePaymentSuccess($payment);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function getPaymentHistory(Request $request)
    {
        $user = Auth::user();
        
        $query = Payment::with(['serviceRequest.client', 'serviceRequest.serviceman', 'serviceRequest.category']);

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

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($payments);
    }

    private function canAccessServiceRequest($serviceRequest, $user)
    {
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

    private function validatePaymentAmount($serviceRequest, $paymentType, $amount)
    {
        if ($paymentType === 'INITIAL_BOOKING') {
            $expectedAmount = $serviceRequest->calculateBookingFee();
            return $amount == $expectedAmount;
        } elseif ($paymentType === 'FINAL_PAYMENT') {
            $expectedAmount = $serviceRequest->final_cost;
            return $expectedAmount && $amount == $expectedAmount;
        }

        return false;
    }

    private function generateReference($serviceRequestId, $paymentType)
    {
        $timestamp = now()->timestamp;
        $random = Str::random(6);
        return "SR{$serviceRequestId}-{$paymentType}-{$timestamp}-{$random}";
    }

    private function verifyWebhookSignature($payload, $signature)
    {
        $expectedSignature = hash_hmac('sha512', $payload, $this->paystackWebhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    private function handlePaymentSuccess($payment)
    {
        $serviceRequest = $payment->serviceRequest;

        if ($payment->payment_type === 'INITIAL_BOOKING') {
            // Update service request status to pending admin assignment
            $serviceRequest->update(['status' => 'PENDING_ADMIN_ASSIGNMENT']);
            
            // Notify admin
            $this->notifyAdmin('PAYMENT_RECEIVED', 
                'Payment Received', 
                "Initial booking payment received for service request #{$serviceRequest->id}",
                $serviceRequest
            );
        } elseif ($payment->payment_type === 'FINAL_PAYMENT') {
            // Update service request status to payment confirmed
            $serviceRequest->update(['status' => 'PAYMENT_CONFIRMED']);
            
            // Notify admin and serviceman
            $this->notifyAdmin('PAYMENT_RECEIVED', 
                'Final Payment Received', 
                "Final payment received for service request #{$serviceRequest->id}",
                $serviceRequest
            );

            if ($serviceRequest->serviceman) {
                $this->notifyServiceman($serviceRequest->serviceman, 'PAYMENT_RECEIVED',
                    'Payment Confirmed',
                    "Payment confirmed for service request #{$serviceRequest->id}. You can proceed with the work.",
                    $serviceRequest
                );
            }
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
}