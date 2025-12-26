<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function loginForm()
    {
        $general = Company::first(); // ambil logo dari company pertama

        return response()->json([
            'message' => 'Login form retrieved successfully',
            'id' => $general->id,
            'general_logo' => $general->logo_url,
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();


        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email not found',
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect password',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;


        $roles = $user->roles->pluck('name');
        $profile = $user->profile;
        $site = $user->site;
        $sites_leadder = $user->sites_leader;

        $user->makeHidden(['password', 'remember_token']);

        return response()->json([
            'status' => true,
            'message' => 'User successfully logged in',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
