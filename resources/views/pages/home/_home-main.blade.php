<section class="home-hub" id="home" style="--hub-image: url('{{ $imageUrl($heroImage) }}');">
    {{-- Decorative background: floating blobs + sparkle dots --}}
    <div class="hub-decor" aria-hidden="true">
        <span class="hub-blob hub-blob-1"></span>
        <span class="hub-blob hub-blob-2"></span>
        <span class="hub-blob hub-blob-3"></span>
        <span class="hub-sparkle hub-sparkle-1"></span>
        <span class="hub-sparkle hub-sparkle-2"></span>
        <span class="hub-sparkle hub-sparkle-3"></span>
        <span class="hub-sparkle hub-sparkle-4"></span>
    </div>

    <div class="home-container hub-inner">
        <div class="hub-spotlight">
            <div class="hub-copy">
                <div class="hub-badge-row">
                    <span class="eyebrow eyebrow-shine">
                        <span class="eyebrow-dot"></span>
                        Nền tảng đào tạo trực tuyến
                    </span>
                    <span class="hub-order-badge"><i class="fas fa-circle-check"></i> Đã được kiểm chứng</span>
                </div>
                <h1>{{ $heroTitle }}</h1>
                <p>{{ $heroDescription }}</p>

                {{-- Trust bar: rating + learners + verified --}}
                <div class="hub-trust-bar">
                    <div class="trust-item">
                        <div class="trust-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <span><strong>4.9/5</strong> đánh giá từ học viên</span>
                    </div>
                    <span class="trust-divider"></span>
                    <div class="trust-item">
                        <div class="trust-avatars">
                            @foreach($featuredInstructors->take(4) as $iv)
                                @php
                                    $iAvatar = $avatarUrl($iv->avatar_url ?: optional($iv->nguoiDung)->anh_dai_dien);
                                    $iInit = mb_substr($iv->nguoiDung->ho_ten ?? 'GV', 0, 1);
                                @endphp
                                @if($iAvatar)
                                    <img src="{{ $iAvatar }}" alt="">
                                @else
                                    <span class="trust-avatar-fallback">{{ $iInit }}</span>
                                @endif
                            @endforeach
                        </div>
                        <span><strong>{{ number_format($stats['tong_hoc_vien']) }}+</strong> học viên đang theo học</span>
                    </div>
                    <span class="trust-divider"></span>
                    <div class="trust-item">
                        <div class="trust-shield"><i class="fas fa-shield-halved"></i></div>
                        <span><strong>Cam kết</strong> chất lượng & bảo mật</span>
                    </div>
                </div>

                <div class="hub-capabilities-grid">
                    <div class="cap-item">
                        <div class="cap-icon"><i class="fas fa-layer-group"></i></div>
                        <div class="cap-text">
                            <strong>Thư viện khóa học đa dạng</strong>
                            <span>Lọc nhanh theo cấp độ, danh mục và lịch khai giảng phù hợp.</span>
                        </div>
                    </div>
                    <div class="cap-item">
                        <div class="cap-icon"><i class="fas fa-video"></i></div>
                        <div class="cap-text">
                            <strong>Học trực tuyến tương tác</strong>
                            <span>Phòng học live, tài nguyên buổi học và bài giảng có sẵn mọi lúc.</span>
                        </div>
                    </div>
                    <div class="cap-item">
                        <div class="cap-icon"><i class="fas fa-chart-pie"></i></div>
                        <div class="cap-text">
                            <strong>Theo dõi tiến độ minh bạch</strong>
                            <span>Điểm danh, kết quả kiểm tra và phản hồi của giảng viên rõ ràng từng module.</span>
                        </div>
                    </div>
                </div>

                <div class="hub-actions">
                    <a href="#courses" class="btn-main">Khám phá khóa học <i class="fas fa-arrow-right"></i></a>
                    <a href="#contact" class="btn-light-outline"><i class="fas fa-headset"></i> Đăng ký tư vấn</a>
                    <a href="#about" class="btn-ghost-light"><i class="fas fa-circle-play"></i> Giới thiệu hệ thống</a>
                </div>
            </div>

            <div class="hub-panel">
                <div class="hub-panel-header">
                    <i class="fas fa-bolt"></i>
                    <span>Bắt đầu tìm hiểu nhanh</span>
                    <span class="panel-live-dot" aria-label="đang hoạt động"></span>
                </div>

                <form method="GET" action="{{ route('home') }}#courses" class="hub-search-box">
                    <i class="fas fa-magnifying-glass hub-search-icon"></i>
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tên khóa học, mã khóa...">
                    <button type="submit">Tìm <i class="fas fa-arrow-right"></i></button>
                </form>

                {{-- Strip uy tín (thay cho floating deco cards) --}}
                <div class="panel-trust-row">
                    <div class="panel-trust-item">
                        <span class="ptr-icon ptr-icon-warm"><i class="fas fa-trophy"></i></span>
                        <span class="ptr-copy">
                            <strong>Top 1</strong>
                            <small>khóa được chọn nhiều</small>
                        </span>
                    </div>
                    <div class="panel-trust-item">
                        <span class="ptr-icon ptr-icon-success"><i class="fas fa-graduation-cap"></i></span>
                        <span class="ptr-copy">
                            <strong>{{ number_format($stats['tong_giang_vien_noi_bat']) }}+ GV</strong>
                            <small>được kiểm chứng</small>
                        </span>
                    </div>
                </div>

                {{-- Trending tags --}}
                <div class="hub-trending">
                    <span class="hub-trending-label"><i class="fas fa-fire"></i> Tag hot:</span>
                    <div class="hub-trending-list">
                        <a href="{{ route('home', ['level' => 'co_ban']) }}#courses" class="hub-tag">Cơ bản</a>
                        <a href="{{ route('home', ['level' => 'nang_cao']) }}#courses" class="hub-tag">Nâng cao</a>
                        @foreach($categories->take(3) as $cat)
                            <a href="{{ route('home', ['category' => $cat->id]) }}#courses" class="hub-tag">{{ $cat->ten_nhom_nganh }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="hub-quick-stats">
                    <div class="stat-box">
                        <strong data-counter="{{ $stats['tong_khoa_hoc'] }}">0</strong>
                        <span>Khóa học</span>
                    </div>
                    <div class="stat-box">
                        <strong data-counter="{{ $stats['tong_module'] }}">0</strong>
                        <span>Module</span>
                    </div>
                    <div class="stat-box">
                        <strong data-counter="{{ $stats['tong_hoc_vien'] }}">0</strong>
                        <span>Học viên</span>
                    </div>
                </div>

                @if($featuredCourse)
                    <a href="#courses" class="hub-highlight-card hub-highlight-link">
                        <span class="card-label">
                            <span class="card-label-flame">🔥</span>
                            Khóa học tiêu biểu
                        </span>
                        <div class="card-content">
                            <div class="card-thumb">
                                <img src="{{ $imageUrl($featuredCourse->hinh_anh) }}" alt="{{ $featuredCourse->ten_khoa_hoc }}">
                                <span class="card-hot-badge">HOT</span>
                            </div>
                            <div>
                                <h3>{{ $featuredCourse->ten_khoa_hoc }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($featuredCourse->mo_ta_ngan ?: 'Lộ trình đào tạo chuẩn quốc tế.', 60) }}</p>
                                <div class="card-mini-stats">
                                    <span><i class="fas fa-cubes"></i> {{ $featuredCourse->module_hocs_count ?? 0 }} module</span>
                                    <span><i class="fas fa-users"></i> {{ $featuredCourse->hoc_vien_dang_hoc_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="card-link">Xem chi tiết <i class="fas fa-arrow-right"></i></span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Wave divider --}}
    <div class="hub-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C240,80 480,0 720,30 C960,60 1200,80 1440,40 L1440,80 L0,80 Z" fill="var(--soft)"/>
        </svg>
    </div>
