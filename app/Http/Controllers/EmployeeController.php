<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeesDataTable;
use App\Exports\EmployeeDataExport;
use App\Imports\EmployeePegawaiImport;
use App\Models\Company;
use App\Models\Site;
use App\Models\User;
use App\Support\EmployeeColumnConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    private const IMPORT_CHUNK = 100;

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
            $query = User::with(['profile', 'roles', 'site.company', 'applicants', 'latestGenerate.letter.numberConfig', 'latestGenerate.letter.type', 'latestGenerate.site.company', 'generates.letter.type'])
                ->where('is_employee', 1)
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'App Administrator'))
                ->whereHas('site', fn ($q) => $q->where('company_id', $company->id));

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            }

            if ($request->status === 'resign') {
                $query->resigned();
            } elseif ($request->status === 'aktif') {
                $query->activeEmployee();
            }

            $columnKeys = array_column($columns, 'key');

            $dt = DataTables::of($query)->addIndexColumn();

            foreach ($columnKeys as $key) {
                $dt->addColumn($key, function ($row) use ($key) {
                    return $this->resolveColumnValue($row, $key);
                });
            }

            // Semua kolom di atas virtual; tanpa filter eksplisit Yajra akan mencari
            // "users.<key>" yang tidak ada di tabel dan query-nya error.
            foreach (self::columnFilters() as $key => $filter) {
                $dt->filterColumn($key, $filter);
            }

            foreach (self::ORDERABLE_COLUMNS as $key => $column) {
                $dt->orderColumn($key, "{$column} \$1");
            }

            $dt->addColumn('aksi', function ($row) {
                $applicant = $row->applicants->first();

                if (! $applicant) {
                    return '<span class="text-muted">-</span>';
                }

                return '<a href="'.route('applicants.resume', $applicant->id).'" target="_blank" class="btn btn-sm btn-white d-inline-flex align-items-center">
                            <i class="ti ti-file-description me-1"></i> Resume
                        </a>';
            });

            $dt->rawColumns(['status', 'aksi']);

            return $dt->make(true);
        }

        $sites = Site::where('company_id', $company->id)->orderBy('name')->get();
        $searchableKeys = array_keys(self::columnFilters());
        $orderableKeys = array_keys(self::ORDERABLE_COLUMNS);

        return view('admin.employees.company', compact('company', 'sites', 'columns', 'searchableKeys', 'orderableKeys'));
    }

    /** Kolom tabel per-PT yang bisa diurutkan => kolom database. */
    private const ORDERABLE_COLUMNS = [
        'name' => 'users.name',
        'email' => 'users.email',
        'phone' => 'users.phone',
        'nik' => 'users.nik',
        'employee_nik' => 'users.employee_nik',
    ];

    /**
     * Kolom tabel per-PT yang bisa dicari => filter query. Kolom lain hanya tampilan.
     *
     * @return array<string, \Closure>
     */
    private static function columnFilters(): array
    {
        $user = fn (string $column) => fn ($query, $keyword) => $query->where("users.{$column}", 'like', "%{$keyword}%");
        $profile = fn (string $column) => fn ($query, $keyword) => $query->whereHas('profile', fn ($q) => $q->where($column, 'like', "%{$keyword}%"));
        $site = fn (string $column) => fn ($query, $keyword) => $query->whereHas('site', fn ($q) => $q->where($column, 'like', "%{$keyword}%"));

        return [
            'name' => $user('name'),
            'email' => $user('email'),
            'phone' => $user('phone'),
            'nik' => $user('nik'),
            'employee_nik' => $user('employee_nik'),
            'area' => $site('name'),
            'klien' => $site('client_name'),
            'jabatan1' => fn ($query, $keyword) => $query->whereHas('roles', fn ($q) => $q->where('name', 'like', "%{$keyword}%")),
            'mother_name' => $profile('mother_name'),
            'no_kk' => $profile('no_kk'),
            'npwp_number' => $profile('npwp_number'),
            'birth_place' => $profile('birth_place'),
            'account_number' => $profile('account_number'),
        ];
    }

    private function resolveColumnValue($row, string $key): string
    {
        $interviewGenerate = null;
        if ($row->generates) {
            $interviewGenerate = $row->generates
                ->filter(fn ($gen) => $gen->letter && $gen->letter->type && $gen->letter->type->name === 'INTERVIEW')
                ->sortByDesc('created_at')
                ->first();
        }

        return match ($key) {
            'name' => e($row->name ?? '-'),
            'email' => e($row->email ?? '-'),
            'phone' => e($row->phone ?? '-'),
            'nik' => e($row->nik ?? '-'),
            'employee_nik' => e($row->employee_nik ?? '-'),
            'no_surat', 'no_srt' => e($interviewGenerate?->formatted_letter_number ?? '-'),
            'company_name' => e($row->site->company->name ?? '-'),
            'area' => e($row->site->name ?? '-'),
            'klien' => e($row->site->client_name ?? '-'),
            'jabatan1' => e($row->getRoleNames()->first() ?? '-'),
            'jabatan2' => e($row->getRoleNames()->skip(1)->first() ?? '-'),
            'birth_place' => e($row->profile->birth_place ?? '-'),
            'birth_date' => e(format_tanggal($row->profile?->birth_date)),
            'gender' => e($row->profile->gender ?? '-'),
            'mother_name' => e($row->profile->mother_name ?? '-'),
            'npwp_number' => e($row->profile->npwp_number ?? '-'),
            'no_kk' => e($row->profile->no_kk ?? '-'),
            'keterangan' => e($row->profile->keterangan ?? '-'),
            'address' => e(implode(', ', array_filter([
                $row->profile->address ?? null,
                $row->profile->rt_rw ?? null,
                $row->profile->kelurahan ? 'Kel. '.$row->profile->kelurahan : null,
                $row->profile->kecamatan ? 'Kec. '.$row->profile->kecamatan : null,
            ])) ?: '-'),
            'religion' => e($row->profile->religion ?? '-'),
            'marriage_status' => e($row->profile->marriage_status ?? '-'),
            'join_date' => e(format_tanggal($row->profile?->join_date)),
            'end_date' => e(format_tanggal($row->profile?->end_date)),
            'resign_date' => e(format_tanggal($row->profile?->resign_date)),
            'bank_name' => e($row->profile->bank_name ?? '-'),
            'account_number' => e($row->profile->account_number ?? '-'),
            'account_name' => e($row->profile->account_name ?? '-'),
            'status' => $row->profile->isResigned()
                                    ? '<span class="badge bg-danger">Resign</span>'
                                    : '<span class="badge bg-success">Aktif</span>',
            'tgl_aktif' => e($row->profile->join_date ? date('d', strtotime($row->profile->join_date)) : '-'),
            'bln_aktif' => e($row->profile->join_date ? date('m', strtotime($row->profile->join_date)) : '-'),
            'thn_aktif' => e($row->profile->join_date ? date('Y', strtotime($row->profile->join_date)) : '-'),
            'tgl_pm' => e($row->latestGenerate?->created_at ? $row->latestGenerate->created_at->format('d') : '-'),
            'bln_pem' => e($row->latestGenerate?->created_at ? $row->latestGenerate->created_at->format('m') : '-'),
            'thn_pem' => e($row->latestGenerate?->created_at ? $row->latestGenerate->created_at->format('Y') : '-'),
            'join_or' => e($row->profile->join_date ? date('d', strtotime($row->profile->join_date)) : '-'),
            'bln_or' => e($row->profile->join_date ? date('m', strtotime($row->profile->join_date)) : '-'),
            'thn_or' => e($row->profile->join_date ? date('Y', strtotime($row->profile->join_date)) : '-'),
            'bln_resign' => e($row->profile->resign_date ? date('m', strtotime($row->profile->resign_date)) : '-'),
            'thn_resign' => e($row->profile->resign_date ? date('Y', strtotime($row->profile->resign_date)) : '-'),
            default => '-',
        };
    }

    public function importForm()
    {
        return view('admin.employees.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $rows = Excel::toArray(new EmployeePegawaiImport, $request->file('file'))[0] ?? [];

        $token = (string) Str::uuid();
        Cache::put("employee_import:{$token}", [
            'rows' => $rows,
            'fileName' => $request->file('file')->getClientOriginalName(),
            'at' => now()->format('d-m-Y H:i'),
            'prepared' => [],
            'rowErrors' => [],
            'totalRows' => 0,
            'created' => 0,
            'updated' => 0,
            'sitesCreated' => 0,
            'rolesCreated' => 0,
        ], now()->addHours(2));

        return response()->json([
            'token' => $token,
            'total' => count($rows),
            'chunk' => self::IMPORT_CHUNK,
        ]);
    }

    public function importValidateChunk(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'offset' => ['required', 'integer', 'min:0'],
        ]);

        $key = 'employee_import:'.$request->string('token');
        $state = Cache::get($key);

        if (! $state) {
            return response()->json(['message' => 'Sesi import kedaluwarsa. Silakan upload ulang.'], 410);
        }

        $offset = $request->integer('offset');
        $slice = array_slice($state['rows'], $offset, self::IMPORT_CHUNK, true);

        $import = new EmployeePegawaiImport;
        $import->totalRows = $state['totalRows'];
        $import->rowErrors = $state['rowErrors'];

        $prepared = $import->validateRows($slice);

        $state['prepared'] = array_merge($state['prepared'], $prepared);
        $state['rowErrors'] = $import->rowErrors;
        $state['totalRows'] = $import->totalRows;
        Cache::put($key, $state, now()->addHours(2));

        $next = $offset + self::IMPORT_CHUNK;
        $done = $next >= count($state['rows']);

        return response()->json([
            'phase' => 'validate',
            'offset' => min($next, count($state['rows'])),
            'total' => count($state['rows']),
            'done' => $done,
            'errorCount' => count($state['rowErrors']),
            'validRows' => count($state['prepared']),
        ]);
    }

    public function importPersistChunk(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'offset' => ['required', 'integer', 'min:0'],
        ]);

        $key = 'employee_import:'.$request->string('token');
        $state = Cache::get($key);

        if (! $state) {
            return response()->json(['message' => 'Sesi import kedaluwarsa. Silakan upload ulang.'], 410);
        }

        if ($state['rowErrors']) {
            return response()->json(['message' => 'Terdapat data yang tidak valid.'], 422);
        }

        $offset = $request->integer('offset');
        $slice = array_slice($state['prepared'], $offset, self::IMPORT_CHUNK);

        $import = new EmployeePegawaiImport;
        DB::transaction(fn () => $import->persist($slice));

        $state['created'] += $import->created;
        $state['updated'] += $import->updated;
        $state['sitesCreated'] += $import->sitesCreated;
        $state['rolesCreated'] += $import->rolesCreated;
        Cache::put($key, $state, now()->addHours(2));

        $next = $offset + self::IMPORT_CHUNK;
        $done = $next >= count($state['prepared']);

        if ($done) {
            $request->session()->forget('employee_import_result');
            Cache::forget($key);
        }

        return response()->json([
            'phase' => 'persist',
            'offset' => min($next, count($state['prepared'])),
            'total' => count($state['prepared']),
            'done' => $done,
            'created' => $state['created'],
            'updated' => $state['updated'],
            'sitesCreated' => $state['sitesCreated'],
            'rolesCreated' => $state['rolesCreated'],
        ]);
    }

    public function importStoreResult(Request $request)
    {
        $request->validate(['token' => ['required', 'string']]);

        $key = 'employee_import:'.$request->string('token');
        $state = Cache::get($key);

        if (! $state) {
            return response()->json(['message' => 'Sesi import kedaluwarsa.'], 410);
        }

        $request->session()->put('employee_import_result', [
            'rowErrors' => $state['rowErrors'],
            'totalRows' => $state['totalRows'],
            'fileName' => $state['fileName'],
            'at' => $state['at'],
        ]);

        Cache::forget($key);

        return response()->json(['redirect' => route('employees.import.result')]);
    }

    public function importResult(Request $request)
    {
        $result = $request->session()->get('employee_import_result');

        if (! $result) {
            return redirect()->route('employees.import.form');
        }

        return view('admin.employees.import-result', $result);
    }

    public function importResultPdf(Request $request)
    {
        $result = $request->session()->get('employee_import_result');

        if (! $result) {
            return redirect()->route('employees.import.form');
        }

        return Pdf::loadView('admin.employees.import-result-pdf', $result)
            ->setPaper('a4', 'portrait')
            ->download('Laporan Kegagalan Import Pegawai - '.date('d-m-Y').'.pdf');
    }

    public function importTemplate()
    {
        return Excel::download(new EmployeeDataExport(template: true), 'Template Import Pegawai.xlsx');
    }

    public function export(Request $request)
    {
        $request->validate([
            'site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'status' => ['nullable', 'in:aktif,resign'],
        ]);

        $site = $request->filled('site_id') ? Site::find($request->integer('site_id')) : null;
        $company = $request->filled('company_id') ? Company::find($request->integer('company_id')) : null;
        $status = $request->input('status') ?: null;

        $scope = $site?->name ?? ($company ? ($company->short_name ?: $company->name).' - Semua Site' : 'Semua Site');
        $statusLabel = match ($status) {
            'aktif' => ' - Aktif',
            'resign' => ' - Resign',
            default => '',
        };
        $filename = 'Data Pegawai - '.str_replace(['/', '\\'], '-', $scope).$statusLabel.' - '.date('d-m-Y').'.xlsx';

        return Excel::download(new EmployeeDataExport(
            siteId: $site?->id,
            companyId: $company?->id,
            status: $status,
        ), $filename);
    }
}
