<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Floor;
use App\Models\Jobdesk;
use App\Models\TaskPlanner;
use Illuminate\Http\Request;
use function Termwind\render;
use Illuminate\Contracts\Queue\Job;
use App\DataTables\JobdeskPatrollsDataTable;
use App\DataTables\JobdeskPatrollsSitesDataTable;

class JobdeskPatrollsController extends Controller
{
    public function index(JobdeskPatrollsSitesDataTable $dataTable)
    {
        return $dataTable->render('jobdesk-patroll.index');
    }

    public function show(JobdeskPatrollsDataTable $dataTable, $id)
    {
        $floors = Floor::where('site_id', $id)->get();
        return $dataTable->render('jobdesk-patroll.show', compact('floors', 'id'));
    }

    public function addJob(Request $request)
    {
        $validateRequest = $request->validate([
            'site_id' => 'required',
            'name' => 'required|string',
            'floor_id' => 'required',
            'work_type' => 'required',
            'service_type' => 'required',
        ]);

        TaskPlanner::create($validateRequest);

        return redirect()->back()->with('success', 'Jobdesk created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validateRequest = $request->validate([
            'site_id' => 'required',
            'name' => 'required|string',
            'floor_id' => 'required',
            'work_type' => 'required',
            'service_type' => 'required',
        ]);

        $jobdesk = TaskPlanner::findOrFail($id);

        if ($request->job_code != $jobdesk->job_code) {
            $validateRequest['job_code'] = 'required|unique:jobdesks,job_code';
            $jobdesk->update([
                'site_id' => $request->site_id,
                'name' => $request->name,
                'floor_id' => $request->floor_id,
                'work_type' => $request->work_type,
                'service_type' => $request->service_type,
            ]);
            return redirect()->back()->with('success', 'Jobdesk berhasil diperbarui');
        }

        $jobdesk->update([
            'site_id' => $request->site_id,
            'name' => $request->name,
            'floor_id' => $request->floor_id,
            'work_type' => $request->work_type,
            'service_type' => $request->service_type,
        ]);
        return redirect()->back()->with('success', 'Jobdesk berhasil diperbarui');
    }

    public function delete($id)
    {
        $jobdesk = TaskPlanner::findOrFail($id);

        if ($jobdesk->delete()) {
            return redirect()->back()->with('success', 'Jobdesk patrol berhasil dihapus');
        }

        return redirect()->back()->with('failed', 'Gagal hapus jobdesk patrol');
    }
}
