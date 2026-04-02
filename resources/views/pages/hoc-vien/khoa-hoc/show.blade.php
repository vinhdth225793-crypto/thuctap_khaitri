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
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-1 fw-semibold">Buổi học & thời khóa biểu</h5>
                    <p class="text-muted small mb-0">Mỗi buổi là một điểm vào riêng để mở tài liệu, live room và bài kiểm tra liên quan.</p>
                </div>
                <div class="card-body">
                    @forelse($courseSchedules as $lichHoc)
                        <div class="content-row">
                            <div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-light text-dark border">Buổi {{ $lichHoc->buoi_so ?: '#' }}</span>
                                    <span class="badge bg-{{ $lichHoc->trang_thai_color }}">{{ $lichHoc->trang_thai_label }}</span>
                                    <span class="badge bg-{{ $lichHoc->hinh_thuc_color }}">{{ $lichHoc->hinh_thuc_label }}</span>
                                </div>
                                <div class="fw-semibold text-dark">{{ $lichHoc->moduleHoc->ten_module ?? 'Chưa gán module' }}</div>
                                <div class="small text-muted">
                                    {{ $lichHoc->ngay_hoc?->format('d/m/Y') ?: 'Chưa có ngày học' }} • {{ substr((string) $lichHoc->gio_bat_dau, 0, 5) ?: '--:--' }}
                                    @if($lichHoc->gio_ket_thuc)
                                        - {{ substr((string) $lichHoc->gio_ket_thuc, 0, 5) }}
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">{{ $lichHoc->taiNguyen->count() }} tài nguyên • {{ $lichHoc->baiGiangs->count() }} bài giảng</div>
                            </div>
                            <a href="{{ route('hoc-vien.buoi-hoc.show', $lichHoc->id) }}" class="btn btn-sm btn-outline-primary">
                                Xem chi tiết buổi học
                            </a>
                        </div>
                    @empty
                        <div class="empty-state-box">Khóa học này chưa có buổi học nào được lên lịch.</div>
                    @endforelse
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
            <div class="card vip-card border-0 shadow-sm sticky-top" style="top: 1.5rem;">
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
    .empty-state-box { border: 1px dashed #cbd5e1; border-radius: 18px; padding: 1.25rem; background: #f8fafc; color: #64748b; text-align: center; }
</style>
@endpush
@endsection
