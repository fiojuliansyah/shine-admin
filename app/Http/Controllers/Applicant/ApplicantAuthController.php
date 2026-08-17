<?php

namespace App\Http\Controllers\Applicant;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ApplicantAuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showLoginForm()
    {
        return view('website.auth.login');
    }

    public function showRegisterForm()
    {
        return view('website.auth.register');
    }
    

    public function storeRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar gunakan email lain.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $link = route('applicants.resume', ['id' => $user->id]); 
        
        $qrCodeSvg = QrCode::format('svg')->size(300)->generate($link);

        $user->update([
            'profile_qr' => $qrCodeSvg,
        ]);

        Auth::login($user);

        return redirect()->route('web.applicants.dashboard')->with('success', 'Pendaftaran berhasil!');
    }
    

    public function storeLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau Nomor WhatsApp wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $request->login, 'password' => $request->password]
            : ['phone' => $request->login, 'password' => $request->password];

        if (Auth::attempt($credentials, $request->remember)) {
            return redirect()->route('web.applicants.dashboard')->with('success', 'Login berhasil!');
        }

        return back()->with('error', 'Email/No HP atau Password yang Anda masukkan salah.')->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant-login')->with('success', 'Berhasil keluar.');
    }

    public function showForgotForm()
    {
        return view('website.auth.forgot');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $phone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $request->phone)->orWhere('phone', $phone)->first();

        if (!$user) {
            return back()->with('error', 'Nomor WhatsApp tidak terdaftar.')->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $request->session()->put('reset_otp', [
            'user_id' => $user->id,
            'code' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ]);

        $message = "🔐 *RESET PASSWORD* 🔐\n\nKode OTP Anda: *{$otp}*\n\nKode berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.\n\n*Tim HR Ciptakarir*";

        if (!$this->sendWhatsapp($phone, $message)) {
            return back()->with('error', 'Gagal mengirim OTP. Coba lagi nanti.')->withInput();
        }

        return redirect()->route('applicant-otp')->with('success', 'Kode OTP telah dikirim ke WhatsApp Anda.');
    }

    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('reset_otp')) {
            return redirect()->route('applicant-forgot');
        }
        return view('website.auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string'], ['otp.required' => 'Kode OTP wajib diisi.']);

        $data = $request->session()->get('reset_otp');
        if (!$data || now()->timestamp > $data['expires_at']) {
            $request->session()->forget('reset_otp');
            return redirect()->route('applicant-forgot')->with('error', 'Kode OTP kedaluwarsa. Silakan minta ulang.');
        }

        if ($data['attempts'] >= 5) {
            $request->session()->forget('reset_otp');
            return redirect()->route('applicant-forgot')->with('error', 'Terlalu banyak percobaan. Silakan minta ulang.');
        }

        if (!Hash::check($request->otp, $data['code'])) {
            $data['attempts']++;
            $request->session()->put('reset_otp', $data);
            return back()->with('error', 'Kode OTP salah.');
        }

        $request->session()->put('reset_verified', $data['user_id']);
        $request->session()->forget('reset_otp');

        return redirect()->route('applicant-reset');
    }

    public function showResetForm(Request $request)
    {
        if (!$request->session()->has('reset_verified')) {
            return redirect()->route('applicant-forgot');
        }
        return view('website.auth.reset');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $userId = $request->session()->get('reset_verified');
        if (!$userId) {
            return redirect()->route('applicant-forgot');
        }

        User::whereKey($userId)->update(['password' => Hash::make($request->password)]);
        $request->session()->forget('reset_verified');

        return redirect()->route('applicant-login')->with('success', 'Password berhasil diperbarui. Silakan masuk.');
    }

    private function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return str_starts_with($phone, '0') ? '62' . substr($phone, 1) : $phone;
    }

    private function sendWhatsapp($phone, $message)
    {
        $token = env('FONNTE_TOKEN');
        if (!$token) {
            Log::error('Fonnte TOKEN tidak terkonfigurasi di .env.');
            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
                'delay' => '2',
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Fonnte OTP Exception: ' . $e->getMessage());
            return false;
        }
    }
}
