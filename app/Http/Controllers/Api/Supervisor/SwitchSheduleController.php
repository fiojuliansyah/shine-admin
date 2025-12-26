<?php

namespace App\Http\Controllers\Api\Supervisor;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SwitchSheduleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userSites = $user->sites_leader;

        return response()->json([
            'sites' => $userSites->pluck('name', 'id'),
        ]);
    }

    public function switchSchedule($siteId)
    {
        $user = Auth::user();

        $userSites = $user->sites_leader()
            ->where('sites.id', $siteId)
            ->first();

        if (!$userSites) {
            return response()->json(['message' => 'site tidak ditemukan'], 404);
        }

        return response()->json([
            'users' => $userSites->users->pluck('name', 'id')
        ]);
    }

    public function userSchedule($userId)
    {
        $authUser = Auth::user();
        $user = User::with([
            'schedules' => function ($q) {
                $q->where('date', '>=', Carbon::today()->toDateString())
                    ->orderBy('date', 'asc')
                    ->with('shift:id,name'); // supaya bisa akses shift name di response
            }
        ])->find($userId);

        if (!$user) {
            return response()->json(['message' => 'schedules user tidak ditemukan']);
        }

        return response()->json([
            'schedules' => $user->schedules,
        ]);
    }

    public function swapSchedules(Request $request)
    {
        $request->validate([
            'user_a'     => 'required',
            'schedule_a' => 'required',
            'user_b'     => 'required',
            'schedule_b' => 'required',
            'note'       => 'nullable|string',
        ]);

        $scheduleA = Schedule::find($request->schedule_a);
        $scheduleB = Schedule::find($request->schedule_b);

        // pastikan schedule A memang milik user_a
        if (!$scheduleA) {
            return response()->json(['message' => 'Schedule A tidak ditemukan'], 404);
        }

        // pastikan schedule B memang milik user_b
        if (!$scheduleB) {
            return response()->json(['message' => 'Schedule B tidak ditemukan'], 404);
        }

        // swap user_id
        $tempUser = $scheduleA->user_id;
        $scheduleA->user_id = $scheduleB->user_id;
        $scheduleB->user_id = $tempUser;

        $scheduleA->save();
        $scheduleB->save();

        // opsional: simpan ke tabel log tukar jadwal
        // SwitchSchedule::create([
        //     'user_a' => $request->user_a,
        //     'schedule_a' => $request->schedule_a,
        //     'user_b' => $request->user_b,
        //     'schedule_b' => $request->schedule_b,
        //     'note' => $request->note,
        //     'supervisor_id' => Auth::id(),
        // ]);

        return response()->json([
            'message' => 'Jadwal berhasil ditukar',
            'schedule_a' => $scheduleA,
            'schedule_b' => $scheduleB,
        ]);
    }
}
