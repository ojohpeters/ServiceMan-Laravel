<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'serviceman_id',
        'service_name',
        'service_description',
        'why_needed',
        'target_market',
        'status',
        'category_id',
        'admin_response',
        'approved_at',
        'rejected_at',
        'reviewed_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function serviceman()
    {
        return $this->belongsTo(User::class, 'serviceman_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }
}
