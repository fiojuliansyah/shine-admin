<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Minute;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MinuteController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $minutes = Minute::with('attendance')
            ->whereHas('attendance', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Berita acara ditemukan.',
            'data' => $minutes
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'    => 'required|in:clockin,clockout',
            'date'    => 'required|date',
            'clock'   => 'required|date_format:H:i',
            'latlong' => 'nullable|string',
            'remark'  => 'nullable|string',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();
        $imageUrl = null;
        $imagePublicId = null;

        if ($request->hasFile('image')) {
            try {
                $upload = $request->file('image')->storeOnCloudinary('minutes');
                $imageUrl = $upload->getSecurePath();
                $imagePublicId = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Upload image gagal: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        if ($request->type === 'clockin') {
            // Buat Attendance baru
            $attendance = Attendance::create([
                'date'    => $request->date,
                'latlong' => $request->latlong,
                'user_id' => $user->id,
                'site_id' => $user->site_id,
                'clock_in' => $request->clock,
                'remark' => $request->remark,
            ]);

            // Buat Minute untuk clockin
            $minute = Minute::create([
                'attendance_id' => $attendance->id,
                'type' => 'clockin',
                'image_url' => $imageUrl,
                'image_public_id' => $imagePublicId,
                'remark' => $request->remark,
            ]);

            return response()->json([
                'message' => 'Clock in berita acara berhasil.',
                'data' => $minute
            ]);
        }

        if ($request->type === 'clockout') {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $request->date)
                ->orderByDesc('created_at')
                ->first();

            if (!$attendance) {
                return response()->json(['message' => 'Clock in belum dilakukan.'], 404);
            }

            // Update clock_out di Attendance
            $attendance->update([
                'clock_out' => $request->clock,
                'remark' => $request->remark,
            ]);

            // Buat Minute untuk clockout
            $minute = Minute::create([
                'attendance_id' => $attendance->id,
                'type' => 'clockout',
                'image_url' => $imageUrl,
                'image_public_id' => $imagePublicId,
                'remark' => $request->remark,
            ]);

            return response()->json([
                'message' => 'Clock out berita acara berhasil.',
                'data' => $minute
            ]);
        }

        return response()->json(['message' => 'Tipe tidak valid.'], 400);
    }

    public function show($id)
    {
        $minute = Minute::with([
            'attendance.user' => ['roles', 'leader.leader.roles', 'leader.roles'],
            'attendance.site' => ['company']
        ])
            ->where('id', $id)
            ->whereHas('attendance', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->first();

        if (!$minute) {
            return response()->json(['message' => 'Data cuti tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail berita acara ditemukan.',
            'data' => $minute
        ]);
    }
}
