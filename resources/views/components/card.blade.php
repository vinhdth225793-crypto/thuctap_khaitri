@props([
    'title' => '',
    'subtitle' => '',
    'icon' => '',
    'color' => 'primary',
    'hover' => true,
    'padding' => true
])

@php
    $colors = [
        'primary' => 'bg-primary bg-gradient',
        'secondary' => 'bg-secondary bg-gradient',
        'success' => 'bg-success bg-gradient',
        'danger' => 'bg-danger bg-gradient',
        'warning' => 'bg-warning bg-gradient',
        'info' => 'bg-info bg-gradient',
        'light' => 'bg-light',
        'dark' => 'bg-dark bg-gradient'
    ];
    
    $iconColors = [
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'success' => 'text-success',
        'danger' => 'text-danger',
        'warning' => 'text-warning',
        'info' => 'text-info',
        'light' => 'text-dark',
        'dark' => 'text-light'
    ];
@endphp

<div class="card vip-card {{ $hover ? 'hover-lift' : '' }} {{ $padding ? 'p-4' : '' }}">
    @if($icon || $title)
    <div class="card-header border-0 bg-transparent {{ $padding ? 'pb-2' : '' }}">
        <div class="d-flex align-items-center">
            @if($icon)
            <div class="card-icon-wrapper me-3">
                <div class="card-icon {{ $colors[$color] ?? $colors['primary'] }} rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas {{ $icon }} text-white"></i>
                </div>
            </div>
            @endif
            
            <div class="flex-grow-1">
                @if($title)
                <h5 class="card-title mb-0 {{ $iconColors[$color] ?? $iconColors['primary'] }} fw-semibold">
                    {{ $title }}
                </h5>
                @endif
                
                @if($subtitle)
                <p class="card-subtitle mb-0 text-muted small">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <div class="card-body {{ $padding ? 'pt-3' : '' }}">
        {{ $slot }}
    </div>
</div>