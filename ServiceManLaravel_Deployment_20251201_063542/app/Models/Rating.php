<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'client_id',
        'serviceman_id',
        'rating',
        'review',
        'is_featured'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean'
    ];

    // Relationships
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function serviceman()
    {
        return $this->belongsTo(User::class, 'serviceman_id');
    }

    // Scopes
    public function scopeForServiceman($query, $servicemanId)
    {
        return $query->where('serviceman_id', $servicemanId);
    }

    // Validation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rating) {
            // Ensure rating is between 1 and 5
            if ($rating->rating < 1 || $rating->rating > 5) {
                throw new \InvalidArgumentException('Rating must be between 1 and 5');
            }
        });
    }
}