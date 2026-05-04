@extends('layouts.app', ['title' => 'Hoạt động và tiến độ học tập'])

@section('content')
@php
    $tongKhoaHoc = $tienDoKhoaHoc->count();
    $hasKiemTra = $baiKiemTraCanChuY->count() > 0;
@endphp

<div class="container-fluid admin-page-x hd-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome hd-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-chart-line"></i></div>
        <div class="apx-welcome-text">
            <div class="hd-tag-row">
                <span class="hd-loai-badge">
                    <i class="fas fa-route"></i> HOẠT ĐỘNG &amp; TIẾN ĐỘ
                </span>
                <span class="hd-status-badge">
                    <i class="fas fa-graduation-cap"></i>
                    {{ $tongKhoaHoc }} khóa học
                </span>
                @if(($dashboardStats['buoi_hoc_hom_nay'] ?? 0) > 0)
                    <span class="hd-pending-badge">
                        <i class="fas fa-bell"></i>
                        {{ $dashboardStats['buoi_hoc_hom_nay'] }} buổi hôm nay
                    </span>
                @endif
            </div>
            <h4>Tổng quan học tập của tôi</h4>
            <p>
                <span><i class="fas fa-calendar-day"></i> {{ $dashboardStats['buoi_hoc_sap_toi'] }} buổi sắp tới</span>
                <span class="hd-sep">·</span>
                <span><i class="fas fa-folder-open"></i> {{ $dashboardStats['tai_lieu_moi_7_ngay'] }} tài liệu mới</span>
                <span class="hd-sep">·</span>
                <span><i class="fas fa-user-check"></i> Chuyên cần {{ $chuyenCanTongQuan['ty_le_tham_du'] }}%</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn btn-light text-primary fw-bold shadow-sm hd-create-btn">
                <i class="fas fa-book-open me-1"></i> Khóa học của tôi
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- ========== ① Tổng quan ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan nhanh</h2>
                    <p>Bốn chỉ số bạn nên xem mỗi ngày để bám sát tiến độ học tập.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $dashboardStats['tien_do_tong_quan'] }}%</strong> tiến độ chung</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="aps-text">
                        <strong>{{ $dashboardStats['buoi_hoc_sap_toi'] }}</strong>
                        <small>Buổi học sắp tới</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-folder-open"></i></div>
                    <div class="aps-text">
                        <strong>{{ $dashboardStats['tai_lieu_moi_7_ngay'] }}</strong>
                        <small>Tài liệu mới (7 ngày)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-user-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $chuyenCanTongQuan['ty_le_tham_du'] }}%</strong>
                        <small>Tỷ lệ chuyên cần</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="aps-text">
                        <strong>{{ $dashboardStats['tien_do_tong_quan'] }}%</strong>
                        <small>Tiến độ tổng quan</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Hoạt động gần đây & Chuyên cần ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-stream"></i> Hoạt động gần đây &amp; Chuyên cần</h2>
                    <p>Tổng hợp hoạt động gần nhất kèm thống kê điểm danh chi tiết.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $chuyenCanTongQuan['tong'] }}</strong> lượt điểm danh</span>
            </div>
        </header>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="hd-card h-100">
                    <div class="hd-card-head">
                        <h6><i class="fas fa-stream"></i> Hoạt động gần đây</h6>
                        <small>Điểm danh, tài liệu mới và buổi học đã hoàn thành.</small>
                    </div>
                    <div class="hd-card-body">
                        @forelse($hoatDongGanDay as $hoatDong)
                            <div class="activity-item">
                                <div class="activity-icon bg-{{ $hoatDong['color'] }}">
                                    <i class="fas {{ $hoatDong['icon'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark">{{ $hoatDong['title'] }}</div>
                                    <div class="text-muted small">{{ $hoatDong['description'] ?: 'Không có mô tả bổ sung' }}</div>
                                    <div class="small text-muted mt-1">
                                        {{ $hoatDong['meta'] ?: 'Chưa cập nhật' }}
                                        @if(!empty($hoatDong['sort_at']))
                                            • {{ $hoatDong['sort_at']->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="empty-icon mb-3"><i class="fas fa-stream"></i></div>
                                <h6 class="fw-semibold">Chưa có hoạt động nào để hiển thị</h6>
                                <p class="text-muted mb-0">Hoạt động sẽ xuất hiện sau khi học viên bắt đầu học, điểm danh hoặc giảng viên công khai tài liệu.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="hd-card h-100">
                    <div class="hd-card-head">
                        <h6><i class="fas fa-user-check"></i> Chuyên cần tổng quan</h6>
                        <small>Sử dụng dữ liệu điểm danh đã được giảng viên ghi nhận.</small>
                    </div>
                    <div class="hd-card-body">
                        <div class="attendance-progress mb-4">
                            <div class="d-flex justify-content-between small mb-2">
                                <span>Tỷ lệ tham dự</span>
                                <strong>{{ $chuyenCanTongQuan['ty_le_tham_du'] }}%</strong>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-success progress-live"
                                     role="progressbar"
                                     style="width: {{ $chuyenCanTongQuan['ty_le_tham_du'] }}%;"
                                     aria-valuenow="{{ $chuyenCanTongQuan['ty_le_tham_du'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="attendance-box attendance-success">
                                    <div class="attendance-value">{{ $chuyenCanTongQuan['co_mat'] }}</div>
                                    <div class="attendance-label">Có mặt</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="attendance-box attendance-warning">
                                    <div class="attendance-value">{{ $chuyenCanTongQuan['vao_tre'] }}</div>
                                    <div class="attendance-label">Vào trễ</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="attendance-box attendance-danger">
                                    <div class="attendance-value">{{ $chuyenCanTongQuan['vang_mat'] }}</div>
                                    <div class="attendance-label">Vắng mặt</div>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-semibold mb-3">Điểm danh gần đây</h6>
                        @forelse($diemDanhGanDay as $diemDanh)
                            @php
                                $trangThaiLabel = match ($diemDanh->trang_thai) {
                                    'co_mat' => 'Có mặt',
                                    'vao_tre' => 'Vào trễ',
                                    'vang_mat' => 'Vắng mặt',
                                    default => 'Chưa rõ',
                                };
                                $trangThaiColor = match ($diemDanh->trang_thai) {
                                    'co_mat' => 'success',
                                    'vao_tre' => 'warning',
                                    'vang_mat' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <div class="attendance-log">
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="fw-semibold text-dark">
                                        {{ optional(optional($diemDanh->lichHoc)->khoaHoc)->ten_khoa_hoc ?: 'Chưa xác định khóa học' }}
                                    </div>
                                    <span class="badge bg-{{ $trangThaiColor }}">{{ $trangThaiLabel }}</span>
                                </div>
                                <div class="small text-muted">
                                    {{ optional(optional($diemDanh->lichHoc)->moduleHoc)->ten_module ?: 'Chưa gán module' }}
                                </div>
                                <div class="small text-muted">
                                    {{ optional(optional($diemDanh->lichHoc)->ngay_hoc)->format('d/m/Y') ?: 'Chưa có ngày học' }}
                                    @if(optional($diemDanh->updated_at))
                                        • {{ $diemDanh->updated_at->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <div class="empty-icon mb-3"><i class="fas fa-user-check"></i></div>
                                <p class="text-muted mb-0">Chưa có dữ liệu điểm danh để thống kê.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ③ Lộ trình buổi học & Tài liệu mới ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-route"></i> Lộ trình buổi học &amp; Tài liệu mới</h2>
                    <p>Buổi đã xong, đang học, còn lại — và tài liệu giảng viên vừa công khai.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill">
                    <strong>{{ $dashboardStats['tong_buoi_hoan_thanh_dang_hoc'] }}/{{ $dashboardStats['tong_buoi_dang_hoc'] }}</strong> buổi đã xong
                </span>
            </div>
        </header>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="hd-card h-100">
                    <div class="hd-card-head">
                        <h6><i class="fas fa-route"></i> Lộ trình buổi học</h6>
                        <small>Đã xong • Đang học • Còn lại</small>
                    </div>
                    <div class="hd-card-body">
                        <div class="lesson-roadmap-summary">
                            <div class="lesson-roadmap-summary-item is-done">
                                <span>Đã xong</span>
                                <strong>{{ $dashboardStats['tong_buoi_hoan_thanh_dang_hoc'] }}</strong>
                            </div>
                            <div class="lesson-roadmap-summary-item is-current">
                                <span>Đang học</span>
                                <strong>{{ $dashboardStats['tong_buoi_dang_hoc_hien_tai'] }}</strong>
                            </div>
                            <div class="lesson-roadmap-summary-item is-upcoming">
                                <span>Còn lại</span>
                                <strong>{{ $dashboardStats['tong_buoi_con_lai_dang_hoc'] }}</strong>
                            </div>
                        </div>

                        @php
                            $currentLessonIds = collect($dashboardStats['buoi_hoc_hien_tai_ids'] ?? [])
                                ->map(fn ($id) => (int) $id)
                                ->all();
                        @endphp

                        <div class="lesson-roadmap-list">
                            @forelse($dongThoiGianBuoiHoc as $lichHoc)
                                @php
                                    $isCurrentLesson = $lichHoc->is_in_progress
                                        || in_array((int) $lichHoc->id, $currentLessonIds, true);
                                    $lessonState = $lichHoc->is_ended ? 'done' : ($isCurrentLesson ? 'current' : 'upcoming');
                                    $lessonStateLabel = match ($lessonState) {
                                        'done' => 'Đã xong',
                                        'current' => 'Đang học',
                                        default => 'Còn lại',
                                    };
                                    $lessonStateIcon = match ($lessonState) {
                                        'done' => 'fa-check',
                                        'current' => 'fa-play',
                                        default => 'fa-clock',
                                    };
                                @endphp
                                <div class="lesson-roadmap-item is-{{ $lessonState }}">
                                    <div class="lesson-roadmap-marker">
                                        <i class="fas {{ $lessonStateIcon }}"></i>
                                    </div>

                                    <div class="lesson-roadmap-card">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                            <div class="min-w-0">
                                                <div class="lesson-roadmap-eyebrow">
                                                    Buổi {{ $lichHoc->buoi_so ?: $loop->iteration }}
                                                    <span>•</span>
                                                    {{ $lichHoc->hinh_thuc_label }}
                                                </div>
                                                <h6 class="lesson-roadmap-title">{{ $lichHoc->khoaHoc->ten_khoa_hoc }}</h6>
                                            </div>
                                            <span class="lesson-state-badge lesson-state-{{ $lessonState }}">{{ $lessonStateLabel }}</span>
                                        </div>

                                        <div class="lesson-roadmap-meta">
                                            <span>
                                                <i class="fas fa-layer-group"></i>
                                                {{ $lichHoc->moduleHoc->ten_module ?? 'Chưa gán module' }}
                                            </span>
                                            <span>
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ optional($lichHoc->ngay_hoc)->format('d/m/Y') ?: 'Chưa có ngày học' }}
                                            </span>
                                            <span>
                                                <i class="fas fa-clock"></i>
                                                {{ $lichHoc->gio_bat_dau ?: '--:--' }}
                                                @if($lichHoc->gio_ket_thuc)
                                                    - {{ $lichHoc->gio_ket_thuc }}
                                                @endif
                                            </span>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <a href="{{ route('hoc-vien.buoi-hoc.show', $lichHoc->id) }}" class="btn btn-sm btn-outline-primary">
                                                Xem buổi học
                                            </a>
                                            <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $lichHoc->khoa_hoc_id) }}" class="btn btn-sm btn-outline-secondary">
                                                Xem khóa học
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
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="empty-icon mb-3"><i class="fas fa-calendar-check"></i></div>
                                    <p class="text-muted mb-0">Chưa có buổi học nào trong khóa đang học.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="hd-card h-100">
                    <div class="hd-card-head d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h6><i class="fas fa-folder-open"></i> Tài liệu mới được công khai</h6>
                            <small>Chỉ hiển thị tài liệu giảng viên đã mở cho học viên.</small>
                        </div>
                        <span class="badge bg-light text-success">{{ $dashboardStats['tai_lieu_moi_7_ngay'] }} mới</span>
                    </div>
                    <div class="hd-card-body">
                        @forelse($taiLieuMoi as $taiLieu)
                            <div class="list-block">
                                <div class="d-flex justify-content-between gap-2 align-items-start">
                                    <div class="fw-semibold text-dark">{{ $taiLieu->tieu_de }}</div>
                                    <span class="badge bg-{{ $taiLieu->loai_color }}">{{ $taiLieu->loai_label }}</span>
                                </div>
                                <div class="small text-muted">
                                    {{ optional(optional($taiLieu->lichHoc)->khoaHoc)->ten_khoa_hoc ?: 'Chưa xác định khóa học' }}
                                </div>
                                <div class="small text-muted">
                                    {{ optional(optional($taiLieu->lichHoc)->moduleHoc)->ten_module ?: 'Chưa gán module' }}
                                    • {{ optional($taiLieu->created_at)->diffForHumans() }}
                                </div>
                                <p class="small text-muted mb-0 mt-2">
                                    {{ \Illuminate\Support\Str::limit($taiLieu->mo_ta ?: $taiLieu->file_status_message, 100) }}
                                </p>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', optional($taiLieu->lichHoc)->khoa_hoc_id) }}" class="btn btn-sm btn-outline-primary">
                                        Xem trong khóa học
                                    </a>
                                    @if($taiLieu->is_external)
                                        <a href="{{ $taiLieu->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                            Mở liên kết
                                        </a>
                                    @elseif($taiLieu->is_downloadable)
                                        <a href="{{ $taiLieu->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                            Xem file
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <div class="empty-icon mb-3"><i class="fas fa-folder-open"></i></div>
                                <p class="text-muted mb-0">Chưa có tài liệu mới được công khai.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ④ Bài kiểm tra cần chú ý ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">4</span>
                <div>
                    <h2><i class="fas fa-list-check"></i> Bài kiểm tra cần chú ý</h2>
                    <p>Các bài sắp mở hoặc đang mở trong những khóa học bạn đang theo học.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $baiKiemTraCanChuY->count() }}</strong> bài</span>
            </div>
        </header>

        <div class="hd-card">
            <div class="hd-card-body">
                <div class="row g-3">
                    @forelse($baiKiemTraCanChuY as $baiKiemTra)
                        <div class="col-lg-6">
                            <div class="course-progress-card h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1">{{ $baiKiemTra->tieu_de }}</h5>
                                        <div class="small text-muted">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Chưa xác định khóa học' }}</div>
                                        <div class="small text-muted">{{ $baiKiemTra->moduleHoc->ten_module ?? 'Toàn khóa' }}</div>
                                    </div>
                                    <span class="badge bg-{{ $baiKiemTra->access_status_color }}">{{ $baiKiemTra->access_status_label }}</span>
                                </div>
                                <div class="small text-muted mb-3">
                                    {{ $baiKiemTra->thoi_gian_lam_bai }} phút
                                    • {{ $baiKiemTra->ngay_mo ? $baiKiemTra->ngay_mo->format('d/m/Y H:i') : 'Mở ngay' }}
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-sm btn-outline-primary">
                                        Xem bài kiểm tra
                                    </a>
                                    @if($baiKiemTra->lich_hoc_id)
                                        <a href="{{ route('hoc-vien.buoi-hoc.show', $baiKiemTra->lich_hoc_id) }}" class="btn btn-sm btn-outline-secondary">
                                            Về buổi học
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <div class="empty-icon mb-3"><i class="fas fa-list-check"></i></div>
                            <p class="text-muted mb-0">Hiện chưa có bài kiểm tra sắp mở hoặc đang mở.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ⑤ Tiến độ theo khóa học ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">5</span>
                <div>
                    <h2><i class="fas fa-graduation-cap"></i> Tiến độ theo khóa học</h2>
                    <p>Tiến độ tối thiểu được tính theo số buổi đã hoàn thành trên tổng số buổi đã lên lịch.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $tongKhoaHoc }}</strong> khóa học</span>
            </div>
        </header>

        <div class="hd-card">
            <div class="hd-card-body">
                @if($tienDoKhoaHoc->isNotEmpty())
                    <div class="row g-4">
                        @foreach($tienDoKhoaHoc as $dong)
                            @php
                                $khoaHoc = $dong['khoa_hoc'];
                                $ghiDanh = $dong['ghi_danh'];
                            @endphp
                            <div class="col-xl-6">
                                <div class="course-progress-card h-100">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h5 class="fw-semibold mb-1">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                                            <div class="small text-muted">{{ $khoaHoc->ma_khoa_hoc ?: 'Chưa có mã khóa học' }}</div>
                                            <div class="small text-muted">
                                                {{ optional($khoaHoc->nhomNganh)->ten_nhom_nganh ?: 'Chưa cập nhật nhóm ngành' }}
                                            </div>
                                        </div>
                                        <span class="badge {{ $ghiDanh->trang_thai_badge }}">{{ $ghiDanh->trang_thai_label }}</span>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span>{{ $dong['buoi_hoan_thanh'] }}/{{ $dong['tong_buoi'] }} buổi đã học</span>
                                            <strong>{{ $dong['tien_do'] }}%</strong>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar bg-primary progress-live"
                                                 role="progressbar"
                                                 style="width: {{ $dong['tien_do'] }}%;"
                                                 aria-valuenow="{{ $dong['tien_do'] }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="row g-3 small">
                                        <div class="col-sm-6">
                                            <div class="info-chip">
                                                <span class="label">Buổi online</span>
                                                <strong>{{ $dong['buoi_online'] }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-chip">
                                                <span class="label">Tỷ lệ tham dự</span>
                                                <strong>{{ $dong['ty_le_tham_du'] !== null ? $dong['ty_le_tham_du'] . '%' : 'Chưa có' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="info-chip success-chip">
                                                <span class="label">Có mặt</span>
                                                <strong>{{ $dong['co_mat'] }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="info-chip warning-chip">
                                                <span class="label">Vào trễ</span>
                                                <strong>{{ $dong['vao_tre'] }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="info-chip danger-chip">
                                                <span class="label">Vắng mặt</span>
                                                <strong>{{ $dong['vang_mat'] }}</strong>
                                            </div>
                                        </div>
                                        @if($dong['ket_qua_hoc_tap'])
                                            <div class="col-sm-4">
                                                <div class="info-chip">
                                                    <span class="label">Điểm chuyên cần</span>
                                                    <strong>{{ $dong['ket_qua_hoc_tap']->diem_diem_danh !== null ? number_format((float) $dong['ket_qua_hoc_tap']->diem_diem_danh, 2) : 'Chưa có' }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="info-chip">
                                                    <span class="label">Điểm kiểm tra</span>
                                                    <strong>{{ $dong['ket_qua_hoc_tap']->diem_kiem_tra !== null ? number_format((float) $dong['ket_qua_hoc_tap']->diem_kiem_tra, 2) : 'Chưa có' }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="info-chip success-chip">
                                                    <span class="label">Tổng kết</span>
                                                    <strong>{{ $dong['ket_qua_hoc_tap']->diem_tong_ket !== null ? number_format((float) $dong['ket_qua_hoc_tap']->diem_tong_ket, 2) : 'Đang tính' }}</strong>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($dong['buoi_sap_toi'])
                                        <div class="next-session mt-3">
                                            <div class="small text-muted">Buổi học kế tiếp</div>
                                            <div class="fw-semibold text-dark">
                                                {{ $dong['buoi_sap_toi']->ngay_hoc->format('d/m/Y') }}
                                                • {{ $dong['buoi_sap_toi']->gio_bat_dau ?: '--:--' }}
                                            </div>
                                            <div class="small text-muted">{{ $dong['buoi_sap_toi']->hinh_thuc_label }}</div>
                                        </div>
                                    @endif

                                    <div class="mt-3 d-flex gap-2">
                                        <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoaHoc->id) }}" class="btn btn-sm btn-outline-primary">
                                            Xem chi tiết khóa học
                                        </a>
                                        @if($dong['module_breakdowns']->isNotEmpty())
                                            <button class="btn btn-sm btn-soft-info" type="button" data-bs-toggle="collapse" data-bs-target="#module-scores-{{ $khoaHoc->id }}">
                                                <i class="fas fa-list-ol me-1"></i> Bảng điểm chi tiết
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Bảng điểm chi tiết cho học viên --}}
                                    @if($dong['module_breakdowns']->isNotEmpty())
                                        <div class="collapse mt-3" id="module-scores-{{ $khoaHoc->id }}">
                                            <div class="p-3 bg-white rounded-4 border shadow-xs">
                                                <h6 class="fw-bold smaller text-muted text-uppercase mb-3">Kết quả chi tiết theo Module</h6>
                                                @foreach($dong['module_breakdowns'] as $mb)
                                                    @php
                                                        $bd = $mb['breakdown'];
                                                        $sm = $bd['summary'];
                                                    @endphp
                                                    <div class="module-score-card mb-3 p-3 rounded-3 border-start border-4 border-{{ $mb['is_finalized'] ? 'success' : 'primary' }} bg-light bg-opacity-10">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <div class="fw-bold text-dark">{{ $mb['module_name'] }}</div>
                                                            <span class="badge bg-{{ $mb['is_finalized'] ? 'success' : 'secondary' }}-soft text-{{ $mb['is_finalized'] ? 'success' : 'secondary' }} rounded-pill">
                                                                {{ $mb['is_finalized'] ? 'ĐÃ CHỐT ĐIỂM' : 'TẠM TÍNH' }}
                                                            </span>
                                                        </div>

                                                        <div class="row g-2 text-center mt-2">
                                                            <div class="col-4">
                                                                <div class="p-2 bg-white rounded border">
                                                                    <div class="smaller text-muted">Quá trình (A)</div>
                                                                    <div class="fw-bold">{{ number_format($sm['process_score'] ?: 0, 2) }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="p-2 bg-white rounded border">
                                                                    <div class="smaller text-muted">Thi (B)</div>
                                                                    <div class="fw-bold">{{ number_format($sm['module_exam_score'] ?: 0, 2) }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="p-2 bg-white rounded border border-primary">
                                                                    <div class="smaller text-muted">Tổng kết</div>
                                                                    <div class="fw-extrabold text-primary">{{ number_format($mb['final_score'] ?: 0, 2) }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <button class="btn btn-link btn-xs w-100 text-muted mt-2 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#exam-list-{{ $khoaHoc->id }}-{{ $mb['module_id'] }}">
                                                            Chi tiết bài thi & chuyên cần <i class="fas fa-chevron-down ms-1"></i>
                                                        </button>

                                                        <div class="collapse mt-2" id="exam-list-{{ $khoaHoc->id }}-{{ $mb['module_id'] }}">
                                                            <div class="smaller p-2 bg-white rounded border border-dashed">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span>Điểm danh:</span>
                                                                    <span class="fw-bold">{{ $bd['attendance']['so_buoi_tham_du'] }}/{{ $bd['attendance']['tong_so_buoi'] }} buổi</span>
                                                                </div>
                                                                <hr class="my-1">
                                                                @foreach($bd['exam_results'] as $er)
                                                                    <div class="d-flex justify-content-between mb-1">
                                                                        <span class="text-truncate me-2" style="max-width: 70%;">{{ $er['tieu_de'] }}:</span>
                                                                        <span class="fw-bold text-primary">{{ number_format($er['diem'], 2) }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-icon mb-3"><i class="fas fa-book-reader"></i></div>
                        <h6 class="fw-semibold">Chưa có dữ liệu tiến độ</h6>
                        <p class="text-muted mb-0">Khi học viên được ghi danh và khóa học có lịch học, tiến độ sẽ xuất hiện tại đây.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

@push('styles')
<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .hd-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .hd-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .hd-page .apx-section-title h2 i { color: #dc2626; }
    .hd-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .hd-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .hd-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .hd-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .hd-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        border-radius: 999px;
    }
    .hd-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .hd-status-badge i { font-size: 0.65rem; opacity: 0.85; }
    .hd-pending-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        animation: hdPendingPulse 1.6s ease-out infinite;
    }
    @keyframes hdPendingPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }
    .apx-welcome.hd-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.hd-welcome p i { color: #fef3c7; margin-right: 4px; }
    .hd-sep { opacity: 0.5; }
    .hd-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== HD card ===== */
    .hd-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .hd-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        background: linear-gradient(135deg, #f8fafc 0%, #fef2f2 100%);
    }
    .hd-card-head h6 {
        margin: 0 0 2px;
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .hd-card-head h6 i { color: #dc2626; font-size: 0.85rem; }
    .hd-card-head small {
        display: block;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 500;
    }
    .hd-card-body { padding: 18px; }

    /* ===== Existing inner styles giữ nguyên ===== */
    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
    }
    .activity-item:first-child { padding-top: 0; }
    .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
    .activity-icon {
        width: 42px; height: 42px; min-width: 42px;
        border-radius: 14px;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
    }

    .attendance-box {
        border-radius: 16px;
        padding: 1rem;
        text-align: center;
    }
    .attendance-success { background: #ecfdf5; color: #047857; }
    .attendance-warning { background: #fff7ed; color: #c2410c; }
    .attendance-danger  { background: #fef2f2; color: #b91c1c; }
    .attendance-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; margin-bottom: 0.25rem; }
    .attendance-label { font-size: 0.85rem; }

    .lesson-roadmap-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.6rem;
        margin-bottom: 0.9rem;
    }
    .lesson-roadmap-summary-item {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        padding: 0.65rem 0.75rem;
    }
    .lesson-roadmap-summary-item span {
        display: block; color: #64748b;
        font-size: 0.78rem; margin-bottom: 0.2rem;
    }
    .lesson-roadmap-summary-item strong {
        display: block; color: #0f172a;
        font-size: 1.25rem; line-height: 1;
    }
    .lesson-roadmap-summary-item.is-done    { background: #ecfdf5; border-color: #86efac; }
    .lesson-roadmap-summary-item.is-current { background: #eff6ff; border-color: #93c5fd; }
    .lesson-roadmap-summary-item.is-upcoming{ background: #f8fafc; border-color: #cbd5e1; }

    .lesson-roadmap-list {
        display: grid;
        gap: 0.65rem;
        max-height: min(52vh, 520px);
        overflow-y: auto;
        overscroll-behavior: contain;
        padding-right: 0.35rem;
        scrollbar-gutter: stable;
        scrollbar-width: thin;
        scrollbar-color: #93c5fd #eff6ff;
    }
    .lesson-roadmap-list::-webkit-scrollbar { width: 8px; }
    .lesson-roadmap-list::-webkit-scrollbar-track { background: #eff6ff; border-radius: 8px; }
    .lesson-roadmap-list::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 8px; }

    .lesson-roadmap-item {
        position: relative;
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr);
        gap: 0.6rem;
    }
    .lesson-roadmap-item:not(:last-child)::before {
        content: "";
        position: absolute;
        left: 15px; top: 32px; bottom: -0.65rem;
        border-left: 2px solid #dbeafe;
    }
    .lesson-roadmap-marker {
        position: relative; z-index: 1;
        width: 32px; height: 32px;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.82rem;
    }
    .lesson-roadmap-card {
        min-width: 0;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 0.7rem 0.8rem;
    }
    .lesson-roadmap-card .min-w-0 { min-width: 0; }
    .lesson-roadmap-eyebrow {
        display: flex; flex-wrap: wrap; gap: 0.3rem;
        color: #2563eb;
        font-size: 0.78rem; font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .lesson-roadmap-title {
        color: #0f172a;
        font-size: 0.94rem; font-weight: 700;
        margin-bottom: 0; overflow-wrap: anywhere;
    }
    .lesson-roadmap-meta {
        display: flex; flex-wrap: wrap;
        gap: 0.3rem 0.8rem;
        color: #64748b; font-size: 0.82rem;
        margin-top: 0.45rem;
    }
    .lesson-roadmap-meta span {
        display: inline-flex; align-items: center; gap: 0.35rem;
        min-width: 0; overflow-wrap: anywhere;
    }
    .lesson-roadmap-meta i {
        flex: 0 0 auto;
        color: #2563eb; font-size: 0.78rem;
    }
    .lesson-state-badge {
        border-radius: 8px;
        font-size: 0.72rem; font-weight: 800;
        line-height: 1; padding: 0.35rem 0.5rem;
        white-space: nowrap;
    }
    .lesson-state-done    { background: #dcfce7; color: #166534; }
    .lesson-state-current { background: #dbeafe; color: #1d4ed8; }
    .lesson-state-upcoming{ background: #f1f5f9; color: #475569; }
    .lesson-roadmap-item.is-done:not(:last-child)::before { border-color: #86efac; }
    .lesson-roadmap-item.is-done .lesson-roadmap-marker {
        background: #dcfce7; border-color: #86efac; color: #15803d;
    }
    .lesson-roadmap-item.is-done .lesson-roadmap-card {
        background: #f0fdf4; border-color: #bbf7d0;
    }
    .lesson-roadmap-item.is-current .lesson-roadmap-marker {
        background: #dbeafe; border-color: #60a5fa; color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }
    .lesson-roadmap-item.is-current .lesson-roadmap-card {
        border-color: #60a5fa;
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.1);
    }
    .lesson-roadmap-item.is-upcoming .lesson-roadmap-card { background: #ffffff; }

    .attendance-log, .list-block {
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
    }
    .attendance-log:first-of-type, .list-block:first-of-type { padding-top: 0; }
    .attendance-log:last-child, .list-block:last-child { padding-bottom: 0; border-bottom: none; }

    .course-progress-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        transition: all 0.2s ease;
    }
    .course-progress-card:hover {
        border-color: #fecaca;
        box-shadow: 0 12px 24px rgba(220, 38, 38, 0.08);
    }

    .progress-thin { height: 8px; border-radius: 999px; background: #e2e8f0; }

    .info-chip {
        height: 100%;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 0.9rem;
        display: flex; align-items: center; justify-content: space-between;
        gap: 0.75rem;
    }
    .info-chip .label { color: #64748b; }
    .success-chip { background: #ecfdf5; border-color: #a7f3d0; }
    .warning-chip { background: #fff7ed; border-color: #fed7aa; }
    .danger-chip  { background: #fef2f2; border-color: #fecaca; }

    .next-session { border-top: 1px dashed #cbd5e1; padding-top: 1rem; }

    .empty-icon {
        width: 64px; height: 64px;
        border-radius: 18px;
        background: #fef2f2;
        color: #dc2626;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
    }

    @media (max-width: 575.98px) {
        .lesson-roadmap-summary { grid-template-columns: 1fr; }
        .lesson-roadmap-list { max-height: 58vh; }
        .lesson-roadmap-item { grid-template-columns: 32px minmax(0, 1fr); gap: 0.65rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bars = document.querySelectorAll('.progress-live');
        if (!bars.length || typeof IntersectionObserver === 'undefined') {
            return;
        }
        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const bar = entry.target;
                const finalWidth = bar.style.width;
                bar.style.width = '0%';
                window.setTimeout(() => { bar.style.width = finalWidth; }, 120);
                currentObserver.unobserve(bar);
            });
        }, { threshold: 0.3 });
        bars.forEach((bar) => observer.observe(bar));
    });
</script>
@endpush
@endsection
