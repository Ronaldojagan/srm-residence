<?php

namespace App\Mail;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Bill $bill;
    public array $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(Bill $bill, array $settings = [])
    {
        $this->bill = $bill;
        $this->settings = !empty($settings) ? $settings : app(\App\Service\SettingsService::class)->settingsIndex();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $residence = $this->settings['residence_name'] ?? 'SRM Residence';
        $month = $this->bill->billing_month ?: now()->format('F Y');

        return new Envelope(
            subject: "Rent Invoice #{$this->bill->bill_number} ({$month}) - {$residence}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
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
