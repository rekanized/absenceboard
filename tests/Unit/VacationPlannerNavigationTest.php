<?php

namespace Tests\Unit;

use App\Livewire\VacationPlanner;
use PHPUnit\Framework\TestCase;

class VacationPlannerNavigationTest extends TestCase
{
    public function test_it_can_navigate_across_year_boundaries(): void
    {
        $planner = new VacationPlanner();
        $planner->viewDate = '2026-12-01';

        $planner->nextMonth();
        $this->assertSame('2027-01-01', $planner->viewDate);

        $planner->previousYear();
        $this->assertSame('2026-01-01', $planner->viewDate);

        $planner->nextYear();
        $this->assertSame('2027-01-01', $planner->viewDate);
    }
}
