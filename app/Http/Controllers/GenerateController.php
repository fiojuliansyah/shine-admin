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
use Illuminate\Support\Facades\Log;
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
                ->when(request('search')['value'] ?? null, function ($query, $search) {
                    // Dikelompokkan agar OR pencarian tidak melewati filter site/jenis/tanggal
                    return $query->where(function ($q) use ($search) {
                        $q->whereHas('letter', fn ($l) => $l->where('title', 'like', "%$search%"))
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$search%"))
                            ->orWhereHas('site', fn ($st) => $st->where('name', 'like', "%$search%"));
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
    
            // Satu surat yang datanya bermasalah tidak boleh membuat seluruh tabel gagal (Ajax error):
            // sel tersebut tampil "⚠" dan detail error dicatat ke laravel.log.
            $safe = fn (string $column, \Closure $callback) => function ($row) use ($column, $callback) {
                try {
                    return $callback($row);
                } catch (\Throwable $e) {
                    Log::error("Surat Terbit: kolom [{$column}] gagal untuk surat #{$row->id}", [
                        'generate_id' => $row->id,
                        'letter_number' => $row->getRawOriginal('letter_number'),
                        'letter_id' => $row->letter_id,
                        'user_id' => $row->user_id,
                        'site_id' => $row->site_id,
                        'error' => get_class($e) . ': ' . $e->getMessage(),
                        'at' => $e->getFile() . ':' . $e->getLine(),
                    ]);

                    return '⚠';
                }
            };

            $dataTable = DataTables::of($generates)
                ->addIndexColumn()
                ->addColumn('checkbox', $safe('checkbox', function ($row) {
                    return '<input type="checkbox" class="generate-checkbox" value="' . $row->id . '">';
                }))
                ->addColumn('no_surat', $safe('no_surat', function ($row) {
                    return e($row->formatted_letter_number ?? '-');
                }))
                ->addColumn('nik_karyawan', $safe('nik_karyawan', function ($row) {
                    return e($row->user->employee_nik ?? '-');
                }))
                ->addColumn('nama_perusahaan', $safe('nama_perusahaan', function ($row) {
                    $company = $row->user->site->company->name
                        ?? $row->site->company->name
                        ?? null;
                    return e($company ?: '-');
                }))
                ->addColumn('tgl_surat', $safe('tgl_surat', function ($row) {
                    return $row->created_at ? $row->created_at->translatedFormat('d') : '-';
                }))
                ->addColumn('bulan_surat', $safe('bulan_surat', function ($row) {
                    return $row->created_at ? $row->created_at->locale('id')->translatedFormat('F') : '-';
                }))
                ->addColumn('tahun_surat', $safe('tahun_surat', function ($row) {
                    return $row->created_at ? $row->created_at->translatedFormat('Y') : '-';
                }))
                ->addColumn('nama', $safe('nama', function ($row) {
                    return e($row->user->name ?? '-');
                }))
                ->addColumn('email', $safe('email', function ($row) {
                    return e($row->user->email ?? '-');
                }))
                ->addColumn('ibu_kandung', $safe('ibu_kandung', function ($row) {
                    return e($row->user->profile->mother_name ?? '-');
                }))
                ->addColumn('tempat_lahir', $safe('tempat_lahir', function ($row) {
                    return e($row->user->profile->birth_place ?? '-');
                }))
                ->addColumn('tanggal_lahir', $safe('tanggal_lahir', function ($row) {
                    return e(format_tanggal($row->user?->profile?->birth_date));
                }))
                ->addColumn('jenis_kelamin', $safe('jenis_kelamin', function ($row) {
                    return e($row->user->profile->gender ?? '-');
                }))
                ->addColumn('no_ktp', $safe('no_ktp', function ($row) {
                    return e($row->user->nik ?? '-');
                }))
                ->addColumn('no_kk', $safe('no_kk', function ($row) {
                    return e($row->user->profile->no_kk ?? '-');
                }))
                ->addColumn('no_npwp', $safe('no_npwp', function ($row) {
                    return e($row->user->profile->npwp_number ?? '-');
                }))
                ->addColumn('alamat_ktp', $safe('alamat_ktp', function ($row) {
                    $profile = $row->user?->profile; // surat bisa milik user yang sudah dihapus
                    $parts = array_filter([
                        $profile?->address,
                        $profile?->rt_rw,
                        $profile?->kelurahan ? 'Kel. ' . $profile->kelurahan : null,
                        $profile?->kecamatan ? 'Kec. ' . $profile->kecamatan : null,
                    ]);
                    return e(implode(', ', $parts) ?: '-');
                }))
                ->addColumn('no_handphone', $safe('no_handphone', function ($row) {
                    return e($row->user->phone ?? '-');
                }))
                ->addColumn('agama', $safe('agama', function ($row) {
                    return e($row->user->profile->religion ?? '-');
                }))
                ->addColumn('status_pernikahan', $safe('status_pernikahan', function ($row) {
                    return e($row->user->profile->marriage_status ?? '-');
                }))
                ->addColumn('jabatan', $safe('jabatan', function ($row) {
                    return e($row->user && $row->user->roles->isNotEmpty() ? $row->user->roles->pluck('name')->implode(', ') : '-');
                }))
                ->addColumn('site_project', $safe('site_project', function ($row) {
                    return e($row->user->site->name ?? $row->site->name ?? '-');
                }))
                ->addColumn('nama_client', $safe('nama_client', function ($row) {
                    return e($row->user->site->client_name ?? $row->site->client_name ?? '-');
                }))
                ->addColumn('jabatan_client', $safe('jabatan_client', function ($row) {
                    return e($row->user->site->client_position ?? $row->site->client_position ?? '-');
                }))
                ->addColumn('tgl_join', $safe('tgl_join', function ($row) {
                    $join = $row->user->profile->join_date ?? null;
                    return $join ? e(date('d', strtotime($join))) : '-';
                }))
                ->addColumn('bulan_join', $safe('bulan_join', function ($row) {
                    $join = $row->user->profile->join_date ?? null;
                    return $join ? e(\Illuminate\Support\Carbon::parse($join)->locale('id')->translatedFormat('F')) : '-';
                }))
                ->addColumn('tahun_join', $safe('tahun_join', function ($row) {
                    $join = $row->user->profile->join_date ?? null;
                    return $join ? e(date('Y', strtotime($join))) : '-';
                }))
                ->addColumn('nama_bank', $safe('nama_bank', function ($row) {
                    return e($row->user->profile->bank_name ?? '-');
                }))
                ->addColumn('nama_rekening', $safe('nama_rekening', function ($row) {
                    return e($row->user->profile->account_name ?? '-');
                }))
                ->addColumn('no_rekening', $safe('no_rekening', function ($row) {
                    return e($row->user->profile->account_number ?? '-');
                }))
                ->addColumn('signature', $safe('signature', function ($row) {
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
                }))
                ->addColumn('action', $safe('action', function ($row) {
                    return view('admin.generates.partials.actions', compact('row'))->render();
                }));

            foreach ($customVarColumns as $col) {
                $variable = $col['variable'];
                $dataTable->addColumn($col['key'], $safe($col['key'], function ($row) use ($variable) {
                    $value = $row->valueVariables
                        ->first(fn($cv) => $cv->customVariable && $cv->customVariable->variable === $variable);
                    return e($value->value ?? '-');
                }));
            }

            try {
                return $dataTable
                    ->rawColumns(['action', 'checkbox', 'signature'])
                    ->make(true);
            } catch (\Throwable $e) {
                // Error di luar kolom (query / filter): catat beserta filter yang sedang dipakai
                Log::error('Surat Terbit: tabel gagal dimuat', [
                    'error' => get_class($e) . ': ' . $e->getMessage(),
                    'at' => $e->getFile() . ':' . $e->getLine(),
                    'filters' => request()->only(['site_id', 'type_id', 'start_date', 'end_date']),
                    'search' => request('search')['value'] ?? null,
                    'start' => request('start'),
                    'length' => request('length'),
                ]);

                throw $e;
            }
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
    

    public function export(Request $request)
    {
        $startDate = $request->start_date ?: null;
        $endDate = $request->end_date ?: null;
        $siteId = $request->site_id ?: null;
        $typeId = $request->type_id ?: null;

        $range = '';
        if ($startDate && $endDate) {
            $range = ' - ' . date('d-m-Y', strtotime($startDate)) . ' sd ' . date('d-m-Y', strtotime($endDate));
        } elseif ($startDate) {
            $range = ' - sejak ' . date('d-m-Y', strtotime($startDate));
        } elseif ($endDate) {
            $range = ' - sampai ' . date('d-m-Y', strtotime($endDate));
        }

        $filename = 'Surat Terbit' . $range . ' - ' . date('d-m-Y') . '.xlsx';

        return Excel::download(
            new \App\Exports\GeneratesExport($startDate, $endDate, $siteId, $typeId),
            $filename
        );
    }

    public function bulkApprove(Request $request)
    {
        // Tanda tangan diambil dari profil admin yang login (bukan dari input form,
        // yang bisa dimanipulasi untuk menyisipkan HTML ke surat).
        $esign = Auth::user()->profile?->esign;
        $ids = $request->input('ids');

        if (empty($esign)) {
            return redirect()->back()->with('error', 'Tanda tangan digital Anda belum ada.');
        }

        $esign = $this->convertEsignToBase64($esign);
        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,[A-Za-z0-9+\/]+={0,2}$/', $esign)) {
            return redirect()->back()->with('error', 'Format tanda tangan digital Anda tidak valid. Buat ulang tanda tangan Anda.');
        }

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

        // Tanda tangan dari aplikasi mobile: JSON {"esign_url", "esign_public_id"} -> file di storage.
        $json = json_decode($esign, true);
        if (is_array($json) && !empty($json['esign_public_id'])) {
            try {
                $bytes = \App\Services\FileStorage::disk()->get($json['esign_public_id']);
                $info = $bytes ? @getimagesizefromstring($bytes) : false;
                if ($info && in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'], true)) {
                    return 'data:' . $info['mime'] . ';base64,' . base64_encode($bytes);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal membaca file tanda tangan: ' . $e->getMessage());
            }
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
            'file'      => 'required|file|mimes:xlsx,xls,csv,zip,txt|extensions:xlsx,xls,csv',
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

        if ($import->getTotalRows() === 0) {
            return redirect()->route('generates.index')
                ->with('error', 'File tidak berisi data. Pastikan file diisi minimal 1 baris di bawah header, atau jangan centang "Template kosong" saat export.');
        }

        $message = "Import selesai. {$import->getCreated()} surat terbit dibuat.";
        if ($import->getSalaryUpdated() > 0) {
            $message .= " {$import->getSalaryUpdated()} pengaturan gaji karyawan diperbarui.";
        }
        if ($import->getEmptyNikRows() > 0) {
            $message .= " {$import->getEmptyNikRows()} baris dilewati (kolom NIK Karyawan kosong).";
        }
        if ($import->getSkipped() > 0) {
            $niks = implode(', ', array_slice($import->getSkippedNiks(), 0, 10));
            $more = $import->getSkipped() > 10 ? ' ...' : '';
            $message .= " {$import->getSkipped()} baris dilewati (NIK karyawan tidak ditemukan: {$niks}{$more}).";
        }

        $flash = $import->getCreated() > 0 ? 'success' : 'warning';
        return redirect()->route('generates.index')->with($flash, $message);
    }

    public function show(Generate $generate)
    {
        $no_surat = $generate->formatted_letter_number ?? '';
        
        $tgl_surat = isset($generate->created_at) 
            ? Carbon::parse($generate->created_at)->format('d-m-Y') 
            : '';
            
        $romawi = $generate->romawi ?? '';
        $tahun = $generate->year ?? '';
        $hari = $generate->day ?? '';
        $pihak_2 = $generate->second_party ?? '';
        $sign_2 = $generate->second_party_esign ?? '';
        $nama_karyawan = strtoupper($generate->user->name ?? '');
        
        $ttl = isset($generate->user->profile->birth_place) && isset($generate->user->profile->birth_date)
            ? $generate->user->profile->birth_place . ', ' . Carbon::parse($generate->user->profile->birth_date)->format('d-m-Y')
            : '';
            
        $alamat = $generate->user->profile->address ?? '';
        $handphone = $generate->user->phone ?? '';
        $no_karyawan = $generate->user->employee_nik ?? '';
        $area = strtoupper($generate->user->site->name ?? '');
        $area_project = $generate->user->site->area ?? '';
        $nik_ktp = $generate->user->nik ?? '';
        $jenis_kelamin = $generate->user->profile->gender ?? '';
        $nama_client = $generate->user->site->client_name ?? '';
        $jabatan_client = $generate->user->site->client_position ?? '';
        $jabatan = strtoupper($generate->user->roles->first()->name ?? '');
        $esign = $generate->esign ?? '';
        $nama_kontak = $generate->emergency_name ?? '';
        $no_kontak = $generate->emergency_number ?? '';
        $alamat_kontak = $generate->emergency_address ?? '';
        $hubungan = $generate->relationship ?? '';
        
        $gaji_type = $generate->gaji_type ?? 'monthly'; 
        if ($gaji_type === 'monthly') {
            $gaji = $generate->user->payroll->salary_amount ?? 0;
        } elseif ($gaji_type === 'daily') {
            $gaji = $generate->user->payroll->daily_rate ?? 0;
        } else {
            $gaji = 0;
        }

        $tunjangan_calculation = 0;
        $tunjangan = '';
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
        $komisi = '';
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
        $potongan = '';
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
            ? Carbon::parse($generate->start_date)->format('d-m-Y') 
            : '';

        $selesai = isset($generate->end_date) 
            ? Carbon::parse($generate->end_date)->format('d-m-Y') 
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

        $generate->letter->description = str_replace($search, \App\Support\LetterPlaceholders::forHtml($replace), $generate->letter->description);

        return view('admin.generates.show', compact('generate'));
    }

    private function _buildSearchReplace(Generate $generate): array
    {
        $no_surat = $generate->formatted_letter_number ?? '';
        $tgl_surat = isset($generate->created_at) ? Carbon::parse($generate->created_at)->format('d-m-Y') : '';
        $romawi = $generate->romawi ?? '';
        $tahun = $generate->year ?? '';
        $hari = $generate->day ?? '';
        $pihak_2 = $generate->second_party ?? '';
        $sign_2 = $generate->second_party_esign ?? '';
        $nama_karyawan = strtoupper($generate->user->name ?? '');
        $ttl = isset($generate->user->profile->birth_place) && isset($generate->user->profile->birth_date)
            ? $generate->user->profile->birth_place . ', ' . Carbon::parse($generate->user->profile->birth_date)->format('d-m-Y')
            : '';
        $alamat = $generate->user->profile->address ?? '';
        $handphone = $generate->user->phone ?? '';
        $no_karyawan = $generate->user->employee_nik ?? '';
        $area = strtoupper($generate->user->site->name ?? '');
        $area_project = $generate->user->site->area ?? '';
        $nik_ktp = $generate->user->nik ?? '';
        $jenis_kelamin = $generate->user->profile->gender ?? '';
        $nama_client = $generate->user->site->client_name ?? '';
        $jabatan_client = $generate->user->site->client_position ?? '';
        $jabatan = strtoupper($generate->user->roles->first()->name ?? '');
        $esign = $generate->esign ?? '';
        $nama_kontak = $generate->emergency_name ?? '';
        $no_kontak = $generate->emergency_number ?? '';
        $alamat_kontak = $generate->emergency_address ?? '';
        $hubungan = $generate->relationship ?? '';

        $gaji_type = $generate->gaji_type ?? 'monthly';
        if ($gaji_type === 'monthly') $gaji = $generate->user->payroll->salary_amount ?? 0;
        elseif ($gaji_type === 'daily') $gaji = $generate->user->payroll->daily_rate ?? 0;
        else $gaji = 0;

        $tunjangan_calculation = 0; $tunjangan = '';
        $komisi_calculation = 0; $komisi = '';
        $potongan_calculation = 0; $potongan = '';

        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                $amt = $component->amount ? $component->amount : ($component->percentage ? ($gaji * $component->percentage / 100) : 0);
                $tunjangan_calculation += $amt; $tunjangan = $component->name . ' = ' . $tunjangan_calculation;
                if ($component->component_type === 'comission') { $komisi_calculation += $amt; $komisi = $component->name . ' = ' . $komisi_calculation; }
                if ($component->component_type === 'deduction') { $potongan_calculation += $amt; $potongan = $component->name . ' = ' . $potongan_calculation; }
            }
        }

        $mulai = isset($generate->start_date) ? Carbon::parse($generate->start_date)->format('d-m-Y') : '';
        $selesai = isset($generate->end_date) ? Carbon::parse($generate->end_date)->format('d-m-Y') : 'Sampai dengan Selesai';

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
                    $descriptionReplaced = str_replace($search, \App\Support\LetterPlaceholders::forJson($replace), $description);
                    $parsedReplaced = json_decode($descriptionReplaced, true);
                    $pages = $parsedReplaced['pages'] ?? [];
                }
            } catch (\Exception $e) {}
        }

        if (!$isFabric) {
            $description = str_replace($search, \App\Support\LetterPlaceholders::forHtml($replace), $description);
        }

        return view('admin.letters.print', compact('pages', 'isFabric', 'title', 'description'));
    }

    public function pdf(Generate $generate)
    {
        $no_surat = $generate->formatted_letter_number ?? '';
        $tgl_surat = isset($generate->created_at) ? Carbon::parse($generate->created_at)->format('d-m-Y') : '';
        $romawi = $generate->romawi ?? '';
        $tahun = $generate->year ?? '';
        $hari = $generate->day ?? '';
        $pihak_2 = $generate->second_party ?? '';
        $sign_2 = $generate->second_party_esign ?? '';
        $nama_karyawan = strtoupper($generate->user->name ?? '');
        $ttl = isset($generate->user->profile->birth_place) && isset($generate->user->profile->birth_date)
            ? $generate->user->profile->birth_place . ', ' . Carbon::parse($generate->user->profile->birth_date)->format('d-m-Y')
            : '';
        $alamat = $generate->user->profile->address ?? '';
        $handphone = $generate->user->phone ?? '';
        $no_karyawan = $generate->user->employee_nik ?? '';
        $area = strtoupper($generate->user->site->name ?? '');
        $area_project = $generate->user->site->area ?? '';
        $nik_ktp = $generate->user->nik ?? '';
        $jenis_kelamin = $generate->user->profile->gender ?? '';
        $nama_client = $generate->user->site->client_name ?? '';
        $jabatan_client = $generate->user->site->client_position ?? '';
        $jabatan = strtoupper($generate->user->roles->first()->name ?? '');
        $esign = $generate->esign ?? '';
        $nama_kontak = $generate->emergency_name ?? '';
        $no_kontak = $generate->emergency_number ?? '';
        $alamat_kontak = $generate->emergency_address ?? '';
        $hubungan = $generate->relationship ?? '';

        $gaji_type = $generate->gaji_type ?? 'monthly';
        if ($gaji_type === 'monthly') {
            $gaji = $generate->user->payroll->salary_amount ?? 0;
        } elseif ($gaji_type === 'daily') {
            $gaji = $generate->user->payroll->daily_rate ?? 0;
        } else {
            $gaji = 0;
        }

        $tunjangan_calculation = 0;
        $tunjangan = '';
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
        $komisi = '';
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
        $potongan = '';
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

        $mulai = isset($generate->start_date) ? Carbon::parse($generate->start_date)->format('d-m-Y') : '';
        $selesai = isset($generate->end_date) ? Carbon::parse($generate->end_date)->format('d-m-Y') : 'Sampai dengan Selesai';

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
                    $descriptionReplaced = str_replace($search, \App\Support\LetterPlaceholders::forJson($replace), $description);
                    $parsedReplaced = json_decode($descriptionReplaced, true);
                    $pages = $this->savePageImages($parsedReplaced['pages'] ?? []);
                }
            } catch (\Exception $e) {}
        }

        if (!$isFabric) {
            $description = str_replace($search, \App\Support\LetterPlaceholders::forHtml($replace), $description);
        }

        $title = $generate->letter->title ?? 'surat';

        $pdf = Pdf::loadView('admin.letters.pdf', compact('pages', 'isFabric', 'description', 'title'))
            ->setPaper([0, 0, 794, 1123], 'portrait');

        $response = $pdf->stream($title . '.pdf');
        $this->cleanPageImages($pages);
        return $response;
    }
}
