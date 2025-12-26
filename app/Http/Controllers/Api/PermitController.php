<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Permit;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PermitController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $permits = Permit::where('user_id', $userId)->orderBy('start_date', 'desc')->get();

        return response()->json([
            'message' => 'Daftar cuti ditemukan.',
            'data' => $permits
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'required|string',
            'contact'     => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();
        $imgUrl = null;
        $imgPublicId = null;

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'permits',
                    'transformation' => [
                        'width' => 300,
                        'height' => 200,
                        'crop' => 'fill'
                    ]
                ]);
                $imgUrl = $upload->getSecurePath();
                $imgPublicId = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Upload gambar permit gagal: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'site_id' => $user->site_id,
            'date'    => $request->start_date,
            'type' => 'permit',
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
        ]);

        $permit = Permit::create([
            'user_id'         => $user->id,
            'site_id'         => $user->site_id,
            'title'           => $request->title,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'image_url'       => $imgUrl,
            'image_public_id' => $imgPublicId,
            'reason'          => $request->reason,
            'contact'         => $request->contact,
            'attendance_id'   => $attendance->id,
        ]);

        return response()->json([
            'message' => 'Permohonan cuti berhasil diajukan.',
            'data' => $permit
        ]);
    }

    public function show($id)
    {
        $permit = Permit::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        $permit->load(['user.roles', 'user.site', 'user.leader', 'user.leader.roles', 'user.leader.leader.roles', 'site.company']);

        if (!$permit) {
            return response()->json(['message' => 'Data cuti tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail cuti ditemukan.',
            'data' => $permit
        ]);
    }

    public function update(Request $request, $id)
    {
        $permit = Permit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'reason'      => 'nullable|string',
            'contact'     => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            try {
                if (!empty($permit->image_public_id)) {
                    Cloudinary::destroy($permit->image_public_id);
                }

                $image = $request->file('image');
                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'permits',
                ]);
                $permit->image_url = $upload->getSecurePath();
                $permit->image_public_id = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Gagal update gambar permit: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        $permit->update($request->only(['start_date', 'end_date', 'reason', 'contact']));

        return response()->json([
            'message' => 'Data cuti berhasil diperbarui.',
            'data' => $permit
        ]);
    }
}
