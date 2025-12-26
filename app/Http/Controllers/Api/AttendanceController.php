<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

use function PHPSTORM_META\type;

class AttendanceController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $schedule = Schedule::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        $latestAttendance = Attendance::where('user_id', $userId)
            ->where(function ($query) use ($today) {
                $query->whereDate('date', $today)->orWhereNull('clock_out');
            })
            ->latest()
            ->first();

        $user = Auth::user()->load('site'); // pastikan User ada relasi ke Site

        return response()->json([
            'date' => $today,
            'schedule' => $schedule,
            'latest_attendance' => $latestAttendance,
            'is_clocked_in' => !!optional($latestAttendance)->clock_in,
            'is_clocked_out' => !!optional($latestAttendance)->clock_out,
            'latest_clock_in' => optional($latestAttendance)->clock_in,
            'site' => $user->site,
            'department_id' => $user->department_id, // ⬅️ tambahin
        ]);
    }


    public function clockInPage()
    {
        $user = Auth::user();
        $today = Carbon::now()->toDateString();

        $existingAttendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        if ($existingAttendance && $existingAttendance->clock_in) {
            return response()->json([
                'message' => 'Anda sudah melakukan clock in hari ini.',
            ], 400);
        }

        return response()->json([
            'message' => 'Anda berhasil clock in',
        ], 200);
    }
    public function clockOutPage()
    {
        $user = Auth::user();
        $today = Carbon::now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->whereDate('clock_in', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum melakukan clock in atau sudah melakukan clock out hari ini.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Silakan lanjutkan proses clock out.',
            'data' => [
                'clock_in_time' => $attendance->clock_in,
            ]
        ]);
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'latlong' => 'required',
            'image' => 'nullable|string'
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $timeNow = Carbon::now()->toTimeString();

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'type' => 'regular',
            'date' => $today
        ]);

        if ($attendance->clock_in) {
            return response()->json(['message' => 'Sudah clock in hari ini.'], 409);
        }

        $attendance->latlong = $request->latlong;
        $attendance->site_id = $user->site_id;
        $attendance->clock_in = $timeNow;

        $schedule = Schedule::where('user_id', $user->id)->where('date', $today)->first();
        if ($schedule && $schedule->clock_in) {
            $actual = Carbon::parse($timeNow);
            $expected = Carbon::parse($schedule->clock_in);
            if ($actual->gt($expected)) {
                $attendance->type = 'late';
                $attendance->late_duration = $actual->diffInMinutes($expected);
            }
        }

        if ($request->filled('image')) {
            try {
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
                $imageData = base64_decode($image);
                $tmp = tempnam(sys_get_temp_dir(), 'clockin_');
                file_put_contents($tmp, $imageData);
                $upload = Cloudinary::upload($tmp, ['folder' => 'attendances_images']);
                unlink($tmp);
                $attendance->face_image_url_clockin = $upload->getSecurePath();
                $attendance->face_image_public_id_clockin = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Clock-in image upload failed: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        $attendance->save();

        return response()->json(['message' => 'Clock in berhasil', 'data' => $attendance]);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $timeNow = Carbon::now()->toTimeString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Belum clock in'], 409);
        }

        $attendance->clock_out = $timeNow;

        if ($request->filled('image')) {
            try {
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
                $imageData = base64_decode($image);
                $tmp = tempnam(sys_get_temp_dir(), 'clockout_');
                file_put_contents($tmp, $imageData);
                $upload = Cloudinary::upload($tmp, ['folder' => 'attendances_images']);
                unlink($tmp);
                $attendance->face_image_url_clockout = $upload->getSecurePath();
                $attendance->face_image_public_id_clockout = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Clock-out image upload failed: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        $attendance->save();

        return response()->json(['message' => 'Clock out berhasil', 'data' => $attendance]);
    }

    public function status()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['status' => 'no_attendance', 'message' => 'Belum melakukan absensi']);
        }

        if ($attendance->clock_in && !$attendance->clock_out) {
            return response()->json([
                'status' => 'clocked_in',
                'message' => 'Sudah clock in',
                'clock_in' => $attendance->clock_in
            ]);
        }

        if ($attendance->clock_in && $attendance->clock_out) {
            return response()->json([
                'status' => 'completed',
                'message' => 'Absensi selesai',
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out
            ]);
        }

        return response()->json(['status' => 'unknown', 'message' => 'Status tidak diketahui']);
    }

    public function timeOff(Request $request)
    {
        $request->validate([
            'type' => 'required|in:off,leave,permit,alpha,regular,late',
            'remark' => 'nullable|string',
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $timeNow = Carbon::now()->toTimeString();

        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->site_id = $user->site_id;
        $attendance->date = $today;
        $attendance->clock_in = $timeNow;
        $attendance->clock_out = $timeNow;
        $attendance->type = $request->type;
        $attendance->remark = $request->remark;
        $attendance->save();

        return response()->json(['message' => 'Time off berhasil', 'data' => $attendance]);
    }
}
