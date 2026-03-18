# LeaveBoard Agent Context

## Product Summary
LeaveBoard is an internal leave-planning application built with Laravel and Livewire. It combines a multi-month absence planner, a current-user profile workspace, and a lightweight admin area for absence-option and application settings management.

## Current Scope
- Planner page with department grouping, filters, drag/range selection, and holiday markers
- Approval workflow with pending, approved, and rejected requests
- Profile page for holiday-country and theme preferences
- Admin page for impersonation, branding, absence options, and request logs

## Important Domain Rules
- Users with a manager submit requests as `pending`.
- Users without a manager are auto-approved.
- Multi-day requests are grouped by `absences.request_uuid`.
- Rejections require a manager decision reason stored in `absences.decision_reason`.
- Holiday rendering depends on `users.holiday_country` and `holidays.country_code`.
- Theme preference is persisted per user and shared across planner, profile, and admin pages.

## Primary Paths
- `app/Livewire/VacationPlanner.php` - planner state, request creation, filters, approvals, editing
- `resources/views/livewire/vacation-planner.blade.php` - planner UI
- `resources/views/profile/show.blade.php` - current-user profile UI
- `resources/views/admin/index.blade.php` - admin workspace
- `public/app.css` - main handcrafted stylesheet for shell, planner, profile, and admin

## Development Commands
- Install PHP dependencies: `composer install`
- Prepare local SQLite database: `cp .env.example .env && touch database/database.sqlite && php artisan key:generate`
- Fresh seed local database: `php artisan migrate:fresh --seed`
- Start local development: `php artisan serve`
- Run tests: `composer test`

## Agent Guidance
- Keep README.md GitHub-facing and concise.
- Put implementation detail and project memory in agent docs, not in README.md.
- Preserve the existing handcrafted vanilla CSS and Blade/Livewire structure.
- Prefer updating `.agent/context/` when product scope, architecture, or stack changes.

## Related Context Files
- `.agent/context/project_context.md`
- `.agent/context/architecture.md`
- `.agent/context/tech_stack.md`