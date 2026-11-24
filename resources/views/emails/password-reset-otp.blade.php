{{-- Email OTP reset password. Warna mengikuti dashboard (primary #3E6CFF). Inline CSS agar tampil di semua email client. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password</title>
</head>
<body style="margin:0; padding:0; background-color:#F5F6FA; font-family:Roboto, Arial, Helvetica, sans-serif; color:#202C4B;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F5F6FA; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#FFFFFF; border-radius:12px; overflow:hidden; border:1px solid #E5E7EB;">
                    {{-- Header --}}
                    <tr>
                        <td align="center" style="background-color:#3E6CFF; padding:28px 24px;">
                            <img src="{{ asset('admin/assets/img/logo-light-ciptakarir.png') }}" alt="Cipta Karir" width="140" style="display:block; border:0; max-width:140px; height:auto;">
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <h2 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#202C4B;">Reset Password</h2>
                            <p style="margin:0 0 16px; font-size:14px; line-height:22px; color:#4B5563;">
                                Halo <strong>{{ $name }}</strong>,<br>
                                Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode OTP berikut:
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 32px;">
                            <div style="display:inline-block; background-color:#EEF2FF; border:1px dashed #3E6CFF; border-radius:10px; padding:16px 28px;">
                                <span style="font-size:32px; font-weight:700; letter-spacing:10px; color:#3E6CFF; font-family:'Courier New', monospace;">{{ $otp }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 8px;">
                            <p style="margin:0 0 12px; font-size:14px; line-height:22px; color:#4B5563;">
                                Kode berlaku <strong>{{ $expiresInMinutes }} menit</strong>. Jangan bagikan kode ini kepada siapa pun, termasuk pihak yang mengaku dari Cipta Karir.
                            </p>
                            <p style="margin:0 0 24px; font-size:13px; line-height:20px; color:#6B7280;">
                                Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
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
