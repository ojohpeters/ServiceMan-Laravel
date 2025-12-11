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
        $prevTotal = $this->total_jobs_completed ?? 0;
        $prevRating = $this->rating ?? 0;
        
        \Log::info('Updating rating', [
            'serviceman_id' => $this->user_id,
            'prev_total' => $prevTotal,
            'prev_rating' => $prevRating,
            'new_rating' => $newRating
        ]);
        
        // If previous rating is negative (penalty debt), deduct it from the new rating
        // The penalty debt is "paid off" from the new rating
        if ($prevRating < 0) {
            // They have a penalty debt - deduct it from the new rating
            $penaltyDebt = abs($prevRating);
            $adjustedNewRating = max(0, $newRating - $penaltyDebt); // Deduct penalty, don't go below 0
            
            \Log::info('Penalty debt being paid off', [
                'penalty_debt' => $penaltyDebt,
                'original_new_rating' => $newRating,
                'adjusted_new_rating' => $adjustedNewRating
            ]);
            
            // If this is their first rating (no previous jobs), rating becomes the adjusted value
            if ($prevTotal == 0) {
                // First rating after penalty: rating becomes the adjusted value
                $newAvg = $adjustedNewRating;
            } else {
                // They had previous ratings. The penalty reduced their rating, so we need to:
                // Reconstruct the actual average before penalty, then add the adjusted new rating
                // Previous actual rating = prevRating + penaltyDebt (to undo the penalty)
                $prevActualRating = $prevRating + $penaltyDebt;
                // Now calculate normally with the adjusted new rating
                $newAvg = (($prevActualRating * $prevTotal) + $adjustedNewRating) / ($prevTotal + 1);
            }
        } else {
            // Normal calculation when no penalty
            $newAvg = (($prevRating * $prevTotal) + $newRating) / ($prevTotal + 1);
        }
        
        \Log::info('Rating updated', [
            'serviceman_id' => $this->user_id,
            'new_average' => $newAvg,
            'new_total_jobs' => $prevTotal + 1
        ]);
        
        $this->update([
            'rating' => round($newAvg, 2),
            'total_jobs_completed' => $prevTotal + 1
        ]);
    }

    public function applyRatingPenalty($penalty = 0.2)
    {
        // Apply penalty - can result in negative rating (debt)
        // This debt will be deducted from future ratings
        $currentRating = $this->rating ?? 0.0; // Start from 0 if no rating
        $newRating = $currentRating - $penalty; // Allow negative values (debt)
        
        \Log::info('Applying rating penalty', [
            'serviceman_id' => $this->user_id,
            'current_rating' => $currentRating,
            'penalty' => $penalty,
            'new_rating' => $newRating
        ]);
        
        $this->update([
            'rating' => round($newRating, 2)
        ]);
    }

    public function getCategoryRank()
    {
        if (!$this->category_id) {
            return null;
        }

        $currentRating = $this->rating ?? 0;

        // Get count of servicemen with higher rating in the same category
        $higherRatedCount = \App\Models\User::where('user_type', 'SERVICEMAN')
            ->whereHas('servicemanProfile', function($q) use ($currentRating) {
                $q->where('category_id', $this->category_id)
                  ->where('is_available', true)
                  ->whereNotNull('rating')
                  ->where('rating', '>', $currentRating);
            })
            ->count();

        return $higherRatedCount + 1;
    }

    // Scope
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}