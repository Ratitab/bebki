<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $emailSubject;
    public $templateName;
    public $attachmentFiles;

    /**
     * Create a new message instance.
     *
     * @param string $template - Template name (e.g., 'emails.welcome', 'emails.booking-confirmation')
     * @param array $content - Data to pass to the template (accessible as $content['key'])
     * @param string $subject - Email subject
     * @param array $attachments - Optional array of file paths to attach
     */
    public function __construct(
        string $template,
        array $content = [],
        string $subject = '',
        array $attachments = []
    ) {
        $this->templateName = $template;
        $this->content = $content;
        $this->emailSubject = $subject ?: 'Notification from ' . config('app.name');
        $this->attachmentFiles = $attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->templateName,
            with: ['content' => $this->content]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->attachmentFiles as $file) {
            if (is_string($file)) {
                $attachments[] = Attachment::fromPath($file);
            } elseif (is_array($file) && isset($file['path'])) {
                $attachment = Attachment::fromPath($file['path']);

                if (isset($file['as'])) {
                    $attachment->as($file['as']);
                }

                if (isset($file['mime'])) {
                    $attachment->withMime($file['mime']);
                }

                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }
}
