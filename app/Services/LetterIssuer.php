<?php

namespace App\Services;

use App\Models\CustomVariable;
use App\Models\Generate;
use App\Models\Letter;
use App\Models\Site;
use App\Models\User;
use App\Models\ValueVariable;

/**
 * Menerbitkan surat (baris "Surat Terbit" / generates) untuk satu karyawan:
 * ambil nomor urut berikutnya (terkunci), bentuk nomor surat, simpan variabel kustom.
 * Panggil di dalam DB::transaction agar nomor urut ikut batal bila proses gagal.
 */
class LetterIssuer
{
    private const ROMAWI = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];

    /**
     * @param  array  $attributes    kolom generates tambahan (start_date, end_date, description, ...)
     * @param  array  $customValues  [custom_variable_id => nilai]
     */
    public static function issue(Letter $letter, User $user, ?Site $site, array $attributes = [], array $customValues = []): Generate
    {
        $letter->loadMissing('type', 'numberConfig.sharedCounter');
        $site?->loadMissing('company');

        $sequence = $letter->numberConfig
            ? $letter->numberConfig->nextSequence()
            : ($letter->type ? $letter->type->nextNumber() : 1);

        $letterNumber = ($letter->letter_number_config_id || $letter->number_format)
            ? $letter->generateLetterNumber($sequence, $site, $user)
            : str_pad((string) $sequence, $letter->number_padding ?? 3, '0', STR_PAD_LEFT);

        $generate = Generate::create(array_merge([
            'letter_id' => $letter->id,
            'letter_number' => $letterNumber,
            'sequence_number' => $sequence,
            'romawi' => self::ROMAWI[(int) date('n')],
            'year' => date('Y'),
            'user_id' => $user->id,
            'site_id' => $site?->id ?? $user->site_id,
            'second_party' => $user->name,
        ], $attributes));

        // Hanya variabel kustom milik template ini yang disimpan.
        $validIds = CustomVariable::where('letter_id', $letter->id)->pluck('id')->all();
        foreach ($customValues as $variableId => $value) {
            if (! in_array((int) $variableId, $validIds, true) || $value === null || $value === '') {
                continue;
            }
            ValueVariable::create([
                'generate_id' => $generate->id,
                'custom_variable_id' => (int) $variableId,
                'value' => (string) $value,
            ]);
        }

        return $generate;
    }
}
