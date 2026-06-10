<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Письмо с приложенным файлом экспорта персональных данных (GDPR) */
class GdprExportReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $filePath,
        public string $fileName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your personal data export is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gdpr-export-ready',
            with: [
                'userName' => $this->userName,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)->as($this->fileName)->withMime('application/json'),
        ];
    }
}
