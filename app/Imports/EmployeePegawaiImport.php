<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Profile;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class EmployeePegawaiImport implements WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public int $sitesCreated = 0;

    public int $rolesCreated = 0;

    public array $rowErrors = [];

    public int $totalRows = 0;

    private const HEADERS = [
        'status', 'pt', 'nik_karyawan', 'nama', 'email', 'handphone',
        'nama_ibu_kandung', 'jabatan', 'project', 'area_wilayah',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'no_nik_ktp',
        'no_npwp', 'no_kk', 'alamat_ktp', 'agama', 'status_pernikahan',
        'manager', 'join_date', 'end_date', 'tgl_mutasi', 'tgl_resign',
        'no_rekening', 'nama_bank', 'nama_rekenig', 'no_bpjs_tk',
        'no_bpjs_kes', 'keterangan',
    ];

    private const DATE_FIELDS = [
        'tanggal_lahir', 'join_date', 'end_date', 'tgl_mutasi', 'tgl_resign',
    ];

    public function collection(\Illuminate\Support\Collection $rows): void
    {
        $prepared = $this->validateRows($rows);
        if (! $this->rowErrors) {
            $this->persist($prepared);
        }
    }

    public static function headers(): array
    {
        return self::HEADERS;
    }

    public function failed(): bool
    {
        return (bool) $this->rowErrors;
    }

    /**
     * Validate a chunk of rows without touching the database writes.
     * Array keys must be the original zero-based row indexes.
     */
    public function validateRows(iterable $rows): array
    {
        $prepared = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = is_array($row) ? $row : $row->toArray();

            $nik = $this->str($data['nik_karyawan'] ?? null);
            $email = strtolower($this->str($data['email'] ?? null));
            $name = $this->str($data['nama'] ?? null);
            $companyCode = $this->str($data['pt'] ?? null);
            $project = $this->str($data['project'] ?? null);
            $roleCode = $this->str($data['jabatan'] ?? null);

            if ($nik === '' && $email === '' && $name === '' && $companyCode === '' && $project === '') {
                continue;
            }

            $this->totalRows++;
            $who = $name !== '' ? $name : ($nik !== '' ? "NIK {$nik}" : ($email !== '' ? $email : 'tanpa nama'));
            $errorsBefore = count($this->rowErrors);

            if ($name === '') {
                $this->addError($line, $who, 'NAMA', "NAMA pegawai (NIK {$nik}) kosong");
            }
            if ($nik === '') {
                $this->addError($line, $who, 'NIK KARYAWAN', "NIK atas nama {$who} kosong");
            }
            if ($email === '') {
                $this->addError($line, $who, 'EMAIL', "EMAIL atas nama {$who} kosong");
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError($line, $who, 'EMAIL', "EMAIL {$email} atas nama {$who} tidak valid");
            }

            $company = null;
            if ($companyCode === '') {
                $this->addError($line, $who, 'PT', "PT atas nama {$who} kosong");
            } else {
                $company = Company::where('short_name', $companyCode)
                    ->orWhere('unique_id', $companyCode)
                    ->first();
                if (! $company) {
                    $this->addError($line, $who, 'PT', "PT {$companyCode} belum dibuat");
                }
            }

            if ($project === '') {
                $this->addError($line, $who, 'PROJECT', "SITE atas nama {$who} kosong");
            }

            if ($roleCode === '') {
                $this->addError($line, $who, 'JABATAN', "JABATAN atas nama {$who} kosong");
            }

            $dates = [];
            foreach (self::DATE_FIELDS as $key) {
                $dates[$key] = $this->date($data[$key] ?? null);
            }

            if (count($this->rowErrors) > $errorsBefore) {
                continue;
            }

            $prepared[] = [
                'data' => $data,
                'dates' => $dates,
                'name' => $name,
                'email' => $email,
                'nik' => $nik,
                'company_id' => $company->id,
                'project' => $project,
                'role_code' => $roleCode,
            ];
        }

        return $prepared;
    }

    public function persist(array $prepared): void
    {
        foreach ($prepared as $item) {
            $data = $item['data'];
            $area = $this->str($data['area_wilayah'] ?? null) ?: null;

            $site = Site::firstOrCreate(
                ['company_id' => $item['company_id'], 'name' => $item['project']],
                ['area' => $area]
            );

            if ($site->wasRecentlyCreated) {
                $this->sitesCreated++;
            } elseif ($area) {
                $site->update(['area' => $area]);
            }

            $payload = [
                'name' => $item['name'],
                'email' => $item['email'],
                'employee_nik' => $item['nik'],
                'nik' => $this->str($data['no_nik_ktp'] ?? null) ?: null,
                'phone' => $this->str($data['handphone'] ?? null) ?: null,
                'site_id' => $site->id,
                'is_employee' => 1,
            ];

            $user = User::where('email', $item['email'])->first()
                ?? User::where('employee_nik', $item['nik'])->first()
                ?? User::where('name', $item['name'])->whereNull('email')->first();

            if ($user && User::where('employee_nik', $item['nik'])->where('id', '!=', $user->id)->exists()) {
                unset($payload['employee_nik']);
            }

            if ($user) {
                $user->fill($payload)->save();
                $this->updated++;
            } else {
                $payload['password'] = Hash::make(Str::random(12));
                $user = User::create($payload);
                $this->created++;
            }

            $role = Role::where('guard_name', 'web')
                ->where(fn ($q) => $q->where('code', $item['role_code'])->orWhere('name', $item['role_code']))
                ->first();

            if (! $role) {
                $role = Role::create([
                    'name' => $item['role_code'],
                    'code' => strtoupper($item['role_code']),
                    'guard_name' => 'web',
                ]);
                $this->rolesCreated++;
            }

            $user->syncRoles([$role]);

            $isResign = strtoupper($this->str($data['status'] ?? '')) === 'RESIGN';

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employment_status' => $isResign ? 'RESIGN' : 'ACTIVE',
                    'mother_name' => $this->str($data['nama_ibu_kandung'] ?? null) ?: null,
                    'birth_place' => $this->str($data['tempat_lahir'] ?? null) ?: null,
                    'birth_date' => $item['dates']['tanggal_lahir'],
                    'gender' => $this->normalizeGender($data['jenis_kelamin'] ?? null),
                    'npwp_number' => $this->str($data['no_npwp'] ?? null) ?: null,
                    'no_kk' => $this->str($data['no_kk'] ?? null) ?: null,
                    'address' => $this->str($data['alamat_ktp'] ?? null) ?: null,
                    'religion' => $this->str($data['agama'] ?? null) ?: null,
                    'marriage_status' => $this->str($data['status_pernikahan'] ?? null) ?: null,
                    'manager' => $this->str($data['manager'] ?? null) ?: null,
                    'join_date' => $item['dates']['join_date'],
                    'end_date' => $item['dates']['end_date'],
                    'mutation_date' => $item['dates']['tgl_mutasi'],
                    'resign_date' => $isResign ? ($item['dates']['tgl_resign'] ?: now()->toDateString()) : $item['dates']['tgl_resign'],
                    'account_number' => $this->str($data['no_rekening'] ?? null) ?: null,
                    'bank_name' => $this->str($data['nama_bank'] ?? null) ?: null,
                    'account_name' => $this->str($data['nama_rekenig'] ?? null) ?: null,
                    'bpjs_tk_number' => $this->str($data['no_bpjs_tk'] ?? null) ?: null,
                    'bpjs_kes_number' => $this->str($data['no_bpjs_kes'] ?? null) ?: null,
                    'keterangan' => $this->str($data['keterangan'] ?? null) ?: null,
                ]
            );
        }
    }

    private function addError(int $line, string $who, string $field, string $message): void
    {
        $this->rowErrors[] = [
            'row' => $line,
            'employee' => $who,
            'field' => $field,
            'message' => $message,
        ];
    }

    private function str($value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function date($value): ?string
    {
        $value = $this->str($value);
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-m-y'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $value);
            if ($dt && $dt->format($fmt) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        return null;
    }

    private function normalizeGender($value): ?string
    {
        $v = strtoupper($this->str($value));

        return match ($v) {
            'P', 'PEREMPUAN', 'F', 'W', 'WANITA' => 'P',
            'L', 'LAKI-LAKI', 'LAKI', 'M', 'PRIA' => 'L',
            default => null,
        };
    }
}
