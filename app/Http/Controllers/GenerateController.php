<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\Letter;
use App\Models\Generate;
use App\Models\TypeLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class GenerateController extends Controller
{
    public function index()
    {
        $types = TypeLetter::with('letters')->get();
        $letters = Letter::all();
        $sites = Site::with('company')->get();
        
        // Get filter values
        $filters = [
            'site_id' => request('site_id'),
            'type_id' => request('type_id'),
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
        ];
    
        if (request()->ajax()) {
            $generates = Generate::with('letter.type', 'site', 'user') 
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
                ->when(request('type_id'), function ($query) {
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
    
            return DataTables::of($generates)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="generate-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('template', function ($row) {
                    return $row->letter->title . '<br>' . ($row->letter->type ? $row->letter->type->name : 'No Type');
                })
                ->addColumn('name', function ($row) {
                    return $row->user->name . '<br>' . $row->user->employee_nik;
                })
                ->addColumn('signature', function ($row) {
                    $employeeStatus = $row->second_party_esign === null ? '<span class="badge bg-danger">Belum Tertanda Tangan</span>' : '<span class="badge bg-success">Sudah Tertanda Tangan</span>';
                    $hrdStatus = $row->esign === null ? '<span class="badge bg-danger">Belum Tertanda Tangan</span>' : '<span class="badge bg-success">Sudah Tertanda Tangan</span>';
                    return '
                    <div class="row">
                        <div class="col-4">Employee</div>
                        <div class="col-8">: ' . $employeeStatus . '</div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-4">HRD</div>
                        <div class="col-8">: ' . $hrdStatus . '</div>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return view('generates.partials.actions', compact('row'))->render();
                })
                ->rawColumns(['action', 'checkbox', 'signature', 'template', 'name'])
                ->make(true);
        }
    
        // Pass filters to the view
        return view('generates.index', compact('letters', 'sites', 'types', 'filters'));
    }
    

    public function bulkApprove(Request $request)
    {
        $esign = $request->esign;
        $ids = $request->input('ids');
    
        if (empty($esign)) {
            return redirect()->back()->with('error', 'Tanda tangan digital Anda belum ada.');
        }


        $ids = explode(',', $ids);
    
        $updated = Generate::whereIn('id', $ids)
                            ->update(['second_party_esign' => $esign]);
    
        if ($updated) {
            return redirect()->back()->with('success', 'Tanda tangan berhasil disalin ke surat yang dipilih.');
        } else {
            return redirect()->back()->with('error', 'Tidak ada data yang diperbarui.');
        }
    }
    

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }
    
        $ids = explode(',', $ids);
    
        $deleted = Generate::whereIn('id', $ids)->delete();
    
        if ($deleted) {
            return redirect()->back()->with('success', 'Data yang dipilih berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Tidak ada data yang dihapus.');
        }
    }

    public function show(Generate $generate)
    {
        $no_surat = $generate->letter_number ?? 'belum ada no surat';
        $romawi = $generate->romawi ?? 'belum ada data';
        $tahun = $generate->year ?? 'belum ada tahun';
        $hari = $generate->day ?? 'belum ada hari';
        $pihak_2 = $generate->second_party ?? 'belum ada data';
        $sign_2 = $generate->second_party_esign ?? 'belum ada data';
        $nama_karyawan = $generate->user->name ?? 'belum ada nama';
        $ttl = $generate->user->profile->birth_place . ', ' . Carbon::parse($generate->user->profile->birth_date)->format('d-m-Y') ?? 'belum ada data';
        $alamat = $generate->user->profile->address ?? 'belum ada alamat';
        $handphone = $generate->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $generate->user->employee_nik ?? 'belum ada no karyawan';
        $area = $generate->site->name ?? 'belum ada area';
        $jabatan = $generate->jabatan ?? 'belum ada jabatan';
        $esign = $generate->esign ?? 'belum ada tanda tangan';
        $nama_kontak = $generate->emergency_name ?? 'belum ada nama';
        $no_kontak = $generate->emergency_number ?? 'belum ada no hp';
        $alamat_kontak = $generate->emergency_address ?? 'belum ada alamat';
        $hubungan = $generate->relationship ?? 'belum ada hubungan';
        
        
        $gaji_type = $generate->gaji_type ?? 'monthly'; 
        if ($gaji_type === 'monthly') {
            $gaji = $generate->user->payroll->salary_amount ?? 'belum ada gaji';
        } elseif ($gaji_type === 'daily') {
            $gaji = $generate->user->payroll->daily_rate ?? 'belum ada gaji';
        } else {
            $gaji = 'tipe gaji tidak valid';
        }

        $tunjangan = 'Tidak ada data';
        $tunjangan_calculation = 0;
        if ($generate->user->payroll && $generate->user->payroll->payroll_components) {
            foreach ($generate->user->payroll->payroll_components as $component) {
                if ($component->component_type === 'allowance') {
                    if ($component->amount) {
                        $tunjangan_calculation += $component->amount;
                        $tunjangan = $component->name . ' = ' . $tunjangan_calculation;

                    } elseif ($component->percentage) {
                        $tunjangan_calculation += ($gaji * $component->percentage) / 100;
                        $tunjangan = $component->name . ' = ' . $tunjangan_calculation;
                    }
                }
            }
        }

        $komisi = 'Tidak ada data';
        $komisi_calculation = 0;
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

        $potongan = 'Tidak ada data';
        $potongan_calculation = 0;
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
        
        $mulai = Carbon::parse($generate->join_date)->format('d-m-Y') ?? 'belum ada data';

        $selesai = Carbon::parse($generate->end_date)->format('d-m-Y') ?? 'belum ada data';
    
        $generate->letter->description = str_replace(
            [
                '[no_surat]', 
                '[romawi]', 
                '[tahun]',
                '[hari]',
                '[mulai]',
                '[selesai]',
                '[pihak_2]',
                '[sign_2]',
                '[nama_karyawan]',
                '[ttl]',
                '[alamat]',
                '[handphone]',
                '[no_karyawan]',
                '[area]',
                '[jabatan]',
                '[esign]',
                '[gaji]',
                '[tunjangan]',
                '[komisi]',
                '[potongan]',
                '[nama_kontak]',
                '[no_kontak]',
                '[alamat_kontak]',
                '[hubungan]'
            ],
            [
                $no_surat, 
                $romawi, 
                $tahun,
                $hari,
                $mulai,
                $selesai,
                $pihak_2,
                $sign_2,
                $nama_karyawan,
                $ttl,
                $alamat,
                $handphone,
                $no_karyawan,
                $area,
                $jabatan,
                $esign,
                $gaji,
                $tunjangan,
                $komisi,
                $potongan,
                $nama_kontak,
                $no_kontak,
                $alamat_kontak,
                $hubungan
            ],
            $generate->letter->description
        );
    
        return view('generates.show', compact('generate'));
    }
}
