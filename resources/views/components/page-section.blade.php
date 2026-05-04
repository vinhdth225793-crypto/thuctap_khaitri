@props([
    'number' => null,        // Số thứ tự section (null thì không hiện)
    'icon' => null,          // Icon FontAwesome cho title
    'title' => '',           // Tiêu đề section
    'subtitle' => null,      // Mô tả phụ
    'wrapperClass' => 'apx-section', // Cho phép override class wrapper
])

<section class="{{ $wrapperClass }}">
    <header class="apx-section-head">
        <div class="apx-section-title">
            @if($number !== null)
                <span class="apx-section-num">{{ $number }}</span>
            @endif
            <div>
                <h2>
                    @if($icon)<i class="{{ $icon }}"></i>@endif {{ $title }}
                </h2>
                @if($subtitle)
                    <p>{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @isset($meta)
            <div class="apx-section-meta">{{ $meta }}</div>
        @endisset
    </header>
    {{ $slot }}
</section>
