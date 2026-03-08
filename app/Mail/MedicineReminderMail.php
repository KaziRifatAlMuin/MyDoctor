<?php

namespace App\Mail;

use App\Models\MedicineReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MedicineReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reminder;

    public function __construct(MedicineReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Medicine Reminder: ' . $this->reminder->schedule->medicine->medicine_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.medicine-reminder',
        );
    }
}