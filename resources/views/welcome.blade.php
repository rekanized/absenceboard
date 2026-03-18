<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'LeaveBoard') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('brand/leaveboard-mark.svg') }}">
        <link rel="stylesheet" href="{{ asset('app.css') }}">
    </head>
    <body class="app-body" data-theme="light">
        <main class="app-main" style="max-width: 760px; margin: 0 auto; padding: 48px 20px;">
            <section class="admin-card admin-stack">
                <div class="admin-toolbar">
                    <div>
                        <p class="planner-kicker">LeaveBoard</p>
                        <h1 style="font-size: 2rem; margin-bottom: 12px;">Vanilla CSS leave planning</h1>
                        <p style="max-width: 52ch; color: #475569; margin: 0;">
                            LeaveBoard is a Laravel and Livewire leave-planning app with a multi-month planner,
                            current-user profile workspace, and internal admin tools.
                        </p>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('planner') }}" class="admin-button">Open planner</a>
                    <a href="{{ route('profile.show') }}" class="admin-button secondary">Profile</a>
                    <a href="{{ route('admin.index') }}" class="admin-button ghost">Admin</a>
                </div>
            </section>
        </main>
    </body>
</html>