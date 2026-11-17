<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorApprovedCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tutorName,
        public string $email,
        public string $temporaryPassword,
        public array $verifiedLevels,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pendaftaran Tutor KawalBelajar Disetujui');
    }

    public function content(): Content
    {
        return new Content(view: 'Emails.Tutor.approved-credentials');
    }
}
