{{-- Widget Cloudflare Turnstile. Tidak tampil bila TURNSTILE_SITE_KEY / TURNSTILE_SECRET_KEY kosong. --}}
@if (\App\Rules\Turnstile::enabled())
    <div class="mb-3 d-flex justify-content-center">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
            data-language="id" data-theme="light" data-size="flexible"></div>
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
