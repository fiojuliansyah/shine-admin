<?php

namespace App\Http\Controllers\Applicant;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $link = 'https://google.com/'; 
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


}
