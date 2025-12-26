<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'avatar_url' => null, // Avatar null sesuai permintaan
            'avatar_public_id' => null,
            'faceid_1' => null, // Face ID null sesuai permintaan
            'faceid_2' => null,
            'esign_url' => null,
            'esign_public_id' => null,
            'gender' => $this->faker->randomElement(['laki-laki', 'perempuan']),
            'birth_place' => $this->faker->city,
            'birth_date' => $this->faker->date,
            'mother_name' => $this->faker->name('female'),
            'npwp_number' => $this->faker->numerify('##.###.###.#-###.###'),
            'marriage_status' => $this->faker->randomElement(['TK-0', 'TK-1', 'TK-2', 'TK-3', 'K-0', 'K-1', 'K-2', 'K-3']),
            'address' => $this->faker->address,
            'join_date' => $this->faker->date,
            'resign_date' => null,
            'bank_name' => $this->faker->company,
            'account_name' => $this->faker->name,
            'account_number' => $this->faker->numerify('##########'),
        ];
    }
}
