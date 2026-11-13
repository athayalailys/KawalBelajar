<?php

namespace App\Mail;

use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Teacher $teacher,
        public string $reason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pemberitahuan Pendaftaran Tutor Kawal Belajar');
    }

    public function content(): Content
    {
        return new Content(view: 'Emails.Tutor.rejected');
    }
}
