<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class MobileLoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = 'mobile/home'; 

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('mobile.home');
        }

        return view('mobiles.auth.login');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ], [
            'required' => 'Kolom :attribute wajib diisi.',
            'string' => 'Kolom :attribute harus berupa teks.',
        ]);
    }

    public function username()
    {
        $login = request()->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_nik';
        request()->merge([$field => $login]);
        return $field;
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->can('view-mobile')) {
            return redirect('/');
        }
    }

    public function logout(Request $request)
    {
        $guard = Auth::guard(); // Pastikan menggunakan guard mobile jika ada
        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.login'); // Mengarahkan ke halaman login mobile
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [
            $this->username() => ['Email atau NIK dan password yang Anda masukkan tidak cocok.'],
        ];
        throw ValidationException::withMessages($errors);
    }
}
