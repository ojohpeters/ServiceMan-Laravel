<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    const USER_TYPES = [
        'ADMIN' => 'Admin',
        'SERVICEMAN' => 'Serviceman',
        'CLIENT' => 'Client'
    ];

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'user_type',
        'is_email_verified',
        'email_verified_at',
        'email_verification_token',
        'remember_token',
        'profile_picture',
        'is_approved',
        'approved_at',
        'approved_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_email_verified' => 'boolean',
        'password' => 'hashed',
    ];


    public function getFullNameAttribute()
    {
        $fullName = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        return !empty($fullName) ? $fullName : ($this->username ?? 'Unknown');
    }

    public function getProfilePictureUrlAttribute()
    {
        // Check if profile picture exists in public/uploads
        if ($this->profile_picture) {
            $path = public_path($this->profile_picture);
            if (file_exists($path)) {
                return asset($this->profile_picture);
            }
            
            // Fallback: check in storage/app/public
            $storagePath = storage_path('app/public/' . $this->profile_picture);
            if (file_exists($storagePath)) {
                return asset('storage/' . $this->profile_picture);
            }
        }
        
        // Default profile pictures based on user type
        if ($this->user_type === 'SERVICEMAN') {
            return asset('images/default-serviceman.jpg');
        } elseif ($this->user_type === 'CLIENT') {
            return asset('images/default-client.jpg');
        } else {
            return asset('images/default-admin.jpg');
        }
    }

    public function isAdmin()
    {
        return $this->user_type === 'ADMIN';
    }

    public function isServiceman()
    {
        return $this->user_type === 'SERVICEMAN';
    }

    public function isClient()
    {
        return $this->user_type === 'CLIENT';
    }

    // Relationships
    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function servicemanProfile()
    {
        return $this->hasOne(ServicemanProfile::class);
    }

    public function clientRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id');
    }

    public function servicemanRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'serviceman_id');
    }

    public function backupRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'backup_serviceman_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'client_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'serviceman_id');
    }

    public function negotiations()
    {
        return $this->hasMany(PriceNegotiation::class, 'proposed_by');
    }
}