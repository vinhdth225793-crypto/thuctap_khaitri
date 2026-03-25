@extends('layouts.home')

@section('title', ($settings['site_name'] ?: 'Trang chủ') . ' - Không gian học tập')

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

@if(filled($settings['general_notification']))
    <div class="announcement-bar">
        <div class="container announcement-inner">
            <div class="announcement-label">Thông báo từ hệ thống</div>
            <div class="announcement-content">{!! $settings['general_notification'] !!}</div>
        </div>
    </div>
@endif

<header class="site-header">
    <div class="container header-shell">
        <a href="{{ route('home') }}" class="brand-mark">
            <div class="brand-logo">
                @if(!empty($settings['site_logo']))
                    <img src="{{ asset($settings['site_logo']) }}" alt="{{ $settings['site_name'] ?: 'Logo hệ thống' }}">
                @else
                    <span>K</span>
                @endif
            </div>
            <div>
                <div class="brand-kicker">Hệ thống đào tạo</div>
                <div class="brand-name">{{ $settings['site_name'] ?: 'Khai Tri Education' }}</div>
            </div>
        </a>

        <nav class="site-nav">
            <a href="#hero">Trang chủ</a>
            <a href="#courses">Khóa học</a>
            <a href="#instructors">Giảng viên</a>
            <a href="#contact">Liên hệ</a>
        </nav>

        <div class="header-actions">
            @if(filled($settings['hotline']))
                <a href="tel:{{ preg_replace('/\s+/', '', $settings['hotline']) }}" class="contact-pill">
                    <i class="fas fa-phone-alt"></i>
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
                <a href="{{ $dashboardRoute }}" class="btn-primary-surface">Vào dashboard</a>
            @else
                <a href="{{ route('dang-nhap') }}" class="btn-ghost-surface">Đăng nhập</a>
                <a href="{{ route('dang-ky') }}" class="btn-primary-surface">Tạo tài khoản</a>
            @endauth
        </div>
    </div>
</header>

