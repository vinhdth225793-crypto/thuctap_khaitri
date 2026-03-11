@props([
    'title' => '',
    'value' => '0',
    'icon' => 'fas fa-chart-line',
    'color' => 'primary',
    'trend' => 'up', // up, down, neutral
    'trendValue' => '0%',
    'description' => '',
    'loading' => false
])

@php
    $colorClasses = [
        'primary' => 'stat-primary',
        'secondary' => 'stat-secondary',
        'success' => 'stat-success',
        'danger' => 'stat-danger',
        'warning' => 'stat-warning',
        'info' => 'stat-info',
        'light' => 'stat-light',
        'dark' => 'stat-dark'
    ];
    
    $iconColors = [
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'success' => 'text-success',
        'danger' => 'text-danger',
        'warning' => 'text-warning',
        'info' => 'text-info',
        'light' => 'text-dark',
        'dark' => 'text-white'
    ];
@endphp

<div class="stat-card {{ $colorClasses[$color] ?? $colorClasses['primary'] }} {{ $loading ? 'loading' : '' }}" 
     data-aos="fade-up" data-aos-delay="100">
    <div class="stat-icon-wrapper">
        <div class="stat-icon {{ $iconColors[$color] ?? $iconColors['primary'] }}">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
    
    <div class="stat-content">
        <h3 class="stat-value count-up" data-count="{{ $value }}">
            0
        </h3>
        <h6 class="stat-title">{{ $title }}</h6>
        
        @if($description || $trendValue)
        <div class="stat-footer d-flex align-items-center justify-content-between mt-3">
            @if($description)
            <span class="stat-desc text-muted small">{{ $description }}</span>
            @endif
            
            @if($trendValue)
            <span class="stat-trend trend-{{ $trend }}">
                <i class="fas fa-arrow-{{ $trend }} me-1"></i>
                {{ $trendValue }}
            </span>
            @endif
        </div>
        @endif
    </div>
    
    <div class="stat-wave"></div>
</div>