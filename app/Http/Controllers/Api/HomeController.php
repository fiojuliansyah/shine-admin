<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        return response()->json([
            'user' => Auth::user()->load('profile', 'roles', 'site', 'sites_leader'),
        ]);
    }

    public function logs(Request $request)
    {
        return response()->json([]);
    }
}
