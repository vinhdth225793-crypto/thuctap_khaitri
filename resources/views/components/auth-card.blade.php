@props([
    'title',
    'subtitle',
    'logo' => 'logo.png', // tên file logo
    'formAction' => '',
    'formMethod' => 'POST'
])

<div class="auth-container">
    <div class="auth-card fade-in">
        <div class="auth-header">
            <div class="auth-logo">
                <img 
                    src="{{ asset('images/logo/' . $logo) }}" 
                    alt="Logo" 
                    class="auth-logo-img"
                >
            </div>

            <h1 class="auth-title">{{ $title }}</h1>
            <p class="auth-subtitle">{{ $subtitle }}</p>
        </div>
        
        <div class="auth-body">
            {{ $slot }}
        </div>
        
        <div class="auth-footer">
            <p class="mb-0 text-muted small">
                © {{ date('Y') }} Hệ thống Quản lý. Tất cả các quyền được bảo lưu.
            </p>
        </div>
    </div>
</div>