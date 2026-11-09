<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Pakai bersama interface WithCustomValueBinder.
 *
 * String angka panjang (NIK/KK 16 digit, rekening, BPJS) atau yang diawali 0 (No HP, NIK karyawan)
 * ditulis sebagai TEKS. Tanpa ini Excel menyimpannya sebagai angka: digit ke-16 berubah jadi 0
 * dan angka 0 di depan hilang.
 */
trait BindsLongNumbersAsText
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && preg_match('/^\+?\d+$/', $value) && (strlen($value) > 11 || str_starts_with($value, '0'))) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }
}
