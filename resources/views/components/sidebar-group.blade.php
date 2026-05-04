@props([
    'id',                    // ID cho collapse target.
    'open' => false,         // Trạng thái mở mặc định (thường là routeIs() truyền vào).
    'icon' => 'fas fa-folder',
    'tone' => 'primary',
    'tooltip' => null,
    'badge' => null,         // Số đếm hiển thị badge bên cạnh label, null thì không hiện.
    'badgePulse' => false,   // True để dùng class edu-badge-pulse.
])

<div class="nav-item">
    <a class="edu-link-parent {{ $open ? '' : 'collapsed' }}"
       data-bs-toggle="collapse" data-bs-target="#{{ $id }}" role="button"
       aria-expanded="{{ $open ? 'true' : 'false' }}"
       @if($tooltip !== null) data-tooltip="{{ $tooltip }}" @endif>
        <div class="edu-icon-circle bg-soft-{{ $tone }}"><i class="{{ $icon }}"></i></div>
        <span class="edu-link-label">{{ $slot }}</span>
        @if($badge !== null && $badge > 0)
            <span class="edu-menu-badge {{ $badgePulse ? 'edu-badge-pulse' : '' }}">{{ $badge }}</span>
        @endif
        <i class="fas fa-chevron-right arrow-toggle {{ ($badge !== null && $badge > 0) ? '' : 'ms-auto' }}"></i>
    </a>
    <div class="collapse {{ $open ? 'show' : '' }}" id="{{ $id }}">
        <div class="edu-submenu-container">
            {{ $items ?? '' }}
        </div>
    </div>
</div>
