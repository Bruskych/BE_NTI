<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Письмо верификации email с одноразовым кодом подтверждения */
class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $confirmationCode,
        public int $expiresIn = 3600, // 1 hour
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm your e-mail address',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-verification',
            with: [
                'userName' => $this->userName,
                'confirmationCode' => $this->confirmationCode,
                'expiresIn' => $this->expiresIn,
            ],
        );
    }
}
