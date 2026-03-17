<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            ['date' => '2026-01-01', 'name' => 'Nyårsdagen'],
            ['date' => '2026-01-06', 'name' => 'Trettondedag jul'],
            ['date' => '2026-04-03', 'name' => 'Långfredagen'],
            ['date' => '2026-04-05', 'name' => 'Påskdagen'],
            ['date' => '2026-04-06', 'name' => 'Annandag påsk'],
            ['date' => '2026-05-01', 'name' => 'Första maj'],
            ['date' => '2026-05-14', 'name' => 'Kristi himmelsfärdsdag'],
            ['date' => '2026-05-24', 'name' => 'Pingstdagen'],
            ['date' => '2026-06-06', 'name' => 'Sveriges nationaldag'],
            ['date' => '2026-06-19', 'name' => 'Midsommarafton'],
            ['date' => '2026-06-20', 'name' => 'Midsommardagen'],
            ['date' => '2026-10-31', 'name' => 'Alla helgons dag'],
            ['date' => '2026-12-24', 'name' => 'Julafton'],
            ['date' => '2026-12-25', 'name' => 'Juldagen'],
            ['date' => '2026-12-26', 'name' => 'Annandag jul'],
            ['date' => '2026-12-31', 'name' => 'Nyårsafton'],
        ];

        foreach ($holidays as $holiday) {
            \App\Models\Holiday::updateOrCreate(['date' => $holiday['date']], $holiday);
        }
    }
}
