@props([
    'label' => '',
    'value' => 0,
    'unit' => '',
    'icon' => 'fas fa-chart-line',
    'tone' => 'primary', // primary | success | info | warning | danger | secondary
    'note' => null,
])

<div class="card border-0 shadow-sm h-100 border-start border-4 border-{{ $tone }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="text-xs font-weight-bold text-{{ $tone }} text-uppercase mb-1">{{ $label }}</div>
                <div class="h4 mb-0 font-weight-bold text-gray-800">
                    {{ $value }}@if($unit) {{ $unit }}@endif
                </div>
                @if($note)
                    <div class="small text-muted mt-1">{{ $note }}</div>
                @endif
            </div>
            <i class="{{ $icon }} fa-2x text-gray-300"></i>
        </div>
    </div>
</div>