<main class="home-landing">
    <section class="hero-edu" id="hero">
        <div class="hero-backdrop hero-backdrop-one"></div>
        <div class="hero-backdrop hero-backdrop-two"></div>
        <div class="container hero-grid">
            <div class="hero-copy" data-aos="fade-right">
                <div class="hero-badge">Learning hub cho người học mới</div>
                <h1>Khám phá các khóa học đang mở ngay từ trang chủ.</h1>
                <p class="hero-lead">
                    {{ $settings['site_name'] ?: 'Hệ thống Khai Trí' }} hiển thị công khai các khóa học đang hoạt động,
                    thông tin liên hệ từ admin và đội ngũ giảng viên nổi bật để người dùng chưa có tài khoản vẫn có thể tìm hiểu trước khi đăng ký.
                </p>

                <div class="hero-actions">
                    <a href="#courses" class="btn-primary-surface">Xem khóa học</a>
                    @guest
                        <a href="{{ route('dang-ky') }}" class="btn-outline-surface">Đăng ký học viên</a>
                    @else
                        <a href="#contact" class="btn-outline-surface">Xem thông tin liên hệ</a>
                    @endguest
                </div>

                <div class="hero-metrics">
                    <article class="metric-card" data-aos="fade-up" data-aos-delay="50">
                        <span class="metric-label">Khóa học công khai</span>
                        <strong>{{ number_format($stats['tong_khoa_hoc']) }}</strong>
                        <small>{{ number_format($stats['sap_khai_giang']) }} khóa sắp khai giảng</small>
                    </article>
                    <article class="metric-card" data-aos="fade-up" data-aos-delay="120">
                        <span class="metric-label">Học viên đang học</span>
                        <strong>{{ number_format($stats['tong_hoc_vien']) }}</strong>
                        <small>Dữ liệu thật từ hệ thống đào tạo</small>
                    </article>
                    <article class="metric-card" data-aos="fade-up" data-aos-delay="190">
                        <span class="metric-label">Module chuyên môn</span>
                        <strong>{{ number_format($stats['tong_module']) }}</strong>
                        <small>{{ number_format($stats['tong_giang_vien_noi_bat']) }} giảng viên nổi bật</small>
                    </article>
                </div>
            </div>

            <div class="hero-side" data-aos="fade-left">
                @if($featuredCourse)
                    <article class="spotlight-card">
                        <div class="spotlight-head">
                            <span class="eyebrow">Khóa học nổi bật</span>
                            @php $status = $statusLabels[$featuredCourse->trang_thai_van_hanh] ?? ['label' => 'Đang cập nhật', 'class' => 'status-waiting']; @endphp
                            <span class="status-pill {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </div>

                        <h2>{{ $featuredCourse->ten_khoa_hoc }}</h2>
                        <p>
                            {{ $featuredCourse->mo_ta_ngan ?: 'Khóa học đang hoạt động và sẵn sàng để người học tìm hiểu trước khi tạo tài khoản tham gia.' }}
                        </p>

                        <div class="spotlight-meta">
                            <div>
                                <span>Mã khóa</span>
                                <strong>{{ $featuredCourse->ma_khoa_hoc }}</strong>
                            </div>
                            <div>
                                <span>Nhóm ngành</span>
                                <strong>{{ optional($featuredCourse->nhomNganh)->ten_nhom_nganh ?: 'Đa lĩnh vực' }}</strong>
                            </div>
                            <div>
                                <span>Module</span>
                                <strong>{{ number_format($featuredCourse->module_hocs_count ?? 0) }}</strong>
                            </div>
                            <div>
                                <span>Học viên</span>
                                <strong>{{ number_format($featuredCourse->hoc_vien_dang_hoc_count ?? 0) }}</strong>
                            </div>
                        </div>

                        <div class="spotlight-footer">
                            @php $levelInfo = $levelLabels[$featuredCourse->cap_do] ?? ['label' => 'Tổng hợp', 'class' => 'level-basic']; @endphp
                            <span class="level-pill {{ $levelInfo['class'] }}">{{ $levelInfo['label'] }}</span>
                            @if($featuredCourse->ngay_khai_giang)
                                <span class="date-pill">
                                    <i class="far fa-calendar-alt"></i>
                                    {{ $featuredCourse->ngay_khai_giang->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </article>
                @endif

                <div class="contact-surface">
                    <div>
                        <span class="eyebrow">Thông tin hệ thống</span>
                        <h3>Kênh liên hệ dành cho học viên mới</h3>
                    </div>
                    <ul class="contact-list">
                        @if(filled($settings['email']))
                            <li>
                                <i class="far fa-envelope"></i>
                                <a href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a>
                            </li>
                        @endif
                        @if(filled($settings['hotline']))
                            <li>
                                <i class="fas fa-phone-alt"></i>
                                <a href="tel:{{ preg_replace('/\s+/', '', $settings['hotline']) }}">{{ $settings['hotline'] }}</a>
                            </li>
                        @endif
                        @if(filled($settings['facebook']))
                            <li>
                                <i class="fab fa-facebook-f"></i>
                                <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener noreferrer">Facebook chính thức</a>
                            </li>
                        @endif
                        @if(filled($settings['zalo']))
                            <li>
                                <i class="fas fa-comment-dots"></i>
                                <a href="{{ $settings['zalo'] }}" target="_blank" rel="noopener noreferrer">Zalo hỗ trợ</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </section>

    @if($banners->isNotEmpty())
        <section class="banner-spotlight">
            <div class="container">
                <div id="homeBannerCarousel" class="carousel slide spotlight-carousel" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-inner">
                        @foreach($banners as $index => $banner)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <div class="banner-frame">
                                    <img src="{{ asset($banner->duong_dan_anh) }}" alt="{{ $banner->tieu_de }}">
                                    <div class="banner-overlay">
                                        <span class="eyebrow">Điểm nhấn từ admin</span>
                                        <h2>{{ $banner->tieu_de }}</h2>
                                        @if($banner->mo_ta)
                                            <div class="banner-copy">{!! $banner->mo_ta !!}</div>
                                        @endif
                                        @if($banner->link)
                                            <a href="{{ $banner->link }}" target="_blank" rel="noopener noreferrer" class="btn-primary-surface">Xem thêm</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($banners->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="category-wave">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Danh mục nổi bật</span>
                    <h2>Những nhóm ngành đang có khóa học công khai</h2>
                </div>
                <p>Người dùng chưa có tài khoản vẫn có thể duyệt nhanh lĩnh vực đang đào tạo trước khi quyết định đăng ký.</p>
            </div>

            <div class="category-cloud">
                @forelse($categories as $item)
                    <a href="{{ route('home', ['category' => $item->id]) }}#courses" class="category-chip {{ (string) $filters['category'] === (string) $item->id ? 'is-active' : '' }}">
                        <span>{{ $item->ten_nhom_nganh }}</span>
                        <strong>{{ $item->public_course_count }}</strong>
                    </a>
                @empty
                    <div class="empty-public-block">Hiện chưa có nhóm ngành công khai để hiển thị trên trang chủ.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="courses-zone" id="courses">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Khóa học công khai</span>
                    <h2>Không cần tài khoản vẫn xem được danh sách khóa học</h2>
                </div>
                <p>Trang chủ đã lấy trực tiếp từ hệ thống quản trị: khóa đang hoạt động, mô tả ngắn, hình ảnh, cấp độ và ngày khai giảng.</p>
            </div>

            <form method="GET" action="{{ route('home') }}" class="course-filter">
                <div class="filter-field">
                    <label for="q">Tìm khóa học</label>
                    <input id="q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tên khóa học, mã khóa học hoặc mô tả ngắn">
                </div>
                <div class="filter-field">
                    <label for="level">Cấp độ</label>
                    <select id="level" name="level">
                        <option value="">Tất cả cấp độ</option>
                        <option value="co_ban" @selected($filters['level'] === 'co_ban')>Cơ bản</option>
                        <option value="trung_binh" @selected($filters['level'] === 'trung_binh')>Trung bình</option>
                        <option value="nang_cao" @selected($filters['level'] === 'nang_cao')>Nâng cao</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="category">Nhóm ngành</label>
                    <select id="category" name="category">
                        <option value="">Tất cả nhóm ngành</option>
                        @foreach($categories as $item)
                            <option value="{{ $item->id }}" @selected((string) $filters['category'] === (string) $item->id)>{{ $item->ten_nhom_nganh }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary-surface">Lọc khóa học</button>
                    <a href="{{ route('home') }}#courses" class="btn-outline-surface">Đặt lại</a>
                </div>
            </form>

            <div class="course-grid">
                @forelse($courses as $course)
                    @php
                        $levelInfo = $levelLabels[$course->cap_do] ?? ['label' => 'Tổng hợp', 'class' => 'level-basic'];
                        $status = $statusLabels[$course->trang_thai_van_hanh] ?? ['label' => 'Đang cập nhật', 'class' => 'status-waiting'];
                    @endphp
                    <article class="course-card" data-aos="fade-up">
                        <div class="course-cover">
                            @if($course->hinh_anh)
                                <img src="{{ asset($course->hinh_anh) }}" alt="{{ $course->ten_khoa_hoc }}">
                            @else
                                <div class="course-cover-fallback">
                                    <span>{{ strtoupper(mb_substr($course->ten_khoa_hoc, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="course-overlay-top">
                                <span class="status-pill {{ $status['class'] }}">{{ $status['label'] }}</span>
                                <span class="level-pill {{ $levelInfo['class'] }}">{{ $levelInfo['label'] }}</span>
                            </div>
                        </div>

                        <div class="course-body">
                            <div class="course-meta">
                                <span>{{ optional($course->nhomNganh)->ten_nhom_nganh ?: 'Đa lĩnh vực' }}</span>
                                <strong>{{ $course->ma_khoa_hoc }}</strong>
                            </div>

                            <h3>{{ $course->ten_khoa_hoc }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($course->mo_ta_ngan ?: 'Khóa học công khai đang được hiển thị trên trang chủ để học viên mới có thể tìm hiểu trước khi đăng ký.', 140) }}</p>

                            <div class="course-data-grid">
                                <div>
                                    <span>Module</span>
                                    <strong>{{ number_format($course->module_hocs_count ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>Lịch học</span>
                                    <strong>{{ number_format($course->lich_hocs_count ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>Học viên</span>
                                    <strong>{{ number_format($course->hoc_vien_dang_hoc_count ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>Khai giảng</span>
                                    <strong>{{ $course->ngay_khai_giang ? $course->ngay_khai_giang->format('d/m/Y') : 'Đang cập nhật' }}</strong>
                                </div>
                            </div>

                            <div class="course-actions">
                                @guest
                                    <a href="{{ route('dang-ky') }}" class="btn-primary-surface">Tạo tài khoản để tham gia</a>
                                    <a href="{{ route('dang-nhap') }}" class="btn-outline-surface">Đã có tài khoản</a>
                                @else
                                    <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn-primary-surface">Xem luồng tham gia</a>
                                    <a href="{{ route('hoc-vien.dashboard') }}" class="btn-outline-surface">Vào khu học viên</a>
                                @endguest
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-public-block large">
                        <h3>Chưa có khóa học phù hợp</h3>
                        <p>Hãy thử bỏ bớt bộ lọc hoặc quay lại sau khi admin mở thêm khóa học mới.</p>
                        <a href="{{ route('home') }}#courses" class="btn-primary-surface">Xem lại toàn bộ</a>
                    </div>
                @endforelse
            </div>

            @if($courses->hasPages())
                <div class="pagination-shell">
                    {{ $courses->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>

    <section class="cta-strip">
        <div class="container cta-grid">
            <div>
                <span class="section-kicker">Sẵn sàng bắt đầu?</span>
                <h2>Tạo tài khoản để theo dõi khóa học, tài liệu và lịch học cá nhân.</h2>
            </div>
            <div class="cta-actions">
                @guest
                    <a href="{{ route('dang-ky') }}" class="btn-primary-surface">Đăng ký học viên</a>
                    <a href="{{ route('dang-nhap') }}" class="btn-outline-surface">Đăng nhập</a>
                @else
                    <a href="{{ route('hoc-vien.dashboard') }}" class="btn-primary-surface">Vào dashboard</a>
                    <a href="#courses" class="btn-outline-surface">Tiếp tục xem khóa học</a>
                @endguest
            </div>
        </div>
    </section>

    <section class="instructors-zone" id="instructors">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Giảng viên nổi bật</span>
                    <h2>Danh sách được admin chọn hiển thị trên trang chủ</h2>
                </div>
                <p>Phần này lấy trực tiếp từ cấu hình giảng viên nổi bật trong khu quản trị, không còn là dữ liệu mẫu cứng.</p>
            </div>

            <div class="instructor-grid">
                @forelse($featuredInstructors as $giangVien)
                    <article class="instructor-card-public" data-aos="fade-up">
                        <div class="instructor-avatar">
                            @if(optional($giangVien->nguoiDung)->anh_dai_dien)
                                <img src="{{ asset($giangVien->nguoiDung->anh_dai_dien) }}" alt="{{ $giangVien->nguoiDung->ho_ten }}">
                            @elseif($giangVien->avatar_url)
                                <img src="{{ asset($giangVien->avatar_url) }}" alt="{{ $giangVien->nguoiDung->ho_ten }}">
                            @else
                                <span>{{ strtoupper(mb_substr($giangVien->nguoiDung->ho_ten ?? 'G', 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="instructor-copy">
                            <span class="instructor-badge">{{ $giangVien->chuyen_nganh ?: 'Đang cập nhật chuyên ngành' }}</span>
                            <h3>{{ $giangVien->nguoiDung->ho_ten ?? 'Giảng viên' }}</h3>
                            <p>{{ $giangVien->mo_ta_ngan ?: 'Giảng viên được admin lựa chọn để đại diện cho năng lực đào tạo trên trang chủ.' }}</p>
                        </div>
                        <div class="instructor-foot">
                            <span>
                                <i class="fas fa-graduation-cap"></i>
                                {{ $giangVien->hoc_vi ?: 'Đang cập nhật học vị' }}
                            </span>
                            <span>
                                <i class="far fa-clock"></i>
                                {{ number_format((int) $giangVien->so_gio_day) }} giờ giảng dạy
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="empty-public-block">
                        Chưa có giảng viên nổi bật được chọn trong phần cài đặt hệ thống.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</main>

<footer class="site-footer" id="contact">
    <div class="container footer-grid">
        <div>
            <div class="brand-mark footer-brand">
                <div class="brand-logo">
                    @if(!empty($settings['site_logo']))
                        <img src="{{ asset($settings['site_logo']) }}" alt="{{ $settings['site_name'] ?: 'Logo hệ thống' }}">
                    @else
                        <span>K</span>
                    @endif
                </div>
                <div>
                    <div class="brand-kicker">Cổng thông tin công khai</div>
                    <div class="brand-name">{{ $settings['site_name'] ?: 'Khai Tri Education' }}</div>
                </div>
            </div>
            <p class="footer-copy">
                Trang chủ công khai dành cho người dùng chưa có tài khoản: xem khóa học, xem giảng viên nổi bật và kiểm tra thông tin liên hệ do admin cấu hình.
            </p>
        </div>

        <div>
            <h3>Liên hệ</h3>
            <ul class="footer-list">
                @if(filled($settings['hotline']))
                    <li><i class="fas fa-phone-alt"></i><a href="tel:{{ preg_replace('/\s+/', '', $settings['hotline']) }}">{{ $settings['hotline'] }}</a></li>
                @endif
                @if(filled($settings['email']))
                    <li><i class="far fa-envelope"></i><a href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a></li>
                @endif
                @if(filled($settings['address']))
                    <li class="address-line">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>{!! $settings['address'] !!}</div>
                    </li>
                @endif
            </ul>
        </div>

        <div>
            <h3>Mạng xã hội</h3>
            <div class="social-links-public">
                @if(filled($settings['facebook']))
                    <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                @endif
                @if(filled($settings['zalo']))
                    <a href="{{ $settings['zalo'] }}" target="_blank" rel="noopener noreferrer">Zalo</a>
                @endif
                <a href="{{ route('home') }}#courses">Khóa học</a>
                <a href="{{ route('dang-ky') }}">Đăng ký</a>
            </div>
        </div>
    </div>
</footer>
@endsection

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    :root {
        --home-bg: #f3f8ff;
        --ink: #102a43;
        --muted: #5f6f8a;
        --brand: #0d6efd;
        --brand-dark: #0a58ca;
        --accent-soft: rgba(13, 110, 253, 0.14);
        --shadow-lg: 0 30px 60px rgba(13, 42, 100, 0.14);
        --shadow-md: 0 18px 36px rgba(13, 42, 100, 0.10);
        --radius-xl: 32px;
        --radius-md: 18px;
    }

    html { scroll-behavior: smooth; }

    body {
        background: radial-gradient(circle at top left, #dfeeff 0%, var(--home-bg) 48%, #f8fbff 100%);
        color: var(--ink);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    h1, h2, h3, h4, .brand-name {
        font-family: 'Space Grotesk', sans-serif;
        letter-spacing: -0.03em;
    }

    a { text-decoration: none; }

    .announcement-bar {
        background: linear-gradient(90deg, #0a3d91, #0d6efd 52%, #3b82f6);
        color: #f8fbff;
        padding: 0.9rem 0;
    }

    .announcement-inner {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 1rem;
        align-items: start;
    }

    .announcement-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        opacity: 0.8;
    }

    .announcement-content {
        font-size: 0.95rem;
        line-height: 1.7;
    }

    .announcement-content p:last-child { margin-bottom: 0; }

    .site-header {
        position: sticky;
        top: 0;
        z-index: 1100;
        backdrop-filter: blur(18px);
        background: rgba(243, 248, 255, 0.82);
        border-bottom: 1px solid rgba(13, 42, 100, 0.08);
    }

    .header-shell {
        min-height: 86px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .brand-mark {
        display: inline-flex;
        align-items: center;
        gap: 0.9rem;
        color: var(--ink);
    }

    .brand-logo {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        background: linear-gradient(145deg, #0d6efd, #60a5fa);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #fff;
        font-weight: 700;
        font-size: 1.2rem;
        box-shadow: 0 14px 28px rgba(13, 110, 253, 0.24);
    }

    .brand-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .brand-kicker {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: var(--muted);
        margin-bottom: 0.15rem;
    }

    .brand-name {
        font-size: 1.2rem;
        font-weight: 700;
    }

    .site-nav {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .site-nav a {
        color: var(--muted);
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .site-nav a:hover { color: var(--brand); }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .contact-pill,
    .btn-primary-surface,
    .btn-outline-surface,
    .btn-ghost-surface {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-height: 48px;
        border-radius: 999px;
        padding: 0 1.15rem;
        font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .contact-pill:hover,
    .btn-primary-surface:hover,
    .btn-outline-surface:hover,
    .btn-ghost-surface:hover { transform: translateY(-1px); }

    .contact-pill {
        background: rgba(13, 110, 253, 0.10);
        color: var(--brand-dark);
        border: 1px solid rgba(13, 110, 253, 0.12);
    }

    .btn-primary-surface {
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: #fff;
        box-shadow: 0 14px 28px rgba(13, 110, 253, 0.22);
    }

    .btn-outline-surface {
        background: transparent;
        color: var(--ink);
        border: 1px solid rgba(13, 42, 100, 0.16);
    }

    .btn-ghost-surface {
        background: rgba(255, 255, 255, 0.72);
        color: var(--ink);
        border: 1px solid rgba(13, 42, 100, 0.08);
    }

    .hero-edu {
        position: relative;
        overflow: hidden;
        padding: 6rem 0 4rem;
    }

    .hero-backdrop {
        position: absolute;
        border-radius: 999px;
        filter: blur(20px);
        opacity: 0.55;
        pointer-events: none;
    }

    .hero-backdrop-one {
        width: 380px;
        height: 380px;
        background: rgba(96, 165, 250, 0.22);
        top: -70px;
        left: -80px;
    }

    .hero-backdrop-two {
        width: 420px;
        height: 420px;
        background: rgba(13, 110, 253, 0.16);
        right: -110px;
        bottom: -120px;
    }

    .hero-grid {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
        gap: 2rem;
        align-items: start;
    }

    .hero-badge,
    .section-kicker,
    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(13, 110, 253, 0.08);
        color: var(--brand-dark);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
    }

    .hero-copy h1 {
        font-size: clamp(2.8rem, 5vw, 5rem);
        line-height: 1.02;
        margin: 1.15rem 0 1.3rem;
        max-width: 10ch;
    }

    .hero-lead {
        font-size: 1.08rem;
        line-height: 1.85;
        color: var(--muted);
        max-width: 60ch;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin: 2rem 0;
    }

    .hero-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .metric-card,
    .spotlight-card,
    .contact-surface,
    .course-card,
    .instructor-card-public,
    .empty-public-block {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.65);
        box-shadow: var(--shadow-md);
        backdrop-filter: blur(10px);
    }

    .metric-card {
        border-radius: var(--radius-md);
        padding: 1.15rem;
    }

    .metric-label {
        display: block;
        color: var(--muted);
        font-size: 0.84rem;
        margin-bottom: 0.65rem;
    }

    .metric-card strong {
        display: block;
        font-size: 2rem;
        line-height: 1;
    }

    .metric-card small {
        display: block;
        color: var(--muted);
        margin-top: 0.65rem;
    }

    .hero-side {
        display: grid;
        gap: 1rem;
    }

    .spotlight-card,
    .contact-surface {
        border-radius: var(--radius-xl);
        padding: 1.6rem;
    }

    .spotlight-head,
    .spotlight-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .spotlight-card h2 {
        margin: 1rem 0 0.8rem;
        font-size: 1.85rem;
    }

    .spotlight-card p,
    .contact-surface p {
        color: var(--muted);
        line-height: 1.8;
    }

    .spotlight-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        margin: 1.4rem 0;
    }

    .spotlight-meta div {
        padding: 0.95rem 1rem;
        border-radius: 18px;
        background: rgba(227, 238, 255, 0.82);
        border: 1px solid rgba(13, 42, 100, 0.06);
    }

    .spotlight-meta span,
    .course-data-grid span {
        display: block;
        color: var(--muted);
        font-size: 0.82rem;
        margin-bottom: 0.35rem;
    }

    .spotlight-meta strong,
    .course-data-grid strong { font-size: 1rem; }

    .status-pill,
    .level-pill,
    .date-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .status-live {
        background: rgba(16, 185, 129, 0.16);
        color: #0f8a61;
    }

    .status-ready {
        background: rgba(59, 130, 246, 0.14);
        color: #2563eb;
    }

    .status-waiting {
        background: rgba(96, 165, 250, 0.18);
        color: #1d4ed8;
    }

    .level-basic {
        background: rgba(13, 110, 253, 0.12);
        color: var(--brand-dark);
    }

    .level-mid {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
    }

    .level-advanced {
        background: rgba(79, 70, 229, 0.14);
        color: #4338ca;
    }

    .date-pill {
        background: rgba(13, 42, 100, 0.07);
        color: var(--ink);
    }

    .contact-surface h3 {
        margin: 0.9rem 0 0.35rem;
        font-size: 1.5rem;
    }

    .contact-list {
        list-style: none;
        padding: 0;
        margin: 1.35rem 0 0;
        display: grid;
        gap: 0.9rem;
    }

    .contact-list li {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        color: var(--ink);
    }

    .contact-list i {
        width: 1.1rem;
        color: var(--brand);
        text-align: center;
    }

    .contact-list a { color: var(--ink); }

    .banner-spotlight,
    .category-wave,
    .courses-zone,
    .cta-strip,
    .instructors-zone {
        padding: 2rem 0 4rem;
    }

    .spotlight-carousel,
    .banner-frame {
        border-radius: var(--radius-xl);
        overflow: hidden;
    }

    .banner-frame {
        position: relative;
        min-height: 430px;
        box-shadow: var(--shadow-lg);
        background: #102a43;
    }

    .banner-frame img {
        width: 100%;
        height: 430px;
        object-fit: cover;
        opacity: 0.8;
    }

    .banner-overlay {
        position: absolute;
        inset: 0;
        padding: 2.5rem;
        display: flex;
        flex-direction: column;
        justify-content: end;
        background: linear-gradient(180deg, rgba(10, 31, 68, 0.08), rgba(10, 31, 68, 0.76));
        color: #fff;
    }

    .banner-overlay h2 {
        font-size: clamp(2rem, 3vw, 3.3rem);
        margin: 1rem 0 0.65rem;
    }

    .banner-copy {
        max-width: 60ch;
        line-height: 1.8;
        margin-bottom: 1.35rem;
    }

    .section-heading {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 0.7fr);
        gap: 1rem;
        align-items: end;
        margin-bottom: 1.8rem;
    }

    .section-heading h2 {
        margin: 0.8rem 0 0;
        font-size: clamp(2rem, 3vw, 3rem);
    }

    .section-heading p {
        margin: 0;
        color: var(--muted);
        line-height: 1.75;
    }

    .category-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
    }

    .category-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.9rem 1.15rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.84);
        border: 1px solid rgba(13, 42, 100, 0.08);
        color: var(--ink);
        font-weight: 700;
        box-shadow: 0 12px 24px rgba(13, 42, 100, 0.06);
    }

    .category-chip strong {
        min-width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(13, 110, 253, 0.12);
        color: var(--brand-dark);
    }

    .category-chip.is-active {
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: #fff;
    }

    .category-chip.is-active strong {
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
    }

    .course-filter {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 1rem;
        padding: 1.2rem;
        border-radius: 26px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: var(--shadow-md);
        margin-bottom: 1.8rem;
        border: 1px solid rgba(255, 255, 255, 0.74);
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .filter-field label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .filter-field input,
    .filter-field select {
        width: 100%;
        min-height: 52px;
        border-radius: 16px;
        border: 1px solid rgba(13, 42, 100, 0.12);
        background: rgba(250, 252, 255, 0.96);
        padding: 0 1rem;
        color: var(--ink);
    }

    .filter-actions {
        display: flex;
        align-items: end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .course-grid,
    .instructor-grid,
    .footer-grid {
        display: grid;
        gap: 1.3rem;
    }

    .course-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .course-card {
        border-radius: 30px;
        overflow: hidden;
    }

    .course-cover {
        position: relative;
        height: 220px;
        background: linear-gradient(145deg, #0a58ca, #0d6efd);
    }

    .course-cover img,
    .instructor-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-overlay-top {
        position: absolute;
        inset: 1rem 1rem auto 1rem;
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .course-cover-fallback,
    .instructor-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-family: 'Space Grotesk', sans-serif;
    }

    .course-cover-fallback {
        height: 100%;
        font-size: 4rem;
        background: linear-gradient(135deg, #0a58ca, #0d6efd, #60a5fa);
    }

    .course-body { padding: 1.35rem; }

    .course-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: var(--muted);
        font-size: 0.88rem;
        margin-bottom: 0.85rem;
    }

    .course-body h3 {
        font-size: 1.45rem;
        margin-bottom: 0.75rem;
    }

    .course-body p {
        color: var(--muted);
        line-height: 1.75;
        min-height: 4.9rem;
    }

    .course-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin: 1.2rem 0 1.25rem;
    }

    .course-data-grid > div {
        background: rgba(227, 238, 255, 0.74);
        border-radius: 18px;
        padding: 0.9rem 1rem;
    }

    .course-actions {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .cta-strip { padding-top: 0; }

    .cta-grid {
        border-radius: 36px;
        background: linear-gradient(135deg, #102a43 0%, #0a58ca 55%, #0d6efd 100%);
        color: #fff;
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1.5rem;
        align-items: center;
    }

    .cta-grid h2 {
        margin: 0.8rem 0 0;
        max-width: 18ch;
    }

    .cta-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .cta-grid .btn-outline-surface {
        color: #fff;
        border-color: rgba(255, 255, 255, 0.24);
    }

    .instructor-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .instructor-card-public {
        border-radius: 28px;
        padding: 1.35rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .instructor-avatar {
        width: 84px;
        height: 84px;
        border-radius: 24px;
        overflow: hidden;
        background: linear-gradient(135deg, #0a58ca, #60a5fa);
        font-size: 2rem;
    }

    .instructor-badge {
        display: inline-flex;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: var(--accent-soft);
        color: var(--brand-dark);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .instructor-copy h3 {
        margin: 0.8rem 0 0.55rem;
        font-size: 1.45rem;
    }

    .instructor-copy p {
        color: var(--muted);
        line-height: 1.7;
    }

    .instructor-foot {
        margin-top: auto;
        display: grid;
        gap: 0.55rem;
        color: var(--muted);
        font-size: 0.94rem;
    }

    .instructor-foot span {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .site-footer {
        padding: 2.5rem 0 3rem;
        background: #102a43;
        color: rgba(255, 255, 255, 0.78);
    }

    .footer-grid {
        grid-template-columns: 1.15fr 1fr 0.8fr;
        align-items: start;
    }

    .footer-brand {
        color: #fff;
        margin-bottom: 1rem;
    }

    .footer-copy {
        max-width: 48ch;
        line-height: 1.85;
    }

    .site-footer h3 {
        color: #fff;
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .footer-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 0.8rem;
    }

    .footer-list li {
        display: flex;
        align-items: start;
        gap: 0.75rem;
    }

    .footer-list i {
        width: 1rem;
        color: #93c5fd;
        margin-top: 0.2rem;
    }

    .footer-list a,
    .social-links-public a {
        color: rgba(255, 255, 255, 0.88);
    }

    .social-links-public {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .empty-public-block {
        border-radius: 28px;
        padding: 1.5rem;
        text-align: center;
        color: var(--muted);
    }

    .empty-public-block.large {
        padding: 2.5rem 1.5rem;
        grid-column: 1 / -1;
    }

    .pagination-shell {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    .pagination-shell .pagination { gap: 0.35rem; }

    .pagination-shell .page-link {
        border-radius: 999px;
        border: none;
        color: var(--ink);
        min-width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(13, 42, 100, 0.08);
    }

    .pagination-shell .active > .page-link {
        background: var(--brand);
        color: #fff;
    }

    @media (max-width: 1199px) {
        .hero-grid,
        .section-heading,
        .cta-grid,
        .footer-grid {
            grid-template-columns: 1fr;
        }

        .course-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .instructor-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .course-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .header-shell,
        .header-actions {
            flex-wrap: wrap;
        }

        .site-nav {
            width: 100%;
            order: 3;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .hero-copy h1 {
            max-width: none;
        }

        .hero-metrics {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .hero-edu { padding-top: 4rem; }

        .course-grid,
        .instructor-grid,
        .course-filter,
        .announcement-inner {
            grid-template-columns: 1fr;
        }

        .brand-mark { width: 100%; }

        .header-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .banner-frame,
        .banner-frame img {
            min-height: 360px;
            height: 360px;
        }

        .banner-overlay { padding: 1.5rem; }

        .spotlight-meta,
        .course-data-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
