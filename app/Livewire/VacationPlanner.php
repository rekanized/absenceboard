<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AbsenceOption;
use App\Models\Department;
use App\Models\User;
use App\Models\Absence;
use App\Models\Holiday;
use App\Support\SwedishHolidayCalendar;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VacationPlanner extends Component
{
    public string $viewDate;
    public ?int $currentUserId = null;

    public array $selectedSites = [];
    public array $selectedDepartments = [];
    public string $search = '';
    public ?string $absenceType = null;

    public string $reason = '';
    public ?string $editingRequestUuid = null;
    public ?string $editingRequestStartDate = null;
    public ?string $editingRequestEndDate = null;
    public ?string $editingRequestType = null;
    public string $editingRequestReason = '';

    public function mount(): void
    {
        $this->syncCurrentUser();
        $this->viewDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->syncAbsenceType();
    }

    public function previousMonth(): void
    {
        $this->viewDate = Carbon::parse($this->viewDate)->subMonth()->format('Y-m-d');
    }

    public function nextMonth(): void
    {
        $this->viewDate = Carbon::parse($this->viewDate)->addMonth()->format('Y-m-d');
    }

    public function previousYear(): void
    {
        $this->viewDate = Carbon::parse($this->viewDate)->subYear()->format('Y-m-d');
    }

    public function nextYear(): void
    {
        $this->viewDate = Carbon::parse($this->viewDate)->addYear()->format('Y-m-d');
    }

    public function goToToday(): void
    {
        $this->viewDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    }

    public function updatedSelectedDepartments(array $value): void
    {
        $this->selectedDepartments = $this->normalizeSelection(
            $value,
            Department::query()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function updatedSelectedSites(array $value): void
    {
        $this->selectedSites = $this->normalizeSelection(
            $value,
            User::query()->whereNotNull('location')->orderBy('location')->distinct()->pluck('location')->all(),
        );
    }



    public function render()
    {
        $this->syncCurrentUser();
        $this->syncAbsenceType();
        $searchTerm = trim($this->search);

        $start = Carbon::parse($this->viewDate)->startOfMonth();
        $end = $start->copy()->addMonths(2)->endOfMonth();

        $period = CarbonPeriod::create($start, $end);

        $holidays = $this->holidaysForRange($start, $end);

        $dates = collect($period)->map(function ($date) use ($holidays) {
            $dateStr = $date->format('Y-m-d');
            $holiday = $holidays->get($dateStr);

            return [
                'date' => $dateStr,
                'day' => $date->format('j'),
                'month' => $date->translatedFormat('M'),
                'week_key' => sprintf('%s-W%02d', $date->isoWeekYear(), $date->isoWeek()),
                'week' => $date->isoWeek(),
                'week_year' => $date->isoWeekYear(),
                'is_weekend' => $date->isWeekend(),
                'holiday_name' => $holiday['name'] ?? null,
                'is_holiday' => !!$holiday,
            ];
        });

        $weeks = $dates->groupBy('week_key');

        $sites = User::query()
            ->whereNotNull('location')
            ->orderBy('location')
            ->distinct()
            ->pluck('location')
            ->values();

        $allDepartments = Department::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $selectedSites = $this->normalizeSelection($this->selectedSites, $sites->all());
        $selectedDepartments = $this->normalizeSelection($this->selectedDepartments, $allDepartments->all());

        $this->selectedSites = $selectedSites;
        $this->selectedDepartments = $selectedDepartments;

        $currentUser = User::query()
            ->with('manager')
            ->find($this->currentUserId);

        $absenceOptions = AbsenceOption::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $absenceOptionsByCode = $absenceOptions->keyBy('code');

        $departments = Department::with(['users' => function ($query) use ($start, $end, $searchTerm) {
            if ($this->selectedSites !== []) {
                $query->whereIn('location', $this->selectedSites);
            }

            if ($searchTerm !== '') {
                $query->where('name', 'like', "%{$searchTerm}%");
            }

            $query->orderBy('name');

            $query->with(['absences' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->whereIn('status', [Absence::STATUS_APPROVED, Absence::STATUS_PENDING]);
            }]);

            $query->with('manager');
        }])
        ->when($selectedDepartments !== [], function ($query) use ($selectedDepartments) {
            $query->whereIn('name', $selectedDepartments);
        })
        ->when($selectedSites !== [], function ($query) use ($selectedSites) {
            $query->whereHas('users', function ($userQuery) use ($selectedSites) {
                $userQuery->whereIn('location', $selectedSites);
            });
        })
        ->when($searchTerm !== '', function ($query) use ($searchTerm) {
            $query->whereHas('users', function ($userQuery) use ($searchTerm) {
                $userQuery->where('name', 'like', "%{$searchTerm}%");
            });
        })
        ->orderBy('name')
        ->get()
        ->filter(function ($dept) {
            return $dept->users->isNotEmpty();
        })
        ->values()
        ->map(function ($dept) {
            $dept->setAttribute(
                'day_counts',
                $dept->users
                    ->flatMap(fn ($user) => $user->absences->pluck('date'))
                    ->countBy()
            );

            return $dept;
        });

        return view('livewire.vacation-planner', [
            'dates' => $dates,
            'weeks' => $weeks,
            'departments' => $departments,
            'sites' => $sites,
            'allDepartments' => $allDepartments,
            'periodLabel' => $this->formatPeriodLabel($start, $end),
            'currentUser' => $currentUser,
            'absenceOptions' => $absenceOptions,
            'absenceOptionsByCode' => $absenceOptionsByCode,
            'pendingRequests' => $this->pendingRequestsForCurrentUser(),
            'managerApprovals' => $this->pendingRequestsForManager(),
        ])->layout('layouts.app');
    }

    public function setAbsenceType(string $type): void
    {
        if (! AbsenceOption::query()->where('code', $type)->exists()) {
            return;
        }

        $this->absenceType = $type;
    }

    public function applyAbsence(int $userId, array $dateRange, string $type, string $reason): void
    {
        $this->syncCurrentUser();

        if ($this->currentUserId === null || $this->currentUserId !== $userId) {
            return;
        }

        if (! AbsenceOption::query()->where('code', $type)->exists()) {
            return;
        }

        $currentUser = User::query()->find($this->currentUserId);

        if (! $currentUser) {
            return;
        }

        $dates = $this->normalizeDates($dateRange);

        if ($dates === []) {
            return;
        }

        $status = $currentUser->manager_id ? Absence::STATUS_PENDING : Absence::STATUS_APPROVED;
        $requestUuid = (string) Str::uuid();
        $trimmedReason = trim($reason) !== '' ? trim($reason) : null;

        foreach ($dates as $date) {
            Absence::query()->updateOrCreate(
                ['user_id' => $userId, 'date' => $date],
                [
                    'type' => $type,
                    'reason' => $trimmedReason,
                    'status' => $status,
                    'request_uuid' => $requestUuid,
                    'approved_by' => $status === Absence::STATUS_APPROVED ? $this->currentUserId : null,
                    'approved_at' => $status === Absence::STATUS_APPROVED ? now() : null,
                ]
            );
        }

        $this->reason = '';
        $this->absenceType = $type;
    }

    public function removeAbsence(int $userId, array $dateRange): void
    {
        $this->syncCurrentUser();

        if ($this->currentUserId === null || $this->currentUserId !== $userId) {
            return;
        }

        $dates = $this->normalizeDates($dateRange);

        if ($dates === []) {
            return;
        }

        Absence::query()
            ->where('user_id', $userId)
            ->whereIn('date', $dates)
            ->delete();
    }

    public function approveRequest(string $requestUuid): void
    {
        $this->updateApprovalStatus($requestUuid, Absence::STATUS_APPROVED);
    }

    public function rejectRequest(string $requestUuid): void
    {
        $this->updateApprovalStatus($requestUuid, Absence::STATUS_REJECTED);
    }

    public function startEditingRequest(string $requestUuid): void
    {
        $this->syncCurrentUser();

        $requestAbsences = $this->pendingRequestAbsencesForCurrentUser($requestUuid);

        if ($requestAbsences->isEmpty()) {
            return;
        }

        /** @var Absence|null $firstAbsence */
        $firstAbsence = $requestAbsences->first();
        /** @var Absence|null $lastAbsence */
        $lastAbsence = $requestAbsences->last();

        $this->editingRequestUuid = $requestUuid;
        $this->editingRequestStartDate = $firstAbsence?->date;
        $this->editingRequestEndDate = $lastAbsence?->date;
        $this->editingRequestType = $firstAbsence?->type;
        $this->editingRequestReason = $firstAbsence?->reason ?? '';
    }

    public function cancelEditingRequest(): void
    {
        $this->editingRequestUuid = null;
        $this->editingRequestStartDate = null;
        $this->editingRequestEndDate = null;
        $this->editingRequestType = null;
        $this->editingRequestReason = '';
    }

    public function updatePendingRequest(): void
    {
        $this->syncCurrentUser();

        if ($this->currentUserId === null || $this->editingRequestUuid === null) {
            return;
        }

        if (
            $this->editingRequestStartDate === null
            || $this->editingRequestEndDate === null
            || $this->editingRequestType === null
        ) {
            session()->flash('status', 'Please choose a valid date range and absence type.');

            return;
        }

        if (! AbsenceOption::query()->where('code', $this->editingRequestType)->exists()) {
            session()->flash('status', 'The selected absence type is no longer available.');

            return;
        }

        $requestAbsences = $this->pendingRequestAbsencesForCurrentUser($this->editingRequestUuid);

        if ($requestAbsences->isEmpty()) {
            $this->cancelEditingRequest();

            return;
        }

        $dates = $this->expandDateRange($this->editingRequestStartDate, $this->editingRequestEndDate);

        if ($dates === []) {
            session()->flash('status', 'Please choose a valid date range.');

            return;
        }

        $conflictingDates = Absence::query()
            ->where('user_id', $this->currentUserId)
            ->whereIn('date', $dates)
            ->where(function ($query) {
                $query
                    ->where('status', '!=', Absence::STATUS_PENDING)
                    ->orWhere('request_uuid', '!=', $this->editingRequestUuid);
            })
            ->pluck('date');

        if ($conflictingDates->isNotEmpty()) {
            session()->flash('status', 'Unable to update the request because one or more selected dates already have another absence.');

            return;
        }

        $existingDates = $requestAbsences->pluck('date')->all();
        $datesToKeep = array_values(array_intersect($existingDates, $dates));
        $datesToDelete = array_values(array_diff($existingDates, $dates));
        $datesToCreate = array_values(array_diff($dates, $existingDates));
        $trimmedReason = trim($this->editingRequestReason) !== '' ? trim($this->editingRequestReason) : null;

        if ($datesToDelete !== []) {
            Absence::query()
                ->where('user_id', $this->currentUserId)
                ->where('status', Absence::STATUS_PENDING)
                ->where('request_uuid', $this->editingRequestUuid)
                ->whereIn('date', $datesToDelete)
                ->delete();
        }

        if ($datesToKeep !== []) {
            Absence::query()
                ->where('user_id', $this->currentUserId)
                ->where('status', Absence::STATUS_PENDING)
                ->where('request_uuid', $this->editingRequestUuid)
                ->whereIn('date', $datesToKeep)
                ->update([
                    'type' => $this->editingRequestType,
                    'reason' => $trimmedReason,
                    'approved_by' => null,
                    'approved_at' => null,
                ]);
        }

        foreach ($datesToCreate as $date) {
            Absence::query()->create([
                'user_id' => $this->currentUserId,
                'type' => $this->editingRequestType,
                'date' => $date,
                'reason' => $trimmedReason,
                'status' => Absence::STATUS_PENDING,
                'request_uuid' => $this->editingRequestUuid,
                'approved_by' => null,
                'approved_at' => null,
            ]);
        }

        $this->absenceType = $this->editingRequestType;
        $this->reason = $this->editingRequestReason;
        $this->cancelEditingRequest();

        session()->flash('status', 'Pending request updated.');
    }

    public function deletePendingRequest(string $requestUuid): void
    {
        $this->syncCurrentUser();

        if ($this->currentUserId === null || $requestUuid === '') {
            return;
        }

        $deleted = Absence::query()
            ->where('user_id', $this->currentUserId)
            ->where('status', Absence::STATUS_PENDING)
            ->where('request_uuid', $requestUuid)
            ->delete();

        if ($deleted === 0) {
            return;
        }

        if ($this->editingRequestUuid === $requestUuid) {
            $this->cancelEditingRequest();
        }

        session()->flash('status', 'Pending request deleted.');
    }

    private function normalizeSelection(array $values, array $allowedValues): array
    {
        $allowedLookup = array_fill_keys($allowedValues, true);

        return collect($values)
            ->filter(fn (mixed $value) => is_string($value) && $value !== '' && isset($allowedLookup[$value]))
            ->unique()
            ->values()
            ->all();
    }

    private function holidaysForRange(Carbon $start, Carbon $end): Collection
    {
        $generatedHolidays = collect(range($start->year, $end->year))
            ->flatMap(fn (int $year) => SwedishHolidayCalendar::forYear($year))
            ->keyBy('date');

        $storedHolidays = Holiday::query()
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->map(fn (Holiday $holiday) => [
                'date' => $holiday->date,
                'name' => $holiday->name,
            ])
            ->keyBy('date');

        return $generatedHolidays
            ->merge($storedHolidays)
            ->filter(fn (array $holiday, string $date) => $date >= $start->format('Y-m-d') && $date <= $end->format('Y-m-d'));
    }

    private function formatPeriodLabel(Carbon $start, Carbon $end): string
    {
        if ($start->isSameMonth($end)) {
            return $start->translatedFormat('F Y');
        }

        if ($start->year === $end->year) {
            return sprintf(
                '%s – %s',
                $start->translatedFormat('F'),
                $end->translatedFormat('F Y')
            );
        }

        return sprintf(
            '%s – %s',
            $start->translatedFormat('F Y'),
            $end->translatedFormat('F Y')
        );
    }

    private function syncCurrentUser(): void
    {
        $currentUserId = session('current_user_id');

        if ($currentUserId && User::query()->whereKey($currentUserId)->exists()) {
            $this->currentUserId = (int) $currentUserId;

            return;
        }

        $firstUserId = User::query()->orderBy('id')->value('id');
        $this->currentUserId = $firstUserId !== null ? (int) $firstUserId : null;

        if ($this->currentUserId !== null) {
            session(['current_user_id' => $this->currentUserId]);
        }
    }

    private function syncAbsenceType(): void
    {
        if ($this->absenceType !== null && AbsenceOption::query()->where('code', $this->absenceType)->exists()) {
            return;
        }

        $this->absenceType = AbsenceOption::query()->orderBy('sort_order')->value('code') ?? 'S';
    }

    private function normalizeDates(array $dateRange): array
    {
        return collect($dateRange)
            ->filter(fn (mixed $date) => is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function expandDateRange(?string $startDate, ?string $endDate): array
    {
        if ($startDate === null || $endDate === null) {
            return [];
        }

        if (
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) !== 1
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) !== 1
        ) {
            return [];
        }

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();
        } catch (\Throwable) {
            return [];
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return collect(CarbonPeriod::create($start, $end))
            ->map(fn (Carbon $date) => $date->format('Y-m-d'))
            ->all();
    }

    private function pendingRequestsForCurrentUser(): Collection
    {
        if ($this->currentUserId === null) {
            return collect();
        }

        return Absence::query()
            ->with('user')
            ->where('user_id', $this->currentUserId)
            ->where('status', Absence::STATUS_PENDING)
            ->whereNotNull('request_uuid')
            ->orderBy('date')
            ->get()
            ->groupBy('request_uuid')
            ->map(fn (Collection $absences) => $this->summarizeRequest($absences))
            ->values();
    }

    private function pendingRequestAbsencesForCurrentUser(string $requestUuid): Collection
    {
        if ($this->currentUserId === null || $requestUuid === '') {
            return collect();
        }

        return Absence::query()
            ->where('user_id', $this->currentUserId)
            ->where('status', Absence::STATUS_PENDING)
            ->where('request_uuid', $requestUuid)
            ->orderBy('date')
            ->get();
    }

    private function pendingRequestsForManager(): Collection
    {
        if ($this->currentUserId === null) {
            return collect();
        }

        return Absence::query()
            ->with(['user', 'user.manager'])
            ->where('status', Absence::STATUS_PENDING)
            ->whereNotNull('request_uuid')
            ->whereHas('user', fn ($query) => $query->where('manager_id', $this->currentUserId))
            ->orderBy('date')
            ->get()
            ->groupBy('request_uuid')
            ->map(fn (Collection $absences) => $this->summarizeRequest($absences))
            ->sortBy(fn (array $request) => sprintf('%s-%s', $request['user_name'] ?? '', $request['date_start'] ?? ''))
            ->values();
    }

    private function summarizeRequest(Collection $absences): array
    {
        /** @var \Illuminate\Support\Collection<int, Absence> $absences */
        $orderedAbsences = $absences->sortBy('date')->values();
        $firstAbsence = $orderedAbsences->first();
        $lastAbsence = $orderedAbsences->last();

        return [
            'request_uuid' => $firstAbsence?->request_uuid,
            'user_id' => $firstAbsence?->user_id,
            'user_name' => $firstAbsence?->user?->name,
            'type' => $firstAbsence?->type,
            'reason' => $firstAbsence?->reason,
            'date_start' => $firstAbsence?->date,
            'date_end' => $lastAbsence?->date,
            'date_label' => $this->formatDateLabel($firstAbsence?->date, $lastAbsence?->date),
            'date_count' => $orderedAbsences->count(),
            'submitted_at' => $firstAbsence?->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function formatDateLabel(?string $startDate, ?string $endDate): string
    {
        if ($startDate === null || $endDate === null) {
            return 'Unknown dates';
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->isSameDay($end)) {
            return $start->format('Y-m-d');
        }

        return sprintf('%s → %s', $start->format('Y-m-d'), $end->format('Y-m-d'));
    }

    private function updateApprovalStatus(string $requestUuid, string $status): void
    {
        $this->syncCurrentUser();

        if ($this->currentUserId === null || $requestUuid === '') {
            return;
        }

        Absence::query()
            ->where('request_uuid', $requestUuid)
            ->where('status', Absence::STATUS_PENDING)
            ->whereHas('user', fn ($query) => $query->where('manager_id', $this->currentUserId))
            ->update([
                'status' => $status,
                'approved_by' => $this->currentUserId,
                'approved_at' => now(),
            ]);
    }
}
