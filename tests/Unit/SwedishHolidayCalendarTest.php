<?php

namespace Tests\Unit;

use App\Support\SwedishHolidayCalendar;
use PHPUnit\Framework\TestCase;

class SwedishHolidayCalendarTest extends TestCase
{
    public function test_it_generates_moveable_holidays_for_any_year(): void
    {
        $holidays = SwedishHolidayCalendar::forYear(2027);

        $this->assertSame('Nyårsdagen', $holidays->get('2027-01-01')['name']);
        $this->assertSame('Långfredagen', $holidays->get('2027-03-26')['name']);
        $this->assertSame('Påskdagen', $holidays->get('2027-03-28')['name']);
        $this->assertSame('Midsommarafton', $holidays->get('2027-06-25')['name']);
        $this->assertSame('Alla helgons dag', $holidays->get('2027-11-06')['name']);
    }

    public function test_it_keeps_expected_fixed_number_of_holidays(): void
    {
        $this->assertCount(16, SwedishHolidayCalendar::forYear(2030));
    }
}
