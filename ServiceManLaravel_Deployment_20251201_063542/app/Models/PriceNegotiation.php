<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceNegotiation extends Model
{
    use HasFactory;

    const STATUS_CHOICES = [
        'PENDING' => 'Pending',
        'ACCEPTED' => 'Accepted',
        'REJECTED' => 'Rejected',
        'COUNTERED' => 'Countered'
    ];

    protected $fillable = [
        'service_request_id',
        'proposed_by',
        'proposed_amount',
        'reason',
        'message',
        'admin_response',
        'counter_amount',
        'processed_by',
        'processed_at',
        'status'
    ];

    protected $casts = [
        'proposed_amount' => 'decimal:2',
        'counter_amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    // Relationships
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function proposedBy()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Methods
    public function accept()
    {
        $this->update(['status' => 'ACCEPTED']);
    }

    public function reject()
    {
        $this->update(['status' => 'REJECTED']);
    }

    public function counter()
    {
        $this->update(['status' => 'COUNTERED']);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'ACCEPTED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }
}