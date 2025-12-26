<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\Status;
use App\Models\Document;
use App\Models\Applicant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\DataTables\StatusesDataTable;

class StatusController extends Controller
{

    public function index(StatusesDataTable $dataTable)
    {
        $title = 'Manage Statuses';
        return $dataTable->render('statuses.index', compact('title'));
    }

    public function store(Request $request)
    {
        $status = new Status;
        $status->color = $request->color;
        $status->name = $request->name;
        $status->slug = Str::slug($request->name);
        $status->is_approve = $request->is_approve;
        $status->process_to_offering = $request->process_to_offering;
        $status->save();
    
        return redirect()->route('statuses.index')
            ->with('success', 'Status ' . $status->name . ' berhasil dibuat');
    }

    public function show($slug)
    {
        $status = Status::where('slug', $slug)->first();
        $statuses = Status::all();
        $sites = Site::all();

        if (request()->ajax()) {

            $applicants = Applicant::with('user','career')
                    ->where('status_id', $status->id)
                    ->whereNull('done')
                    ->when(request('search')['value'], function ($query) {
                        $search = request('search')['value'];
        
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('career', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%");
                        });
                    });

            return DataTables::of($applicants)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="applicant-checkbox" value="' . $row->id . '">';
                })            
                ->addColumn('employee', function ($row) {
                    return $row->user->employee_nik ?? 'Belum di Update';
                })
                ->addColumn('name', function ($row) {
                    return $row->user->name ?? '';
                })
                ->addColumn('career', function ($row) {
                    return $row->career->name ?? '';
                })
                ->addColumn('role', function ($row) {
                    $roles = $row->user->getRoleNames();
                    return $roles->isNotEmpty() ? $roles->implode(', ') : 'Jabatan belum diupdate';
                })
                ->addColumn('progress', function ($row) {
                    return $row->done === 'done'
                        ? '<span class="badge bg-success">Selesai</span>'
                        : '<span class="badge bg-warning">Menunggu</span>';
                })
                ->addColumn('resume', function ($row) {
                    $statuses = Status::all();
                    $documents = Document::where('user_id', $row->user->id)->get();
                    return view('statuses.partials.resume', compact('row','statuses','documents'))->render();
                })
                ->addColumn('action', function ($row) {
                    return view('statuses.partials.show-actions', compact('row'))->render();
                })
                ->rawColumns(['action','progress','resume','checkbox','role'])
                
                ->make(true);
        }
            
        return view('statuses.show', compact('status','statuses','sites'));
    }

    public function update(Request $request, $id)
    {
        $status = Status::findOrFail($id);
        $status->color = $request->color;
        $status->name = $request->name;
        $status->slug = Str::slug($request->name);
        $status->is_approve = $request->is_approve;
        $status->process_to_offering = $request->process_to_offering;

        $status->update();

        return redirect()->route('statuses.index')
                        ->with('success', 'Status ' . $status->name . ' berhasil diperbarui');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $status_id = $request->status_id;
        $applicant_ids = $request->applicant_ids;

        if (!$status_id || empty($applicant_ids)) {
            return redirect()->back()->with('error', 'Mohon pilih kandidat yang terpilih.');
        }

        $status = Status::find($status_id);
        if (!$status) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }

        if (is_string($applicant_ids)) {
            $applicant_ids = explode(',', $applicant_ids);
        }

        foreach ($applicant_ids as $applicant_id) {
            $applicant = Applicant::find($applicant_id);

            if ($applicant) {
                Applicant::create([
                    'status_id' => $status_id,
                    'user_id' => $applicant->user_id,
                    'career_id' => $applicant->career_id,
                ]);

                $applicant->update(['done' => 'done']);
            }
        }

        // Redirect with a success message
        return redirect()->back()->with('success', 'Status kandidat berhasil diperbarui dan data baru berhasil dibuat.');
    }

    public function bulkUpdateOffering(Request $request)
    {
        $site_id = $request->site_id;
        $applicant_ids = $request->applicant_ids;
        
        if (!$site_id || empty($applicant_ids)) {
            return redirect()->back()->with('error', 'Mohon pilih kandidat yang terpilih.');
        }
        
        $site = Site::find($site_id);
        if (!$site) {
            return redirect()->back()->with('error', 'Site/Project tidak valid');
        }

        if (is_string($applicant_ids)) {
            $applicant_ids = explode(',', $applicant_ids);
        }

        Applicant::whereIn('id', $applicant_ids)->each(function ($applicant) use ($site_id) {
            $applicant->user->update([
                'site_id' => $site_id,
                'is_employee' => 1
            ]);

            $applicant->update([
                'done' => 'done'
            ]);
        });

        return redirect()->back()->with('success', 'Kandidat berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        $status->delete();
    
        return redirect()->route('statuses.index')
            ->with('success', 'Data Lokasi berhasil dihapus');
    }
}
