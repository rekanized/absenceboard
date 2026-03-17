<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Department;
use App\Models\User;
use App\Models\Absence;
use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class VacationPlanner extends Component
{
    public string $viewDate;

    public string $selectedSite = '';
    public string $selectedDept = '';
    public ?string $absenceType = 'S';

    public string $reason = '';

    public function mount()
    {
        $this->viewDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    }

    public function previousMonth()
    {
        $this->viewDate = Carbon::parse($this->viewDate)->subMonth()->format('Y-m-d');
    }

    public function nextMonth()
    {
        $this->viewDate = Carbon::parse($this->viewDate)->addMonth()->format('Y-m-d');
    }

    public function goToToday()
    {
        $this->viewDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    }



    public function render()
    {
        $start = Carbon::parse($this->viewDate);
        $end = $start->copy()->addMonths(2)->endOfMonth();
        
        $period = CarbonPeriod::create($start, $end);
        
        $holidays = Holiday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])

            ->get()
            ->keyBy('date');

        $dates = collect($period)->map(function($date) use ($holidays) {
            $dateStr = $date->format('Y-m-d');
            $holiday = $holidays->get($dateStr);
            
            return [
                'date' => $dateStr,
                'day' => $date->format('j'),
                'month' => $date->translatedFormat('M'),
                'week' => $date->isoWeek(),
                'is_weekend' => $date->isWeekend(),
                'holiday_name' => $holiday?->name,
                'is_holiday' => !!$holiday,
            ];
        });

        $weeks = $dates->groupBy('week');

        $sites = User::distinct()->pluck('location')->sort();
        $allDepartments = Department::orderBy('name')->pluck('name');

        $departments = Department::with(['users' => function ($query) use ($start, $end) {
            if ($this->selectedSite) {
                $query->where('location', $this->selectedSite);
            }
            $query->with(['absences' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
            }]);
        }])
        ->when($this->selectedDept, function ($query) {
            $query->where('name', $this->selectedDept);
        })
        ->get()
        ->filter(function ($dept) {
            return $dept->users->isNotEmpty();
        });

        return view('livewire.vacation-planner', [
            'dates' => $dates,
            'weeks' => $weeks,
            'departments' => $departments,
            'sites' => $sites,
            'allDepartments' => $allDepartments,
        ])->layout('layouts.app');


    }

    public function setAbsenceType(string $type)
    {
        $this->absenceType = $type;
    }

    public function applyAbsence(int $userId, array $dateRange, string $type, string $reason)
    {
        foreach ($dateRange as $date) {
            Absence::updateOrCreate(
                ['user_id' => $userId, 'date' => $date],
                ['type' => $type, 'reason' => $reason]
            );
        }
        
        $this->reason = '';
    }

    public function removeAbsence(int $userId, array $dateRange)
    {
        Absence::where('user_id', $userId)
            ->whereIn('date', $dateRange)
            ->delete();
    }
}
