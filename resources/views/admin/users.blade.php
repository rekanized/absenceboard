<x-layouts.app :layout-current-user="$layoutCurrentUser">
@php
    $manualUserFields = [
        'first_name',
        'last_name',
        'email',
        'password',
        'password_confirmation',
        'department_name',
        'location',
        'manager_id',
        'is_admin',
    ];
    $manualUserFormSubmitted = old('_manual_user_form') === '1';
    $manualUserModalShouldOpen = $manualUserFormSubmitted
        && collect($manualUserFields)->contains(fn (string $field) => $errors->has($field) || old($field) !== null);
    $pageErrorMessages = collect($errors->getBag('default')->messages());

    if ($manualUserFormSubmitted) {
        $pageErrorMessages = $pageErrorMessages->except($manualUserFields);
    }

    $pageErrorList = $pageErrorMessages->flatten()->values();
    $isImpersonating = session()->has('impersonator_user_id');
@endphp

<div
    class="page-shell page-shell-admin"
    x-data="{ isManualUserModalOpen: @js($manualUserModalShouldOpen) }"
    x-effect="if (isManualUserModalOpen) { $nextTick(() => $refs.manualUserFirstName?.focus()) }"
>
    <section class="admin-card admin-stack">
        <div>
            <p class="planner-kicker">Admin workspace</p>
            <h1 style="font-size: 28px; margin-bottom: 12px;">User information</h1>
            <p style="color: var(--text-soft); max-width: 760px; margin: 0;">
                Manage manual accounts, review who can access the application, impersonate another user for support work, and control admin permissions without mixing those tasks into application configuration.
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

    <section class="admin-card">
        <form method="GET" action="{{ route('admin.users') }}" class="admin-form" style="margin-bottom: 24px; gap: 16px;">
            <div class="admin-user-toolbar" style="align-items: end; gap: 16px;">
                <label class="admin-label" style="min-width: 220px; flex: 1 1 260px;">
                    Search
                    <input type="search" name="search" value="{{ $search }}" class="admin-input" placeholder="Name or email">
                </label>

                <label class="admin-label" style="min-width: 180px; flex: 1 1 180px;">
                    Department
                    <select name="department" class="admin-select">
                        <option value="">All departments</option>
                        @foreach ($departmentOptions as $departmentOption)
                            <option value="{{ $departmentOption }}" @selected($selectedDepartment === $departmentOption)>{{ $departmentOption }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="admin-label" style="min-width: 180px; flex: 1 1 180px;">
                    Site
                    <select name="location" class="admin-select">
                        <option value="">All sites</option>
                        @foreach ($locationOptions as $locationOption)
                            <option value="{{ $locationOption }}" @selected($selectedLocation === $locationOption)>{{ $locationOption }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="admin-label" style="min-width: 160px; flex: 1 1 160px;">
                    Status
                    <select name="status" class="admin-select">
                        <option value="">Any status</option>
                        @foreach ($statusOptions as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected($selectedStatus === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="admin-label" style="min-width: 160px; flex: 1 1 160px;">
                    Access
                    <select name="access" class="admin-select">
                        <option value="">Any access</option>
                        @foreach ($accessOptions as $accessValue => $accessLabel)
                            <option value="{{ $accessValue }}" @selected($selectedAccess === $accessValue)>{{ $accessLabel }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="admin-user-toolbar" style="gap: 12px; align-items: center;">
                <div class="admin-helper-text">Showing {{ $users->count() }} user{{ $users->count() === 1 ? '' : 's' }}.</div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="submit" class="admin-button">Apply filters</button>
                    <a href="{{ route('admin.users') }}" class="admin-button secondary" style="text-decoration: none;">Reset</a>
                </div>
            </div>
        </form>

        <div class="admin-user-toolbar">
            <div class="admin-user-heading">
                <h2>Users and permissions</h2>
                <p style="color: var(--text-soft); max-width: 760px;">New Azure users are created automatically on first sign-in. Create manual email/password accounts here when Azure is not available for everyone, then manage access, reporting lines, and admin permissions in one place.</p>
            </div>

            <button type="button" class="admin-button" @click="isManualUserModalOpen = true">Create manual user</button>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Department</th>
                    <th>Manager</th>
                    <th>Access</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $canDeactivate = $user->is_active && $activeUserCount > 1;
                        $canRevokeAdmin = $user->is_admin && $adminCount > 1;
                    @endphp
                    <tr class="admin-user-row {{ $user->is_active ? '' : 'inactive' }}">
                        <td>
                            <div class="admin-user-copy">
                                <strong>{{ $user->name }}</strong>
                                <span class="admin-helper-text">{{ $user->email ?: 'No email captured yet' }}</span>
                            </div>
                        </td>
                        <td>{{ $user->department?->name ?? '—' }}</td>
                        <td>
                            <div class="admin-user-copy">
                                <span>{{ $user->manager?->name ?? 'No manager' }}</span>

                                @if ($user->isManualAccount())
                                    <form method="POST" action="{{ route('admin.users.manager', $user) }}" class="admin-form admin-user-manager-form">
                                        @csrf
                                        @method('PATCH')

                                        <label class="admin-label" for="manager-id-{{ $user->id }}">
                                            <span class="admin-helper-text">Assign manager</span>
                                            <select id="manager-id-{{ $user->id }}" name="manager_id" class="admin-select">
                                                <option value="">No manager</option>
                                                @foreach ($managerOptions as $managerOption)
                                                    @if ($managerOption->id !== $user->id && $managerOption->is_active)
                                                        <option value="{{ $managerOption->id }}" @selected($user->manager_id === $managerOption->id)>
                                                            {{ $managerOption->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </label>

                                        <button type="submit" class="admin-button secondary">Save manager</button>
                                    </form>
                                @else
                                    <span class="admin-helper-text">Managed from Azure profile data</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="admin-chip {{ $user->is_admin ? 'success' : '' }}">
                                {{ $user->is_admin ? 'Admin' : 'Standard' }}
                            </span>
                        </td>
                        <td>
                            <span class="admin-chip {{ $user->is_active ? 'success' : 'warning' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-user-actions">
                                <form method="POST" action="{{ route('admin.users.admin', $user) }}" class="admin-form" style="gap: 8px;">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="admin-button {{ $user->is_admin ? 'secondary' : '' }}"
                                        @disabled($user->is_admin && ! $canRevokeAdmin)
                                    >
                                        {{ $user->is_admin ? 'Remove admin' : 'Grant admin' }}
                                    </button>

                                    @if ($user->is_admin && ! $canRevokeAdmin)
                                        <p class="admin-helper-text">At least one admin must remain.</p>
                                    @endif
                                </form>

                                <form method="POST" action="{{ route('admin.users.impersonate', $user) }}" class="admin-form" style="gap: 8px;">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="admin-button secondary"
                                        @disabled($isImpersonating || ! $user->is_active || ($currentUser && $currentUser->id === $user->id))
                                    >
                                        Impersonate
                                    </button>

                                    @if ($currentUser && $currentUser->id === $user->id)
                                        <p class="admin-helper-text">Choose another user.</p>
                                    @elseif ($isImpersonating)
                                        <p class="admin-helper-text">Stop the current impersonation first.</p>
                                    @elseif (! $user->is_active)
                                        <p class="admin-helper-text">Only active users can be impersonated.</p>
                                    @endif
                                </form>

                                <form method="POST" action="{{ route('admin.users.activity', $user) }}" class="admin-form" style="gap: 8px;">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="admin-button {{ $user->is_active ? 'secondary' : '' }}"
                                        @disabled($user->is_active && ! $canDeactivate)
                                    >
                                        {{ $user->is_active ? 'Mark inactive' : 'Reactivate' }}
                                    </button>

                                    @if ($user->is_active && ! $canDeactivate)
                                        <p class="admin-helper-text">At least one active user must remain.</p>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <div
        class="modal-overlay"
        x-show="isManualUserModalOpen"
        x-cloak
        x-transition
        x-on:keydown.escape.window="isManualUserModalOpen = false"
    >
        <div
            class="modal-content admin-manual-user-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="manual-user-modal-title"
            @click.away="isManualUserModalOpen = false"
            @click.stop
        >
            <div class="modal-stack">
                <div class="request-edit-modal-head">
                    <div>
                        <p class="planner-kicker" style="margin-bottom: 8px;">Manual account</p>
                        <h2 class="modal-title" id="manual-user-modal-title">Create manual user</h2>
                        <p class="admin-helper-text admin-manual-user-modal-copy">Create an email/password account for anyone who cannot use Azure sign-in yet. The account is active immediately after creation.</p>
                    </div>

                    <button type="button" class="admin-button secondary request-edit-close" @click="isManualUserModalOpen = false">Close</button>
                </div>

                <form method="POST" action="{{ route('admin.manual-users.store') }}" class="admin-form admin-manual-user-form">
                    @csrf
                    <input type="hidden" name="_manual_user_form" value="1">

                    <section class="admin-manual-user-section">
                        <div class="admin-manual-user-section-head">
                            <div>
                                <h3>Identity</h3>
                                <p class="admin-helper-text">Set the user’s name, sign-in email, and password.</p>
                            </div>
                        </div>

                        <div class="admin-manual-user-grid">
                            <label class="admin-label">
                                First name
                                <input x-ref="manualUserFirstName" name="first_name" class="admin-input" value="{{ old('first_name') }}" placeholder="Asta" autocomplete="given-name">
                                @error('first_name')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label">
                                Last name
                                <input name="last_name" class="admin-input" value="{{ old('last_name') }}" placeholder="Admin" autocomplete="family-name">
                                @error('last_name')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label admin-manual-user-field-wide">
                                Email
                                <input type="email" name="email" class="admin-input" value="{{ old('email') }}" placeholder="asta@example.com" autocomplete="email">
                                @error('email')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label">
                                Password
                                <input type="password" name="password" class="admin-input" placeholder="At least 12 characters" autocomplete="new-password">
                                @error('password')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label">
                                Confirm password
                                <input type="password" name="password_confirmation" class="admin-input" placeholder="Repeat password" autocomplete="new-password">
                                @error('password_confirmation')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </section>

                    <section class="admin-manual-user-section">
                        <div class="admin-manual-user-section-head">
                            <div>
                                <h3>Work profile</h3>
                                <p class="admin-helper-text">These details control planner grouping, location labels, and approval routing.</p>
                            </div>
                        </div>

                        <div class="admin-manual-user-grid">
                            <label class="admin-label">
                                Department
                                <input name="department_name" class="admin-input" value="{{ old('department_name') }}" list="department-name-options" placeholder="Unassigned" autocomplete="organization">
                                @error('department_name')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label">
                                Location
                                <input name="location" class="admin-input" value="{{ old('location') }}" placeholder="Unassigned" autocomplete="address-level2">
                                @error('location')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="admin-label admin-manual-user-field-wide">
                                Manager
                                <select name="manager_id" class="admin-select">
                                    <option value="">No manager</option>
                                    @foreach ($managerOptions as $managerOption)
                                        @if ($managerOption->is_active)
                                            <option value="{{ $managerOption->id }}" @selected((string) old('manager_id', '') === (string) $managerOption->id)>
                                                {{ $managerOption->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <span class="admin-helper-text">Leave this empty if the user should auto-approve their own requests.</span>
                                @error('manager_id')
                                    <span class="admin-field-error">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </section>

                    <section class="admin-manual-user-section admin-manual-user-section-compact">
                        <div class="admin-manual-user-section-head">
                            <div>
                                <h3>Permissions</h3>
                                <p class="admin-helper-text">Choose whether this person should manage the admin workspace.</p>
                            </div>
                        </div>

                        <label class="admin-checkbox-label admin-manual-user-checkbox">
                            <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin'))>
                            Grant admin access
                        </label>
                        @error('is_admin')
                            <span class="admin-field-error">{{ $message }}</span>
                        @enderror
                    </section>

                    <div class="modal-actions request-edit-modal-actions">
                        <button type="button" class="admin-button secondary" @click="isManualUserModalOpen = false">Cancel</button>
                        <button type="submit" class="admin-button">Create manual user</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <datalist id="department-name-options">
        @foreach ($departmentOptions as $departmentOption)
            <option value="{{ $departmentOption }}"></option>
        @endforeach
    </datalist>
</div>

</x-layouts.app>