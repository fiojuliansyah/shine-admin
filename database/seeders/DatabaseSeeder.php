<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PtkpRatesSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\TaxBracketsSeeder;
use Database\Seeders\EffectiveRatesSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('generate_payrolls')->insert([
            'user_id' => 1,
            'site_id' => 1,
            'payroll_id' => 1,
            'start_date' => Carbon::parse('2025-07-01'),
            'end_date' => Carbon::parse('2025-07-31'),

            'salary' => 5000000,
            'allowance_fix' => 1000000,
            'allowance_non_fix' => 250000,
            'deduction_fix' => 200000,
            'deduction_non_fix' => 100000,
            'late_time_deduction' => 50000,
            'alpha_time_deduction' => 0,
            'permit_time_deduction' => 0,
            'leave_time_deduction' => 0,
            'overtime_amount' => 300000,

            // BPJS (persentase nominal)
            'jkk_company' => 75000.00,
            'jkm_company' => 20000.00,
            'jht_company' => 100000.00,
            'jht_employee' => 100000.00,
            'jp_company' => 50000.00,
            'jp_employee' => 50000.00,
            'kes_company' => 80000.00,
            'kes_employee' => 80000.00,

            // Pajak
            'pph21' => 120000,
            'pph21_monthly' => 120000.00,

            // THP
            'take_home_pay' => '5980000',

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->call(PermissionSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(PtkpRatesSeeder::class);
        $this->call(TaxBracketsSeeder::class);
        $this->call(EffectiveRatesSeeder::class);
    }
}
