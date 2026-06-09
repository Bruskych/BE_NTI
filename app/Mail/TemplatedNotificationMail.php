<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Письмо на основе управляемого администратором шаблона с подстановкой переменных */
class TemplatedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailTemplate $template,
        public array $variables = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bulk-message',
            with: [
                'subjectLine' => $this->template->subject,
                'body'        => $this->template->render($this->variables),
            ],
        );
    }
}
