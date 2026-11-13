<?php

namespace App\Mail;

use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Teacher $teacher,
        public array $approvedLevels,
        public string $username,
        public string $password
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Selamat! Akun Tutor Kawal Belajar Anda Disetujui');
    }

    public function content(): Content
    {
        return new Content(view: 'Emails.Tutor.approved');
    }
}
