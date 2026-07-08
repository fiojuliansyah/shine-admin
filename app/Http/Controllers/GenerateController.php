<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\Letter;
use App\Models\Generate;
use App\Models\TypeLetter;
use App\Models\CustomVariable;
use App\Exports\GenerateTemplateExport;
use App\Imports\GenerateTemplateImport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateController extends Controller
{
    use \App\Traits\PdfPageImageTrait;
    use \App\Traits\SalaryVariableTrait;
    public function folders()
    {
        $types = TypeLetter::withCount('generates')->orderBy('name')->get();
        $uncategorizedCount = Generate::whereHas('letter', function ($q) {
            $q->whereNull('type_letter_id');
        })->count();

        return view('admin.generates.folders', compact('types', 'uncategorizedCount'));
    }

    private function resolveCustomVariableColumns($typeId = null)
    {
        $query = CustomVariable::query()
            ->select('variable', 'name')
            ->whereNotNull('variable')
            ->where('variable', '!=', '');

        if ($typeId === 'none') {
            $query->whereHas('letter', function ($q) {
                $q->whereNull('type_letter_id');
            });
        } elseif ($typeId) {
            $query->whereHas('letter', function ($q) use ($typeId) {
                $q->where('type_letter_id', $typeId);
            });
        }

        return $query->get()
            ->unique('variable')
            ->values()
            ->map(function ($cv) {
                return [
                    'variable' => $cv->variable,
                    'name' => $cv->name ?: $cv->variable,
                    'key' => 'cv_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cv->variable),
                ];
            });
    }

    public function index()
    {
        $types = TypeLetter::with('letters')->get();
        $letters = Letter::all();
        $sites = Site::with('company')->get();
        
        $filters = [
            'site_id' => request('site_id'),
            'type_id' => request('type_id'),
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
        ];
    
        $customVarColumns = $this->resolveCustomVariableColumns(request('type_id'));

        if (request()->ajax()) {
            $generates = Generate::with([
                    'letter.type',
                    'site.company',
                    'user.profile',
                    'user.roles',
                    'user.site.company',
                    'valueVariables.customVariable',
                ])
                ->orderBy('created_at', 'DESC')
                // Search filter
                ->when(request('search')['value'], function ($query) {
                    $search = request('search')['value'];
                    return $query->whereHas('letter', function ($q) use ($search) {
                        $q->where('title', 'like', "%$search%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('site', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
                })
                // Site filter
                ->when(request('site_id'), function ($query) {
                    return $query->where('site_id', request('site_id'));
                })
                // Type filter
                ->when(request('type_id') === 'none', function ($query) {
                    return $query->whereHas('letter', function ($q) {
                        $q->whereNull('type_letter_id');
                    });
                })
                ->when(request('type_id') && request('type_id') !== 'none', function ($query) {
                    $typeId = request('type_id');
                    return $query->whereHas('letter', function ($q) use ($typeId) {
                        $q->where('type_letter_id', $typeId);
                    });
                })
                // Date range filter
                ->when(request('start_date') && request('end_date'), function ($query) {
                    return $query->whereBetween('created_at', [
                        request('start_date') . ' 00:00:00',
                        request('end_date') . ' 23:59:59'
                    ]);
                })
                ->when(request('start_date') && !request('end_date'), function ($query) {
                    return $query->where('created_at', '>=', request('start_date') . ' 00:00:00');
                })
                ->when(!request('start_date') && request('end_date'), function ($query) {
                    return $query->where('created_at', '<=', request('end_date') . ' 23:59:59');
                });
    
            $dataTable = DataTables::of($generates)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="generate-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('no_surat', function ($row) {
                    return e($row->formatted_letter_number ?? '-');
                })
                ->addColumn('nik_karyawan', function ($row) {
                    return e($row->user->employee_nik ?? '-');
                })
                ->addColumn('nama_perusahaan', function ($row) {
                    $company = $row->user->site->company->name
                        ?? $row->site->company->name
                        ?? null;
                    return e($company ?: '-');
                })
                ->addColumn('tgl_surat', function ($row) {
                    return $row->created_at ? $row->created_at->translatedFormat('d') : '-';
                })
                ->addColumn('bulan_surat', function ($row) {
                    return $row->created_at ? $row->created_at->locale('id')->translatedFormat('F') : '-';
                })
                ->addColumn('tahun_surat', function ($row) {
                    return $row->created_at ? $row->created_at->translatedFormat('Y') : '-';
                })
                ->addColumn('nama', function ($row) {
                    return e($row->user->name ?? '-');
                })
                ->addColumn('email', function ($row) {
                    return e($row->user->email ?? '-');
                })
                ->addColumn('ibu_kandung', function ($row) {
                    return e($row->user->profile->mother_name ?? '-');
                })
                ->addColumn('tempat_lahir', function ($row) {
                    return e($row->user->profile->birth_place ?? '-');
                })
                ->addColumn('tanggal_lahir', function ($row) {
                    return e($row->user->profile->birth_date ?? '-');
                })
                ->addColumn('jenis_kelamin', function ($row) {
                    return e($row->user->profile->gender ?? '-');
                })
                ->addColumn('no_ktp', function ($row) {
                    return e($row->user->nik ?? '-');
                })
                ->addColumn('no_kk', function ($row) {
                    return e($row->user->profile->kk_number ?? '-');
                })
                ->addColumn('no_npwp', function ($row) {
                    return e($row->user->profile->npwp_number ?? '-');
                })
                ->addColumn('alamat_ktp', function ($row) {
                    $parts = array_filter([
                        $row->user->profile->address    ?? null,
                        $row->user->profile->rt_rw      ?? null,
                        $row->user->profile->kelurahan  ? 'Kel. ' . $row->user->profile->kelurahan : null,
                        $row->user->profile->kecamatan  ? 'Kec. ' . $row->user->profile->kecamatan : null,
                    ]);
                    return e(implode(', ', $parts) ?: '-');
                })
                ->addColumn('no_handphone', function ($row) {
                    return e($row->user->phone ?? '-');
                })
                ->addColumn('agama', function ($row) {
                    return e($row->user->profile->religion ?? '-');
                })
                ->addColumn('status_pernikahan', function ($row) {
                    return e($row->user->profile->marriage_status ?? '-');
                })
                ->addColumn('jabatan', function ($row) {
                    return e($row->user && $row->user->roles->isNotEmpty() ? $row->user->roles->pluck('name')->implode(', ') : '-');
                })
                ->addColumn('site_project', function ($row) {
                    return e($row->user->site->name ?? $row->site->name ?? '-');
                })
                ->addColumn('nama_client', function ($row) {
                    return e($row->user->site->client_name ?? $row->site->client_name ?? '-');
                })
                ->addColumn('jabatan_client', function ($row) {
                    return e($row->user->site->client_position ?? $row->site->client_position ?? '-');
                })
                ->addColumn('tgl_join', function ($row) {
                    $join = $row->user->profile->join_date ?? null;
                    return $join ? e(\Illuminate\Support\Carbon::parse($join)->translatedFormat('d')) : '-';
                })
                ->addColumn('bulan_join', function ($row) {
                    $join = $row->user->profile->join_date ?? null;
                    return $join ? e(\Illuminate\Support\Carbon::parse($join)->locale('id')->translatedFormat('F')) : '-';
                })
                ->addColumn('tahun_join', function ($row) {
                    $join = $row->user->profile->join_date ?? null;
                    return $join ? e(\Illuminate\Support\Carbon::parse($join)->translatedFormat('Y')) : '-';
                })
                ->addColumn('nama_bank', function ($row) {
                    return e($row->user->profile->bank_name ?? '-');
                })
                ->addColumn('nama_rekening', function ($row) {
                    return e($row->user->profile->account_name ?? '-');
                })
                ->addColumn('no_rekening', function ($row) {
                    return e($row->user->profile->account_number ?? '-');
                })
                ->addColumn('signature', function ($row) {
                    $requireHrd = $row->letter->require_hrd_signature ?? true;
                    $requireEmployee = $row->letter->require_employee_signature ?? true;

                    if (!$requireHrd && !$requireEmployee) {
                        return '<span class="badge bg-secondary">Tidak Perlu Tanda Tangan</span>';
                    }

                    $rows = '';
                    if ($requireEmployee) {
                        $employeeStatus = $row->second_party_esign === null
                            ? '<span class="badge bg-danger">Belum Tertanda Tangan</span>'
                            : '<span class="badge bg-success">Sudah Tertanda Tangan</span>';
                        $rows .= '
                        <div class="row">
                            <div class="col-4">Employee</div>
                            <div class="col-8">: ' . $employeeStatus . '</div>
                        </div>';
                    }

                    if ($requireHrd) {
                        $hrdStatus = $row->esign === null
                            ? '<span class="badge bg-danger">Belum Tertanda Tangan</span>'
                            : '<span class="badge bg-success">Sudah Tertanda Tangan</span>';
                        if ($rows !== '') {
                            $rows .= '<br>';
                        }
                        $rows .= '
                        <div class="row">
                            <div class="col-4">HRD</div>
                            <div class="col-8">: ' . $hrdStatus . '</div>
                        </div>';
                    }

                    return $rows;
                })
                ->addColumn('action', function ($row) {
                    return view('admin.generates.partials.actions', compact('row'))->render();
                });

            foreach ($customVarColumns as $col) {
                $variable = $col['variable'];
                $dataTable->addColumn($col['key'], function ($row) use ($variable) {
                    $value = $row->valueVariables
                        ->first(fn($cv) => $cv->customVariable && $cv->customVariable->variable === $variable);
                    return e($value->value ?? '-');
                });
            }

            return $dataTable
                ->rawColumns(['action', 'checkbox', 'signature'])
                ->make(true);
        }
    
        // Pass filters to the view
        $currentType = null;
        if (request('type_id') && request('type_id') !== 'none') {
            $currentType = TypeLetter::find(request('type_id'));
        } elseif (request('type_id') === 'none') {
            $currentType = 'none';
        }

        return view('admin.generates.index', compact('letters', 'sites', 'types', 'filters', 'currentType', 'customVarColumns'));
    }
    

    public function bulkApprove(Request $request)
    {
        $esign = $request->esign;
        $ids = $request->input('ids');
    
        if (empty($esign)) {
            return redirect()->back()->with('error', 'Tanda tangan digital Anda belum ada.');
        }

        $esign = $this->convertEsignToBase64($esign);

        $ids = explode(',', $ids);
    
        $updated = Generate::whereIn('id', $ids)
                            ->update(['esign' => $esign]);
    
        if ($updated) {
            return redirect()->back()->with('success', 'Tanda tangan berhasil disalin ke surat yang dipilih.');
        } else {
            return redirect()->back()->with('error', 'Tidak ada data yang diperbarui.');
        }
    }
    

    private function convertEsignToBase64(string $esign): string
    {
        $esign = trim($esign);

        // Tanda tangan sudah berupa PNG base64 data URI, simpan apa adanya.
        if (str_starts_with($esign, 'data:image/')) {
            return $esign;
        }

        // Kompatibilitas data lama: markup SVG mentah dirasterisasi ke PNG base64.
        if (str_contains($esign, '<svg') && extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick();
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImageBlob($esign);
                $imagick->setImageFormat('png');
                $png = $imagick->getImageBlob();
                $imagick->clear();

                return 'data:image/png;base64,' . base64_encode($png);
            } catch (\Exception $e) {
                return $esign;
            }
        }

        return $esign;
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }
    
        $ids = explode(',', $ids);

        $generates = Generate::whereIn('id', $ids)->get();

        if ($generates->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data yang dihapus.');
        }

        foreach ($generates as $generate) {
            $generate->delete();
        }

        return redirect()->back()->with('success', 'Data yang dipilih berhasil dihapus. Nomor surat dan NIK karyawan telah direset.');
    }

    public function destroy(Generate $generate)
    {
        $generate->delete();

        return redirect()->route('generates.index')
            ->with('success', 'Surat berhasil dihapus. Nomor surat dan NIK karyawan telah direset.');
    }

    public function exportTemplate(Request $request)
    {
        $request->validate([
            'letter_id' => 'required|exists:letters,id',
            'site_id'   => 'nullable|exists:sites,id',
        ]);

        $letter = Letter::with('customVariables')->findOrFail($request->letter_id);
        $withEmployees = !$request->boolean('empty_template');

        $filename = 'Template - ' . Str::slug($letter->title, ' ') . ' - ' . date('d-m-Y') . '.xlsx';

        return Excel::download(
            new GenerateTemplateExport($letter, $request->site_id ?: null, $withEmployees),
            $filename
        );
    }

    public function importTemplate(Request $request)
    {
        $request->validate([
            'letter_id' => 'required|exists:letters,id',
            'site_id'   => 'nullable|exists:sites,id',
            'file'      => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $letter = Letter::with('type', 'site', 'customVariables', 'numberConfig.sharedCounter')
            ->findOrFail($request->letter_id);

        $import = new GenerateTemplateImport($letter, $request->site_id ?: null);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return redirect()->route('generates.index')
                ->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }

        $message = "Import selesai. {$import->getCreated()} surat terbit dibuat.";
        if ($import->getSalaryUpdated() > 0) {
            $message .= " {$import->getSalaryUpdated()} pengaturan gaji karyawan diperbarui.";
        }
        if ($import->getSkipped() > 0) {
            $niks = implode(', ', array_slice($import->getSkippedNiks(), 0, 10));
            $more = $import->getSkipped() > 10 ? ' ...' : '';
            $message .= " {$import->getSkipped()} baris dilewati (NIK karyawan tidak ditemukan: {$niks}{$more}).";
        }

        return redirect()->route('generates.index')->with('success', $message);
    }

    public function show(Generate $generate)
    {
        $no_surat = $generate->formatted_letter_number ?? 'belum ada no surat';
        
        $tgl_surat = isset($generate->created_at) 
            ? Carbon::parse($generate->created_at)->locale('id')->translatedFormat('j F Y') 
            : '';
            
        $romawi = $generate->romawi ?? 'belum ada data';
        $tahun = $generate->year ?? 'belum ada tahun';
        $hari = $generate->day ?? 'belum ada hari';
        $pihak_2 = $generate->second_party ?? 'belum ada data';
        $sign_2 = $generate->second_party_esign ?? 'belum ada data';
        $nama_karyawan = strtoupper($generate->user->name ?? 'belum ada nama');
        
        $ttl = isset($generate->user->profile->birth_place) && isset($generate->user->profile->birth_date)
            ? $generate->user->profile->birth_place . ', ' . Carbon::parse($generate->user->profile->birth_date)->format('d-m-Y')
            : 'belum ada data';
            
        $alamat = $generate->user->profile->address ?? 'belum ada alamat';
        $handphone = $generate->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $generate->user->employee_nik ?? 'belum ada no karyawan';
        $area = strtoupper($generate->user->site->name ?? 'belum ada area');
        $area_project = $generate->user->site->area ?? 'belum ada area';
        $nik_ktp = $generate->user->nik ?? 'belum ada NIK KTP';
        $jenis_kelamin = $generate->user->profile->gender ?? 'belum ada Jenis Kelamin';
        $nama_client = $generate->user->site->client_name ?? 'belum ada area';
        $jabatan_client = $generate->user->site->client_position ?? 'belum ada jabatan client';
        $jabatan = strtoupper($generate->user->roles->first()->name ?? 'belum ada jabatan');
        $esign = $generate->esign ?? 'belum ada tanda tangan';
        $nama_kontak = $generate->emergency_name ?? 'belum ada nama';
        $no_kontak = $generate->emergency_number ?? 'belum ada no hp';
        $alamat_kontak = $generate->emergency_address ?? 'belum ada alamat';
        $hubungan = $generate->relationship ?? 'belum ada hubungan';
        
        $gaji_type = $generate->gaji_type ?? 'monthly'; 
        if ($gaji_type === 'monthly') {
            $gaji = $generate->user->payroll->salary_amount ?? 0;
        } elseif ($gaji_type === 'daily') {
            $gaji = $generate->user->payroll->daily_rate ?? 0;
        } else {
            $gaji = 0;
        }

        $tunjangan_calculation = 0;
        $tunjangan = 'Tidak ada data';
        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                if ($component->amount) {
                    $tunjangan_calculation += $component->amount;
                    $tunjangan = $component->name . ' = ' . $tunjangan_calculation;
                } elseif ($component->percentage) {
                    $tunjangan_calculation += ($gaji * $component->percentage) / 100;
                    $tunjangan = $component->name . ' = ' . $tunjangan_calculation;
                }
            }
        }

        $komisi_calculation = 0;
        $komisi = 'Tidak ada data';
        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                if ($component->component_type === 'comission') {
                    if ($component->amount) {
                        $komisi_calculation += $component->amount;
                        $komisi = $component->name . ' = ' . $komisi_calculation;
                    } elseif ($component->percentage) {
                        $komisi_calculation += ($gaji * $component->percentage) / 100;
                        $komisi = $component->name . ' = ' . $komisi_calculation;
                    }
                }
            }
        }

        $potongan_calculation = 0;
        $potongan = 'Tidak ada data';
        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                if ($component->component_type === 'deduction') {
                    if ($component->amount) {
                        $potongan_calculation += $component->amount;
                        $potongan = $component->name . ' = ' . $potongan_calculation;
                    } elseif ($component->percentage) {
                        $potongan_calculation += ($gaji * $component->percentage) / 100;
                        $potongan = $component->name . ' = ' . $potongan_calculation;
                    }
                }
            }
        }
        
        $mulai = isset($generate->start_date) 
            ? Carbon::parse($generate->start_date)->locale('id')->translatedFormat('j F Y') 
            : 'belum ada data';

        $selesai = isset($generate->end_date) 
            ? Carbon::parse($generate->end_date)->locale('id')->translatedFormat('j F Y') 
            : 'Sampai dengan Selesai';

        $search = [
            '[no_surat]', '[tgl_surat]', '[romawi]', '[tahun]', '[hari]', '[mulai]', '[selesai]',
            '[pihak_2]', '[sign_2]', '[nama_karyawan]', '[nik_ktp]', '[jenis_kelamin]', '[ttl]', '[alamat]', '[handphone]',
            '[no_karyawan]', '[lokasi_project]', '[area]', '[nama_client]', '[jabatan_client]', '[jabatan]',
            '[esign]', '[gaji]', '[tunjangan]', '[komisi]', '[potongan]', '[nama_kontak]',
            '[no_kontak]', '[alamat_kontak]', '[hubungan]'
        ];

        $replace = [
            $no_surat, $tgl_surat, $romawi, $tahun, $hari, $mulai, $selesai,
            $pihak_2, $sign_2, $nama_karyawan, $nik_ktp, $jenis_kelamin, $ttl, $alamat, $handphone,
            $no_karyawan, $area, $area_project, $nama_client, $jabatan_client, $jabatan,
            $esign, $gaji, $tunjangan, $komisi, $potongan, $nama_kontak,
            $no_kontak, $alamat_kontak, $hubungan
        ];

        [$salarySearch, $salaryReplace] = $this->buildSalaryVariables($generate->user);
        $search = array_merge($search, $salarySearch);
        $replace = array_merge($replace, $salaryReplace);

        $customValues = \App\Models\ValueVariable::where('generate_id', $generate->id)
            ->with('customVariable')
            ->get();

        foreach ($customValues as $cv) {
            if ($cv->customVariable) {
                $search[] = '[' . $cv->customVariable->variable . ']';
                $replace[] = $cv->value;
            }
        }

        $generate->letter->description = str_replace($search, $replace, $generate->letter->description);

        return view('admin.generates.show', compact('generate'));
    }

    private function _buildSearchReplace(Generate $generate): array
    {
        $no_surat = $generate->formatted_letter_number ?? 'belum ada no surat';
        $tgl_surat = isset($generate->created_at) ? Carbon::parse($generate->created_at)->locale('id')->translatedFormat('j F Y') : '';
        $romawi = $generate->romawi ?? 'belum ada data';
        $tahun = $generate->year ?? 'belum ada tahun';
        $hari = $generate->day ?? 'belum ada hari';
        $pihak_2 = $generate->second_party ?? 'belum ada data';
        $sign_2 = $generate->second_party_esign ?? 'belum ada data';
        $nama_karyawan = strtoupper($generate->user->name ?? 'belum ada nama');
        $ttl = isset($generate->user->profile->birth_place) && isset($generate->user->profile->birth_date)
            ? $generate->user->profile->birth_place . ', ' . Carbon::parse($generate->user->profile->birth_date)->format('d-m-Y')
            : 'belum ada data';
        $alamat = $generate->user->profile->address ?? 'belum ada alamat';
        $handphone = $generate->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $generate->user->employee_nik ?? 'belum ada no karyawan';
        $area = strtoupper($generate->user->site->name ?? 'belum ada area');
        $area_project = $generate->user->site->area ?? 'belum ada area';
        $nik_ktp = $generate->user->nik ?? 'belum ada NIK KTP';
        $jenis_kelamin = $generate->user->profile->gender ?? 'belum ada Jenis Kelamin';
        $nama_client = $generate->user->site->client_name ?? 'belum ada area';
        $jabatan_client = $generate->user->site->client_position ?? 'belum ada jabatan client';
        $jabatan = strtoupper($generate->user->roles->first()->name ?? 'belum ada jabatan');
        $esign = $generate->esign ?? 'belum ada tanda tangan';
        $nama_kontak = $generate->emergency_name ?? 'belum ada nama';
        $no_kontak = $generate->emergency_number ?? 'belum ada no hp';
        $alamat_kontak = $generate->emergency_address ?? 'belum ada alamat';
        $hubungan = $generate->relationship ?? 'belum ada hubungan';

        $gaji_type = $generate->gaji_type ?? 'monthly';
        if ($gaji_type === 'monthly') $gaji = $generate->user->payroll->salary_amount ?? 0;
        elseif ($gaji_type === 'daily') $gaji = $generate->user->payroll->daily_rate ?? 0;
        else $gaji = 0;

        $tunjangan_calculation = 0; $tunjangan = 'Tidak ada data';
        $komisi_calculation = 0; $komisi = 'Tidak ada data';
        $potongan_calculation = 0; $potongan = 'Tidak ada data';

        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                $amt = $component->amount ? $component->amount : ($component->percentage ? ($gaji * $component->percentage / 100) : 0);
                $tunjangan_calculation += $amt; $tunjangan = $component->name . ' = ' . $tunjangan_calculation;
                if ($component->component_type === 'comission') { $komisi_calculation += $amt; $komisi = $component->name . ' = ' . $komisi_calculation; }
                if ($component->component_type === 'deduction') { $potongan_calculation += $amt; $potongan = $component->name . ' = ' . $potongan_calculation; }
            }
        }

        $mulai = isset($generate->start_date) ? Carbon::parse($generate->start_date)->locale('id')->translatedFormat('j F Y') : 'belum ada data';
        $selesai = isset($generate->end_date) ? Carbon::parse($generate->end_date)->locale('id')->translatedFormat('j F Y') : 'Sampai dengan Selesai';

        $search = ['[no_surat]','[tgl_surat]','[romawi]','[tahun]','[hari]','[mulai]','[selesai]','[pihak_2]','[sign_2]','[nama_karyawan]','[nik_ktp]','[jenis_kelamin]','[ttl]','[alamat]','[handphone]','[no_karyawan]','[lokasi_project]','[area]','[nama_client]','[jabatan_client]','[jabatan]','[esign]','[gaji]','[tunjangan]','[komisi]','[potongan]','[nama_kontak]','[no_kontak]','[alamat_kontak]','[hubungan]'];
        $replace = [$no_surat,$tgl_surat,$romawi,$tahun,$hari,$mulai,$selesai,$pihak_2,$sign_2,$nama_karyawan,$nik_ktp,$jenis_kelamin,$ttl,$alamat,$handphone,$no_karyawan,$area,$area_project,$nama_client,$jabatan_client,$jabatan,$esign,$gaji,$tunjangan,$komisi,$potongan,$nama_kontak,$no_kontak,$alamat_kontak,$hubungan];

        [$salarySearch, $salaryReplace] = $this->buildSalaryVariables($generate->user);
        $search = array_merge($search, $salarySearch);
        $replace = array_merge($replace, $salaryReplace);

        $customValues = \App\Models\ValueVariable::where('generate_id', $generate->id)->with('customVariable')->get();
        foreach ($customValues as $cv) {
            if ($cv->customVariable) { $search[] = '[' . $cv->customVariable->variable . ']'; $replace[] = $cv->value; }
        }

        return [$search, $replace];
    }

    public function printView(Generate $generate)
    {
        [$search, $replace] = $this->_buildSearchReplace($generate);

        $description = $generate->letter->description ?? '';
        $pages = [];
        $isFabric = false;
        $title = $generate->letter->title ?? 'Surat';

        if ($description) {
            try {
                $parsed = json_decode($description, true);
                if (isset($parsed['pages']) && is_array($parsed['pages'])) {
                    $isFabric = true;
                    $descriptionReplaced = str_replace($search, $replace, $description);
                    $parsedReplaced = json_decode($descriptionReplaced, true);
                    $pages = $parsedReplaced['pages'] ?? [];
                }
            } catch (\Exception $e) {}
        }

        if (!$isFabric) {
            $description = str_replace($search, $replace, $description);
        }

        return view('admin.letters.print', compact('pages', 'isFabric', 'title', 'description'));
    }

    public function pdf(Generate $generate)
    {
        $no_surat = $generate->formatted_letter_number ?? 'belum ada no surat';
        $tgl_surat = isset($generate->created_at) ? Carbon::parse($generate->created_at)->locale('id')->translatedFormat('j F Y') : '';
        $romawi = $generate->romawi ?? 'belum ada data';
        $tahun = $generate->year ?? 'belum ada tahun';
        $hari = $generate->day ?? 'belum ada hari';
        $pihak_2 = $generate->second_party ?? 'belum ada data';
        $sign_2 = $generate->second_party_esign ?? 'belum ada data';
        $nama_karyawan = strtoupper($generate->user->name ?? 'belum ada nama');
        $ttl = isset($generate->user->profile->birth_place) && isset($generate->user->profile->birth_date)
            ? $generate->user->profile->birth_place . ', ' . Carbon::parse($generate->user->profile->birth_date)->format('d-m-Y')
            : 'belum ada data';
        $alamat = $generate->user->profile->address ?? 'belum ada alamat';
        $handphone = $generate->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $generate->user->employee_nik ?? 'belum ada no karyawan';
        $area = strtoupper($generate->user->site->name ?? 'belum ada area');
        $area_project = $generate->user->site->area ?? 'belum ada area';
        $nik_ktp = $generate->user->nik ?? 'belum ada NIK KTP';
        $jenis_kelamin = $generate->user->profile->gender ?? 'belum ada Jenis Kelamin';
        $nama_client = $generate->user->site->client_name ?? 'belum ada area';
        $jabatan_client = $generate->user->site->client_position ?? 'belum ada jabatan client';
        $jabatan = strtoupper($generate->user->roles->first()->name ?? 'belum ada jabatan');
        $esign = $generate->esign ?? 'belum ada tanda tangan';
        $nama_kontak = $generate->emergency_name ?? 'belum ada nama';
        $no_kontak = $generate->emergency_number ?? 'belum ada no hp';
        $alamat_kontak = $generate->emergency_address ?? 'belum ada alamat';
        $hubungan = $generate->relationship ?? 'belum ada hubungan';

        $gaji_type = $generate->gaji_type ?? 'monthly';
        if ($gaji_type === 'monthly') {
            $gaji = $generate->user->payroll->salary_amount ?? 0;
        } elseif ($gaji_type === 'daily') {
            $gaji = $generate->user->payroll->daily_rate ?? 0;
        } else {
            $gaji = 0;
        }

        $tunjangan_calculation = 0;
        $tunjangan = 'Tidak ada data';
        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                if ($component->amount) {
                    $tunjangan_calculation += $component->amount;
                    $tunjangan = $component->name . ' = ' . $tunjangan_calculation;
                } elseif ($component->percentage) {
                    $tunjangan_calculation += ($gaji * $component->percentage) / 100;
                    $tunjangan = $component->name . ' = ' . $tunjangan_calculation;
                }
            }
        }

        $komisi_calculation = 0;
        $komisi = 'Tidak ada data';
        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                if ($component->component_type === 'comission') {
                    if ($component->amount) {
                        $komisi_calculation += $component->amount;
                        $komisi = $component->name . ' = ' . $komisi_calculation;
                    } elseif ($component->percentage) {
                        $komisi_calculation += ($gaji * $component->percentage) / 100;
                        $komisi = $component->name . ' = ' . $komisi_calculation;
                    }
                }
            }
        }

        $potongan_calculation = 0;
        $potongan = 'Tidak ada data';
        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                if ($component->component_type === 'deduction') {
                    if ($component->amount) {
                        $potongan_calculation += $component->amount;
                        $potongan = $component->name . ' = ' . $potongan_calculation;
                    } elseif ($component->percentage) {
                        $potongan_calculation += ($gaji * $component->percentage) / 100;
                        $potongan = $component->name . ' = ' . $potongan_calculation;
                    }
                }
            }
        }

        $mulai = isset($generate->start_date) ? Carbon::parse($generate->start_date)->locale('id')->translatedFormat('j F Y') : 'belum ada data';
        $selesai = isset($generate->end_date) ? Carbon::parse($generate->end_date)->locale('id')->translatedFormat('j F Y') : 'Sampai dengan Selesai';

        $search = [
            '[no_surat]', '[tgl_surat]', '[romawi]', '[tahun]', '[hari]', '[mulai]', '[selesai]',
            '[pihak_2]', '[sign_2]', '[nama_karyawan]', '[nik_ktp]', '[jenis_kelamin]', '[ttl]', '[alamat]', '[handphone]',
            '[no_karyawan]', '[lokasi_project]', '[area]', '[nama_client]', '[jabatan_client]', '[jabatan]',
            '[esign]', '[gaji]', '[tunjangan]', '[komisi]', '[potongan]', '[nama_kontak]',
            '[no_kontak]', '[alamat_kontak]', '[hubungan]'
        ];

        $replace = [
            $no_surat, $tgl_surat, $romawi, $tahun, $hari, $mulai, $selesai,
            $pihak_2, $sign_2, $nama_karyawan, $nik_ktp, $jenis_kelamin, $ttl, $alamat, $handphone,
            $no_karyawan, $area, $area_project, $nama_client, $jabatan_client, $jabatan,
            $esign, $gaji, $tunjangan, $komisi, $potongan, $nama_kontak,
            $no_kontak, $alamat_kontak, $hubungan
        ];

        [$salarySearch, $salaryReplace] = $this->buildSalaryVariables($generate->user);
        $search = array_merge($search, $salarySearch);
        $replace = array_merge($replace, $salaryReplace);

        $customValues = \App\Models\ValueVariable::where('generate_id', $generate->id)
            ->with('customVariable')
            ->get();

        foreach ($customValues as $cv) {
            if ($cv->customVariable) {
                $search[] = '[' . $cv->customVariable->variable . ']';
                $replace[] = $cv->value;
            }
        }

        $description = $generate->letter->description ?? '';
        $pages = [];
        $isFabric = false;

        if ($description) {
            try {
                $parsed = json_decode($description, true);
                if (isset($parsed['pages']) && is_array($parsed['pages'])) {
                    $isFabric = true;
                    $descriptionReplaced = str_replace($search, $replace, $description);
                    $parsedReplaced = json_decode($descriptionReplaced, true);
                    $pages = $this->savePageImages($parsedReplaced['pages'] ?? []);
                }
            } catch (\Exception $e) {}
        }

        if (!$isFabric) {
            $description = str_replace($search, $replace, $description);
        }

        $title = $generate->letter->title ?? 'surat';

        $pdf = Pdf::loadView('admin.letters.pdf', compact('pages', 'isFabric', 'description', 'title'))
            ->setPaper([0, 0, 794, 1123], 'portrait');

        $response = $pdf->stream($title . '.pdf');
        $this->cleanPageImages($pages);
        return $response;
    }
}
