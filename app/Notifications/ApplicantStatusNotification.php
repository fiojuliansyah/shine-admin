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

    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    public function via($notifiable)
    {
        return [WhatsappChannel::class];
    }

    public function toWhatsapp($notifiable)
    {
        // Mengambil data dari property $applicant yang sudah di-set di constructor
        $applicant = $this->applicant;
        
        // Ambil nomor telepon dari relasi user
        // Pastikan relasi 'user' sudah di-load di Controller
        $phoneNumber = $applicant->user->phone ?? ''; 
        
        $careerName = $applicant->career->name ?? 'Posisi Pekerjaan';
        $statusName = $applicant->status->name ?? 'Update Status';
        
        $waMessage = "📢 *UPDATE STATUS LAMARAN* 📢\n\n";
        $waMessage .= "Halo *{$applicant->user->name}*,\n"; // Mengambil nama dari user
        $waMessage .= "Kami ingin menginformasikan bahwa terdapat update pada lamaran kerja Anda.\n\n";
        
        $waMessage .= "*Detail Lamaran:*\n";
        $waMessage .= "• Posisi: {$careerName}\n";
        $waMessage .= "• Status Saat Ini: *{$statusName}*\n\n";
        
        $waMessage .= "Terima kasih telah berpartisipasi dalam proses rekrutmen kami. Silakan pantau terus informasi selanjutnya melalui website kami.\n\n";
        $waMessage .= "Salam,\n";
        $waMessage .= "*Tim HR Ciptakarir*";

        return [
            'to' => $this->formatPhoneNumber($phoneNumber),
            'message' => $waMessage,
        ];
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