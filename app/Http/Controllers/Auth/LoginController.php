<?php

namespace App\Http\Controllers\Auth;

use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        $agent = new Agent;
        
        if ($agent->isMobile() || $agent->isTablet()) {
            return view('mobiles.auth.login');
        } else {
            if (Auth::check()) {
                return redirect()->intended($this->redirectTo);
            }
            return view('auth.login');
        }

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
        if ($user->can('view-desktop')) {
            return redirect()->route('dashboard')
                        ->with('success', 'Selamat datang!');
        }
    
        return redirect()->intended($this->redirectTo);
    }
    

    public function logout(Request $request)
    {
        $guard = Auth::guard();
        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [
            $this->username() => ['Email atau NIK dan password yang Anda masukkan tidak cocok.'],
        ];
        throw ValidationException::withMessages($errors);
    }
}
