<?php

namespace App\Mail\Util;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationOtp extends Mailable
{
    use Queueable, SerializesModels;

    private $otp;
    private $name;

    private $subjectEmail;
    private $action;
    private $ignore;
    /**
     * Create a new message instance.
     */
    public function __construct($otp, $type, $name = null)
    {
        $this->otp = $otp;
        $this->name = $name;
        $this->ignore = true;
        switch ($type) {
            case 'account':
                $this->subjectEmail = 'Verification Account';
                $this->action = 'This is your verification code :';
                $this->ignore = false;
                break;
            case 'forgot-password':
                $this->subjectEmail = 'Forgot Password';
                $this->action = 'Please enter otp below to complete your forgot password :';
                break;
            case 'edit-profile':
                $this->subjectEmail = 'Edit Profile';
                $this->action = 'Please enter otp below to complete your edit profile :';
                break;
            default:
                break;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectEmail,
        );
    }

    /**
     * Get the message content definition.
     */

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.verification-otp',
            with: [
                'otp' => $this->otp,
                'name' => $this->name,
                'action' => $this->action,
                'ignore' => $this->ignore,
            ],
        );
    }
}
