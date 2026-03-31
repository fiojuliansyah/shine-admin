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
            'signature' => 'nullable|string',
            'signature_file' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $svgContent = null;

        // Skenario 1: Jika user Upload File
        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $type = $file->getClientOriginalExtension();
            $data = file_get_contents($file->getRealPath());
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            // Membungkus Image ke dalam SVG agar format kolom esign tetap SVG
            $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="300" height="150 text-align="center"">
                            <image xlink:href="' . $base64 . '" height="100%" width="100%" />
                        </svg>';
        } 
        // Skenario 2: Jika user Menggambar di Canvas
        elseif ($request->filled('signature')) {
            $signatureData = $request->input('signature');
            
            if (strpos($signatureData, 'data:image/svg+xml;base64,') !== false) {
                $base64Data = substr($signatureData, strlen('data:image/svg+xml;base64,'));
                $svgContent = base64_decode($base64Data);
            }
        }

        if (!$svgContent) {
            return back()->with('error', 'Mohon buat tanda tangan atau upload file.');
        }

        // Simpan ke database
        $profile = Auth::user()->profile ?? new Profile(['user_id' => Auth::id()]);
        $profile->esign = $svgContent;
        $profile->save();

        return redirect()->back()->with('success', 'Tanda tangan berhasil diperbarui!');
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

