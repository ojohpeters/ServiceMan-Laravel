<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\AppNotification;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaystackPaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user->isClient()) {
            $payments = Payment::whereHas('serviceRequest', function($query) use ($user) {
                $query->where('client_id', $user->id);
            })->with(['serviceRequest.category'])->latest()->paginate(10);
        } else {
            $payments = collect();
        }

        return view('payments.index', compact('payments'));
    }

    public function initialize(Request $request)
    {
        // Debug logging
        \Log::info('Payment initialize request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'service_request' => 'required',
            'type' => ['required', 'in:INITIAL_BOOKING,FINAL_PAYMENT'],
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        
        // Handle pending booking (payment before service request creation)
        if ($request->service_request === 'pending' && $request->type === 'INITIAL_BOOKING') {
            $pendingBooking = session('pending_booking');
            
            if (!$pendingBooking) {
                return back()->with('error', 'Booking session expired. Please start a new booking.');
            }
            
            // Use pending booking data
            $serviceRequestId = 'pending';
        } else {
            // Existing service request
            $serviceRequest = ServiceRequest::findOrFail($request->service_request);
            $serviceRequestId = $serviceRequest->id;
        }

        // Skip validations for pending bookings (service request doesn't exist yet)
        if ($serviceRequestId !== 'pending') {
            // Check permissions
            if ($user->isClient() && $serviceRequest->client_id !== $user->id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only pay for your own service requests.'
                    ], 403);
                }
                abort(403, 'You can only pay for your own service requests.');
            }

            // Validate payment type and amount
            if ($request->type === 'INITIAL_BOOKING') {
                if ($serviceRequest->status !== 'PENDING_ADMIN_ASSIGNMENT') {
                    $message = 'Booking fee can only be paid for pending requests.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 400);
                    }
                    return back()->with('error', $message);
                }
                if ($request->amount != $serviceRequest->initial_booking_fee) {
                    $message = 'Invalid booking fee amount.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 400);
                    }
                    return back()->with('error', $message);
                }
            } elseif ($request->type === 'FINAL_PAYMENT') {
                if ($serviceRequest->status !== 'AWAITING_PAYMENT') {
                    $message = 'Final payment can only be made when awaiting payment.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 400);
                    }
                    return back()->with('error', $message);
                }
                if ($request->amount != $serviceRequest->final_cost) {
                    $message = 'Invalid final payment amount.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 400);
                    }
                    return back()->with('error', $message);
                }
            }
        }

        // Generate unique reference
        $reference = $this->paystackService->generateReference($serviceRequestId, $request->type);

        // Initialize Paystack payment
        $paymentData = [
            'amount' => $request->amount,
            'email' => $user->email,
            'reference' => $reference,
            'service_request_id' => $serviceRequestId,
            'payment_type' => $request->type,
            'user_id' => $user->id,
        ];

        $paystackResponse = $this->paystackService->initializePayment($paymentData);

        if (!$paystackResponse['success']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $paystackResponse['message']
                ], 400);
            }
            return back()->with('error', $paystackResponse['message']);
        }

        // Create payment record (service_request_id will be null for pending bookings)
        $payment = $this->paystackService->createPaymentRecord([
            'service_request_id' => $serviceRequestId === 'pending' ? null : $serviceRequestId,
            'payment_type' => $request->type,
            'amount' => $request->amount,
            'reference' => $reference,
            'access_code' => $paystackResponse['access_code'],
            'metadata' => $paymentData,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'authorization_url' => $paystackResponse['authorization_url'],
                'reference' => $reference,
                'message' => 'Payment initialized successfully. Redirecting to payment gateway...'
            ]);
        }

        // Redirect to Paystack payment page
        return redirect($paystackResponse['authorization_url']);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $reference = $request->reference;
        
        // Verify payment with Paystack
        $verification = $this->paystackService->verifyPayment($reference);

        if ($verification['success']) {
            // Update payment status
            $payment = $this->paystackService->updatePaymentStatus($reference, 'SUCCESSFUL', [
                'paystack_data' => $verification['data'],
                'verified_at' => now(),
            ]);

            if ($payment) {
                return $this->handlePaymentSuccess($payment);
            }
        }

        // Payment failed
        $this->paystackService->updatePaymentStatus($reference, 'FAILED');
        return back()->with('error', 'Payment verification failed. Please try again.');
    }

    public function webhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        
        // In production, verify the signature with Paystack
        $expectedSignature = hash_hmac('sha512', $payload, config('paystack.secret_key'));
        if (!hash_equals($expectedSignature, $signature)) {
            \Log::error('Invalid Paystack webhook signature');
            abort(400, 'Invalid signature');
        }

        $event = $request->json()->all();
        
        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            
            // Verify payment
            $verification = $this->paystackService->verifyPayment($reference);
            
            if ($verification['success']) {
                $payment = $this->paystackService->updatePaymentStatus($reference, 'SUCCESSFUL', [
                    'paystack_data' => $verification['data'],
                    'webhook_received_at' => now(),
                ]);
                
                if ($payment) {
                    $this->handlePaymentSuccess($payment);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function history()
    {
        $user = Auth::user();
        
        $payments = $this->paystackService->getPaymentHistory($user->id, $user->user_type);

        return view('payments.history', compact('payments'));
    }

    private function handlePaymentSuccess(Payment $payment)
    {
        if ($payment->payment_type === 'INITIAL_BOOKING') {
            // Check if this is a pending booking (service request created after payment)
            if (!$payment->service_request_id) {
                $pendingBooking = session('pending_booking');
                
                if (!$pendingBooking) {
                    return redirect()->route('dashboard')->with('error', 'Booking session expired. Please contact support.');
                }

                // NOW create the service request after successful payment
                $serviceRequest = ServiceRequest::create([
                    'client_id' => $pendingBooking['client_id'],
                    'serviceman_id' => $pendingBooking['serviceman_id'],
                    'backup_serviceman_id' => null,
                    'category_id' => $pendingBooking['category_id'],
                    'booking_date' => $pendingBooking['booking_date'],
                    'is_emergency' => $pendingBooking['is_emergency'],
                    'auto_flagged_emergency' => $pendingBooking['auto_flagged_emergency'],
                    'status' => 'PENDING_ADMIN_ASSIGNMENT',
                    'initial_booking_fee' => $pendingBooking['booking_fee'],
                    'admin_markup_percentage' => 10.00,
                    'client_address' => $pendingBooking['client_address'],
                    'service_description' => $pendingBooking['service_description'],
                    'title' => 'Service Request - ' . $pendingBooking['category_name'],
                    'description' => $pendingBooking['service_description'],
                    'location' => $pendingBooking['client_address'],
                ]);

                // Link payment to service request
                $payment->update(['service_request_id' => $serviceRequest->id]);

                // Clear session data
                session()->forget('pending_booking');

                // Create notification for admin
                $client = \App\Models\User::find($pendingBooking['client_id']);
                AppNotification::create([
                    'user_id' => null,
                    'service_request_id' => $serviceRequest->id,
                    'type' => 'NEW_SERVICE_REQUEST',
                    'title' => '🆕 New Service Request - Assignment Needed',
                    'message' => "Client {$client->full_name} has created a PAID service request #{$serviceRequest->id} for {$pendingBooking['category_name']}. Booking fee: ₦" . number_format($pendingBooking['booking_fee']) . " already paid. Preferred serviceman: {$pendingBooking['serviceman_name']}. Please confirm assignment.",
                    'is_read' => false,
                ]);

                // Notify client
                AppNotification::create([
                    'user_id' => $serviceRequest->client_id,
                    'service_request_id' => $serviceRequest->id,
                    'type' => 'BOOKING_CONFIRMED',
                    'title' => '✅ Booking Confirmed & Paid',
                    'message' => "Your service request #{$serviceRequest->id} has been created successfully and booking fee of ₦" . number_format($pendingBooking['booking_fee']) . " has been paid. Admin will assign {$pendingBooking['serviceman_name']} shortly.",
                    'is_read' => false,
                ]);

                return redirect()->route('service-requests.show', $serviceRequest)
                    ->with('success', 'Booking successful! Your service request has been created and payment confirmed.');
            } else {
                // Existing flow - service request already exists, just update status
                $serviceRequest = $payment->serviceRequest;
                
                $serviceRequest->update([
                    'status' => 'PENDING_ADMIN_ASSIGNMENT',
                ]);

                // Create notification for admin
                AppNotification::create([
                    'user_id' => null,
                    'service_request_id' => $serviceRequest->id,
                    'type' => 'BOOKING_FEE_PAID',
                    'title' => '💰 Booking Fee Paid - Ready for Assignment',
                    'message' => "Booking fee for service request #{$serviceRequest->id} has been paid. Please assign serviceman.",
                    'is_read' => false,
                ]);

                // Notify client
                AppNotification::create([
                    'user_id' => $serviceRequest->client_id,
                    'service_request_id' => $serviceRequest->id,
                    'type' => 'PAYMENT_CONFIRMED',
                    'title' => '✅ Payment Confirmed',
                    'message' => "Your booking fee has been received. We're processing your request.",
                    'is_read' => false,
                ]);

                return redirect()->route('service-requests.show', $serviceRequest)
                    ->with('success', 'Booking fee paid successfully! Serviceman will be assigned shortly.');
            }

        } elseif ($payment->payment_type === 'FINAL_PAYMENT') {
            // Load service request
            $serviceRequest = $payment->serviceRequest;
            
            if (!$serviceRequest) {
                return redirect()->route('dashboard')->with('error', 'Service request not found.');
            }
            
            // Update service request status to PAYMENT_CONFIRMED (admin will change to IN_PROGRESS after notifying serviceman)
            $serviceRequest->update([
                'status' => 'PAYMENT_CONFIRMED',
            ]);

            // Notify ADMIN ONLY - admin will then notify serviceman professionally
            AppNotification::create([
                'user_id' => null, // Admin notification
                'service_request_id' => $serviceRequest->id,
                'type' => 'FINAL_PAYMENT_RECEIVED',
                'title' => '💰 Final Payment Received - Action Required',
                'message' => "Final payment of ₦" . number_format($serviceRequest->final_cost) . " for service request #{$serviceRequest->id} has been received from {$serviceRequest->client->full_name}. Please contact and notify serviceman {$serviceRequest->serviceman->full_name} to begin work immediately.",
                'is_read' => false,
            ]);

            // Notify client - payment confirmed, admin will coordinate next steps
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'PAYMENT_CONFIRMED',
                'title' => '✅ Payment Confirmed',
                'message' => "Your final payment of ₦" . number_format($serviceRequest->final_cost) . " has been received successfully. Our admin team will coordinate with the serviceman to begin work shortly.",
                'is_read' => false,
            ]);

            return redirect()->route('service-requests.show', $serviceRequest)
                ->with('success', 'Final payment completed successfully! Our admin team will coordinate with the serviceman to begin work.');
        }

        return back()->with('success', 'Payment completed successfully!');
    }
}
