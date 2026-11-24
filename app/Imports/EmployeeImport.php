<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Profile;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Spatie\Permission\Models\Role;

class EmployeeImport implements ToModel, WithStartRow
{
    protected $siteId;

    public function __construct($siteId)
    {
        $this->siteId = $siteId;
    }

    public function model(array $row)
    {
        if ($row[0] === 'employee_nik' && $row[3] === 'email') {
            return null;
        }

        if ($row[0] === null) {
            return null;
        }
        
        // Kolom: 0 NIK karyawan, 1 nama, 2 HP, 3 email, 4 role, 5 NIK KTP, 6 alamat, 7 tempat lahir, 8 tgl lahir, ...
        // Hanya cocokkan berdasarkan kolom yang terisi: where('x', null) menjadi "x IS NULL"
        // dan bisa mencocokkan user lain yang tidak berhubungan.
        $lookups = array_filter([
            'nik' => trim((string) ($row[5] ?? '')),
            'employee_nik' => trim((string) ($row[0] ?? '')),
            'email' => strtolower(trim((string) ($row[3] ?? ''))),
        ], fn ($v) => $v !== '');

        $user = User::where(function ($q) use ($lookups) {
            foreach ($lookups as $column => $value) {
                $q->orWhere($column, $value);
            }
        })->first();

        if ($user) {
            
            $user->update([
                'nik' => $row[5],
                'employee_nik' => $row[0],
                'name' => $row[1],
                'phone' => $row[2],
                // Password user yang sudah ada TIDAK direset oleh import.
                'site_id' => $this->siteId,
                'department_id' => 1,
                'is_employee' => 1,
                'leader_id' => null,
            ]);
        } else {
            
            $user = User::create([
                'nik' => $row[5],
                'employee_nik' => $row[0],
                'name' => $row[1],
                'phone' => $row[2],
                'email' => strtolower($row[3]),
                'password' => bcrypt('123456'),
                'site_id' => $this->siteId,
                'department_id' => 1,
                'is_employee' => 1,
                'leader_id' => null,
            ]);
        }

        
        $roleIdentifier = $row[4]; 

        
        if (is_numeric($roleIdentifier)) {
            $role = Role::findById($roleIdentifier);
        } else {
            $role = Role::findByName($roleIdentifier, 'web'); 
        }

        
        if ($role) {
            $user->syncRoles([$role]); 
        } else {
            
            
            throw new \Exception("Role not found: $roleIdentifier");
        }

        
        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $row[6],
                'birth_place' => $row[7],
                'birth_date' => $this->parseDate($row[8] ?? null),
                'marriage_status' => $row[9],
                'mother_name' => $row[10],
                'gender' => $row[11],
                'weight' => $row[12],
                'height' => $row[13],
                'bank_name' => $row[14],
                'account_name' => $row[15],
                'account_number' => $row[16],
                'npwp_number' => $row[17],
            ]
        );

        return $user;
    }

    /** Tanggal bisa berupa angka serial Excel atau teks (DD-MM-YYYY / YYYY-MM-DD); kosong -> null. */
    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            $value = trim((string) $value);
            if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $value, $m)) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }

            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
