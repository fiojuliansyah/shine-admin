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
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
    
        $link = 'https://google.com/'; 
    
        $qrCodeSvg = QrCode::format('svg')->size(300)->generate($link);
    
        $user->update([
            'profile_qr' => $qrCodeSvg,
        ]);
    
        Auth::login($user);
    
        return redirect()->route('web.applicants.dashboard')->with('success', 'Pendaftaran berhasil! QR Code Anda telah dibuat.');
    }
    

    public function storeLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $request->login, 'password' => $request->password]
            : ['phone' => $request->login, 'password' => $request->password];

        if (Auth::attempt($credentials, $request->remember)) {
            return redirect()->route('web.applicants.dashboard')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'login' => 'Email/No HP atau Password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant-login')->with('success', 'Berhasil keluar.');
    }


}
