<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Career;
use App\Models\Document;
use App\Models\Generate;
use App\Models\Letter;
use App\Models\Profile;
use App\Models\Site;
use App\Models\User;
use App\Support\ApplicantRequirements;
use App\Support\IdentityRules;
use App\Services\FileStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DataController extends Controller
{
    use \App\Traits\PdfPageImageTrait;
    use \App\Traits\SalaryVariableTrait;
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

        // 1-2. Kelengkapan profil & dokumen (lihat App\Support\ApplicantRequirements)
        $missingProfileFields = ApplicantRequirements::missingFields($user);
        $missingDocuments = ApplicantRequirements::missingDocuments($user);

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
            // 3. Untuk surat yang HANYA butuh tanda tangan HRD (tanpa tanda tangan
            //    employee), sembunyikan dari applicant selama HRD belum approve /
            //    tanda tangan (esign masih kosong).
            ->where(function ($query) {
                $query->whereDoesntHave('letter', function ($q) {
                    $q->where('require_hrd_signature', true)
                      ->where('require_employee_signature', false);
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('esign')
                      ->where('esign', '!=', '');
                });
            })
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return view('website.letters.index', compact('histories'));
    }

    private function hrdSignaturePending(Generate $eletter): bool
    {
        return $eletter->letter
            && $eletter->letter->require_hrd_signature
            && !$eletter->letter->require_employee_signature
            && empty($eletter->esign);
    }

    public function letterDetail($id)
    {
        $user = Auth::user();

        // Hanya surat milik pelamar yang login (cegah membuka surat orang lain lewat ID).
        $eletter = Generate::where('id', $id)
            ->where('user_id', $user->id)
            ->with('letter')
            ->first();

        if (!$eletter) {
            return redirect()->back()->with('error', 'Belum ada surat digital untuk Anda.');
        }

        // Surat yang hanya butuh tanda tangan HRD belum boleh dibuka applicant
        // selama HRD belum approve / tanda tangan (esign masih kosong).
        if ($this->hrdSignaturePending($eletter)) {
            return redirect()->route('web.applicants.letter')->with('error', 'Surat belum tersedia. Menunggu persetujuan HRD.');
        }

        $no_surat = $eletter->formatted_letter_number ?? '';
        $tgl_surat = isset($eletter->created_at) 
            ? Carbon::parse($eletter->created_at)->format('d-m-Y') 
            : '';
        $romawi = $eletter->romawi ?? '';
        $tahun = $eletter->year ?? '';
        $hari = $eletter->day ?? '';
        $pihak_2 = $eletter->second_party ?? '';
        $sign_2 = $eletter->second_party_esign ?? '';
        $nama_karyawan = strtoupper($eletter->user->name ?? '');
        $nik_ktp = $eletter->user->nik ?? '';
        $jenis_kelamin = $eletter->user->profile->gender ?? '';
        $ttl = isset($eletter->user->profile->birth_place) && isset($eletter->user->profile->birth_date)
            ? $eletter->user->profile->birth_place . ', ' . Carbon::parse($eletter->user->profile->birth_date)->format('d-m-Y')
            : '';
        $alamat = $eletter->user->profile->address ?? '';
        $handphone = $eletter->user->phone ?? '';
        $no_karyawan = $eletter->user->employee_nik ?? '';
        $lokasi_project = $eletter->site->name ?? '';
        $area = $eletter->site->area ?? '';
        $nama_client = $eletter->site->client_name ?? '';
        $jabatan_client = $eletter->site->client_position ?? '';
        $jabatan = strtoupper($eletter->user->roles->first()->name ?? '');
        $esign = $eletter->esign ?? '';
        
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

        $tunjangan = !empty($tunjangan_items) ? implode("\n", $tunjangan_items) : '-'; // \n -> <br> setelah di-escape
        
        $mulai = isset($eletter->start_date)
            ? Carbon::parse($eletter->start_date)->format('d-m-Y')
            : '';
        $selesai = isset($eletter->end_date)
            ? Carbon::parse($eletter->end_date)->format('d-m-Y')
            : '';

        $search = [
            '[no_surat]', '[tgl_surat]', '[romawi]', '[tahun]', '[hari]', '[mulai]', '[selesai]',
            '[pihak_2]', '[sign_2]', '[nama_karyawan]', '[jenis_kelamin]', '[nik_ktp]', '[ttl]', 
            '[alamat]', '[handphone]', '[no_karyawan]', '[lokasi_project]', '[area]', '[nama_client]', '[jabatan_client]', '[jabatan]', '[esign]', 
            '[gaji]', '[tunjangan]'
        ];

        $replace = [
            $no_surat, $tgl_surat, $romawi, $tahun, $hari, $mulai, $selesai,
            $pihak_2, $sign_2, $nama_karyawan, $jenis_kelamin, $nik_ktp, $ttl, 
            $alamat, $handphone, $no_karyawan, $lokasi_project, $area, $nama_client, $jabatan_client, $jabatan, $esign, 
            $gaji, $tunjangan
        ];

        [$salarySearch, $salaryReplace] = $this->buildSalaryVariables($eletter->user);
        $search = array_merge($search, $salarySearch);
        $replace = array_merge($replace, $salaryReplace);

        $customValues = \App\Models\ValueVariable::where('generate_id', $eletter->id)
            ->with('customVariable')
            ->get();

        foreach ($customValues as $cv) {
            if ($cv->customVariable) {
                $search[] = '[' . $cv->customVariable->variable . ']';
                $replace[] = $cv->value;
            }
        }

        $eletter->letter->description = str_replace($search, \App\Support\LetterPlaceholders::forHtml($replace), $eletter->letter->description);

        return view('website.letters.show', compact('eletter'));
    }

    public function letterPdf($id)
    {
        $eletter = Generate::where('id', $id)->where('user_id', Auth::id())->with('letter')->first();

        if (!$eletter) {
            abort(404);
        }

        if ($this->hrdSignaturePending($eletter)) {
            abort(403, 'Surat belum tersedia. Menunggu persetujuan HRD.');
        }

        $user = $eletter->user;

        $no_surat = $eletter->formatted_letter_number ?? '';
        $tgl_surat = isset($eletter->created_at) ? Carbon::parse($eletter->created_at)->format('d-m-Y') : '';
        $romawi = $eletter->romawi ?? '';
        $tahun = $eletter->year ?? '';
        $hari = $eletter->day ?? '';
        $pihak_2 = $eletter->second_party ?? '';
        $sign_2 = $eletter->second_party_esign ?? '';
        $nama_karyawan = strtoupper($eletter->user->name ?? '');
        $nik_ktp = $eletter->user->nik ?? '';
        $jenis_kelamin = $eletter->user->profile->gender ?? '';
        $ttl = isset($eletter->user->profile->birth_place) && isset($eletter->user->profile->birth_date)
            ? $eletter->user->profile->birth_place . ', ' . Carbon::parse($eletter->user->profile->birth_date)->format('d-m-Y')
            : '';
        $alamat = $eletter->user->profile->address ?? '';
        $handphone = $eletter->user->phone ?? '';
        $no_karyawan = $eletter->user->employee_nik ?? '';
        $lokasi_project = $eletter->site->name ?? '';
        $area = $eletter->site->area ?? '';
        $nama_client = $eletter->site->client_name ?? '';
        $jabatan_client = $eletter->site->client_position ?? '';
        $jabatan = strtoupper($eletter->user->roles->first()->name ?? '');
        $esign = $eletter->esign ?? '';

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

        $mulai = isset($eletter->start_date) ? Carbon::parse($eletter->start_date)->format('d-m-Y') : '';
        $selesai = isset($eletter->end_date) ? Carbon::parse($eletter->end_date)->format('d-m-Y') : '';

        $search = [
            '[no_surat]', '[tgl_surat]', '[romawi]', '[tahun]', '[hari]', '[mulai]', '[selesai]',
            '[pihak_2]', '[sign_2]', '[nama_karyawan]', '[jenis_kelamin]', '[nik_ktp]', '[ttl]',
            '[alamat]', '[handphone]', '[no_karyawan]', '[lokasi_project]', '[area]', '[nama_client]',
            '[jabatan_client]', '[jabatan]', '[esign]', '[gaji]', '[tunjangan]'
        ];

        $replace = [
            $no_surat, $tgl_surat, $romawi, $tahun, $hari, $mulai, $selesai,
            $pihak_2, $sign_2, $nama_karyawan, $jenis_kelamin, $nik_ktp, $ttl,
            $alamat, $handphone, $no_karyawan, $lokasi_project, $area, $nama_client,
            $jabatan_client, $jabatan, $esign, $gaji, $tunjangan
        ];

        [$salarySearch, $salaryReplace] = $this->buildSalaryVariables($eletter->user);
        $search = array_merge($search, $salarySearch);
        $replace = array_merge($replace, $salaryReplace);

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
                    $descriptionReplaced = str_replace($search, \App\Support\LetterPlaceholders::forJson($replace), $description);
                    $parsedReplaced = json_decode($descriptionReplaced, true);
                    $pages = $this->savePageImages($parsedReplaced['pages'] ?? []);
                }
            } catch (\Exception $e) {}
        }

        if (!$isFabric) {
            $description = str_replace($search, \App\Support\LetterPlaceholders::forHtml($replace), $description);
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
        $eletter = Generate::where('id', $id)->where('user_id', Auth::id())->with('letter')->first();
        if (!$eletter) abort(404);

        if ($this->hrdSignaturePending($eletter)) {
            abort(403, 'Surat belum tersedia. Menunggu persetujuan HRD.');
        }

        $user = $eletter->user;
        $no_surat = $eletter->formatted_letter_number ?? '';
        $tgl_surat = isset($eletter->created_at) ? Carbon::parse($eletter->created_at)->format('d-m-Y') : '';
        $romawi = $eletter->romawi ?? '';
        $tahun = $eletter->year ?? '';
        $hari = $eletter->day ?? '';
        $pihak_2 = $eletter->second_party ?? '';
        $sign_2 = $eletter->second_party_esign ?? '';
        $nama_karyawan = strtoupper($eletter->user->name ?? '');
        $nik_ktp = $eletter->user->nik ?? '';
        $jenis_kelamin = $eletter->user->profile->gender ?? '';
        $ttl = isset($eletter->user->profile->birth_place) && isset($eletter->user->profile->birth_date)
            ? $eletter->user->profile->birth_place . ', ' . Carbon::parse($eletter->user->profile->birth_date)->format('d-m-Y')
            : '';
        $alamat = $eletter->user->profile->address ?? '';
        $handphone = $eletter->user->phone ?? '';
        $no_karyawan = $eletter->user->employee_nik ?? '';
        $lokasi_project = $eletter->site->name ?? '';
        $area = $eletter->site->area ?? '';
        $nama_client = $eletter->site->client_name ?? '';
        $jabatan_client = $eletter->site->client_position ?? '';
        $jabatan = strtoupper($eletter->user->roles->first()->name ?? '');
        $esign = $eletter->esign ?? '';

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

        $mulai = isset($eletter->start_date) ? Carbon::parse($eletter->start_date)->format('d-m-Y') : '';
        $selesai = isset($eletter->end_date) ? Carbon::parse($eletter->end_date)->format('d-m-Y') : '';

        $search = ['[no_surat]','[tgl_surat]','[romawi]','[tahun]','[hari]','[mulai]','[selesai]','[pihak_2]','[sign_2]','[nama_karyawan]','[jenis_kelamin]','[nik_ktp]','[ttl]','[alamat]','[handphone]','[no_karyawan]','[lokasi_project]','[area]','[nama_client]','[jabatan_client]','[jabatan]','[esign]','[gaji]','[tunjangan]'];
        $replace = [$no_surat,$tgl_surat,$romawi,$tahun,$hari,$mulai,$selesai,$pihak_2,$sign_2,$nama_karyawan,$jenis_kelamin,$nik_ktp,$ttl,$alamat,$handphone,$no_karyawan,$lokasi_project,$area,$nama_client,$jabatan_client,$jabatan,$esign,$gaji,$tunjangan];

        [$salarySearch, $salaryReplace] = $this->buildSalaryVariables($eletter->user);
        $search = array_merge($search, $salarySearch);
        $replace = array_merge($replace, $salaryReplace);

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

    public function sign(Request $request, $id)
    {
        $request->validate([
            'second_party_esign' => 'required|string',
        ]);

        // Validasi ketat: hanya PNG base64 murni (cegah HTML/JS disisipkan ke tanda tangan)
        $signatureData = \App\Support\SignatureImage::normalizePng($request->input('second_party_esign'));
        if (!$signatureData) {
            return back()->with('error', 'Data tanda tangan tidak valid (harus gambar PNG).');
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
            \Illuminate\Support\Facades\Log::error('Gagal menyimpan tanda tangan surat #' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan tanda tangan. Silakan coba lagi.');
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

        // Syarat kelengkapan: lihat App\Support\ApplicantRequirements
        $emptyFields = ApplicantRequirements::missingFields($user);

        if (!empty($emptyFields)) {
            return redirect()->back()
                ->with('error', 'Profil belum lengkap. Mohon isi: ' . implode(', ', $emptyFields));
        }

        $missingDocuments = ApplicantRequirements::missingDocuments($user);

        if (!empty($missingDocuments)) {
            return redirect()->back()
                ->with('error', 'Dokumen belum lengkap. Silahkan unggah dokumen: ' . implode(', ', $missingDocuments));
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
            'nik'        => 'required|numeric|digits:16',
            'name'       => 'required|string|max:255',
            'birth_place'=> 'nullable|string|max:100',
            'birth_date' => 'nullable|string',
            'gender'     => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'rt_rw'      => 'nullable|string|max:20',
            'kelurahan'  => 'nullable|string|max:100',
            'kecamatan'  => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        $user->update([
            'name' => $request->name,
            'nik'  => $request->nik,
        ]);

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'birth_place' => $request->birth_place,
                'birth_date'  => $request->birth_date,
                'gender'      => $request->gender,
                'address'     => $request->address,
                'rt_rw'       => $request->rt_rw,
                'kelurahan'   => $request->kelurahan,
                'kecamatan'   => $request->kecamatan,
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

        IdentityRules::normalize($request, ['nik']);

        // Hanya field akun milik pelamar; jangan pakai $request->all() (bisa mengubah
        // is_employee, employee_nik, site_id, dsb. lewat request yang dimanipulasi).
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nik' => IdentityRules::nik(),
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ], IdentityRules::messages());

        if (!empty($input['password'])) {
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

        IdentityRules::normalize($request, ['no_kk', 'account_number']);
        $request->validate(['no_kk' => IdentityRules::kk()] + IdentityRules::bank(), IdentityRules::messages());

        $profileData = $request->only([
            'no_kk',
            'address',
            'current_address',
            'rt_rw',
            'kelurahan',
            'kecamatan',
            'gender',
            'religion',
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
            $request->validate(['avatar' => 'image|mimes:jpg,jpeg,png,webp|max:5120'], ['avatar.image' => 'Pas foto harus berupa gambar.', 'avatar.mimes' => 'Pas foto harus JPG/PNG/WebP.', 'avatar.max' => 'Ukuran pas foto maksimal 5 MB.']);
            $avatar = FileStorage::upload($request->file('avatar'), 'avatars');
            $profileData['avatar_url'] = $avatar['url'];
            $profileData['avatar_public_id'] = $avatar['path'];
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

        $file = FileStorage::upload($request->file('file'), 'Documents');

        $document = new Document;
        $document->user_id = $user->id;
        $document->name = $request->name;
        $document->description = $request->description ?? null;
        $document->validate = null; // status validasi hanya diisi admin
        $document->file_url = $file['url'];
        $document->file_public_id = $file['path'];
        $document->save();

        return redirect()->back()->with('success', 'Dokumen ' . $document->name . ' berhasil diunggah');
    }
}
