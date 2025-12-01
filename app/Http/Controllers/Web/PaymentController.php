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
use Illuminate\Support\Str;

class PaymentController extends Controller
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
        } elseif ($user->isServiceman()) {
            $payments = Payment::whereHas('serviceRequest', function($query) use ($user) {
                $query->where('serviceman_id', $user->id);
            })->with(['serviceRequest.category'])->latest()->paginate(10);
        } else {
            // Admin view
            $payments = Payment::with(['serviceRequest.category', 'serviceRequest.client'])->latest()->paginate(10);
        }

        return view('payments.index', compact('payments'));
    }

    public function initialize(Request $request)
    {
        // Debug logging
        \Log::info('Payment initialize request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'service_request' => 'required|exists:service_requests,id',
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

        $serviceRequest = ServiceRequest::findOrFail($request->service_request);
        $user = Auth::user();

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

        // Create payment record
        $payment = Payment::create([
            'service_request_id' => $serviceRequest->id,
            'payment_type' => $request->type,
            'amount' => $request->amount,
            'paystack_reference' => $this->generatePaystackReference($serviceRequest->id, $request->type),
            'status' => 'PENDING',
        ]);

        // For demo purposes, we'll simulate payment success
        // In production, you would integrate with Paystack here
        if ($request->expectsJson()) {
            return $this->simulatePaymentSuccess($payment, true);
        }
        return $this->simulatePaymentSuccess($payment);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $payment = Payment::where('paystack_reference', $request->reference)->firstOrFail();
        
        // In production, verify with Paystack API
        // For demo, we'll assume payment is successful
        return $this->handlePaymentSuccess($payment);
    }

    public function history()
    {
        $user = Auth::user();
        
        $payments = Payment::whereHas('serviceRequest', function($query) use ($user) {
            if ($user->isClient()) {
                $query->where('client_id', $user->id);
            } elseif ($user->isServiceman()) {
                $query->where('serviceman_id', $user->id);
            }
        })->with(['serviceRequest.category'])
          ->where('status', 'SUCCESSFUL')
          ->latest()
          ->paginate(10);

        return view('payments.history', compact('payments'));
    }

    private function generatePaystackReference($serviceRequestId, $paymentType)
    {
        $timestamp = time();
        return "{$serviceRequestId}-{$paymentType}-{$timestamp}";
    }

    private function simulatePaymentSuccess(Payment $payment, $isJson = false)
    {
        // Simulate payment processing delay
        sleep(1);
        
        // Update payment status
        $payment->update([
            'status' => 'SUCCESSFUL',
            'paid_at' => now(),
        ]);

        return $this->handlePaymentSuccess($payment, $isJson);
    }

    private function handlePaymentSuccess(Payment $payment, $isJson = false)
    {
        $serviceRequest = $payment->serviceRequest;

        if ($payment->payment_type === 'INITIAL_BOOKING') {
            // Update service request status - still pending admin assignment
            $serviceRequest->update([
                'status' => 'PENDING_ADMIN_ASSIGNMENT', // Admin still needs to assign
            ]);

            // Create notification for admin - booking fee paid, ready for assignment
            AppNotification::create([
                'user_id' => null, // Admin notification
                'service_request_id' => $serviceRequest->id,
                'type' => 'BOOKING_FEE_PAID',
                'title' => 'Booking Fee Paid - Ready for Assignment',
                'message' => "Booking fee for service request #{$serviceRequest->id} has been paid. Please assign the serviceman and contact them.",
                'is_read' => false,
            ]);

            // Create notification for client - payment confirmed
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'PAYMENT_CONFIRMED',
                'title' => 'Payment Confirmed',
                'message' => "Your booking fee has been received. We're now processing your request and will contact the serviceman shortly.",
                'is_read' => false,
            ]);

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking fee paid successfully! A serviceman will be assigned to your request shortly.',
                    'redirect_url' => route('service-requests.show', $serviceRequest)
                ]);
            }
            return redirect()->route('service-requests.show', $serviceRequest)
                ->with('success', 'Booking fee paid successfully! A serviceman will be assigned to your request shortly.');

        } elseif ($payment->payment_type === 'FINAL_PAYMENT') {
            // Update service request status
            $serviceRequest->update([
                'status' => 'IN_PROGRESS',
            ]);

            // Create notification for admin
            AppNotification::create([
                'user_id' => null, // Admin notification
                'service_request_id' => $serviceRequest->id,
                'type' => 'FINAL_PAYMENT_RECEIVED',
                'title' => 'Final Payment Received',
                'message' => "Final payment of ₦{$serviceRequest->final_cost} for service request #{$serviceRequest->id} has been received. Please notify the serviceman to begin work.",
                'is_read' => false,
            ]);

            // Create notification for serviceman
            if ($serviceRequest->serviceman) {
                AppNotification::create([
                    'user_id' => $serviceRequest->serviceman_id,
                    'service_request_id' => $serviceRequest->id,
                    'type' => 'PAYMENT_RECEIVED',
                    'title' => 'Final Payment Received - Begin Work',
                    'message' => "Final payment for service request #{$serviceRequest->id} has been received. Please begin the work as soon as possible.",
                    'is_read' => false,
                ]);
            }

            // Create notification for client
            AppNotification::create([
                'user_id' => $serviceRequest->client_id,
                'service_request_id' => $serviceRequest->id,
                'type' => 'SERVICE_STARTED',
                'title' => 'Service Started',
                'message' => "Your final payment has been received. The serviceman will begin work on your request shortly.",
                'is_read' => false,
            ]);

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Final payment completed successfully! The serviceman will begin work shortly.',
                    'redirect_url' => route('service-requests.show', $serviceRequest)
                ]);
            }
            return redirect()->route('service-requests.show', $serviceRequest)
                ->with('success', 'Final payment completed successfully! The serviceman will begin work shortly.');
        }

        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully!'
            ]);
        }
        return back()->with('success', 'Payment completed successfully!');
    }

    // Webhook handler for Paystack (for production)
    public function webhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        
        // In production, verify the signature with Paystack
        // $expectedSignature = hash_hmac('sha512', $payload, config('paystack.secret_key'));
        // if (!hash_equals($expectedSignature, $signature)) {
        //     abort(400, 'Invalid signature');
        // }

        $event = $request->json()->all();
        
        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $payment = Payment::where('paystack_reference', $reference)->first();
            
            if ($payment && $payment->status === 'PENDING') {
                $payment->update([
                    'status' => 'SUCCESSFUL',
                    'paid_at' => now(),
                ]);
                
                $this->handlePaymentSuccess($payment);
            }
        }

        return response()->json(['status' => 'success']);
    }
}