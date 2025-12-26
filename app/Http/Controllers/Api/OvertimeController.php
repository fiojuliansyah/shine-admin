<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use DateTime;
use App\Models\User;
use App\Models\Overtime;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $lastAttendance = Attendance::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        $siteId = Auth::user()->site_id;
        $teams = User::where('site_id', $siteId)
            ->where('id', '!=', $userId)
            ->get();

        $latestOvertime = null;
        $clockInStatus = false;
        $clockOutStatus = false;
        $logs = [];

        if ($lastAttendance) {
            $latestOvertime = Overtime::where('attendance_id', $lastAttendance->id)
                ->latest()
                ->first();

            $clockInStatus = Overtime::where('attendance_id', $lastAttendance->id)
                ->whereNotNull('clock_in')
                ->exists();

            $clockOutStatus = Overtime::where('attendance_id', $lastAttendance->id)
                ->whereNotNull('clock_out')
                ->exists();

            $logs = Overtime::with('attendance')
                ->where('attendance_id', $lastAttendance->id)
                ->get();

            foreach ($logs as $log) {
                $clockIn = new DateTime($log->clock_in);
                $clockOut = new DateTime($log->clock_out);

                $diff = $clockIn->diff($clockOut);
                $log->duration = $diff->format('%H:%I:%S');
            }
        }

        return response()->json([
            'clockInStatus' => $clockInStatus,
            'clockOutStatus' => $clockOutStatus,
            'logs' => $logs,
            'latestOvertime' => $latestOvertime,
            'teams' => $teams,
        ], 200);
    }

    public function storeClockIn(Request $request)
    {
        $request->validate([
            'reason'     => 'required|string',
            'demand'     => 'nullable|string',
            'backup_id'  => 'nullable|exists:users,id'
        ]);

        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $timeNow = Carbon::now()->toTimeString();

        $attendance = Attendance::firstOrCreate(
            ['date' => $today, 'user_id' => $user->id],
            ['site_id' => $user->site_id, 'clock_in' => $timeNow]
        );

        $overtime = Overtime::updateOrCreate(
            ['attendance_id' => $attendance->id],
            [
                'site_id' => $user->site_id,
                'date' => Carbon::today()->toDateString(),
                'clock_in' => $timeNow,
                'reason'   => $request->reason,
                'demand'   => $request->demand,
                'backup_id' => $request->backup_id
            ]
        );

        return response()->json([
            'message' => 'Overtime clock-in berhasil.',
            'data' => $overtime
        ]);
    }

    public function storeClockOut()
    {
        $user = Auth::user();
        $timeNow = Carbon::now()->toTimeString();

        $lastAttendance = Attendance::where('user_id', $user->id)
            ->latest()
            ->first();

        $lastOvertime = Overtime::where('attendance_id', $lastAttendance->id ?? null)
            ->latest()
            ->first();

        if (!$lastOvertime || !$lastOvertime->clock_in) {
            return response()->json([
                'message' => 'Belum melakukan clock-in overtime.',
            ], 400);
        }

        $lastOvertime->clock_out = $timeNow;
        $lastOvertime->save();

        $lastAttendance->clock_out = $timeNow;
        $lastAttendance->save();

        return response()->json([
            'message' => 'Overtime clock-out berhasil.',
            'data' => $lastOvertime
        ]);
    }
}