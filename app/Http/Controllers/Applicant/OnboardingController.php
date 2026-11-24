<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Services\FileStorage;
use App\Support\ApplicantRequirements;
use App\Support\IdentityRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Wizard "Lengkapi Profil" pelamar setelah daftar: data diri -> alamat -> fisik ->
 * kontak darurat & rekening -> dokumen. Syarat lengkap mengikuti ApplicantRequirements.
 */
class OnboardingController extends Controller
{
    public const STEPS = [
        1 => ['title' => 'Data Diri', 'icon' => 'ti-user'],
        2 => ['title' => 'Alamat', 'icon' => 'ti-map-pin'],
        3 => ['title' => 'Fisik & Keahlian', 'icon' => 'ti-heartbeat'],
        4 => ['title' => 'Kontak & Rekening', 'icon' => 'ti-address-book'],
        5 => ['title' => 'Dokumen', 'icon' => 'ti-files'],
    ];

    /** Kolom wajib (ApplicantRequirements::PROFILE_FIELDS) per langkah. */
    private const STEP_FIELDS = [
        1 => ['avatar_url', 'gender', 'birth_place', 'birth_date', 'mother_name', 'no_kk', 'last_education', 'marriage_status'],
        2 => ['address', 'current_address', 'living_with'],
        3 => ['height', 'weight', 'eye_condition', 'hearing'],
        4 => ['family_name', 'family_relation', 'family_phone', 'bank_name', 'account_name', 'account_number'],
    ];

    public const EDUCATIONS = ['SD', 'SMP', 'SMA/SLTA', 'D3', 'S1', 'S2', 'S3'];
    public const RELIGIONS = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
    public const MARRIAGE_STATUSES = [
        'TK-0' => 'TK-0 : Tidak Kawin',
        'TK-1' => 'TK-1 : Duda/Janda (Anak 1)',
        'TK-2' => 'TK-2 : Duda/Janda (Anak 2)',
        'TK-3' => 'TK-3 : Duda/Janda (Anak 3)',
        'K-0' => 'K-0 : Kawin',
        'K-1' => 'K-1 : Kawin (Anak 1)',
        'K-2' => 'K-2 : Kawin (Anak 2)',
        'K-3' => 'K-3 : Kawin (Anak 3)',
    ];
    public const LIVING_WITH = ['Orang Tua' => 'Orang Tua/Lainnya', 'Kontrak/Sewa' => 'Kontrak/Sewa', 'Milik Sendiri' => 'Milik Sendiri'];

    public function show(Request $request, ?int $step = null)
    {
        $user = $this->user();
        $firstIncomplete = $this->firstIncompleteStep($user);

        // Tanpa nomor langkah: lanjutkan dari langkah yang belum lengkap.
        // Tidak boleh melompat melewati langkah yang belum lengkap.
        if ($step === null || ! isset(self::STEPS[$step]) || $step > $firstIncomplete) {
            return redirect()->route('applicant.onboarding', ['step' => $firstIncomplete]);
        }

        $documents = Document::where('user_id', $user->id)->get()->keyBy(fn ($d) => strtoupper(trim($d->name)));

        return view('website.onboarding.index', [
            'user' => $user,
            'profile' => $user->profile,
            'step' => $step,
            'steps' => self::STEPS,
            'firstIncomplete' => $firstIncomplete,
            'requiredDocuments' => ApplicantRequirements::requiredDocuments($user),
            'documents' => $documents,
        ]);
    }

    public function save(Request $request, int $step)
    {
        abort_unless(isset(self::STEPS[$step]), 404);

        $user = $this->user();

        if ($step === 5) {
            return $this->finish($user);
        }

        match ($step) {
            1 => $this->saveDataDiri($request, $user),
            2 => $this->saveAlamat($request, $user),
            3 => $this->saveFisik($request, $user),
            4 => $this->saveKontak($request, $user),
        };

        return redirect()->route('applicant.onboarding', ['step' => $step + 1]);
    }

