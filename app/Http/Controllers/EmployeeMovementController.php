<?php

namespace App\Http\Controllers;

use App\Models\CustomVariable;
use App\Models\Generate;
use App\Models\Letter;
use App\Models\Mutation;
use App\Models\Site;
use App\Models\User;
use App\Services\LetterIssuer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Proses karier karyawan dari halaman detail pegawai:
 * mutasi, promosi, demosi, resign, aktif kembali, dan revisi periode kontrak.
 * Setiap proses dicatat di tabel mutations (riwayat karier).
 */
class EmployeeMovementController extends Controller
{
    /** Jabatan yang tidak boleh dipakai sebagai tujuan promosi/demosi. */
    private const EXCLUDED_ROLES = ['App Administrator'];

    /** Proses yang wajib disertai surat terbit. */
    public const LETTER_REQUIRED = ['mutasi', 'promosi', 'demosi', 'resign'];

    /**
     * Variabel kustom template yang diisi otomatis dari data proses karier
     * (tulis [nama_variabel] di template surat). Nilai tetap bisa diubah di form.
     */
    public const AUTO_VARIABLES = [
        'jenis_proses' => 'Jenis proses (Mutasi/Promosi/Demosi/Resign)',
        'tgl_efektif' => 'Tanggal efektif',
        'site_lama' => 'Site/penempatan lama',
        'site_baru' => 'Site/penempatan baru',
        'jabatan_lama' => 'Jabatan lama',
        'jabatan_baru' => 'Jabatan baru',
        'keterangan' => 'Keterangan / alasan',
    ];

    /** Riwayat yang dibuat proses terakhir (untuk ditautkan ke surat terbit). */
    private ?Mutation $lastMutation = null;

