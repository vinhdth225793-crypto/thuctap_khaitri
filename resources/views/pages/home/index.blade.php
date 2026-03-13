@extends('layouts.home')
@section('title', 'Trang chủ')

@section('content')

{{-- ===== BANNER QUAY LẠI DASHBOARD (chỉ hiện khi đã đăng nhập) ===== --}}
@auth
@php
    $authUser  = auth()->user();
    $dashRoute = match($authUser->vai_tro) {
        'admin'      => route('admin.dashboard'),
        'giang_vien' => route('giang-vien.dashboard'),
        default      => route('hoc-vien.dashboard'),
    };

    // Label nút theo vai trò
    $dashLabel = match($authUser->vai_tro) {
        'admin'      => '⚙️ Quay lại Dashboard quản trị',
        'giang_vien' => '📚 Hoạt động dạy của ' . $authUser->ho_ten,
        default      => '🎓 Hoạt động học của ' . $authUser->ho_ten,
    };

    // Màu sắc theo vai trò
    $dashColor = match($authUser->vai_tro) {
        'admin'      => '#dc2626',   // đỏ
        'giang_vien' => '#2563eb',   // xanh dương
        default      => '#16a34a',   // xanh lá
    };

    $homeAvatar2 = $authUser->anh_dai_dien ? asset($authUser->anh_dai_dien) : null;
@endphp

<div style="background: {{ $dashColor }}; color:white; padding:8px 20px;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:2000;
            box-shadow:0 2px 10px rgba(0,0,0,.2); gap:12px; flex-wrap:wrap;">

    {{-- Thông tin user --}}
    <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
        @if($homeAvatar2)
            <img src="{{ $homeAvatar2 }}" alt="{{ $authUser->ho_ten }}"
                 style="width:30px; height:30px; border-radius:50%; object-fit:cover;
                        border:2px solid rgba(255,255,255,0.7);">
        @else
            <div style="width:30px; height:30px; border-radius:50%;
                        background:rgba(255,255,255,0.25); color:white; font-weight:800;
                        font-size:13px; display:flex; align-items:center; justify-content:center;
                        border:2px solid rgba(255,255,255,0.5);">
                {{ strtoupper(mb_substr($authUser->ho_ten, 0, 1)) }}
            </div>
        @endif
        <span style="font-size:13px; font-weight:600; opacity:.9;">
            Xin chào, <strong>{{ $authUser->ho_ten }}</strong>
        </span>
    </div>

    {{-- Nút quay lại dashboard --}}
    <a href="{{ $dashRoute }}"
       style="background:white; color:{{ $dashColor }}; border:none; border-radius:20px;
              padding:5px 18px; font-size:13px; font-weight:800; text-decoration:none;
              display:flex; align-items:center; gap:6px; white-space:nowrap;
              transition:all .2s; box-shadow:0 2px 8px rgba(0,0,0,.15);"
       onmouseover="this.style.transform='scale(1.04)'"
       onmouseout="this.style.transform='scale(1)'">
        {{ $dashLabel }}
    </a>

</div>
@endauth
{{-- ===== END BANNER ===== --}}

