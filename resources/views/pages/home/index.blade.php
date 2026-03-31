@extends('layouts.home')

@section('title', ($settings['site_name'] ?: 'Trang chủ') . ' - Hệ thống đào tạo trực tuyến')

@section('content')
@php
    $levelLabels = [
        'co_ban' => ['label' => 'Cơ bản', 'class' => 'level-basic'],
        'trung_binh' => ['label' => 'Trung bình', 'class' => 'level-mid'],
        'nang_cao' => ['label' => 'Nâng cao', 'class' => 'level-advanced'],
    ];

    $statusLabels = [
        'dang_day' => ['label' => 'Đang giảng dạy', 'class' => 'status-live'],
        'san_sang' => ['label' => 'Sẵn sàng khai giảng', 'class' => 'status-ready'],
        'cho_giang_vien' => ['label' => 'Đang hoàn thiện lịch học', 'class' => 'status-waiting'],
    ];

    $homeUser = auth()->user();
@endphp

{{-- Thông báo hệ thống --}}
@if(filled($settings['general_notification']))
    <div class="notification-banner">
        <div class="container">
            <div class="notification-content">
                <span class="notification-icon">📢</span>
                <span class="notification-text">{!! $settings['general_notification'] !!}</span>
            </div>
        </div>
    </div>
@endif