</section>

@if($sliderBanners->isNotEmpty())
    <section class="banner-slider-section" aria-label="Banner nổi bật">
        <div class="home-container">
            <div class="banner-slider-card" data-banner-slider>
                <div class="banner-slider-track">
                    @foreach($sliderBanners as $banner)
                        <article class="banner-slide {{ $loop->first ? 'is-active' : '' }}" data-banner-slide>
                            <img src="{{ $imageUrl($banner->duong_dan_anh) }}" alt="{{ $banner->tieu_de }}">
                            <div class="banner-slide-copy">
                                <span>Banner {{ $banner->thu_tu }}</span>
                                <h2>{{ $banner->tieu_de }}</h2>
                                @if(filled($banner->mo_ta))
                                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($banner->mo_ta), 150) }}</p>
                                @endif
                                @if($banner->link)
                                    <a href="{{ $banner->link }}" class="btn-main" target="_blank" rel="noopener">Tìm hiểu thêm</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($sliderBanners->count() > 1)
                    <button type="button" class="banner-slider-control prev" data-banner-prev aria-label="Banner trước">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="banner-slider-control next" data-banner-next aria-label="Banner tiếp theo">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="banner-slider-dots" aria-label="Chọn banner">
                        @foreach($sliderBanners as $banner)
                            <button type="button" class="{{ $loop->first ? 'is-active' : '' }}" data-banner-dot="{{ $loop->index }}" aria-label="Xem banner {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif

