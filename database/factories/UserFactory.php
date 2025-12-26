<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password
            'nik' => $this->faker->unique()->numerify('##########'),
            'employee_nik' => $this->faker->unique()->numerify('EMP########'),
            'phone' => $this->faker->phoneNumber,
            'site_id' => 3, // Sesuai permintaan
            'leader_id' => 1,
            'department_id' => null,
            'is_employee' => 1,
            'remember_token' => Str::random(10),
        ];
    }
}
