<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'name' => 'role-list',
                'mock' => 'List',
                'category' => 'Roles Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'role-create',
                'mock' => 'Buat',
                'category' => 'Roles Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'role-edit',
                'mock' => 'Ubah',
                'category' => 'Roles Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'role-delete',
                'mock' => 'Hapus',
                'category' => 'Roles Management',
                'guard_name' => 'web',
                'status' => '1',
            ],


            [
                'name' => 'permission-list',
                'mock' => 'List',
                'category' => 'Permissions Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'permission-create',
                'mock' => 'Buat',
                'category' => 'Permissions Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'permission-edit',
                'mock' => 'Ubah',
                'category' => 'Permissions Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'permission-delete',
                'mock' => 'Hapus',
                'category' => 'Permissions Management',
                'guard_name' => 'web',
                'status' => '1',
            ],


            [
                'name' => 'user-list',
                'mock' => 'List',
                'category' => 'Users Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'user-create',
                'mock' => 'Buat',
                'category' => 'Users Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'user-edit',
                'mock' => 'Ubah',
                'category' => 'Users Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'user-delete',
                'mock' => 'Hapus',
                'category' => 'Users Management',
                'guard_name' => 'web',
                'status' => '1',
            ],


            [
                'name' => 'site-list',
                'mock' => 'List',
                'category' => 'Sites Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'site-create',
                'mock' => 'Buat',
                'category' => 'Sites Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'site-edit',
                'mock' => 'Ubah',
                'category' => 'Sites Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'site-delete',
                'mock' => 'Hapus',
                'category' => 'Sites Management',
                'guard_name' => 'web',
                'status' => '1',
            ],

            [
                'name' => 'attendance-list',
                'mock' => 'List',
                'category' => 'Attendances Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'attendance-create',
                'mock' => 'Buat',
                'category' => 'Attendances Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'attendance-edit',
                'mock' => 'Ubah',
                'category' => 'Attendances Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'attendance-delete',
                'mock' => 'Hapus',
                'category' => 'Attendances Management',
                'guard_name' => 'web',
                'status' => '1',
            ],

            [
                'name' => 'overtime-list',
                'mock' => 'List',
                'category' => 'Overtimes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'overtime-create',
                'mock' => 'Buat',
                'category' => 'Overtimes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'overtime-edit',
                'mock' => 'Ubah',
                'category' => 'Overtimes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'overtime-delete',
                'mock' => 'Hapus',
                'category' => 'Overtimes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'minute-list',
                'mock' => 'List',
                'category' => 'Minutes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'minute-create',
                'mock' => 'Buat',
                'category' => 'Minutes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'minute-edit',
                'mock' => 'Ubah',
                'category' => 'Minutes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'minute-delete',
                'mock' => 'Hapus',
                'category' => 'Minutes Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'leave-list',
                'mock' => 'List',
                'category' => 'Leaves Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'leave-create',
                'mock' => 'Buat',
                'category' => 'Leaves Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'leave-edit',
                'mock' => 'Ubah',
                'category' => 'Leaves Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'leave-delete',
                'mock' => 'Hapus',
                'category' => 'Leaves Management',
                'guard_name' => 'web',
                'status' => '1',
            ],

            [
                'name' => 'reliver-list',
                'mock' => 'List',
                'category' => 'Relivers Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'reliver-create',
                'mock' => 'Buat',
                'category' => 'Relivers Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'reliver-edit',
                'mock' => 'Ubah',
                'category' => 'Relivers Management',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'reliver-delete',
                'mock' => 'Hapus',
                'category' => 'Relivers Management',
                'guard_name' => 'web',
                'status' => '1',
            ],

            [
                'name' => 'attendance-module',
                'mock' => 'Attendance',
                'category' => 'Modules',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'work-module',
                'mock' => 'Work',
                'category' => 'Modules',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'cleaning-app',
                'mock' => 'Cleaning',
                'category' => 'Mobile App',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'security-app',
                'mock' => 'Security',
                'category' => 'Mobile App',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'civil-app',
                'mock' => 'Civil',
                'category' => 'Mobile App',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'reliver-app',
                'mock' => 'Reliver',
                'category' => 'Mobile App',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'view-mobile',
                'mock' => 'Mobile',
                'category' => 'View',
                'guard_name' => 'web',
                'status' => '1',
            ],
            [
                'name' => 'view-desktop',
                'mock' => 'Desktop',
                'category' => 'View',
                'guard_name' => 'web',
                'status' => '1',
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }
    }
}