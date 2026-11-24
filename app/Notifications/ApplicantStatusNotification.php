<?php

namespace App\Notifications;

use App\Broadcasting\WhatsappChannel;
use App\Models\Applicant;
use App\Models\MessageTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicantStatusNotification extends Notification
{
    use Queueable;

    public const CHANNELS = ['whatsapp', 'email'];

    protected Applicant $applicant;
    protected ?MessageTemplate $template;
    protected array $channels;

    /**
     * @param  array  $channels  subset dari ['whatsapp', 'email']
     * @param  MessageTemplate|null  $template  null = template default (pertama yang aktif)
     */
    public function __construct(Applicant $applicant, ?MessageTemplate $template = null, array $channels = ['whatsapp'])
    {
        $this->applicant = $applicant->loadMissing(['user.site.company', 'career', 'status']);
        $this->template = $template ?? MessageTemplate::active()->orderBy('id')->first();
        $this->channels = array_values(array_intersect($channels, self::CHANNELS));
    }

    /**
     * Buat notifikasi dari pilihan di modal konfirmasi
     * (notify_channels[] & message_template_id). Null = tidak kirim notifikasi.
     */
    public static function fromRequest(Applicant $applicant, \Illuminate\Http\Request $request): ?self
    {
        $channels = array_values(array_intersect((array) $request->input('notify_channels', []), self::CHANNELS));
        if (empty($channels)) {
            return null;
        }

        $template = $request->filled('message_template_id')
            ? MessageTemplate::find($request->input('message_template_id'))
            : null;

        return new self($applicant, $template, $channels);
    }

    public function via($notifiable)
    {
        $via = [];

        if (in_array('whatsapp', $this->channels, true) && filled($this->applicant->user?->phone)) {
            $via[] = WhatsappChannel::class;
        }
        if (in_array('email', $this->channels, true) && filled($this->applicant->user?->email)) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toWhatsapp($notifiable)
    {
        return [
            'to' => $this->formatPhoneNumber($this->applicant->user->phone ?? ''),
            'message' => $this->body(),
        ];
    }

    public function toMail($notifiable)
    {
        $subject = $this->template
            ? $this->template->renderSubject($this->applicant)
            : 'Update Status Lamaran';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.applicant-status', [
                'subject' => $subject,
                'bodyHtml' => self::whatsappToHtml($this->body()),
            ]);
    }

    protected function body(): string
    {
        if ($this->template) {
            return $this->template->renderBody($this->applicant);
        }

        // Cadangan bila belum ada template sama sekali.
        $values = MessageTemplate::valuesFor($this->applicant);

        return "📢 *UPDATE STATUS LAMARAN* 📢\n\nHalo *{$values['{nama}']}*,\n"
            . "Status lamaran Anda untuk posisi {$values['{posisi}']} kini: *{$values['{status}']}*.\n\n"
            . "Salam,\n*Tim HR Ciptakarir*";
    }

    /**
     * Ubah format WhatsApp (*tebal*, _miring_, ~coret~, baris baru) menjadi HTML aman untuk email.
     */
    public static function whatsappToHtml(string $text): string
    {
        $html = e($text);
        $html = preg_replace('/\*(\S(?:[^*\n]*\S)?)\*/u', '<strong>$1</strong>', $html);
        $html = preg_replace('/(?<![\w])_(\S(?:[^_\n]*\S)?)_(?![\w])/u', '<em>$1</em>', $html);
        $html = preg_replace('/~(\S(?:[^~\n]*\S)?)~/u', '<s>$1</s>', $html);

        return nl2br($html);
    }

    private function formatPhoneNumber($phone)
    {
        if (!$phone) return "";

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        return $phone;
    }
}
