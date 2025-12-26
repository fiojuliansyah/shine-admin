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

        $leaderName = $row[6] ?? null;
        $leaderId = null;

        if ($leaderName) {
            $leader = User::where('name', $leaderName)->first();
            if ($leader) {
                $leaderId = $leader->id;
            }
        }

        if ($user) {
            
            $user->update([
                'nik' => $row[7],
                'employee_nik' => $row[0],
                'name' => $row[1],
                'phone' => $row[2],
                'password' => bcrypt($row[4]),
                'site_id' => $this->siteId,
                'department_id' => 1,
                'is_employee' => 1,
                'leader_id' => $leaderId,
            ]);
        } else {
            
            $user = User::create([
                'nik' => $row[7],
                'employee_nik' => $row[0],
                'name' => $row[1],
                'phone' => $row[2],
                'email' => $row[3],
                'password' => bcrypt($row[4]),
                'site_id' => $this->siteId,
                'department_id' => 1,
                'is_employee' => 1,
                'leader_id' => $leaderId,
            ]);
        }

        
        $roleIdentifier = $row[5]; 

        
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
                'address' => $row[8],
                'birth_place' => $row[9],
                'birth_date' => Date::excelToDateTimeObject($row[10])->format('Y-m-d'),
                'marriage_status' => $row[11],
                'mother_name' => $row[12],
                'gender' => $row[13],
                'weight' => $row[14],
                'height' => $row[15],
                'bank_name' => $row[16],
                'account_name' => $row[17],
                'account_number' => $row[18],
                'npwp_number' => $row[19],
            ]
        );

        return $user;
    }

    public function startRow(): int
    {
        return 2;
    }
}
