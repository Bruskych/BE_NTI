<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Письмо подтверждения доступа к документу с одноразовым кодом */
class DocumentAccessConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userEmail,
        public string $confirmationCode,
        public string $documentName,
        public int $expiresIn = 3600, // 1 hour
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm your document access - ' . $this->documentName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-access-confirmation',
            with: [
                'confirmationCode' => $this->confirmationCode,
                'documentName' => $this->documentName,
                'expiresIn' => $this->expiresIn,
            ],
        );
    }
}
