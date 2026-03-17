<?php

namespace Tests\Feature;

use App\Livewire\VacationPlanner;
use App\Models\Absence;
use App\Models\AbsenceOption;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_route_signs_in_as_the_first_user_when_no_user_is_in_session(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $firstUser = $department->users()->create(['name' => 'Asta First', 'location' => 'Stockholm']);
        $department->users()->create(['name' => 'Bertil Second', 'location' => 'Göteborg']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSessionHas('current_user_id', $firstUser->id);
        $response->assertSeeText('Asta First');
    }

    public function test_absence_request_is_pending_when_the_user_has_a_manager(): void
    {
        AbsenceOption::create(['code' => 'S', 'label' => 'Vacation', 'color' => '#4ade80', 'sort_order' => 1]);

        $department = Department::create(['name' => 'Operations']);
        $manager = $department->users()->create(['name' => 'Maja Manager', 'location' => 'Stockholm']);
        $employee = $department->users()->create(['name' => 'Emil Employee', 'location' => 'Stockholm', 'manager_id' => $manager->id]);

        session(['current_user_id' => $employee->id]);

        $component = Livewire::test(VacationPlanner::class)
            ->call('applyAbsence', $employee->id, ['2026-07-01', '2026-07-02'], 'S', 'Summer trip');

        $absence = Absence::query()->firstOrFail();

        $this->assertSame(Absence::STATUS_PENDING, $absence->status);
        $this->assertSame($employee->id, $absence->user_id);
        $this->assertNotNull($absence->request_uuid);

        $pendingRequests = $component->instance()->render()->getData()['pendingRequests'];
        $this->assertCount(1, $pendingRequests);

        session(['current_user_id' => $manager->id]);

        $managerComponent = Livewire::test(VacationPlanner::class);

        $managerApprovals = $managerComponent->instance()->render()->getData()['managerApprovals'];
        $this->assertCount(1, $managerApprovals);

        $managerComponent->call('approveRequest', $absence->request_uuid);

        $absence->refresh();

        $this->assertSame(Absence::STATUS_APPROVED, $absence->status);
        $this->assertSame($manager->id, $absence->approved_by);
        $this->assertNotNull($absence->approved_at);
    }

    public function test_absence_request_is_auto_approved_when_the_user_has_no_manager(): void
    {
        AbsenceOption::create(['code' => 'S', 'label' => 'Vacation', 'color' => '#4ade80', 'sort_order' => 1]);

        $department = Department::create(['name' => 'Sales']);
        $user = $department->users()->create(['name' => 'Solo User', 'location' => 'Malmö']);

        session(['current_user_id' => $user->id]);

        Livewire::test(VacationPlanner::class)
            ->call('applyAbsence', $user->id, ['2026-08-04'], 'S', 'One day off');

        $absence = Absence::query()->firstOrFail();

        $this->assertSame(Absence::STATUS_APPROVED, $absence->status);
        $this->assertSame($user->id, $absence->approved_by);
    }

    public function test_pending_absence_request_can_be_updated_by_the_request_owner(): void
    {
        AbsenceOption::create(['code' => 'S', 'label' => 'Vacation', 'color' => '#4ade80', 'sort_order' => 1]);
        AbsenceOption::create(['code' => 'B', 'label' => 'Parental leave', 'color' => '#facc15', 'sort_order' => 2]);

        $department = Department::create(['name' => 'Support']);
        $manager = $department->users()->create(['name' => 'Maja Manager', 'location' => 'Stockholm']);
        $employee = $department->users()->create(['name' => 'Ella Employee', 'location' => 'Stockholm', 'manager_id' => $manager->id]);

        session(['current_user_id' => $employee->id]);

        Livewire::test(VacationPlanner::class)
            ->call('applyAbsence', $employee->id, ['2026-09-01', '2026-09-02'], 'S', 'Initial request');

        $requestUuid = Absence::query()->value('request_uuid');

        Livewire::test(VacationPlanner::class)
            ->call('startEditingRequest', $requestUuid)
            ->set('editingRequestStartDate', '2026-09-03')
            ->set('editingRequestEndDate', '2026-09-05')
            ->set('editingRequestType', 'B')
            ->set('editingRequestReason', 'Updated request')
            ->call('updatePendingRequest')
            ->assertSet('editingRequestUuid', null);

        $updatedAbsences = Absence::query()->orderBy('date')->get();

        $this->assertCount(3, $updatedAbsences);
        $this->assertSame(['2026-09-03', '2026-09-04', '2026-09-05'], $updatedAbsences->pluck('date')->all());
        $this->assertSame(['B'], $updatedAbsences->pluck('type')->unique()->values()->all());
        $this->assertSame(['Updated request'], $updatedAbsences->pluck('reason')->unique()->values()->all());
        $this->assertSame([$requestUuid], $updatedAbsences->pluck('request_uuid')->unique()->values()->all());
        $this->assertSame([Absence::STATUS_PENDING], $updatedAbsences->pluck('status')->unique()->values()->all());
    }

    public function test_pending_absence_request_can_be_deleted_by_the_request_owner(): void
    {
        AbsenceOption::create(['code' => 'S', 'label' => 'Vacation', 'color' => '#4ade80', 'sort_order' => 1]);

        $department = Department::create(['name' => 'Finance']);
        $manager = $department->users()->create(['name' => 'Maja Manager', 'location' => 'Stockholm']);
        $employee = $department->users()->create(['name' => 'Dana Employee', 'location' => 'Stockholm', 'manager_id' => $manager->id]);

        session(['current_user_id' => $employee->id]);

        Livewire::test(VacationPlanner::class)
            ->call('applyAbsence', $employee->id, ['2026-10-11', '2026-10-12'], 'S', 'Needs review');

        $requestUuid = Absence::query()->value('request_uuid');

        Livewire::test(VacationPlanner::class)
            ->call('deletePendingRequest', $requestUuid);

        $this->assertDatabaseCount('absences', 0);
    }
}
