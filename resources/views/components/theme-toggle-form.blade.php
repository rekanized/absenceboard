@props([
    'theme' => \App\Models\User::THEME_LIGHT,
    'heading' => 'Appearance',
    'copy' => 'Saved to your profile.',
    'buttonClass' => '',
])

@php
    $isDarkTheme = $theme === \App\Models\User::THEME_DARK;
    $nextTheme = $isDarkTheme ? \App\Models\User::THEME_LIGHT : \App\Models\User::THEME_DARK;
@endphp

<form method="POST" action="{{ route('profile.theme.update') }}" {{ $attributes->class(['theme-toggle-form']) }}>
    @csrf
    @method('PATCH')

    <input type="hidden" name="theme_preference" value="{{ $nextTheme }}">

    <div class="theme-toggle-copy-wrap">
        <span class="theme-toggle-label">{{ $heading }}</span>
        <span class="theme-toggle-copy">{{ $copy }}</span>
    </div>

    <x-loading-button
        type="submit"
        class="theme-toggle-button {{ $buttonClass }} {{ $isDarkTheme ? 'is-active' : '' }}"
        aria-label="Toggle dark mode"
        aria-pressed="{{ $isDarkTheme ? 'true' : 'false' }}"
    >
        <span class="theme-toggle-track" aria-hidden="true">
            <span class="theme-toggle-thumb"></span>
        </span>

        <span class="theme-toggle-button-copy">
            <span class="theme-toggle-button-title">{{ $isDarkTheme ? 'Dark mode on' : 'Light mode on' }}</span>
            <span class="theme-toggle-button-meta">Switch to {{ $isDarkTheme ? 'light' : 'dark' }} mode</span>
        </span>
    </x-loading-button>
</form>