<?php

namespace App\Notifications;

use App\Broadcasting\WhatsappChannel;
use App\Models\MessageTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi umum ke user/karyawan berbasis Template Pesan (WhatsApp dan/atau Email).
 * Dipakai mis. saat revisi periode kontrak massal.
 */
class TemplateMessageNotification extends Notification
{
    use Queueable;

    protected array $channels;
    protected array $rendered;

    /**
     * @param  array  $values    hasil MessageTemplate::valuesForUser()
     * @param  array  $channels  subset ['whatsapp', 'email']
     */
    public function __construct(?MessageTemplate $template, array $values, array $channels, string $fallbackSubject, string $fallbackBody)
    {
        $this->channels = array_values(array_intersect($channels, ApplicantStatusNotification::CHANNELS));
        $this->rendered = $template
            ? $template->renderFor($values)
            : ['subject' => strtr($fallbackSubject, $values), 'body' => strtr($fallbackBody, $values)];
    }

    public function via($notifiable)
    {
        $via = [];
        if (in_array('whatsapp', $this->channels, true) && filled($notifiable->phone ?? null)) {
            $via[] = WhatsappChannel::class;
        }
        if (in_array('email', $this->channels, true) && filled($notifiable->email ?? null)) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toWhatsapp($notifiable)
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $notifiable->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return ['to' => $phone, 'message' => $this->rendered['body']];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->rendered['subject'])
            ->view('emails.applicant-status', [
                'subject' => $this->rendered['subject'],
                'bodyHtml' => ApplicantStatusNotification::whatsappToHtml($this->rendered['body']),
            ]);
    }
}
