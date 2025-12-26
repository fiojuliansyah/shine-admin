<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Floor;
use App\Models\TaskPlanner;
use Illuminate\Http\Request;
use App\Models\PatrollSession;
use App\Models\SecurityPatroll;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SecurityPatrollController extends Controller
{
    public function index()
    {
        $site_id = Auth::user()->site_id; //user site id

        $floor = Floor::with(['site:id,name', 'tasks' => function ($q) {
            $q->where('date', now()->toDateString());
        }])
            ->where('site_id', $site_id)
            ->get();

        return response()->json([
            'message' => 'List of Floors',
            'data' => $floor
        ]);
    }

    public function scan_floor(Request $request)
    {
        // misalnya QR berisi URL: /mobile/security-patrol/{id}
        // kita ambil id-nya saja:
        $user = Auth::user();
        $qrData = $request->input('result');
        $floorId = last(explode('/', $qrData)); // ambil id terakhir
        $user_site = $user->site_id;
        // simpan ke DB atau lakukan aksi lain
        $floor = Floor::where('id', $floorId)->where(
            'site_id',
            $user_site
        )->first();


        return response()->json([
            'status' => true,
            'message' => 'Scan saved successfully',
            'data' => $floor
        ]);
    }

    public function startPatroll(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Hitung turn hari ini untuk user ini
        $turnToday = PatrollSession::where('user_id', $userId)
            ->whereDate('created_at', $today) // gunakan created_at untuk filter tanggal
            ->count() + 1;

        // Buat session baru
        $session = PatrollSession::create([
            'user_id'      => $userId,
            'site_id'      => Auth::user()->site_id,
            'patroll_code' => strtoupper(uniqid('PATROL-')),
            'start_time'   => $request->start_time,
            'turn'         => $turnToday,
            'date' => $request->date
        ]);

        Cookie::queue('patroll_session_id', $session->id, 1);
        return response()->json([
            'message' => 'Patrol started',
            'data'    => $session
        ]);
    }

    public function endPatroll(Request $request)
    {
        $patroll_session_id = $request->patroll_session_id;
        $user = Auth::user();
        $userId = $user->id;

        $patroll_session = PatrollSession::where('user_id', $userId)
            ->where('id', $patroll_session_id)
            ->firstOrFail();

        $patroll_session->update([
            'end_time' => $request->end_time,
        ]);

        // pastikan relasi yang di-load benar
        // Ambil semua task hari ini
        $tasksToday = TaskPlanner::where('date', Carbon::today())
            ->with(['security' => function ($q) use ($userId, $patroll_session_id) {
                $q->where('user_id', $userId)
                    ->where('patroll_session_id', $patroll_session_id);
            }])
            ->where('site_id', $user->site_id)
            ->get();

        // Cek setiap task
        foreach ($tasksToday as $task) {
            // Kalau belum ada securityPatrolls untuk user+session ini
            if ($task->security->isEmpty()) {
                SecurityPatroll::updateOrCreate(
                    [
                        'task_planner_id'    => $task->id,
                        'user_id'            => $user->id,
                        'site_id'            => $user->site_id,
                        'floor_id'           => $task->floor_id,
                        'patroll_session_id' => $patroll_session_id,
                    ],
                    [
                        'task_planner_id'    => $task->id,
                        'user_id'            => $user->id,
                        'site_id'            => $user->site_id,
                        'floor_id'           => $task->floor_id,
                        'patroll_session_id' => $patroll_session_id,
                        'name'               => 'Tidak dilaporkan',
                        'description'        => "$user->name tidak melaporkan patroli",
                        'status'             => "not_reported",
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]
                );
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Patroll saved successfully',
            'data'    => $patroll_session
        ]);
    }


    public function listTask(Request $request, $id_floor)
    {
        $now = Carbon::now();
        $tasks = TaskPlanner::with([
            'security' => function ($q) use ($request) {
                // hanya load security yg patroll_session_id sesuai
                $q->where('patroll_session_id', $request->patroll_session_id);
            },
            'floor:id,name',
            'site:id,name'
        ])
            ->where('floor_id', $id_floor)
            ->where('date', $now->toDateString())
            ->get();

        // telat laporan patroll
        $user = Auth::user();
        foreach ($tasks as $task) {
            $not_reported = Carbon::parse($task->start_time)->addHour(1)->toTimeString();
            if ($now->toTimeString() >= $not_reported) {
                $record = SecurityPatroll::where([
                    'task_planner_id' => $task->id,
                    'user_id'         => $user->id,
                    'site_id'         => $user->site_id,
                    'floor_id'        => $task->floor_id,
                    'patroll_session_id' => $request->patroll_session_id
                ])->first();

                if (!$record || $record->status !== 'reported') {
                    SecurityPatroll::updateOrCreate(
                        [
                            'task_planner_id' => $task->id,
                            'user_id' => $user->id,
                            'site_id' => $user->site_id,
                            'floor_id' => $task->floor_id,
                            'patroll_session_id' => $request->patroll_session_id,
                        ],
                        [
                            'name' => 'Tidak di laporakan',
                            'description' => "$user->name tidak melaporkan patroli",
                            'status' => "not_reported",
                            'patroll_session_id' => $request->patroll_session_id,
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString()
                        ]
                    );
                }
            }
        }
        $tasks = TaskPlanner::with([
            'security' => function ($q) use ($request) {
                // hanya load security yg patroll_session_id sesuai
                $q->where('patroll_session_id', $request->patroll_session_id);
            },
            'security.patroll',
            'floor:id,name',
            'site:id,name'
        ])
            ->where('floor_id', $id_floor)
            ->where('date', $now->toDateString())
            ->get();

        return response()->json([
            'message' => 'List of tasks',
            'data' => $tasks,
        ]);
    }

    public function show(Request $request, $id_task)
    {
        $task = TaskPlanner::with('floor:id,name', 'site.company')
            ->where('id', $id_task)
            ->first();

        $security_patroll = SecurityPatroll::where('task_planner_id', $id_task)
            ->where('patroll_session_id', $request->patroll_session_id)
            ->first();

        return response()->json([
            'message' => 'Task details',
            'data' => [
                'task' => $task,
                'security_patroll' => $security_patroll
            ]
        ]);
    }

    public function edit_create(Request $request, $id_task)
    {
        $task = TaskPlanner::with('floor:id', 'site:id')
            ->where('id', $id_task)
            ->first();

        $security_patroll = SecurityPatroll::where('task_planner_id', $id_task)
            ->where('patroll_session_id', $request->patroll_session_id)
            ->first();

        return response()->json([
            'message' => 'Task details for edit/create',
            'data' => [
                'task' => $task,
                'security_patroll' => $security_patroll,
                'userId' => Auth::id()
            ]
        ]);
    }

    // membuat fungsi updated or created
    public function updateOrCreate(Request $request, $id_task)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
        ]);

        // cari data id untuk di update
        $user = Auth::user();
        $task = TaskPlanner::where('id', $id_task)->first();
        $security_patroll = SecurityPatroll::where('task_planner_id', $id_task)->first();

        // jika ada data maka update, jika tidak ada maka create atau update
        if ($request->has('image')) {
            try {
                // jika ada img di update maka hapus img lama
                if (!empty($security_patroll->image_public_id)) {
                    Cloudinary::destroy($security_patroll->image_public_id);
                }

                // upload img baru
                $file = $request->file('image');
                $upload = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'security_patroll',
                    'transformation' => [
                        'width' => 300,
                        'height' => 200,
                        'crop' => 'fill'
                    ]
                ]);
                $imageUrl = $upload->getSecurePath();
                $imagePublicId = $upload->getPublicId();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to upload image', 'error' => $e->getMessage()], 500);
            }
        }
        // kirim update or create
        $security_patroll = SecurityPatroll::updateOrCreate(
            [
                'task_planner_id' => $id_task,
                'user_id' => $user->id,
                'site_id' => $user->site_id,
                'floor_id' => $task->floor_id,
                'patroll_session_id' => $request->patroll_session_id,
            ],
            [
                'name' => $request->name,
                'description' => $request->description,
                'image_url' => $imageUrl ?? ($security_patroll->image_url ?? null),
                'status' => $request->status ?? 'reported',
                'patroll_session_id' => $request->patroll_session_id,
                'image_public_id' => $imagePublicId ?? ($security_patroll->image_public_id ?? null),
                'created_at' => Carbon::parse($request->created_at)->timezone('Asia/jakarta')->format('Y-m-d H:i:s') ?? now()->toDateTimeString(),
                'updated_at' => Carbon::parse($request->updated_at)->timezone('Asia/jakarta')->format('Y-m-d H:i:s') ?? now()->toDateTimeString()
            ]
        );

        return response()->json([
            'message' => 'Task details for edit/create',
            'data' => $security_patroll
        ]);
    }
}
