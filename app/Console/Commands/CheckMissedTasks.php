<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\TaskPlanner;
use App\Models\TaskProgress;
use Illuminate\Console\Command;
use App\Mail\MissedTaskReportMail;
use Illuminate\Support\Facades\Mail;

class CheckMissedTasks extends Command
{
    protected $signature = 'tasks:check-progress';
    protected $description = 'Cek apakah user sudah mengerjakan task hari ini, jika tidak masuk ke report temuan';

    public function handle()
    {
        $today = Carbon::now()->toDateString();
        // Ambil semua user sekaligus dengan relasi site (supaya tidak N+1 query)
        $users = User::with('site')->get();

        // Ambil semua site_id dari user
        $siteIds = $users->pluck('site_id')->unique();

        // Ambil semua task hari ini untuk semua site terkait
        $tasksBySite = TaskPlanner::whereIn('site_id', $siteIds)
            ->whereDate('date', $today)
            ->get()
            ->groupBy('site_id');

        // Ambil semua progress untuk semua user hari ini
        $progressToday = TaskProgress::whereIn('user_id', $users->pluck('id'))
            ->whereIn('task_planner_id', $tasksBySite->flatten()->pluck('id'))
            ->where('status', 'completed')
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '-' . $item->task_planner_id;
            });

        // Loop per user
        foreach ($users as $user) {
            // Ambil task sesuai site user
            $tasks = $tasksBySite[$user->site_id] ?? collect();

            foreach ($tasks as $task) {
                $key = $user->id . '-' . $task->id;

                // Cek apakah progress sudah ada
                if (!isset($progressToday[$key])) {
                    // Buat atau update report
                    $report = TaskProgress::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'site_id' => $user->site_id,
                            'task_planner_id' => $task->id,
                        ],
                        [
                            'progress_description' => 'Hari ini tidak mengerjakan tugas',
                            'is_worked' => 'not_worked',
                            'date' => now(),
                            'created_at' => now(),
                        ]
                    );

                    // Kirim email jika ada alamat email client site
                    if (!empty($user->site->client_email)) {
                        Mail::to($user->site->client_email)->send(new MissedTaskReportMail($report));
                    }
                }
            }
        }

        $this->info("Pengecekan selesai.");
    }
}
