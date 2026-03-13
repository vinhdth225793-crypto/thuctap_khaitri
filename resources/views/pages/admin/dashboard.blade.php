@extends('layouts.app')

@section('title', 'Dashboard - Quản trị viên')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Dashboard Quản trị viên</h3>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-home"></i> Về trang chủ
                    </a>
                </div>
                <div class="card-body">
                    <!-- Section: Người dùng -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $tongNguoiDung }}</h3>
                                    <p>Tổng người dùng</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $tongHocVien }}</h3>
                                    <p>Học viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $tongGiangVien }}</h3>
                                    <p>Giảng viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $tongAdmin }}</h3>
                                    <p>Quản trị viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 1 — Phase 5: Đào tạo & Module (Stat Cards) -->
                    <div class="row mb-4 mt-3">
                        <!-- Card 1: Môn học -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold">Môn học</p>
                                            <h3 class="mb-0 fw-bold">{{ $stats['tong_mon_hoc'] }}</h3>
                                            <small class="text-success">{{ $stats['mon_hoc_hoat_dong'] }} đang hoạt động</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-book fa-2x text-primary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card 2: Khóa học -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold">Khóa học</p>
                                            <h3 class="mb-0 fw-bold">{{ $stats['tong_khoa_hoc'] }}</h3>
                                            <small class="text-success">{{ $stats['khoa_hoc_hoat_dong'] }} đang mở</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-graduation-cap fa-2x text-success opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Module chưa có GV -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold">Module chưa có GV</p>
                                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['module_chua_co_gv'] }}</h3>
                                            <small class="text-muted">Cần phân công dạy</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exclamation-circle fa-2x text-warning opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Phân công chờ xác nhận -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1 small fw-bold">Phân công chờ xác nhận</p>
                                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['phan_cong_cho_xn'] }}</h3>
                                            <small class="text-muted">Giảng viên chưa phản hồi</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-clock fa-2x text-danger opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 — Phase 5: Bảng chi tiết -->
                    <div class="row mt-4">
                        <!-- Bảng trái: Phân công chờ xác nhận -->
                        <div class="col-md-6">
                            <div class="vip-card h-100">
                                <div class="vip-card-header bg-white">
                                    <h6 class="vip-card-title mb-0 fw-bold">
                                        <i class="fas fa-clock text-warning me-2"></i>
                                        Phân công chờ xác nhận ({{ $stats['phan_cong_cho_xn'] }})
                                    </h6>
                                </div>
                                <div class="vip-card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-3">Module / Khóa học</th>
                                                    <th>Giảng viên</th>
                                                    <th class="text-center">Ngày PC</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($phanCongMoiNhat as $pc)
                                                <tr>
                                                    <td class="ps-3">
                                                        <a href="{{ route('admin.module-hoc.show', $pc->moduleHoc->id) }}" class="fw-bold text-decoration-none small">
                                                            {{ $pc->moduleHoc->ten_module ?? 'N/A' }}
                                                        </a>
                                                        <small class="d-block text-muted" style="font-size: 0.7rem;">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc ?? '' }}</small>
                                                    </td>
                                                    <td>
                                                        <small class="fw-bold">{{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <small class="text-muted">{{ $pc->ngay_phan_cong ? $pc->ngay_phan_cong->format('d/m/Y') : $pc->created_at->format('d/m/Y') }}</small>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center text-muted py-4 small">Không có phân công chờ xác nhận</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if($stats['phan_cong_cho_xn'] > 5)
                                    <div class="card-footer bg-white border-top-0 text-center py-2">
                                        <a href="{{ route('admin.module-hoc.index') }}" class="small text-decoration-none">Xem tất cả</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Bảng phải: Module chưa có GV -->
                        <div class="col-md-6">
                            <div class="vip-card h-100">
                                <div class="vip-card-header bg-white">
                                    <h6 class="vip-card-title mb-0 fw-bold">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        Module chưa có giảng viên ({{ $stats['module_chua_co_gv'] }})
                                    </h6>
                                </div>
                                <div class="vip-card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-3">Module học</th>
                                                    <th>Khóa học</th>
                                                    <th class="text-center">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($moduleChuaCoGv as $m)
                                                <tr>
                                                    <td class="ps-3">
                                                        <span class="fw-bold small">{{ $m->ten_module }}</span>
                                                        <code class="d-block" style="font-size: 0.7rem;">{{ $m->ma_module }}</code>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $m->khoaHoc->ten_khoa_hoc }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('admin.khoa-hoc.show', $m->khoa_hoc_id) }}" class="btn btn-xs btn-primary py-0 px-2" style="font-size: 0.7rem;">
                                                            Phân công
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center text-muted py-4 small">Tất cả module đều đã được phân công</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if($stats['module_chua_co_gv'] > 5)
                                    <div class="card-footer bg-white border-top-0 text-center py-2">
                                        <a href="{{ route('admin.module-hoc.index') }}" class="small text-decoration-none">Xem tất cả</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Người dùng mới -->
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h3 class="card-title mb-0 fw-bold">10 Tài khoản mới nhất</h3>
                                    <a href="{{ route('admin.tai-khoan.index') }}" class="btn btn-sm btn-outline-primary fw-bold">Xem tất cả</a>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap align-middle">
                                        <thead>
                                            <tr>
                                                <th class="ps-3">Ảnh</th>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>Vai trò</th>
                                                <th>Trạng thái</th>
                                                <th>Ngày tạo</th>
                                                <th class="text-center">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($nguoiDungMoi as $user)
                                            <tr>
                                                <td class="ps-3">
                                                    @if($user->anh_dai_dien)
                                                        <img src="{{ asset('images/'.$user->anh_dai_dien) }}" alt="Ảnh" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center text-muted" style="width: 32px; height: 32px; border: 1px solid #ddd; font-size: 12px;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td><strong>{{ $user->ho_ten }}</strong></td>
                                                <td><small>{{ $user->email }}</small></td>
                                                <td>
                                                    <span class="badge badge-{{ $user->vai_tro === 'admin' ? 'danger' : ($user->vai_tro === 'giang_vien' ? 'warning' : 'success') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $user->vai_tro)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($user->trashed())
                                                        <span class="badge badge-dark">Đã xóa</span>
                                                    @elseif($user->trang_thai)
                                                        <span class="badge badge-success">Hoạt động</span>
                                                    @else
                                                        <span class="badge badge-warning">Khóa</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted small">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.tai-khoan.edit', $user->ma_nguoi_dung) }}" class="btn btn-xs btn-warning text-white" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted small">Chưa có tài khoản nào</td>
                                            </tr>
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
@endsection
