<?php

namespace Tests\Feature;

use App\Livewire\VacationPlanner;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VacationPlannerFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_filter_supports_multiple_selected_departments(): void
    {
        $operations = Department::create(['name' => 'Operations']);
        $engineering = Department::create(['name' => 'Engineering']);
        $sales = Department::create(['name' => 'Sales']);

        $operations->users()->create(['name' => 'Olivia Ops', 'location' => 'Stockholm']);
        $engineering->users()->create(['name' => 'Elias Eng', 'location' => 'Göteborg']);
        $sales->users()->create(['name' => 'Sara Sales', 'location' => 'Malmö']);

        $component = Livewire::test(VacationPlanner::class)
            ->set('viewDate', '2026-03-01')
            ->set('selectedDepartments', ['Operations', 'Sales']);

        $departments = $component->instance()->render()->getData()['departments'];

        $this->assertSame(['Operations', 'Sales'], $departments->pluck('name')->all());
    }

    public function test_site_filter_supports_multiple_selected_sites_and_limits_users(): void
    {
        $operations = Department::create(['name' => 'Operations']);
        $engineering = Department::create(['name' => 'Engineering']);

        $operations->users()->create(['name' => 'Olivia Ops', 'location' => 'Stockholm']);
        $operations->users()->create(['name' => 'Mark Ops', 'location' => 'Umeå']);
        $engineering->users()->create(['name' => 'Elias Eng', 'location' => 'Göteborg']);
        $engineering->users()->create(['name' => 'Nora Eng', 'location' => 'Lund']);

        $component = Livewire::test(VacationPlanner::class)
            ->set('viewDate', '2026-03-01')
            ->set('selectedSites', ['Stockholm', 'Lund']);

        $departments = $component->instance()->render()->getData()['departments'];

        $this->assertSame(['Engineering', 'Operations'], $departments->pluck('name')->all());
        $this->assertSame(['Nora Eng'], $departments->firstWhere('name', 'Engineering')->users->pluck('name')->all());
        $this->assertSame(['Olivia Ops'], $departments->firstWhere('name', 'Operations')->users->pluck('name')->all());
    }
}
