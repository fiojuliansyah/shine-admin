<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\FindingsReport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\DataTables\FindingsReportDataTable;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class FindingReportController extends Controller
{
    public function index(FindingsReportDataTable $dataTable, Request $request)
    {
        $users = User::all();
        $sites = Site::all();

        // Get filter values from request
        $filters = [
            'date' => $request->date,
            'type' => $request->type,
            'user_id' => $request->user_id,
            'site_id' => $request->site_id,
            'status' => $request->status,
        ];

        // Counts for dashboard cards
        $reportCount = FindingsReport::count();
        $solvedCount = FindingsReport::where('status', 'solved')->count();
        $pendingCount = FindingsReport::where('status', 'pending')->count();

        return $dataTable->render('findingsreport.index', compact(
            'users',
            'sites',
            'pendingCount',
            'reportCount',
            'solvedCount',
            'filters'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:solved,pending',
            'type' => 'required|in:low,medium,high',
            'user_id' => 'required|exists:users,id',
            'site_id' => 'required|exists:sites,id',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'direct_action' => 'nullable|string',
        ]);

        $report = FindingsReport::findOrFail($id);

        // update
        $report->update([
            'status' => $request->status,
            'type' => $request->type,
            'user_id' => $request->user_id,
            'site_id' => $request->site_id,
            'location' => $request->location,
            'description' => $request->description,
            'direct_action' => $request->direct_action,
        ]);

        return redirect()->route('findingReport.index')
            ->with('success', 'Report successfully updated.');
    }

    public function destroy($id)
    {
        $report = FindingsReport::findOrFail($id);

        if ($report->image_public_id) {
            Cloudinary::destroy($report->image_public_id);
        }

        $report->delete();

        return redirect()->route('findingReport.index')
            ->with('success', 'Report successfully deleted.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['date', 'type', 'user_id', 'site_id', 'status']);

        $query = FindingsReport::with(['user', 'site']);
        
        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
            // dd($query->get());
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $reports = $query->get();

        $data = [
            'title' => 'Laporan Temuan',
            'reports' => $reports
        ];

        $pdf = PDF::loadView('pdf.findingsReport', $data);

        return $pdf->stream('laporan-temuan.pdf');
    }
}
