# Architecture and Data Model

## Main Application Areas
- Planner logic lives in `app/Livewire/VacationPlanner.php`.
- Planner UI lives in `resources/views/livewire/vacation-planner.blade.php`.
- Profile page logic is handled by `app/Http/Controllers/ProfileController.php`.
- Admin page logic is handled by `app/Http/Controllers/AdminController.php`.
- Shared shell and visual styles are centered in `resources/views/components/layouts/app.blade.php` and `public/app.css`.

## Core Entities
- `Department`: organizational grouping for users
- `User`: employee record with department, site, manager, holiday country, and theme preference
- `Absence`: date-based leave record with request status and approval metadata
- `AbsenceOption`: configurable absence code, label, and color
- `Holiday`: stored holiday override keyed by date and country
- `Setting`: application-level key/value configuration
- `AbsenceRequestLog`: audit log for request-related activity

## Important Relationships
- A department has many users.
- A user belongs to a department.
- A user can belong to a manager through `users.manager_id`.
- A user has many absences.
- An absence belongs to a user and optionally references approval metadata.

## Request Lifecycle
1. A user selects a date range in the planner.
2. The request is either auto-approved or stored as pending depending on manager assignment.
3. Multi-day requests share one `request_uuid`.
4. Pending requests remain editable by the request owner.
5. Managers can approve or reject pending requests for direct reports.
6. Rejections must store a `decision_reason`.

## Rendering and State Notes
- Planner day grids are generated from date periods and decorated with holiday and weekend metadata.
- Filters are applied through component state in the main Livewire planner.
- Theme state is shared through the application layout and persisted on the user.
- Public holidays combine computed values from `App\Support\HolidayCalendar` with stored holiday rows.
