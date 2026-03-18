<x-layouts.app :layout-current-user="$layoutCurrentUser ?? ($currentUser ?? null)" :title="$title ?? null">
    {{ $slot }}
</x-layouts.app>
