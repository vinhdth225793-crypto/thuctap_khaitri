@extends('layouts.app')

@section('title', $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid admin-page-x cdt-page">
    {{-- Welcome banner xanh dương --}}
    <div class="apx-welcome cdt-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="apx-welcome-text">
            <div class="cdt-tag-row">
                <span class="cdt-loai-badge"><i class="fas fa-fingerprint"></i> {{ $khoaHoc->ma_khoa_hoc }}</span>
                <span class="cdt-status-badge"><i class="fas fa-tag"></i> {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'Chưa phân nhóm' }}</span>
                <span class="badge rounded-pill {{ $ghiDanh->trang_thai_badge }} px-3 py-2">{{ $ghiDanh->trang_thai_label }}</span>
                <span class="badge rounded-pill bg-{{ $khoaHoc->trang_thai_hoc_tap_badge }} px-3 py-2">{{ $khoaHoc->trang_thai_hoc_tap_label }}</span>
            </div>
            <h4>{{ $khoaHoc->ten_khoa_hoc }}</h4>
            <p>{{ $khoaHoc->mo_ta_ngan ?: 'Theo dõi module, buổi học, tài liệu, live room và bài kiểm tra của khóa học này.' }}</p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="apx-view-toggle"><i class="fas fa-arrow-left"></i> <span>Khóa của tôi</span></a>
            <a href="{{ route('hoc-vien.hoat-dong-tien-do') }}" class="apx-view-toggle"><i class="fas fa-chart-line"></i> <span>Tiến độ</span></a>
            <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn btn-light text-primary fw-bold shadow-sm cdt-cta-btn">
                <i class="fas fa-list-check me-1"></i> Bài kiểm tra
            </a>
        </div>
    </div>

    {{-- Breadcrumb đỏ --}}
    <nav aria-label="breadcrumb" class="cdt-breadcrumb mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}">Khóa học của tôi</a></li>
            <li class="breadcrumb-item active">{{ $khoaHoc->ten_khoa_hoc }}</li>
        </ol>
    </nav>

    @include('components.alert')

    <div class="card vip-card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="section-link-bar mb-4">
                <a href="#tong-quan" class="section-link">Tổng quan</a>
                <a href="#module" class="section-link">Module</a>
                <a href="#lich-hoc" class="section-link">Buổi học</a>
                <a href="#tai-lieu" class="section-link">Tài liệu</a>
                <a href="#bai-kiem-tra" class="section-link">Bài kiểm tra</a>
                <a href="#tien-do" class="section-link">Tiến độ</a>
            </div>

            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <div class="stat-box__label">Module</div>
                        <div class="stat-box__value">{{ $stats['tong_module'] }}</div>
                        <div class="small text-muted">{{ $stats['module_hoan_thanh'] }} module đã hoàn thành</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <div class="stat-box__label">Buổi học</div>
                        <div class="stat-box__value">{{ $stats['tong_buoi_hoc'] }}</div>
                        <div class="small text-muted">{{ $stats['buoi_hoan_thanh'] }} buổi đã hoàn thành</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <div class="stat-box__label">Tài nguyên</div>
                        <div class="stat-box__value">{{ $stats['tai_nguyen_cong_khai'] }}</div>
                        <div class="small text-muted">{{ $stats['bai_giang_cong_khai'] }} bài giảng đã mở</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-box">
                        <div class="stat-box__label">Tiến độ khóa học</div>
                        <div class="stat-box__value">{{ $khoaHoc->tien_do_hoc_tap }}%</div>
                        <div class="small text-muted">{{ $stats['bai_kiem_tra_cong_khai'] }} bài kiểm tra đã phát hành</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <section id="tong-quan" class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Tổng quan khóa học</h5>
                    <p class="text-muted small mb-0">Trục điều hướng chính của học viên: khóa học -> buổi học -> bài giảng/live/test.</p>
                </div>
                <div class="card-body">
                    <div class="overview-box">
                        <div class="small text-muted mb-2">Ngày ghi danh: {{ $ghiDanh->ngay_tham_gia?->format('d/m/Y') ?: 'Chưa cập nhật' }}</div>
                        <div class="progress progress-thin mb-3">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $khoaHoc->tien_do_hoc_tap }}%"></div>
                        </div>
                        @if($buoiSapToi)
                            <div class="fw-semibold text-dark mb-1">Buổi học sắp tới</div>
                            <div class="small text-muted mb-3">
                                {{ $buoiSapToi->ngay_hoc?->format('d/m/Y') }} • {{ substr((string) $buoiSapToi->gio_bat_dau, 0, 5) ?: '--:--' }} • {{ $buoiSapToi->moduleHoc->ten_module ?? 'Chưa gán module' }}
                            </div>
                            <a href="{{ route('hoc-vien.buoi-hoc.show', $buoiSapToi->id) }}" class="btn btn-sm btn-outline-primary">
                                Vào buổi học sắp tới
                            </a>
                        @else
                            <div class="small text-muted">Khóa học chưa có buổi học nào sắp diễn ra.</div>
                        @endif
                    </div>
                </div>
            </section>

            <section id="module" class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Lộ trình module</h5>
                    <p class="text-muted small mb-0">Theo dõi tình trạng từng module và số buổi đã học trong từng phần.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($khoaHoc->moduleHocs as $module)
                            <div class="col-md-6">
                                <div class="module-card h-100">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <div class="small text-muted">{{ $module->ma_module }}</div>
                                            <h6 class="fw-semibold mb-1">{{ $module->ten_module }}</h6>
                                            <div class="small text-muted">{{ $module->so_buoi_hoan_thanh }}/{{ $module->so_buoi_hop_le }} buổi hoàn thành</div>
                                        </div>
                                        <span class="badge bg-{{ $module->trang_thai_hoc_tap_badge }}">{{ $module->trang_thai_hoc_tap_label }}</span>
                                    </div>
                                    <div class="progress progress-thin mb-2">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $module->tien_do_hoc_tap }}%"></div>
                                    </div>
                                    <div class="small text-muted">{{ $module->lichHocs->count() }} buổi đã lên lịch</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><div class="empty-state-box">Khóa học này chưa có module nào để hiển thị.</div></div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section id="lich-hoc" class="card vip-card border-0 shadow-sm mb-4">
                @php
                    $currentSchedule = $courseSchedules->first(fn ($item) => ! $item->is_ended);
                    $currentScheduleId = (int) ($currentSchedule?->id ?? 0);
                    $doneScheduleCount = $courseSchedules->filter(fn ($item) => $item->is_ended)->count();
                    $currentScheduleCount = $currentSchedule ? 1 : 0;
                    $remainingScheduleCount = max($courseSchedules->count() - $doneScheduleCount - $currentScheduleCount, 0);
                @endphp
                <div class="card-header border-0 bg-white py-3 d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1 fw-semibold">Bảng buổi học</h5>
                        <p class="text-muted small mb-0">Theo dõi buổi đã xong, buổi đang học và các buổi còn lại trong khóa.</p>
                    </div>
                    <div class="course-schedule-pill">
                        <i class="fas fa-calendar-check"></i>
                        <span>{{ $doneScheduleCount }}/{{ $courseSchedules->count() }} buổi đã xong</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="course-schedule-summary">
                        <div class="course-schedule-summary-item is-done">
                            <span>Đã xong</span>
                            <strong>{{ $doneScheduleCount }}</strong>
                        </div>
                        <div class="course-schedule-summary-item is-current">
                            <span>Đang học</span>
                            <strong>{{ $currentScheduleCount }}</strong>
                        </div>
                        <div class="course-schedule-summary-item is-upcoming">
                            <span>Còn lại</span>
                            <strong>{{ $remainingScheduleCount }}</strong>
                        </div>
                        <div class="course-schedule-summary-item is-online">
                            <span>Online</span>
                            <strong>{{ $stats['buoi_online'] }}</strong>
                        </div>
                    </div>

                    <div class="course-schedule-board">
                        @forelse($courseSchedules as $lichHoc)
                            @php
                                $isCurrentSchedule = ! $lichHoc->is_ended && (int) $lichHoc->id === $currentScheduleId;
                                $scheduleState = $lichHoc->is_ended ? 'done' : ($isCurrentSchedule ? 'current' : 'upcoming');
                                $scheduleStateLabel = match ($scheduleState) {
                                    'done' => 'Đã xong',
                                    'current' => 'Đang học',
                                    default => 'Còn lại',
                                };
                                $scheduleStateIcon = match ($scheduleState) {
                                    'done' => 'fa-check',
                                    'current' => 'fa-play',
                                    default => 'fa-clock',
                                };
                            @endphp
                            <article class="course-schedule-item is-{{ $scheduleState }}">
                                <div class="course-schedule-marker">
                                    <i class="fas {{ $scheduleStateIcon }}"></i>
                                </div>

                                <div class="course-schedule-card">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            <div class="course-schedule-eyebrow">
                                                Buổi {{ $lichHoc->buoi_so ?: $loop->iteration }}
                                                <span>•</span>
                                                {{ $lichHoc->hinh_thuc_label }}
                                            </div>
                                            <h6 class="course-schedule-title">{{ $lichHoc->moduleHoc->ten_module ?? 'Chưa gán module' }}</h6>
                                        </div>
                                        <span class="course-schedule-status course-schedule-status-{{ $scheduleState }}">{{ $scheduleStateLabel }}</span>
                                    </div>

                                    <div class="course-schedule-meta">
                                        <span>
                                            <i class="fas fa-calendar-alt"></i>
                                            {{ $lichHoc->ngay_hoc?->format('d/m/Y') ?: 'Chưa có ngày học' }}
                                        </span>
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            {{ substr((string) $lichHoc->gio_bat_dau, 0, 5) ?: '--:--' }}
                                            @if($lichHoc->gio_ket_thuc)
                                                - {{ substr((string) $lichHoc->gio_ket_thuc, 0, 5) }}
                                            @endif
                                        </span>
                                        <span>
                                            <i class="fas fa-folder-open"></i>
                                            {{ $lichHoc->taiNguyen->count() }} tài nguyên
                                        </span>
                                        <span>
                                            <i class="fas fa-chalkboard-teacher"></i>
                                            {{ $lichHoc->baiGiangs->count() }} bài giảng
                                        </span>
                                        @if($lichHoc->giangVien?->nguoiDung)
                                            <span>
                                                <i class="fas fa-user-tie"></i>
                                                {{ $lichHoc->giangVien->nguoiDung->ho_ten }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <a href="{{ route('hoc-vien.buoi-hoc.show', $lichHoc->id) }}" class="btn btn-sm btn-outline-primary">
                                            Xem buổi học
                                        </a>
                                        @if($lichHoc->can_open_online_room)
                                            <a href="{{ $lichHoc->online_entry_url }}"
                                               @if($lichHoc->online_entry_target_blank) target="_blank" rel="noopener noreferrer" @endif
                                               class="btn btn-sm btn-primary">
                                                Vào phòng học
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state-box">Khóa học này chưa có buổi học nào được lên lịch.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            {{-- ========== Section: Bài giảng & tài liệu đã công bố (interactive list + preview) ========== --}}
            <section id="tai-lieu" class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Bài giảng &amp; tài liệu đã công bố</h5>
                    <p class="text-muted small mb-0">Bấm vào một mục bên trái để xem trước nội dung. Bấm "Xem chi tiết" để mở bài giảng đầy đủ.</p>
                </div>
                <div class="card-body p-0">
                    @if($publishedLectures->isEmpty())
                        <div class="p-4">
                            <div class="empty-state-box">Chưa có bài giảng nào được công bố cho khóa học này.</div>
                        </div>
                    @else
                        <div class="cdt-tl-wrap" id="cdtTaiLieuWrap">
                            <div class="cdt-tl-list">
                                @foreach($publishedLectures as $bg)
                                    @php
                                        $isLive = $bg->isLive();
                                        $tn = $bg->taiNguyenChinh;
                                        $loaiBadge = match($tn?->loai_tai_nguyen) {
                                            'video' => ['icon' => 'fa-play-circle', 'label' => 'VIDEO', 'color' => '#dc2626'],
                                            'pdf'   => ['icon' => 'fa-file-pdf',    'label' => 'PDF',   'color' => '#dc2626'],
                                            'image' => ['icon' => 'fa-image',       'label' => 'ẢNH',   'color' => '#0ea5e9'],
                                            'word', 'powerpoint', 'excel' => ['icon' => 'fa-file-lines', 'label' => 'TÀI LIỆU', 'color' => '#16a34a'],
                                            'audio' => ['icon' => 'fa-volume-high', 'label' => 'AUDIO', 'color' => '#7c3aed'],
                                            'link_ngoai' => ['icon' => 'fa-link',   'label' => 'LINK',  'color' => '#0ea5e9'],
                                            default => ['icon' => 'fa-file',        'label' => 'FILE',  'color' => '#64748b'],
                                        };
                                        if ($isLive) {
                                            $loaiBadge = ['icon' => 'fa-broadcast-tower', 'label' => 'LIVE', 'color' => '#dc2626'];
                                        }
                                    @endphp
                                    <button type="button" class="cdt-tl-item {{ $loop->first ? 'is-active' : '' }}" data-target="cdtPreview-{{ $bg->id }}">
                                        <div class="cdt-tl-thumb" style="background: {{ $loaiBadge['color'] }};">
                                            <i class="fas {{ $loaiBadge['icon'] }}"></i>
                                        </div>
                                        <div class="cdt-tl-text">
                                            <div class="cdt-tl-module">
                                                <i class="fas fa-cube"></i> {{ $bg->moduleHoc->ten_module ?? 'Chưa gán module' }}
                                            </div>
                                            <div class="cdt-tl-title">{{ $bg->tieu_de }}</div>
                                            <span class="cdt-tl-type" style="color: {{ $loaiBadge['color'] }};">
                                                <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                                            </span>
                                        </div>
                                        <i class="fas fa-chevron-right cdt-tl-arrow"></i>
                                    </button>
                                @endforeach
                            </div>

                            <div class="cdt-tl-preview-area">
                                @foreach($publishedLectures as $bg)
                                    @php
                                        $isLive = $bg->isLive();
                                        $tn = $bg->taiNguyenChinh;
                                        $href = $isLive && $bg->phongHocLive
                                            ? route('hoc-vien.live-room.show', $bg->id)
                                            : route('hoc-vien.bai-giang.show', $bg->id);

                                        $youtubeId = null;
                                        if ($tn && $tn->is_external && !empty($tn->link_ngoai)) {
                                            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $tn->link_ngoai, $m)) {
                                                $youtubeId = $m[1];
                                            }
                                        }
                                    @endphp
                                    <div id="cdtPreview-{{ $bg->id }}" class="cdt-tl-preview-pane {{ $loop->first ? 'is-active' : '' }}">
                                        <div class="cdt-tl-preview">
                                            @if($isLive)
                                                <div class="cdt-tl-fallback live-tone">
                                                    <i class="fas fa-broadcast-tower"></i>
                                                    <h5>Phòng học live</h5>
                                                    <p>{{ $bg->phongHocLive?->platform_label ?? 'Sẵn sàng tham gia' }}</p>
                                                </div>
                                            @elseif($tn && $tn->loai_tai_nguyen === 'video' && !$tn->is_external && $tn->file_url)
                                                <video controls preload="metadata" class="cdt-tl-media">
                                                    <source src="{{ $tn->file_url }}">
                                                </video>
                                            @elseif($youtubeId)
                                                <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" allowfullscreen frameborder="0" class="cdt-tl-media"></iframe>
                                            @elseif($tn && $tn->loai_tai_nguyen === 'image' && $tn->file_url)
                                                <img src="{{ $tn->file_url }}" alt="{{ $bg->tieu_de }}" class="cdt-tl-media">
                                            @elseif($tn && $tn->loai_tai_nguyen === 'pdf' && $tn->file_url)
                                                <iframe src="{{ $tn->file_url }}" class="cdt-tl-media cdt-tl-pdf" frameborder="0"></iframe>
                                            @elseif($tn && $tn->loai_tai_nguyen === 'audio' && $tn->file_url)
                                                <div class="cdt-tl-fallback audio-tone">
                                                    <i class="fas fa-volume-high"></i>
                                                    <audio controls preload="metadata" class="cdt-tl-audio">
                                                        <source src="{{ $tn->file_url }}">
                                                    </audio>
                                                </div>
                                            @else
                                                <div class="cdt-tl-fallback">
                                                    <i class="fas fa-file"></i>
                                                    <h5>{{ $tn?->loai_label ?: 'Bài giảng' }}</h5>
                                                    <p>{{ $tn ? 'Không có bản xem trước cho loại tài nguyên này.' : 'Bài giảng chưa gắn tài nguyên chính.' }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="cdt-tl-info">
                                            <div class="cdt-tl-info-eyebrow">
                                                <span><i class="fas fa-cube"></i> {{ $bg->moduleHoc->ten_module ?? 'Chưa gán module' }}</span>
                                                @if($bg->lichHoc)
                                                    <span class="cdt-tl-sep">·</span>
                                                    <span><i class="far fa-calendar-alt"></i> Buổi {{ $bg->lichHoc->buoi_so ?: '#' }} · {{ optional($bg->lichHoc->ngay_hoc)->format('d/m/Y') ?? '—' }}</span>
                                                @endif
                                            </div>
                                            <h5 class="cdt-tl-info-title">{{ $bg->tieu_de }}</h5>
                                            @if($bg->mo_ta)
                                                <p class="cdt-tl-info-desc">{{ $bg->mo_ta }}</p>
                                            @endif

                                            <div class="cdt-tl-buttons">
                                                <a href="{{ $href }}" class="cdt-tl-btn-primary">
                                                    <i class="fas {{ $isLive ? 'fa-broadcast-tower' : 'fa-arrow-up-right-from-square' }}"></i>
                                                    {{ $isLive ? 'Vào live room' : 'Xem chi tiết' }}
                                                </a>
                                                @if($tn && !$isLive && $tn->file_url)
                                                    <a href="{{ $tn->file_url }}" target="_blank" rel="noopener" class="cdt-tl-btn-secondary">
                                                        <i class="fas fa-up-right-from-square"></i> Mở file
                                                    </a>
                                                @endif
                                                @if($bg->lich_hoc_id)
                                                    <a href="{{ route('hoc-vien.buoi-hoc.show', $bg->lich_hoc_id) }}" class="cdt-tl-btn-secondary">
                                                        <i class="far fa-calendar"></i> Buổi học
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ========== Card: Bài giảng & tài liệu mới (đã chuyển xuống đây) ========== --}}
            <div class="cdt-side-card mb-4" id="bai-giang-moi">
                <div class="cdt-side-head">
                    <div>
                        <h6><i class="fas fa-clock-rotate-left"></i> Bài giảng &amp; tài liệu mới</h6>
                        <small>5 nội dung gần nhất giảng viên đã công bố cho khóa học.</small>
                    </div>
                    <span class="cdt-side-count">{{ $publishedLectures->count() }}</span>
                </div>
                <div class="cdt-side-body cdt-side-body--grid">
                    @forelse($publishedLectures->take(5) as $bg)
                        @php
                            $isLive = $bg->isLive();
                            $tn = $bg->taiNguyenChinh;
                            $href = $isLive && $bg->phongHocLive
                                ? route('hoc-vien.live-room.show', $bg->id)
                                : route('hoc-vien.bai-giang.show', $bg->id);

                            $youtubeId = null;
                            if ($tn && $tn->is_external && !empty($tn->link_ngoai)) {
                                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $tn->link_ngoai, $m)) {
                                    $youtubeId = $m[1];
                                }
                            }

                            $loaiBadge = match($tn?->loai_tai_nguyen) {
                                'video' => ['icon' => 'fa-play-circle', 'label' => 'VIDEO', 'color' => '#dc2626'],
                                'pdf'   => ['icon' => 'fa-file-pdf',    'label' => 'PDF',   'color' => '#dc2626'],
                                'image' => ['icon' => 'fa-image',       'label' => 'ẢNH',   'color' => '#0ea5e9'],
                                'word', 'powerpoint', 'excel' => ['icon' => 'fa-file-lines', 'label' => 'TÀI LIỆU', 'color' => '#16a34a'],
                                'audio' => ['icon' => 'fa-volume-high', 'label' => 'AUDIO', 'color' => '#7c3aed'],
                                'link_ngoai' => ['icon' => 'fa-link',   'label' => 'LINK',  'color' => '#0ea5e9'],
                                default => ['icon' => 'fa-file',        'label' => 'FILE',  'color' => '#64748b'],
                            };
                            if ($isLive) {
                                $loaiBadge = ['icon' => 'fa-broadcast-tower', 'label' => 'LIVE', 'color' => '#dc2626'];
                            }
                        @endphp
                        <article class="cdt-side-card-item">
                            <div class="cdt-side-preview">
                                @if($isLive)
                                    <div class="cdt-side-preview-live">
                                        <i class="fas fa-broadcast-tower"></i>
                                        <strong>Phòng học live</strong>
                                        <small>{{ $bg->phongHocLive?->platform_label ?? 'Sẵn sàng tham gia' }}</small>
                                    </div>
                                @elseif($tn && $tn->loai_tai_nguyen === 'video' && !$tn->is_external && $tn->file_url)
                                    <video controls preload="metadata" class="cdt-side-media">
                                        <source src="{{ $tn->file_url }}">
                                    </video>
                                @elseif($youtubeId)
                                    <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" allowfullscreen frameborder="0" class="cdt-side-media"></iframe>
                                @elseif($tn && $tn->loai_tai_nguyen === 'image' && $tn->file_url)
                                    <img src="{{ $tn->file_url }}" alt="{{ $bg->tieu_de }}" class="cdt-side-media">
                                @elseif($tn && $tn->loai_tai_nguyen === 'pdf' && $tn->file_url)
                                    <iframe src="{{ $tn->file_url }}#toolbar=0&navpanes=0" class="cdt-side-media cdt-side-pdf" frameborder="0"></iframe>
                                @elseif($tn && $tn->loai_tai_nguyen === 'audio' && $tn->file_url)
                                    <div class="cdt-side-audio-wrap">
                                        <i class="fas fa-volume-high"></i>
                                        <audio controls preload="metadata" class="cdt-side-audio">
                                            <source src="{{ $tn->file_url }}">
                                        </audio>
                                    </div>
                                @else
                                    <div class="cdt-side-preview-fallback">
                                        <i class="fas {{ $loaiBadge['icon'] }}"></i>
                                        <small>{{ $tn?->loai_label ?: 'Bài giảng' }}</small>
                                    </div>
                                @endif

                                <span class="cdt-side-type-badge" style="background: {{ $loaiBadge['color'] }};">
                                    <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                                </span>
                            </div>

                            <div class="cdt-side-meta">
                                <div class="cdt-side-module">
                                    <i class="fas fa-cube"></i> {{ $bg->moduleHoc->ten_module ?? 'Chưa gán module' }}
                                </div>
                                <div class="cdt-side-title">{{ $bg->tieu_de }}</div>
                                @if($bg->mo_ta)
                                    <p class="cdt-side-desc">{{ \Illuminate\Support\Str::limit($bg->mo_ta, 90) }}</p>
                                @endif
                                <div class="cdt-side-buttons">
                                    <a href="{{ $href }}" class="cdt-side-btn-primary">
                                        <i class="fas {{ $isLive ? 'fa-broadcast-tower' : 'fa-arrow-up-right-from-square' }}"></i>
                                        {{ $isLive ? 'Vào live room' : 'Xem chi tiết' }}
                                    </a>
                                    @if($tn && !$isLive && $tn->file_url)
                                        <a href="{{ $tn->file_url }}" target="_blank" rel="noopener" class="cdt-side-btn-secondary" title="Mở file trong tab mới">
                                            <i class="fas fa-up-right-from-square"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="cdt-side-empty">
                            <i class="fas fa-folder-open"></i>
                            <p>Chưa có bài giảng nào được công bố.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card vip-card border-0 shadow-sm course-quick-card mb-4">
                <div class="card-header border-0 bg-white py-3"><h6 class="fw-semibold mb-0">Thông tin nhanh</h6></div>
                <div class="card-body">
                    <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" alt="{{ $khoaHoc->ten_khoa_hoc }}" class="img-fluid rounded-4 border mb-4">
                    <div class="content-row"><span class="small text-muted">Khai giảng</span><strong>{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?: 'Chưa cập nhật' }}</strong></div>
                    <div class="content-row"><span class="small text-muted">Trình độ</span><strong>{{ ['co_ban' => 'Cơ bản', 'trung_binh' => 'Trung bình', 'nang_cao' => 'Nâng cao'][$khoaHoc->cap_do] ?? 'Chưa cập nhật' }}</strong></div>
                    <div class="content-row"><span class="small text-muted">Buổi online</span><strong>{{ $stats['buoi_online'] }}</strong></div>
                    <div class="content-row"><span class="small text-muted">Tài nguyên đã mở</span><strong>{{ $stats['tai_nguyen_cong_khai'] }}</strong></div>
                    <div class="d-grid gap-2 mt-4">
                        <a href="#lich-hoc" class="btn btn-outline-primary"><i class="fas fa-calendar-day me-2"></i>Xem thời khóa biểu</a>
                        <a href="#tai-lieu" class="btn btn-outline-secondary"><i class="fas fa-folder-open me-2"></i>Xem tài liệu</a>
                        <a href="#bai-kiem-tra" class="btn btn-outline-dark"><i class="fas fa-list-check me-2"></i>Xem bài kiểm tra</a>
                    </div>
                </div>
            </div>

            {{-- ========== Bài kiểm tra đã phát hành (chuyển từ main column sang sidebar) ========== --}}
            <section id="bai-kiem-tra" class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Bài kiểm tra đã phát hành</h5>
                    <p class="text-muted small mb-0">Lọc theo đúng khóa học bạn đang theo học.</p>
                </div>
                <div class="card-body">
                    @forelse($publishedExams as $baiKiemTra)
                        <div class="cdt-side-exam">
                            <div class="cdt-side-exam-info">
                                <div class="cdt-side-exam-title">{{ $baiKiemTra->tieu_de }}</div>
                                <div class="cdt-side-exam-meta">
                                    <span><i class="fas fa-cube"></i> {{ $baiKiemTra->moduleHoc->ten_module ?? 'Toàn khóa' }}</span>
                                    <span class="badge bg-{{ $baiKiemTra->access_status_color }} px-2 py-1">{{ $baiKiemTra->access_status_label }}</span>
                                </div>
                            </div>
                            <div class="cdt-side-exam-actions">
                                <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="cdt-side-exam-btn primary">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                                @if($baiKiemTra->lich_hoc_id)
                                    <a href="{{ route('hoc-vien.buoi-hoc.show', $baiKiemTra->lich_hoc_id) }}" class="cdt-side-exam-btn">
                                        <i class="far fa-calendar"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-box">Chưa có bài kiểm tra nào được phát hành.</div>
                    @endforelse
                </div>
            </section>

            {{-- ========== Tiến độ & kết quả học tập ========== --}}
            <section id="tien-do" class="card vip-card border-0 shadow-sm">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Tiến độ &amp; kết quả</h5>
                    <p class="text-muted small mb-0">Tổng hợp điểm danh và kết quả của khóa học.</p>
                </div>
                <div class="card-body">
                    @if($ketQuaHocTap)
                        <div class="cdt-side-score">
                            <div class="cdt-side-score-row">
                                <span><i class="fas fa-user-check text-success"></i> Chuyên cần</span>
                                <strong>{{ $ketQuaHocTap->diem_diem_danh !== null ? number_format((float) $ketQuaHocTap->diem_diem_danh, 2) : '—' }}</strong>
                            </div>
                            <div class="cdt-side-score-row">
                                <span><i class="fas fa-file-pen text-primary"></i> Kiểm tra</span>
                                <strong>{{ $ketQuaHocTap->diem_kiem_tra !== null ? number_format((float) $ketQuaHocTap->diem_kiem_tra, 2) : '—' }}</strong>
                            </div>
                            <div class="cdt-side-score-row is-final">
                                <span><i class="fas fa-trophy text-warning"></i> Tổng kết</span>
                                <strong>{{ $ketQuaHocTap->diem_tong_ket !== null ? number_format((float) $ketQuaHocTap->diem_tong_ket, 2) : 'Đang tính' }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="empty-state-box">Kết quả sẽ xuất hiện khi đủ dữ liệu điểm danh và bài kiểm tra.</div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ===== Welcome banner xanh dương ===== */
    .cdt-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .cdt-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .cdt-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.74rem; font-weight: 800; letter-spacing: 0.5px; border-radius: 999px; }
    .cdt-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.74rem; font-weight: 700; border-radius: 999px; }
    .apx-welcome.cdt-welcome p { font-size: 0.9rem; line-height: 1.55; }
    .cdt-cta-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    /* Breadcrumb đỏ */
    .cdt-breadcrumb { background: #fff; border: 1px solid #e2e8f0; border-left: 3px solid #dc2626; border-radius: 8px; padding: 8px 16px; }
    .cdt-breadcrumb .breadcrumb { font-size: 0.82rem; }
    .cdt-breadcrumb a { color: #dc2626; text-decoration: none; font-weight: 600; }
    .cdt-breadcrumb a:hover { color: #b91c1c; text-decoration: underline; }
    .cdt-breadcrumb .breadcrumb-item.active { color: #0f172a; font-weight: 700; }

    /* Section card title polish — đỏ */
    .cdt-page .vip-card .card-header h5 { color: #0f172a; font-size: 0.96rem; font-weight: 800; }
    .cdt-page .vip-card .card-header h5::before { content: ''; display: inline-block; width: 4px; height: 16px; background: #dc2626; margin-right: 10px; vertical-align: middle; border-radius: 2px; }

    /* Section link bar — chuyển sang style đỏ */
    .cdt-page .section-link-bar .section-link { background: #fff; color: #475569; border-color: #e2e8f0; }
    .cdt-page .section-link-bar .section-link:hover { color: #dc2626; border-color: #fecaca; background: #fef2f2; }

    /* ===== Sidebar card: Bài giảng & tài liệu mới ===== */
    .cdt-side-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }
    .cdt-side-head {
        padding: 14px 16px;
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        border-bottom: 1px solid #fecaca;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }
    .cdt-side-head h6 {
        margin: 0 0 3px;
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }
    .cdt-side-head h6 i { color: #dc2626; font-size: 0.85rem; }
    .cdt-side-head small {
        display: block;
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 500;
        line-height: 1.4;
    }
    .cdt-side-count {
        flex-shrink: 0;
        min-width: 32px;
        padding: 4px 10px;
        background: #dc2626;
        color: #fff;
        font-size: 0.78rem;
        font-weight: 800;
        border-radius: 999px;
        text-align: center;
    }

    .cdt-side-body {
        padding: 12px;
        max-height: 720px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #fecaca #fef2f2;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .cdt-side-body::-webkit-scrollbar { width: 6px; }
    .cdt-side-body::-webkit-scrollbar-track { background: #fef2f2; }
    .cdt-side-body::-webkit-scrollbar-thumb { background: #fecaca; border-radius: 999px; }

    /* Item card with inline preview */
    .cdt-side-card-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .cdt-side-card-item:hover {
        border-color: #fecaca;
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.08);
        transform: translateY(-2px);
    }

    /* Preview area */
    .cdt-side-preview {
        position: relative;
        background: #0f172a;
        aspect-ratio: 16/9;
        overflow: hidden;
    }
    .cdt-side-media {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border: 0;
    }
    .cdt-side-media-wrap { width: 100%; height: 100%; }
    .cdt-side-pdf { background: #fff; }

    .cdt-side-preview-live,
    .cdt-side-preview-fallback {
        width: 100%; height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-align: center;
        padding: 16px;
        background: linear-gradient(135deg, #1d4ed8 0%, #4361ee 100%);
    }
    .cdt-side-preview-live i,
    .cdt-side-preview-fallback i {
        font-size: 2.4rem;
        margin-bottom: 10px;
        opacity: 0.95;
    }
    .cdt-side-preview-live strong,
    .cdt-side-preview-fallback small {
        font-size: 0.86rem;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .cdt-side-preview-live small {
        font-size: 0.75rem;
        opacity: 0.85;
    }
    .cdt-side-preview-live {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }
    .cdt-side-preview-live i { animation: cdtLiveBlink 1.5s ease-in-out infinite; }
    @keyframes cdtLiveBlink {
        0%, 100% { opacity: 1; }
        50%      { opacity: 0.5; }
    }
    .cdt-side-preview-fallback {
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    }

    .cdt-side-audio-wrap {
        width: 100%; height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: #fff;
        gap: 14px;
        padding: 16px;
    }
    .cdt-side-audio-wrap > i { font-size: 2.4rem; opacity: 0.9; }
    .cdt-side-audio { width: 100%; max-width: 280px; }

    .cdt-side-type-badge {
        position: absolute;
        top: 8px; left: 8px;
        padding: 3px 10px;
        color: #fff;
        font-size: 0.66rem;
        font-weight: 800;
        border-radius: 6px;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .cdt-side-type-badge i { font-size: 0.62rem; }

    /* Meta below preview */
    .cdt-side-meta {
        padding: 12px 14px;
    }
    .cdt-side-module {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .cdt-side-module i { color: #1d4ed8; font-size: 0.62rem; }
    .cdt-side-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cdt-side-desc {
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 10px;
    }

    .cdt-side-buttons {
        display: flex;
        gap: 6px;
    }
    .cdt-side-btn-primary {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 12px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.76rem;
        font-weight: 800;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .cdt-side-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(220, 38, 38, 0.28);
        color: #fff;
    }
    .cdt-side-btn-primary i { font-size: 0.7rem; }
    .cdt-side-btn-secondary {
        display: inline-grid;
        place-items: center;
        width: 32px; height: 32px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.78rem;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
        flex-shrink: 0;
    }
    .cdt-side-btn-secondary:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    .cdt-side-empty {
        padding: 30px 20px;
        text-align: center;
        color: #94a3b8;
    }
    .cdt-side-empty i { font-size: 1.8rem; opacity: 0.5; display: block; margin-bottom: 8px; color: #dc2626; }
    .cdt-side-empty p { font-size: 0.84rem; margin: 0; }

    .cdt-side-more {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 14px;
        margin: 8px;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #dc2626;
        font-size: 0.78rem;
        font-weight: 800;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .cdt-side-more:hover {
        background: #dc2626;
        color: #fff;
    }
    .cdt-side-more i { font-size: 0.7rem; }

    /* ===== Body grid layout cho Bài giảng & tài liệu mới (đã chuyển vào main col) ===== */
    .cdt-side-body--grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        max-height: none;
    }
    @media (max-width: 575.98px) {
        .cdt-side-body--grid { grid-template-columns: 1fr; }
    }

    /* ===== Interactive Bài giảng & tài liệu đã công bố (list + preview) ===== */
    .cdt-tl-wrap {
        display: grid;
        grid-template-columns: 320px 1fr;
        min-height: 480px;
    }
    @media (max-width: 991.98px) {
        .cdt-tl-wrap { grid-template-columns: 1fr; }
    }

    .cdt-tl-list {
        background: #fafafa;
        border-right: 1px solid #fecaca;
        max-height: 600px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #fecaca #fef2f2;
    }
    .cdt-tl-list::-webkit-scrollbar { width: 6px; }
    .cdt-tl-list::-webkit-scrollbar-track { background: #fef2f2; }
    .cdt-tl-list::-webkit-scrollbar-thumb { background: #fecaca; border-radius: 999px; }
    @media (max-width: 991.98px) {
        .cdt-tl-list { border-right: 0; border-bottom: 1px solid #fecaca; max-height: 360px; }
    }

    .cdt-tl-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 12px 14px;
        background: transparent;
        border: 0;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .cdt-tl-item:hover {
        background: #fef2f2;
    }
    .cdt-tl-item.is-active {
        background: #fff;
        border-left: 3px solid #dc2626;
        padding-left: 11px;
    }
    .cdt-tl-item.is-active .cdt-tl-arrow { color: #dc2626; opacity: 1; transform: translateX(0); }

    .cdt-tl-thumb {
        flex-shrink: 0;
        width: 38px; height: 38px;
        border-radius: 10px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 0.95rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .cdt-tl-text {
        flex: 1; min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .cdt-tl-module {
        font-size: 0.66rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .cdt-tl-module i { color: #1d4ed8; margin-right: 4px; font-size: 0.6rem; }
    .cdt-tl-title {
        font-size: 0.84rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cdt-tl-type {
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        margin-top: 2px;
    }
    .cdt-tl-type i { font-size: 0.58rem; margin-right: 3px; }
    .cdt-tl-arrow {
        color: #cbd5e1;
        font-size: 0.78rem;
        opacity: 0.6;
        transform: translateX(-4px);
        transition: all 0.18s ease;
    }

    /* Preview area */
    .cdt-tl-preview-area { padding: 18px 20px; background: #fff; }
    .cdt-tl-preview-pane { display: none; }
    .cdt-tl-preview-pane.is-active { display: block; }

    .cdt-tl-preview {
        background: #0f172a;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 16/9;
        margin-bottom: 16px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    }
    .cdt-tl-media {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
        border: 0;
    }
    .cdt-tl-pdf { background: #fff; aspect-ratio: auto; height: 100%; }

    .cdt-tl-fallback {
        width: 100%; height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    }
    .cdt-tl-fallback i { font-size: 3rem; margin-bottom: 14px; opacity: 0.95; }
    .cdt-tl-fallback h5 { font-size: 1rem; font-weight: 800; margin: 0 0 4px; }
    .cdt-tl-fallback p { font-size: 0.85rem; margin: 0; opacity: 0.85; }
    .cdt-tl-fallback.live-tone {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }
    .cdt-tl-fallback.live-tone i { animation: cdtLiveBlink 1.5s ease-in-out infinite; }
    .cdt-tl-fallback.audio-tone {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        gap: 14px;
    }
    .cdt-tl-audio { width: 100%; max-width: 360px; }

    .cdt-tl-info-eyebrow {
        display: flex; flex-wrap: wrap; gap: 6px;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .cdt-tl-info-eyebrow i { color: #dc2626; margin-right: 4px; font-size: 0.7rem; }
    .cdt-tl-sep { opacity: 0.5; }

    .cdt-tl-info-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin: 0 0 8px;
    }
    .cdt-tl-info-desc {
        font-size: 0.86rem;
        color: #475569;
        line-height: 1.6;
        margin: 0 0 14px;
        white-space: pre-wrap;
    }

    .cdt-tl-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .cdt-tl-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.84rem;
        font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
        transition: all 0.18s ease;
    }
    .cdt-tl-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.32);
        color: #fff;
    }
    .cdt-tl-btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 0.82rem;
        font-weight: 700;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .cdt-tl-btn-secondary:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .cdt-tl-btn-primary i, .cdt-tl-btn-secondary i { font-size: 0.78rem; }

    /* ===== Sidebar: Bài kiểm tra (compressed) ===== */
    .cdt-side-exam {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px dashed #f1f5f9;
    }
    .cdt-side-exam:first-child { padding-top: 0; }
    .cdt-side-exam:last-child { padding-bottom: 0; border-bottom: 0; }
    .cdt-side-exam-info { flex: 1; min-width: 0; }
    .cdt-side-exam-title {
        font-size: 0.86rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: 5px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cdt-side-exam-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
    }
    .cdt-side-exam-meta i { color: #1d4ed8; margin-right: 3px; font-size: 0.66rem; }
    .cdt-side-exam-actions {
        display: flex; gap: 4px;
        flex-shrink: 0;
    }
    .cdt-side-exam-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 6px 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .cdt-side-exam-btn:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .cdt-side-exam-btn.primary {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        border-color: transparent;
    }
    .cdt-side-exam-btn.primary:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    /* Sidebar: Tiến độ điểm */
    .cdt-side-score { display: flex; flex-direction: column; gap: 8px; }
    .cdt-side-score-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
    }
    .cdt-side-score-row span {
        font-size: 0.82rem;
        color: #475569;
        font-weight: 700;
    }
    .cdt-side-score-row span i { margin-right: 6px; font-size: 0.78rem; }
    .cdt-side-score-row strong {
        font-size: 1.05rem;
        font-weight: 900;
        color: #0f172a;
    }
    .cdt-side-score-row.is-final {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #fecaca;
    }
    .cdt-side-score-row.is-final strong { color: #dc2626; font-size: 1.2rem; }

    .section-link-bar { display: flex; flex-wrap: wrap; gap: 0.75rem; }
    .section-link { display: inline-flex; align-items: center; padding: 0.45rem 0.85rem; border-radius: 999px; border: 1px solid #dbe4ef; background: #fff; color: #334155; text-decoration: none; font-size: 0.92rem; font-weight: 600; }
    .section-link:hover { color: #0d6efd; border-color: #9ec5fe; }
    .stat-box, .overview-box, .module-card { border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; padding: 1rem 1.1rem; height: 100%; }
    .stat-box__label { color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem; }
    .stat-box__value { font-size: 2rem; line-height: 1; font-weight: 700; color: #0f172a; }
    .progress-thin { height: 8px; border-radius: 999px; background: #e2e8f0; }
    .content-row { padding: 1rem 0; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
    .content-row:first-child { padding-top: 0; }
    .content-row:last-child { padding-bottom: 0; border-bottom: none; }
    .course-quick-card { position: relative; z-index: 0; }
    .course-schedule-pill { display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid #bfdbfe; border-radius: 8px; background: #eff6ff; color: #1d4ed8; font-size: 0.86rem; font-weight: 700; padding: 0.5rem 0.75rem; white-space: nowrap; }
    .course-schedule-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.6rem; margin-bottom: 0.9rem; }
    .course-schedule-summary-item { border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; padding: 0.65rem 0.75rem; }
    .course-schedule-summary-item span { display: block; color: #64748b; font-size: 0.78rem; margin-bottom: 0.2rem; }
    .course-schedule-summary-item strong { display: block; color: #0f172a; font-size: 1.25rem; line-height: 1; }
    .course-schedule-summary-item.is-done { background: #ecfdf5; border-color: #86efac; }
    .course-schedule-summary-item.is-current { background: #eff6ff; border-color: #93c5fd; }
    .course-schedule-summary-item.is-upcoming { background: #f8fafc; border-color: #cbd5e1; }
    .course-schedule-summary-item.is-online { background: #f0fdfa; border-color: #99f6e4; }
    .course-schedule-board { display: grid; gap: 0.65rem; max-height: min(58vh, 560px); overflow-y: auto; overscroll-behavior: contain; padding-right: 0.35rem; scrollbar-gutter: stable; scrollbar-width: thin; scrollbar-color: #93c5fd #eff6ff; }
    .course-schedule-board::-webkit-scrollbar { width: 8px; }
    .course-schedule-board::-webkit-scrollbar-track { background: #eff6ff; border-radius: 8px; }
    .course-schedule-board::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 8px; }
    .course-schedule-item { position: relative; display: grid; grid-template-columns: 32px minmax(0, 1fr); gap: 0.6rem; }
    .course-schedule-item:not(:last-child)::before { content: ""; position: absolute; left: 15px; top: 32px; bottom: -0.65rem; border-left: 2px solid #dbeafe; }
    .course-schedule-marker { position: relative; z-index: 1; width: 32px; height: 32px; border: 1px solid #bfdbfe; border-radius: 8px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; }
    .course-schedule-card { min-width: 0; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; padding: 0.7rem 0.8rem; }
    .course-schedule-card .min-w-0 { min-width: 0; }
    .course-schedule-eyebrow { display: flex; flex-wrap: wrap; gap: 0.3rem; color: #2563eb; font-size: 0.78rem; font-weight: 700; margin-bottom: 0.15rem; }
    .course-schedule-title { color: #0f172a; font-size: 0.94rem; font-weight: 700; margin-bottom: 0; overflow-wrap: anywhere; }
    .course-schedule-meta { display: flex; flex-wrap: wrap; gap: 0.3rem 0.8rem; color: #64748b; font-size: 0.82rem; margin-top: 0.45rem; }
    .course-schedule-meta span { display: inline-flex; align-items: center; gap: 0.35rem; min-width: 0; overflow-wrap: anywhere; }
    .course-schedule-meta i { flex: 0 0 auto; color: #2563eb; font-size: 0.78rem; }
    .course-schedule-status { border-radius: 8px; font-size: 0.72rem; font-weight: 800; line-height: 1; padding: 0.35rem 0.5rem; white-space: nowrap; }
    .course-schedule-status-done { background: #dcfce7; color: #166534; }
    .course-schedule-status-current { background: #dbeafe; color: #1d4ed8; }
    .course-schedule-status-upcoming { background: #f1f5f9; color: #475569; }
    .course-schedule-item.is-done:not(:last-child)::before { border-color: #86efac; }
    .course-schedule-item.is-done .course-schedule-marker { background: #dcfce7; border-color: #86efac; color: #15803d; }
    .course-schedule-item.is-done .course-schedule-card { background: #f0fdf4; border-color: #bbf7d0; }
    .course-schedule-item.is-current .course-schedule-marker { background: #dbeafe; border-color: #60a5fa; color: #1d4ed8; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12); }
    .course-schedule-item.is-current .course-schedule-card { border-color: #60a5fa; box-shadow: 0 10px 24px rgba(37, 99, 235, 0.1); }
    .empty-state-box { border: 1px dashed #cbd5e1; border-radius: 18px; padding: 1.25rem; background: #f8fafc; color: #64748b; text-align: center; }

    @media (max-width: 767.98px) {
        .course-schedule-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .course-schedule-pill { width: 100%; justify-content: center; }
        .course-schedule-board { max-height: 60vh; }
    }

    @media (max-width: 479.98px) {
        .course-schedule-summary { grid-template-columns: 1fr; }
        .course-schedule-item { grid-template-columns: 28px minmax(0, 1fr); gap: 0.55rem; }
        .course-schedule-marker { width: 28px; height: 28px; font-size: 0.76rem; }
        .course-schedule-item:not(:last-child)::before { left: 13px; top: 28px; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrap = document.getElementById('cdtTaiLieuWrap');
    if (!wrap) return;

    const items = wrap.querySelectorAll('.cdt-tl-item');
    const panes = wrap.querySelectorAll('.cdt-tl-preview-pane');

    items.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.dataset.target;
            // Pause any playing media in non-active panes
            panes.forEach(p => {
                if (p.id === target) {
                    p.classList.add('is-active');
                } else {
                    p.classList.remove('is-active');
                    p.querySelectorAll('video, audio').forEach(m => { try { m.pause(); } catch(e){} });
                    // YouTube iframes — reset src to stop playback
                    p.querySelectorAll('iframe[src*="youtube.com"]').forEach(f => {
                        const src = f.src;
                        f.src = src;
                    });
                }
            });
            items.forEach(i => i.classList.remove('is-active'));
            this.classList.add('is-active');
        });
    });
});
</script>
@endpush

@include('pages.admin.partials._admin-page-styles')
@endsection
