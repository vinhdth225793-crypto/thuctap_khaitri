@extends('layouts.app')

@section('title', 'Dashboard Giảng viên')

@section('content')
<div class="container-fluid">
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card vip-card border-0 shadow-sm overflow-hidden welcome-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2 fw-bold text-white">Chào mừng, {{ auth()->user()->ho_ten }}! 👨‍🏫</h2>
                            <p class="text-white-50 mb-0">Bạn có <span class="fw-bold text-white">{{ $stats['cho_xac_nhan'] }}</span> phân công mới đang chờ phản hồi.</p>
                            <div class="mt-3">
                                <span class="badge bg-white text-primary me-2">Giảng viên</span>
                                <span class="badge bg-success-soft text-white border border-white-50">Đang hoạt động</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end d-none d-md-block">
                            <i class="fas fa-chalkboard-teacher fa-5x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="vip-card p-3 border-0 shadow-sm bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-soft p-3 me-3">
                        <i class="fas fa-book-reader text-primary fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Đang giảng dạy</div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['dang_day'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="vip-card p-3 border-0 shadow-sm bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning-soft p-3 me-3">
                        <i class="fas fa-bell text-warning fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Chờ xác nhận</div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['cho_xac_nhan'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="vip-card p-3 border-0 shadow-sm bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-soft p-3 me-3">
                        <i class="fas fa-users text-success fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Tổng học viên</div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['tong_hoc_vien'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="vip-card p-3 border-0 shadow-sm bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-soft p-3 me-3">
                        <i class="fas fa-clock text-info fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Tổng giờ dạy</div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['so_gio_day'] }}h</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- New Assignments -->
        <div class="col-lg-7 mb-4">
            <div class="vip-card border-0 shadow-sm h-100">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-paper-plane me-2 text-warning"></i>Phân công mới</h5>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4">Khóa học / Module</th>
                                    <th class="text-center">Số buổi</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($phanCongMoi as $pc)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $pc->moduleHoc->ten_module }}</div>
                                            <div class="smaller text-muted">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $pc->moduleHoc->so_buoi ?? 0 }} buổi</span>
                                        </td>
                                        <td class="text-center pe-3">
                                            <div class="d-flex justify-content-center gap-1">
                                                <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $pc->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success px-3 fw-bold shadow-xs">Xác nhận</button>
                                                </form>
                                                <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-sm btn-outline-primary shadow-xs">Chi tiết</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-2 opacity-25"></i>
                                            <p class="mb-0">Tuyệt vời! Bạn không có phân công mới nào.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Tasks -->
        <div class="col-lg-5 mb-4">
            <div class="vip-card border-0 shadow-sm mb-4">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-alt me-2 text-primary"></i>Lớp học hôm nay</h5>
                </div>
                <div class="vip-card-body p-4 text-center">
                    <div class="text-muted small mb-3">Tính năng lịch biểu đang được đồng bộ hóa...</div>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-primary w-100 fw-bold">
                        <i class="fas fa-calendar-check me-2"></i>Xem lịch trình dạy học
                    </a>
                </div>
            </div>

            <div class="vip-card border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-rocket me-2 text-info"></i>Thao tác nhanh</h5>
                </div>
                <div class="vip-card-body p-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center border-2">
                                <i class="fas fa-chalkboard-teacher fa-lg mb-2"></i>
                                <span class="small fw-bold">Bài giảng</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-outline-warning w-100 py-3 d-flex flex-column align-items-center border-2">
                                <i class="fas fa-user-check fa-lg mb-2"></i>
                                <span class="small fw-bold">Điểm danh</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endsection
