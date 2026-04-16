@extends('layouts.home')

@section('title', ($settings['site_name'] ?: 'Khải Trí') . ' - Trang chủ')

@php
    $siteName = $settings['site_name'] ?: 'Khải Trí';
    if (\Illuminate\Support\Str::contains($siteName, ['Ã', 'Â', '�', 'ï¿½'])) {
        $siteName = 'Khải Trí';
    }

    $homeUser = auth()->user();
    $firstBanner = $heroBanner ?? null;
    $sliderHighlight = $sliderBanners->first();
    $heroImage = $firstBanner?->duong_dan_anh ?: ($featuredCourse?->hinh_anh ?: 'images/khoa-hoc/1773463343_b1-vstep.jpg');
    $heroTitle = filled($firstBanner?->tieu_de)
        ? $firstBanner->tieu_de
        : 'Chọn khóa phù hợp, theo dõi lộ trình, nhận hỗ trợ khi cần.';
    $heroDescription = filled($firstBanner?->mo_ta)
        ? \Illuminate\Support\Str::limit(strip_tags($firstBanner->mo_ta), 220)
        : "{$siteName} giúp học viên và khách mới nhanh chóng xem khóa học, lịch khai giảng, giảng viên phụ trách và kênh liên hệ chính thức.";
    $heroLink = $firstBanner?->link;

    $levelLabels = [
        'co_ban' => ['label' => 'Cơ bản', 'class' => 'tone-good'],
        'trung_binh' => ['label' => 'Trung bình', 'class' => 'tone-warm'],
        'nang_cao' => ['label' => 'Nâng cao', 'class' => 'tone-alert'],
    ];

    $statusLabels = [
        'dang_day' => ['label' => 'Đang giảng dạy', 'class' => 'tone-good'],
        'san_sang' => ['label' => 'Sẵn sàng khai giảng', 'class' => 'tone-info'],
        'cho_giang_vien' => ['label' => 'Đang hoàn thiện lịch', 'class' => 'tone-warm'],
    ];

    $dashboardRoute = null;
    $courseAreaRoute = route('dang-ky');
    $courseAreaLabel = 'Ghi danh';

    if ($homeUser) {
        $dashboardRoute = match ($homeUser->vai_tro) {
            'admin' => route('admin.dashboard'),
            'giang_vien' => route('giang-vien.dashboard'),
            default => route('hoc-vien.dashboard'),
        };

        $courseAreaRoute = match ($homeUser->vai_tro) {
            'admin' => route('admin.khoa-hoc.index'),
            'giang_vien' => route('giang-vien.khoa-hoc'),
            default => route('hoc-vien.khoa-hoc-tham-gia'),
        };

        $courseAreaLabel = match ($homeUser->vai_tro) {
            'admin' => 'Quản lý khóa',
            'giang_vien' => 'Khóa phụ trách',
            default => 'Tham gia khóa',
        };
    }

    $imageUrl = function (?string $path, string $fallback = 'images/default-course.svg') {
        $path = $path ?: $fallback;
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    };

    $avatarUrl = function (?string $path) {
        if (! $path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['avatars/'])) {
            return asset('storage/' . $path);
        }

        return \Illuminate\Support\Str::contains($path, '/') ? asset($path) : asset('images/' . $path);
    };

    $cleanHotline = !empty($settings['hotline']) ? preg_replace('/\s+/', '', $settings['hotline']) : null;
    $zaloLink = null;
    if (!empty($settings['zalo'])) {
        $zaloLink = \Illuminate\Support\Str::startsWith($settings['zalo'], ['http://', 'https://'])
            ? $settings['zalo']
            : 'https://zalo.me/' . preg_replace('/\D+/', '', $settings['zalo']);
    }

    $facebookLink = null;
    if (!empty($settings['facebook'])) {
        $facebookLink = \Illuminate\Support\Str::startsWith($settings['facebook'], ['http://', 'https://'])
            ? $settings['facebook']
            : 'https://facebook.com/' . ltrim($settings['facebook'], '@/');
    }

    $accountName = $homeUser?->ho_ten ?: 'Tài khoản';
    $accountInitial = \Illuminate\Support\Str::upper(mb_substr($accountName, 0, 1));
    $accountAvatar = null;

    if ($homeUser?->anh_dai_dien) {
        $accountAvatar = \Illuminate\Support\Str::startsWith($homeUser->anh_dai_dien, ['http://', 'https://'])
            ? $homeUser->anh_dai_dien
            : asset(\Illuminate\Support\Str::startsWith($homeUser->anh_dai_dien, ['avatars/'])
                ? 'storage/' . $homeUser->anh_dai_dien
                : $homeUser->anh_dai_dien);
    }

    $accountRoleLabel = match ($homeUser?->vai_tro) {
        'admin' => 'Quản trị viên',
        'giang_vien' => 'Giảng viên',
        'hoc_vien' => 'Học viên',
        default => 'Thành viên',
    };
@endphp

@section('content')
@if(filled($settings['general_notification']))
    <aside class="site-announcement">
        <div class="home-container">
            <i class="fas fa-bullhorn"></i>
            <div>{!! $settings['general_notification'] !!}</div>
        </div>
    </aside>
@endif

<header class="site-header" id="siteHeader">
    <div class="home-container header-row">
        <a href="{{ route('home') }}" class="brand-link" aria-label="Trang chủ {{ $siteName }}">
            <span class="brand-mark">
                @if(!empty($settings['site_logo']))
                    <img src="{{ asset($settings['site_logo']) }}" alt="{{ $siteName }}">
                @else
                    <i class="fas fa-graduation-cap"></i>
                @endif
            </span>
            <span class="brand-copy">
                <strong>{{ $siteName }}</strong>
                <small>Học tập rõ ràng, theo dõi dễ dàng</small>
            </span>
        </a>

        <nav class="site-nav" id="siteNav" aria-label="Điều hướng trang chủ">
            <a href="#home" class="is-active" data-scroll-link="home">
                <i class="fas fa-house"></i>
                <span>Trang chủ</span>
            </a>
            <a href="#courses" data-scroll-link="courses">
                <i class="fas fa-layer-group"></i>
                <span>Khóa học</span>
            </a>
            <a href="#updates" data-scroll-link="updates">
                <i class="fas fa-bullhorn"></i>
                <span>Thông tin mới</span>
            </a>
            <a href="#instructors" data-scroll-link="instructors">
                <i class="fas fa-chalkboard-user"></i>
                <span>Giảng viên</span>
            </a>
            <a href="#contact" data-scroll-link="contact">
                <i class="fas fa-headset"></i>
                <span>Liên hệ</span>
            </a>
        </nav>

        <div class="header-actions" id="headerActions">
            @auth
                <a href="{{ $dashboardRoute }}" class="account-chip" aria-label="Mở tài khoản {{ $accountName }}">
                    <span class="account-avatar" aria-hidden="true">
                        @if($accountAvatar)
                            <img src="{{ $accountAvatar }}" alt="">
                        @else
                            <span>{{ $accountInitial }}</span>
                        @endif
                    </span>
                    <span class="account-copy">
                        <strong>{{ $accountName }}</strong>
                        <small>{{ $accountRoleLabel }}</small>
                    </span>
                </a>
                <a href="{{ $dashboardRoute }}" class="btn-soft">Bảng điều khiển</a>
                <form action="{{ route('dang-xuat') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-main">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('dang-nhap') }}" class="btn-soft">Đăng nhập</a>
                <a href="{{ route('dang-ky') }}" class="btn-main">Đăng ký</a>
            @endauth
        </div>

        <button type="button" class="menu-button" id="menuButton" aria-label="Mở menu" aria-controls="siteNav headerActions" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<div class="contact-shortcuts" aria-label="Liên hệ nhanh">
    @if($cleanHotline)
        <a href="tel:{{ $cleanHotline }}" class="contact-icon" aria-label="Gọi {{ $settings['hotline'] }}">
            <i class="fas fa-phone"></i>
        </a>
    @endif
    @if($zaloLink)
        <a href="{{ $zaloLink }}" class="contact-icon" target="_blank" rel="noopener" aria-label="Nhắn Zalo">
            <i class="fas fa-comment-dots"></i>
        </a>
    @endif
    @if($facebookLink)
        <a href="{{ $facebookLink }}" class="contact-icon" target="_blank" rel="noopener" aria-label="Mở Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
    @endif
    @if(!empty($settings['email']))
        <a href="mailto:{{ $settings['email'] }}" class="contact-icon" aria-label="Gửi email">
            <i class="fas fa-envelope"></i>
        </a>
    @endif
    @unless($cleanHotline || $zaloLink || $facebookLink || !empty($settings['email']))
        <a href="#contact" class="contact-icon" aria-label="Mở phần liên hệ">
            <i class="fas fa-headset"></i>
        </a>
    @endunless
</div>

<main>
    @include('pages.home._home-main')
</main>

<footer class="site-footer">
    <div class="home-container footer-grid">
        <div>
            <a href="{{ route('home') }}" class="brand-link footer-brand">
                <span class="brand-mark">
                    @if(!empty($settings['site_logo']))
                        <img src="{{ asset($settings['site_logo']) }}" alt="{{ $siteName }}">
                    @else
                        <i class="fas fa-graduation-cap"></i>
                    @endif
                </span>
                <span>
                    <strong>{{ $siteName }}</strong>
                    <small>Nền tảng đào tạo trực tuyến</small>
                </span>
            </a>
            <p>Thông tin khóa học, lịch học, giảng viên và kênh liên hệ được cập nhật từ hệ thống quản trị để học viên theo dõi thuận tiện.</p>
        </div>

        <nav aria-label="Liên kết trang chủ">
            <strong>Khám phá</strong>
            <a href="#courses">Khóa học</a>
            <a href="#updates">Thông tin mới</a>
            <a href="#instructors">Giảng viên</a>
            <a href="#contact">Liên hệ</a>
        </nav>

        <nav aria-label="Tài khoản">
            <strong>Tài khoản</strong>
            @auth
                <a href="{{ $dashboardRoute }}">Bảng điều khiển</a>
            @else
                <a href="{{ route('dang-nhap') }}">Đăng nhập</a>
                <a href="{{ route('dang-ky') }}">Đăng ký</a>
            @endauth
            @if(!empty($settings['hotline']))
                <a href="tel:{{ preg_replace('/\s+/', '', $settings['hotline']) }}">{{ $settings['hotline'] }}</a>
            @endif
        </nav>
    </div>

    <div class="home-container footer-bottom">
        <span>&copy; {{ now()->year }} {{ $siteName }}. Cập nhật từ hệ thống quản trị.</span>
    </div>
</footer>
@endsection

@include('pages.home._home-styles')
@include('pages.home._home-scripts')
