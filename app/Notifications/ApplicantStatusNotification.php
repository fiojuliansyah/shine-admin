<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Broadcasting\WhatsappChannel;
use App\Models\Applicant;

class ApplicantStatusNotification extends Notification
{
    use Queueable;

    protected $applicant;

    /**
     * Create a new notification instance.
     * * @param Applicant $applicant
     */
    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        // Menggunakan channel WhatsApp kustom Anda
        return [WhatsappChannel::class];
    }

    /**
     * Representation of the notification for WhatsApp.
     */
    public function toWhatsapp($notifiable)
    {
        $applicant = $this->applicant;
        $careerName = $applicant->career->name ?? 'Posisi Pekerjaan';
        $statusName = $applicant->status->name ?? 'Update Status';
        
        // Pesan WA kustom berdasarkan status
        $waMessage = "📢 *UPDATE STATUS LAMARAN* 📢\n\n";
        $waMessage .= "Halo *{$applicant->name}*,\n";
        $waMessage .= "Kami ingin menginformasikan bahwa terdapat update pada lamaran kerja Anda.\n\n";
        
        $waMessage .= "*Detail Lamaran:*\n";
        $waMessage .= "• Posisi: {$careerName}\n";
        $waMessage .= "• Status Saat Ini: *{$statusName}*\n\n";
        
        $waMessage .= "Terima kasih telah berpartisipasi dalam proses rekrutmen kami. Silakan pantau terus informasi selanjutnya melalui website kami.\n\n";
        $waMessage .= "Salam,\n";
        $waMessage .= "*Tim HR Ciptakarir*";

        return [
            'to' => $this->formatPhoneNumber($applicant->phone),
            'message' => $waMessage,
        ];
    }

    /**
     * Format nomor telepon ke standar internasional (62)
     */
    private function formatPhoneNumber($phone)
    {
        // Menghilangkan karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Mengganti awalan '0' menjadi '62'
        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }
        
        return $phone;
    }
}