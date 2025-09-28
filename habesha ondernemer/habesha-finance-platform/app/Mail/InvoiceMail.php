<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

final class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?string $message = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Factuur {$this->invoice->invoice_number} - {$this->invoice->client->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'message' => $this->message,
            ],
        );
    }

    public function attachments(): array
    {
        // Generate PDF
        $this->invoice->load(['client', 'project', 'items', 'user']);
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this->invoice]);
        
        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                "factuur-{$this->invoice->invoice_number}.pdf",
                [
                    'mime' => 'application/pdf',
                ]
            ),
        ];
    }
}