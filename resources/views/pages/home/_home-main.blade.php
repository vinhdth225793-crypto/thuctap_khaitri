<section class="home-hub" id="home" style="--hub-image: url('{{ $imageUrl($heroImage) }}');">
    <div class="home-container hub-inner">
        <div class="hub-spotlight">
            <div class="hub-copy">
                <div class="hub-badge-row">
                    <span class="eyebrow">Hệ thống đào tạo toàn diện</span>
                    <span class="hub-order-badge"><i class="fas fa-shield-halved"></i> Dữ liệu đồng nhất</span>
                </div>
                <h1>{{ $heroTitle }}</h1>
                <p>{{ $heroDescription }}</p>

                <div class="hub-capabilities-grid">
                    <div class="cap-item">
                        <div class="cap-icon"><i class="fas fa-layer-group"></i></div>
                        <div class="cap-text">
                            <strong>Thư viện Khóa học</strong>
                            <span>Dễ dàng tìm kiếm khóa phù hợp theo cấp độ, danh mục và lộ trình.</span>
                        </div>
                    </div>
                    <div class="cap-item">
                        <div class="cap-icon"><i class="fas fa-video"></i></div>
                        <div class="cap-text">
                            <strong>Lớp học Trực tuyến</strong>
                            <span>Tham gia phòng học Live, truy cập tài nguyên và bài giảng tức thì.</span>
                        </div>
                    </div>
                    <div class="cap-item">
                        <div class="cap-icon"><i class="fas fa-chart-pie"></i></div>
                        <div class="cap-text">
                            <strong>Quản lý Kết quả</strong>
                            <span>Theo dõi điểm danh, tiến độ học tập và thực hiện bài kiểm tra Online.</span>
                        </div>
                    </div>
                </div>

                <div class="hub-actions">
                    <a href="#courses" class="btn-main">Khám phá khóa học <i class="fas fa-arrow-right"></i></a>
                    <a href="#contact" class="btn-light-outline">Tư vấn ngay</a>
                </div>
            </div>

            <div class="hub-panel">
                <div class="hub-panel-header">
                    <i class="fas fa-magnifying-glass"></i>
                    <span>Bắt đầu tìm hiểu</span>
                </div>

                <form method="GET" action="{{ route('home') }}#courses" class="hub-search-box">
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tên khóa học, mã khóa...">
                    <button type="submit">Tìm nhanh</button>
                </form>

                <div class="hub-quick-stats">
                    <div class="stat-box">
                        <strong>{{ number_format($stats['tong_khoa_hoc']) }}</strong>
                        <span>Khóa học</span>
                    </div>
                    <div class="stat-box">
                        <strong>{{ number_format($stats['tong_module']) }}</strong>
                        <span>Module</span>
                    </div>
                    <div class="stat-box">
                        <strong>{{ number_format($stats['tong_hoc_vien']) }}</strong>
                        <span>Học viên</span>
                    </div>
                </div>

                @if($featuredCourse)
                    <div class="hub-highlight-card">
                        <span class="card-label">Khóa học tiêu biểu</span>
                        <div class="card-content">
                            <img src="{{ $imageUrl($featuredCourse->hinh_anh) }}" alt="{{ $featuredCourse->ten_khoa_hoc }}">
                            <div>
                                <h3>{{ $featuredCourse->ten_khoa_hoc }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($featuredCourse->mo_ta_ngan ?: 'Lộ trình đào tạo chuẩn quốc tế.', 60) }}</p>
                            </div>
                        </div>
                        <a href="#courses" class="card-link">Xem chi tiết <i class="fas fa-chevron-right"></i></a>
                    </div>
                @endif
            </div>
        </div>
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
                            <a href="{{ $courseAreaRoute }}" class="btn-main">{{ $courseAreaLabel }}</a>
                            @guest
                                <a href="{{ route('dang-nhap') }}" class="btn-soft">Đăng nhập</a>
                            @else
                                <a href="{{ $dashboardRoute }}" class="btn-soft">Theo dõi</a>
                            @endguest
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
            <span class="eyebrow">Lộ trình</span>
            <h2>Học viên nắm được việc cần làm ở từng bước</h2>
        </div>

        <div class="flow-grid">
            <div class="flow-item">
                <span>1</span>
                <h3>Chọn khóa phù hợp</h3>
                <p>Xem cấp độ, số module, trạng thái và ngày khai giảng ngay trên danh sách khóa.</p>
            </div>
            <div class="flow-item">
                <span>2</span>
                <h3>Gửi yêu cầu tham gia</h3>
                <p>Tài khoản học viên có thể gửi yêu cầu tham gia khóa đang mở để được xác nhận.</p>
            </div>
            <div class="flow-item">
                <span>3</span>
                <h3>Theo dõi tiến độ</h3>
                <p>Sau khi vào học, học viên theo dõi lịch, tài nguyên, điểm danh và bài kiểm tra.</p>
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
