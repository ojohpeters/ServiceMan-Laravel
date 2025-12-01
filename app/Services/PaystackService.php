<?php

namespace App\Services;

use Yabacon\Paystack;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected $paystack;

    public function __construct()
    {
        $this->paystack = new Paystack(config('paystack.secret_key'));
    }

    /**
     * Initialize a payment transaction
     */
    public function initializePayment(array $data)
    {
        try {
            $transaction = $this->paystack->transaction->initialize([
                'amount' => $data['amount'] * 100, // Convert to kobo
                'email' => $data['email'],
                'reference' => $data['reference'],
                'callback_url' => config('paystack.callback_url'),
                'metadata' => [
                    'service_request_id' => $data['service_request_id'],
                    'payment_type' => $data['payment_type'],
                    'user_id' => $data['user_id'],
                ],
            ]);

            if ($transaction->status) {
                return [
                    'success' => true,
                    'authorization_url' => $transaction->data->authorization_url,
                    'access_code' => $transaction->data->access_code,
                    'reference' => $transaction->data->reference,
                ];
            }

            return [
                'success' => false,
                'message' => $transaction->message ?? 'Payment initialization failed',
            ];

        } catch (\Exception $e) {
            Log::error('Paystack initialization error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment initialization failed. Please try again.',
            ];
        }
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $reference)
    {
        try {
            $transaction = $this->paystack->transaction->verify([
                'reference' => $reference,
            ]);

            if ($transaction->status && $transaction->data->status === 'success') {
                return [
                    'success' => true,
                    'data' => $transaction->data,
                ];
            }

            return [
                'success' => false,
                'message' => $transaction->message ?? 'Payment verification failed',
            ];

        } catch (\Exception $e) {
            Log::error('Paystack verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment verification failed. Please try again.',
            ];
        }
    }

    /**
     * Create a payment record
     */
    public function createPaymentRecord(array $data)
    {
        return Payment::create([
            'service_request_id' => $data['service_request_id'],
            'payment_type' => $data['payment_type'],
            'amount' => $data['amount'],
            'paystack_reference' => $data['reference'],
            'paystack_access_code' => $data['access_code'] ?? null,
            'status' => 'PENDING',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $reference, string $status, array $data = [])
    {
        $payment = Payment::where('paystack_reference', $reference)->first();
        
        if ($payment) {
            $payment->update([
                'status' => $status,
                'paid_at' => $status === 'SUCCESSFUL' ? now() : null,
                'metadata' => array_merge($payment->metadata ?? [], $data),
            ]);
            
            return $payment;
        }

        return null;
    }

    /**
     * Generate a unique reference
     */
    public function generateReference(int|string $serviceRequestId, string $paymentType)
    {
        $timestamp = time();
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Handle pending bookings (service request not yet created)
        if ($serviceRequestId === 'pending') {
            return "PENDING-{$paymentType}-{$timestamp}-{$random}";
        }
        
        return "SR{$serviceRequestId}-{$paymentType}-{$timestamp}-{$random}";
    }

    /**
     * Get payment history for a user
     */
    public function getPaymentHistory(int $userId, string $userType = 'CLIENT')
    {
        $query = Payment::with(['serviceRequest.category'])
            ->whereHas('serviceRequest', function($q) use ($userId, $userType) {
                if ($userType === 'CLIENT') {
                    $q->where('client_id', $userId);
                } elseif ($userType === 'SERVICEMAN') {
                    $q->where('serviceman_id', $userId);
                }
            })
            ->where('status', 'SUCCESSFUL')
            ->latest();

        return $query->paginate(10);
    }

    /**
     * Calculate admin fee (markup)
     */
    public function calculateAdminFee(float $amount, float $markupPercentage = 10)
    {
        return $amount * ($markupPercentage / 100);
    }

    /**
     * Get total revenue for admin
     */
    public function getTotalRevenue()
    {
        return Payment::where('status', 'SUCCESSFUL')->sum('amount');
    }

    /**
     * Get monthly revenue
     */
    public function getMonthlyRevenue()
    {
        return Payment::where('status', 'SUCCESSFUL')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');
    }
}
