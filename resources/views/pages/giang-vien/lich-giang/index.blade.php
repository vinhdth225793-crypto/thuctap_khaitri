@extends('layouts.app')

@section('title', 'Lịch dạy của tôi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}">Giảng viên</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lịch giảng</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1">Lịch dạy của tôi</h4>
            <p class="text-muted mb-0">Theo dõi thời khóa biểu và quản lý đơn xin nghỉ giảng.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-primary shadow-sm px-3">
                <i class="fas fa-paper-plane me-2"></i>Gửi đơn xin nghỉ
            </a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-primary-subtle text-primary rounded-3 p-3 me-3">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Buổi dạy sắp tới</div>
                        <div class="h3 fw-bold mb-0">{{ $stats['upcoming_schedules'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-warning-subtle text-warning rounded-3 p-3 me-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Đơn chờ duyệt</div>
                        <div class="h3 fw-bold mb-0 text-warning">{{ $stats['leave_requests_pending'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-success-subtle text-success rounded-3 p-3 me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Đơn đã duyệt</div>
                        <div class="h3 fw-bold mb-0 text-success">{{ $stats['leave_requests_approved'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.teacher-schedule-board', [
        'scheduleView' => $scheduleView,
    ])

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Chi tiết buổi dạy sắp tới</h5>
                        <p class="text-muted small mb-0">Danh sách các buổi giảng dạy trong thời gian tới.</p>
                    </div>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-sm btn-light border px-3">
                        Xem tất cả khóa học
                    </a>
                </div>
                <div class="card-body p-4 pt-2">
                    @forelse($upcomingSchedules as $schedule)
                        <div class="p-3 mb-3 border rounded-3 bg-light-hover transition-all">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="fw-bold text-primary mb-1">{{ $schedule->moduleHoc?->ten_module }}</h6>
                                    <div class="small text-muted"><i class="fas fa-graduation-cap me-1"></i> {{ $schedule->khoaHoc?->ten_khoa_hoc }}</div>
                                </div>
                                <span class="badge bg-white text-dark border shadow-sm py-2 px-3">
                                    <i class="far fa-clock me-1 text-primary"></i> {{ $schedule->schedule_range_label }}
                                </span>
                            </div>
                            
                            <div class="d-flex align-items-center gap-4 small text-muted mb-3">
                                <span><i class="far fa-calendar-alt me-1 text-primary"></i> {{ $schedule->ngay_hoc?->format('d/m/Y') }}</span>
                                <span><i class="far fa-clock me-1 text-primary"></i> {{ substr((string) $schedule->gio_bat_dau, 0, 5) }} - {{ substr((string) $schedule->gio_ket_thuc, 0, 5) }}</span>
                                @if($schedule->hinh_thuc)
                                    <span><i class="fas fa-map-marker-alt me-1 text-primary"></i> {{ $schedule->hinh_thuc_label }}</span>
                                @endif
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ $schedule->phan_cong_id ? route('giang-vien.khoa-hoc.show', $schedule->phan_cong_id) : route('giang-vien.khoa-hoc') }}" class="btn btn-sm btn-primary px-3">
                                    <i class="fas fa-external-link-alt me-1"></i> Vào lớp học
                                </a>
                                <a href="{{ route('giang-vien.don-xin-nghi.create', ['lich_hoc_id' => $schedule->id]) }}" class="btn btn-sm btn-outline-warning px-3">
                                    <i class="fas fa-user-clock me-1"></i> Xin nghỉ buổi này
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <img src="{{ asset('images/empty-schedule.svg') }}" alt="Empty" style="width: 120px;" class="mb-3 opacity-50">
                            <p class="text-muted">Chưa có buổi học nào được sắp xếp trong tương lai.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Đơn xin nghỉ gần đây</h5>
                        <p class="text-muted small mb-0">Theo dõi trạng thái phản hồi từ quản trị viên.</p>
                    </div>
                    <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-sm btn-light border px-3">Xem tất cả</a>
                </div>
                <div class="card-body p-4 pt-2">
                    @forelse($recentLeaveRequests as $item)
                        <div class="p-3 mb-3 border rounded-3 border-start border-4 border-start-{{ $item->trang_thai_color }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-bold text-dark">{{ $item->ngay_xin_nghi?->format('d/m/Y') }}</div>
                                <span class="badge rounded-pill bg-{{ $item->trang_thai_color }}-subtle text-{{ $item->trang_thai_color }} border border-{{ $item->trang_thai_color }}-subtle px-3 py-2">
                                    {{ $item->trang_thai_label }}
                                </span>
                            </div>
                            <div class="small fw-semibold text-primary mb-2">
                                {{ $item->moduleHoc?->ten_module ?: 'Nghỉ cả ngày' }}
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-info-circle me-1"></i> {{ $item->ly_do }}
                            </div>
                            @if($item->ghi_chu_phan_hoi)
                                <div class="p-2 mt-2 bg-light rounded small border-start border-3 border-secondary">
                                    <strong>Phản hồi:</strong> {{ $item->ghi_chu_phan_hoi }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <p class="text-muted">Bạn chưa gửi đơn xin nghỉ nào gần đây.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-hover:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6 !important;
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
    .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
    .text-primary { color: #0d6efd !important; }
    .text-warning { color: #ffc107 !important; }
    .text-success { color: #198754 !important; }
</style>
@endsection
