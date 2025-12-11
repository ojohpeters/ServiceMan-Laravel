<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_CHOICES = [
        'PENDING_ADMIN_ASSIGNMENT' => 'Pending Admin Assignment',
        'ASSIGNED_TO_SERVICEMAN' => 'Assigned to Serviceman',
        'SERVICEMAN_INSPECTED' => 'Serviceman Inspected',
        'AWAITING_CLIENT_APPROVAL' => 'Awaiting Client Approval',
        'NEGOTIATING' => 'Negotiating',
        'AWAITING_PAYMENT' => 'Awaiting Payment',
        'PAYMENT_CONFIRMED' => 'Payment Confirmed',
        'IN_PROGRESS' => 'In Progress',
        'WORK_COMPLETED' => 'Work Completed',
        'COMPLETED' => 'Completed',
        'CANCELLED' => 'Cancelled'
    ];

    protected $fillable = [
        'client_id',
        'serviceman_id',
        'backup_serviceman_id',
        'category_id',
        'title',
        'description',
        'location',
        'booking_date',
        'is_emergency',
        'auto_flagged_emergency',
        'status',
        'initial_booking_fee',
        'serviceman_estimated_cost',
        'admin_markup_percentage',
        'final_cost',
        'client_address',
        'service_description',
        'inspection_completed_at',
        'work_completed_at',
        'accepted_at',
        'was_declined_after_acceptance',
        'is_deleted'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'is_emergency' => 'boolean',
        'auto_flagged_emergency' => 'boolean',
        'initial_booking_fee' => 'decimal:2',
        'serviceman_estimated_cost' => 'decimal:2',
        'admin_markup_percentage' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'inspection_completed_at' => 'datetime',
        'work_completed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'was_declined_after_acceptance' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function serviceman()
    {
        return $this->belongsTo(User::class, 'serviceman_id');
    }

    public function backupServiceman()
    {
        return $this->belongsTo(User::class, 'backup_serviceman_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function negotiations()
    {
        return $this->hasMany(PriceNegotiation::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    // Methods
    public function checkEmergencyFlag()
    {
        $bookingDate = Carbon::parse($this->booking_date);
        $today = Carbon::today();
        
        if ($bookingDate->diffInDays($today) < 2) {
            $this->update([
                'is_emergency' => true,
                'auto_flagged_emergency' => true
            ]);
            return true;
        }
        return false;
    }

    public function calculateBookingFee()
    {
        return $this->is_emergency ? 5000 : 2000;
    }

    public function calculateFinalCost()
    {
        if ($this->serviceman_estimated_cost && $this->admin_markup_percentage) {
            $markup = $this->serviceman_estimated_cost * ($this->admin_markup_percentage / 100);
            return $this->serviceman_estimated_cost + $markup;
        }
        return null;
    }

    // Scopes
    public function scopeForUser($query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        } elseif ($user->isClient()) {
            return $query->where('client_id', $user->id);
        } elseif ($user->isServiceman()) {
            return $query->where(function ($q) use ($user) {
                $q->where('serviceman_id', $user->id)
                  ->orWhere('backup_serviceman_id', $user->id);
            });
        }
        return $query->whereRaw('1 = 0'); // Return empty result
    }

    public function scopePendingAssignment($query)
    {
        return $query->where('status', 'PENDING_ADMIN_ASSIGNMENT');
    }

    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }
}