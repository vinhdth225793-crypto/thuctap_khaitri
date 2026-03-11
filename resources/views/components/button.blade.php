@props([
    'type' => 'button',
    'variant' => 'primary',
    'icon' => null,
    'loading' => false,
    'size' => 'normal'
])

<button 
    type="{{ $type }}" 
    class="btn vip-btn vip-btn-{{ $variant }} {{ $loading ? 'btn-loading' : '' }} {{ $size === 'small' ? 'btn-sm' : ($size === 'large' ? 'btn-lg' : '') }}"
    {{ $attributes }}
>
    @if($icon)
        <i class="fas {{ $icon }} me-2"></i>
    @endif
    {{ $slot }}
</button>