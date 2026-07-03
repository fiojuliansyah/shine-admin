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
    protected array $skippedNiks = [];

    public function __construct(Letter $letter, $siteId = null)
    {
        $this->letter = $letter->loadMissing('type', 'site', 'customVariables', 'numberConfig.sharedCounter');
        $this->siteId = $siteId ?: null;
        $this->columns = $this->buildTemplateColumns($this->letter);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $values = $this->mapRow($row);

            $employeeNik = trim((string) ($values['nik']['employee_nik'] ?? ''));
            if ($employeeNik === '') {
                continue;
            }

            $userQuery = User::where('employee_nik', $employeeNik);
            if ($this->siteId) {
                $userQuery->where('site_id', $this->siteId);
            }
            $user = $userQuery->first();

            if (!$user) {
                $this->skipped++;
                $this->skippedNiks[] = $employeeNik;
                continue;
            }

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
                $result['fixed'][$col['key']] = $value;
            } elseif ($col['type'] === 'custom') {
                $result['custom'][$col['key']] = $value;
            } elseif ($col['type'] === 'salary') {
                $result['salary'][$col['key']] = $value;
            }
        }

        return $result;
    }

    protected function nextSequence(): int
    {
        if ($this->letter->numberConfig) {
            return $this->letter->numberConfig->nextSequence();
        }

        $typeLetter = $this->letter->type;
        $current = (int) ($typeLetter?->number ?? 0);
        $start = $this->letter->numberConfig?->start_number ?? 1;
        $next = max($current + 1, $start);
        $typeLetter?->update(['number' => $next]);

        return $next;
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

    public function getSkippedNiks(): array
    {
        return $this->skippedNiks;
    }
}
