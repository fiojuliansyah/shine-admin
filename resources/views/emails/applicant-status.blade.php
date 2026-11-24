{{-- Email notifikasi status pelamar. Warna mengikuti dashboard (primary #3E6CFF). Inline CSS agar tampil di semua email client. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0; padding:0; background-color:#F5F6FA; font-family:Roboto, Arial, Helvetica, sans-serif; color:#202C4B;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F5F6FA; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#FFFFFF; border-radius:12px; overflow:hidden; border:1px solid #E5E7EB;">
                    <tr>
                        <td align="center" style="background-color:#3E6CFF; padding:28px 24px;">
                            <img src="{{ asset('admin/assets/img/logo-light-ciptakarir.png') }}" alt="Cipta Karir" width="140" style="display:block; border:0; max-width:140px; height:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 24px;">
                            <h2 style="margin:0 0 16px; font-size:20px; font-weight:700; color:#202C4B;">{{ $subject }}</h2>
                            <div style="font-size:14px; line-height:22px; color:#4B5563;">
                                {!! $bodyHtml !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 32px 28px;">
                            <a href="{{ route('applicant-login') }}" style="display:inline-block; background-color:#3E6CFF; color:#FFFFFF; text-decoration:none; font-size:14px; font-weight:600; padding:12px 28px; border-radius:6px;">Lihat Status Lamaran</a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background-color:#F9FAFB; border-top:1px solid #E5E7EB; padding:16px 24px;">
                            <p style="margin:0; font-size:12px; color:#9CA3AF;">Tim HR Cipta Karir &middot; &copy; {{ date('Y') }} Cipta Karir</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
