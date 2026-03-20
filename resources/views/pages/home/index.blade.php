@extends('layouts.home')

@section('title', ($settings['site_name'] ?: 'Trang chu') . ' - Khong gian hoc tap')

@section('content')
@php
    $levelLabels = [
        'co_ban' => ['label' => 'Co ban', 'class' => 'level-basic'],
        'trung_binh' => ['label' => 'Trung binh', 'class' => 'level-mid'],
        'nang_cao' => ['label' => 'Nang cao', 'class' => 'level-advanced'],
    ];

    $statusLabels = [
        'dang_day' => ['label' => 'Dang giang day', 'class' => 'status-live'],
        'san_sang' => ['label' => 'San sang khai giang', 'class' => 'status-ready'],
        'cho_giang_vien' => ['label' => 'Dang hoan thien lich hoc', 'class' => 'status-waiting'],
    ];

    $homeUser = auth()->user();
@endphp

@if(filled($settings['general_notification']))
    <div class="announcement-bar">
        <div class="container announcement-inner">
            <div class="announcement-label">Thong bao tu he thong</div>
            <div class="announcement-content">{!! $settings['general_notification'] !!}</div>
        </div>
    </div>
@endif

<header class="site-header">
    <div class="container header-shell">
        <a href="{{ route('home') }}" class="brand-mark">
            <div class="brand-logo">
                @if(!empty($settings['site_logo']))
                    <img src="{{ asset($settings['site_logo']) }}" alt="{{ $settings['site_name'] ?: 'Logo he thong' }}">
                @else
                    <span>K</span>
                @endif
            </div>
            <div>
                <div class="brand-kicker">He thong dao tao</div>
                <div class="brand-name">{{ $settings['site_name'] ?: 'Khai Tri Education' }}</div>
            </div>
        </a>

        <nav class="site-nav">
            <a href="#hero">Trang chu</a>
            <a href="#courses">Khoa hoc</a>
            <a href="#instructors">Giang vien</a>
            <a href="#contact">Lien he</a>
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
                <a href="{{ $dashboardRoute }}" class="btn-primary-surface">Vao dashboard</a>
            @else
                <a href="{{ route('dang-nhap') }}" class="btn-ghost-surface">Dang nhap</a>
                <a href="{{ route('dang-ky') }}" class="btn-primary-surface">Tao tai khoan</a>
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
                <div class="hero-badge">Learning hub cho nguoi hoc moi</div>
                <h1>Kham pha cac khoa hoc dang mo ngay tu trang chu.</h1>
                <p class="hero-lead">
                    {{ $settings['site_name'] ?: 'He thong Khai Tri' }} hien thi cong khai cac khoa hoc dang hoat dong,
                    thong tin lien he tu admin va doi ngu giang vien noi bat de nguoi dung chua co tai khoan van co the tim hieu truoc khi dang ky.
                </p>

                <div class="hero-actions">
                    <a href="#courses" class="btn-primary-surface">Xem khoa hoc</a>
                    @guest
                        <a href="{{ route('dang-ky') }}" class="btn-outline-surface">Dang ky hoc vien</a>
                    @else
                        <a href="#contact" class="btn-outline-surface">Xem thong tin lien he</a>
                    @endguest
                </div>

                <div class="hero-metrics">
                    <article class="metric-card" data-aos="fade-up" data-aos-delay="50">
                        <span class="metric-label">Khoa hoc cong khai</span>
                        <strong>{{ number_format($stats['tong_khoa_hoc']) }}</strong>
                        <small>{{ number_format($stats['sap_khai_giang']) }} khoa sap khai giang</small>
                    </article>
                    <article class="metric-card" data-aos="fade-up" data-aos-delay="120">
                        <span class="metric-label">Hoc vien dang hoc</span>
                        <strong>{{ number_format($stats['tong_hoc_vien']) }}</strong>
                        <small>Du lieu that tu he thong dao tao</small>
                    </article>
                    <article class="metric-card" data-aos="fade-up" data-aos-delay="190">
                        <span class="metric-label">Module chuyen mon</span>
                        <strong>{{ number_format($stats['tong_module']) }}</strong>
                        <small>{{ number_format($stats['tong_giang_vien_noi_bat']) }} giang vien noi bat</small>
                    </article>
                </div>
            </div>

            <div class="hero-side" data-aos="fade-left">
                @if($featuredCourse)
                    <article class="spotlight-card">
                        <div class="spotlight-head">
                            <span class="eyebrow">Khoa hoc noi bat</span>
                            @php $status = $statusLabels[$featuredCourse->trang_thai_van_hanh] ?? ['label' => 'Dang cap nhat', 'class' => 'status-waiting']; @endphp
                            <span class="status-pill {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </div>

                        <h2>{{ $featuredCourse->ten_khoa_hoc }}</h2>
                        <p>
                            {{ $featuredCourse->mo_ta_ngan ?: 'Khoa hoc dang hoat dong va san sang de nguoi hoc tim hieu truoc khi tao tai khoan tham gia.' }}
                        </p>

                        <div class="spotlight-meta">
                            <div>
                                <span>Ma khoa</span>
                                <strong>{{ $featuredCourse->ma_khoa_hoc }}</strong>
                            </div>
                            <div>
                                <span>Nhom nganh</span>
                                <strong>{{ optional($featuredCourse->nhomNganh)->ten_nhom_nganh ?: 'Da linh vuc' }}</strong>
                            </div>
                            <div>
                                <span>Module</span>
                                <strong>{{ number_format($featuredCourse->module_hocs_count ?? 0) }}</strong>
                            </div>
                            <div>
                                <span>Hoc vien</span>
                                <strong>{{ number_format($featuredCourse->hoc_vien_dang_hoc_count ?? 0) }}</strong>
                            </div>
                        </div>

                        <div class="spotlight-footer">
                            @php $levelInfo = $levelLabels[$featuredCourse->cap_do] ?? ['label' => 'Tong hop', 'class' => 'level-basic']; @endphp
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
                        <span class="eyebrow">Thong tin he thong</span>
                        <h3>Kenh lien he danh cho hoc vien moi</h3>
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
                                <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener noreferrer">Facebook chinh thuc</a>
                            </li>
                        @endif
                        @if(filled($settings['zalo']))
                            <li>
                                <i class="fas fa-comment-dots"></i>
                                <a href="{{ $settings['zalo'] }}" target="_blank" rel="noopener noreferrer">Zalo ho tro</a>
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
                                        <span class="eyebrow">Diem nhan tu admin</span>
                                        <h2>{{ $banner->tieu_de }}</h2>
                                        @if($banner->mo_ta)
                                            <div class="banner-copy">{!! $banner->mo_ta !!}</div>
                                        @endif
                                        @if($banner->link)
                                            <a href="{{ $banner->link }}" target="_blank" rel="noopener noreferrer" class="btn-primary-surface">Xem them</a>
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
                    <span class="section-kicker">Danh muc noi bat</span>
                    <h2>Nhung nhom nganh dang co khoa hoc cong khai</h2>
                </div>
                <p>Nguoi dung chua co tai khoan van co the duyet nhanh linh vuc dang dao tao truoc khi quyet dinh dang ky.</p>
            </div>

            <div class="category-cloud">
                @forelse($categories as $item)
                    <a href="{{ route('home', ['category' => $item->id]) }}#courses" class="category-chip {{ (string) $filters['category'] === (string) $item->id ? 'is-active' : '' }}">
                        <span>{{ $item->ten_nhom_nganh }}</span>
                        <strong>{{ $item->public_course_count }}</strong>
                    </a>
                @empty
                    <div class="empty-public-block">Hien chua co nhom nganh cong khai de hien thi tren trang chu.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="courses-zone" id="courses">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Khoa hoc cong khai</span>
                    <h2>Khong can tai khoan van xem duoc danh sach khoa hoc</h2>
                </div>
                <p>Trang chu da lay truc tiep tu he thong quan tri: khoa dang hoat dong, mo ta ngan, hinh anh, cap do va ngay khai giang.</p>
            </div>

            <form method="GET" action="{{ route('home') }}" class="course-filter">
                <div class="filter-field">
                    <label for="q">Tim khoa hoc</label>
                    <input id="q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Ten khoa hoc, ma khoa hoc hoac mo ta ngan">
                </div>
                <div class="filter-field">
                    <label for="level">Cap do</label>
                    <select id="level" name="level">
                        <option value="">Tat ca cap do</option>
                        <option value="co_ban" @selected($filters['level'] === 'co_ban')>Co ban</option>
                        <option value="trung_binh" @selected($filters['level'] === 'trung_binh')>Trung binh</option>
                        <option value="nang_cao" @selected($filters['level'] === 'nang_cao')>Nang cao</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="category">Nhom nganh</label>
                    <select id="category" name="category">
                        <option value="">Tat ca nhom nganh</option>
                        @foreach($categories as $item)
                            <option value="{{ $item->id }}" @selected((string) $filters['category'] === (string) $item->id)>{{ $item->ten_nhom_nganh }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary-surface">Loc khoa hoc</button>
                    <a href="{{ route('home') }}#courses" class="btn-outline-surface">Dat lai</a>
                </div>
            </form>

            <div class="course-grid">
                @forelse($courses as $course)
                    @php
                        $levelInfo = $levelLabels[$course->cap_do] ?? ['label' => 'Tong hop', 'class' => 'level-basic'];
                        $status = $statusLabels[$course->trang_thai_van_hanh] ?? ['label' => 'Dang cap nhat', 'class' => 'status-waiting'];
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
                                <span>{{ optional($course->nhomNganh)->ten_nhom_nganh ?: 'Da linh vuc' }}</span>
                                <strong>{{ $course->ma_khoa_hoc }}</strong>
                            </div>

                            <h3>{{ $course->ten_khoa_hoc }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($course->mo_ta_ngan ?: 'Khoa hoc cong khai dang duoc hien thi tren trang chu de hoc vien moi co the tim hieu truoc khi dang ky.', 140) }}</p>

                            <div class="course-data-grid">
                                <div>
                                    <span>Module</span>
                                    <strong>{{ number_format($course->module_hocs_count ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>Lich hoc</span>
                                    <strong>{{ number_format($course->lich_hocs_count ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>Hoc vien</span>
                                    <strong>{{ number_format($course->hoc_vien_dang_hoc_count ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>Khai giang</span>
                                    <strong>{{ $course->ngay_khai_giang ? $course->ngay_khai_giang->format('d/m/Y') : 'Dang cap nhat' }}</strong>
                                </div>
                            </div>

                            <div class="course-actions">
                                @guest
                                    <a href="{{ route('dang-ky') }}" class="btn-primary-surface">Tao tai khoan de tham gia</a>
                                    <a href="{{ route('dang-nhap') }}" class="btn-outline-surface">Da co tai khoan</a>
                                @else
                                    <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn-primary-surface">Xem luong tham gia</a>
                                    <a href="{{ route('hoc-vien.dashboard') }}" class="btn-outline-surface">Vao khu hoc vien</a>
                                @endguest
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-public-block large">
                        <h3>Chua co khoa hoc phu hop</h3>
                        <p>Hay thu bo bot bo loc hoac quay lai sau khi admin mo them khoa hoc moi.</p>
                        <a href="{{ route('home') }}#courses" class="btn-primary-surface">Xem lai toan bo</a>
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
                <span class="section-kicker">San sang bat dau?</span>
                <h2>Tao tai khoan de theo doi khoa hoc, tai lieu va lich hoc ca nhan.</h2>
            </div>
            <div class="cta-actions">
                @guest
                    <a href="{{ route('dang-ky') }}" class="btn-primary-surface">Dang ky hoc vien</a>
                    <a href="{{ route('dang-nhap') }}" class="btn-outline-surface">Dang nhap</a>
                @else
                    <a href="{{ route('hoc-vien.dashboard') }}" class="btn-primary-surface">Vao dashboard</a>
                    <a href="#courses" class="btn-outline-surface">Tiep tuc xem khoa hoc</a>
                @endguest
            </div>
        </div>
    </section>

    <section class="instructors-zone" id="instructors">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Giang vien noi bat</span>
                    <h2>Danh sach duoc admin chon hien thi tren trang chu</h2>
                </div>
                <p>Phan nay lay truc tiep tu cau hinh giang vien noi bat trong khu quan tri, khong con la du lieu mau cung.</p>
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
                            <span class="instructor-badge">{{ $giangVien->chuyen_nganh ?: 'Dang cap nhat chuyen nganh' }}</span>
                            <h3>{{ $giangVien->nguoiDung->ho_ten ?? 'Giang vien' }}</h3>
                            <p>{{ $giangVien->mo_ta_ngan ?: 'Giang vien duoc admin lua chon de dai dien cho nang luc dao tao tren trang chu.' }}</p>
                        </div>
                        <div class="instructor-foot">
                            <span>
                                <i class="fas fa-graduation-cap"></i>
                                {{ $giangVien->hoc_vi ?: 'Dang cap nhat hoc vi' }}
                            </span>
                            <span>
                                <i class="far fa-clock"></i>
                                {{ number_format((int) $giangVien->so_gio_day) }} gio giang day
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="empty-public-block">
                        Chua co giang vien noi bat duoc chon trong phan cai dat he thong.
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
                        <img src="{{ asset($settings['site_logo']) }}" alt="{{ $settings['site_name'] ?: 'Logo he thong' }}">
                    @else
                        <span>K</span>
                    @endif
                </div>
                <div>
                    <div class="brand-kicker">Cong thong tin cong khai</div>
                    <div class="brand-name">{{ $settings['site_name'] ?: 'Khai Tri Education' }}</div>
                </div>
            </div>
            <p class="footer-copy">
                Trang chu cong khai danh cho nguoi dung chua co tai khoan: xem khoa hoc, xem giang vien noi bat va kiem tra thong tin lien he do admin cau hinh.
            </p>
        </div>

        <div>
            <h3>Lien he</h3>
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
            <h3>Mang xa hoi</h3>
            <div class="social-links-public">
                @if(filled($settings['facebook']))
                    <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                @endif
                @if(filled($settings['zalo']))
                    <a href="{{ $settings['zalo'] }}" target="_blank" rel="noopener noreferrer">Zalo</a>
                @endif
                <a href="{{ route('home') }}#courses">Khoa hoc</a>
                <a href="{{ route('dang-ky') }}">Dang ky</a>
            </div>
        </div>
    </div>
</footer>
@endsection

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    :root {
        --home-bg: #f8f5ee;
        --ink: #10231f;
        --muted: #5e6f6b;
        --brand: #0f766e;
        --brand-dark: #0b5d56;
        --accent-soft: rgba(245, 158, 11, 0.14);
        --shadow-lg: 0 30px 60px rgba(18, 40, 35, 0.12);
        --shadow-md: 0 18px 36px rgba(18, 40, 35, 0.10);
        --radius-xl: 32px;
        --radius-md: 18px;
    }

    html { scroll-behavior: smooth; }

    body {
        background: radial-gradient(circle at top left, #fff7e4 0%, var(--home-bg) 48%, #f7fbf8 100%);
        color: var(--ink);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    h1, h2, h3, h4, .brand-name {
        font-family: 'Space Grotesk', sans-serif;
        letter-spacing: -0.03em;
    }

    a { text-decoration: none; }

    .announcement-bar {
        background: linear-gradient(90deg, #103f3b, #0f766e 52%, #127c73);
        color: #f7fffb;
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
        background: rgba(248, 245, 238, 0.78);
        border-bottom: 1px solid rgba(16, 35, 31, 0.08);
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
        background: linear-gradient(145deg, #0f766e, #1f9d89);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #fff;
        font-weight: 700;
        font-size: 1.2rem;
        box-shadow: 0 14px 28px rgba(15, 118, 110, 0.24);
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
        background: rgba(15, 118, 110, 0.10);
        color: var(--brand-dark);
        border: 1px solid rgba(15, 118, 110, 0.12);
    }

    .btn-primary-surface {
        background: linear-gradient(135deg, #0f766e, #0a5d56);
        color: #fff;
        box-shadow: 0 14px 28px rgba(15, 118, 110, 0.20);
    }

    .btn-outline-surface {
        background: transparent;
        color: var(--ink);
        border: 1px solid rgba(16, 35, 31, 0.16);
    }

    .btn-ghost-surface {
        background: rgba(255, 255, 255, 0.72);
        color: var(--ink);
        border: 1px solid rgba(16, 35, 31, 0.08);
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
        background: rgba(245, 158, 11, 0.16);
        top: -70px;
        left: -80px;
    }

    .hero-backdrop-two {
        width: 420px;
        height: 420px;
        background: rgba(15, 118, 110, 0.12);
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
        background: rgba(15, 118, 110, 0.08);
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
        background: rgba(245, 237, 224, 0.78);
        border: 1px solid rgba(16, 35, 31, 0.06);
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
        background: rgba(245, 158, 11, 0.16);
        color: #c57900;
    }

    .level-basic {
        background: rgba(15, 118, 110, 0.12);
        color: var(--brand-dark);
    }

    .level-mid {
        background: rgba(245, 158, 11, 0.16);
        color: #ba6b00;
    }

    .level-advanced {
        background: rgba(190, 24, 93, 0.14);
        color: #be185d;
    }

    .date-pill {
        background: rgba(16, 35, 31, 0.07);
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
        background: #1a2a27;
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
        background: linear-gradient(180deg, rgba(8, 20, 18, 0.05), rgba(8, 20, 18, 0.72));
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
        border: 1px solid rgba(16, 35, 31, 0.08);
        color: var(--ink);
        font-weight: 700;
        box-shadow: 0 12px 24px rgba(16, 35, 31, 0.06);
    }

    .category-chip strong {
        min-width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--brand-dark);
    }

    .category-chip.is-active {
        background: linear-gradient(135deg, #0f766e, #0c5c55);
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
        border: 1px solid rgba(255, 255, 255, 0.7);
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
        border: 1px solid rgba(16, 35, 31, 0.12);
        background: rgba(255, 253, 248, 0.94);
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
        background: linear-gradient(145deg, #114a45, #0f766e);
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
        background: linear-gradient(135deg, #0f766e, #1b9c74, #f59e0b);
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
        background: rgba(245, 237, 224, 0.68);
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
        background: linear-gradient(135deg, #10231f 0%, #0f4e49 55%, #106b62 100%);
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
        background: linear-gradient(135deg, #0f766e, #f59e0b);
        font-size: 2rem;
    }

    .instructor-badge {
        display: inline-flex;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: var(--accent-soft);
        color: #b66900;
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
        background: #10231f;
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
        color: #f8c36d;
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
        box-shadow: 0 10px 20px rgba(16, 35, 31, 0.08);
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
