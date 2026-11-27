<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Letter;
use App\Models\Generate;
use App\Models\ValueVariable;
use App\Models\SalarySetting;
use App\Models\LetterNumberConfig;
use App\Traits\GenerateTemplateColumns;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class GenerateTemplateImport implements ToCollection, WithStartRow
{
    use GenerateTemplateColumns;

    protected Letter $letter;
    protected $siteId;
    protected array $columns;

    protected int $created = 0;
    protected int $skipped = 0;
    protected int $salaryUpdated = 0;
    protected int $emptyNikRows = 0;
    protected int $totalRows = 0;
    protected array $skippedNiks = [];
    /** Alasan per baris yang dilewati, mis. "IC06.1024 (baris 5): NIK tidak ditemukan". */
    protected array $skipReasons = [];
    /** user_id yang sudah dibuatkan surat pada import ini (cegah 1 karyawan dapat beberapa surat). */
    protected array $issuedUserIds = [];

    public function __construct(Letter $letter, $siteId = null)
    {
        $this->letter = $letter->loadMissing('type', 'site', 'customVariables', 'numberConfig.sharedCounter');
        $this->siteId = $siteId ?: null;
        $this->columns = $this->buildTemplateColumns($this->letter);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($row->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                continue;
            }

            $this->totalRows++;
            $rowNumber = $index + $this->startRow(); // nomor baris di Excel
            $values = $this->mapRow($row);

            $employeeNik = trim((string) ($values['nik']['employee_nik'] ?? ''));
            if ($employeeNik === '') {
                $this->emptyNikRows++;
                continue;
            }

            [$user, $reason] = $this->findEmployee($employeeNik);

            if ($user && in_array($user->id, $this->issuedUserIds, true)) {
                [$user, $reason] = [null, "karyawan {$user->name} sudah dibuatkan surat dari baris lain"];
            }

            if (!$user) {
                $this->skipped++;
                $this->skippedNiks[] = $employeeNik;
                $this->skipReasons[] = "{$employeeNik} (baris {$rowNumber}): {$reason}";
                continue;
            }

            $this->issuedUserIds[] = $user->id;

            DB::transaction(function () use ($user, $values) {
                $siteId = $this->siteId ?: $user->site_id;
                $site = $this->letter->site ?: $user->site;

                $sequence = $this->nextSequence();
                $letterNumber = ($this->letter->letter_number_config_id || $this->letter->number_format)
                    ? $this->letter->generateLetterNumber($sequence, $site, $user)
                    : str_pad($sequence, $this->letter->number_padding ?? 3, '0', STR_PAD_LEFT);

                $fixed = $values['fixed'] ?? [];

                $generate = Generate::create(array_merge([
                    'letter_id'       => $this->letter->id,
                    'letter_number'   => $letterNumber,
                    'sequence_number' => $sequence,
                    'romawi'          => $this->getRomawi(date('m')),
                    'year'            => date('Y'),
                    'user_id'         => $user->id,
                    'site_id'         => $siteId,
                    'description'     => 'Imported from template: ' . $this->letter->title,
                ], $fixed));

                foreach ($values['custom'] ?? [] as $customVariableId => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    ValueVariable::create([
                        'generate_id'        => $generate->id,
                        'custom_variable_id' => $customVariableId,
                        'value'              => $value,
                    ]);
                }

                // Update pengaturan gaji karyawan sesuai data yang diimport.
                $salaryData = [];
                foreach ($values['salary'] ?? [] as $field => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $salaryData[$field] = $this->parseNumber($value);
                }
                if (!empty($salaryData)) {
                    SalarySetting::updateOrCreate(['user_id' => $user->id], $salaryData);
                    $this->salaryUpdated++;
                }

                $this->created++;
            });
        }
    }

    /**
     * Cari tepat SATU karyawan untuk NIK di file.
     *
     * 1) Cocok persis (tanpa beda huruf besar/kecil & spasi).
     * 2) Khusus NIK yang seluruhnya angka: Excel bisa menghapus angka 0 di depan,
     *    jadi cocokkan dengan NIK angka di database yang nilainya sama.
     * Tidak ada pencocokan "CAST ke angka" untuk NIK berhuruf (dulu: semua NIK
     * berawalan huruf dibaca 0 sehingga semua baris cocok ke karyawan yang sama).
     * Bila lebih dari satu karyawan cocok, baris dilewati (ambigu).
     *
     * @return array{0: ?User, 1: ?string} [user, alasan bila tidak ketemu]
     */
    protected function findEmployee(string $employeeNik): array
    {
        $base = fn () => User::query()->when($this->siteId, fn ($q) => $q->where('site_id', $this->siteId));

        $matches = $base()->whereRaw('UPPER(TRIM(employee_nik)) = ?', [strtoupper($employeeNik)])->limit(2)->get();

        if ($matches->isEmpty() && ctype_digit($employeeNik)) {
            $matches = $base()
                ->whereRaw("employee_nik REGEXP '^[0-9]+$'")
                ->whereRaw('TRIM(LEADING \'0\' FROM employee_nik) = ?', [ltrim($employeeNik, '0')])
                ->limit(2)
                ->get();
        }

        if ($matches->count() > 1) {
            return [null, 'NIK cocok ke lebih dari satu karyawan'];
        }
        if ($matches->isEmpty()) {
            return [null, $this->siteId ? 'NIK tidak ditemukan di site terpilih' : 'NIK tidak ditemukan'];
        }

        return [$matches->first(), null];
    }

    /**
     * Petakan satu baris excel ke nilai berdasarkan urutan kolom template.
     */
    protected function mapRow(Collection $row): array
    {
        $result = ['nik' => [], 'fixed' => [], 'custom' => [], 'salary' => []];

        foreach ($this->columns as $index => $col) {
            $raw = $row[$index] ?? null;
            $value = is_string($raw) ? trim($raw) : $raw;

            if ($col['type'] === 'nik') {
                $result['nik']['employee_nik'] = $value;
            } elseif ($col['type'] === 'fixed') {
                $result['fixed'][$col['key']] = in_array($col['key'], ['start_date', 'end_date'], true)
                    ? $this->normalizeDate($value)
                    : $value;
            } elseif ($col['type'] === 'custom') {
                $result['custom'][$col['key']] = $value;
            } elseif ($col['type'] === 'salary') {
                $result['salary'][$col['key']] = $value;
            }
        }

        return $result;
    }

    /**
     * Tanggal di template boleh DD-MM-YYYY, DD/MM/YYYY, YYYY-MM-DD, atau sel tanggal Excel (angka serial).
     * Disimpan sebagai YYYY-MM-DD.
     */
    protected function normalizeDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \Illuminate\Support\Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                )->toDateString();
            }

            $value = trim((string) $value);
            if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $value, $m)) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }

            return \Illuminate\Support\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function nextSequence(): int
    {
        if ($this->letter->numberConfig) {
            return $this->letter->numberConfig->nextSequence();
        }

        $typeLetter = $this->letter->type;

        return $typeLetter ? $typeLetter->nextNumber() : 1;
    }

    protected function getRomawi($month): string
    {
        $map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return $map[(int) $month] ?? 'I';
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getSalaryUpdated(): int
    {
        return $this->salaryUpdated;
    }

    /** @return string[] */
    public function getSkipReasons(): array
    {
        return $this->skipReasons;
    }

    public function getSkippedNiks(): array
    {
        return $this->skippedNiks;
    }

    public function getEmptyNikRows(): int
    {
        return $this->emptyNikRows;
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }
}
