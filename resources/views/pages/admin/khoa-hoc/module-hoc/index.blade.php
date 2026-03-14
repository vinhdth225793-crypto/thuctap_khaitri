@extends('layouts.app')

@section('title', 'Quản lý Module theo Khóa học')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý đào tạo</li>
                    <li class="breadcrumb-item active" aria-current="page">Module học</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0">Quản lý Module học</h3>
            <p class="text-muted small">Danh sách các module được sắp xếp theo từng khóa học.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.module-hoc.create') }}" class="btn btn-primary fw-bold px-4">
                <i class="fas fa-plus me-2"></i> Thêm module mới
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="vip-card border-0 shadow-sm mb-4">
        <div class="vip-card-body p-3">
            <form action="{{ route('admin.module-hoc.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" placeholder="Tìm tên module hoặc mã..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="khoa_hoc_id" class="form-select vip-form-control">
                        <option value="">-- Tất cả khóa học --</option>
                        @foreach($allKhoaHocs as $kh)
                            <option value="{{ $kh->id }}" {{ $khoaHocId == $kh->id ? 'selected' : '' }}>
                                [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 flex-fill fw-bold">Lọc dữ liệu</button>
                    @if($search || $khoaHocId)
                        <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-outline-secondary px-3">Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Course Groups -->
    <div class="course-groups">
        @forelse($khoaHocs as $kh)
            <div class="vip-card mb-5 border-0 shadow">
                <div class="vip-card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">
                            <span class="text-primary me-2">[{{ $kh->ma_khoa_hoc }}]</span> 
                            {{ $kh->ten_khoa_hoc }}
                        </h5>
                        <div class="small">
                            <span class="text-muted">Môn học:</span> 
                            <span class="fw-bold text-info">{{ $kh->monHoc->ten_mon_hoc }}</span>
                            <span class="mx-2 text-muted">|</span>
                            <span class="badge bg-light text-dark border">{{ $kh->moduleHocs->count() }} modules</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $kh->id]) }}" class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="fas fa-plus me-1"></i> Thêm Module
                        </a>
                        <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" class="btn btn-sm btn-light border fw-bold" title="Xem khóa học">
                            <i class="fas fa-external-link-alt text-muted"></i>
                        </a>
                    </div>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="smaller text-muted text-uppercase">
                                    <th class="ps-4" width="60">Thứ tự</th>
                                    <th width="120">Mã Module</th>
                                    <th>Tên Module học tập</th>
                                    <th class="text-center">Thời lượng</th>
                                    <th>Giảng viên phụ trách</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="pe-4 text-center" width="150">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kh->moduleHocs as $module)
                                    @php 
                                        $pc = $module->phanCongGiangViens->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan'])->first();
                                    @endphp
                                    <tr>
                                        <td class="ps-4 text-center">
                                            <span class="badge bg-light text-dark border fw-bold px-3">#{{ $module->thu_tu_module }}</span>
                                        </td>
                                        <td><code class="fw-bold text-primary">{{ $module->ma_module }}</code></td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                            @if($module->mo_ta)
                                                <small class="text-muted d-block text-truncate" style="max-width: 300px;">{{ $module->mo_ta }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($module->thoi_luong_du_kien)
                                                @php $h = intdiv($module->thoi_luong_du_kien, 60); $m = $module->thoi_luong_du_kien % 60; @endphp
                                                <small class="fw-bold text-dark">{{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}</small>
                                            @else — @endif
                                        </td>
                                        <td>
                                            @if($pc)
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-{{ $pc->trang_thai === 'da_nhan' ? 'success' : 'warning' }} shadow-sm px-3">
                                                        {{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-muted smaller italic">Chưa phân công</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($module->trang_thai)
                                                <span class="badge bg-success rounded-pill px-3">Active</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-sm btn-info text-white shadow-sm" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.module-hoc.edit', $module->id) }}" class="btn btn-sm btn-warning text-white shadow-sm" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm" title="Xóa" onclick="confirmDelete({{ $module->id }}, '{{ $module->ten_module }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted bg-light">Khóa học này hiện chưa có module nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white shadow-sm rounded">
                <i class="fas fa-inbox fa-4x text-muted mb-3 opacity-25"></i>
                <h4 class="text-muted">Không tìm thấy khóa học nào phù hợp.</h4>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $khoaHocs->links('pagination::bootstrap-5') }}
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
            <div class="modal-body p-4">
                <p>Bạn có chắc chắn muốn xóa module <strong id="deleteModuleName" class="text-danger"></strong> không?</p>
                <p class="small text-muted mb-0 italic">Lưu ý: Chỉ có thể xóa khi chưa có giảng viên đang tiếp nhận dạy.</p>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 text-white fw-bold shadow-sm">Đồng ý Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteModuleName').textContent = name;
        document.getElementById('deleteForm').action = `/admin/module-hoc/${id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<style>
    .smaller { font-size: 0.75rem; }
    .vip-card { border-radius: 12px; }
    .vip-card-header { border-top-left-radius: 12px !important; border-top-right-radius: 12px !important; }
    .table thead th { border-top: none; }
    .course-groups .vip-card:hover { transform: translateY(-5px); transition: transform 0.3s; }
</style>
@endsection
