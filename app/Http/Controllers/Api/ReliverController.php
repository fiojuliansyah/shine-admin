<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Intervention\Image\Facades\Image;

class ReliverController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $logs = Attendance::where('user_id', $userId)
            ->where('is_reliver', 1)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'message' => 'Data reliver ditemukan.',
            'data' => $logs
        ]);
    }

    public function storeClockIn(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'backup_id' => 'nullable|integer',
            'remark' => 'nullable|string',
            'latlong' => 'nullable|string',
            'image' => 'nullable|string' 
        ]);

        $user = Auth::user();
        $dateNow = Carbon::now()->toDateString();
        $timeNow = Carbon::now()->toTimeString();

        $attendance = new Attendance();
        $attendance->date = $dateNow;
        $attendance->user_id = $user->id;
        $attendance->site_id = $user->site_id;
        $attendance->is_reliver = 1;
        $attendance->type = $request->type;
        $attendance->backup_id = $request->backup_id;
        $attendance->remark = $request->remark;
        $attendance->latlong = $request->latlong;
        $attendance->clock_in = $timeNow;

        if ($request->filled('image')) {
            try {
                $imageData = $request->input('image');
                $base64Image = preg_replace('/^data:image\\/[a-z]+;base64,/', '', $imageData);
                $decoded = base64_decode($base64Image);

                $image = Image::make($decoded)->encode('jpg', 75);
                $upload = Cloudinary::upload($image->__toString(), ['folder' => 'attendances_images']);

                $attendance->imagein_url = $upload->getSecurePath();
                $attendance->imagein_public_id = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Gagal upload image clock in: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload image'], 500);
            }
        }

        $attendance->save();

        return response()->json(['message' => 'Clock-in reliver berhasil disimpan']);
    }

    public function storeClockOut(Request $request)
    {
        $request->validate([
            'image' => 'nullable|string'
        ]);

        $user = Auth::user();
        $timeNow = Carbon::now()->toTimeString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('is_reliver', 1)
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Data clock-in tidak ditemukan.'], 404);
        }

        $attendance->clock_out = $timeNow;

        if ($request->filled('image')) {
            try {
                $imageData = $request->input('image');
                $base64Image = preg_replace('/^data:image\\/[a-z]+;base64,/', '', $imageData);
                $decoded = base64_decode($base64Image);

                $image = Image::make($decoded)->encode('jpg', 75);
                $upload = Cloudinary::upload($image->__toString(), ['folder' => 'attendances_images']);

                $attendance->imageout_url = $upload->getSecurePath();
                $attendance->imageout_public_id = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Gagal upload image clock out: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload image'], 500);
            }
        }

        $attendance->save();

        return response()->json(['message' => 'Clock-out reliver berhasil disimpan']);
    }
}
