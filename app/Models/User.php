<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public const THEME_LIGHT = 'light';
    public const THEME_DARK = 'dark';

    protected $fillable = ['department_id', 'manager_id', 'name', 'location', 'holiday_country', 'theme_preference', 'is_active'];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public static function supportedThemePreferences(): array
    {
        return [
            self::THEME_LIGHT,
            self::THEME_DARK,
        ];
    }

    public function prefersDarkTheme(): bool
    {
        return $this->theme_preference === self::THEME_DARK;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function absences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Absence::class);
    }
}
