<?php

namespace App\Http\Controllers\Applicant;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Floor;
use App\Models\Career;
use App\Models\Letter;
use App\Models\Document;
use App\Models\Generate;
use App\Models\Applicant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class DataController extends Controller
{
    use \App\Traits\PdfPageImageTrait;
    public function registrationForm()
    {
        return view('website.registration-form');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $timelines = Applicant::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        // 1. Cek Kelengkapan Field Profil
        $requiredFields = [
            'avatar_url' => 'Pas Foto', // Sesuaikan dengan nama kolom di DB Anda
            'gender' => 'Jenis Kelamin',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'mother_name' => 'Nama Ibu Kandung',
            'last_education' => 'Pendidikan Terakhir',
            'marriage_status' => 'Status Pernikahan',
            'living_with' => 'Status Tempat Tinggal',
            'height' => 'Tinggi Badan',
            'weight' => 'Berat Badan',
            'eye_condition' => 'Kondisi Mata',
            'hearing' => 'Pendengaran',
            'address' => 'Alamat KTP',
            'current_address' => 'Alamat Domisili',
            'family_name' => 'Nama Kontak Darurat',
            'family_relation' => 'Hubungan Kontak Darurat',
            'family_phone' => 'No. Telp Kontak Darurat',
        ];

        $missingProfileFields = [];
        if (!$user->profile) {
            $missingProfileFields = array_values($requiredFields);
        } else {
            foreach ($requiredFields as $field => $label) {
                if (empty($user->profile->$field)) {
                    $missingProfileFields[] = $label;
                }
            }
        }

        // 2. Cek Kelengkapan Dokumen
        $requiredDocs = ['KTP', 'IJAZAH', 'KARTU KELUARGA'];
        if ($user->profile?->gada_pratama === 'yes') $requiredDocs[] = 'GADA PRATAMA';
        if ($user->profile?->gada_madya === 'yes') $requiredDocs[] = 'GADA MADYA';
        if ($user->profile?->gada_utama === 'yes') $requiredDocs[] = 'GADA UTAMA';

        // Ambil dokumen yang sudah diupload
        $uploadedDocs = Document::where('user_id', $user->id)->pluck('name')->toArray();
        $missingDocuments = array_diff($requiredDocs, $uploadedDocs);

        // 3. Status Akhir
        $showWarning = !empty($missingProfileFields) || !empty($missingDocuments);

        return view('website.dashboard', compact(
            'timelines', 
            'showWarning', 
            'missingProfileFields', 
            'missingDocuments'
        ));
    }

    public function faq()
    {
        return view('website.faq');
    }

    public function history()
    {
        $user = Auth::user();
        
        $applicants = Applicant::with(['career', 'status'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->unique('career_id'); 

        return view('website.history', compact('applicants'));
    }

    public function letter()
    {
        $user = Auth::user();

        $histories = Generate::where('user_id', $user->id)
            ->with(['letter.type', 'site'])
            ->where(function ($query) {
                // Tampilkan surat jika:
                // 1. Tipenya BUKAN PKWT
                $query->whereHas('letter.type', function ($q) {
                    $q->where('name', '!=', 'PKWT');
                })
                // 2. ATAU Tipenya PKWT tapi second_party_esign MASIH KOSONG
                ->orWhere(function ($q) {
                    $q->whereHas('letter.type', function ($subQ) {
                        $subQ->where('name', 'PKWT');
                    })
                    ->where(function($esignQuery) {
                        $esignQuery->whereNull('second_party_esign')
                                ->orWhere('second_party_esign', '');
                    });
                });
            })
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return view('website.letters.index', compact('histories'));
    }

    public function letterDetail($id)
    {
        $user = Auth::user();

        $eletter = Generate::where('id', $id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$eletter) {
            return redirect()->back()->with('error', 'Belum ada surat digital untuk Anda.');
        }

        $no_surat = $eletter->letter_number ?? 'belum ada no surat';
        $tgl_surat = isset($eletter->created_at) 
            ? Carbon::parse($eletter->created_at)->locale('id')->translatedFormat('j F Y') 
            : '';
        $romawi = $eletter->romawi ?? 'belum ada data';
        $tahun = $eletter->year ?? 'belum ada tahun';
        $hari = $eletter->day ?? 'belum ada hari';
        $pihak_2 = $eletter->second_party ?? 'belum ada data';
        $sign_2 = $eletter->second_party_esign ?? 'belum ada data';
        $nama_karyawan = strtoupper($eletter->user->name ?? 'belum ada nama');
        $nik_ktp = $eletter->user->nik ?? 'belum ada NIK KTP';
        $jenis_kelamin = $eletter->user->profile->gender ?? 'belum ada Jenis Kelamin';
        $ttl = isset($eletter->user->profile->birth_place) && isset($eletter->user->profile->birth_date)
            ? $eletter->user->profile->birth_place . ', ' . Carbon::parse($eletter->user->profile->birth_date)->format('d-m-Y')
            : 'belum ada data';
        $alamat = $eletter->user->profile->address ?? 'belum ada alamat';
        $handphone = $eletter->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $eletter->user->employee_nik ?? 'belum ada no karyawan';
        $lokasi_project = $eletter->site->name ?? 'belum ada area';
        $nama_client = $eletter->site->client_name ?? 'belum ada area';
        $jabatan_client = $eletter->site->client_position ?? 'belum ada jabatan client';
        $jabatan = strtoupper($eletter->user->roles->first()->name ?? 'belum ada jabatan');
        $esign = $eletter->esign ?? 'belum ada tanda tangan';
        
        $payroll = $user->payroll;
        $gaji_raw = 0;
        $gaji_label = "";

        if ($payroll) {
            $gaji_raw = $payroll->amount ?? 0;
            $gaji_label = ($payroll->pay_type === 'monthly') ? " / Bulan" : (($payroll->pay_type === 'daily') ? " / Hari" : "");
        }

        $gaji = ($gaji_raw > 0) ? 'Rp ' . number_format($gaji_raw, 0, ',', '.') . $gaji_label : 'Sesuai Kebijakan Perusahaan';

        $tunjangan_items = [];
        
        if ($eletter->user->payroll && $eletter->user->payroll->payroll_components) {
            foreach ($eletter->user->payroll->payroll_components as $component) {
                $nominal = $component->amount;
                $nama_komponen = $component->component_type->name ?? 'Tunjangan';
                
                $tunjangan_items[] = '- ' . $nama_komponen . ' : Rp ' . number_format($nominal, 0, ',', '.');
            }
        }

        $tunjangan = !empty($tunjangan_items) ? implode("<br>", $tunjangan_items) : '-';
        
        $mulai = isset($eletter->start_date)
            ? Carbon::parse($eletter->start_date)->format('d-m-Y')
            : 'belum ada data';
        $selesai = isset($eletter->end_date)
            ? Carbon::parse($eletter->end_date)->format('d-m-Y')
            : 'belum ada data';

        $search = [
            '[no_surat]', '[tgl_surat]', '[romawi]', '[tahun]', '[hari]', '[mulai]', '[selesai]',
            '[pihak_2]', '[sign_2]', '[nama_karyawan]', '[jenis_kelamin]', '[nik_ktp]', '[ttl]', 
            '[alamat]', '[handphone]', '[no_karyawan]', '[lokasi_project]', '[nama_client]', '[jabatan_client]', '[jabatan]', '[esign]', 
            '[gaji]', '[tunjangan]'
        ];

        $replace = [
            $no_surat, $tgl_surat, $romawi, $tahun, $hari, $mulai, $selesai,
            $pihak_2, $sign_2, $nama_karyawan, $jenis_kelamin, $nik_ktp, $ttl, 
            $alamat, $handphone, $no_karyawan, $lokasi_project, $nama_client, $jabatan_client, $jabatan, $esign, 
            $gaji, $tunjangan
        ];

        $customValues = \App\Models\ValueVariable::where('generate_id', $eletter->id)
            ->with('customVariable')
            ->get();

        foreach ($customValues as $cv) {
            if ($cv->customVariable) {
                $search[] = '[' . $cv->customVariable->variable . ']';
                $replace[] = $cv->value;
            }
        }

        $eletter->letter->description = str_replace($search, $replace, $eletter->letter->description);

        return view('website.letters.show', compact('eletter'));
    }

    public function letterPdf($id)
    {
        $eletter = Generate::where('id', $id)->first();

        if (!$eletter) {
            abort(404);
        }

        $user = $eletter->user;

        $no_surat = $eletter->letter_number ?? 'belum ada no surat';
        $tgl_surat = isset($eletter->created_at) ? Carbon::parse($eletter->created_at)->locale('id')->translatedFormat('j F Y') : '';
        $romawi = $eletter->romawi ?? 'belum ada data';
        $tahun = $eletter->year ?? 'belum ada tahun';
        $hari = $eletter->day ?? 'belum ada hari';
        $pihak_2 = $eletter->second_party ?? 'belum ada data';
        $sign_2 = $eletter->second_party_esign ?? 'belum ada data';
        $nama_karyawan = strtoupper($eletter->user->name ?? 'belum ada nama');
        $nik_ktp = $eletter->user->nik ?? 'belum ada NIK KTP';
        $jenis_kelamin = $eletter->user->profile->gender ?? 'belum ada Jenis Kelamin';
        $ttl = isset($eletter->user->profile->birth_place) && isset($eletter->user->profile->birth_date)
            ? $eletter->user->profile->birth_place . ', ' . Carbon::parse($eletter->user->profile->birth_date)->format('d-m-Y')
            : 'belum ada data';
        $alamat = $eletter->user->profile->address ?? 'belum ada alamat';
        $handphone = $eletter->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $eletter->user->employee_nik ?? 'belum ada no karyawan';
        $lokasi_project = $eletter->site->name ?? 'belum ada area';
        $nama_client = $eletter->site->client_name ?? 'belum ada area';
        $jabatan_client = $eletter->site->client_position ?? 'belum ada jabatan client';
        $jabatan = strtoupper($eletter->user->roles->first()->name ?? 'belum ada jabatan');
        $esign = $eletter->esign ?? 'belum ada tanda tangan';

        $payroll = $user->payroll;
        $gaji_raw = 0;
        $gaji_label = '';
        if ($payroll) {
            $gaji_raw = $payroll->amount ?? 0;
            $gaji_label = ($payroll->pay_type === 'monthly') ? ' / Bulan' : (($payroll->pay_type === 'daily') ? ' / Hari' : '');
        }
        $gaji = ($gaji_raw > 0) ? 'Rp ' . number_format($gaji_raw, 0, ',', '.') . $gaji_label : 'Sesuai Kebijakan Perusahaan';

        $tunjangan_items = [];
        if ($eletter->user->payroll && $eletter->user->payroll->payroll_components) {
            foreach ($eletter->user->payroll->payroll_components as $component) {
                $nominal = $component->amount;
                $nama_komponen = $component->component_type->name ?? 'Tunjangan';
                $tunjangan_items[] = '- ' . $nama_komponen . ' : Rp ' . number_format($nominal, 0, ',', '.');
            }
        }
        $tunjangan = !empty($tunjangan_items) ? implode("\n", $tunjangan_items) : '-';

        $mulai = isset($eletter->start_date) ? Carbon::parse($eletter->start_date)->format('d-m-Y') : 'belum ada data';
        $selesai = isset($eletter->end_date) ? Carbon::parse($eletter->end_date)->format('d-m-Y') : 'belum ada data';

        $search = [
            '[no_surat]', '[tgl_surat]', '[romawi]', '[tahun]', '[hari]', '[mulai]', '[selesai]',
            '[pihak_2]', '[sign_2]', '[nama_karyawan]', '[jenis_kelamin]', '[nik_ktp]', '[ttl]',
            '[alamat]', '[handphone]', '[no_karyawan]', '[lokasi_project]', '[nama_client]',
            '[jabatan_client]', '[jabatan]', '[esign]', '[gaji]', '[tunjangan]'
        ];

        $replace = [
            $no_surat, $tgl_surat, $romawi, $tahun, $hari, $mulai, $selesai,
            $pihak_2, $sign_2, $nama_karyawan, $jenis_kelamin, $nik_ktp, $ttl,
            $alamat, $handphone, $no_karyawan, $lokasi_project, $nama_client,
            $jabatan_client, $jabatan, $esign, $gaji, $tunjangan
        ];

        $customValues = \App\Models\ValueVariable::where('generate_id', $eletter->id)
            ->with('customVariable')->get();

        foreach ($customValues as $cv) {
            if ($cv->customVariable) {
                $search[] = '[' . $cv->customVariable->variable . ']';
                $replace[] = $cv->value;
            }
        }

        $description = $eletter->letter->description ?? '';
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

        $title = $eletter->letter->title ?? 'surat';

        $pdf = Pdf::loadView('admin.letters.pdf', compact('pages', 'isFabric', 'description', 'title'))
            ->setPaper([0, 0, 794, 1123], 'portrait');

        $response = $pdf->stream($title . '.pdf');
        $this->cleanPageImages($pages);
        return $response;
    }

    public function letterPrint($id)
    {
        $eletter = Generate::where('id', $id)->first();
        if (!$eletter) abort(404);

        $user = $eletter->user;
        $no_surat = $eletter->letter_number ?? 'belum ada no surat';
        $tgl_surat = isset($eletter->created_at) ? Carbon::parse($eletter->created_at)->locale('id')->translatedFormat('j F Y') : '';
        $romawi = $eletter->romawi ?? 'belum ada data';
        $tahun = $eletter->year ?? 'belum ada tahun';
        $hari = $eletter->day ?? 'belum ada hari';
        $pihak_2 = $eletter->second_party ?? 'belum ada data';
        $sign_2 = $eletter->second_party_esign ?? 'belum ada data';
        $nama_karyawan = strtoupper($eletter->user->name ?? 'belum ada nama');
        $nik_ktp = $eletter->user->nik ?? 'belum ada NIK KTP';
        $jenis_kelamin = $eletter->user->profile->gender ?? 'belum ada Jenis Kelamin';
        $ttl = isset($eletter->user->profile->birth_place) && isset($eletter->user->profile->birth_date)
            ? $eletter->user->profile->birth_place . ', ' . Carbon::parse($eletter->user->profile->birth_date)->format('d-m-Y')
            : 'belum ada data';
        $alamat = $eletter->user->profile->address ?? 'belum ada alamat';
        $handphone = $eletter->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $eletter->user->employee_nik ?? 'belum ada no karyawan';
        $lokasi_project = $eletter->site->name ?? 'belum ada area';
        $nama_client = $eletter->site->client_name ?? 'belum ada area';
        $jabatan_client = $eletter->site->client_position ?? 'belum ada jabatan client';
        $jabatan = strtoupper($eletter->user->roles->first()->name ?? 'belum ada jabatan');
        $esign = $eletter->esign ?? 'belum ada tanda tangan';

        $payroll = $user->payroll;
        $gaji_raw = 0; $gaji_label = '';
        if ($payroll) {
            $gaji_raw = $payroll->amount ?? 0;
            $gaji_label = ($payroll->pay_type === 'monthly') ? ' / Bulan' : (($payroll->pay_type === 'daily') ? ' / Hari' : '');
        }
        $gaji = ($gaji_raw > 0) ? 'Rp ' . number_format($gaji_raw, 0, ',', '.') . $gaji_label : 'Sesuai Kebijakan Perusahaan';

        $tunjangan_items = [];
        if ($eletter->user->payroll && $eletter->user->payroll->payroll_components) {
            foreach ($eletter->user->payroll->payroll_components as $component) {
                $nominal = $component->amount;
                $nama_komponen = $component->component_type->name ?? 'Tunjangan';
                $tunjangan_items[] = '- ' . $nama_komponen . ' : Rp ' . number_format($nominal, 0, ',', '.');
            }
        }
        $tunjangan = !empty($tunjangan_items) ? implode("\n", $tunjangan_items) : '-';

        $mulai = isset($eletter->start_date) ? Carbon::parse($eletter->start_date)->format('d-m-Y') : 'belum ada data';
        $selesai = isset($eletter->end_date) ? Carbon::parse($eletter->end_date)->format('d-m-Y') : 'belum ada data';

        $search = ['[no_surat]','[tgl_surat]','[romawi]','[tahun]','[hari]','[mulai]','[selesai]','[pihak_2]','[sign_2]','[nama_karyawan]','[jenis_kelamin]','[nik_ktp]','[ttl]','[alamat]','[handphone]','[no_karyawan]','[lokasi_project]','[nama_client]','[jabatan_client]','[jabatan]','[esign]','[gaji]','[tunjangan]'];
        $replace = [$no_surat,$tgl_surat,$romawi,$tahun,$hari,$mulai,$selesai,$pihak_2,$sign_2,$nama_karyawan,$jenis_kelamin,$nik_ktp,$ttl,$alamat,$handphone,$no_karyawan,$lokasi_project,$nama_client,$jabatan_client,$jabatan,$esign,$gaji,$tunjangan];

        $customValues = \App\Models\ValueVariable::where('generate_id', $eletter->id)->with('customVariable')->get();
        foreach ($customValues as $cv) {
            if ($cv->customVariable) { $search[] = '[' . $cv->customVariable->variable . ']'; $replace[] = $cv->value; }
        }

        $description = $eletter->letter->description ?? '';
        $pages = [];
        $isFabric = false;
        $title = $eletter->letter->title ?? 'surat';

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

    public function sign(Request $request, $id)
    {
        $request->validate([
            'second_party_esign' => 'required|string',
        ]);

        $signatureData = $request->input('second_party_esign');

        // 1. Cek apakah formatnya PNG (sesuai dengan JS toDataURL)
        if (!$signatureData || strpos($signatureData, 'data:image/png;base64,') === false) {
            return back()->with('error', 'Data tanda tangan tidak valid (Harus format PNG).');
        }

        // 2. Hapus prefix 'data:image/png;base64,'
        $base64Data = str_replace('data:image/png;base64,', '', $signatureData);
        $base64Data = str_replace(' ', '+', $base64Data); // Opsional: jaga-jaga jika ada spasi dalam transmisi

        // 3. Decode
        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            return back()->with('error', 'Gagal mengonversi data tanda tangan.');
        }

        $generate = Generate::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        try {
            $generate->update([
                'second_party_esign' => $signatureData,
            ]);
            return redirect()->route('web.applicants.dashboard')->with('success', 'Berhasil!');
        } catch (\Exception $e) {
            // Tampilkan pesan error asli untuk debugging
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $query = Career::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('date') && $request->date != '') {
            $query->whereDate('created_at', '>=', $request->date);
        }

        $careers = $query->addSelect([
            'applicants_count' => Applicant::selectRaw('count(distinct user_id)')
                ->whereColumn('career_id', 'careers.id')
        ])->get();

        return view('website.careers.index', compact('careers'));
    }

    public function detail($slug)
    {
        $career  = Career::where('slug', $slug)->firstOrFail();
        return view('website.careers.detail', compact('career'));
    }

    public function apply($slug)
    {
        $user = Auth::user();

        // Key adalah nama kolom di database, Value adalah label untuk tampilan error
        $requiredFields = [
            'avatar_url' => 'Pas Foto',
            'gender' => 'Jenis Kelamin',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'mother_name' => 'Nama Ibu Kandung',
            'last_education' => 'Pendidikan Terakhir',
            'marriage_status' => 'Status Pernikahan',
            'living_with' => 'Status Tempat Tinggal',
            'height' => 'Tinggi Badan',
            'weight' => 'Berat Badan',
            'eye_condition' => 'Kondisi Mata',
            'hearing' => 'Pendengaran',
            'address' => 'Alamat KTP',
            'current_address' => 'Alamat Domisili',
            'family_name' => 'Nama Kontak Darurat',
            'family_relation' => 'Hubungan Kontak Darurat',
            'family_phone' => 'No. Telp Kontak Darurat',
        ];

        // 1. Cek apakah profil ada, jika tidak ada langsung error
        if (!$user->profile) {
            return redirect()->back()->with('error', 'Silahkan lengkapi data profil Anda terlebih dahulu.');
        }

        // 2. Cari field mana saja yang masih kosong
        $emptyFields = [];
        foreach ($requiredFields as $column => $label) {
            if (empty($user->profile->$column)) {
                $emptyFields[] = $label;
            }
        }

        // 3. Jika ada yang kosong, tampilkan listnya
        if (!empty($emptyFields)) {
            $listMissing = implode(', ', $emptyFields);
            return redirect()->back()
                ->with('error', 'Profil belum lengkap. Mohon isi: ' . $listMissing);
        }

        $requiredDocuments = ['KTP', 'KARTU KELUARGA'];
        
        $userDocuments = Document::where('user_id', $user->id)
            ->pluck('name')
            ->toArray();

        $missingDocuments = array_diff($requiredDocuments, $userDocuments);

        if (!empty($missingDocuments)) {
            $listMissingDoc = implode(', ', $missingDocuments);
            return redirect()->back()
                ->with('error', 'Dokumen belum lengkap. Silahkan unggah dokumen: ' . $listMissingDoc);
        }

        $career = Career::where('slug', $slug)->firstOrFail();
        
        if (Applicant::where(['user_id' => $user->id, 'career_id' => $career->id])->exists()) {
            return redirect()->back()->with('error', 'Anda sudah melamar posisi ini.');
        }

        Applicant::create([
            'user_id' => $user->id,
            'career_id' => $career->id,
            'status_id' => 0,
        ]);

        return redirect()->route('web.applicants.dashboard')
            ->with('success', 'Terimakasih telah melamar pekerjaan!');
    }

    public function ocrPage()
    {
        return view('website.profiles.ocr');
    }

    public function storeOcr(Request $request)
    {
        $request->validate([
            'nik' => 'required|numeric|digits:16',
            'name' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = Auth::user();

        $user->update([
            'name' => $request->name
        ]);

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
            ]
        );

        return redirect()->route('applicants.profiles.index')
            ->with('success', 'Data profil berhasil diperbarui.');
    }

    public function indexProfile()
    {
        $sites = Site::all();
        $user = Auth::user();

        $user->loadMissing('profile');

        $documents = Document::where('user_id', $user->id)->get();
        return view('website.profiles.index', compact('user', 'sites', 'documents'));
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();

        if (isset($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }

        $user->update($input);

        return redirect()->back()
            ->with('success', 'Profil ' . $user->name . ' berhasil diperbarui');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $profileData = $request->only([
            'address',
            'current_address',
            'gender',
            'birth_place',
            'birth_date',
            'mother_name',
            'npwp_number',
            'marriage_status',
            'last_education',
            'living_with',
            'family_name',
            'family_relation',
            'family_phone',
            'height',
            'weight',
            'eye_condition',
            'sense',
            'tattoo',
            'hearing',
            'piercing',
            'push_up',
            'pbb',
            'gada_pratama',
            'gada_madya',
            'gada_utama',
            'skills',
            'bank_name',
            'account_name',
            'account_number',
        ]);

        if ($request->hasFile('avatar')) {
            $cloudinaryImage = $request->file('avatar')->storeOnCloudinary('avatars');
            $profileData['avatar_url'] = $cloudinaryImage->getSecurePath();
            $profileData['avatar_public_id'] = $cloudinaryImage->getPublicId();
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->back()->with('success', $user->name . ' berhasil diperbarui');
    }

    public function storeDocument(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg',
            'name' => 'required|string|max:255',
        ]);

        $cloudinaryFile = $request->file('file')->storeOnCloudinary('Documents');
        $url = $cloudinaryFile->getSecurePath();
        $public_id = $cloudinaryFile->getPublicId();

        $document = new Document;
        $document->user_id = $user->id;
        $document->name = $request->name;
        $document->description = $request->description ?? null;
        $document->validate = $request->validate ?? null;
        $document->file_url = $url;
        $document->file_public_id = $public_id;
        $document->save();

        return redirect()->back()->with('success', 'Dokumen ' . $document->name . ' berhasil diunggah');
    }
}
