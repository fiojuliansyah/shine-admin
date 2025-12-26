<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $clockIn = $this->faker->time('H:i:s', '07:00:00');
        $clockOut = Carbon::createFromFormat('H:i:s', $clockIn)->addHours(8)->format('H:i:s');

        return [
            'date' => now()->format('Y-m-d'),
            'latlong' => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'user_id' => $this->faker->numberBetween(2, 31),
            'site_id' => $this->faker->randomElement([1, 2]),
            'face_image_url_clockin' => 'https://as1.ftcdn.net/v2/jpg/00/67/73/14/1000_F_67731473_GAsdRUCBh7XEhM3X0tpzbIYDgHirJAgP.jpg',
            'face_image_public_id_clockin' => null,
            'clock_in' => $clockIn,
            'face_image_url_clockout' => 'https://as1.ftcdn.net/v2/jpg/00/67/73/14/1000_F_67731473_GAsdRUCBh7XEhM3X0tpzbIYDgHirJAgP.jpg',
            'face_image_public_id_clockout' => null,
            'clock_out' => $clockOut,
            'type' => $this->faker->randomElement(['regular', 'off']),
            'is_reliver' => null,
            'backup_id' => null,
            'remark' => null,
        ];
    }
}
