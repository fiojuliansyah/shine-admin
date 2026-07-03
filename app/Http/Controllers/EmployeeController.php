<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Company;
use App\Models\User;
use App\DataTables\EmployeesDataTable;
use App\Exports\EmployeesExport;
use App\Support\EmployeeColumnConfig;
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
        $columns = EmployeeColumnConfig::getColumns($company->short_name ?? $company->name);

        if ($request->ajax()) {
            $query = User::with(['profile', 'roles', 'site.company', 'latestGenerate.letter.numberConfig', 'latestGenerate.letter.type', 'latestGenerate.site.company', 'generates.letter.type'])
                ->where('is_employee', 1)
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'App Administrator'))
                ->whereHas('site', fn($q) => $q->where('company_id', $company->id));

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            }

            if ($request->filled('status')) {
                if ($request->status === 'resign') {
                    $query->whereHas('profile', fn($q) => $q->whereNotNull('resign_date'));
                } elseif ($request->status === 'aktif') {
                    $query->where(function ($q) {
                        $q->whereDoesntHave('profile')
                          ->orWhereHas('profile', fn($q2) => $q2->whereNull('resign_date'));
                    });
                }
            }

            $columnKeys = array_column($columns, 'key');

            $dt = DataTables::of($query)->addIndexColumn();

            foreach ($columnKeys as $key) {
                $dt->addColumn($key, function ($row) use ($key) {
                    return $this->resolveColumnValue($row, $key);
                });
            }

            $dt->addColumn('aksi', function ($row) {
                return '<a href="' . route('users.resume', $row->id) . '" target="_blank" class="btn btn-sm btn-white d-inline-flex align-items-center">
                            <i class="ti ti-file-description me-1"></i> Resume
                        </a>';
            });

            $dt->rawColumns(['status', 'aksi']);

            return $dt->make(true);
        }

        $sites = Site::where('company_id', $company->id)->orderBy('name')->get();

        return view('admin.employees.company', compact('company', 'sites', 'columns'));
    }

    private function resolveColumnValue($row, string $key): string
    {
        $interviewGenerate = null;
        if ($row->generates) {
            $interviewGenerate = $row->generates
                ->filter(fn($gen) => $gen->letter && $gen->letter->type && $gen->letter->type->name === 'INTERVIEW')
                ->sortByDesc('created_at')
                ->first();
        }
        
        return match ($key) {
            'name'           => e($row->name ?? '-'),
            'email'          => e($row->email ?? '-'),
            'phone'          => e($row->phone ?? '-'),
            'nik'            => e($row->nik ?? '-'),
            'employee_nik'   => e($row->employee_nik ?? '-'),
            'no_surat', 'no_srt' => e($interviewGenerate?->formatted_letter_number ?? '-'),
            'company_name'   => e($row->site->company->name ?? '-'),
            'area'           => e($row->site->name ?? '-'),
            'klien'          => e($row->site->client_name ?? '-'),
            'jabatan1'       => e($row->getRoleNames()->first() ?? '-'),
            'jabatan2'       => e($row->getRoleNames()->skip(1)->first() ?? '-'),
            'birth_place'    => e($row->profile->birth_place ?? '-'),
            'birth_date'     => e($row->profile->birth_date ?? '-'),
            'gender'         => e($row->profile->gender ?? '-'),
            'mother_name'    => e($row->profile->mother_name ?? '-'),
            'npwp_number'    => e($row->profile->npwp_number ?? '-'),
            'address'        => e(implode(', ', array_filter([
                                    $row->profile->address ?? null,
                                    $row->profile->rt_rw ?? null,
                                    $row->profile->kelurahan ? 'Kel. '.$row->profile->kelurahan : null,
                                    $row->profile->kecamatan ? 'Kec. '.$row->profile->kecamatan : null,
                                ])) ?: '-'),
            'religion'       => e($row->profile->religion ?? '-'),
            'marriage_status'=> e($row->profile->marriage_status ?? '-'),
            'join_date'      => e($row->profile->join_date ?? '-'),
            'resign_date'    => e($row->profile->resign_date ?? '-'),
            'bank_name'      => e($row->profile->bank_name ?? '-'),
            'account_number' => e($row->profile->account_number ?? '-'),
            'account_name'   => e($row->profile->account_name ?? '-'),
            'status'         => $row->profile && $row->profile->resign_date
                                    ? '<span class="badge bg-danger">Resign</span>'
                                    : '<span class="badge bg-success">Aktif</span>',
            'tgl_aktif'      => e($row->profile->join_date ? date('d', strtotime($row->profile->join_date)) : '-'),
            'bln_aktif'      => e($row->profile->join_date ? date('m', strtotime($row->profile->join_date)) : '-'),
            'thn_aktif'      => e($row->profile->join_date ? date('Y', strtotime($row->profile->join_date)) : '-'),
            'tgl_pm'         => e($row->latestGenerate?->created_at ? $row->latestGenerate->created_at->format('d') : '-'),
            'bln_pem'        => e($row->latestGenerate?->created_at ? $row->latestGenerate->created_at->format('m') : '-'),
            'thn_pem'        => e($row->latestGenerate?->created_at ? $row->latestGenerate->created_at->format('Y') : '-'),
            'join_or'        => e($row->profile->join_date ? date('d', strtotime($row->profile->join_date)) : '-'),
            'bln_or'         => e($row->profile->join_date ? date('m', strtotime($row->profile->join_date)) : '-'),
            'thn_or'         => e($row->profile->join_date ? date('Y', strtotime($row->profile->join_date)) : '-'),
            'bln_resign'     => e($row->profile->resign_date ? date('m', strtotime($row->profile->resign_date)) : '-'),
            'thn_resign'     => e($row->profile->resign_date ? date('Y', strtotime($row->profile->resign_date)) : '-'),
            default          => '-',
        };
    }

    public function export(Request $request)
    {
        $siteId   = $request->site_id ?: null;
        $siteName = $siteId ? Site::find($siteId)?->name : 'Semua Site';
        $filename = 'Data Karyawan - ' . $siteName . ' - ' . date('d-m-Y') . '.xlsx';

        return Excel::download(new EmployeesExport($siteId), $filename);
    }
}