@auth
    <div class="home-container role-panel-wrap">
        @switch($homeUser->vai_tro)
            @case('admin')
                @include('pages.home.partials.admin-quick-view', ['dashboardData' => $dashboardData])
                @break
            @case('giang_vien')
                @include('pages.home.partials.teacher-quick-view', ['dashboardData' => $dashboardData])
                @break
            @default
                @include('pages.home.partials.student-quick-view', ['dashboardData' => $dashboardData])
        @endswitch
    </div>
@else
    <section class="guest-paths">
        <div class="home-container path-grid">
            <a href="#courses" class="path-item">
                <i class="fas fa-search"></i>
                <strong>Tìm khóa học</strong>
                <span>Lọc theo cấp độ, danh mục và lịch khai giảng.</span>
            </a>
            <a href="{{ route('dang-ky') }}" class="path-item">
                <i class="fas fa-user-plus"></i>
                <strong>Tạo tài khoản</strong>
                <span>Gửi đăng ký để trung tâm duyệt và hỗ trợ ghi danh.</span>
            </a>
            <a href="#contact" class="path-item">
                <i class="fas fa-headset"></i>
                <strong>Cần tư vấn</strong>
                <span>Gọi hotline, gửi email hoặc mở kênh mạng xã hội.</span>
            </a>
        </div>
    </section>

    {{-- ========== Về tổ chức ========== --}}
    <section class="about-section" id="about">
        <div class="home-container">
            <div class="about-grid">
                <div class="about-visual">
                    <img src="{{ $imageUrl($featuredCourse?->hinh_anh ?: 'images/khoa-hoc/1773463343_b1-vstep.jpg') }}" alt="Học viên {{ $siteName }}">
                    <div class="floating-card fc-top">
                        <div class="fc-icon is-brand"><i class="fas fa-users"></i></div>
                        <div>
                            <strong>{{ number_format($stats['tong_hoc_vien']) }}+ học viên</strong>
                            <span>đang theo học</span>
                        </div>
                    </div>
                    <div class="floating-card fc-bottom">
                        <div class="fc-icon is-success"><i class="fas fa-award"></i></div>
                        <div>
                            <strong>Chất lượng đảm bảo</strong>
                            <span>cam kết đầu ra</span>
                        </div>
                    </div>
                </div>

                <div class="about-content">
                    <span class="eyebrow">Về {{ $siteName }}</span>
                    <h2>Đào tạo bài bản, đồng hành lâu dài cùng học viên</h2>
                    <p>
                        {{ $siteName }} là nền tảng đào tạo trực tuyến tích hợp toàn bộ quy trình học tập từ
                        <strong>chọn khóa học</strong>, <strong>theo dõi lịch giảng</strong>, đến
                        <strong>kết quả học tập</strong>. Hệ thống được xây dựng phù hợp cho mọi lứa tuổi —
                        từ học sinh THCS đến người đi làm muốn nâng cao kỹ năng.
                    </p>

                    <div class="about-pillars">
                        <div class="about-pillar">
                            <div class="pillar-icon tone-brand"><i class="fas fa-graduation-cap"></i></div>
                            <div>
                                <strong>Lộ trình rõ ràng</strong>
                                <span>Mỗi khóa chia thành module, có buổi học cụ thể và mục tiêu đầu ra minh bạch.</span>
                            </div>
                        </div>
                        <div class="about-pillar">
                            <div class="pillar-icon tone-info"><i class="fas fa-chalkboard-user"></i></div>
                            <div>
                                <strong>Giảng viên tận tâm</strong>
                                <span>Đội ngũ có học vị, được phân công đúng chuyên ngành và đồng hành sát sao.</span>
                            </div>
                        </div>
                        <div class="about-pillar">
                            <div class="pillar-icon tone-warn"><i class="fas fa-shield-halved"></i></div>
                            <div>
                                <strong>Thi cử công bằng</strong>
                                <span>Bài kiểm tra online có giám sát chặt chẽ, kết quả được phê duyệt nhiều bước.</span>
                            </div>
                        </div>
                        <div class="about-pillar">
                            <div class="pillar-icon tone-success"><i class="fas fa-handshake"></i></div>
                            <div>
                                <strong>Hỗ trợ nhanh chóng</strong>
                                <span>Hotline, Zalo, email — phản hồi trong giờ hành chính, giải đáp mọi vướng mắc.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== Stats highlight ========== --}}
    <section class="stats-highlight">
        <div class="home-container">
            <div class="stats-highlight-grid">
                <div class="stat-highlight-item">
                    <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                    <strong>{{ number_format($stats['tong_khoa_hoc']) }}</strong>
                    <span>Khóa học đang mở</span>
                </div>
                <div class="stat-highlight-item">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <strong>{{ number_format($stats['tong_hoc_vien']) }}</strong>
                    <span>Học viên đang theo học</span>
                </div>
                <div class="stat-highlight-item">
                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <strong>{{ number_format($stats['tong_giang_vien_noi_bat']) }}</strong>
                    <span>Giảng viên nổi bật</span>
                </div>
                <div class="stat-highlight-item">
                    <div class="stat-icon"><i class="fas fa-cubes"></i></div>
                    <strong>{{ number_format($stats['tong_module']) }}</strong>
                    <span>Module bài giảng</span>
                </div>
            </div>
        </div>
    </section>
