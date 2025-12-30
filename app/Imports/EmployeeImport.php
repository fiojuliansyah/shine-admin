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
        
        $user = User::where('nik', $row[7])
                    ->orWhere('employee_nik', $row[0])
                    ->orWhere('email', $row[3])
                    ->first();

        if ($user) {
            
            $user->update([
                'nik' => $row[7],
                'employee_nik' => $row[0],
                'name' => $row[1],
                'phone' => $row[2],
                'password' => bcrypt('123456'),
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
                'birth_date' => Date::excelToDateTimeObject($row[8])->format('Y-m-d'),
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

    public function startRow(): int
    {
        return 2;
    }
}
