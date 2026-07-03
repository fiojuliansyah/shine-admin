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

        $esign = null;

        // Skenario 1: Jika user Upload File
        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');

            // Ubah gambar apapun menjadi PNG base64 agar konsisten.
            $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));

            if ($image === false) {
                return back()->with('error', 'File tanda tangan tidak valid.');
            }

            imagesavealpha($image, true);

            ob_start();
            imagepng($image);
            $pngData = ob_get_clean();
            imagedestroy($image);

            $esign = 'data:image/png;base64,' . base64_encode($pngData);
        }
        // Skenario 2: Jika user Menggambar di Canvas (PNG base64 dari signature_pad)
        elseif ($request->filled('signature')) {
            $signatureData = $request->input('signature');

            if (strpos($signatureData, 'data:image/png;base64,') !== false) {
                $esign = $signatureData;
            }
        }

        if (!$esign) {
            return back()->with('error', 'Mohon buat tanda tangan atau upload file.');
        }

        // Simpan ke database
        $profile = Auth::user()->profile ?? new Profile(['user_id' => Auth::id()]);
        $profile->esign = $esign;
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

