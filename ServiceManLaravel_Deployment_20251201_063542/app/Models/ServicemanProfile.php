<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicemanProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'rating',
        'total_jobs_completed',
        'bio',
        'years_of_experience',
        'experience_years',
        'skills',
        'phone_number',
        'is_available',
        'hourly_rate'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_available' => 'boolean'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Validate user type before creating/updating
        static::creating(function ($profile) {
            $user = User::find($profile->user_id);
            if (!$user || !$user->isServiceman()) {
                throw new \Exception('Only users with user_type SERVICEMAN can have a serviceman profile');
            }
        });
        
        static::updating(function ($profile) {
            if ($profile->isDirty('user_id')) {
                $user = User::find($profile->user_id);
                if (!$user || !$user->isServiceman()) {
                    throw new \Exception('Only users with user_type SERVICEMAN can have a serviceman profile');
                }
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->user ? $this->user->getFullNameAttribute() : '';
    }

    // Methods
    public function updateRating($newRating)
    {
        $prevTotal = $this->total_jobs_completed;
        $prevRating = $this->rating;
        
        $newAvg = (($prevRating * $prevTotal) + $newRating) / ($prevTotal + 1);
        
        $this->update([
            'rating' => round($newAvg, 2),
            'total_jobs_completed' => $prevTotal + 1
        ]);
    }

    // Scope
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}