<?php

namespace App\Http\Controllers;

use App\DataTables\StatusesDataTable;
use App\Models\Applicant;
use App\Models\Company;
use App\Models\Document;
use App\Models\Generate;
use App\Models\Letter;
use App\Models\Site;
use App\Models\Status;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    public function index(StatusesDataTable $dataTable)
    {
        $title = 'Manage Statuses';
        return $dataTable->render('admin.statuses.index', compact('title'));
    }

    public function store(Request $request)
    {
        $status = new Status;
        $status->color = $request->color;
        $status->name = $request->name;
        $status->slug = Str::slug($request->name);
        $status->is_approve = $request->is_approve;
        $status->is_applicant_document = $request->is_applicant_document;
        $status->process_to_offering = $request->process_to_offering;
        $status->save();
    
        return redirect()->route('statuses.index')
            ->with('success', 'Status ' . $status->name . ' berhasil dibuat');
    }

    public function show($slug)
    {
        $status = Status::where('slug', $slug)->first();
        $statuses = Status::all();
        $companies = Company::all();
        $letters = Letter::all();
        $sites = Site::all();

        if (request()->ajax()) {
            $applicants = Applicant::with(['user', 'career'])
                ->where('status_id', $status->id)
                ->where(function ($q) {
                    $q->where('done', 'document-digital')
                        ->orWhereNull('done');
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
                    if ($row->done === 'done') {
                        return '<span class="badge bg-success">Selesai</span>';
                    }
                    if ($row->done === 'document-digital') {
                        return '<span class="badge bg-info text-white">Terlampir E-Dokumen</span>';
                    }
                    return '<span class="badge bg-warning">Menunggu</span>';
                })
                ->addColumn('resume', function ($row) {
                    return '<a href="' . route('applicants.resume', $row->id) . '" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                                <i class="ti ti-file-description me-1"></i> Lihat Resume
                            </a>';
                })
                ->addColumn('action', function ($row) {
                    return view('admin.statuses.partials.show-actions', compact('row'))->render();
                })
                ->rawColumns(['action', 'progress', 'resume', 'checkbox', 'role'])
                ->make(true);
        }

        return view('admin.statuses.show', compact('status', 'statuses', 'sites', 'letters', 'companies'));
    }

    public function getSitesByCompany($company_id)
    {
        $sites = Site::where('company_id', $company_id)->get();
        return response()->json($sites);
    }

    public function update(Request $request, $id)
    {
        $status = Status::findOrFail($id);
        $status->color = $request->color;
        $status->name = $request->name;
        $status->slug = Str::slug($request->name);
        $status->is_approve = $request->is_approve;
        $status->is_applicant_document = $request->is_applicant_document;
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

        return redirect()->back()->with('success', 'Status kandidat berhasil diperbarui.');
    }

    public function bulkUpdateApplicantDocument(Request $request)
    {
        $site_id = $request->site_id;
        $applicant_ids = $request->applicant_ids;
        
        $request->validate([
            'letter_id' => 'required',
            'start_date' => 'required|date',
            'applicant_ids' => 'required|array'
        ]);

        Applicant::whereIn('id', $applicant_ids)->with('user.profile')->each(function ($applicant) use ($request, $site_id) {
            $applicant->user->update([
                'site_id' => $site_id,
            ]);

            $applicant->update([
                'done' => 'document-digital'
            ]);

            Generate::create([
                'letter_id'      => $request->letter_id,
                'letter_number'  => $request->letter_number,
                'romawi'         => $this->getRomawi(date('m')),
                'year'           => date('Y'),
                'start_date'     => $request->start_date,
                'end_date'       => $request->start_date,
                'user_id'        => $applicant->user_id,
                'site_id'        => $site_id,
                'second_party'   => $applicant->user->name,
                'esign'          => 'no-need',
                'description'    => 'Auto generated from Bulk Offering',
            ]);
        });

        return redirect()->back()->with('success', 'Kandidat berhasil dibuatkan dokumen digital.');
    }

    public function bulkUpdateOffering(Request $request)
    {
        $site_id = $request->site_id;
        $applicant_ids = $request->applicant_ids;
        
        $request->validate([
            'letter_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'applicant_ids' => 'required|array'
        ]);

        Applicant::whereIn('id', $applicant_ids)->with('user.profile')->each(function ($applicant) use ($request, $site_id) {
            $applicant->user->update([
                'site_id' => $site_id,
                'is_employee' => 1
            ]);

            $applicant->update([
                'done' => 'done'
            ]);

            $site = Site::with('company')->find($site_id);
            $companyCode  = $site->company->code ?? 'XX';
            $roleCode     = $applicant->user->roles()->first()->code ?? 'XX';
            $monthJoinCode = Carbon::parse($request->start_date)->format('m');
            $yearJoinCode = Carbon::parse($request->start_date)->format('y');
            $employeeNIK = $companyCode . $roleCode . $monthJoinCode . $yearJoinCode . str_pad($applicant->user_id, 5, '0', STR_PAD_LEFT);

            $letter = Letter::with('type_letter')->find($request->letter_id);
            $typeLetter = $letter->type_letter;
            
            $currentNumber = $typeLetter->number ?? 0;
            $newNumber = $currentNumber + 1;
            
            $formattedNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            $letterNumber = $formattedNumber . '/' . $request->letter_number;

            $typeLetter->update([
                'number' => $newNumber
            ]);

            Generate::create([
                'letter_id'      => $request->letter_id,
                'letter_number'  => $letterNumber,
                'romawi'         => $this->getRomawi(date('m')),
                'year'           => date('Y'),
                'start_date'     => $request->start_date,
                'end_date'       => $request->end_date,
                'user_id'        => $applicant->user_id,
                'site_id'        => $site_id,
                'second_party'   => $applicant->user->name,
                'description'    => 'Auto generated from Bulk Offering',
            ]);

            $applicant->user->update([
                'employee_nik' => $employeeNIK,
            ]);
        });

        return redirect()->back()->with('success', 'Kandidat berhasil dikonversi menjadi karyawan.');
    }

    private function getRomawi($month) 
    {
        $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        return $map[(int)$month] ?? 'I';
    }
    
    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        $status->delete();
    
        return redirect()->route('statuses.index')
            ->with('success', 'Data Lokasi berhasil dihapus');
    }
}