    public function uploadDocument(Request $request)
    {
        $user = $this->user();

        $request->validate([
            'name' => ['required', Rule::in(ApplicantRequirements::requiredDocuments($user))],
            'file' => 'required|file|mimes:png,jpg,jpeg|max:5120',
        ], [
            'file.required' => 'Pilih file dokumen terlebih dahulu.',
            'file.mimes' => 'Dokumen harus berupa gambar JPG/PNG.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $uploaded = FileStorage::upload($request->file('file'), 'Documents');

        // Ganti dokumen lama dengan nama yang sama agar tidak dobel.
        $document = Document::where('user_id', $user->id)
            ->whereRaw('UPPER(TRIM(name)) = ?', [$request->name])
            ->latest('id')
            ->first();
        $oldPath = $document?->file_public_id;

        $document ??= new Document(['user_id' => $user->id]);
        $document->name = $request->name;
        $document->file_url = $uploaded['url'];
        $document->file_public_id = $uploaded['path'];
        $document->save();

        if ($oldPath && $oldPath !== $uploaded['path']) {
            FileStorage::delete($oldPath);
        }

        return redirect()->route('applicant.onboarding', ['step' => 5])
            ->with('success', 'Dokumen ' . $request->name . ' berhasil diunggah.');
    }

    private function finish(User $user)
    {
        $missing = array_merge(
            ApplicantRequirements::missingFields($user),
            array_map(fn ($d) => 'Dokumen ' . $d, ApplicantRequirements::missingDocuments($user))
        );

        if (! empty($missing)) {
            return redirect()->route('applicant.onboarding', ['step' => $this->firstIncompleteStep($user)])
                ->with('error', 'Masih ada data yang belum lengkap: ' . implode(', ', $missing) . '.');
        }

        return redirect()->route('web.applicants.dashboard')
            ->with('success', 'Profil Anda sudah lengkap. Silakan pilih lowongan dan mulai melamar!');
    }

    private function saveDataDiri(Request $request, User $user): void
    {
        IdentityRules::normalize($request, ['nik', 'no_kk']);

        // Pas foto disimpan lebih dulu, terpisah dari kolom lain: file upload tidak bisa
        // dikembalikan browser setelah validasi gagal, jadi pelamar tidak perlu memilih ulang.
        if ($request->hasFile('avatar')) {
            $request->validate(
                ['avatar' => ['image', 'mimes:jpg,jpeg,png', 'max:5120']],
                ['avatar.image' => 'Pas foto harus berupa gambar.', 'avatar.mimes' => 'Pas foto harus JPG/PNG.', 'avatar.max' => 'Ukuran pas foto maksimal 5 MB.']
            );

            $avatar = FileStorage::upload($request->file('avatar'), 'avatars');
            $oldAvatar = $user->profile?->avatar_public_id;
            $this->saveProfile($user, ['avatar_url' => $avatar['url'], 'avatar_public_id' => $avatar['path']]);
            if ($oldAvatar && $oldAvatar !== $avatar['path']) {
                FileStorage::delete($oldAvatar);
            }
            $user->load('profile');
        }

        $data = $request->validate([
            'nik' => IdentityRules::nik(),
            'avatar' => [Rule::requiredIf(blank($user->profile?->avatar_url)), 'nullable'],
            'gender' => ['required', Rule::in(['Laki-Laki', 'Perempuan'])],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'mother_name' => ['required', 'string', 'max:255'],
            'religion' => ['required', Rule::in(self::RELIGIONS)],
            'last_education' => ['required', Rule::in(self::EDUCATIONS)],
            'marriage_status' => ['required', Rule::in(array_keys(self::MARRIAGE_STATUSES))],
            'no_kk' => ['required', 'digits:16'],
            'npwp_number' => ['nullable', 'string', 'max:30'],
        ], IdentityRules::messages() + [
            'avatar.required' => 'Pas foto wajib diunggah.',
            'avatar.image' => 'Pas foto harus berupa gambar.',
            'no_kk.required' => 'No Kartu Keluarga wajib diisi.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
        ]);

        $user->update(['nik' => $data['nik']]);

        $this->saveProfile($user, collect($data)->except(['nik', 'avatar'])->all());
    }

    private function saveAlamat(Request $request, User $user): void
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:500'],
            'rt_rw' => ['nullable', 'string', 'max:20'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'current_address' => ['required', 'string', 'max:500'],
            'living_with' => ['required', Rule::in(array_keys(self::LIVING_WITH))],
        ], [
            'address.required' => 'Alamat KTP wajib diisi.',
            'current_address.required' => 'Alamat domisili wajib diisi.',
        ]);

        $this->saveProfile($user, $data);
    }

