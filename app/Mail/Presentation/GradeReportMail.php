<?php

namespace App\Mail\Presentation;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GradeReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $report_title,
        public string $recipient_name,
        public string $note,
        public string $pdf_contents,
        public string $pdf_filename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->report_title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.presentation.grade-report',
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdf_contents, $this->pdf_filename)
                ->withMime('application/pdf'),
        ];
    }
}
