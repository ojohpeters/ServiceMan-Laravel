<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const PAYMENT_TYPE_CHOICES = [
        'INITIAL_BOOKING' => 'Initial Booking',
        'FINAL_PAYMENT' => 'Final Payment'
    ];

    const STATUS_CHOICES = [
        'PENDING' => 'Pending',
        'SUCCESSFUL' => 'Successful',
        'FAILED' => 'Failed'
    ];

    protected $fillable = [
        'service_request_id',
        'payment_type',
        'amount',
        'paystack_reference',
        'paystack_access_code',
        'status',
        'metadata',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    // Methods
    public function markAsSuccessful()
    {
        $this->update([
            'status' => 'SUCCESSFUL',
            'paid_at' => now()
        ]);
    }

    public function markAsFailed()
    {
        $this->update([
            'status' => 'FAILED'
        ]);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'SUCCESSFUL');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }
}