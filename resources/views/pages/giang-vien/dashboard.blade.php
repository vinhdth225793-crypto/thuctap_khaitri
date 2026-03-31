@extends('layouts.app')

@section('title', 'Trung tâm điều hành Giảng viên')

@section('content')
<div class="container-fluid py-4">
    <!-- Banner chào mừng -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2 fw-bold text-white">Xin chào, {{ auth()->user()->ho_ten }}!</h2>
                            <p class="text-white-50 mb-0">Chào mừng bạn đến với trung tâm điều hành giảng dạy. Hôm nay bạn có <span class="fw-bold text-white">{{ $lichHomNay->count() }}</span> buổi dạy.</p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-light btn-sm fw-bold px-3">
                                    <i class="fas fa-file-signature me-1"></i> Tạo / Cấu hình đề
                                </a>
                                <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-outline-light btn-sm fw-bold px-3">
                                    <i class="fas fa-marker me-1"></i> Chấm tự luận
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end d-none d-md-block">
                            <i class="fas fa-chalkboard-teacher fa-5x opacity-25"></i>
                        </div>
                    </div>
                    <div class="small text-white-50 mt-3">
                        Vào Lộ trình giảng dạy, chọn Vào dạy, sau đó bấm Tạo bài kiểm tra hoặc Cấu hình đề.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chỉ số thống kê -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Đang giảng dạy</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['dang_day'] }} lớp</div>
                        </div>
                        <i class="fas fa-book-reader fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Buổi sắp tới</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['buoi_sap_toi'] }} buổi</div>
                        </div>
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng học viên</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['tong_hoc_vien'] }} người</div>
                        </div>
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đơn chờ duyệt</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['don_xin_nghi_cho_duyet'] }} đơn</div>
                        </div>
                        <i class="fas fa-envelope-open-text fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cột trái: Lịch dạy và Phân công -->
        <div class="col-lg-8 mb-4">
            <!-- Lịch dạy hôm nay -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-clock me-2 text-primary"></i>Lịch dạy hôm nay</h5>
                    <a href="{{ route('giang-vien.lich-giang.index') }}" class="btn btn-sm btn-outline-primary">Xem toàn bộ lịch</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Thời gian</th>
                                    <th>Khóa học / Module</th>
                                    <th class="text-center">Hình thức</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lichHomNay as $lich)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ substr((string) $lich->gio_bat_dau, 0, 5) }} - {{ substr((string) $lich->gio_ket_thuc, 0, 5) }}</div>
                                            <div class="text-muted small">{{ $lich->tiet_range_label }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $lich->moduleHoc->ten_module }}</div>
                                            <div class="small text-muted">{{ $lich->khoaHoc->ten_khoa_hoc }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $lich->hinh_thuc_color }}-subtle text-{{ $lich->hinh_thuc_color }} border border-{{ $lich->hinh_thuc_color }}-subtle">
                                                {{ $lich->hinh_thuc_label }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <a href="{{ route('giang-vien.diem-danh.index', ['lich_hoc_id' => $lich->id]) }}" class="btn btn-sm btn-primary" title="Điểm danh"><i class="fas fa-user-check"></i></a>
                                                <a href="{{ route('giang-vien.khoa-hoc.show', $lich->khoa_hoc_id) }}" class="btn btn-sm btn-outline-primary" title="Vào lớp"><i class="fas fa-external-link-alt"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">Hôm nay bạn không có lịch dạy nào.</div>
                                            <a href="{{ route('giang-vien.lich-giang.index') }}" class="btn btn-sm btn-link">Xem lịch tuần này</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tiến độ các lớp đang dạy -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks me-2 text-info"></i>Tiến độ các lớp đang dạy</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Khóa học / Module</th>
                                    <th>Tiến độ học tập</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-end pe-4">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lopDangDay as $pc)
                                    @php $snapshot = $pc->moduleHoc->learning_progress_snapshot; @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $pc->moduleHoc->ten_module }}</div>
                                            <div class="small text-muted">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</div>
                                        </td>
                                        <td style="min-width: 200px;">
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $snapshot['progress_percent'] }}%"></div>
                                                </div>
                                                <span class="ms-2 small fw-bold">{{ $snapshot['completed_schedules'] }}/{{ $snapshot['valid_schedules'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $pc->moduleHoc->trang_thai_hoc_tap_badge }}-subtle text-{{ $pc->moduleHoc->trang_thai_hoc_tap_badge }} border border-{{ $pc->moduleHoc->trang_thai_hoc_tap_badge }}-subtle">
                                                {{ $pc->moduleHoc->trang_thai_hoc_tap_label }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-sm btn-light border"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Bạn chưa nhận dạy lớp nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thao tác nhanh và Thông báo -->
        <div class="col-lg-4 mb-4">
            <!-- Thao tác nhanh -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-bolt me-2 text-warning"></i>Thao tác nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('giang-vien.lich-giang.index') }}" class="card bg-light border-0 text-center p-3 text-decoration-none transition-hover h-100">
                                <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                <div class="small fw-bold text-dark">Lịch dạy</div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="card bg-light border-0 text-center p-3 text-decoration-none transition-hover h-100">
                                <i class="fas fa-paper-plane fa-2x text-warning mb-2"></i>
                                <div class="small fw-bold text-dark">Xin nghỉ</div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('giang-vien.cham-diem.index') }}" class="card bg-light border-0 text-center p-3 text-decoration-none transition-hover h-100">
                                <i class="fas fa-star fa-2x text-success mb-2"></i>
                                <div class="small fw-bold text-dark">Chấm điểm</div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('giang-vien.khoa-hoc') }}" class="card bg-light border-0 text-center p-3 text-decoration-none transition-hover h-100">
                                <i class="fas fa-file-signature fa-2x text-danger mb-2"></i>
                                <div class="small fw-bold text-dark">Lớp dạy</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phân công mới chờ xác nhận -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-bell me-2 text-danger"></i>Phân công mới ({{ $phanCongMoi->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($phanCongMoi as $pc)
                            <div class="list-group-item p-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $pc->moduleHoc->ten_module }}</div>
                                        <div class="text-muted x-small">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</div>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning small">{{ $pc->moduleHoc->so_buoi }} buổi</span>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $pc->id) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <input type="hidden" name="hanh_dong" value="da_nhan">
                                        <button type="submit" class="btn btn-sm btn-success w-100 fw-bold">Nhận dạy</button>
                                    </form>
                                    <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-sm btn-light border flex-grow-1">Chi tiết</a>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">Không có phân công mới.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-xs { font-size: .7rem; }
    .x-small { font-size: 0.75rem; }
    .transition-hover { transition: all 0.2s ease-in-out; }
    .transition-hover:hover { transform: translateY(-3px); background-color: #e9ecef !important; }
    .bg-primary-subtle { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-subtle { background-color: rgba(28, 200, 138, 0.1); }
    .bg-info-subtle { background-color: rgba(54, 185, 204, 0.1); }
    .bg-warning-subtle { background-color: rgba(246, 194, 62, 0.1); }
    .bg-danger-subtle { background-color: rgba(231, 74, 59, 0.1); }
</style>
@endsection
