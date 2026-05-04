@props([
    'icon' => null,
    'badge' => null, // Số đếm bên cạnh label, null thì không hiện.
])

<div class="edu-section-label">
    @if($icon)<i class="{{ $icon }}"></i>@endif {{ $slot }}
    @if($badge !== null && $badge > 0)
        <span class="edu-section-badge">{{ $badge }}</span>
    @endif
</div>
