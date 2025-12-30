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

class DataController extends Controller
{
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
        return view('website.dashboard', compact('timelines'));
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

        $eletter = Generate::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$eletter) {
            return redirect()->back()->with('error', 'Belum ada surat digital untuk Anda.');
        }

        $no_surat = $eletter->letter_number ?? 'belum ada no surat';
        $romawi = $eletter->romawi ?? 'belum ada data';
        $tahun = $eletter->year ?? 'belum ada tahun';
        $hari = $eletter->day ?? 'belum ada hari';
        $pihak_2 = $eletter->second_party ?? 'belum ada data';
        $sign_2 = $eletter->second_party_esign ?? 'belum ada data';
        $nama_karyawan = $eletter->user->name ?? 'belum ada nama';
        $nik_ktp = $eletter->user->nik ?? 'belum ada NIK KTP';
        $jenis_kelamin = $eletter->user->profile->gender ?? 'belum ada Jenis Kelamin';
        $ttl = $eletter->user->profile->birth_place . ', ' . Carbon::parse($eletter->user->profile->birth_date)->format('d-m-Y') ?? 'belum ada data';
        $alamat = $eletter->user->profile->address ?? 'belum ada alamat';
        $handphone = $eletter->user->phone ?? 'belum ada no handphone';
        $no_karyawan = $eletter->user->employee_nik ?? 'belum ada no karyawan';
        $area = $eletter->site->name ?? 'belum ada area';
        $jabatan = $eletter->jabatan ?? 'belum ada jabatan';
        $esign = $eletter->esign ?? 'belum ada tanda tangan';
        $nama_kontak = $eletter->emergency_name ?? 'belum ada nama';
        $no_kontak = $eletter->emergency_number ?? 'belum ada no hp';
        $alamat_kontak = $eletter->emergency_address ?? 'belum ada alamat';
        $hubungan = $eletter->relationship ?? 'belum ada hubungan';
        
        
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
                if ($component->type === 'allowance') {
                    $nominal = $component->amount;
                    $nama_komponen = $component->component_type->name ?? 'Tunjangan';
                    $tunjangan_items[] = $nama_komponen . ' : Rp ' . number_format($nominal, 0, ',', '.');
                }
            }
        }
        $tunjangan = !empty($tunjangan_items) ? implode('<br>', $tunjangan_items) : 'Tidak ada data';
        
        $mulai = Carbon::parse($eletter->join_date)->format('d-m-Y') ?? 'belum ada data';
        $selesai = Carbon::parse($eletter->end_date)->format('d-m-Y') ?? 'belum ada data';
    
        $eletter->letter->description = str_replace(
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
                '[jenis_kelamin]',
                '[nik_ktp]',
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
                $jenis_kelamin,
                $nik_ktp,
                $ttl,
                $alamat,
                $handphone,
                $no_karyawan,
                $area,
                $jabatan,
                $esign,
                $gaji,
                $tunjangan,
                // $komisi,
                // $potongan,
                $nama_kontak,
                $no_kontak,
                $alamat_kontak,
                $hubungan
            ],
            $eletter->letter->description
        );

        return view('website.letters.show', compact('eletter'));
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

        $requiredFields = [
            'marriage_status', 
            
        ];

        $isComplete = $user->profile && collect($requiredFields)->every(function($field) use ($user) {
            return !empty($user->profile->$field);
        });

        if (!$isComplete) {
            return redirect()->back()
                ->with('error', 'Profil tidak lengkap. Mohon dilengkapi Data diri anda.');
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

    public function indexProfile()
    {
        $sites = Site::all();
        $user = Auth::user();

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
            'gender',
            'birth_place',
            'birth_date',
            'mother_name',
            'npwp_number',
            'marriage_status',
            'bank_name',
            'account_name',
            'account_number',
            'resign_date'
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
            'file' => 'required|file|mimes:png,jpg,jpeg,pdf|max:2048',
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
