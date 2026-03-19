<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        $applicationName = $this->normalizeApplicationName(Setting::valueFor('app_name', config('app.name')));
        $applicationTimezone = Setting::valueFor('app_timezone');

        if ($applicationName !== null && $applicationName !== '') {
            Config::set('app.name', $applicationName);
        }

        if ($applicationTimezone !== null && $applicationTimezone !== '') {
            Config::set('app.timezone', $applicationTimezone);
            date_default_timezone_set($applicationTimezone);
        }
    }

    private function normalizeApplicationName(?string $applicationName): ?string
    {
        if ($applicationName === 'AbsenseBoard') {
            return 'AbsenceBoard';
        }

        return $applicationName;
    }
}