@endauth

<section class="courses-section" id="courses">
    <div class="home-container">
        <div class="section-heading split">
            <div>
                <span class="eyebrow">Khóa học</span>
                <h2>Khóa học công khai để học viên dễ lựa chọn</h2>
                <p>Mỗi khóa thể hiện trạng thái vận hành, số module, cấp độ và ngày khai giảng nếu đã có lịch.</p>
            </div>
            <a href="{{ route('home') }}#courses" class="btn-soft">Xóa bộ lọc</a>
        </div>

        <form method="GET" action="{{ route('home') }}#courses" class="course-filter">
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo tên khóa, mã khóa hoặc mô tả">
            <select name="category">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $item)
                    <option value="{{ $item->id }}" @selected((string) $filters['category'] === (string) $item->id)>{{ $item->ten_nhom_nganh }}</option>
                @endforeach
            </select>
            <select name="level">
                <option value="">Tất cả cấp độ</option>
                <option value="co_ban" @selected($filters['level'] === 'co_ban')>Cơ bản</option>
                <option value="trung_binh" @selected($filters['level'] === 'trung_binh')>Trung bình</option>
                <option value="nang_cao" @selected($filters['level'] === 'nang_cao')>Nâng cao</option>
            </select>
            <button type="submit">Lọc khóa</button>
        </form>

        <div class="course-grid">
            @forelse($courses as $course)
                @php
                    $levelInfo = $levelLabels[$course->cap_do] ?? ['label' => 'Tổng hợp', 'class' => 'tone-info'];
                    $statusInfo = $statusLabels[$course->trang_thai_van_hanh] ?? ['label' => 'Đang cập nhật', 'class' => 'tone-info'];
                @endphp
                <article class="course-card">
                    <img src="{{ $imageUrl($course->hinh_anh) }}" alt="{{ $course->ten_khoa_hoc }}">
                    <div class="course-body">
                        <div class="course-tags">
                            <span class="{{ $levelInfo['class'] }}">{{ $levelInfo['label'] }}</span>
                            <span class="{{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                        </div>
                        <p class="course-category">{{ $course->nhomNganh->ten_nhom_nganh ?? 'Đa lĩnh vực' }}</p>
                        <h3>{{ $course->ten_khoa_hoc }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($course->mo_ta_ngan ?: 'Khóa học đang được trung tâm cập nhật lộ trình và nội dung chi tiết.', 120) }}</p>
                        <dl class="course-facts">
                            <div><dt>Module</dt><dd>{{ $course->module_hocs_count ?? 0 }}</dd></div>
                            <div><dt>Học viên</dt><dd>{{ $course->hoc_vien_dang_hoc_count ?? 0 }}</dd></div>
                            <div><dt>Khai giảng</dt><dd>{{ $course->ngay_khai_giang ? $course->ngay_khai_giang->format('d/m/Y') : 'Linh hoạt' }}</dd></div>
                        </dl>
                        <div class="course-actions">
                            @if(isset($course->is_enrolled) && $course->is_enrolled)
                                <a href="javascript:void(0)" class="btn-main" style="background-color: #28a745; border-color: #28a745; cursor: default;">Đã tham gia</a>
                                <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $course->id) }}" class="btn-soft" style="color: #dc3545; border-color: #dc3545;">Vào lớp</a>
                            @else
                                <a href="{{ $courseAreaRoute }}" class="btn-main">{{ $courseAreaLabel }}</a>
                                @guest
                                    <a href="{{ route('dang-nhap') }}" class="btn-soft">Đăng nhập</a>
                                @else
                                    <a href="{{ $dashboardRoute }}" class="btn-soft">Theo dõi</a>
                                @endguest
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-panel wide">
                    <h3>Chưa có khóa học phù hợp</h3>
                    <p>Hãy thử đổi từ khóa, cấp độ hoặc danh mục để xem thêm khóa học đang mở.</p>
                </div>
            @endforelse
        </div>

        @if($courses->hasPages())
            <div class="pagination-wrap">
                {{ $courses->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</section>

<section class="learning-flow">
    <div class="home-container">
        <div class="section-heading">
            <span class="eyebrow">Quy trình tham gia</span>
            <h2>Bốn bước đơn giản để bắt đầu hành trình học tập</h2>
            <p>Quy trình minh bạch — từ lúc bạn quan tâm đến khi nhận kết quả cuối khóa.</p>
        </div>

        <div class="flow-grid">
            <div class="flow-item">
                <span>1</span>
                <h3>Chọn khóa phù hợp</h3>
                <p>Xem cấp độ, số module, trạng thái khai giảng và ngày bắt đầu để chọn khóa đúng nhu cầu.</p>
            </div>
            <div class="flow-item">
                <span>2</span>
                <h3>Đăng ký tài khoản</h3>
                <p>Tạo tài khoản học viên trong vài phút, gửi yêu cầu tham gia khóa đã chọn để được duyệt.</p>
            </div>
            <div class="flow-item">
                <span>3</span>
                <h3>Học và thực hành</h3>
                <p>Tham gia buổi học live, xem lại bài giảng, làm bài kiểm tra và trao đổi cùng giảng viên.</p>
            </div>
            <div class="flow-item">
                <span>4</span>
                <h3>Nhận kết quả</h3>
                <p>Theo dõi điểm từng module, kết quả tổng kết được giảng viên chốt và admin phê duyệt minh bạch.</p>
            </div>
        </div>
    </div>
</section>

@if($banners->isNotEmpty())
    <section class="updates-section" id="updates">
        <div class="home-container">
            <div class="section-heading split">
                <div>
                    <span class="eyebrow">Thông tin mới</span>
                    <h2>Thông báo và nội dung nổi bật từ trung tâm</h2>
                </div>
                @if($sliderHighlight?->link)
                    <a href="{{ $sliderHighlight->link }}" class="btn-soft" target="_blank" rel="noopener">Xem thông tin chính</a>
                @endif
            </div>

            <div class="updates-grid">
                @foreach($banners as $banner)
                    <article class="update-card {{ $loop->first ? 'featured' : '' }}">
                        <img src="{{ $imageUrl($banner->duong_dan_anh) }}" alt="{{ $banner->tieu_de }}">
                        <div class="update-body">
                            <span>{{ $loop->first ? 'Nổi bật' : 'Cập nhật' }}</span>
                            <h3>{{ $banner->tieu_de }}</h3>
                            @if(filled($banner->mo_ta))
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($banner->mo_ta), $loop->first ? 180 : 90) }}</p>
                            @endif
                            @if($banner->link)
                                <a href="{{ $banner->link }}" target="_blank" rel="noopener">Tìm hiểu thêm <i class="fas fa-arrow-right"></i></a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="categories-section">
    <div class="home-container">
        <div class="section-heading">
            <span class="eyebrow">Danh mục</span>
            <h2>Lĩnh vực học đang có khóa mở</h2>
            <p>Chọn một nhóm ngành để xem nhanh những khóa phù hợp với mục tiêu học tập.</p>
        </div>

        <div class="category-list">
            @forelse($categories as $item)
                <a href="{{ route('home', ['category' => $item->id]) }}#courses" class="category-chip {{ (string) $filters['category'] === (string) $item->id ? 'active' : '' }}">
                    <span>{{ $item->ten_nhom_nganh }}</span>
                    <strong>{{ $item->public_course_count }} khóa</strong>
                </a>
            @empty
                <div class="empty-panel">Danh mục khóa học đang được cập nhật.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="instructors-section" id="instructors">
    <div class="home-container">
        <div class="section-heading split">
            <div>
                <span class="eyebrow">Giảng viên</span>
                <h2>Đội ngũ được admin chọn hiển thị trên trang chủ</h2>
                <p>Thông tin chuyên ngành, học vị và kinh nghiệm được lấy từ hồ sơ giảng viên.</p>
            </div>
        </div>

        <div class="instructor-grid">
            @forelse($featuredInstructors as $giangVien)
                @php
                    $teacherName = $giangVien->nguoiDung->ho_ten ?? 'Giảng viên';
                    $teacherPhoto = $avatarUrl($giangVien->avatar_url ?: optional($giangVien->nguoiDung)->anh_dai_dien);
                @endphp
                <article class="instructor-card">
                    @if($teacherPhoto)
                        <img src="{{ $teacherPhoto }}" alt="{{ $teacherName }}">
                    @else
                        <div class="avatar-fallback">{{ mb_substr($teacherName, 0, 1) }}</div>
                    @endif
                    <div>
                        <h3>{{ $teacherName }}</h3>
                        <p class="teacher-meta">{{ $giangVien->hoc_vi ?: 'Giảng viên' }} - {{ $giangVien->chuyen_nganh ?: 'Chuyên gia đào tạo' }}</p>
                        <p>{{ \Illuminate\Support\Str::limit($giangVien->mo_ta_ngan ?: 'Đồng hành cùng học viên trong quá trình học và thực hành.', 110) }}</p>
                        <strong>{{ number_format((int) $giangVien->so_gio_day) }} giờ giảng dạy</strong>
                    </div>
                </article>
            @empty
                <div class="empty-panel wide">Danh sách giảng viên nổi bật đang được cập nhật.</div>
            @endforelse
        </div>
    </div>
