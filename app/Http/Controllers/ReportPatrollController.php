<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Floor;
use App\Models\TaskPlanner;
use Illuminate\Http\Request;
use App\Models\PatrollSession;
use App\Models\SecurityPatroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\DataTables\PatrollReportDataTable;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ReportPatrollController extends Controller
{
    public function index(Request $request, PatrollReportDataTable $dataTable)
    {
        $users = User::all();
        $sites = Site::all();
        $floors = Floor::all();

        $filters = [
            'date' => $request->date,
            'user_id' => $request->user_id,
            'site_id' => $request->site_id,
            'floor_id' => $request->floor_id,
        ];

        $today = Carbon::today()->toDateString();
        $totalCount = SecurityPatroll::count();
        $todayCount = SecurityPatroll::whereDate('created_at', $today)->count();

        $today = Carbon::today()->toDateString();

        $totalTasks = TaskPlanner::whereDate('date', $today)->count();

        $doneTasks = TaskPlanner::whereDate('date', $today)
            ->whereHas('security', function ($q) {
                $q->where('status', 'reported');
            })
            ->count();

        $pendingTasks = $totalTasks - $doneTasks;

        $donePercent = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100, 2) : 0;
        $pendingPercent = $totalTasks > 0 ? round(($pendingTasks / $totalTasks) * 100, 2) : 0;

        $patrollSessions = PatrollSession::select(
            'turn',
            DB::raw('MAX(id) as id'),
            DB::raw('MAX(patroll_code) as patroll_code'),
            DB::raw('MAX(user_id) as user_id')
        )
            ->where('date', Carbon::today()->toDateString())
            ->groupBy('turn')
            ->paginate(10);

        return $dataTable->render('patroll_report.index', compact(
            'dataTable',
            'users',
            'sites',
            'filters',
            'totalCount',
            'todayCount',
            'floors',
            'doneTasks',
            'pendingTasks',
            'donePercent',
            'pendingPercent',
            'patrollSessions'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
        ]);

        $user = Auth::user();
        $task = TaskPlanner::where('id', $id)->first();
        $security_patroll = SecurityPatroll::where('task_planner_id', $id)->first();

        if ($request->has('image')) {
            try {
                if (!empty($security_patroll->image_public_id)) {
                    Cloudinary::destroy($security_patroll->image_public_id);
                }

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
        $security_patroll = SecurityPatroll::findOrFail($id);

        $security_patroll->update([
            'user_id' => $request->user_id,
            'site_id' => $request->site_id,
            'floor_id' => $request->floor_id,
            'name'            => $request->name,
            'description'     => $request->description,
            'image_url'       => $imageUrl ?? $security_patroll->image_url,
            'image_public_id' => $imagePublicId ?? $security_patroll->image_public_id,
        ]);

        return to_route('patrollReport.index')
            ->with('success', 'Report successfully updated.');
    }

    public function destroy($id)
    {
        $report = SecurityPatroll::findOrFail($id);
        $report->delete();

        return redirect()->route('patrollReport.index')->with('success', 'Report deleted successfully.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['date', 'user_id', 'site_id', 'floor_id']);

        $query = SecurityPatroll::with(['user', 'site', 'floor']);

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }
        if (!empty($filters['floor_id'])) {
            $query->where('floor_id', $filters['floor_id']);
        }

        $reports = $query->get();

        $data = [
            'title' => "Laporan Patroli Keamanan",
            'reports' => $reports
        ];

        $pdf = Pdf::loadView('pdf.patrollReport', $data);

        return $pdf->stream('patroll-report.pdf');
    }

    public function printTodayReport($id_site)
    {
        $site = Site::findOrFail($id_site);
        $floors = Floor::where('site_id', $id_site)->get();

        $reports = SecurityPatroll::with(['user', 'floor'])
            ->whereDate('created_at', today())
            ->get();

        $pdf = Pdf::loadView('pdf.printPatrollToday', [
            'title' => 'Laporan Patroli Hari Ini',
            'site'  => $site,
            'floors' => $floors,
            'reports' => $reports
        ]);

        return $pdf->stream('patroll-report-print-today.pdf');
    }

}
