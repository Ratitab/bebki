<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\DynamicEmail;
use Exception;

class EmailSendingService
{
    /**
     * Send an email using a Mailable class
     *
     * @param string|array $to
     * @param \Illuminate\Mail\Mailable $mailable
     * @return bool
     */
    public function send($to, $mailable): bool
    {
        try {
            Mail::to($to)->send($mailable);

            Log::info('Email sent successfully', [
                'to' => $to,
                'mailable' => get_class($mailable)
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send dynamic email with template and parameters
     *
     * @param string|array $to
     * @param string $template - Template name (e.g., 'emails.welcome')
     * @param array $content - Data for the template
     * @param string $subject - Email subject
     * @param array $attachments - Optional file attachments
     * @return bool
     */
    public function sendDynamic(
        $to,
        string $template,
        array $content = [],
        string $subject = '',
        array $attachments = []
    ): bool {
        try {
            $mailable = new DynamicEmail($template, $content, $subject, $attachments);
            Mail::to($to)->send($mailable);

            Log::info('Dynamic email sent successfully', [
                'to' => $to,
                'template' => $template,
                'subject' => $subject
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send dynamic email', [
                'to' => $to,
                'template' => $template,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Queue dynamic email for later sending
     *
     * @param string|array $to
     * @param string $template
     * @param array $content
     * @param string $subject
     * @param array $attachments
     * @return bool
     */
    public function queueDynamic(
        $to,
        string $template,
        array $content = [],
        string $subject = '',
        array $attachments = []
    ): bool {
        try {
            $mailable = new DynamicEmail($template, $content, $subject, $attachments);
            Mail::to($to)->queue($mailable);

            Log::info('Dynamic email queued successfully', [
                'to' => $to,
                'template' => $template,
                'subject' => $subject
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to queue dynamic email', [
                'to' => $to,
                'template' => $template,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send email to multiple recipients
     *
     * @param array $recipients
     * @param \Illuminate\Mail\Mailable $mailable
     * @return bool
     */
    public function sendToMultiple(array $recipients, $mailable): bool
    {
        try {
            Mail::to($recipients)->send($mailable);

            Log::info('Email sent to multiple recipients', [
                'recipients_count' => count($recipients),
                'mailable' => get_class($mailable)
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email to multiple recipients', [
                'recipients_count' => count($recipients),
                'mailable' => get_class($mailable),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send dynamic email to multiple recipients
     *
     * @param array $recipients
     * @param string $template
     * @param array $content
     * @param string $subject
     * @param array $attachments
     * @return bool
     */
    public function sendDynamicToMultiple(
        array $recipients,
        string $template,
        array $content = [],
        string $subject = '',
        array $attachments = []
    ): bool {
        try {
            $mailable = new DynamicEmail($template, $content, $subject, $attachments);
            Mail::to($recipients)->send($mailable);

            Log::info('Dynamic email sent to multiple recipients', [
                'recipients_count' => count($recipients),
                'template' => $template,
                'subject' => $subject
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send dynamic email to multiple recipients', [
                'recipients_count' => count($recipients),
                'template' => $template,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send email with CC
     *
     * @param string|array $to
     * @param string|array $cc
     * @param \Illuminate\Mail\Mailable $mailable
     * @return bool
     */
    public function sendWithCC($to, $cc, $mailable): bool
    {
        try {
            Mail::to($to)->cc($cc)->send($mailable);

            Log::info('Email sent with CC', [
                'to' => $to,
                'cc' => $cc,
                'mailable' => get_class($mailable)
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email with CC', [
                'to' => $to,
                'cc' => $cc,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send dynamic email with CC
     *
     * @param string|array $to
     * @param string|array $cc
     * @param string $template
     * @param array $content
     * @param string $subject
     * @param array $attachments
     * @return bool
     */
    public function sendDynamicWithCC(
        $to,
        $cc,
        string $template,
        array $content = [],
        string $subject = '',
        array $attachments = []
    ): bool {
        try {
            $mailable = new DynamicEmail($template, $content, $subject, $attachments);
            Mail::to($to)->cc($cc)->send($mailable);

            Log::info('Dynamic email sent with CC', [
                'to' => $to,
                'cc' => $cc,
                'template' => $template,
                'subject' => $subject
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send dynamic email with CC', [
                'to' => $to,
                'cc' => $cc,
                'template' => $template,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send email with BCC
     *
     * @param string|array $to
     * @param string|array $bcc
     * @param \Illuminate\Mail\Mailable $mailable
     * @return bool
     */
    public function sendWithBCC($to, $bcc, $mailable): bool
    {
        try {
            Mail::to($to)->bcc($bcc)->send($mailable);

            Log::info('Email sent with BCC', [
                'to' => $to,
                'mailable' => get_class($mailable)
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email with BCC', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Queue an email for later sending
     *
     * @param string|array $to
     * @param \Illuminate\Mail\Mailable $mailable
     * @return bool
     */
    public function queue($to, $mailable): bool
    {
        try {
            Mail::to($to)->queue($mailable);

            Log::info('Email queued successfully', [
                'to' => $to,
                'mailable' => get_class($mailable)
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to queue email', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
