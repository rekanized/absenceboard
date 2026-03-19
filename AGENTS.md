# AbsenceBoard AI Agent Guide

## Start Here
Use this file as the short entry point for repository context. Detailed project context lives in `.agent/context/` and `GEMINI.md`.

## Product Snapshot
AbsenceBoard is a Laravel 13 and Livewire 4 leave-planning application with:
- a multi-month planner,
- drag and date-based absence entry,
- manager-based approval flow,
- a current-user profile page,
- an internal admin workspace,
- country-aware holiday support.

## Important Business Rules
- Requests from users with a manager are created as pending.
- Requests from users without a manager are auto-approved.
- Planner users may replace their own existing planner days after an overwrite warning.
- Planner users may clear their own approved or pending absences.
- Rejections require a decision reason.
- Absence options are database-driven and editable from admin.
- Application timezone is admin-configurable.
- Theme and holiday-country preferences are user-specific.

## Key Files
- `app/Livewire/VacationPlanner.php`
- `resources/views/livewire/vacation-planner.blade.php`
- `resources/views/profile/show.blade.php`
- `resources/views/admin/index.blade.php`
- `public/app.css`

## Working Rules
- Keep README.md oriented to GitHub readers.
- Keep implementation context in agent docs.
- Prefer minimal, focused changes.
- Do not introduce Tailwind, Bootstrap, or jQuery.

## Deep Context
- `.agent/context/project_context.md`
- `.agent/context/architecture.md`
- `.agent/context/tech_stack.md`