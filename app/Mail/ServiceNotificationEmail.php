<?php

namespace App\Mail;

use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceNotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $title;
    public $message;
    public $serviceRequest;
    public $type;
    public $extraData;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $title, string $message, ?ServiceRequest $serviceRequest = null, string $type = 'GENERAL', array $extraData = [])
    {
        $this->user = $user;
        $this->title = $title;
        $this->message = $message;
        $this->serviceRequest = $serviceRequest;
        $this->type = $type;
        $this->extraData = $extraData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title . ' - ServiceMan',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.service-notification',
            with: [
                'user' => $this->user,
                'title' => $this->title,
                'message' => $this->message,
                'serviceRequest' => $this->serviceRequest,
                'type' => $this->type,
                'extraData' => $this->extraData,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