</section>

@guest
{{-- ========== FAQ ========== --}}
<section class="faq-section" id="faq">
    <div class="home-container">
        <div class="section-heading">
            <span class="eyebrow">Câu hỏi thường gặp</span>
            <h2>Những điều học viên hay thắc mắc</h2>
            <p>Nếu chưa thấy câu trả lời mong muốn, bạn có thể gửi câu hỏi qua mục liên hệ phía dưới.</p>
        </div>

        <div class="faq-list">
            <details class="faq-item" open>
                <summary>Tôi cần có nền tảng gì trước khi đăng ký khóa học?</summary>
                <div class="faq-body">
                    Mỗi khóa học đều ghi rõ <strong>cấp độ</strong> (Cơ bản / Trung bình / Nâng cao) và mô tả nội dung.
                    Bạn chỉ cần đọc phần mô tả chi tiết, hoặc liên hệ tư vấn để được giảng viên hướng dẫn chọn khóa
                    phù hợp với trình độ hiện tại.
                </div>
            </details>

            <details class="faq-item">
                <summary>Tôi học hoàn toàn online hay phải đến trung tâm?</summary>
                <div class="faq-body">
                    Phần lớn khóa học của {{ $siteName }} được tổ chức theo hình thức <strong>online</strong>
                    qua phòng học live, kèm tài nguyên và bài giảng có thể xem lại. Một số khóa có thể yêu cầu
                    buổi gặp trực tiếp — thông tin sẽ được hiển thị rõ trong lịch học.
                </div>
            </details>

            <details class="faq-item">
                <summary>Quy trình kiểm tra và đánh giá kết quả như thế nào?</summary>
                <div class="faq-body">
                    Học viên làm bài kiểm tra online theo từng module hoặc cuối khóa. Hệ thống có
                    <strong>giám sát thi qua camera</strong>, chống gian lận và lưu lại nhật ký. Điểm số sẽ được
                    giảng viên chấm, sau đó qua phiếu xét duyệt của admin trước khi chính thức chốt.
                </div>
            </details>

            <details class="faq-item">
                <summary>Tài khoản giảng viên đăng ký có gì khác học viên?</summary>
                <div class="faq-body">
                    Tài khoản học viên được kích hoạt ngay sau khi đăng ký. Tài khoản giảng viên cần
                    <strong>admin phê duyệt</strong> trước khi sử dụng — bạn sẽ nhận thông báo qua email khi
                    yêu cầu được duyệt.
                </div>
            </details>

            <details class="faq-item">
                <summary>Tôi có thể đổi lịch học hoặc xin nghỉ buổi học không?</summary>
                <div class="faq-body">
                    Có. Học viên có thể xem lịch học, gửi yêu cầu đổi/bổ sung học viên trong khóa.
                    Giảng viên có chức năng gửi <strong>đơn xin nghỉ giảng</strong> để admin xét duyệt và
                    sắp xếp lịch thay thế phù hợp.
                </div>
            </details>

            <details class="faq-item">
                <summary>Học phí và cách thanh toán?</summary>
                <div class="faq-body">
                    Học phí được niêm yết trong từng khóa khi liên hệ tư vấn. {{ $siteName }} hỗ trợ nhiều
                    phương thức thanh toán phổ biến. Vui lòng gọi hotline hoặc nhắn Zalo để được báo giá chi tiết.
                </div>
            </details>
        </div>
    </div>
