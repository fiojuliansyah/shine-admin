<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifikasi token Cloudflare Turnstile (field "cf-turnstile-response").
 * Nonaktif otomatis bila TURNSTILE_SECRET_KEY / TURNSTILE_SITE_KEY kosong (mis. di lokal).
 */
class Turnstile implements ValidationRule
{
    public const FIELD = 'cf-turnstile-response';

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public static function enabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key'));
    }

    /** Aturan validasi siap pakai; kosong bila Turnstile nonaktif. */
    public static function rules(): array
    {
        return self::enabled() ? [self::FIELD => ['required', 'string', new self]] : [];
    }

    public static function messages(): array
    {
        return [self::FIELD . '.required' => 'Selesaikan verifikasi keamanan (captcha) terlebih dahulu.'];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::asForm()->timeout(10)->post(self::VERIFY_URL, [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Turnstile: gagal menghubungi Cloudflare', ['error' => $e->getMessage()]);
            $fail('Verifikasi keamanan tidak dapat diproses. Silakan coba lagi.');

            return;
        }

        if (! $response->json('success')) {
            Log::info('Turnstile: verifikasi ditolak', ['errors' => $response->json('error-codes')]);
            $fail('Verifikasi keamanan gagal atau kedaluwarsa. Silakan ulangi verifikasi.');
        }
    }
}
