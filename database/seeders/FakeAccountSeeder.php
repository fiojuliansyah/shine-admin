<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FakeAccountSeeder extends Seeder
{
    public function run()
    {
        User::factory()
            ->count(20)
            ->has(Profile::factory()->state([
                'avatar_url' => null,
                'avatar_public_id' => null,
                'faceid_1' => null,
                'faceid_2' => null,
            ]))
            ->create([
                'site_id' => 1,
            ]);
    }
}
