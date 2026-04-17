@extends('layouts.app')

@section('title', $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}">Khóa học của tôi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $khoaHoc->ten_khoa_hoc }}</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge rounded-pill bg-light text-primary border">{{ $khoaHoc->ma_khoa_hoc }}</span>
                <span class="badge rounded-pill bg-light text-dark border">{{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'Chưa cập nhật nhóm ngành' }}</span>
                <span class="badge rounded-pill {{ $ghiDanh->trang_thai_badge }}">{{ $ghiDanh->trang_thai_label }}</span>
                <span class="badge rounded-pill bg-{{ $khoaHoc->trang_thai_hoc_tap_badge }}">{{ $khoaHoc->trang_thai_hoc_tap_label }}</span>
            </div>
            <h2 class="fw-bold mb-2">{{ $khoaHoc->ten_khoa_hoc }}</h2>
            <p class="text-muted mb-0">{{ $khoaHoc->mo_ta_ngan ?: 'Theo dõi module, buổi học, tài liệu, live room và bài kiểm tra của khóa học này tại một nơi.' }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('hoc-vien.hoat-dong-tien-do') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-line me-2"></i>Hoạt động & tiến độ
            </a>
            <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn btn-primary">
                <i class="fas fa-list-check me-2"></i>Danh sách bài kiểm tra
            </a>
        </div>
    </div>

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

            <section id="tai-lieu" class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Bài giảng & tài liệu đã công bố</h5>
                    <p class="text-muted small mb-0">Chỉ hiển thị nội dung đã được mở cho học viên trong khóa học này.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($publishedLectures->take(6) as $baiGiang)
                            <div class="col-md-6">
                                <div class="module-card h-100">
                                    <div class="small text-muted mb-2">{{ $baiGiang->moduleHoc->ten_module ?? 'Chưa gán module' }}</div>
                                    <h6 class="fw-semibold mb-2">{{ $baiGiang->tieu_de }}</h6>
                                    <p class="small text-muted mb-3">{{ \Illuminate\Support\Str::limit($baiGiang->mo_ta ?: 'Nội dung đã được công bố cho học viên.', 110) }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ $baiGiang->isLive() && $baiGiang->phongHocLive ? route('hoc-vien.live-room.show', $baiGiang->id) : route('hoc-vien.bai-giang.show', $baiGiang->id) }}" class="btn btn-sm btn-outline-primary">
                                            {{ $baiGiang->isLive() ? 'Vào live room' : 'Xem bài giảng' }}
                                        </a>
                                        @if($baiGiang->lich_hoc_id)
                                            <a href="{{ route('hoc-vien.buoi-hoc.show', $baiGiang->lich_hoc_id) }}" class="btn btn-sm btn-outline-secondary">Buổi học</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><div class="empty-state-box">Chưa có bài giảng nào được công bố cho khóa học này.</div></div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section id="bai-kiem-tra" class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Bài kiểm tra đã phát hành</h5>
                    <p class="text-muted small mb-0">Các bài kiểm tra được lọc theo đúng khóa học mà học viên đang theo học.</p>
                </div>
                <div class="card-body">
                    @forelse($publishedExams as $baiKiemTra)
                        <div class="content-row">
                            <div>
                                <div class="fw-semibold text-dark">{{ $baiKiemTra->tieu_de }}</div>
                                <div class="small text-muted">{{ $baiKiemTra->moduleHoc->ten_module ?? 'Toàn khóa' }} • {{ $baiKiemTra->access_status_label }}</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                @if($baiKiemTra->lich_hoc_id)
                                    <a href="{{ route('hoc-vien.buoi-hoc.show', $baiKiemTra->lich_hoc_id) }}" class="btn btn-sm btn-outline-secondary">Buổi học</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-box">Chưa có bài kiểm tra nào được phát hành cho khóa học này.</div>
                    @endforelse
                </div>
            </section>

            <section id="tien-do" class="card vip-card border-0 shadow-sm">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Tiến độ & kết quả học tập</h5>
                    <p class="text-muted small mb-0">Tổng hợp theo buổi học, module và kết quả học tập hiện có của khóa.</p>
                </div>
                <div class="card-body">
                    @if($ketQuaHocTap)
                        <div class="row g-3">
                            <div class="col-md-4"><div class="stat-box"><div class="stat-box__label">Điểm chuyên cần</div><div class="stat-box__value">{{ $ketQuaHocTap->diem_diem_danh !== null ? number_format((float) $ketQuaHocTap->diem_diem_danh, 2) : 'Chưa có' }}</div></div></div>
                            <div class="col-md-4"><div class="stat-box"><div class="stat-box__label">Điểm kiểm tra</div><div class="stat-box__value">{{ $ketQuaHocTap->diem_kiem_tra !== null ? number_format((float) $ketQuaHocTap->diem_kiem_tra, 2) : 'Chưa có' }}</div></div></div>
                            <div class="col-md-4"><div class="stat-box"><div class="stat-box__label">Điểm tổng kết</div><div class="stat-box__value">{{ $ketQuaHocTap->diem_tong_ket !== null ? number_format((float) $ketQuaHocTap->diem_tong_ket, 2) : 'Đang tính' }}</div></div></div>
                        </div>
                    @else
                        <div class="empty-state-box">Kết quả học tập tổng hợp sẽ xuất hiện khi hệ thống có đủ dữ liệu điểm danh và bài kiểm tra.</div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <div class="card vip-card border-0 shadow-sm course-quick-card">
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
        </div>
    </div>
</div>

@push('styles')
<style>
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
@endsection
