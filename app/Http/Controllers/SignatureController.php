<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;

class SignatureController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $signatureData = $request->input('signature');

        if (!$signatureData || strpos($signatureData, 'data:image/svg+xml;base64,') === false) {
            return back()->with('error', 'Data tanda tangan tidak valid.');
        }

        $base64Data = substr($signatureData, strlen('data:image/svg+xml;base64,'));

        $svgData = base64_decode($base64Data);

        if ($svgData === false) {
            return back()->with('error', 'Gagal mengonversi data Base64 ke SVG.');
        }

        $profile = Auth::user()->profile;

        if (!$profile) {
            $profile = new Profile();
            $profile->user_id = Auth::id();
            $profile->esign = $svgData;
            $profile->save();
        } else {
            $profile->esign = $svgData;
            $profile->save();
        }

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan!');
    }

    public function delete(Request $request)
    {
        $profile = Auth::user()->profile;

        if ($profile && $profile->esign) {
            $profile->esign = null;
            $profile->save();
            
            return redirect()->back()->with('success', 'Tanda tangan berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Tanda tangan tidak ditemukan.');
    }
}

