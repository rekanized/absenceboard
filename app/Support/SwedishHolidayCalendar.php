<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SwedishHolidayCalendar
{
    public static function forYear(int $year): Collection
    {
        $easterSunday = Carbon::createMidnightDate($year, 3, 21)->addDays(easter_days($year));
        $midsummerEve = self::firstWeekdayOnOrAfter(Carbon::createMidnightDate($year, 6, 19), Carbon::FRIDAY);
        $midsummerDay = self::firstWeekdayOnOrAfter(Carbon::createMidnightDate($year, 6, 20), Carbon::SATURDAY);
        $allSaintsDay = self::firstWeekdayOnOrAfter(Carbon::createMidnightDate($year, 10, 31), Carbon::SATURDAY);

        return collect([
            ['date' => Carbon::createMidnightDate($year, 1, 1)->format('Y-m-d'), 'name' => 'Nyårsdagen'],
            ['date' => Carbon::createMidnightDate($year, 1, 6)->format('Y-m-d'), 'name' => 'Trettondedag jul'],
            ['date' => $easterSunday->copy()->subDays(2)->format('Y-m-d'), 'name' => 'Långfredagen'],
            ['date' => $easterSunday->format('Y-m-d'), 'name' => 'Påskdagen'],
            ['date' => $easterSunday->copy()->addDay()->format('Y-m-d'), 'name' => 'Annandag påsk'],
            ['date' => Carbon::createMidnightDate($year, 5, 1)->format('Y-m-d'), 'name' => 'Första maj'],
            ['date' => $easterSunday->copy()->addDays(39)->format('Y-m-d'), 'name' => 'Kristi himmelsfärdsdag'],
            ['date' => $easterSunday->copy()->addDays(49)->format('Y-m-d'), 'name' => 'Pingstdagen'],
            ['date' => Carbon::createMidnightDate($year, 6, 6)->format('Y-m-d'), 'name' => 'Sveriges nationaldag'],
            ['date' => $midsummerEve->format('Y-m-d'), 'name' => 'Midsommarafton'],
            ['date' => $midsummerDay->format('Y-m-d'), 'name' => 'Midsommardagen'],
            ['date' => $allSaintsDay->format('Y-m-d'), 'name' => 'Alla helgons dag'],
            ['date' => Carbon::createMidnightDate($year, 12, 24)->format('Y-m-d'), 'name' => 'Julafton'],
            ['date' => Carbon::createMidnightDate($year, 12, 25)->format('Y-m-d'), 'name' => 'Juldagen'],
            ['date' => Carbon::createMidnightDate($year, 12, 26)->format('Y-m-d'), 'name' => 'Annandag jul'],
            ['date' => Carbon::createMidnightDate($year, 12, 31)->format('Y-m-d'), 'name' => 'Nyårsafton'],
        ])->keyBy('date');
    }

    private static function firstWeekdayOnOrAfter(Carbon $date, int $weekday): Carbon
    {
        while ($date->dayOfWeek !== $weekday) {
            $date->addDay();
        }

        return $date;
    }
}
