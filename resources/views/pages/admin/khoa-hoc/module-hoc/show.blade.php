@extends('layouts.app')

@section('title', 'Chi tiết module: ' . $moduleHoc->ten_module)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý khóa học</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.index') }}">Module học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="fw-bold mb-0">
                <span class="text-muted fw-normal fs-5">{{ $moduleHoc->ma_module }}:</span> {{ $moduleHoc->ten_module }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.module-hoc.edit', $moduleHoc->id) }}" class="btn btn-warning text-white fw-bold">
                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                </a>
                <form action="{{ route('admin.module-hoc.toggle-status', $moduleHoc->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary fw-bold">
                        <i class="fas fa-sync-alt me-1"></i> Đổi trạng thái
                    </button>
                </form>
                <button type="button" class="btn btn-danger fw-bold" onclick="confirmDelete()">
                    <i class="fas fa-trash me-1"></i> Xóa
                </button>
                <a href="{{ route('admin.khoa-hoc.show', $moduleHoc->khoa_hoc_id) }}" class="btn btn-outline-secondary fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại Khóa học
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Cột trái: Chi tiết Module -->
        <div class="col-md-8">
            <div class="vip-card mb-4 h-100">
                <div class="vip-card-header border-bottom">
                    <h5 class="vip-card-title fw-bold">Thông tin module</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="row mb-4">
                        <div class="col-sm-4">
                            <label class="small text-muted fw-bold d-block mb-1">Mã định danh</label>
                            <span class="fs-5 fw-bold text-primary">{{ $moduleHoc->ma_module }}</span>
                        </div>
                        <div class="col-sm-4">
                            <label class="small text-muted fw-bold d-block mb-1">Thứ tự hiển thị</label>
                            <span class="badge bg-light text-dark border px-3 fs-6">#{{ $moduleHoc->thu_tu_module }}</span>
                        </div>
                        <div class="col-sm-4">
                            <label class="small text-muted fw-bold d-block mb-1">Thời lượng</label>
                            <span class="fs-5 fw-bold text-dark">
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
                        <label class="small text-muted fw-bold d-block mb-2">Mô tả nội dung học tập</label>
                        <div class="p-3 bg-light rounded min-vh-10 border border-dashed">
                            {!! nl2br(e($moduleHoc->mo_ta)) ?: '<em class="text-muted small">Chưa có mô tả cho module này.</em>' !!}
                        </div>
                    </div>

                    <div class="row pt-2">
                        <div class="col-sm-6">
                            <label class="small text-muted fw-bold d-block">Trạng thái hoạt động</label>
                            @if($moduleHoc->trang_thai)
                                <span class="badge bg-success rounded-pill px-3"><i class="fas fa-check-circle me-1"></i> Đang kích hoạt</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3"><i class="fas fa-times-circle me-1"></i> Đang tạm dừng</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <label class="small text-muted fw-bold d-block">Thời gian khởi tạo</label>
                            <span class="text-muted small">{{ $moduleHoc->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin Khóa học & Môn học -->
        <div class="col-md-4">
            <div class="vip-card h-100 border-primary border-top border-4">
                <div class="vip-card-header">
                    <h5 class="vip-card-title fw-bold text-primary">Khóa học & Môn học</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="text-center mb-4">
                        @if($moduleHoc->khoaHoc->hinh_anh)
                            <img src="{{ asset($moduleHoc->khoaHoc->hinh_anh) }}" class="img-fluid rounded shadow-sm border mb-3" style="max-height: 120px; width: 100%; object-fit: cover;">
                        @else
                            <div class="bg-light rounded p-4 mb-3 border">
                                <i class="fas fa-graduation-cap fa-3x text-muted opacity-25"></i>
                            </div>
                        @endif
                        <h6 class="fw-bold mb-1">{{ $moduleHoc->khoaHoc->ten_khoa_hoc }}</h6>
                        <code class="text-primary fw-bold">{{ $moduleHoc->khoaHoc->ma_khoa_hoc }}</code>
                    </div>

                    <div class="list-group list-group-flush mb-4 small border rounded">
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="text-muted fw-bold">Môn học:</span>
                            <span class="text-info fw-bold">{{ $moduleHoc->khoaHoc->monHoc->ten_mon_hoc ?? 'N/A' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="text-muted fw-bold">Cấp độ:</span>
                            <span class="text-dark fw-bold text-capitalize">{{ str_replace('_', ' ', $moduleHoc->khoaHoc->cap_do) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between border-bottom-0">
                            <span class="text-muted fw-bold">Trạng thái KH:</span>
                            @if($moduleHoc->khoaHoc->trang_thai)
                                <span class="text-success fw-bold">Đang mở</span>
                            @else
                                <span class="text-danger fw-bold">Đã đóng</span>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('admin.khoa-hoc.show', $moduleHoc->khoa_hoc_id) }}" class="btn btn-primary w-100 fw-bold shadow-sm">
                        <i class="fas fa-external-link-alt me-1"></i> Quản lý Khóa học
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Giảng viên phụ trách (Phase 3A) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-header d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title fw-bold mb-0">Giảng viên phụ trách</h5>
                </div>
                <div class="vip-card-body p-4">
                    <!-- Form phân công mới -->
                    <div class="mb-4 p-3 bg-light rounded border shadow-sm">
                        <h6 class="fw-bold mb-3 small text-muted text-uppercase">Phân công giảng viên mới</h6>
                        <form action="{{ route('admin.phan-cong.assign', $moduleHoc->id) }}" method="POST">
                            @csrf
                            <div class="row g-2 align-items-end">
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

                    <!-- Bảng danh sách phân công -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Giảng viên</th>
                                    <th>Học vị</th>
                                    <th class="text-center">Ngày phân công</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th>Ghi chú</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($moduleHoc->phanCongGiangViens as $pc)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $pc->giangVien->nguoiDung->email ?? '' }}</small>
                                        </td>
                                        <td><small class="fw-bold text-primary">{{ $pc->giangVien->hoc_vi ?: '-' }}</small></td>
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
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 fw-bold">
                                                        <i class="fas fa-times me-1"></i> Hủy
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small italic">Không thể tác động</span>
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
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-trash-alt fa-4x text-danger opacity-25"></i>
                </div>
                <h5>Bạn có chắc chắn muốn xóa module này?</h5>
                <p class="fw-bold text-primary mb-0 mt-2">{{ $moduleHoc->ten_module }}</p>
                <div class="alert alert-warning border-0 small mt-3 mb-0 text-start">
                    <i class="fas fa-info-circle me-1"></i> **Lưu ý:** Hành động này sẽ xóa vĩnh viễn dữ liệu và không thể khôi phục.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                <form method="POST" action="{{ route('admin.module-hoc.destroy', $moduleHoc->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 text-white fw-bold shadow-sm">Xác nhận Xóa</button>
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
    .border-dashed { border-style: dashed !important; }
</style>
@endsection