    public function store(Request $request, $id)
    {
        $user = User::with('profile')->findOrFail($id);
        // Pegawai lama/hasil import bisa belum punya baris profil -> buat agar tidak error 500.
        if (! $user->profile) {
            $user->setRelation('profile', $user->profile()->firstOrCreate(['user_id' => $user->id]));
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(['mutasi', 'promosi', 'demosi', 'resign', 'aktif'])],
            'date' => ['required', 'date'],
            'to' => ['nullable', 'required_if:type,mutasi', 'exists:sites,id'],
            'to_role' => ['nullable', 'required_if:type,promosi,demosi', Rule::exists('roles', 'name'), Rule::notIn(self::EXCLUDED_ROLES)],
            'from_role' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
            'letter_id' => ['nullable', Rule::requiredIf(in_array($request->input('type'), self::LETTER_REQUIRED, true)), 'exists:letters,id'],
            'custom_values' => ['nullable', 'array'],
            'custom_values.*' => ['nullable', 'string', 'max:2000'],
        ], [
            'letter_id.required' => 'Pilih template surat yang akan diterbitkan.',
            'type.required' => 'Pilih jenis proses.',
            'date.required' => 'Tanggal efektif wajib diisi.',
            'to.required_if' => 'Pilih site tujuan mutasi.',
            'to_role.required_if' => 'Pilih jabatan baru.',
            'to_role.not_in' => 'Jabatan tersebut tidak bisa dipilih.',
        ]);

        // Proses karier + penerbitan surat dalam satu transaksi: bila salah satu gagal,
        // semuanya batal (termasuk nomor urut surat).
        $message = DB::transaction(function () use ($user, $data) {
            $message = match ($data['type']) {
                'mutasi' => $this->mutate($user, $data),
                'promosi', 'demosi' => $this->changeRole($user, $data),
                'resign' => $this->resign($user, $data),
                'aktif' => $this->reactivate($user, $data),
            };

            if (filled($data['letter_id'] ?? null)) {
                $generate = $this->issueLetter($user->fresh(['site.company', 'roles']), $data);
                $this->lastMutation?->update(['generate_id' => $generate->id]);
                $message .= ' Surat ' . ($generate->formatted_letter_number ?? $generate->letter_number) . ' diterbitkan.';
            }

            return $message;
        });

        return redirect()->back()->with('success', $message);
    }

    public function reviseContract(Request $request, $id)
    {
        $user = User::with('profile')->findOrFail($id);
        // Pegawai lama/hasil import bisa belum punya baris profil -> buat agar tidak error 500.
        if (! $user->profile) {
            $user->setRelation('profile', $user->profile()->firstOrCreate(['user_id' => $user->id]));
        }

        $data = $request->validate([
            'generate_id' => ['required', Rule::exists('generates', 'id')->where('user_id', $user->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'reset_signatures' => ['nullable', 'boolean'],
        ], [
            'generate_id.required' => 'Pilih surat kontrak yang direvisi.',
            'start_date.required' => 'Tanggal mulai kontrak wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal akhir kontrak tidak boleh sebelum tanggal mulai.',
        ]);

        $generate = Generate::findOrFail($data['generate_id']);
        $resetSignatures = $request->boolean('reset_signatures');
        $wasSigned = filled($generate->esign) || filled($generate->second_party_esign);

        DB::transaction(function () use ($user, $generate, $data, $resetSignatures) {
            $oldStart = $generate->start_date;
            $oldPeriod = self::period($generate->start_date, $generate->end_date);

            // Surat dirender dari start_date/end_date, jadi PDF/print langsung memakai periode baru.
            $generate->update(array_merge(
                ['start_date' => $data['start_date'], 'end_date' => $data['end_date'] ?? null],
                $resetSignatures ? ['esign' => null, 'second_party_esign' => null] : []
            ));

            $profile = ['end_date' => $data['end_date'] ?? null];
            if (blank($user->profile->join_date) || $user->profile->join_date === $oldStart) {
                $profile['join_date'] = $data['start_date'];
            }
            $user->profile()->updateOrCreate(['user_id' => $user->id], $profile);

            $this->record($user, 'kontrak', now()->toDateString(), $data['description'] ?? null, [
                'from_value' => $oldPeriod,
                'to_value' => self::period($data['start_date'], $data['end_date'] ?? null),
            ]);
        });

        $number = $generate->formatted_letter_number ?? $generate->letter_number;
        $message = "Periode kontrak {$user->name} diperbarui. Surat {$number} sudah memakai periode baru";
        $message .= $resetSignatures && $wasSigned ? ' dan perlu ditandatangani ulang.' : '.';

        return redirect()->back()->with('success', $message);
    }

    /** Daftar jabatan untuk pilihan promosi/demosi. */
    public static function roleOptions(): array
    {
        return Role::whereNotIn('name', self::EXCLUDED_ROLES)->orderBy('name')->pluck('name')->all();
    }

    private function mutate(User $user, array $data): string
    {
        if ((string) $data['to'] === (string) $user->site_id) {
            throw ValidationException::withMessages(['to' => 'Site tujuan sama dengan site saat ini.']);
        }

        $this->record($user, 'mutasi', $data['date'], $data['description'] ?? null, [
            'from_id' => $user->site_id,
            'to_id' => $data['to'],
        ]);

        $user->update(['site_id' => $data['to']]);
        $user->profile()->updateOrCreate(['user_id' => $user->id], ['mutation_date' => $data['date']]);

        return "{$user->name} berhasil dimutasi ke " . (Site::find($data['to'])?->name ?? 'site baru') . '.';
    }

    private function changeRole(User $user, array $data): string
    {
        $current = $user->getRoleNames();
        $fromRole = filled($data['from_role'] ?? null) && $current->contains($data['from_role'])
            ? $data['from_role']
            : $current->first(fn ($role) => ! in_array($role, self::EXCLUDED_ROLES, true));

        if ($fromRole === $data['to_role']) {
            throw ValidationException::withMessages(['to_role' => 'Jabatan baru sama dengan jabatan saat ini.']);
        }

        if ($fromRole) {
            $user->removeRole($fromRole);
        }
        $user->assignRole($data['to_role']);

        $this->record($user, $data['type'], $data['date'], $data['description'] ?? null, [
            'from_value' => $fromRole,
            'to_value' => $data['to_role'],
        ]);

        $label = $data['type'] === 'promosi' ? 'dipromosikan' : 'didemosi';

        return "{$user->name} berhasil {$label} menjadi {$data['to_role']}.";
    }

    private function resign(User $user, array $data): string
    {
        if ($user->profile->isResigned()) {
            throw ValidationException::withMessages(['type' => "{$user->name} sudah berstatus resign."]);
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], [
            'resign_date' => $data['date'],
            'employment_status' => 'RESIGN',
        ]);

        $this->record($user, 'resign', $data['date'], $data['description'] ?? null, [
            'from_id' => $user->site_id,
            'from_value' => 'Aktif',
            'to_value' => 'Resign',
        ]);

        return "{$user->name} tercatat resign per " . format_tanggal($data['date']) . '.';
    }

    private function reactivate(User $user, array $data): string
    {
        if (! $user->profile->isResigned()) {
            throw ValidationException::withMessages(['type' => "{$user->name} masih berstatus aktif."]);
        }

        $oldResign = $user->profile->resign_date;

        $user->profile()->updateOrCreate(['user_id' => $user->id], [
            'resign_date' => null,
            'employment_status' => 'ACTIVE',
        ]);

        $this->record($user, 'aktif', $data['date'], $data['description'] ?? null, [
            'to_id' => $user->site_id,
            'from_value' => 'Resign' . ($oldResign ? ' (' . format_tanggal($oldResign) . ')' : ''),
            'to_value' => 'Aktif',
        ]);

        return "{$user->name} kembali berstatus aktif.";
    }

    /** Terbitkan surat proses karier; variabel kustom kosong diisi otomatis dari data proses. */
    private function issueLetter(User $user, array $data): Generate
    {
        $letter = Letter::findOrFail($data['letter_id']);
        $auto = $this->autoValues($user, $data);
        $provided = $data['custom_values'] ?? [];

        $customValues = [];
        foreach (CustomVariable::where('letter_id', $letter->id)->get(['id', 'variable']) as $variable) {
            $value = $provided[$variable->id] ?? null;
            $customValues[$variable->id] = filled($value) ? $value : ($auto[$variable->variable] ?? null);
        }

        return LetterIssuer::issue($letter, $user, $user->site, [
            'start_date' => $data['date'],
            'description' => 'Surat ' . (Mutation::TYPES[$data['type']] ?? ucfirst($data['type'])) . ' ' . $user->name,
        ], $customValues);
    }

    /** Nilai otomatis untuk AUTO_VARIABLES berdasarkan riwayat yang baru dibuat. */
    private function autoValues(User $user, array $data): array
    {
        $mutation = $this->lastMutation;
        $currentSite = $user->site?->name ?? '';
        $currentRole = $user->getRoleNames()->first(fn ($role) => ! in_array($role, self::EXCLUDED_ROLES, true)) ?? '';

        $values = [
            'jenis_proses' => Mutation::TYPES[$data['type']] ?? ucfirst($data['type']),
            'tgl_efektif' => format_tanggal($data['date']),
            'site_lama' => $currentSite,
            'site_baru' => $currentSite,
            'jabatan_lama' => $currentRole,
            'jabatan_baru' => $currentRole,
            'keterangan' => $data['description'] ?? '',
        ];

        if ($data['type'] === 'mutasi' && $mutation) {
            $values['site_lama'] = Site::find($mutation->from_id)?->name ?? '';
            $values['site_baru'] = Site::find($mutation->to_id)?->name ?? $currentSite;
        }
        if (in_array($data['type'], ['promosi', 'demosi'], true) && $mutation) {
            $values['jabatan_lama'] = (string) $mutation->from_value;
            $values['jabatan_baru'] = (string) $mutation->to_value;
        }

        return $values;
    }

    private function record(User $user, string $type, string $date, ?string $description, array $attributes = []): Mutation
    {
        return $this->lastMutation = Mutation::create(array_merge([
            'type' => $type,
            'date' => $date,
            'user_id' => $user->id,
            'description' => $description,
            'created_by' => Auth::id(),
        ], $attributes));
    }

    private static function period($start, $end): string
    {
        return format_tanggal($start) . ' s/d ' . format_tanggal($end, 'selesai');
    }
}
