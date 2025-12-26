<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\Floor;
use App\Models\Jobdesk;
use App\Models\TaskPlanner;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
use App\Imports\TaskPlannerImport;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\TaskPlannerDataTable;

class TaskPlannerController extends Controller
{
    public function index(TaskPlannerDataTable $dataTable)
    {
        return $dataTable->render('tasks.index');
    }

    public function show($siteId)
    {
        $today = Carbon::today()->toDateString();
        $tasksTodays = TaskPlanner::where('site_id', $siteId)
            ->whereDate('date', $today)->get();

        // hari berikutnya
        $site = Site::findOrFail($siteId);
        $jobdesks = Jobdesk::where('site_id', $siteId)
        ->whereNot('service_type', 'patroll')->get();
        $floors = Floor::where('site_id', $siteId)->get();
        $tasksNextDays = TaskPlanner::with('floor')
        ->where('site_id', $siteId)
            ->whereDate('date', '>', $today)
            ->orderBy('date', 'asc')
            ->paginate(7);

        return view('tasks.show', compact('site', 'jobdesks', 'tasksTodays', 'tasksNextDays', 'floors'));
    }

    public function store(Request $request)
    {
        $task = new TaskPlanner();
        $task->site_id = $request->site_id;
        $task->date = $request->date;
        $task->name = $request->name;
        $task->service_type = $request->service_type;
        $task->work_type = $request->work_type;
        $task->floorid = $request->floor_id;
        $task->start_time = $request->start_time ?? null; // Biarkan kosong jika tidak diatur
        $task->save();

        return response()->json(['success' => true]);
    }

    public function jobToTaskPlan(Request $request)
    {

        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'jobdesk' => 'required|exists:jobdesks,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'time' => 'required',
        ]);

        $jobdesk = Jobdesk::findOrFail($request->jobdesk);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        while ($startDate <= $endDate) {
            TaskPlanner::create([
                'site_id' => $request->site_id,
                'date' => $startDate->format('Y-m-d'),
                'name' => $jobdesk->name,
                'service_type' => $jobdesk->service_type,
                'work_type' => $jobdesk->work_type,
                'floor_id' => $jobdesk->floor_id,
                'start_time' => $request->time,
            ]);
            $startDate->addDay();
        }

        return redirect()->back()->with('success', 'Jobdesk added to task planner successfully.');
    }

    public function getEvents($site_id, Request $request)
    {
        $events = TaskPlanner::where('site_id', $site_id)
            ->whereBetween('date', [$request->start, $request->end])
            ->get();

        return response()->json($events->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->name,
                'start' => Carbon::parse($task->date . ' ' . ($task->start_time ?? '00:00:00'))->toIso8601String(),
                'classNames' => ['badge', 'badge-primary'],
                'extendedProps' => [
                    'service_type' => $task->service_type,
                    'work_type' => $task->work_type,
                    'floor' => $task->floor
                ]
            ];
        }));
    }

    public function edit($id)
    {
        $taskPlanner = TaskPlanner::findOrFail($id);
        $jobdesk = Jobdesk::where('service_type', $taskPlanner->service_type)->first();
        return response()->json([
            "id_planner" => $taskPlanner->id,
            "jobId" => $jobdesk->id,
            "start_date" => Carbon::parse($taskPlanner->date)->format('Y-m-d'),
            "time" => $taskPlanner->start_time,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'jobdesk' => 'required',
            'start_date' => 'required|date',
            'time' => 'required',
            'site_id' => 'required|exists:sites,id',
        ]);
        // Kalau lolos validasi, lanjut update data
        // Misalnya:
        $task = TaskPlanner::findOrFail($request->id_planner);

        $jobdesk = Jobdesk::findOrFail($request->jobdesk);
        $startDate = Carbon::parse($request->start_date);


        $task->update([
            'site_id' => $request->site_id,
            'date' => $startDate->format('Y-m-d'),
            'name' => $jobdesk->name,
            'service_type' => $jobdesk->service_type,
            'work_type' => $jobdesk->work_type,
            'start_time' => $request->time,
        ]);

        return redirect()->back()->with('success', 'Planner updated successfully!');
    }

    public function destroy($id)
    {
        $taskPlanner = TaskPlanner::findOrFail($id);
        $taskPlanner->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    // import
    public function import(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        $file = $request->file('file');
        $month = $request->month;
        $site_id = $request->site_id;

        $selectedMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $currentMonth  = Carbon::now()->startOfMonth();

        if ($selectedMonth->lt($currentMonth)) {
            return back()->with('failed', "Bulan yang anda masukan sudah lampau");
        }

        Excel::import(new TaskPlannerImport($site_id, $month), $file);
        return back()->with('success', 'Data Task Planner berhasil diimport!');
    }
}
