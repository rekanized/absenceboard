<x-layouts.app :layout-current-user="$layoutCurrentUser">
@php
    $newAbsenceOptionFields = ['code', 'label', 'color'];
    $absenceOptionFormSubmitted = old('_absence_option_form') === '1';
    $absenceOptionModalShouldOpen = $absenceOptionFormSubmitted
        && collect($newAbsenceOptionFields)->contains(
            fn (string $field) => ! old('option_id') && ($errors->has($field) || old($field) !== null)
        );
    $pageErrorMessages = collect($errors->getBag('default')->messages());

    if ($absenceOptionFormSubmitted && ! old('option_id')) {
        $pageErrorMessages = $pageErrorMessages->except($newAbsenceOptionFields);
    }

    $pageErrorList = $pageErrorMessages->flatten()->values();
@endphp

<div class="page-shell page-shell-admin" x-data="{ isAbsenceOptionModalOpen: @js($absenceOptionModalShouldOpen) }">
    <section class="admin-card admin-stack">
        <div>
            <p class="planner-kicker">Admin workspace</p>
            <h1 style="font-size: 28px; margin-bottom: 12px;">Application settings</h1>
            <p style="color: var(--text-soft); max-width: 760px; margin: 0;">
                Manage product-facing settings and planner configuration here. Authentication and user administration now live in their own dedicated admin pages.
            </p>
        </div>

        @if ($pageErrorList->isNotEmpty())
            <ul class="error-list">
                @foreach ($pageErrorList as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @if ($currentUser)
            <div class="admin-chip">
                Signed in as {{ $currentUser->name }}
                <span style="color: var(--text-soft); font-weight: 600;">· {{ $currentUser->is_admin ? 'Admin' : 'Standard user' }}</span>
            </div>
        @endif
    </section>

    <div class="admin-grid">
        <section class="admin-card">
            <h2>Application name</h2>
            <p style="color: var(--text-soft);">Change the product name shown in the sidebar and browser title.</p>

            <form method="POST" action="{{ route('admin.application-name.update') }}" class="admin-form">
                @csrf
                <label class="admin-label">
                    App name
                    <input name="app_name" class="admin-input" maxlength="80" value="{{ old('app_name', $applicationName) }}" placeholder="LeaveBoard">
                </label>

                <button type="submit" class="admin-button">Save name</button>
            </form>
        </section>

        <section class="admin-card">
            <h2>Application timezone</h2>
            <p style="color: var(--text-soft);">Choose the default timezone used for application dates and times.</p>

            <form method="POST" action="{{ route('admin.application-timezone.update') }}" class="admin-form">
                @csrf
                <label class="admin-label">
                    Timezone
                    <select name="app_timezone" class="admin-input">
                        @foreach ($timezoneOptions as $timezoneOption)
                            <option value="{{ $timezoneOption }}" @selected(old('app_timezone', $applicationTimezone) === $timezoneOption)>
                                {{ $timezoneOption }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="admin-button">Save timezone</button>
            </form>
        </section>

        <section class="admin-card">
            <h2>Request log</h2>
            <p style="color: var(--text-soft);">
                Review submitted, updated, approved, rejected, and deleted absence requests.
            </p>

            <div class="admin-chip" style="margin-bottom: 16px;">
                {{ $requestLogCount }} logged events
            </div>

            <a href="{{ route('admin.logs') }}" class="admin-button admin-button-link">Open log view</a>
        </section>
    </div>

    <section class="admin-card admin-stack">
        <div class="admin-section-toolbar">
            <div class="admin-section-heading">
                <h2>Absence options</h2>
                <p style="color: var(--text-soft); max-width: 760px; margin-bottom: 0;">
                    If an option has already been used, the warning below appears before you save or delete it.
                </p>
            </div>

            <button type="button" class="admin-button" @click="isAbsenceOptionModalOpen = true">Create absence option</button>
        </div>

        <div class="admin-options-grid">
            @foreach ($absenceOptions as $option)
                @php
                    $isEditingOption = (string) old('option_id') === (string) $option->id;
                    $labelValue = $isEditingOption ? old('label', $option->label) : $option->label;
                    $codeValue = $isEditingOption ? old('code', $option->code) : $option->code;
                    $colorValue = $isEditingOption ? old('color', $option->color) : $option->color;
                    $hasUsage = $option->user_count > 0;
                    $editConfirmation = $hasUsage
                        ? sprintf('Warning: %d people have already used %s for %d day(s). Saving will update this option, and changing the code will also update those existing days. Continue?', $option->user_count, $option->label, $option->absence_count)
                        : sprintf('Save changes to %s?', $option->label);
                    $deleteConfirmation = $hasUsage
                        ? sprintf('Warning: %d people have already used %s for %d day(s). Deleting will remove it from future selections, but existing days will still keep the deleted code. Continue?', $option->user_count, $option->label, $option->absence_count)
                        : sprintf('Delete %s?', $option->label);
                @endphp

                <article class="admin-option-card">
                    <form
                        id="update-option-{{ $option->id }}"
                        method="POST"
                        action="{{ route('admin.absence-options.update', $option) }}"
                        class="admin-option-fields"
                        onsubmit="return confirm(@js($editConfirmation));"
                    >
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="option_id" value="{{ $option->id }}">

                        <div class="admin-option-card-header">
                            <span class="admin-chip" style="background: {{ $colorValue }}20; color: var(--text-main); width: fit-content;">
                                <span class="option-dot" style="background: {{ $colorValue }};"></span>
                                {{ $labelValue }}
                            </span>

                            <div class="admin-option-meta">
                                <p class="usage-note">Edit the option details below. Changes appear in the planner immediately.</p>
                            </div>
                        </div>

                        <label class="admin-label" for="option-label-{{ $option->id }}">
                            Label
                            <input
                                id="option-label-{{ $option->id }}"
                                name="label"
                                class="admin-input admin-table-input"
                                value="{{ $labelValue }}"
                                maxlength="100"
                            >
                        </label>

                        <div class="admin-option-field-row">
                            <label class="admin-label" for="option-code-{{ $option->id }}">
                                Code
                                <input
                                    id="option-code-{{ $option->id }}"
                                    name="code"
                                    class="admin-input admin-table-input"
                                    value="{{ $codeValue }}"
                                    maxlength="10"
                                >
                            </label>

                            <div class="admin-option-color-group">
                                <label class="admin-label" for="option-color-picker-{{ $option->id }}">
                                    Color
                                    <div class="admin-option-color-control">
                                        <input
                                            id="option-color-picker-{{ $option->id }}"
                                            type="color"
                                            name="color"
                                            class="admin-input admin-table-input"
                                            value="{{ $colorValue }}"
                                            style="padding: 6px 8px; min-height: 48px;"
                                        >

                                        <div class="admin-color-preview admin-color-preview-inline">
                                            <span class="option-dot" style="width: 16px; height: 16px; background: {{ $colorValue }};"></span>
                                            <span class="admin-color-value">{{ $colorValue }}</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </form>

                    <div class="admin-option-footer">
                        <div class="admin-option-usage">
                            @if ($hasUsage)
                                <div class="usage-warning">
                                    <span><strong>Warning:</strong> {{ $option->user_count }} people already used this option.</span>
                                    <span>{{ $option->absence_count }} day(s) will be affected by changes.</span>
                                </div>
                            @else
                                <p class="usage-note">No existing usage yet.</p>
                            @endif
                        </div>

                        <div class="admin-inline-actions">
                            <x-loading-button type="submit" form="update-option-{{ $option->id }}" class="admin-button">Save</x-loading-button>

                            <form
                                method="POST"
                                action="{{ route('admin.absence-options.destroy', $option) }}"
                                onsubmit="return confirm(@js($deleteConfirmation));"
                            >
                                @csrf
                                @method('DELETE')
                                <x-loading-button type="submit" class="admin-button danger" style="width: 100%;">Delete</x-loading-button>
                            </form>
                        </div>

                        @if ($hasUsage)
                            <p class="usage-note">
                                Deleting removes the option from future choices, while existing days keep their stored code.
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div
        class="modal-overlay"
        x-show="isAbsenceOptionModalOpen"
        x-cloak
        x-transition
        x-on:keydown.escape.window="isAbsenceOptionModalOpen = false"
    >
        <div
            class="modal-content admin-create-option-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="absence-option-modal-title"
            @click.away="isAbsenceOptionModalOpen = false"
            @click.stop
        >
            <div class="modal-stack">
                <div class="request-edit-modal-head">
                    <div>
                        <p class="planner-kicker" style="margin-bottom: 8px;">Planner setup</p>
                        <h2 class="modal-title" id="absence-option-modal-title">Create absence option</h2>
                        <p class="admin-helper-text admin-create-option-modal-copy">Add a new planner option with a short code, a clear label, and a color that remains distinct in the schedule.</p>
                    </div>

                    <button type="button" class="admin-button secondary request-edit-close" @click="isAbsenceOptionModalOpen = false">Close</button>
                </div>

                <form method="POST" action="{{ route('admin.absence-options.store') }}" class="admin-form admin-create-option-form">
                    @csrf
                    <input type="hidden" name="_absence_option_form" value="1">

                    <section class="admin-create-option-section">
                        <div class="admin-create-option-grid">
                            <label class="admin-label">
                                Code
                                <input name="code" class="admin-input" maxlength="10" value="{{ old('option_id') ? '' : old('code') }}" placeholder="WFH">
                                <span class="admin-helper-text">Use a short planner code, ideally under 10 characters.</span>
                                @error('code')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label admin-create-option-field-wide">
                                Label
                                <input name="label" class="admin-input" value="{{ old('option_id') ? '' : old('label') }}" placeholder="Work from home">
                                <span class="admin-helper-text">This is what people see in the planner and request details.</span>
                                @error('label')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label admin-create-option-field-wide">
                                Color
                                <div class="admin-option-color-control admin-create-option-color-control">
                                    <input type="color" name="color" class="admin-input" value="{{ old('option_id') ? '#4ade80' : old('color', '#4ade80') }}" style="padding: 6px 8px; min-height: 48px;">

                                    <div class="admin-color-preview admin-color-preview-inline">
                                        <span class="option-dot" style="width: 16px; height: 16px; background: {{ old('option_id') ? '#4ade80' : old('color', '#4ade80') }};"></span>
                                        <span class="admin-color-value">{{ old('option_id') ? '#4ade80' : old('color', '#4ade80') }}</span>
                                    </div>
                                </div>
                                <span class="admin-helper-text">Pick a color that stays readable against the planner background.</span>
                                @error('color')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </section>

                    <div class="modal-actions request-edit-modal-actions">
                        <button type="button" class="admin-button secondary" @click="isAbsenceOptionModalOpen = false">Cancel</button>
                        <button type="submit" class="admin-button">Create absence option</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
