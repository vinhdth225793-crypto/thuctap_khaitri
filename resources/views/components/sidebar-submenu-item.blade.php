@props([
    'route' => null,
    'href' => null,
    'pattern' => null,
    'icon' => 'fas fa-circle',
    'badge' => null,         // Số đếm bên cạnh label, null thì không hiện.
])

@php
    $url = $route ? route($route) : ($href ?? '#');
    $patterns = $pattern ?? $route;
    $isActive = false;
    if ($patterns) {
        $isActive = is_array($patterns) ? request()->routeIs(...$patterns) : request()->routeIs($patterns);
    }
@endphp

<a href="{{ $url }}" class="edu-submenu-item {{ $isActive ? 'active' : '' }}">
    <i class="{{ $icon }}"></i>
    <span>{{ $slot }}</span>
    @if($badge !== null && $badge > 0)
        <span class="edu-submenu-badge">{{ $badge }}</span>
    @endif
</a>
