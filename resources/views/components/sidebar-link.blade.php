@props([
    'route' => null,        // Route name (vd: 'admin.dashboard'). Có thể null nếu dùng href trực tiếp.
    'href' => null,         // URL trực tiếp khi không dùng route name.
    'pattern' => null,      // Pattern cho routeIs() để xác định active. Default = $route nếu không truyền.
    'icon' => 'fas fa-link',
    'tone' => 'primary',    // primary | info | success | warning | danger | secondary | dark
    'tooltip' => null,      // Default = nội dung slot (label).
    'mini' => false,        // True để thêm class edu-link-mini (footer items).
])

@php
    $url = $route ? route($route) : ($href ?? '#');
    $patterns = $pattern ?? $route;
    $isActive = false;
    if ($patterns) {
        $isActive = is_array($patterns) ? request()->routeIs(...$patterns) : request()->routeIs($patterns);
    }
    $extra = $mini ? ' edu-link-mini' : '';
@endphp

<div class="nav-item">
    <a href="{{ $url }}"
       class="edu-link-parent{{ $extra }} {{ $isActive ? 'active' : '' }}"
       @if($tooltip !== null) data-tooltip="{{ $tooltip }}" @endif>
        <div class="edu-icon-circle bg-soft-{{ $tone }}"><i class="{{ $icon }}"></i></div>
        <span class="edu-link-label">{{ $slot }}</span>
    </a>
</div>
