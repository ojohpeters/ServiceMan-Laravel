<?php

namespace App\Services;

use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\AppNotification;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceNotificationEmail;

class NotificationService
{
    /**
     * Send notification to a user (both email and database)
     */
    public function notifyUser(User $user, string $type, string $title, string $message, ?ServiceRequest $serviceRequest = null, array $extraData = [])
    {
        // Create database notification
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'service_request_id' => $serviceRequest?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);

        // Queue email notification (async - doesn't block user)
        try {
            if ($user->email && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($user->email)->queue(new ServiceNotificationEmail(
                    $user,
                    $title,
                    $message,
                    $serviceRequest,
                    $type,
                    $extraData
                ));
                \Log::info('Notification email queued successfully', [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_type' => $user->user_type,
                    'type' => $type,
                    'email' => $user->email,
                    'driver' => config('mail.default'),
                    'title' => $title,
                ]);
            } else {
                \Log::warning('Notification email skipped - invalid or missing email', [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_type' => $user->user_type,
                    'email' => $user->email,
                    'type' => $type,
                ]);
            }
        } catch (\Exception $e) {
            // Log email error but don't fail the notification
            \Log::error('Failed to queue notification email', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'user_type' => $user->user_type,
                'type' => $type,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'driver' => config('mail.default'),
            ]);
        }

        return $notification;
    }

    /**
     * Notify admin users (both email and database)
     */
    public function notifyAdmins(string $type, string $title, string $message, ?ServiceRequest $serviceRequest = null, array $extraData = [])
    {
        $admins = User::where('user_type', 'ADMIN')->get();
        
        \Log::info('Notifying admins', [
            'admin_count' => $admins->count(),
            'type' => $type,
            'title' => $title,
            'admin_ids' => $admins->pluck('id')->toArray(),
            'admin_emails' => $admins->pluck('email')->toArray(),
        ]);
        
        foreach ($admins as $admin) {
            $this->notifyUser($admin, $type, $title, $message, $serviceRequest, $extraData);
        }
        
        \Log::info('Finished notifying all admins', [
            'admin_count' => $admins->count(),
            'type' => $type,
        ]);
    }

    /**
     * Notify serviceman (both email and database)
     */
    public function notifyServiceman(User $serviceman, string $type, string $title, string $message, ?ServiceRequest $serviceRequest = null, array $extraData = [])
    {
        if (!$serviceman->isServiceman()) {
            return null;
        }

        return $this->notifyUser($serviceman, $type, $title, $message, $serviceRequest, $extraData);
    }

    /**
     * Notify client (both email and database)
     */
    public function notifyClient(User $client, string $type, string $title, string $message, ?ServiceRequest $serviceRequest = null, array $extraData = [])
    {
        if (!$client->isClient()) {
            return null;
        }

        return $this->notifyUser($client, $type, $title, $message, $serviceRequest, $extraData);
    }
}