<!-- ========== HEADER & NAVBAR ========== -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" style="font-size: 1.5rem; font-weight: 700; color: white;">
            @if(isset($settings['site_logo']) && $settings['site_logo'])
                <img src="{{ asset($settings['site_logo']) }}" alt="Logo" style="height: 2rem; margin-right:0.5rem; object-fit: contain;">
            @else
                <i class="fas fa-graduation-cap" style="font-size: 2rem; margin-right: 0.5rem;"></i>
            @endif
            <span>{{ $settings['site_name'] ?? 'EduClick' }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('home') }}" style="color: rgba(255,255,255,0.85) !important; font-weight: 500; margin: 0 0.5rem;">
                        <i class="fas fa-home"></i> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" style="color: rgba(255,255,255,0.85) !important; font-weight: 500; margin: 0 0.5rem;">
                        <i class="fas fa-bullhorn"></i> Tuyển sinh
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" style="color: rgba(255,255,255,0.85) !important; font-weight: 500; margin: 0 0.5rem;">
                        <i class="fas fa-newspaper"></i> Tin tức
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" style="color: rgba(255,255,255,0.85) !important; font-weight: 500; margin: 0 0.5rem;">
                        <i class="fas fa-globe"></i> Du học
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dang-ky') }}" style="color: rgba(255,255,255,0.85) !important; font-weight: 500; margin: 0 0.5rem;">
                        <i class="fas fa-book-open"></i> Đăng kí học
                    </a>
                </li>
                @auth
                    @php
                        $homeUser   = auth()->user();
                        $homeAvatar = $homeUser->anh_dai_dien ? asset($homeUser->anh_dai_dien) : null;
                    @endphp
                    <li class="nav-item dropdown" style="margin-left: 1rem;">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: rgba(255,255,255,0.85) !important; padding: 0; gap: 0.5rem;">
                            @if($homeAvatar)
                                <img src="{{ $homeAvatar }}"
                                     alt="{{ $homeUser->ho_ten }}"
                                     style="width:36px; height:36px; border-radius:50%; object-fit:cover;
                                            border:2px solid rgba(255,255,255,0.6); flex-shrink:0;">
                            @else
                                <div style="width:36px; height:36px; border-radius:50%;
                                            background:rgba(255,255,255,0.25);
                                            display:flex; align-items:center; justify-content:center;
                                            color:white; font-weight:800; font-size:15px;
                                            border:2px solid rgba(255,255,255,0.5); flex-shrink:0;">
                                    {{ strtoupper(mb_substr($homeUser->ho_ten, 0, 1)) }}
                                </div>
                            @endif
                            <span style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $homeUser->ho_ten }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="background: white; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.15);">
                            <li>
                                <div style="padding:12px 16px; display:flex; align-items:center; gap:10px;
                                            background:linear-gradient(135deg,rgba(102,126,234,.08),rgba(102,126,234,.03));
                                            border-bottom:1px solid #f1f5f9;">
                                    @if($homeAvatar)
                                        <img src="{{ $homeAvatar }}"
                                             style="width:38px; height:38px; border-radius:50%; object-fit:cover;
                                                    border:2px solid #c7d2fe;">
                                    @else
                                        <div style="width:38px; height:38px; border-radius:50%; background:#667eea;
                                                    display:flex; align-items:center; justify-content:center;
                                                    color:white; font-weight:800; font-size:15px;">
                                            {{ strtoupper(mb_substr($homeUser->ho_ten, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700; color:#1e293b; font-size:13px; line-height:1.2;">
                                            {{ $homeUser->ho_ten }}
                                        </div>
                                        <div style="font-size:11px; color:#64748b; margin-top:2px;">
                                            @if($homeUser->vai_tro === 'admin') Quản trị viên
                                            @elseif($homeUser->vai_tro === 'giang_vien') Giảng viên
                                            @else Học viên
                                            @endif
                                            &middot; {{ $homeUser->email }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider" style="margin:4px 0;"></li>
                            <li>
                                <a class="dropdown-item" style="color:#1d4ed8; font-weight:600;"
                                   href="@if($homeUser->vai_tro === 'admin'){{ route('admin.dashboard') }}
                                         @elseif($homeUser->vai_tro === 'giang_vien'){{ route('giang-vien.dashboard') }}
                                         @else{{ route('hoc-vien.dashboard') }}@endif">
                                    <i class="fas fa-tachometer-alt" style="color:#2563eb;"></i>
                                    Vào Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}" style="color: #333;">
                                    <i class="fas fa-user" style="color: #667eea;"></i> Hồ sơ cá nhân
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#"
                                   onclick="event.preventDefault(); document.getElementById('home-logout-form').submit();"
                                   style="color:#d32f2f;">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </a>
                                <form id="home-logout-form" action="{{ route('dang-xuat') }}" method="POST" style="display:none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item" style="margin-left: 1rem;">
                        <a class="btn" href="{{ route('dang-nhap') }}" style="background-color: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); border-radius: 25px; padding: 0.4rem 1.2rem; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@if(isset($banners) && $banners->isNotEmpty())
<div id="mainBannerSlider" class="carousel slide shadow-sm" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner">
        @foreach($banners as $i => $banner)
        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
            @if($banner->link)<a href="{{ $banner->link }}">@endif
            <img src="{{ asset($banner->duong_dan_anh) }}" class="d-block w-100"
                 alt="{{ $banner->tieu_de }}" style="height:420px; object-fit:cover;">
            @if($banner->link)</a>@endif
            <div class="carousel-caption d-none d-md-block text-start bg-dark bg-opacity-25 p-4 rounded-3"
                 style="left:6%; bottom:40px; text-shadow:0 2px 10px rgba(0,0,0,0.5); backdrop-filter: blur(2px);">
                <h2 class="fw-bold display-6 mb-2">{{ $banner->tieu_de }}</h2>
                @if($banner->mo_ta)<div class="lead mb-0 opacity-90">{!! $banner->mo_ta !!}</div>@endif
            </div>
        </div>
        @endforeach
    </div>
    @if($banners->count() > 1)
    <button class="carousel-control-prev" type="button" data-bs-target="#mainBannerSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon shadow-sm" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#mainBannerSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon shadow-sm" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
    <div class="carousel-indicators">
        @foreach($banners as $i => $b)
        <button type="button" data-bs-target="#mainBannerSlider" data-bs-slide-to="{{ $i }}" class="{{ $i===0?'active':'' }}" aria-current="{{ $i===0?'true':'false' }}" aria-label="Slide {{ $i+1 }}"></button>
        @endforeach
    </div>
    @endif
</div>
@endif

<!-- ========== HERO SECTION ========== -->
<section style="padding: 100px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; position: relative; overflow: hidden;" id="home">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 style="font-size: 3.5rem; font-weight: 700; margin-bottom: 1.5rem; line-height: 1.2;">Nền Tảng Học Tập Thông Minh</h1>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.95;">Kết nối học viên với giảng viên chuyên môn, học tập linh hoạt và phát triển kỹ năng thực tế.</p>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    @guest
                        <a href="{{ route('dang-ky') }}" class="btn" style="background: white; color: #667eea; padding: 0.75rem 2.5rem; border-radius: 25px; font-weight: 600; border: none; cursor: pointer; text-decoration: none;">
                            <i class="fas fa-rocket me-2"></i> Đăng ký ngay
                        </a>
                        <a href="{{ route('dang-nhap') }}" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 0.75rem 2.5rem; border-radius: 25px; font-weight: 600; border: 2px solid white; cursor: pointer; text-decoration: none;">
                            <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
                        </a>
                    @else
                        <a href="@if(auth()->user()->vai_tro === 'admin'){{ route('admin.dashboard') }}@elseif(auth()->user()->vai_tro === 'giang_vien'){{ route('giang-vien.dashboard') }}@else{{ route('hoc-vien.dashboard') }}@endif" class="btn" style="background: white; color: #667eea; padding: 0.75rem 2.5rem; border-radius: 25px; font-weight: 600; border: none; cursor: pointer; text-decoration: none;">
                            <i class="fas fa-arrow-right me-2"></i> Vào Dashboard
                        </a>
                    @endguest
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <img src="https://cdn.pixabay.com/photo/2016/11/21/14/31/machine-learning-1846618_1280.jpg" alt="Học tập thông minh" class="img-fluid" style="border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            </div>
        </div>
    </div>
</section>

<!-- ========== FEATURES SECTION ========== -->
<section style="padding: 80px 0; background: #f8f9fa;" id="features">
    <div class="container">
        <h2 style="font-size: 2.5rem; font-weight: 700; text-align: center; margin-bottom: 1rem; color: #333;">Tính Năng Nổi Bật</h2>
        <p style="text-align: center; color: #666; margin-bottom: 3rem; font-size: 1.1rem;">Trải nghiệm hệ thống với đầy đủ tính năng hiện đại</p>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <i class="fas fa-chalkboard-teacher" style="font-size: 2.5rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 1rem; color: #333;">Giảng Dạy Thông Minh</h4>
                    <p style="color: #666;">Các giảng viên dày dạn kinh nghiệm hướng dẫn một cách trực quan và hiệu quả.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <i class="fas fa-user-tie" style="font-size: 2.5rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 1rem; color: #333;">Học Tập Cá Nhân</h4>
                    <p style="color: #666;">Chương trình học được tùy chỉnh theo nhu cầu và tốc độ học tập của bạn.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <i class="fas fa-chart-line" style="font-size: 2.5rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 1rem; color: #333;">Theo Dõi Tiến Độ</h4>
                    <p style="color: #666;">Xem chi tiết tiến độ học tập của bạn qua các biểu đồ trực quan.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <i class="fas fa-tasks" style="font-size: 2.5rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 1rem; color: #333;">Đánh Giá Đa Dạng</h4>
                    <p style="color: #666;">Nhiều hình thức kiểm tra để đánh giá toàn diện kiến thức của bạn.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <i class="fas fa-comments" style="font-size: 2.5rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 1rem; color: #333;">Tương Tác Hai Chiều</h4>
                    <p style="color: #666;">Giao tiếp trực tiếp với giảng viên và cộng đồng học viên.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <i class="fas fa-mobile-alt" style="font-size: 2.5rem; color: #667eea; margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 1rem; color: #333;">Đa Nền Tảng</h4>
                    <p style="color: #666;">Truy cập mọi lúc, mọi nơi trên các thiết bị khác nhau.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== STATS SECTION ========== -->
<section style="padding: 80px 0; background: white;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="zoom-in">
                <div style="text-align: center; padding: 2rem; border-radius: 15px; background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
                    <h3 style="color: #667eea; font-size: 2.5rem; font-weight: 700; margin: 0;">1000+</h3>
                    <p style="color: #666; margin-top: 0.5rem;">Học viên</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
                <div style="text-align: center; padding: 2rem; border-radius: 15px; background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
                    <h3 style="color: #667eea; font-size: 2.5rem; font-weight: 700; margin: 0;">50+</h3>
                    <p style="color: #666; margin-top: 0.5rem;">Giảng viên</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
                <div style="text-align: center; padding: 2rem; border-radius: 15px; background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
                    <h3 style="color: #667eea; font-size: 2.5rem; font-weight: 700; margin: 0;">100+</h3>
                    <p style="color: #666; margin-top: 0.5rem;">Khóa học</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="300">
                <div style="text-align: center; padding: 2rem; border-radius: 15px; background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
                    <h3 style="color: #667eea; font-size: 2.5rem; font-weight: 700; margin: 0;">95%</h3>
                    <p style="color: #666; margin-top: 0.5rem;">Sự hài lòng</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== INSTRUCTORS SECTION ========== -->
<section style="padding: 80px 0; background: #f8f9fa;" id="instructors">
    <div class="container">
        <h2 style="font-size: 2.5rem; font-weight: 700; text-align: center; margin-bottom: 1rem; color: #333;">Giảng Viên Nổi Bật</h2>
        <p style="text-align: center; color: #666; margin-bottom: 3rem; font-size: 1.1rem;">Gặp gỡ những chuyên gia giàu kinh nghiệm trong lĩnh vực của họ</p>
        
        <div class="row g-4">
            @if(isset($giangVienFeatured) && $giangVienFeatured->count() > 0)
                @foreach($giangVienFeatured as $gv)
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                        <div style="width: 100%; height: 250px; object-fit: cover; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div style="padding: 1.5rem;">
                            <h4 style="color: #333; margin-bottom: 0.5rem;">{{ $gv->ten_giang_vien ?? 'N/A' }}</h4>
                            <p style="color: #667eea; font-size: 0.9rem; margin-bottom: 0.5rem;">{{ $gv->chuyen_khoa ?? 'Chuyên gia' }}</p>
                            <p style="color: #666; font-size: 0.85rem; margin-bottom: 1rem;">{{ $gv->hoc_van ?? 'Bằng cấp' }}</p>
                            <a href="#" class="btn" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">
                                <i class="fas fa-phone me-1"></i> Liên hệ
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-12 text-center py-5">
                    <p style="color: #666;">Chưa có giảng viên nổi bật</p>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- ========== CTA SECTION ========== -->
<section style="padding: 60px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Sẵn sàng bắt đầu học tập?</h2>
                <p style="margin-bottom: 0;">Đăng ký ngay để trải nghiệm hệ thống học tập thông minh</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('dang-ky') }}" class="btn" style="background: white; color: #667eea; padding: 0.75rem 2.5rem; border-radius: 25px; font-weight: 600; border: none; cursor: pointer; text-decoration: none;">
                    <i class="fas fa-rocket me-2"></i> Đăng ký ngay
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer id="footer" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 60px 0 20px; margin-top: 80px;">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 style="font-weight: 600; margin-bottom: 1.5rem; color: white;">
                    <i class="fas fa-graduation-cap me-2"></i> EduClick
                </h5>
                <p style="opacity: 0.85;">Hệ thống quản lý khóa học hiện đại, kết nối học viên và giảng viên chuyên môn. Nâng cao chất lượng giáo dục qua công nghệ.</p>
                <div style="display: flex; gap: 1rem;">
                    @if(isset($settings['facebook']))
                        <a href="{{ $settings['facebook'] }}" target="_blank" style="width: 40px; height: 40px; border-radius: 50%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if(isset($settings['zalo']))
                        <a href="{{ $settings['zalo'] }}" target="_blank" style="width: 40px; height: 40px; border-radius: 50%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i class="fas fa-comments"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="col-md-4 mb-4 mb-md-0">
                <h5 style="font-weight: 600; margin-bottom: 1.5rem; color: white;">Liên Kết Nhanh</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.75rem;">
                        <a href="{{ route('home') }}" style="color: rgba(255,255,255,0.75); text-decoration: none;">Trang chủ</a>
                    </li>
                    <li style="margin-bottom: 0.75rem;">
                        <a href="#features" style="color: rgba(255,255,255,0.75); text-decoration: none;">Tính năng</a>
                    </li>
                    <li style="margin-bottom: 0.75rem;">
                        <a href="#instructors" style="color: rgba(255,255,255,0.75); text-decoration: none;">Giảng viên</a>
                    </li>
                    <li style="margin-bottom: 0.75rem;">
                        <a href="{{ route('dang-ky') }}" style="color: rgba(255,255,255,0.75); text-decoration: none;">Đăng ký</a>
                    </li>
                    <li style="margin-bottom: 0.75rem;">
                        <a href="{{ route('dang-nhap') }}" style="color: rgba(255,255,255,0.75); text-decoration: none;">Đăng nhập</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-4">
                <h5 style="font-weight: 600; margin-bottom: 1.5rem; color: white;">Liên Hệ Với Chúng Tôi</h5>
                @if(isset($settings['hotline']))
                <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-phone" style="color: #667eea; font-size: 1.25rem;"></i>
                    <div>
                        <div style="font-size: 0.85rem; opacity: 0.75;">Hotline</div>
                        <strong>{{ $settings['hotline'] }}</strong>
                    </div>
                </div>
                @endif
                @if(isset($settings['email']))
                <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-envelope" style="color: #667eea; font-size: 1.25rem;"></i>
                    <div>
                        <div style="font-size: 0.85rem; opacity: 0.75;">Email</div>
                        <strong>{{ $settings['email'] }}</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem; margin-top: 2rem; text-align: center; color: rgba(255,255,255,0.75);">
            <p>&copy; 2026 EduClick. Bảo lưu mọi quyền.</p>
        </div>
    </div>
</footer>

@push('scripts')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
</script>
@endpush

@endsection
