<?php

namespace App\Http\Controllers;

use App\Models\Generate;
use App\Models\Mutation;
use App\Models\Site;
use App\Models\User;
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

    public function store(Request $request, $id)
    {
        $user = User::with('profile')->findOrFail($id);

        $data = $request->validate([
            'type' => ['required', Rule::in(['mutasi', 'promosi', 'demosi', 'resign', 'aktif'])],
            'date' => ['required', 'date'],
            'to' => ['nullable', 'required_if:type,mutasi', 'exists:sites,id'],
            'to_role' => ['nullable', 'required_if:type,promosi,demosi', Rule::exists('roles', 'name'), Rule::notIn(self::EXCLUDED_ROLES)],
            'from_role' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'type.required' => 'Pilih jenis proses.',
            'date.required' => 'Tanggal efektif wajib diisi.',
            'to.required_if' => 'Pilih site tujuan mutasi.',
            'to_role.required_if' => 'Pilih jabatan baru.',
            'to_role.not_in' => 'Jabatan tersebut tidak bisa dipilih.',
        ]);

        $message = DB::transaction(fn () => match ($data['type']) {
            'mutasi' => $this->mutate($user, $data),
            'promosi', 'demosi' => $this->changeRole($user, $data),
            'resign' => $this->resign($user, $data),
            'aktif' => $this->reactivate($user, $data),
        });

        return redirect()->back()->with('success', $message);
    }

    public function reviseContract(Request $request, $id)
    {
        $user = User::with('profile')->findOrFail($id);

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

    private function record(User $user, string $type, string $date, ?string $description, array $attributes = []): Mutation
    {
        return Mutation::create(array_merge([
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