</section>
@endguest

<section class="contact-section" id="contact">
    <div class="home-container contact-grid">
        <div class="contact-info-panel">
            <div class="contact-copy">
                <span class="eyebrow">Liên hệ</span>
                <h2>Thông tin liên hệ chính thức từ admin</h2>
                <p>Khách mới và học viên có thể liên hệ trung tâm qua hotline, email, địa chỉ hoặc kênh mạng xã hội được cấu hình trong hệ thống.</p>
            </div>

            <div class="contact-list">
                @if(filled($settings['hotline']))
                    <a href="tel:{{ preg_replace('/\s+/', '', $settings['hotline']) }}" class="contact-row">
                        <i class="fas fa-phone"></i>
                        <span><strong>Hotline</strong>{{ $settings['hotline'] }}</span>
                    </a>
                @endif
                @if(filled($settings['email']))
                    <a href="mailto:{{ $settings['email'] }}" class="contact-row">
                        <i class="fas fa-envelope"></i>
                        <span><strong>Email</strong>{{ $settings['email'] }}</span>
                    </a>
                @endif
                @if(filled($settings['address']))
                    <div class="contact-row">
                        <i class="fas fa-location-dot"></i>
                        <span><strong>Địa chỉ</strong>{!! $settings['address'] !!}</span>
                    </div>
                @endif
                <div class="social-row">
                    @if(filled($settings['facebook']))
                        <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i> Facebook</a>
                    @endif
                    @if(filled($settings['zalo']))
                        <a href="{{ $settings['zalo'] }}" target="_blank" rel="noopener"><i class="fas fa-comment-dots"></i> Zalo</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="contact-register-card">
            @guest
                <div class="contact-register-head">
                    <span class="eyebrow">Đăng ký tài khoản</span>
                    <h3>Tạo tài khoản để trung tâm hỗ trợ ghi danh</h3>
                    <p>Điền đầy đủ thông tin để bắt đầu sử dụng hệ thống học tập của trung tâm.</p>
                </div>

                @if(session('success'))
                    <div class="contact-form-feedback success">
                        <i class="fas fa-circle-check"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('xu-ly-dang-ky') }}#contact" class="contact-register-form">
                    @csrf

                    <div class="contact-form-row">
                        <label class="contact-field">
                            <span>Họ và tên *</span>
                            <input type="text" name="ho_ten" value="{{ old('ho_ten') }}" placeholder="Nguyễn Văn A" required>
                            @error('ho_ten')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="contact-field">
                            <span>Email *</span>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" required>
                            @error('email')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>
                    </div>

                    <div class="contact-form-row">
                        <label class="contact-field">
                            <span>Mật khẩu *</span>
                            <input type="password" name="mat_khau" placeholder="Mật khẩu tối thiểu 8 ký tự" required>
                            @error('mat_khau')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="contact-field">
                            <span>Xác nhận mật khẩu *</span>
                            <input type="password" name="mat_khau_confirmation" placeholder="Nhập lại mật khẩu" required>
                        </label>
                    </div>

                    <div class="contact-form-row">
                        <label class="contact-field">
                            <span>Số điện thoại</span>
                            <input type="tel" name="so_dien_thoai" value="{{ old('so_dien_thoai') }}" placeholder="0912345678">
                            @error('so_dien_thoai')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="contact-field">
                            <span>Ngày sinh</span>
                            <input type="date" name="ngay_sinh" value="{{ old('ngay_sinh') }}">
                            @error('ngay_sinh')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>
                    </div>

                    <label class="contact-field">
                        <span>Địa chỉ</span>
                        <input type="text" name="dia_chi" value="{{ old('dia_chi') }}" placeholder="Số nhà, đường, thành phố">
                        @error('dia_chi')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <div class="contact-role-group">
                        <span class="contact-field-label">Vai trò *</span>
                        <div class="contact-role-options">
                            <label class="contact-role-option" for="contact_hoc_vien">
                                <input type="radio" name="vai_tro" id="contact_hoc_vien" value="hoc_vien" @checked(old('vai_tro', 'hoc_vien') === 'hoc_vien')>
                                <span><i class="fas fa-user-graduate"></i> Học viên</span>
                            </label>

                            <label class="contact-role-option" for="contact_giang_vien">
                                <input type="radio" name="vai_tro" id="contact_giang_vien" value="giang_vien" @checked(old('vai_tro') === 'giang_vien')>
                                <span><i class="fas fa-chalkboard-teacher"></i> Giảng viên</span>
                            </label>
                        </div>
                        @error('vai_tro')
                            <small>{{ $message }}</small>
                        @enderror
                    </div>

                    <label class="contact-terms" for="contact_dong_y_dieu_khoan">
                        <input type="checkbox" id="contact_dong_y_dieu_khoan" required>
                        <span>
                            Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách bảo mật</a>.
                        </span>
                    </label>

                    <button type="submit" class="btn-main">Đăng ký tài khoản <i class="fas fa-arrow-right"></i></button>
                </form>
            @else
                <div class="contact-register-head">
                    <span class="eyebrow">Bạn đã đăng nhập</span>
                    <h3>Tiếp tục theo dõi lộ trình học</h3>
                    <p>Vào khu vực tài khoản để xem lịch học, khóa đang tham gia và các thông báo mới từ trung tâm.</p>
                </div>
                <a href="{{ $dashboardRoute }}" class="btn-main">Vào dashboard <i class="fas fa-arrow-right"></i></a>
            @endguest
        </div>
    </div>
</section>
