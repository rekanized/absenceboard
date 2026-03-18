<p align="center">
  <img src="public/brand/leaveboard-mark.svg" alt="LeaveBoard logo" width="88" height="88">
</p>

<h1 align="center">LeaveBoard</h1>

<p align="center">
  A Laravel-based leave planning app for visualising team availability, submitting absence requests,
  and handling manager approvals across departments and sites.
</p>

## What It Does

LeaveBoard gives teams a shared planning surface for time off and availability.

- Visual multi-month planner for team absences
- Configurable absence options with labels, codes, and colors
- Manager approval flow for submitted requests
- User profile page with holiday-country and theme preferences
- Lightweight admin area for impersonation, settings, and absence-option management
- Country-aware public holiday support

## Highlights

- Planner navigation across the current month and the following months
- Department, site, and personnel filtering
- Pending request editing before approval
- Approval and rejection tracking with manager decision reasons
- Seeded demo data for departments, personnel, holidays, and absence options

## Tech Stack

- PHP 8.3+
- Laravel 13
- Livewire 4
- Blade, Alpine-style interactions, and vanilla CSS
- Vite for frontend assets
- PHPUnit 12

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install
npm run build
```

Run the app locally:

```bash
composer run dev
```

Run the test suite:

```bash
composer test
```

## Seeded Demo Setup

The default seeders provide departments, users, manager relationships, absence options, holidays, and sample absence history. On first visit, the app stores the first available user in session as the active user.

## Project Notes

- The default product name is LeaveBoard and can be changed from the admin area.
- The admin area is intentionally simple and currently aimed at internal validation and prototyping.
- AI-agent-specific project context lives in `GEMINI.md`, `AGENTS.md`, and `.agent/context/`.

## Support

If you use the app and want to support the project:
https://buymeacoffee.com/rekanized

## License

This project is open-sourced under the [MIT license](LICENSE).
