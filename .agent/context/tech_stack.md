# Tech Stack and Development Guidance

## Stack
- Framework: Laravel 13
- PHP: 8.3+
- Reactive UI: Livewire 4
- Templates: Blade
- Frontend interactions: Alpine-style JavaScript
- Styling: handcrafted vanilla CSS in `public/app.css`
- Assets: Vite and npm
- Testing: PHPUnit 12

## Development Commands
- Install PHP packages: `composer install`
- Install frontend packages: `npm install`
- Start local development: `composer run dev`
- Build assets: `npm run build`
- Run tests: `composer test`
- Refresh seeded database: `php artisan migrate:fresh --seed`

## Project Conventions
- Keep styles in the existing handcrafted CSS approach.
- Avoid introducing Tailwind, Bootstrap, or jQuery.
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
