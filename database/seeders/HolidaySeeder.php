<?php

namespace Database\Seeders;

use App\Support\SwedishHolidayCalendar;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = Carbon::now()->year;
        $holidays = collect(range($currentYear - 5, $currentYear + 5))
            ->flatMap(fn (int $year) => SwedishHolidayCalendar::forYear($year))
            ->values();

        foreach ($holidays as $holiday) {
            \App\Models\Holiday::updateOrCreate(['date' => $holiday['date']], $holiday);
        }
    }
}