    private function saveFisik(Request $request, User $user): void
    {
        $data = $request->validate([
            'height' => ['required', 'numeric', 'between:50,250'],
            'weight' => ['required', 'numeric', 'between:20,300'],
            'eye_condition' => ['required', Rule::in(['Normal', 'Color Blind'])],
            'hearing' => ['required', Rule::in(['Normal', 'Impaired'])],
            'tattoo' => ['required', Rule::in(['None', 'Present'])],
            'piercing' => ['required', Rule::in(['None', 'Present'])],
            'skills' => ['nullable', 'string', 'max:2000'],
            'is_security' => ['required', Rule::in(['yes', 'no'])],
            'push_up' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'pbb' => ['nullable', Rule::in(['0', '1'])],
            'gada_pratama' => ['nullable', Rule::in(['yes', 'no'])],
            'gada_madya' => ['nullable', Rule::in(['yes', 'no'])],
            'gada_utama' => ['nullable', Rule::in(['yes', 'no'])],
        ], [
            'height.required' => 'Tinggi badan wajib diisi.',
            'weight.required' => 'Berat badan wajib diisi.',
        ]);

        // Bukan security: sertifikat GADA tidak berlaku.
        if ($data['is_security'] === 'no') {
            $data['gada_pratama'] = $data['gada_madya'] = $data['gada_utama'] = 'no';
        } else {
            foreach (['gada_pratama', 'gada_madya', 'gada_utama'] as $gada) {
                $data[$gada] ??= 'no';
            }
        }
        unset($data['is_security']);

        $this->saveProfile($user, $data);
    }

    private function saveKontak(Request $request, User $user): void
    {
        IdentityRules::normalize($request, ['account_number']);

        $data = $request->validate([
            'family_name' => ['required', 'string', 'max:255'],
            'family_relation' => ['required', 'string', 'max:100'],
            'family_phone' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'digits_between:5,20'],
        ], IdentityRules::messages() + [
            'family_name.required' => 'Nama kontak darurat wajib diisi.',
            'family_relation.required' => 'Hubungan kontak darurat wajib diisi.',
            'family_phone.required' => 'No. telepon kontak darurat wajib diisi.',
        ]);

        $this->saveProfile($user, $data);
    }

    private function saveProfile(User $user, array $data): void
    {
        $user->profile()->updateOrCreate(['user_id' => $user->id], $data);
        $user->unsetRelation('profile');
    }

    /** Langkah pertama yang masih punya data wajib kosong; 5 bila tinggal dokumen / sudah lengkap. */
    private function firstIncompleteStep(User $user): int
    {
        $user->loadMissing('profile');

        if (! preg_match('/^\d{16}$/', (string) $user->nik)) {
            return 1;
        }

        foreach (self::STEP_FIELDS as $step => $fields) {
            foreach ($fields as $field) {
                if (blank($user->profile?->{$field})) {
                    return $step;
                }
            }
        }

        return 5;
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('profile');

        return $user;
    }
}
