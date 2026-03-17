<x-layouts.app>
<div style="max-width: 1200px; margin: 0 auto; padding: 32px 24px 48px; display: flex; flex-direction: column; gap: 24px;">
    <style>
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 20px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            padding: 24px;
        }

        .admin-card h1,
        .admin-card h2,
        .admin-card h3,
        .admin-card p {
            margin-top: 0;
        }

        .admin-form {
            display: grid;
            gap: 14px;
        }

        .admin-label {
            display: grid;
            gap: 8px;
            font-size: 14px;
            color: #334155;
            font-weight: 600;
        }

        .admin-input,
        .admin-select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
            font: inherit;
            background: white;
            color: #0f172a;
        }

        .admin-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 12px;
            background: #0f172a;
            color: white;
            padding: 12px 18px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .admin-button.secondary {
            background: #e2e8f0;
            color: #334155;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .admin-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
        }

        .option-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: inline-block;
        }

        .error-list {
            margin: 0;
            padding-left: 20px;
            color: #b91c1c;
        }
    </style>

    <section class="admin-card">
        <h1 style="font-size: 28px;">Admin proof of concept</h1>
        <p style="color: #475569; max-width: 760px;">
            Anyone can access this page. Use it to impersonate a user and add absence options while the approval flow is being validated.
        </p>

        @if ($errors->any())
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @if ($currentUser)
            <div class="admin-chip">
                Acting as {{ $currentUser->name }}
                @if ($currentUser->manager)
                    <span style="color: #475569; font-weight: 600;">· Manager: {{ $currentUser->manager->name }}</span>
                @endif
            </div>
        @endif
    </section>

    <div class="admin-grid">
        <section class="admin-card">
            <h2>Application name</h2>
            <p style="color: #475569;">Change the product name shown in the sidebar and page title.</p>

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
            <h2>Impersonate a user</h2>
            <p style="color: #475569;">Switch the active user stored in the session.</p>

            <form method="POST" action="{{ route('admin.impersonate') }}" class="admin-form">
                @csrf
                <label class="admin-label">
                    User
                    <select name="user_id" class="admin-select">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($currentUser?->id === $user->id)>
                                {{ $user->name }} · {{ $user->department?->name ?? 'No department' }} · {{ $user->location }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="admin-button">Impersonate</button>
            </form>
        </section>

        <section class="admin-card">
            <h2>Add absence option</h2>
            <p style="color: #475569;">New options become available in the planner modal immediately.</p>

            <form method="POST" action="{{ route('admin.absence-options.store') }}" class="admin-form">
                @csrf
                <label class="admin-label">
                    Code
                    <input name="code" class="admin-input" maxlength="10" value="{{ old('code') }}" placeholder="WFH">
                </label>

                <label class="admin-label">
                    Label
                    <input name="label" class="admin-input" value="{{ old('label') }}" placeholder="Work from home">
                </label>

                <label class="admin-label">
                    Color
                    <input type="color" name="color" class="admin-input" value="{{ old('color', '#4ade80') }}" style="padding: 6px 8px; min-height: 48px;">
                </label>

                <button type="submit" class="admin-button">Add option</button>
            </form>
        </section>
    </div>

    <section class="admin-card">
        <h2>Current absence options</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Option</th>
                    <th>Code</th>
                    <th>Color</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($absenceOptions as $option)
                    <tr>
                        <td>
                            <span class="admin-chip" style="background: {{ $option->color }}20; color: #0f172a;">
                                <span class="option-dot" style="background: {{ $option->color }};"></span>
                                {{ $option->label }}
                            </span>
                        </td>
                        <td>{{ $option->code }}</td>
                        <td>{{ $option->color }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="admin-card">
        <h2>Users and managers</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Department</th>
                    <th>Manager</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->department?->name ?? '—' }}</td>
                        <td>{{ $user->manager?->name ?? 'No manager' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
</x-layouts.app>
