@extends('layouts.app')

@section('title', 'Quản lý khóa học: ' . $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Section 1 — Header & Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý đào tạo</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mon-hoc.index') }}">Môn học</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mon-hoc.show', $khoaHoc->mon_hoc_id) }}">{{ $khoaHoc->monHoc->ten_mon_hoc }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $khoaHoc->ten_khoa_hoc }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-3">{{ $khoaHoc->ten_khoa_hoc }}</h3>
                    @php
                        $capDo = [
                            'co_ban' => ['text' => 'Cơ bản', 'class' => 'success'],
                            'trung_binh' => ['text' => 'Trung bình', 'class' => 'warning text-dark'],
                            'nang_cao' => ['text' => 'Nâng cao', 'class' => 'danger'],
                        ];
                        $cd = $capDo[$khoaHoc->cap_do] ?? ['text' => 'N/A', 'class' => 'secondary'];
                    @endphp
                    <span class="badge bg-{{ $cd['class'] }} me-2 px-3">{{ $cd['text'] }}</span>
                    @if($khoaHoc->trang_thai)
                        <span class="badge bg-success rounded-pill px-3">Hoạt động</span>
                    @else
                        <span class="badge bg-danger rounded-pill px-3">Tạm dừng</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->id) }}" class="btn btn-warning text-white fw-bold">
                        <i class="fas fa-edit me-1"></i> Sửa KH
                    </a>
                    <form action="{{ route('admin.khoa-hoc.toggle-status', $khoaHoc->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary fw-bold">
                            <i class="fas fa-sync-alt me-1"></i> Đổi trạng thái
                        </button>
                    </form>
                    <button class="btn btn-danger fw-bold" onclick="confirmDeleteKH()">
                        <i class="fas fa-trash me-1"></i> Xóa
                    </button>
                    <a href="{{ route('admin.khoa-hoc.index') }}" class="btn btn-outline-secondary fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Section 2 — Thông tin chi tiết -->
    <div class="row">
        <!-- Cột trái: Mô tả -->
        <div class="col-md-8">
            <div class="vip-card mb-4 h-100">
                <div class="vip-card-header">
                    <h5 class="vip-card-title fw-bold">Thông tin chi tiết</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="mb-4">
                        <label class="small text-muted fw-bold d-block mb-1 text-uppercase">Mô tả ngắn</label>
                        <p class="text-dark fw-bold border-start border-primary border-4 ps-3 py-1">
                            {{ $khoaHoc->mo_ta_ngan ?: 'Chưa có mô tả ngắn' }}
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="small text-muted fw-bold d-block mb-2 text-uppercase">Nội dung chi tiết</label>
                        <div class="p-3 bg-light rounded border min-vh-20">
                            {!! nl2br(e($khoaHoc->mo_ta_chi_tiet)) ?: '<em class="text-muted">Chưa có mô tả chi tiết cho khóa học này.</em>' !!}
                        </div>
                    </div>

                    <div class="row pt-2">
                        <div class="col-sm-6">
                            <p class="mb-0 small text-muted"><strong>Ngày tạo:</strong> {{ $khoaHoc->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <p class="mb-0 small text-muted"><strong>Cập nhật cuối:</strong> {{ $khoaHoc->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thumbnail & Metadata -->
        <div class="col-md-4">
            <div class="vip-card mb-4 h-100">
                <div class="vip-card-body p-4 text-center">
                    @if($khoaHoc->hinh_anh)
                        <img src="{{ asset($khoaHoc->hinh_anh) }}" alt="{{ $khoaHoc->ten_khoa_hoc }}" class="img-fluid rounded shadow-sm border mb-4" style="max-height: 200px; width: 100%; object-fit: cover;">
                    @else
                        <div class="bg-light rounded p-5 mb-4 border text-muted">
                            <i class="fas fa-graduation-cap fa-4x opacity-25"></i>
                        </div>
                    @endif

                    <div class="text-start">
                        <div class="mb-3">
                            <label class="small text-muted fw-bold d-block">Mã khóa học</label>
                            <span class="fs-5 fw-bold text-primary">{{ $khoaHoc->ma_khoa_hoc }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted fw-bold d-block">Môn học</label>
                            <a href="{{ route('admin.mon-hoc.show', $khoaHoc->mon_hoc_id) }}" class="text-decoration-none fw-bold text-info">
                                {{ $khoaHoc->monHoc->ten_mon_hoc }}
                            </a>
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted fw-bold d-block">Quy mô đào tạo</label>
                            <span class="fs-5 fw-bold">{{ $khoaHoc->so_module_thuc_te }} Modules</span>
                            <small class="text-muted d-block italic">(Khai báo: {{ $khoaHoc->tong_so_module }} modules)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3 — Danh sách Modules -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="vip-card-title fw-bold mb-0">
                        <i class="fas fa-list-ul me-2 text-primary"></i> 
                        Cấu trúc chương trình học ({{ $khoaHoc->moduleHocs->count() }} module)
                    </h5>
                    <a href="{{ route('admin.module-hoc.create') }}?khoa_hoc_id={{ $khoaHoc->id }}" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="fas fa-plus me-1"></i> Thêm module mới
                    </a>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" width="60">STT</th>
                                    <th width="120">Mã Module</th>
                                    <th>Tên Module học</th>
                                    <th class="text-center">Thời lượng</th>
                                    <th>Giảng viên phụ trách</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center" width="180">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($khoaHoc->moduleHocs as $index => $module)
                                    @php 
                                        // Lấy phân công active mới nhất
                                        $pc = $module->phanCongGiangViens->where('trang_thai', '!==', 'tu_choi')->sortByDesc('updated_at')->first(); 
                                    @endphp
                                    <tr class="{{ !$pc ? 'table-warning opacity-90' : '' }}">
                                        <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                                        <td><code class="fw-bold">{{ $module->ma_module }}</code></td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 300px;">{{ $module->mo_ta }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($module->thoi_luong_du_kien)
                                                @php
                                                    $h = intdiv($module->thoi_luong_du_kien, 60);
                                                    $m = $module->thoi_luong_du_kien % 60;
                                                @endphp
                                                <span class="small fw-bold text-dark">{{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}</span>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($pc)
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-{{ $pc->trang_thai === 'da_nhan' ? 'success' : 'warning' }} px-3">
                                                        {{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}
                                                    </span>
                                                </div>
                                                <small class="d-block text-muted mt-1" style="font-size: 0.75rem;">
                                                    <i class="fas fa-info-circle me-1"></i> {{ $pc->trang_thai === 'da_nhan' ? 'Đã xác nhận' : 'Đang chờ XN' }}
                                                </small>
                                            @else
                                                <span class="text-danger small fw-bold"><i class="fas fa-exclamation-circle me-1"></i> Chưa có GV</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($module->trang_thai)
                                                <span class="badge bg-success rounded-pill">Active</span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-sm btn-outline-info" title="Chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.module-hoc.edit', $module->id) }}" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-phan-cong" 
                                                        data-module-id="{{ $module->id }}" 
                                                        data-module-name="{{ $module->ten_module }}"
                                                        title="Phân công giảng viên">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            Khóa học này chưa có module nào.
                                            <div class="mt-3">
                                                <a href="{{ route('admin.module-hoc.create') }}?khoa_hoc_id={{ $khoaHoc->id }}" class="btn btn-primary btn-sm px-4">
                                                    <i class="fas fa-plus me-1"></i> Thêm module ngay
                                                </a>
                                            </div>
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

<!-- Modal Phân công Giảng viên (Phase 4 Integration) -->
<div class="modal fade" id="modalPhanCong" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Phân công Giảng viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modalPhanCongForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted fw-bold d-block mb-1">Module mục tiêu</label>
                        <p id="phanCong-moduleName" class="fw-bold fs-5 text-dark mb-0"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal-gv-id" class="form-label small fw-bold">Chọn giảng viên giảng dạy <span class="text-danger">*</span></label>
                        <select name="giao_vien_id" id="modal-gv-id" class="form-select vip-form-control" required>
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">
                                    {{ $gv->nguoiDung->ho_ten ?? 'N/A' }} 
                                    @if($gv->hoc_vi) ({{ $gv->hoc_vi }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="modal-ghi-chu" class="form-label small fw-bold">Ghi chú phân công (tùy chọn)</label>
                        <textarea name="ghi_chu" id="modal-ghi-chu" class="form-control vip-form-control" rows="2" placeholder="Ví dụ: Dạy buổi tối, yêu cầu slide chuẩn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-paper-plane me-1"></i> Gửi yêu cầu phân công
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa khóa học -->
<div class="modal fade" id="deleteKHModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">Xác nhận xóa khóa học</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Bạn có chắc chắn muốn xóa vĩnh viễn khóa học này không?</p>
                <div class="alert alert-warning border-0 small mb-0">
                    <i class="fas fa-exclamation-triangle me-1 text-danger"></i> 
                    <strong>Lưu ý:</strong> Tất cả các module và phân công liên quan cũng sẽ bị xóa.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" action="{{ route('admin.khoa-hoc.destroy', $khoaHoc->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 text-white fw-bold">Đồng ý Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logic show modal phân công
        document.querySelectorAll('.btn-phan-cong').forEach(btn => {
            btn.addEventListener('click', function() {
                const moduleId = this.dataset.moduleId;
                const moduleName = this.dataset.moduleName;
                
                document.getElementById('phanCong-moduleName').textContent = moduleName;
                document.getElementById('modalPhanCongForm').action = `/admin/module-hoc/${moduleId}/assign`;
                
                new bootstrap.Modal(document.getElementById('modalPhanCong')).show();
            });
        });
    });

    function confirmDeleteKH() {
        new bootstrap.Modal(document.getElementById('deleteKHModal')).show();
    }
</script>

<style>
    .min-vh-20 { min-height: 150px; }
    .opacity-90 { opacity: 0.9; }
</style>
@endsection
