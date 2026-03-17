<?php

namespace App\Broadcasting;

use Illuminate\Support\Facades\Http;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsappChannel
{
    public function send($notifiable, Notification $notification)
    {
        $messageData = $notification->toWhatsapp($notifiable);
        
        $phoneNumber = $messageData['to'];
        $message = $messageData['message'];

        $token = env('FONNTE_TOKEN');
        $apiUrl = 'https://api.fonnte.com/send';

        if (!$token) {
            Log::error('Fonnte TOKEN tidak terkonfigurasi di .env.');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->asForm()->post($apiUrl, [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62',
                'delay' => '2',
            ]);

            if ($response->successful()) {
                Log::info('Fonnte message sent successfully.', [
                    'response' => $response->json(), 
                    'to' => $phoneNumber
                ]);
            } else {
                Log::error('Fonnte failed to send message.', [
                    'status' => $response->status(),
                    'response' => $response->body(), 
                    'to' => $phoneNumber
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Fonnte API Exception: ' . $e->getMessage());
        }
    }
}