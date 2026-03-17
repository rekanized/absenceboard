# Personnel Absence Planner 2026

A modern, high-performance web application for managing and visualizing personnel absences across multiple sites and departments. Built with Laravel, Livewire, and Alpine.js.

## Key Features

- **Interactive Absence Grid**: Fast, responsive grid for viewing and managing absences.
- **Range Selection**: Intuitive drag-to-select range selection for applying absences over multiple days.
- **Site Filtering**: Real-time filtering to focus on specific locations or sites.
- **Dynamic Swedish Data**: Seeders generate authentic Swedish personnel names for testing.
- **Modern UI**: Clean aesthetics using Vanilla CSS and Material Symbols.

## Tech Stack

- **Backend**: Laravel 11.x
- **Frontend**: Livewire, Alpine.js, Vanilla CSS
- **Icons**: Material Symbols Outlined
- **Database**: SQLite (default) / MySQL

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer

### Installation

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd vacations
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Initialize Database and Seed Data**:
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

5. **Run the application**:
   ```bash
   php artisan serve
   ```

## Development Guidelines

- **Icons**: Always use `<span class="icon">icon_name</span>` with Material Symbols.
- **Styles**: Use Vanilla CSS in `resources/css/app.css` or embedded in Blade for component-specific styles.
- **Package Management**: Use Composer ONLY for all dependencies.

## License

The Personnel Absence Planner is open-sourced software licensed under the [MIT license](LICENSE).
