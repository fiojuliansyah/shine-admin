<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxBracketsSeeder extends Seeder
{
    public function run()
    {
        $taxBrackets = [
            ['lower_limit' => 0, 'upper_limit' => 50000000, 'rate' => 5],
            ['lower_limit' => 50000001, 'upper_limit' => 250000000, 'rate' => 15],
            ['lower_limit' => 250000001, 'upper_limit' => 500000000, 'rate' => 25],
            ['lower_limit' => 500000001, 'upper_limit' => null, 'rate' => 30],
        ];

        foreach ($taxBrackets as $bracket) {
            DB::table('tax_brackets')->insert([
                'lower_limit' => $bracket['lower_limit'],
                'upper_limit' => $bracket['upper_limit'],
                'rate' => $bracket['rate'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
