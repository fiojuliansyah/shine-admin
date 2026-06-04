<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Company;
use App\Models\User;
use App\DataTables\EmployeesDataTable;
use App\Exports\EmployeesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(EmployeesDataTable $dataTable)
    {
        $companies = Company::with(['sites' => function ($q) {
            $q->orderBy('name', 'asc');
        }])->orderBy('name', 'asc')->get();

        $sites = Site::orderBy('name', 'asc')->get();

        return $dataTable->render('admin.employees.index', compact('sites', 'companies'));
    }

    public function byCompany(Request $request, Company $company)
    {
        if ($request->ajax()) {
            $query = User::with(['profile', 'roles', 'site'])
                ->where('is_employee', 1)
                ->where('is_admin', 0)
                ->whereHas('site', fn($q) => $q->where('company_id', $company->id));

            if (request()->filled('site_id')) {
                $query->where('site_id', request('site_id'));
            }

            if (request()->filled('status')) {
                if (request('status') === 'resign') {
                    $query->whereHas('profile', fn($q) => $q->whereNotNull('resign_date'));
                } elseif (request('status') === 'aktif') {
                    $query->where(function ($q) {
                        $q->whereDoesntHave('profile')
                          ->orWhereHas('profile', fn($q2) => $q2->whereNull('resign_date'));
                    });
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nama', function ($row) {
                    return '<strong>' . e($row->name) . '</strong><br>
                            <small class="text-muted">' . e($row->employee_nik ?? '-') . '</small>';
                })
                ->addColumn('site', fn($row) => e($row->site->name ?? '-'))
                ->addColumn('jabatan', fn($row) => e($row->getRoleNames()->implode(', ') ?: '-'))
                ->addColumn('ttl', function ($row) {
                    $bp = $row->profile->birth_place ?? '-';
                    $bd = $row->profile->birth_date ?? '-';
                    return e($bp . ', ' . $bd);
                })
                ->addColumn('alamat', function ($row) {
                    $parts = array_filter([
                        $row->profile->address   ?? null,
                        $row->profile->rt_rw     ?? null,
                        $row->profile->kelurahan ? 'Kel. ' . $row->profile->kelurahan : null,
                        $row->profile->kecamatan ? 'Kec. ' . $row->profile->kecamatan : null,
                    ]);
                    return e(implode(', ', $parts) ?: '-');
                })
                ->addColumn('join_date', fn($row) => e($row->profile->join_date ?? '-'))
                ->addColumn('status', function ($row) {
                    return $row->profile && $row->profile->resign_date
                        ? '<span class="badge bg-danger">Resign</span>'
                        : '<span class="badge bg-success">Aktif</span>';
                })
                ->rawColumns(['nama', 'status'])
                ->make(true);
        }

        $sites = Site::where('company_id', $company->id)->orderBy('name')->get();

        return view('admin.employees.company', compact('company', 'sites'));
    }

    public function export(Request $request)
    {
        $siteId   = $request->site_id ?: null;
        $siteName = $siteId ? Site::find($siteId)?->name : 'Semua Site';
        $filename = 'Data Karyawan - ' . $siteName . ' - ' . date('d-m-Y') . '.xlsx';

        return Excel::download(new EmployeesExport($siteId), $filename);
    }
}
