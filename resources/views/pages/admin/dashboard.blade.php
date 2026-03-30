@extends('layouts.app')

@section('title', 'Dashboard - Quản trị viên')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-muted small mb-2">
            <i class="fas fa-home me-1"></i> Admin > Dashboard
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-dark">Hệ thống Quản trị Đào tạo</h3>
                        <div class="text-muted fw-bold">
                            <i class="far fa-calendar-alt me-1"></i> {{ now()->format('d/m/Y') }}
                        </div>
                    </div>

                    <!-- Section: Người dùng (Stat Boxes) -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="small-box bg-info shadow-sm">
                                <div class="inner p-3">
                                    <h3 class="fw-bold">{{ $tongNguoiDung }}</h3>
                                    <p class="mb-0">Tổng người dùng</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users opacity-50"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-success shadow-sm">
                                <div class="inner p-3">
                                    <h3 class="fw-bold">{{ $tongHocVien }}</h3>
                                    <p class="mb-0">Học viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-graduate opacity-50"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-warning shadow-sm text-dark">
                                <div class="inner p-3">
                                    <h3 class="fw-bold">{{ $tongGiangVien }}</h3>
                                    <p class="mb-0">Giảng viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chalkboard-teacher opacity-50"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-danger shadow-sm">
                                <div class="inner p-3">
                                    <h3 class="fw-bold">{{ $tongAdmin }}</h3>
                                    <p class="mb-0">Quản trị viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-shield opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 1 — Phase 5: Đào tạo & Module (4 Stat Cards) -->
                    <div class="row g-3 mb-4 mt-3">
                        <!-- Nhóm ngành -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold text-uppercase">Nhóm ngành</p>
                                            <h3 class="mb-0 fw-bold">{{ $stats['tong_nhom_nganh'] }}</h3>
                                            <small class="text-success fw-bold">{{ $stats['nhom_nganh_hoat_dong'] }} Active</small>
                                        </div>
                                        <div class="align-self-center text-primary opacity-25">
                                            <i class="fas fa-book fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Khóa học -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold text-uppercase">Khóa học</p>
                                            <h3 class="mb-0 fw-bold">{{ $stats['tong_khoa_hoc'] }}</h3>
                                            <small class="text-success fw-bold">{{ $stats['khoa_hoc_hoat_dong'] }} Đang mở</small>
                                        </div>
                                        <div class="align-self-center text-success opacity-25">
                                            <i class="fas fa-graduation-cap fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Module chưa có GV -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold text-uppercase">Trống giảng viên</p>
                                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['module_chua_co_gv'] }}</h3>
                                            <small class="text-muted fw-bold">Module cần phân công</small>
                                        </div>
                                        <div class="align-self-center text-warning opacity-50">
                                            <i class="fas fa-exclamation-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Phân công chờ XN -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold text-uppercase">Chờ xác nhận</p>
                                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['phan_cong_cho_xn'] }}</h3>
                                            <small class="text-muted fw-bold">Yêu cầu chưa phản hồi</small>
                                        </div>
                                        <div class="align-self-center text-danger opacity-25">
                                            <i class="fas fa-user-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 — Phase 5: Bảng theo dõi đào tạo -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold text-uppercase">Lich giang vien</p>
                                            <h5 class="fw-bold mb-1">Admin sap lich theo khung chuan va xu ly don xin nghi tap trung</h5>
                                            <div class="small text-muted">Flow moi bo phan dang ky lich ranh chu dong. Admin se xem lich day, duyet don nghi va chu dong xu ly dieu chinh neu can.</div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('admin.giang-vien.index') }}" class="btn btn-outline-primary btn-sm fw-bold">
                                                <i class="fas fa-calendar-week me-1"></i> Giang vien va lich day
                                            </a>
                                            <a href="{{ route('admin.khoa-hoc.index') }}" class="btn btn-outline-success btn-sm fw-bold">
                                                <i class="fas fa-calendar-check me-1"></i> Di den sap lich
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-4">
                                            <div class="bg-light rounded p-3 h-100">
                                                <div class="small text-muted text-uppercase fw-bold">Giang vien co lich sap toi</div>
                                                <div class="fs-3 fw-bold text-success">{{ $stats['giang_vien_co_lich_day_tuong_lai'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="bg-light rounded p-3 h-100">
                                                <div class="small text-muted text-uppercase fw-bold">Don xin nghi cho duyet</div>
                                                <div class="fs-3 fw-bold text-primary">{{ $stats['don_xin_nghi_cho_duyet'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="bg-light rounded p-3 h-100">
                                                <div class="small text-muted text-uppercase fw-bold">Giang vien can xu ly</div>
                                                <div class="fs-3 fw-bold text-warning">{{ $stats['giang_vien_can_xu_ly_don_nghi'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <p class="text-muted mb-1 small fw-bold text-uppercase">Flow moi</p>
                                    <div class="small text-muted mb-2">1. Admin xep lich trong khung Thu 2 - Thu 6, 08:00 - 20:00.</div>
                                    <div class="small text-muted mb-2">2. Giang vien xem thoi khoa bieu va gui don xin nghi neu ban.</div>
                                    <div class="small text-muted mb-3">3. Admin duyet don va chu dong doi lich hoac thay giang vien neu can.</div>
                                    <a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="btn btn-primary btn-sm fw-bold w-100">
                                        Mo giao dien don xin nghi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-2">
                        <!-- Bảng trái: Phân công chờ xác nhận -->
                        <div class="col-md-6">
                            <div class="vip-card h-100 border-0 shadow-sm">
                                <div class="vip-card-header bg-white py-3">
                                    <h6 class="vip-card-title mb-0 fw-bold">
                                        <i class="fas fa-clock text-warning me-2"></i>
                                        Phân công chờ xác nhận ({{ $stats['phan_cong_cho_xn'] }})
                                    </h6>
                                </div>
                                <div class="vip-card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="bg-light">
                                                <tr class="smaller">
                                                    <th class="ps-3 py-2">Module / Khóa học</th>
                                                    <th>Giảng viên</th>
                                                    <th class="text-center">Gửi ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($phanCongMoiNhat as $pc)
                                                <tr>
                                                    <td class="ps-3 py-2">
                                                        <a href="{{ route('admin.module-hoc.show', $pc->moduleHoc->id) }}" class="fw-bold text-decoration-none small d-block">
                                                            {{ $pc->moduleHoc->ten_module ?? 'N/A' }}
                                                        </a>
                                                        <small class="text-muted smaller">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc ?? '' }}</small>
                                                    </td>
                                                    <td>
                                                        <small class="fw-bold text-dark">{{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <small class="text-muted">{{ $pc->ngay_phan_cong ? $pc->ngay_phan_cong->format('d/m/Y') : $pc->created_at->format('d/m/Y') }}</small>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center text-muted py-4 small italic">Không có yêu cầu phân công nào đang chờ</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if($stats['phan_cong_cho_xn'] > 5)
                                    <div class="card-footer bg-white border-top-0 text-center py-2">
                                        <a href="{{ route('admin.module-hoc.index') }}" class="small text-decoration-none fw-bold">Xem tất cả <i class="fas fa-arrow-right ms-1"></i></a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Bảng phải: Module chưa có GV -->
                        <div class="col-md-6">
                            <div class="vip-card h-100 border-0 shadow-sm">
                                <div class="vip-card-header bg-white py-3">
                                    <h6 class="vip-card-title mb-0 fw-bold">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        Module trống giảng viên ({{ $stats['module_chua_co_gv'] }})
                                    </h6>
                                </div>
                                <div class="vip-card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="bg-light">
                                                <tr class="smaller">
                                                    <th class="ps-3 py-2">Module học</th>
                                                    <th>Khóa học liên quan</th>
                                                    <th class="text-center">Hành động</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($moduleChuaCoGv as $m)
                                                <tr>
                                                    <td class="ps-3 py-2">
                                                        <span class="fw-bold small text-dark">{{ $m->ten_module }}</span>
                                                        <code class="d-block smaller text-muted">{{ $m->ma_module }}</code>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted small">{{ $m->khoaHoc->ten_khoa_hoc }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('admin.khoa-hoc.show', $m->khoa_hoc_id) }}" class="btn btn-xs btn-primary py-0 px-2 fw-bold" style="font-size: 0.7rem;">
                                                            Phân công ngay
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center text-muted py-4 small italic">Tuyệt vời! Tất cả module đều đã có giảng viên</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if($stats['module_chua_co_gv'] > 5)
                                    <div class="card-footer bg-white border-top-0 text-center py-2">
                                        <a href="{{ route('admin.module-hoc.index') }}" class="small text-decoration-none fw-bold">Xem danh sách module <i class="fas fa-arrow-right ms-1"></i></a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách người dùng mới (Giữ nguyên từ code cũ) -->
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <h5 class="fw-bold mb-3"><i class="fas fa-user-plus me-2 text-primary"></i> Thành viên mới gia nhập</h5>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle text-nowrap">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-3 py-2">Ảnh</th>
                                                    <th>Họ tên</th>
                                                    <th>Email</th>
                                                    <th>Vai trò</th>
                                                    <th class="text-center">Trạng thái</th>
                                                    <th>Ngày tạo</th>
                                                    <th class="text-center">Sửa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($nguoiDungMoi as $user)
                                                <tr>
                                                    <td class="ps-3">
                                                        @if($user->anh_dai_dien)
                                                            <img src="{{ asset('images/'.$user->anh_dai_dien) }}" class="rounded-circle shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px;">
                                                                <i class="fas fa-user text-muted smaller"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td><strong>{{ $user->ho_ten }}</strong></td>
                                                    <td><small class="text-muted">{{ $user->email }}</small></td>
                                                    <td>
                                                        @php
                                                            $roleClasses = ['admin' => 'danger', 'giang_vien' => 'warning text-dark', 'hoc_vien' => 'success'];
                                                        @endphp
                                                        <span class="badge bg-{{ $roleClasses[$user->vai_tro] ?? 'secondary' }} rounded-pill px-2" style="font-size: 0.65rem;">
                                                            {{ strtoupper(str_replace('_', ' ', $user->vai_tro)) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($user->trashed())
                                                            <span class="badge bg-dark rounded-pill">Deleted</span>
                                                        @elseif($user->trang_thai)
                                                            <span class="badge bg-success rounded-pill">Active</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark rounded-pill">Locked</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted small">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('admin.tai-khoan.edit', $user->ma_nguoi_dung) }}" class="text-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="7" class="text-center py-3 text-muted small">Chưa có tài khoản nào</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.75rem; }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.7rem; }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection




