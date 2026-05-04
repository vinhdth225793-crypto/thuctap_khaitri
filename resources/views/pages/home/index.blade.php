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

<div class="site-progress" aria-hidden="true"><div class="site-progress-bar" id="siteProgressBar"></div></div>

<div class="site-topbar" aria-label="Thông tin liên hệ nhanh">
    <div class="home-container topbar-row">
        <div class="topbar-info">
            @if($cleanHotline)
                <a href="tel:{{ $cleanHotline }}" class="topbar-link">
                    <i class="fas fa-phone-volume"></i>
                    <span>{{ $settings['hotline'] }}</span>
                </a>
            @endif
            @if(filled($settings['email']))
                <a href="mailto:{{ $settings['email'] }}" class="topbar-link">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $settings['email'] }}</span>
                </a>
            @endif
            @if(filled($settings['address']))
                <span class="topbar-link topbar-static">
                    <i class="fas fa-location-dot"></i>
                    <span>{{ \Illuminate\Support\Str::limit(strip_tags($settings['address']), 60) }}</span>
                </span>
            @endif
        </div>
        <div class="topbar-extras">
            @if(!empty($stats['sap_khai_giang']) && $stats['sap_khai_giang'] > 0)
                <a href="#courses" class="topbar-pill">
                    <span class="topbar-pulse"></span>
                    <i class="fas fa-fire"></i>
                    <span>{{ $stats['sap_khai_giang'] }} khóa sắp khai giảng</span>
                </a>
            @endif
            <div class="topbar-social">
                <span class="topbar-social-label">Theo dõi:</span>
                @if($facebookLink)
                    <a href="{{ $facebookLink }}" target="_blank" rel="noopener" aria-label="Facebook" class="topbar-social-icon"><i class="fab fa-facebook-f"></i></a>
                @endif
                @if($zaloLink)
                    <a href="{{ $zaloLink }}" target="_blank" rel="noopener" aria-label="Zalo" class="topbar-social-icon"><i class="fas fa-comment-dots"></i></a>
                @endif
                <a href="#contact" aria-label="YouTube" class="topbar-social-icon"><i class="fab fa-youtube"></i></a>
                <a href="#contact" aria-label="TikTok" class="topbar-social-icon"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </div>
</div>

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
            @guest
                <a href="#about" data-scroll-link="about">
                    <i class="fas fa-circle-info"></i>
                    <span>Về chúng tôi</span>
                </a>
            @endguest

            <div class="nav-dropdown is-mega">
                <a href="#courses" data-scroll-link="courses" class="nav-dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-layer-group"></i>
                    <span>Khóa học</span>
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                @if(isset($categories) && $categories->isNotEmpty())
                    <div class="nav-mega-menu" role="menu">
                        <div class="nav-mega-grid">
                            {{-- Cột 1: Lĩnh vực đào tạo --}}
                            <div class="nav-mega-col">
                                <div class="nav-mega-head">
                                    <span class="eyebrow"><i class="fas fa-shapes"></i> Lĩnh vực</span>
                                    <strong>Nhóm ngành đang mở</strong>
                                </div>
                                <div class="nav-mega-list">
                                    @foreach($categories->take(6) as $cat)
                                        <a href="{{ route('home', ['category' => $cat->id]) }}#courses" class="nav-mega-item" role="menuitem">
                                            <span class="ndi-icon"><i class="fas fa-cube"></i></span>
                                            <span class="ndi-copy">
                                                <strong>{{ $cat->ten_nhom_nganh }}</strong>
                                                <small>{{ $cat->public_course_count }} khóa</small>
                                            </span>
                                            <i class="fas fa-arrow-right ndi-arrow"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Cột 2: Khóa học hot --}}
                            <div class="nav-mega-col">
                                <div class="nav-mega-head">
                                    <span class="eyebrow"><i class="fas fa-fire"></i> Đang hot</span>
                                    <strong>Khóa được quan tâm</strong>
                                </div>
                                <div class="nav-mega-list">
                                    @foreach($courses->take(4) as $kh)
                                        <a href="{{ route('home', ['q' => $kh->ma_khoa_hoc]) }}#courses" class="nav-mega-course" role="menuitem">
                                            <img src="{{ $imageUrl($kh->hinh_anh) }}" alt="{{ $kh->ten_khoa_hoc }}">
                                            <span class="nmc-copy">
                                                <strong>{{ \Illuminate\Support\Str::limit($kh->ten_khoa_hoc, 38) }}</strong>
                                                <small>
                                                    <i class="fas fa-layer-group"></i> {{ $kh->module_hocs_count ?? 0 }} module
                                                    @if($kh->hoc_vien_dang_hoc_count ?? 0)
                                                        <span class="dot-sep">•</span>
                                                        <i class="fas fa-users"></i> {{ $kh->hoc_vien_dang_hoc_count }}
                                                    @endif
                                                </small>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Cột 3: Khóa nổi bật + CTA --}}
                            <div class="nav-mega-col nav-mega-feature">
                                @if($featuredCourse)
                                    <a href="{{ route('home', ['q' => $featuredCourse->ma_khoa_hoc]) }}#courses" class="nav-mega-hero">
                                        <img src="{{ $imageUrl($featuredCourse->hinh_anh) }}" alt="{{ $featuredCourse->ten_khoa_hoc }}">
                                        <div class="nav-mega-hero-overlay">
                                            <span class="nav-hero-badge"><i class="fas fa-star"></i> Tiêu biểu</span>
                                            <strong>{{ \Illuminate\Support\Str::limit($featuredCourse->ten_khoa_hoc, 50) }}</strong>
                                            <small>{{ $featuredCourse->module_hocs_count ?? 0 }} module · {{ $featuredCourse->cap_do === 'co_ban' ? 'Cơ bản' : ($featuredCourse->cap_do === 'nang_cao' ? 'Nâng cao' : 'Trung bình') }}</small>
                                        </div>
                                    </a>
                                @endif
                                <div class="nav-mega-cta">
                                    <a href="#courses" class="btn-main">
                                        <i class="fas fa-grip"></i> Xem tất cả khóa
                                    </a>
                                    @guest
                                        <a href="{{ route('dang-ky') }}" class="btn-soft">
                                            <i class="fas fa-user-plus"></i> Đăng ký nhanh
                                        </a>
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <a href="#instructors" data-scroll-link="instructors">
                <i class="fas fa-chalkboard-user"></i>
                <span>Giảng viên</span>
            </a>
            @guest
                <a href="#faq" data-scroll-link="faq">
                    <i class="fas fa-circle-question"></i>
                    <span>Hỏi đáp</span>
                </a>
            @endguest
            <a href="#contact" data-scroll-link="contact">
                <i class="fas fa-headset"></i>
                <span>Liên hệ</span>
            </a>
        </nav>

        <div class="header-actions" id="headerActions">
            {{-- Live search với dropdown gợi ý --}}
            <div class="header-search-wrap" id="headerSearchWrap">
                <button type="button" class="header-search-toggle" id="headerSearchToggle" aria-label="Tìm kiếm khóa học" aria-expanded="false" aria-controls="headerSearchPanel">
                    <i class="fas fa-magnifying-glass"></i>
                </button>

                <form method="GET" action="{{ route('home') }}#courses" class="header-search-panel" id="headerSearchPanel" hidden>
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tìm khóa học, giảng viên..." id="headerSearchInput" autocomplete="off">
                    <button type="submit" aria-label="Tìm"><i class="fas fa-arrow-right"></i></button>
                </form>

                <div class="search-suggest" id="headerSearchSuggest" hidden>
                    <div class="search-suggest-status" id="searchSuggestStatus">
                        <i class="fas fa-keyboard"></i>
                        <span>Gõ ít nhất 2 ký tự để tìm khóa học hoặc giảng viên</span>
                    </div>
                    <div class="search-suggest-body" id="searchSuggestBody"></div>
                </div>
            </div>

            @auth
                {{-- Notification bell --}}
                <div class="header-notif-wrap" id="headerNotifWrap">
                    <button type="button" class="header-icon-btn" id="headerNotifToggle" aria-label="Thông báo" aria-expanded="false" data-notif-url="{{ route('api.notifications.recent') }}">
                        <i class="fas fa-bell"></i>
                        <span class="header-icon-badge" id="headerNotifBadge" hidden>0</span>
                    </button>
                    <div class="notif-popover" id="headerNotifPopover" hidden>
                        <div class="notif-popover-head">
                            <strong>Thông báo</strong>
                            <a href="{{ route('thong-bao.index') }}" class="link-mini">Xem tất cả</a>
                        </div>
                        <div class="notif-popover-body" id="headerNotifBody">
                            <div class="notif-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
                        </div>
                    </div>
                </div>

                {{-- User avatar dropdown --}}
                <div class="header-user-wrap" id="headerUserWrap">
                    <button type="button" class="account-chip is-button" id="headerUserToggle" aria-label="Tài khoản {{ $accountName }}" aria-expanded="false">
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
                        <i class="fas fa-chevron-down account-caret"></i>
                    </button>

                    <div class="user-popover" id="headerUserPopover" hidden>
                        <div class="user-popover-head">
                            <span class="account-avatar lg" aria-hidden="true">
                                @if($accountAvatar)
                                    <img src="{{ $accountAvatar }}" alt="">
                                @else
                                    <span>{{ $accountInitial }}</span>
                                @endif
                            </span>
                            <div>
                                <strong>{{ $accountName }}</strong>
                                <small>{{ $homeUser->email }}</small>
                                <span class="user-popover-role">{{ $accountRoleLabel }}</span>
                            </div>
                        </div>
                        <div class="user-popover-list">
                            <a href="{{ $dashboardRoute }}" class="user-popover-item">
                                <i class="fas fa-gauge-high"></i>
                                <span>Bảng điều khiển</span>
                            </a>
                            <a href="{{ route('profile') }}" class="user-popover-item">
                                <i class="fas fa-user-pen"></i>
                                <span>Hồ sơ cá nhân</span>
                            </a>
                            @if($homeUser->vai_tro === 'hoc_vien')
                                <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="user-popover-item">
                                    <i class="fas fa-book-open"></i>
                                    <span>Khóa học của tôi</span>
                                </a>
                            @elseif($homeUser->vai_tro === 'giang_vien')
                                <a href="{{ route('giang-vien.khoa-hoc') }}" class="user-popover-item">
                                    <i class="fas fa-chalkboard-user"></i>
                                    <span>Lớp tôi phụ trách</span>
                                </a>
                            @endif
                            <a href="{{ route('thong-bao.index') }}" class="user-popover-item">
                                <i class="fas fa-bell"></i>
                                <span>Tất cả thông báo</span>
                            </a>
                        </div>
                        <form action="{{ route('dang-xuat') }}" method="POST" class="user-popover-foot">
                            @csrf
                            <button type="submit" class="user-popover-logout">
                                <i class="fas fa-arrow-right-from-bracket"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('dang-nhap') }}" class="btn-soft">
                    <i class="fas fa-right-to-bracket"></i> Đăng nhập
                </a>
                <a href="{{ route('dang-ky') }}" class="btn-main btn-pulse">
                    Đăng ký <i class="fas fa-arrow-right"></i>
                </a>
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
            @guest
                <a href="#about">Về chúng tôi</a>
            @endguest
            <a href="#courses">Khóa học</a>
            <a href="#instructors">Giảng viên</a>
            @guest
                <a href="#faq">Câu hỏi thường gặp</a>
            @endguest
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
