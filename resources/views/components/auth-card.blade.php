@props([
    'title',
    'subtitle',
    'logo' => 'logo.png', // tên file logo
    'formAction' => '',
    'formMethod' => 'POST'
])

@php
    $siteName = \App\Models\SystemSetting::get('site_name', config('app.name', 'Khải Trí'));
    $siteLogo = \App\Models\SystemSetting::get('site_logo', '');
    $hotline = \App\Models\SystemSetting::get('hotline', '0900 000 000');
    $email = \App\Models\SystemSetting::get('email', \App\Models\SystemSetting::get('site_email', 'tuvan@khaitri.edu.vn'));
    $addressRaw = \App\Models\SystemSetting::get('address', 'Trung tâm đào tạo Khải Trí');
    $logoSrc = filled($siteLogo) ? asset($siteLogo) : asset('images/logo/' . $logo);

    // Xử lý địa chỉ: nếu admin nhập HTML có link, extract URL; nếu không có, build link Google Maps tự động
    $addressText = trim(strip_tags($addressRaw)) ?: 'Trung tâm đào tạo Khải Trí';
    $addressUrl = null;
    if (preg_match('/href\s*=\s*["\']([^"\']+)["\']/i', $addressRaw, $m)) {
        $addressUrl = $m[1];
    } elseif (filled($addressText)) {
        $addressUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($addressText);
    }

    $hotlineClean = preg_replace('/\s+/', '', $hotline);
@endphp

<div class="auth-container">
    <div class="auth-stage fade-in">
        <aside class="auth-brand-card">
            <a href="{{ route('home') }}" class="auth-home-link">
                <i class="fas fa-arrow-left"></i>
                <span>Về trang chủ</span>
            </a>

            <div class="auth-brand-main">
                <div class="auth-logo">
                    <img 
                        src="{{ $logoSrc }}" 
                        alt="{{ $siteName }}" 
                        class="auth-logo-img"
                    >
                </div>

                <p class="auth-eyebrow">Trung tâm đào tạo</p>
                <h1 class="auth-brand-title">{{ $siteName }}</h1>
                <p class="auth-brand-slogan">
                    Học chắc nền tảng, luyện đúng mục tiêu, đồng hành đến khi bạn tự tin tiến lên.
                </p>
            </div>

            <div class="auth-brand-stats">
                <div>
                    <strong>1:1</strong>
                    <span>Tư vấn lộ trình</span>
                </div>
                <div>
                    <strong>Online</strong>
                    <span>Theo dõi tiến độ</span>
                </div>
                <div>
                    <strong>Rõ ràng</strong>
                    <span>Lịch học, tài liệu, kết quả</span>
                </div>
            </div>

            <div class="auth-brand-list">
                <div class="auth-brand-item">
                    <i class="fas fa-route"></i>
                    <span>Lộ trình học được sắp xếp theo từng mục tiêu.</span>
                </div>
                <div class="auth-brand-item">
                    <i class="fas fa-chalkboard-user"></i>
                    <span>Giảng viên đồng hành trong từng buổi học.</span>
                </div>
                <div class="auth-brand-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Kết quả và tiến độ được cập nhật ngay trên hệ thống.</span>
                </div>
            </div>

            <div class="auth-contact-strip">
                <a href="tel:{{ $hotlineClean }}" class="auth-contact-link" aria-label="Gọi {{ $hotline }}">
                    <i class="fas fa-phone"></i>
                    <span>{{ $hotline }}</span>
                </a>
                <a href="mailto:{{ $email }}" class="auth-contact-link" aria-label="Gửi email">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $email }}</span>
                </a>
                @if($addressUrl)
                    <a href="{{ $addressUrl }}" target="_blank" rel="noopener" class="auth-contact-link auth-contact-address" aria-label="Xem bản đồ địa chỉ">
                        <i class="fas fa-location-dot"></i>
                        <span>{{ \Illuminate\Support\Str::limit($addressText, 56) }}</span>
                        <i class="fas fa-arrow-up-right-from-square auth-contact-ext"></i>
                    </a>
                @else
                    <span class="auth-contact-link auth-contact-static">
                        <i class="fas fa-location-dot"></i>
                        <span>{{ \Illuminate\Support\Str::limit($addressText, 56) }}</span>
                    </span>
                @endif
            </div>
        </aside>

        <section class="auth-form-card">
            <div class="auth-header">
                <span class="auth-form-kicker">Tài khoản học tập</span>
                <h1 class="auth-title">{{ $title }}</h1>
                <p class="auth-subtitle">{{ $subtitle }}</p>
            </div>
            
            <div class="auth-body">
                {{ $slot }}
            </div>
            
            <div class="auth-footer">
                <p class="mb-0 text-muted small">
                    © {{ date('Y') }} {{ $siteName }}. Tất cả các quyền được bảo lưu.
                </p>
            </div>
        </section>
    </div>
</div>