{{-- Header --}}
<header class="modern-header">
    <div class="container">
        <div class="header-wrapper">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-icon">
                    @if(!empty($settings['site_logo']))
                        <img src="{{ asset($settings['site_logo']) }}" alt="{{ $settings['site_name'] }}">
                    @else
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M16 4L4 12L16 20L28 12L16 4Z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <path d="M4 20L16 28L28 20" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <path d="M4 16L16 24L28 16" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        </svg>
                    @endif
                </div>
                <div class="logo-text">
                    <span class="logo-name">{{ $settings['site_name'] ?: 'EduHub' }}</span>
                    <span class="logo-tagline">Học tập không giới hạn</span>
                </div>
            </a>

            <nav class="main-nav">
                <a href="#hero" class="nav-link">Trang chủ</a>
                <a href="#courses" class="nav-link">Khóa học</a>
                <a href="#instructors" class="nav-link">Giảng viên</a>
                <a href="#contact" class="nav-link">Liên hệ</a>
            </nav>

            <div class="header-actions">
                @if(filled($settings['hotline']))
                    <a href="tel:{{ preg_replace('/\s+/', '', $settings['hotline']) }}" class="hotline-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M22 16.92V19.92C22.0011 20.4985 21.841 21.0656 21.5388 21.5546C21.2367 22.0437 20.8055 22.4344 20.2936 22.68C19.7817 22.9256 19.2102 23.0155 18.6502 22.9406C18.0902 22.8657 17.5658 22.6286 17.14 22.26C14.2614 19.6306 12.0168 16.3827 10.6 12.71C10.2393 11.8924 10.0439 11.0151 10.02 10.12C10.0035 9.55523 10.1272 8.99553 10.3797 8.495C10.6322 7.99447 11.0051 7.57031 11.46 7.26C11.9291 6.93928 12.4833 6.76839 13.05 6.76839C13.6167 6.76839 14.1709 6.93928 14.64 7.26L17.65 9.33C18.1079 9.64232 18.4687 10.0794 18.6892 10.592C18.9098 11.1046 18.9813 11.6726 18.8961 12.2243C18.8109 12.776 18.5725 13.2873 18.2106 13.6992C17.8486 14.1112 17.3785 14.4064 16.85 14.55" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 4H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M14 8H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span>{{ $settings['hotline'] }}</span>
                    </a>
                @endif

                @auth
                    @php
                        $dashboardRoute = match($homeUser->vai_tro) {
                            'admin' => route('admin.dashboard'),
                            'giang_vien' => route('giang-vien.dashboard'),
                            default => route('hoc-vien.dashboard'),
                        };
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="btn-dashboard">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M3 9L12 3L21 9L12 15L3 9Z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <path d="M12 15V21" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7 12L7 17L12 20L17 17L17 12" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('dang-nhap') }}" class="btn-outline">Đăng nhập</a>
                    <a href="{{ route('dang-ky') }}" class="btn-primary">Đăng ký ngay</a>
                @endauth
            </div>

            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<main>
    {{-- Hero Section --}}
    <section class="hero-section" id="hero">
        <div class="hero-bg-animation">
            <div class="bg-shape shape-1"></div>
            <div class="bg-shape shape-2"></div>
            <div class="bg-shape shape-3"></div>
        </div>
        
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span class="badge-pulse"></span>
                        <span>🌟 Nền tảng học tập hàng đầu</span>
                    </div>
                    <h1 class="hero-title">
                        Khám phá tri thức
                        <span class="gradient-text">không giới hạn</span>
                    </h1>
                    <p class="hero-description">
                        {{ $settings['site_name'] ?: 'EduHub' }} - Nơi hội tụ những khóa học chất lượng cao từ đội ngũ giảng viên giàu kinh nghiệm. 
                        Bắt đầu hành trình học tập của bạn ngay hôm nay!
                    </p>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number">{{ number_format($stats['tong_khoa_hoc']) }}</div>
                            <div class="stat-label">Khóa học</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number">{{ number_format($stats['tong_hoc_vien']) }}+</div>
                            <div class="stat-label">Học viên</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number">{{ number_format($stats['tong_giang_vien_noi_bat']) }}</div>
                            <div class="stat-label">Giảng viên</div>
                        </div>
                    </div>
                    
                    <div class="hero-actions">
                        <a href="#courses" class="btn-primary btn-large">
                            Khám phá khóa học
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </a>
                        @guest
                            <a href="{{ route('dang-ky') }}" class="btn-outline btn-large">
                                Đăng ký miễn phí
                            </a>
                        @endguest
                    </div>
                </div>
                
                <div class="hero-visual">
                    @if($featuredCourse)
                        <div class="featured-card">
                            <div class="featured-badge">Khóa học nổi bật</div>
                            <div class="featured-icon">🎓</div>
                            <h3 class="featured-title">{{ $featuredCourse->ten_khoa_hoc }}</h3>
                            <p class="featured-desc">{{ \Illuminate\Support\Str::limit($featuredCourse->mo_ta_ngan ?: 'Khóa học chất lượng cao với lộ trình bài bản', 80) }}</p>
                            <div class="featured-meta">
                                <span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2V4M12 20V22M4 12H2M6.5 6.5L5 5M17.5 6.5L19 5M6.5 17.5L5 19M17.5 17.5L19 19" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    {{ $levelLabels[$featuredCourse->cap_do]['label'] ?? 'Tổng hợp' }}
                                </span>
                                <span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    {{ $featuredCourse->module_hocs_count ?? 0 }} module
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="hero-scroll-indicator">
            <span>Khám phá thêm</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
    </section>

    {{-- Banner Carousel --}}
    @if($banners->isNotEmpty())
        <section class="banner-section">
            <div class="container">
                <div class="banner-carousel">
                    <div class="swiper banner-swiper">
                        <div class="swiper-wrapper">
                            @foreach($banners as $banner)
                                <div class="swiper-slide">
                                    <div class="banner-card">
                                        <img src="{{ asset($banner->duong_dan_anh) }}" alt="{{ $banner->tieu_de }}">
                                        <div class="banner-overlay">
                                            <div class="banner-content">
                                                <span class="banner-label">Tin tức nổi bật</span>
                                                <h3>{{ $banner->tieu_de }}</h3>
                                                @if($banner->mo_ta)
                                                    <p>{{ \Illuminate\Support\Str::limit($banner->mo_ta, 100) }}</p>
                                                @endif
                                                @if($banner->link)
                                                    <a href="{{ $banner->link }}" class="btn-banner" target="_blank">
                                                        Tìm hiểu ngay
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Categories Section --}}
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <div class="section-label">Danh mục khóa học</div>
                <h2 class="section-title">Khám phá <span class="gradient-text">lĩnh vực</span> bạn quan tâm</h2>
                <p class="section-desc">Hơn {{ number_format($categories->sum('public_course_count')) }} khóa học được phân loại theo từng lĩnh vực chuyên môn</p>
            </div>
            
            <div class="categories-grid">
                @forelse($categories->take(6) as $item)
                    <a href="{{ route('home', ['category' => $item->id]) }}#courses" class="category-card {{ (string) $filters['category'] === (string) $item->id ? 'active' : '' }}">
                        <div class="category-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M4 4H20V20H4V4Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 8H16" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 12H14" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 16H12" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <h3>{{ $item->ten_nhom_nganh }}</h3>
                        <div class="category-count">{{ $item->public_course_count }} khóa học</div>
                    </a>
                @empty
                    <div class="empty-state">Chưa có danh mục khóa học</div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Courses Section --}}
    <section class="courses-section" id="courses">
        <div class="container">
            <div class="section-header">
                <div class="section-label">Khóa học</div>
                <h2 class="section-title">Chương trình đào tạo <span class="gradient-text">chất lượng cao</span></h2>
                <p class="section-desc">Được thiết kế bởi các chuyên gia hàng đầu, cập nhật xu hướng mới nhất</p>
            </div>
            
            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('home') }}" class="filter-bar">
                <div class="filter-group">
                    <div class="filter-input">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm kiếm khóa học...">
                    </div>
                    <select name="level" class="filter-select">
                        <option value="">Tất cả cấp độ</option>
                        <option value="co_ban" @selected($filters['level'] === 'co_ban')>Cơ bản</option>
                        <option value="trung_binh" @selected($filters['level'] === 'trung_binh')>Trung bình</option>
                        <option value="nang_cao" @selected($filters['level'] === 'nang_cao')>Nâng cao</option>
                    </select>
                    <select name="category" class="filter-select">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $item)
                            <option value="{{ $item->id }}" @selected((string) $filters['category'] === (string) $item->id)>{{ $item->ten_nhom_nganh }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-filter">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M3 6H21M6 12H18M10 18H14" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        Lọc
                    </button>
                    <a href="{{ route('home') }}#courses" class="btn-reset">Đặt lại</a>
                </div>
            </form>
            
            {{-- Courses Grid --}}
            <div class="courses-grid">
                @forelse($courses as $course)
                    @php
                        $levelInfo = $levelLabels[$course->cap_do] ?? ['label' => 'Tổng hợp', 'class' => 'level-basic'];
                        $status = $statusLabels[$course->trang_thai_van_hanh] ?? ['label' => 'Đang cập nhật', 'class' => 'status-waiting'];
                    @endphp
                    <div class="course-card">
                        <div class="course-card-inner">
                            <div class="course-image">
                                @if($course->hinh_anh)
                                    <img src="{{ asset($course->hinh_anh) }}" alt="{{ $course->ten_khoa_hoc }}">
                                @else
                                    <div class="course-image-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 6V12L15 15" stroke="currentColor" stroke-width="1.5"/>
                                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="course-badges">
                                    <span class="badge-level {{ $levelInfo['class'] }}">{{ $levelInfo['label'] }}</span>
                                    <span class="badge-status {{ $status['class'] }}">{{ $status['label'] }}</span>
                                </div>
                            </div>
                            
                            <div class="course-info">
                                <div class="course-category">{{ optional($course->nhomNganh)->ten_nhom_nganh ?: 'Đa lĩnh vực' }}</div>
                                <h3>{{ $course->ten_khoa_hoc }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($course->mo_ta_ngan ?: 'Khóa học chất lượng cao với lộ trình học tập bài bản', 100) }}</p>
                                
                                <div class="course-stats">
                                    <div class="course-stat">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5"/>
                                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span>{{ $course->module_hocs_count ?? 0 }} module</span>
                                    </div>
                                    <div class="course-stat">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 2V4M12 20V22M4 12H2M6.5 6.5L5 5M17.5 6.5L19 5M6.5 17.5L5 19M17.5 17.5L19 19" stroke="currentColor" stroke-width="1.5"/>
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span>{{ $course->ngay_khai_giang ? $course->ngay_khai_giang->format('d/m/Y') : 'Sắp khai giảng' }}</span>
                                    </div>
                                </div>
                                
                                <div class="course-actions">
                                    @guest
                                        <a href="{{ route('dang-ky') }}" class="btn-course">Đăng ký ngay</a>
                                        <a href="{{ route('dang-nhap') }}" class="btn-course-outline">Đăng nhập</a>
                                    @else
                                        <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn-course">Tham gia ngay</a>
                                        <a href="{{ route('hoc-vien.dashboard') }}" class="btn-course-outline">Dashboard</a>
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-large">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none">
                            <path d="M12 8V12M12 16H12.01M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        <h3>Chưa có khóa học phù hợp</h3>
                        <p>Hãy thử điều chỉnh bộ lọc hoặc quay lại sau nhé!</p>
                        <a href="{{ route('home') }}#courses" class="btn-primary">Xem tất cả khóa học</a>
                    </div>
                @endforelse
            </div>
            
            @if($courses->hasPages())
                <div class="pagination-wrapper">
                    {{ $courses->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="cta-section">
        <div class="container">
            <div class="cta-wrapper">
                <div class="cta-content">
                    <div class="cta-label">Bắt đầu ngay hôm nay</div>
                    <h2>Sẵn sàng để <span class="gradient-text">nâng tầm tri thức</span>?</h2>
                    <p>Đăng ký ngay để nhận ưu đãi đặc biệt và trải nghiệm nền tảng học tập hiện đại</p>
                    @guest
                        <div class="cta-buttons">
                            <a href="{{ route('dang-ky') }}" class="btn-primary btn-large">Đăng ký miễn phí</a>
                            <a href="{{ route('dang-nhap') }}" class="btn-outline btn-large">Đăng nhập</a>
                        </div>
                    @else
                        <div class="cta-buttons">
                            <a href="{{ route('hoc-vien.dashboard') }}" class="btn-primary btn-large">Vào Dashboard</a>
                            <a href="#courses" class="btn-outline btn-large">Khám phá khóa học</a>
                        </div>
                    @endguest
                </div>
                <div class="cta-decoration">
                    <div class="decoration-circle"></div>
                    <div class="decoration-circle-2"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Instructors Section --}}
    <section class="instructors-section" id="instructors">
        <div class="container">
            <div class="section-header">
                <div class="section-label">Đội ngũ giảng viên</div>
                <h2 class="section-title">Gặp gỡ các <span class="gradient-text">chuyên gia</span> hàng đầu</h2>
                <p class="section-desc">Những người dẫn dắt bạn trên hành trình chinh phục tri thức</p>
            </div>
            
            <div class="instructors-grid">
                @forelse($featuredInstructors as $giangVien)
                    <div class="instructor-card">
                        <div class="instructor-avatar">
                            @if(optional($giangVien->nguoiDung)->anh_dai_dien)
                                <img src="{{ asset($giangVien->nguoiDung->anh_dai_dien) }}" alt="{{ $giangVien->nguoiDung->ho_ten }}">
                            @elseif($giangVien->avatar_url)
                                <img src="{{ asset($giangVien->avatar_url) }}" alt="{{ $giangVien->nguoiDung->ho_ten }}">
                            @else
                                <div class="avatar-placeholder">
                                    {{ strtoupper(mb_substr($giangVien->nguoiDung->ho_ten ?? 'G', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="instructor-info">
                            <h3>{{ $giangVien->nguoiDung->ho_ten ?? 'Giảng viên' }}</h3>
                            <div class="instructor-title">{{ $giangVien->chuyen_nganh ?: 'Chuyên gia đào tạo' }}</div>
                            <p>{{ \Illuminate\Support\Str::limit($giangVien->mo_ta_ngan ?: 'Giảng viên giàu kinh nghiệm, tận tâm với nghề', 80) }}</p>
                            <div class="instructor-stats">
                                <span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 6V12L15 15" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    {{ number_format((int) $giangVien->so_gio_day) }} giờ
                                </span>
                                <span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2V4M12 20V22M4 12H2M6.5 6.5L5 5M17.5 6.5L19 5M6.5 17.5L5 19M17.5 17.5L19 19" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    {{ $giangVien->hoc_vi ?: 'Thạc sĩ' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Đang cập nhật danh sách giảng viên</div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Contact Section --}}
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="section-label">Liên hệ</div>
                    <h2>Chúng tôi luôn sẵn sàng <span class="gradient-text">hỗ trợ bạn</span></h2>
                    <p>Có bất kỳ câu hỏi nào? Đội ngũ hỗ trợ của chúng tôi sẽ giải đáp mọi thắc mắc của bạn.</p>
                    
                    <div class="contact-details">
                        @if(filled($settings['hotline']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M22 16.92V19.92C22.0011 20.4985 21.841 21.0656 21.5388 21.5546C21.2367 22.0437 20.8055 22.4344 20.2936 22.68C19.7817 22.9256 19.2102 23.0155 18.6502 22.9406C18.0902 22.8657 17.5658 22.6286 17.14 22.26C14.2614 19.6306 12.0168 16.3827 10.6 12.71C10.2393 11.8924 10.0439 11.0151 10.02 10.12C10.0035 9.55523 10.1272 8.99553 10.3797 8.495C10.6322 7.99447 11.0051 7.57031 11.46 7.26C11.9291 6.93928 12.4833 6.76839 13.05 6.76839C13.6167 6.76839 14.1709 6.93928 14.64 7.26L17.65 9.33C18.1079 9.64232 18.4687 10.0794 18.6892 10.592C18.9098 11.1046 18.9813 11.6726 18.8961 12.2243C18.8109 12.776 18.5725 13.2873 18.2106 13.6992C17.8486 14.1112 17.3785 14.4064 16.85 14.55" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="contact-label">Hotline hỗ trợ</div>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $settings['hotline']) }}" class="contact-value">{{ $settings['hotline'] }}</a>
                                </div>
                            </div>
                        @endif
                        
                        @if(filled($settings['email']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="contact-label">Email liên hệ</div>
                                    <a href="mailto:{{ $settings['email'] }}" class="contact-value">{{ $settings['email'] }}</a>
                                </div>
                            </div>
                        @endif
                        
                        @if(filled($settings['address']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2C8.13 2 5 5.13 5 9C5 13.17 9.27 17.74 11.18 19.44C11.63 19.82 12.37 19.82 12.82 19.44C14.73 17.74 19 13.17 19 9C19 5.13 15.87 2 12 2Z" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="contact-label">Địa chỉ</div>
                                    <div class="contact-value">{!! $settings['address'] !!}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="social-links">
                        @if(filled($settings['facebook']))
                            <a href="{{ $settings['facebook'] }}" class="social-link" target="_blank" rel="noopener">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 2H15C13.6739 2 12.4021 2.52678 11.4645 3.46447C10.5268 4.40215 10 5.67392 10 7V10H7V14H10V22H14V14H17L18 10H14V7C14 6.73478 14.1054 6.48043 14.2929 6.29289C14.4804 6.10536 14.7348 6 15 6H18V2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </a>
                        @endif
                        @if(filled($settings['zalo']))
                            <a href="{{ $settings['zalo'] }}" class="social-link" target="_blank" rel="noopener">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 12H16M12 8V16" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="contact-map">
                    <div class="map-placeholder">
                        <svg width="100%" height="100%" viewBox="0 0 400 300" fill="none">
                            <rect width="400" height="300" fill="#EFF6FF" rx="24"/>
                            <circle cx="200" cy="150" r="40" fill="#0D6EFD" fill-opacity="0.2"/>
                            <circle cx="200" cy="150" r="20" fill="#0D6EFD"/>
                            <path d="M200 130V170M180 150H220" stroke="white" stroke-width="2"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- Footer --}}
<footer class="modern-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo">
                    <div class="logo-icon">
                        @if(!empty($settings['site_logo']))
                            <img src="{{ asset($settings['site_logo']) }}" alt="{{ $settings['site_name'] }}">
                        @else
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                                <path d="M16 4L4 12L16 20L28 12L16 4Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M4 20L16 28L28 20" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M4 16L16 24L28 16" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        @endif
                    </div>
                    <div class="logo-text">
                        <span class="logo-name">{{ $settings['site_name'] ?: 'EduHub' }}</span>
                        <span class="logo-tagline">Học tập không giới hạn</span>
                    </div>
                </div>
                <p class="footer-desc">Nền tảng đào tạo trực tuyến hàng đầu, mang đến cơ hội học tập chất lượng cao cho mọi người.</p>
            </div>
            
            <div class="footer-links">
                <h4>Liên kết nhanh</h4>
                <ul>
                    <li><a href="#hero">Trang chủ</a></li>
                    <li><a href="#courses">Khóa học</a></li>
                    <li><a href="#instructors">Giảng viên</a></li>
                    <li><a href="#contact">Liên hệ</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>Hỗ trợ</h4>
                <ul>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                    <li><a href="#">Điều khoản sử dụng</a></li>
                    <li><a href="#">Hướng dẫn thanh toán</a></li>
                </ul>
            </div>
            
            <div class="footer-newsletter">
                <h4>Nhận thông tin mới nhất</h4>
                <p>Đăng ký để nhận cập nhật về khóa học mới và ưu đãi đặc biệt</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Email của bạn">
                    <button type="submit">Đăng ký</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} {{ $settings['site_name'] ?: 'EduHub' }}. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</footer>

@endsection

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary: #0D6EFD;
        --primary-dark: #0A58CA;
        --primary-light: #60A5FA;
        --secondary: #8B5CF6;
        --accent: #F59E0B;
        --dark: #0F172A;
        --dark-light: #1E293B;
        --gray: #64748B;
        --gray-light: #F1F5F9;
        --white: #FFFFFF;
        --success: #10B981;
        --danger: #EF4444;
        --warning: #F59E0B;
        --gradient-primary: linear-gradient(135deg, #0D6EFD 0%, #8B5CF6 100%);
        --gradient-secondary: linear-gradient(135deg, #F59E0B 0%, #EF4444 100%);
        --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 24px;
        --radius-2xl: 32px;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--gray-light);
        color: var(--dark);
        overflow-x: hidden;
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
    }

    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 16px;
        }
    }

    /* Notification Banner */
    .notification-banner {
        background: var(--gradient-primary);
        color: var(--white);
        padding: 12px 0;
        position: relative;
        z-index: 100;
    }

    .notification-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        font-size: 14px;
    }

    .notification-icon {
        font-size: 18px;
    }

    /* Modern Header */
    .modern-header {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .header-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 80px;
        gap: 32px;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .logo-icon {
        width: 44px;
        height: 44px;
        background: var(--gradient-primary);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
    }

    .logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: var(--radius-md);
    }

    .logo-text {
        display: flex;
        flex-direction: column;
    }

    .logo-name {
        font-size: 20px;
        font-weight: 800;
        color: var(--dark);
        letter-spacing: -0.02em;
    }

    .logo-tagline {
        font-size: 11px;
        color: var(--gray);
        letter-spacing: 0.5px;
    }

    .main-nav {
        display: flex;
        gap: 28px;
    }

    .nav-link {
        text-decoration: none;
        color: var(--dark);
        font-weight: 500;
        transition: color 0.3s ease;
        position: relative;
    }

    .nav-link:hover {
        color: var(--primary);
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--gradient-primary);
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .hotline-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: var(--gray-light);
        border-radius: var(--radius-lg);
        text-decoration: none;
        color: var(--dark);
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .hotline-btn:hover {
        background: var(--primary);
        color: var(--white);
    }

    .btn-primary, .btn-outline, .btn-dashboard {
        padding: 10px 20px;
        border-radius: var(--radius-lg);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: var(--gradient-primary);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-outline {
        border: 1px solid var(--gray);
        color: var(--dark);
        background: transparent;
    }

    .btn-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-dashboard {
        background: var(--gray-light);
        color: var(--dark);
    }

    .btn-dashboard:hover {
        background: var(--primary);
        color: var(--white);
    }

    .btn-large {
        padding: 14px 28px;
        font-size: 16px;
    }

    .mobile-menu-btn {
        display: none;
        flex-direction: column;
        gap: 6px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
    }

    .mobile-menu-btn span {
        width: 24px;
        height: 2px;
        background: var(--dark);
        transition: all 0.3s ease;
    }

    @media (max-width: 991px) {
        .main-nav, .header-actions {
            display: none;
        }
        .mobile-menu-btn {
            display: flex;
        }
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        overflow: hidden;
        padding: 80px 0;
    }

    .hero-bg-animation {
        position: absolute;
        inset: 0;
        z-index: 0;
    }

    .bg-shape {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.6;
    }

    .shape-1 {
        width: 500px;
        height: 500px;
        background: var(--primary-light);
        top: -100px;
        left: -100px;
        animation: float 20s ease-in-out infinite;
    }

    .shape-2 {
        width: 400px;
        height: 400px;
        background: var(--secondary);
        bottom: -50px;
        right: -50px;
        animation: float 15s ease-in-out infinite reverse;
    }

    .shape-3 {
        width: 300px;
        height: 300px;
        background: var(--accent);
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: pulse 10s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.3; transform: translate(-50%, -50%) scale(1); }
        50% { opacity: 0.6; transform: translate(-50%, -50%) scale(1.1); }
    }

    .hero-grid {
        display: grid;
        grid-template-columns: 1fr 0.8fr;
        gap: 60px;
        position: relative;
        z-index: 1;
    }

    .hero-content {
        animation: fadeInUp 0.8s ease;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 8px 16px;
        background: rgba(13,110,253,0.1);
        border-radius: var(--radius-lg);
        color: var(--primary);
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 24px;
    }

    .badge-pulse {
        width: 8px;
        height: 8px;
        background: var(--primary);
        border-radius: 50%;
        animation: pulse 1.5s ease-in-out infinite;
    }

    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4.5rem);
        line-height: 1.1;
        margin-bottom: 24px;
    }

    .gradient-text {
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .hero-description {
        font-size: 18px;
        color: var(--gray);
        line-height: 1.6;
        margin-bottom: 32px;
        max-width: 500px;
    }

    .hero-stats {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: var(--dark);
    }

    .stat-label {
        font-size: 14px;
        color: var(--gray);
    }

    .stat-divider {
        width: 1px;
        height: 40px;
        background: var(--gray-light);
    }

    .hero-actions {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .hero-visual {
        animation: fadeInRight 0.8s ease;
    }

    .featured-card {
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(10px);
        border-radius: var(--radius-2xl);
        padding: 32px;
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(255,255,255,0.5);
        position: relative;
        overflow: hidden;
    }

    .featured-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
    }

    .featured-badge {
        display: inline-block;
        padding: 4px 12px;
        background: var(--gradient-primary);
        color: var(--white);
        border-radius: var(--radius-lg);
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .featured-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .featured-title {
        font-size: 24px;
        margin-bottom: 12px;
    }

    .featured-desc {
        color: var(--gray);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .featured-meta {
        display: flex;
        gap: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .featured-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: var(--gray);
    }

    .hero-scroll-indicator {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: var(--gray);
        font-size: 14px;
        animation: bounce 2s ease-in-out infinite;
        cursor: pointer;
        z-index: 1;
    }

    @keyframes bounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50% { transform: translateX(-50%) translateY(-10px); }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @media (max-width: 991px) {
        .hero-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        .hero-section {
            padding: 60px 0;
        }
    }

    /* Banner Section */
    .banner-section {
        padding: 60px 0;
    }

    .banner-carousel {
        border-radius: var(--radius-2xl);
        overflow: hidden;
    }

    .banner-card {
        position: relative;
        border-radius: var(--radius-2xl);
        overflow: hidden;
        cursor: pointer;
    }

    .banner-card img {
        width: 100%;
        height: 450px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .banner-card:hover img {
        transform: scale(1.05);
    }

    .banner-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%);
        display: flex;
        align-items: flex-end;
        padding: 40px;
    }

    .banner-content {
        color: var(--white);
        max-width: 500px;
    }

    .banner-label {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(255,255,255,0.2);
        border-radius: var(--radius-lg);
        font-size: 12px;
        margin-bottom: 16px;
    }

    .banner-content h3 {
        font-size: 28px;
        margin-bottom: 12px;
    }

    .banner-content p {
        opacity: 0.9;
        margin-bottom: 20px;
    }

    .btn-banner {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--white);
        color: var(--dark);
        border-radius: var(--radius-lg);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-banner:hover {
        transform: translateX(5px);
    }

    /* Section Styles */
    .section-header {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 48px;
    }

    .section-label {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(13,110,253,0.1);
        color: var(--primary);
        border-radius: var(--radius-lg);
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .section-title {
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        margin-bottom: 16px;
    }

    .section-desc {
        color: var(--gray);
        font-size: 18px;
        line-height: 1.6;
    }

    /* Categories Section */
    .categories-section {
        padding: 60px 0;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 24px;
    }

    .category-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        padding: 32px 20px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: var(--shadow-sm);
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-light);
    }

    .category-card.active {
        background: var(--gradient-primary);
        color: var(--white);
    }

    .category-card.active .category-icon {
        color: var(--white);
    }

    .category-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(13,110,253,0.1);
        border-radius: var(--radius-lg);
        color: var(--primary);
    }

    .category-card h3 {
        font-size: 18px;
        margin-bottom: 8px;
    }

    .category-count {
        font-size: 14px;
        color: var(--gray);
    }

    .category-card.active .category-count {
        color: rgba(255,255,255,0.8);
    }

    /* Courses Section */
    .courses-section {
        padding: 60px 0;
        background: var(--white);
    }

    .filter-bar {
        margin-bottom: 40px;
    }

    .filter-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-input {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        background: var(--gray-light);
        border-radius: var(--radius-lg);
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .filter-input:focus-within {
        border-color: var(--primary);
        background: var(--white);
    }

    .filter-input input {
        flex: 1;
        border: none;
        background: none;
        outline: none;
        font-size: 14px;
    }

    .filter-select {
        padding: 12px 20px;
        background: var(--gray-light);
        border: 1px solid transparent;
        border-radius: var(--radius-lg);
        outline: none;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        border-color: var(--primary);
        background: var(--white);
    }

    .btn-filter, .btn-reset {
        padding: 12px 24px;
        border-radius: var(--radius-lg);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        font-size: 14px;
    }

    .btn-filter {
        background: var(--gradient-primary);
        color: var(--white);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-reset {
        background: var(--gray-light);
        color: var(--dark);
        text-decoration: none;
    }

    .btn-reset:hover {
        background: var(--gray);
        color: var(--white);
    }

    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
    }

    .course-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
    }

    .course-card-inner {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .course-image {
        position: relative;
        height: 220px;
        overflow: hidden;
    }

    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .course-card:hover .course-image img {
        transform: scale(1.05);
    }

    .course-image-placeholder {
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
    }

    .course-badges {
        position: absolute;
        top: 16px;
        left: 16px;
        display: flex;
        gap: 8px;
    }

    .badge-level, .badge-status {
        padding: 4px 10px;
        border-radius: var(--radius-lg);
        font-size: 11px;
        font-weight: 600;
    }

    .level-basic { background: var(--success); color: var(--white); }
    .level-mid { background: var(--warning); color: var(--white); }
    .level-advanced { background: var(--danger); color: var(--white); }
    .status-live { background: var(--success); color: var(--white); }
    .status-ready { background: var(--primary); color: var(--white); }
    .status-waiting { background: var(--gray); color: var(--white); }

    .course-info {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .course-category {
        font-size: 12px;
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 8px;
    }

    .course-info h3 {
        font-size: 20px;
        margin-bottom: 12px;
        line-height: 1.3;
    }

    .course-info p {
        color: var(--gray);
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .course-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .course-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--gray);
    }

    .course-actions {
        display: flex;
        gap: 12px;
        margin-top: auto;
    }

    .btn-course, .btn-course-outline {
        flex: 1;
        padding: 10px;
        border-radius: var(--radius-lg);
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-course {
        background: var(--gradient-primary);
        color: var(--white);
    }

    .btn-course:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-course-outline {
        border: 1px solid var(--gray);
        color: var(--dark);
    }

    .btn-course-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    /* CTA Section */
    .cta-section {
        padding: 80px 0;
    }

    .cta-wrapper {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        border-radius: var(--radius-2xl);
        padding: 60px;
        position: relative;
        overflow: hidden;
    }

    .cta-content {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .cta-label {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(255,255,255,0.1);
        color: var(--white);
        border-radius: var(--radius-lg);
        font-size: 12px;
        margin-bottom: 20px;
    }

    .cta-content h2 {
        color: var(--white);
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        margin-bottom: 20px;
    }

    .cta-content p {
        color: rgba(255,255,255,0.8);
        margin-bottom: 32px;
    }

    .cta-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .cta-decoration {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .decoration-circle {
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(13,110,253,0.1);
        border-radius: 50%;
        top: -150px;
        right: -150px;
    }

    .decoration-circle-2 {
        position: absolute;
        width: 200px;
        height: 200px;
        background: rgba(139,92,246,0.1);
        border-radius: 50%;
        bottom: -100px;
        left: -100px;
    }

    /* Instructors Section */
    .instructors-section {
        padding: 60px 0;
        background: var(--white);
    }

    .instructors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }

    .instructor-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        padding: 24px;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: var(--shadow-sm);
    }

    .instructor-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .instructor-avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto 20px;
        border-radius: 50%;
        overflow: hidden;
        background: var(--gradient-primary);
    }

    .instructor-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
        color: var(--white);
    }

    .instructor-info h3 {
        font-size: 20px;
        margin-bottom: 4px;
    }

    .instructor-title {
        font-size: 14px;
        color: var(--primary);
        margin-bottom: 12px;
    }

    .instructor-info p {
        font-size: 14px;
        color: var(--gray);
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .instructor-stats {
        display: flex;
        justify-content: center;
        gap: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .instructor-stats span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--gray);
    }

    /* Contact Section */
    .contact-section {
        padding: 60px 0;
        background: var(--gray-light);
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .contact-details {
        margin-top: 32px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .contact-item {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .contact-icon {
        width: 48px;
        height: 48px;
        background: rgba(13,110,253,0.1);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .contact-label {
        font-size: 12px;
        color: var(--gray);
        margin-bottom: 4px;
    }

    .contact-value {
        font-size: 18px;
        font-weight: 600;
        color: var(--dark);
        text-decoration: none;
    }

    .contact-value:hover {
        color: var(--primary);
    }

    .social-links {
        display: flex;
        gap: 12px;
        margin-top: 32px;
    }

    .social-link {
        width: 40px;
        height: 40px;
        background: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dark);
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .social-link:hover {
        background: var(--gradient-primary);
        color: var(--white);
        transform: translateY(-3px);
    }

    .contact-map {
        background: var(--white);
        border-radius: var(--radius-2xl);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .map-placeholder {
        width: 100%;
        height: 300px;
    }

    /* Footer */
    .modern-footer {
        background: var(--dark);
        color: rgba(255,255,255,0.7);
        padding: 60px 0 30px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-brand .logo {
        margin-bottom: 20px;
    }

    .footer-brand .logo .logo-icon {
        background: rgba(255,255,255,0.1);
    }

    .footer-brand .logo .logo-name {
        color: var(--white);
    }

    .footer-desc {
        line-height: 1.6;
        font-size: 14px;
    }

    .footer-links h4 {
        color: var(--white);
        margin-bottom: 20px;
        font-size: 16px;
    }

    .footer-links ul {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: color 0.3s ease;
        font-size: 14px;
    }

    .footer-links a:hover {
        color: var(--white);
    }

    .footer-newsletter h4 {
        color: var(--white);
        margin-bottom: 16px;
        font-size: 16px;
    }

    .footer-newsletter p {
        font-size: 14px;
        margin-bottom: 20px;
    }

    .newsletter-form {
        display: flex;
        gap: 12px;
    }

    .newsletter-form input {
        flex: 1;
        padding: 12px 16px;
        border: none;
        border-radius: var(--radius-lg);
        background: rgba(255,255,255,0.1);
        color: var(--white);
        outline: none;
    }

    .newsletter-form input::placeholder {
        color: rgba(255,255,255,0.5);
    }

    .newsletter-form button {
        padding: 12px 24px;
        background: var(--gradient-primary);
        border: none;
        border-radius: var(--radius-lg);
        color: var(--white);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .newsletter-form button:hover {
        transform: translateY(-2px);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        border-top: 1px solid rgba(255,255,255,0.1);
        font-size: 14px;
    }

    @media (max-width: 991px) {
        .footer-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        .courses-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr;
        }
        .filter-group {
            flex-direction: column;
        }
        .filter-input, .filter-select, .btn-filter, .btn-reset {
            width: 100%;
        }
        .cta-wrapper {
            padding: 40px 24px;
        }
    }

    .empty-state {
        text-align: center;
        padding: 60px;
        color: var(--gray);
    }

    .empty-state-large {
        text-align: center;
        padding: 80px 20px;
        background: var(--gray-light);
        border-radius: var(--radius-2xl);
    }

    .empty-state-large svg {
        color: var(--gray);
        margin-bottom: 24px;
    }

    .empty-state-large h3 {
        margin-bottom: 12px;
    }

    .empty-state-large p {
        color: var(--gray);
        margin-bottom: 24px;
    }

    .pagination-wrapper {
        margin-top: 48px;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper .pagination {
        gap: 8px;
    }

    .pagination-wrapper .page-link {
        border-radius: var(--radius-lg);
        border: none;
        color: var(--dark);
        padding: 10px 16px;
        background: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .pagination-wrapper .active .page-link {
        background: var(--gradient-primary);
        color: var(--white);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Banner Swiper
        const bannerSwiper = new Swiper('.banner-swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        // Mobile Menu
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mainNav = document.querySelector('.main-nav');
        const headerActions = document.querySelector('.header-actions');
        
        if (mobileBtn) {
            mobileBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                if (mainNav) mainNav.classList.toggle('show');
                if (headerActions) headerActions.classList.toggle('show');
            });
        }

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll animation observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.category-card, .course-card, .instructor-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    });
</script>
@endpush