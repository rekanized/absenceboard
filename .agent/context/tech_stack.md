# Tech Stack & Coding Standards

## Core Technologies
- **Framework**: [Laravel 11.x](https://laravel.com) (PHP 8.2+)
- **Frontend Interaction**: [Livewire 3.x](https://livewire.laravel.com) & [Alpine.js](https://alpinejs.dev)
- **Styling**: Vanilla CSS (no Tailwind/Bootstrap as per project rules)
- **Icons**: [Material Symbols Outlined](https://fonts.google.com/icons) (custom `.icon` class implementation)
- **Database**: PHP SQLite / MySQL

## Development Standards
- **Package Management**: **Composer ONLY**. NPM is not used or allowed for build steps.
- **CSS Strategy**: Reusable Vanilla CSS classes. Avoid utility-first frameworks.
- **Livewire**: Always create components using `php artisan make:livewire`.
- **Media**: All iconography must use the `.icon` class with Material Symbol names.
- **AI Context**: Always maintain the `.agent/context` files for continuity.

## Key Components
- **Layout**: `resources/views/layouts/app.blade.php` (Global styles and font imports)
- **Main View**: `resources/views/livewire/vacation-planner.blade.php`
- **Controller**: `app/Livewire/VacationPlanner.php`
