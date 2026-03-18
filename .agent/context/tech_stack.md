# Tech Stack and Development Guidance

## Stack
- Framework: Laravel 13
- PHP: 8.3+
- Reactive UI: Livewire 4
- Templates: Blade
- Frontend interactions: Livewire-driven behavior with vanilla JavaScript where needed
- Styling: handcrafted vanilla CSS in `public/app.css`
- Assets: no Node, npm, or Vite pipeline; styles are served directly from `public/app.css`
- Testing: PHPUnit 12

## Development Commands
- Install PHP packages: `composer install`
- Prepare a local SQLite setup: `cp .env.example .env && touch database/database.sqlite && php artisan key:generate`
- Fresh seed local database: `php artisan migrate:fresh --seed`
- Start local development: `php artisan serve`
- Run tests: `composer test`

## Project Conventions
- Keep styles in the existing handcrafted CSS approach.
- Avoid introducing Tailwind, Bootstrap, jQuery, or any frontend build pipeline.
- Prefer minimal Blade and Livewire changes over broad rewrites.
- Update create-table migrations directly when maintaining a clean greenfield schema.
- Use `php artisan make:livewire` for new Livewire components.

## Important Files
- `app/Livewire/VacationPlanner.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Support/HolidayCalendar.php`
- `resources/views/livewire/vacation-planner.blade.php`
- `resources/views/admin/index.blade.php`
- `resources/views/profile/show.blade.php`
- `public/app.css`
