# Project Context

## Product summary
LeaveBoard is an internal leave-planning application built with Laravel and Livewire. The current product combines a multi-month visual planner with a lightweight admin workspace for impersonation, branding, and absence-type management.

## Current functionality

### Planner
* Multi-month timeline view based on the selected month plus the following two months.
* Drag-to-select leave creation for the active session user only.
* Month and year navigation, plus a jump-to-today action.
* Expandable department sections with per-day department totals.
* Live filtering by multiple departments, multiple sites, and personnel name search.
* Current-user row spotlight and quick jump back to that row.

### Approval flow
* Users can have a manager through `users.manager_id`.
* Requests submitted by users with a manager are created as `pending`.
* Requests for users without a manager are auto-approved.
* Multi-day requests are grouped by `absences.request_uuid`.
* Request owners can edit or delete pending requests before approval.
* Managers can approve or reject pending requests for their direct reports.
* Approval metadata is stored in `absences.status`, `approved_by`, and `approved_at`.

### Administration
* `/admin` is a proof-of-concept admin workspace.
* Supports session-based impersonation of any seeded user.
* Supports changing the application name via the `settings` table.
* Supports creating configurable absence options with code, label, color, and sort order.
* Shows current users, managers, and configured absence options.

### Holidays and seed data
* Swedish holidays are generated dynamically through `App\Support\SwedishHolidayCalendar`.
* Holiday seeding covers a rolling range from current year - 5 to current year + 5.
* Personnel seeding now assigns a manager to most users in each department.
* Default absence options are seeded from `AbsenceOptionSeeder`.

## Tech stack & dependencies
* **Backend:** Laravel 13, PHP 8.3+.
* **Realtime UI:** Livewire 4.
* **Frontend behavior:** Alpine-style interactions in Blade plus vanilla JavaScript.
* **Styling:** Vanilla CSS only.
* **Icons:** Material Symbols icons via the `.icon` class.
* **Assets:** Vite is present; npm is used for frontend build/dev commands.
* **Testing:** PHPUnit 12.

## Development guidance
* **PHP dependencies:** Use Composer.
* **Frontend dependencies and builds:** Use npm only when asset installation or Vite builds are required.
* **Frontend libraries:** Avoid jQuery.
* **CSS strategy:** Prefer reusable Vanilla CSS and preserve the existing handcrafted UI style.
* **UI frameworks:** Do NOT introduce Tailwind CSS or Bootstrap.
* **Branding:** The shared branded layout uses `public/brand/leaveboard-mark.svg`.

## Important files
* `app/Livewire/VacationPlanner.php` — planner state, filters, approvals, and request editing.
* `app/Http/Controllers/AdminController.php` — admin page actions.
* `app/Http/Middleware/EnsureCurrentUser.php` — current-user session bootstrap.
* `app/Models/Absence.php` — approval-aware absence model.
* `app/Models/AbsenceOption.php` — configurable leave types.
* `app/Models/Setting.php` — persistent app settings.
* `app/Support/SwedishHolidayCalendar.php` — computed Swedish public holidays.
* `resources/views/livewire/vacation-planner.blade.php` — planner interface.
* `resources/views/admin/index.blade.php` — admin workspace.
* `resources/views/components/layouts/app.blade.php` — branded shell layout.

## Livewire components
* **Creation:** Prefer `php artisan make:livewire` for new components.
* **Manual warning:** Be careful when manually creating Livewire file paths; use the real paths under `app/Livewire/...` and `resources/views/livewire/...`.

## Restrictions
* **No browser:** Never open a browser to view the project.