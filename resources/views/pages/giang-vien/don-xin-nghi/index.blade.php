@extends('layouts.app')

@section('title', 'Đơn xin nghỉ của tôi')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Leave Requests</div>
            <h4 class="fw-bold mb-1">Đơn xin nghỉ / phản hồi lịch dạy</h4>
            <div class="text-muted">Gửi đơn cho từng buổi học đã được sắp hoặc xin off theo ngày, buổi, tiết.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.lich-giang.index') }}" class="btn btn-outline-secondary">Về lịch dạy</a>
            <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-primary fw-bold">Tạo đơn mới</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('giang-vien.don-xin-nghi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="cho_duyet" @selected(($filters['trang_thai'] ?? null) === 'cho_duyet')>Chờ duyệt</option>
                        <option value="da_duyet" @selected(($filters['trang_thai'] ?? null) === 'da_duyet')>Đã duyệt</option>
                        <option value="tu_choi" @selected(($filters['trang_thai'] ?? null) === 'tu_choi')>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill fw-bold">Lọc</button>
                    <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-light border flex-fill">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($leaveRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Ngay</th>
                                <th>Khung nghỉ</th>
                                <th>Khóa học / Module</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th class="pe-4">Phản hồi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequests as $item)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $item->ngay_xin_nghi?->format('d/m/Y') }}</td>
                                    <td>
                                        <div>{{ $item->schedule_range_label }}</div>
                                        <div class="small text-muted">{{ $item->tiet_range_label }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $item->khoaHoc?->ma_khoa_hoc ?: ($item->lichHoc?->khoaHoc?->ma_khoa_hoc ?? 'Không gắn buổi học') }}</div>
                                        <div class="small text-muted">{{ $item->moduleHoc?->ten_module ?: ($item->lichHoc?->moduleHoc?->ten_module ?? '-') }}</div>
                                    </td>
                                    <td class="small text-muted">{{ $item->ly_do }}</td>
                                    <td><span class="badge bg-{{ $item->trang_thai_color }}">{{ $item->trang_thai_label }}</span></td>
                                    <td class="pe-4 small text-muted">{{ $item->ghi_chu_phan_hoi ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-top">
                    {{ $leaveRequests->links() }}
                </div>
            @else
                <div class="p-5 text-center text-muted">Chưa có đơn xin nghỉ nào phù hợp bộ lọc hiện tại.</div>
            @endif
        </div>
    </div>
</div>
@endsection
