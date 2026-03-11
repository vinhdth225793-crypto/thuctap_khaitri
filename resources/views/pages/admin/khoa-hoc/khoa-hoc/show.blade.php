@extends('layouts.app')

@section('title', 'Chi tiết khóa học')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.khoa-hoc.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div>
                    <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <button class="btn btn-danger" onclick="confirmDelete({{ $khoaHoc->id }})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-md-4">
            <div class="vip-card mb-4">
                <div class="vip-card-body text-center">
                    @if($khoaHoc->hinh_anh)
                        <img src="{{ asset($khoaHoc->hinh_anh) }}" alt="{{ $khoaHoc->ten_khoa_hoc }}" class="img-fluid mb-3" style="max-height: 250px; object-fit: cover;">
                    @else
                        <div class="bg-light p-4 mb-3 rounded">
                            <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #ccc;"></i>
                        </div>
                    @endif
                    <h4>{{ $khoaHoc->ten_khoa_hoc }}</h4>
                    <p class="text-muted mb-2">
                        <strong>Mã:</strong> {{ $khoaHoc->ma_khoa_hoc }}
                    </p>
                    <div class="mb-3">
                        @switch($khoaHoc->cap_do)
                            @case('co_ban')
                                <span class="badge bg-success">Cơ bản</span>
                                @break
                            @case('trung_binh')
                                <span class="badge bg-warning">Trung bình</span>
                                @break
                            @case('nang_cao')
                                <span class="badge bg-danger">Nâng cao</span>
                                @break
                        @endswitch
                    </div>
                    <div class="mb-3">
                        @if($khoaHoc->trang_thai)
                            <span class="badge bg-success">Hoạt động</span>
                        @else
                            <span class="badge bg-danger">Tạm dừng</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Thông tin môn học -->
            <div class="vip-card mb-4">
                <div class="vip-card-header">
                    <h6 class="vip-card-title">
                        <i class="fas fa-book"></i> Môn học
                    </h6>
                </div>
                <div class="vip-card-body">
                    <h6>{{ $khoaHoc->monHoc->ten_mon_hoc ?? 'N/A' }}</h6>
                    <p class="text-muted small mb-0">
                        Mã: {{ $khoaHoc->monHoc->ma_mon_hoc ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <!-- Thống kê -->
            <div class="vip-card">
                <div class="vip-card-header">
                    <h6 class="vip-card-title">
                        <i class="fas fa-chart-bar"></i> Thống kê
                    </h6>
                </div>
                <div class="vip-card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $khoaHoc->tong_so_module }}</h4>
                                <small class="text-muted">Modules</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $khoaHoc->giangViens()->distinct()->count() }}</h4>
                            <small class="text-muted">Giảng viên</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chi tiết -->
        <div class="col-md-8">
            <!-- Mô tả -->
            <div class="vip-card mb-4">
                <div class="vip-card-header">
                    <h6 class="vip-card-title">
                        <i class="fas fa-info-circle"></i> Thông tin chi tiết
                    </h6>
                </div>
                <div class="vip-card-body">
                    @if($khoaHoc->mo_ta_ngan)
                        <p><strong>Mô tả ngắn:</strong></p>
                        <p>{{ $khoaHoc->mo_ta_ngan }}</p>
                        <hr>
                    @endif

                    @if($khoaHoc->mo_ta_chi_tiet)
                        <p><strong>Mô tả chi tiết:</strong></p>
                        <div>{!! nl2br(e($khoaHoc->mo_ta_chi_tiet)) !!}</div>
                    @else
                        <p class="text-muted">Chưa có mô tả chi tiết</p>
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Ngày tạo:</strong><br>
                            {{ $khoaHoc->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ngày cập nhật:</strong><br>
                            {{ $khoaHoc->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách modules -->
            <div class="vip-card mb-4">
                <div class="vip-card-header">
                    <h6 class="vip-card-title">
                        <i class="fas fa-list"></i> Danh sách Modules ({{ $khoaHoc->moduleHocs->count() }})
                    </h6>
                </div>
                <div class="vip-card-body">
                    @if($khoaHoc->moduleHocs->count() > 0)
                        <div class="row">
                            @foreach($khoaHoc->moduleHocs as $module)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-bookmark"></i> {{ $module->ten_module }}
                                        </h6>
                                        <p class="text-muted small mb-2">
                                            Mã: {{ $module->ma_module }}
                                        </p>
                                        @if($module->mo_ta)
                                            <p class="small mb-2">{{ Str::limit($module->mo_ta, 100) }}</p>
                                        @endif
                                        @if($module->thoi_luong_du_kien)
                                            <p class="small text-info mb-2">
                                                <i class="fas fa-clock"></i> {{ $module->thoi_luong_du_kien }} giờ
                                            </p>
                                        @endif

                                        <!-- Giảng viên dạy module này -->
                                        @php
                                            $giangVienModule = $module->phanCongGiangViens->where('trang_thai', 'da_nhan')->first();
                                        @endphp
                                        @if($giangVienModule)
                                            <div class="small">
                                                <strong>Giảng viên:</strong>
                                                {{ $giangVienModule->giangVien->nguoiDung->ho_ten ?? 'N/A' }}
                                                @if($giangVienModule->giangVien->hoc_vi)
                                                    <span class="text-muted">({{ $giangVienModule->giangVien->hoc_vi }})</span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-2">
                                            @if($module->trang_thai)
                                                <span class="badge bg-success">Hoạt động</span>
                                            @else
                                                <span class="badge bg-danger">Tạm dừng</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">Chưa có module nào</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danh sách giảng viên -->
            <div class="vip-card">
                <div class="vip-card-header">
                    <h6 class="vip-card-title">
                        <i class="fas fa-users"></i> Danh sách giảng viên ({{ $khoaHoc->giangViens()->distinct()->count() }})
                    </h6>
                </div>
                <div class="vip-card-body">
                    @if($khoaHoc->giangViens()->count() > 0)
                        <div class="row">
                            @foreach($khoaHoc->giangViens()->distinct()->get() as $giangVien)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-2 border rounded">
                                        @if($giangVien->avatar_url)
                                            <img src="{{ asset($giangVien->avatar_url) }}" alt="{{ $giangVien->nguoiDung->ho_ten }}" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $giangVien->nguoiDung->ho_ten ?? 'N/A' }}</h6>
                                            @if($giangVien->hoc_vi)
                                                <small class="text-muted">{{ $giangVien->hoc_vi }}</small>
                                            @endif
                                            @if($giangVien->chuyen_nganh)
                                                <br><small class="text-muted">{{ $giangVien->chuyen_nganh }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">Chưa có giảng viên nào được phân công</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc muốn xóa khóa học này không?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Tất cả module và phân công giảng viên thuộc khóa học này cũng sẽ bị xóa.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form action="{{ route('admin.khoa-hoc.destroy', $khoaHoc->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(khoaHocId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection