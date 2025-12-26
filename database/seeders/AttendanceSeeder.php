<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $startDate = Carbon::create(2025, 7, 1);
        $endDate = Carbon::create(2025, 7, 31);

        while ($startDate->lte($endDate)) {
            foreach (range(2, 16) as $userId) {
                $siteId = 1;
                
                $type = ($startDate->dayOfWeek === 0) ? 'off' : 'regular';

                if ($type === 'regular') {
                    $clockIn = $startDate->copy()->setTime(8, 0, 0);
                    $clockOut = $startDate->copy()->setTime(17, 30, 0);
                } else {
                    $clockIn = null;
                    $clockOut = null;
                }

                Attendance::factory()->create([
                    'user_id' => $userId,
                    'site_id' => $siteId,
                    'date' => $startDate->format('Y-m-d'),
                    'type' => $type,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);
            }
            $startDate->addDay();
        }
    }
}
