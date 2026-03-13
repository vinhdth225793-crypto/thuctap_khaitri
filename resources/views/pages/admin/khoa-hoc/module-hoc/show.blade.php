@extends('layouts.app')

@section('title', 'Chi tiết module: ' . $moduleHoc->ten_module)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý khóa học</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.index') }}">Module học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết module</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="fw-bold mb-0">
                <span class="text-muted fw-normal">{{ $moduleHoc->ma_module }}:</span> {{ $moduleHoc->ten_module }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.module-hoc.edit', $moduleHoc->id) }}" class="btn btn-warning text-white">
                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                </a>
                <form action="{{ route('admin.module-hoc.toggle-status', $moduleHoc->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-sync-alt me-1"></i> Đổi trạng thái
                    </button>
                </form>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash me-1"></i> Xóa module
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Cột trái: Thông tin Module -->
        <div class="col-md-8">
            <div class="vip-card mb-4 h-100">
                <div class="vip-card-header">
                    <h5 class="vip-card-title fw-bold">Thông tin module</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="row mb-4">
                        <div class="col-sm-4">
                            <label class="small text-muted fw-bold d-block">Mã module</label>
                            <span class="fs-5 fw-bold text-primary">{{ $moduleHoc->ma_module }}</span>
                        </div>
                        <div class="col-sm-4">
                            <label class="small text-muted fw-bold d-block">Thứ tự hiển thị</label>
                            <span class="badge bg-light text-dark border px-3 fs-6">#{{ $moduleHoc->thu_tu_module }}</span>
                        </div>
                        <div class="col-sm-4">
                            <label class="small text-muted fw-bold d-block">Thời lượng</label>
                            <span class="fs-5 fw-bold">
                                @if($moduleHoc->thoi_luong_du_kien)
                                    @php
                                        $h = intdiv($moduleHoc->thoi_luong_du_kien, 60);
                                        $m = $moduleHoc->thoi_luong_du_kien % 60;
                                    @endphp
                                    <i class="far fa-clock text-info me-1"></i>
                                    {{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted fw-bold d-block mb-2">Mô tả chi tiết</label>
                        <div class="p-3 bg-light rounded min-vh-10 border">
                            {!! nl2br(e($moduleHoc->mo_ta)) ?: '<em class="text-muted">Chưa có mô tả cho module này.</em>' !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <label class="small text-muted fw-bold d-block">Trạng thái</label>
                            @if($moduleHoc->trang_thai)
                                <span class="badge bg-success rounded-pill px-3"><i class="fas fa-check-circle me-1"></i> Đang hoạt động</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3"><i class="fas fa-times-circle me-1"></i> Đang tạm dừng</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <label class="small text-muted fw-bold d-block">Ngày tạo</label>
                            <span class="text-muted small">{{ $moduleHoc->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin Khóa học -->
        <div class="col-md-4">
            <div class="vip-card h-100">
                <div class="vip-card-header">
                    <h5 class="vip-card-title fw-bold">Khóa học liên quan</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="text-center mb-4">
                        @if($moduleHoc->khoaHoc->hinh_anh)
                            <img src="{{ asset($moduleHoc->khoaHoc->hinh_anh) }}" class="img-fluid rounded shadow-sm border mb-3" style="max-height: 150px; width: 100%; object-fit: cover;">
                        @else
                            <div class="bg-light rounded p-4 mb-3 border">
                                <i class="fas fa-graduation-cap fa-3x text-muted opacity-25"></i>
                            </div>
                        @endif
                        <h6 class="fw-bold mb-1">{{ $moduleHoc->khoaHoc->ten_khoa_hoc }}</h6>
                        <code class="text-primary fw-bold">{{ $moduleHoc->khoaHoc->ma_khoa_hoc }}</code>
                    </div>

                    <div class="list-group list-group-flush mb-4 small">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted fw-bold">Môn học:</span>
                            <span class="text-info fw-bold">{{ $moduleHoc->khoaHoc->monHoc->ten_mon_hoc }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted fw-bold">Cấp độ:</span>
                            @php
                                $capDo = [
                                    'co_ban' => ['text' => 'Cơ bản', 'class' => 'success'],
                                    'trung_binh' => ['text' => 'Trung bình', 'class' => 'warning text-dark'],
                                    'nang_cao' => ['text' => 'Nâng cao', 'class' => 'danger'],
                                ];
                                $cd = $capDo[$moduleHoc->khoaHoc->cap_do] ?? ['text' => 'N/A', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $cd['class'] }}">{{ $cd['text'] }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 border-bottom-0">
                            <span class="text-muted fw-bold">Trạng thái KH:</span>
                            <span class="text-{{ $moduleHoc->khoaHoc->trang_thai ? 'success' : 'danger' }} fw-bold">
                                {{ $moduleHoc->khoaHoc->trang_thai ? 'Đang mở' : 'Đang đóng' }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('admin.khoa-hoc.show', $moduleHoc->khoa_hoc_id) }}" class="btn btn-outline-primary w-100 fw-bold">
                        <i class="fas fa-external-link-alt me-1"></i> Xem khóa học
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Giảng viên phụ trách -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-header d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title fw-bold mb-0">Giảng viên phụ trách</h5>
                </div>
                <div class="vip-card-body p-4">
                    <!-- Form phân công mới -->
                    <div class="mb-4 p-3 bg-light rounded border">
                        <h6 class="fw-bold mb-3 small text-muted text-uppercase">Phân công giảng viên mới</h6>
                        <form action="{{ route('admin.phan-cong.assign', $moduleHoc->id) }}" method="POST">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Chọn giảng viên <span class="text-danger">*</span></label>
                                    <select name="giao_vien_id" class="form-select vip-form-control" required>
                                        <option value="">-- Chọn giảng viên --</option>
                                        @foreach($giangViens as $gv)
                                            <option value="{{ $gv->id }}">
                                                {{ $gv->nguoiDung->ho_ten ?? 'N/A' }} 
                                                @if($gv->hoc_vi) ({{ $gv->hoc_vi }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Ghi chú (tùy chọn)</label>
                                    <input type="text" name="ghi_chu" class="form-control vip-form-control" placeholder="Ghi chú cho lần phân công này">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                                        <i class="fas fa-user-plus me-1"></i> Phân công
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Danh sách phân công -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Giảng viên</th>
                                    <th>Học vị / Chuyên ngành</th>
                                    <th class="text-center">Ngày phân công</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th>Ghi chú</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($moduleHoc->phanCongGiangViens as $pc)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $pc->giangVien->avatar_url ? asset($pc->giangVien->avatar_url) : asset('images/default-avatar.svg') }}" class="rounded-circle me-2" width="35" height="35" style="object-fit: cover;">
                                                <span class="fw-bold text-dark">{{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="d-block fw-bold text-primary">{{ $pc->giangVien->hoc_vi ?: 'N/A' }}</small>
                                            <small class="text-muted">{{ $pc->giangVien->chuyen_nganh ?: 'N/A' }}</small>
                                        </td>
                                        <td class="text-center text-muted small">
                                            {{ $pc->ngay_phan_cong ? $pc->ngay_phan_cong->format('d/m/Y') : $pc->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClasses = [
                                                    'cho_xac_nhan' => 'bg-warning text-dark',
                                                    'da_nhan' => 'bg-success',
                                                    'tu_choi' => 'bg-danger',
                                                ];
                                                $statusTexts = [
                                                    'cho_xac_nhan' => 'Chờ xác nhận',
                                                    'da_nhan' => 'Đã nhận dạy',
                                                    'tu_choi' => 'Đã từ chối/hủy',
                                                ];
                                            @endphp
                                            <span class="badge {{ $statusClasses[$pc->trang_thai] ?? 'bg-secondary' }} rounded-pill px-3">
                                                {{ $statusTexts[$pc->trang_thai] ?? $pc->trang_thai }}
                                            </span>
                                        </td>
                                        <td><small class="text-muted">{{ $pc->ghi_chu ?: '-' }}</small></td>
                                        <td class="text-center">
                                            @if($pc->trang_thai === 'cho_xac_nhan')
                                                <form action="{{ route('admin.phan-cong.huy', $pc->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy yêu cầu phân công này?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                                                        <i class="fas fa-user-minus me-1"></i> Hủy yêu cầu
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small italic">Không thể hủy</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-user-slash fa-2x mb-2 d-block opacity-25"></i>
                                            Chưa có giảng viên nào được phân công dạy module này.
                                        </td>
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

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Xác nhận xóa module</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-trash-alt fa-4x text-danger opacity-25"></i>
                </div>
                <h5>Bạn có chắc chắn muốn xóa module này?</h5>
                <p class="fw-bold text-primary mb-0">{{ $moduleHoc->ten_module }}</p>
                <p class="small text-muted mt-3 mb-0">Hành động này sẽ xóa vĩnh viễn dữ liệu liên quan và không thể khôi phục.</p>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <form method="POST" action="{{ route('admin.module-hoc.destroy', $moduleHoc->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 text-white">Xác nhận Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete() {
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<style>
    .min-vh-10 { min-height: 100px; }
</style>
@endsection
