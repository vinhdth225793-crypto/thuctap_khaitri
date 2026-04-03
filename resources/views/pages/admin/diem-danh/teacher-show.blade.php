@extends('layouts.app')

@section('title', 'Chi tiết điểm danh giảng viên')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.diem-danh.index', ['tab' => 'giang-vien']) }}">Điểm danh</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết giảng viên</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-user-clock me-2 text-primary"></i>Chi tiết điểm danh giảng viên
            </h4>
            <div class="text-muted">
                {{ $teacher->nguoiDung?->ho_ten ?? 'N/A' }} | {{ $schedule->khoaHoc?->ten_khoa_hoc }} | Buổi #{{ $schedule->buoi_so }}
            </div>
        </div>
        <a href="{{ route('admin.diem-danh.index', ['tab' => 'giang-vien']) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Khóa học</div>
                    <div class="fw-bold fs-5">{{ $schedule->khoaHoc?->ten_khoa_hoc ?? 'N/A' }}</div>
                    <div class="small text-muted mt-1">{{ $schedule->moduleHoc?->ten_module ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Buổi học</div>
                    <div class="fw-bold fs-5">#{{ $schedule->buoi_so }}</div>
                    <div class="small text-muted mt-1">
                        {{ $schedule->ngay_hoc?->format('d/m/Y') }} | {{ \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->gio_ket_thuc)->format('H:i') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Giảng viên</div>
                    <div class="fw-bold fs-5">{{ $teacher->nguoiDung?->ho_ten ?? 'N/A' }}</div>
                    <div class="small text-muted mt-1">Hình thức: {{ $schedule->hinh_thuc_label }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Trạng thái</div>
                    @php
                        $statusLabel = $attendance?->trang_thai_label ?? 'Chưa bắt đầu';
                        $statusColor = $attendance?->trang_thai_color ?? 'secondary';
                    @endphp
                    <div class="mt-2">
                        <span class="badge bg-{{ $statusColor }} px-3 py-2">{{ $statusLabel }}</span>
                    </div>
                    <div class="small text-muted mt-2">
                        {{ $attendance?->tong_thoi_luong_day_phut ? $attendance->tong_thoi_luong_day_phut . ' phút thực tế' : 'Chưa có thời lượng thực tế' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Log điểm danh giảng viên</h5>
                </div>
                <div class="card-body">
                    @if($attendance)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <div class="small text-muted text-uppercase">Bắt đầu dạy</div>
                                    <div class="fw-bold">{{ $attendance->thoi_gian_bat_dau_day?->format('H:i d/m/Y') ?? '--' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <div class="small text-muted text-uppercase">Kết thúc dạy</div>
                                    <div class="fw-bold">{{ $attendance->thoi_gian_ket_thuc_day?->format('H:i d/m/Y') ?? '--' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <div class="small text-muted text-uppercase">Mở live</div>
                                    <div class="fw-bold">{{ $attendance->thoi_gian_mo_live?->format('H:i d/m/Y') ?? '--' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <div class="small text-muted text-uppercase">Tắt live</div>
                                    <div class="fw-bold">{{ $attendance->thoi_gian_tat_live?->format('H:i d/m/Y') ?? '--' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="small text-muted text-uppercase mb-2">Ghi chú / log vận hành</div>
                            <div class="border rounded-3 bg-light p-3 small text-muted">
                                {!! nl2br(e($attendance->ghi_chu ?: 'Chưa có ghi chú log.')) !!}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            Buổi học này chưa có log điểm danh giảng viên. Trạng thái hiện tại là <strong>chưa bắt đầu</strong>.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Liên kết live room</h5>
                </div>
                <div class="card-body">
                    @php
                        $liveLectures = $schedule->baiGiangs->filter(fn($lecture) => $lecture->isLive() && $lecture->phongHocLive);
                    @endphp
                    @forelse($liveLectures as $lecture)
                        @php $room = $lecture->phongHocLive; @endphp
                        <div class="border rounded-3 p-3 mb-3 bg-light">
                            <div class="fw-bold text-dark">{{ $lecture->tieu_de }}</div>
                            <div class="small text-muted mt-1">Nền tảng: {{ $room->platform_label }}</div>
                            <div class="small text-muted">Trạng thái phòng: {{ $room->timeline_trang_thai_label }}</div>
                            <div class="small text-muted">Bắt đầu dự kiến: {{ $room->thoi_gian_bat_dau?->format('H:i d/m/Y') }}</div>
                            <div class="small text-muted">Số người tham gia: {{ $room->participant_count }}</div>
                        </div>
                    @empty
                        <div class="alert alert-light border mb-0">
                            Buổi học này chưa gắn live room nội bộ. Nếu giảng viên dùng link ngoài, hệ thống sẽ fallback thời gian live theo thao tác điểm danh.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
