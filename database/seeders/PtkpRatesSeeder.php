<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PtkpRatesSeeder extends Seeder
{
    public function run()
    {
        $ptkpRates = [
            ['status' => 'TK-0', 'amount' => 54000000],
            ['status' => 'TK-1', 'amount' => 58500000],
            ['status' => 'TK-2', 'amount' => 63000000],
            ['status' => 'TK-3', 'amount' => 67500000],
            ['status' => 'K-0', 'amount' => 58500000],
            ['status' => 'K-1', 'amount' => 63000000],
            ['status' => 'K-2', 'amount' => 67500000],
            ['status' => 'K-3', 'amount' => 72000000],
        ];

        foreach ($ptkpRates as $rate) {
            DB::table('ptkp_rates')->insert([
                'status' => $rate['status'],
                'amount' => $rate['amount'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
