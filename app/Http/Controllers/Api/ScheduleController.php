<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TaskPlanner;
use App\Models\TaskProgress;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $tasksToday = TaskPlanner::where('site_id', $user->site_id)
            ->whereDate('date', $today)
            ->latest()
            ->get();

        $progressToday = TaskProgress::with('taskPlanner')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->get()
            ->groupBy('status');

        $inProgressIds = isset($progressToday['in_progress']) ? $progressToday['in_progress']->pluck('task_planner_id')->toArray() : [];
        $completedIds = isset($progressToday['completed']) ? $progressToday['completed']->pluck('task_planner_id')->toArray() : [];

        $tasksPending = $tasksToday->filter(function ($task) use ($inProgressIds, $completedIds) {
            return !in_array($task->id, $inProgressIds) && !in_array($task->id, $completedIds);
        });

        // telat mengerjakan tugas
        $now = Carbon::now()->toTimeString();
        foreach ($tasksPending as $task) {
            if ($now >= $task->start_time) {
                $report = TaskProgress::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'site_id' => $user->site_id,
                        'task_planner_id' => $task->id,
                    ],
                    [
                        'progress_description' => 'Hari ini telat mengerjakan tugas',
                        'is_worked' => 'not_worked',
                        'date' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            // ambil field not worked dari relasi
            $tasksPending = $tasksPending->load('taskProgress:id,task_planner_id,is_worked');
        }


        $tasksTomorrow = TaskPlanner::where('site_id', $user->site_id)
            ->whereDate('date', $tomorrow)
            ->get();

        return response()->json([
            'tasks_pending'      => $tasksPending->values(),
            'tasks_in_progress'  => $progressToday['in_progress'] ?? [],
            'tasks_completed'    => $progressToday['completed'] ?? [],
            'tasks_tomorrow'     => $tasksTomorrow
        ]);
    }

    public function show($id)
    {
        $userId = Auth::id();

        $task = TaskPlanner::with(['taskProgress' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }, 'floor'])->find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        return response()->json([
            'task'     => $task,
            'progress' => $task->taskProgress
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'user_id' => 'required|exists:users,id',
            'date'    => 'required|date',
            'type'    => 'nullable|string',
        ]);

        $task = TaskPlanner::create($request->all());

        return response()->json([
            'message' => 'Task berhasil dibuat',
            'data'    => $task
        ], 201);
    }

    public function progressStart(Request $request)
    {
        $request->validate([
            'task_planner_id' => 'required|exists:task_planners,id',
            'image'           => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();
        $now = Carbon::now();

        $imgUrl = null;
        $imgPublicId = null;

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'schedule',
                    'transformation' => [
                        'width' => 300,
                        'height' => 200,
                        'crop' => 'fill'
                    ]
                ]);
                $imgUrl = $upload->getSecurePath();
                $imgPublicId = $upload->getPublicId();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        $progress = TaskProgress::create([
            'task_planner_id'        => $request->task_planner_id,
            'user_id'                => $user->id,
            'site_id'                => $user->site->id,
            'status'                 => 'in_progress',
            'is_worked'                 => 'worked',
            'date'                   => $now->toDateString(),
            'start_time'             => $now->toTimeString(),
            'image_before_url'       => $imgUrl,
            'image_before_public_id' => $imgPublicId,
        ]);

        return response()->json([
            'message' => 'Task started',
            'data'    => $progress
        ], 200);
    }

    public function progressEnd(Request $request)
    {
        $request->validate([
            'task_planner_id'      => 'required|exists:task_planners,id',
            'progress_description' => 'nullable|string',
            'image'                => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();
        $now = Carbon::now();

        $progress = TaskProgress::where('task_planner_id', $request->task_planner_id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$progress) {
            return response()->json(['message' => 'No in-progress task found.'], 404);
        }

        $imgUrl = null;
        $imgPublicId = null;

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'schedule',
                    'transformation' => [
                        'width' => 300,
                        'height' => 200,
                        'crop' => 'fill'
                    ]
                ]);
                $imgUrl = $upload->getSecurePath();
                $imgPublicId = $upload->getPublicId();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        $progress->update([
            'status'                 => 'completed',
            'end_time'               => $now->toTimeString(),
            'image_after_url'        => $imgUrl,
            'image_after_public_id'  => $imgPublicId,
            'progress_description'   => $request->progress_description
        ]);

        return response()->json([
            'message' => 'Task completed',
            'data'    => $progress
        ]);
    }
}
