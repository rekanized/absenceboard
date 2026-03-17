<div x-data="planner()" @mouseup.window="stopDragging()">
    <style>
        .planner-wrapper {
            padding: 24px;
            max-width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            user-select: none;
        }

        .planner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            padding: 20px 32px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .planner-title {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: var(--text-main);
        }

        .planner-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .grid-viewport {
            overflow: auto;
            flex: 1;
        }

        .planner-grid {
            display: grid;
            grid-template-columns: 240px 120px repeat({{ $dates->count() }}, 44px);
            min-width: max-content;
        }

        .cell {
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            background: transparent;
            min-height: 48px;
            transition: background 0.1s ease;
        }

        .header-cell {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border-color);
        }

        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 30;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-right: 2px solid var(--border-color);
        }

        .sticky-col-2 {
            left: 240px;
        }

        .header-cell.sticky-col {
            z-index: 40;
        }

        .week-header {
            background: #f1f5f9;
            color: var(--text-main);
            font-size: 10px;
        }

        .date-header {
            flex-direction: column;
            gap: 2px;
            line-height: 1.2;
        }

        .month-label {
            color: var(--accent-blue);
            font-weight: 700;
        }

        .department-row {
            grid-column: 1 / -1;
            background: #f8fafc;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-main);
            border-bottom: 2px solid var(--border-color);
            position: sticky;
            left: 0;
            display: flex;
            align-items: center;
            z-index: 15;
        }

        .department-name {
            position: sticky;
            left: 24px;
            display: flex;
            align-items: center;
        }


        .user-cell {
            justify-content: flex-start;
            font-weight: 500;
            color: var(--text-main);
            padding-left: 24px;
        }

        .loc-cell {
            color: var(--text-muted);
            font-size: 12px;
        }

        .weekend {
            background: rgba(241, 245, 249, 0.5);
        }

        .holiday {
            background: rgba(239, 68, 68, 0.1);
        }

        .holiday .date-label {
            color: var(--accent-red);
            font-weight: 700;
        }

        /* Selection Highlight */
        .cell-selected {
            background: rgba(59, 130, 246, 0.2) !important;
            border: 1px dashed var(--accent-blue);
        }

        /* Absence Indicators */
        .absence-indicator {
            width: 100%;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 11px;
            margin: 0 -1px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            cursor: pointer;
        }

        .absence-s { background-color: #4ade80; color: #064e3b; }
        .absence-fl { background-color: #38bdf8; color: #082f49; }
        .absence-b { background-color: #facc15; color: #422006; }

        .absence-indicator.start { border-top-left-radius: 16px; border-bottom-left-radius: 16px; margin-left: 4px; }
        .absence-indicator.end { border-top-right-radius: 16px; border-bottom-right-radius: 16px; margin-right: 4px; }
        .absence-indicator.solo { border-radius: 16px; margin: 0 4px; }

        .cell-interactive:hover {
            background: rgba(59, 130, 246, 0.05);
            cursor: pointer;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 32px;
            border-radius: 20px;
            width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .type-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .type-btn {
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 12px;
        }

        .type-btn.active {
            border-color: var(--text-main);
            background: var(--text-main);
            color: white;
        }

        .reason-input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
        }

        .modal-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary { background: var(--text-main); color: white; }
        .btn-secondary { background: #f1f5f9; color: var(--text-muted); }
        .btn-danger { background: #fee2e2; color: #991b1b; }

        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
    </style>

    <div class="planner-wrapper" x-init="
        const today = document.getElementById('today');
        if (today) {
            $el.querySelector('.grid-viewport').scrollLeft = today.offsetLeft - 400;
        }
    ">
        <header class="planner-header">
            <div style="display: flex; align-items: center; gap: 24px;">
                <h1 class="planner-title">Absence Planner 2026</h1>
                
                <div style="display: flex; align-items: center; background: #f1f5f9; padding: 4px; border-radius: 12px; gap: 4px;">
                    <button wire:click="previousMonth()" class="btn btn-secondary" style="padding: 6px 12px;">
                        <span class="icon" style="font-size: 18px;">chevron_left</span>
                    </button>
                    <button wire:click="goToToday()" class="btn btn-secondary" style="padding: 6px 16px; font-size: 13px;">
                        Today
                    </button>
                    <button wire:click="nextMonth()" class="btn btn-secondary" style="padding: 6px 12px;">
                        <span class="icon" style="font-size: 18px;">chevron_right</span>
                    </button>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 16px;">
                <select wire:model.live="selectedDept" class="reason-input" style="width: auto; padding: 8px 16px;">
                    <option value="">All Departments</option>
                    @foreach($allDepartments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>

                <select wire:model.live="selectedSite" class="reason-input" style="width: auto; padding: 8px 16px;">


                    <option value="">All Sites</option>
                    @foreach($sites as $site)
                        <option value="{{ $site }}">{{ $site }}</option>
                    @endforeach
                </select>
                <div style="font-size: 13px; color: var(--text-muted);">
                    Drag across days to select range
                </div>
            </div>
        </header>


        <main class="planner-card">
            <div class="grid-viewport">
                <div class="planner-grid">
                    <!-- Headers -->
                    <div class="cell header-cell sticky-col" style="grid-row: 1 / 3;"><span>Personnel</span></div>
                    <div class="cell header-cell sticky-col sticky-col-2" style="grid-row: 1 / 3;"><span>Site</span></div>

                    @foreach($weeks as $weekNum => $weekDates)
                        <div class="cell header-cell week-header" style="grid-column: span {{ $weekDates->count() }};">
                            Week {{ $weekNum }}
                        </div>
                    @endforeach

                    @foreach($dates as $index => $date)
                        <div class="cell header-cell date-header date-header-data 
                             {{ $date['is_weekend'] ? 'weekend' : '' }} 
                             {{ $date['is_holiday'] ? 'holiday' : '' }}" 
                             data-date="{{ $date['date'] }}"
                             @if($date['date'] === date('Y-m-d')) id="today" @endif
                             title="{{ $date['holiday_name'] ?? ($date['is_weekend'] ? 'Weekend' : '') }}">

                            @if($date['day'] == 1 || $loop->first)
                                <span class="month-label">{{ $date['month'] }}</span>
                            @endif
                            <span class="date-label">{{ $date['day'] }}</span>
                        </div>
                    @endforeach

                    <!-- Data Rows -->
                    @foreach($departments as $dept)
                        <div class="department-row">
                            <span class="icon" style="margin-right: 8px; font-size: 18px;">business</span>
                            {{ $dept->name }}
                        </div>

                        @foreach($dept->users as $user)
                            <div class="cell sticky-col user-cell">{{ $user->name }}</div>
                            <div class="cell sticky-col sticky-col-2 loc-cell">{{ $user->location }}</div>
                            
                            @php
                                $userAbsences = $user->absences->keyBy('date');
                            @endphp

                            @foreach($dates as $index => $date)
                                @php
                                    $absence = $userAbsences->get($date['date']);
                                    $prevDate = \Carbon\Carbon::parse($date['date'])->subDay()->format('Y-m-d');
                                    $nextDate = \Carbon\Carbon::parse($date['date'])->addDay()->format('Y-m-d');
                                    
                                    $isCurrent = !!$absence;
                                    $type = $absence?->type;
                                    $hasPrev = $isCurrent && $userAbsences->has($prevDate) && $userAbsences->get($prevDate)->type === $type;
                                    $hasNext = $isCurrent && $userAbsences->has($nextDate) && $userAbsences->get($nextDate)->type === $type;
                                    
                                    $isSolo = $isCurrent && !$hasPrev && !$hasNext;
                                    $isStart = $isCurrent && !$hasPrev && $hasNext;
                                    $isEnd = $isCurrent && $hasPrev && !$hasNext;
                                    $isMid = $isCurrent && $hasPrev && $hasNext;
                                @endphp
                                <div class="cell cell-interactive 
                                     {{ $date['is_weekend'] ? 'weekend' : '' }} 
                                     {{ $date['is_holiday'] ? 'holiday' : '' }}" 
                                     :class="{ 'cell-selected': isSelected({{ $user->id }}, {{ $index }}) }"
                                     @mousedown="startDragging({{ $user->id }}, {{ $index }})"
                                     @mouseenter="dragEnter({{ $index }})"
                                     title="{{ $date['holiday_name'] ?? '' }}">
                                    @if($isCurrent)
                                        <div class="absence-indicator absence-{{ strtolower($type) }} 
                                            {{ $isSolo ? 'solo' : '' }}
                                            {{ $isStart ? 'start' : '' }} 
                                            {{ $isEnd ? 'end' : '' }}"
                                            title="{{ $absence->reason }}">
                                            @if($isSolo || $isStart)
                                                {{ $type }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    <!-- Selection Modal -->
    <div class="modal-overlay" x-show="showModal" x-cloak x-transition>
        <div class="modal-content" @click.away="reset()">
            <h2 class="modal-title">Define Absence</h2>
            
            <div class="type-selector">
                <button class="type-btn" :class="{ 'active': absenceType === 'S' }" @click="absenceType = 'S'">
                    <span class="chip-dot dot-s"></span> Semester
                </button>
                <button class="type-btn" :class="{ 'active': absenceType === 'FL' }" @click="absenceType = 'FL'">
                    <span class="chip-dot dot-fl"></span> Parental
                </button>
                <button class="type-btn" :class="{ 'active': absenceType === 'B' }" @click="absenceType = 'B'">
                    <span class="chip-dot dot-b"></span> Unassigned
                </button>
            </div>

            <textarea class="reason-input" x-model="reason" placeholder="Add a reason (optional)..." rows="3"></textarea>

            <div class="modal-actions">
                <button class="btn btn-secondary" @click="reset()">Cancel</button>
                <button class="btn btn-danger" @click="remove()">Clear Selection</button>
                <button class="btn btn-primary" @click="apply()">Apply Absence</button>
            </div>
        </div>
    </div>

    <script>
        function planner() {
            return {
                isDragging: false,
                selectionStart: null,
                selectionEnd: null,
                selectedUser: null,
                showModal: false,
                absenceType: 'S',
                reason: '',

                init() {
                    // Sync entangled properties if needed, or use $wire directly
                },

                startDragging(userId, dateIndex) {
                    this.isDragging = true;
                    this.selectedUser = userId;
                    this.selectionStart = dateIndex;
                    this.selectionEnd = dateIndex;
                },

                dragEnter(dateIndex) {
                    if (!this.isDragging) return;
                    this.selectionEnd = dateIndex;
                },

                stopDragging() {
                    if (!this.isDragging) return;
                    this.isDragging = false;
                    this.showModal = true;
                },

                get selectedRange() {
                    if (this.selectionStart === null || this.selectionEnd === null) return [];
                    const start = Math.min(this.selectionStart, this.selectionEnd);
                    const end = Math.max(this.selectionStart, this.selectionEnd);
                    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
                },

                isSelected(userId, dateIndex) {
                    return this.selectedUser === userId && this.selectedRange.includes(dateIndex);
                },

                apply() {
                    if (this.selectedUser === null) return;
                    const dates = this.selectedRange.map(idx => document.querySelectorAll('.date-header-data')[idx].dataset.date);
                    this.$wire.applyAbsence(this.selectedUser, dates, this.absenceType, this.reason);
                    this.reset();
                },

                remove() {
                    if (this.selectedUser === null) return;
                    const dates = this.selectedRange.map(idx => document.querySelectorAll('.date-header-data')[idx].dataset.date);
                    this.$wire.removeAbsence(this.selectedUser, dates);
                    this.reset();
                },

                reset() {
                    this.showModal = false;
                    this.selectionStart = null;
                    this.selectionEnd = null;
                    this.selectedUser = null;
                    this.reason = '';
                }
            }
        }
    </script>
</div>
