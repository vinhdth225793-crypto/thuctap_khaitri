@props([
    'id',
    'title' => 'Xác nhận',
    'icon' => null,
    'tone' => 'primary', // primary | success | danger | warning | info
    'action' => null, // URL form. Nếu null thì không render form, chỉ render nút có id để JS handle
    'method' => 'POST', // GET | POST | PUT | PATCH | DELETE
    'confirmId' => null, // Id cho nút xác nhận (dùng khi không có action, JS bind sự kiện)
    'confirmLabel' => 'Xác nhận',
    'confirmIcon' => null,
    'cancelLabel' => 'Hủy',
])

@php
    $toneIcon = [
        'primary' => 'fas fa-circle-info',
        'success' => 'fas fa-check-circle',
        'danger' => 'fas fa-circle-xmark',
        'warning' => 'fas fa-triangle-exclamation',
        'info' => 'fas fa-circle-info',
    ];
    $toneHeaderClass = [
        'primary' => 'bg-primary text-white',
        'success' => 'bg-success text-white',
        'danger' => 'bg-danger text-white',
        'warning' => 'bg-warning text-dark',
        'info' => 'bg-info text-white',
    ];
    $toneBtnClass = [
        'primary' => 'btn-primary',
        'success' => 'btn-success',
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'info' => 'btn-info',
    ];
    $resolvedIcon = $icon ?? $toneIcon[$tone] ?? $toneIcon['primary'];
    $resolvedHeader = $toneHeaderClass[$tone] ?? $toneHeaderClass['primary'];
    $resolvedBtn = $toneBtnClass[$tone] ?? $toneBtnClass['primary'];
    $isFormMethod = in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE'], true);
    $formMethod = $isFormMethod ? 'POST' : strtoupper($method);
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header {{ $resolvedHeader }}">
                <h5 class="modal-title fw-bold"><i class="{{ $resolvedIcon }} me-2"></i> {{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            @if($action)
                <form action="{{ $action }}" method="{{ $formMethod }}">
                    @csrf
                    @if($isFormMethod)
                        @method($method)
                    @endif
                    <div class="modal-body p-4">
                        {{ $slot }}
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">{{ $cancelLabel }}</button>
                        <button type="submit" class="btn {{ $resolvedBtn }} fw-bold px-4">
                            @if($confirmIcon)<i class="{{ $confirmIcon }} me-1"></i>@endif
                            {{ $confirmLabel }}
                        </button>
                    </div>
                </form>
            @else
                <div class="modal-body p-4">
                    {{ $slot }}
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">{{ $cancelLabel }}</button>
                    <button type="button" class="btn {{ $resolvedBtn }} fw-bold px-4"
                            @if($confirmId) id="{{ $confirmId }}" @endif>
                        @if($confirmIcon)<i class="{{ $confirmIcon }} me-1"></i>@endif
                        {{ $confirmLabel }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
