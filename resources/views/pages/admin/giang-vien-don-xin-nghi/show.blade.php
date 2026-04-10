@extends('layouts.app')

@section('title', 'Chi tiết đơn xin nghỉ')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}">Đơn xin nghỉ</a></li>
            <li class="breadcrumb-item active">#{{ $leaveRequest->id }}</li>
        </ol>
    </nav>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h4 class="fw-bold mb-1">Đơn xin nghỉ #{{ $leaveRequest->id }}</h4>
                    <div class="text-muted">{{ $leaveRequest->giangVien?->nguoiDung?->ho_ten }} | {{ $leaveRequest->ngay_xin_nghi?->format('d/m/Y') }}</div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><div class="border rounded-3 p-3 h-100"><div class="small text-muted text-uppercase fw-bold mb-1">Khung nghỉ</div><div class="fw-bold">{{ $leaveRequest->schedule_range_label }}</div><div class="small text-muted">{{ $leaveRequest->tiet_range_label }}</div></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3 h-100"><div class="small text-muted text-uppercase fw-bold mb-1">Trạng thái</div><span class="badge bg-{{ $leaveRequest->trang_thai_color }}">{{ $leaveRequest->trang_thai_label }}</span>@if($leaveRequest->nguoiDuyet)<div class="small text-muted mt-2">Người duyệt: {{ $leaveRequest->nguoiDuyet->ho_ten }}</div>@endif</div></div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4">
                        <div class="small text-muted text-uppercase fw-bold mb-2">Lý do</div>
                        <div>{{ $leaveRequest->ly_do }}</div>
                    </div>

                    @if($leaveRequest->ghi_chu_phan_hoi)
                        <div class="border rounded-3 p-3 mb-4 bg-light"><div class="small text-muted text-uppercase fw-bold mb-2">Ghi chú phản hồi</div><div>{{ $leaveRequest->ghi_chu_phan_hoi }}</div></div>
                    @endif

                    <div class="border rounded-3 p-3 bg-warning-subtle border-warning-subtle">
                        <div class="fw-bold mb-1">Lưu ý sau khi duyệt</div>
                        <div class="small text-muted mb-0">Buổi học liên quan không tự động mất khỏi lịch. Admin cần chủ động đổi lịch hoặc thay giảng viên nếu khung nghỉ đã được duyệt.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4"><h5 class="mb-1 fw-bold">Thông tin liên quan</h5></div>
                <div class="card-body pt-0 small">
                    <div class="mb-3"><div class="text-muted text-uppercase fw-bold mb-1">Khóa học</div><div class="fw-bold">{{ $leaveRequest->khoaHoc?->ma_khoa_hoc ?: ($leaveRequest->lichHoc?->khoaHoc?->ma_khoa_hoc ?? '-') }}</div><div class="text-muted">{{ $leaveRequest->khoaHoc?->ten_khoa_hoc ?: ($leaveRequest->lichHoc?->khoaHoc?->ten_khoa_hoc ?? '-') }}</div></div>
                    <div class="mb-3"><div class="text-muted text-uppercase fw-bold mb-1">Module</div><div class="fw-bold">{{ $leaveRequest->moduleHoc?->ten_module ?: ($leaveRequest->lichHoc?->moduleHoc?->ten_module ?? '-') }}</div></div>
                    @if($leaveRequest->lichHoc)
                        <div class="mb-3"><div class="text-muted text-uppercase fw-bold mb-1">Buổi học gắn</div><div class="fw-bold">{{ $leaveRequest->lichHoc->schedule_range_label }}</div><a href="{{ route('admin.khoa-hoc.lich-hoc.index', $leaveRequest->lichHoc->khoa_hoc_id) }}" class="btn btn-sm btn-outline-primary mt-2">Mở planner</a></div>
                    @endif
                </div>
            </div>

            @if($leaveRequest->trang_thai === 'cho_duyet')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 px-4"><h5 class="mb-1 fw-bold">Xử lý đơn</h5></div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.giang-vien-don-xin-nghi.approve', $leaveRequest->id) }}" class="mb-3">
                            @csrf
                            <label class="form-label small fw-bold">Ghi chú khi duyệt</label>
                            <textarea name="ghi_chu_phan_hoi" rows="3" class="form-control mb-3" placeholder="Ví dụ: Đã duyệt, vui lòng phối hợp đổi lịch với phòng đào tạo..."></textarea>
                            <button type="submit" class="btn btn-success w-100 fw-bold">Duyệt đơn</button>
                        </form>
                        <form method="POST" action="{{ route('admin.giang-vien-don-xin-nghi.reject', $leaveRequest->id) }}">
                            @csrf
                            <label class="form-label small fw-bold">Ghi chú khi từ chối</label>
                            <textarea name="ghi_chu_phan_hoi" rows="3" class="form-control mb-3" placeholder="Nhập lý do từ chối nếu cần..."></textarea>
                            <button type="submit" class="btn btn-outline-danger w-100 fw-bold">Từ chối đơn</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
