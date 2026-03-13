@extends('layouts.app')

@section('title', 'Danh sách Module học')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý khóa học</li>
                    <li class="breadcrumb-item active" aria-current="page">Module học</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="fw-bold mb-0">Danh sách Module học</h3>
            <a href="{{ route('admin.module-hoc.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Thêm module mới
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-body">
                    <form action="{{ route('admin.module-hoc.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control vip-form-control" placeholder="Tên hoặc mã module..." value="{{ $search }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Lọc theo khóa học</label>
                            <select name="khoa_hoc_id" class="form-select vip-form-control">
                                <option value="">-- Tất cả khóa học --</option>
                                @foreach($khoaHocs as $kh)
                                    <option value="{{ $kh->id }}" {{ $khoaHocId == $kh->id ? 'selected' : '' }}>
                                        [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-filter me-1"></i> Lọc
                            </button>
                            <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 text-center" width="60">STT</th>
                                    <th>Mã module</th>
                                    <th>Tên module</th>
                                    <th>Khóa học</th>
                                    <th>Môn học</th>
                                    <th class="text-center">Thứ tự</th>
                                    <th class="text-center">Thời lượng</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="px-4 text-center" width="200">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($moduleHocs as $index => $module)
                                    <tr>
                                        <td class="text-center px-4 text-muted small">
                                            {{ ($moduleHocs->currentPage() - 1) * $moduleHocs->perPage() + $loop->iteration }}
                                        </td>
                                        <td><code class="fw-bold text-primary">{{ $module->ma_module }}</code></td>
                                        <td><span class="fw-bold">{{ $module->ten_module }}</span></td>
                                        <td><small>{{ $module->khoaHoc->ten_khoa_hoc }}</small></td>
                                        <td><small class="text-info">{{ $module->khoaHoc->monHoc->ten_mon_hoc }}</small></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3">#{{ $module->thu_tu_module }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($module->thoi_luong_du_kien)
                                                @php
                                                    $h = intdiv($module->thoi_luong_du_kien, 60);
                                                    $m = $module->thoi_luong_du_kien % 60;
                                                @endphp
                                                <small class="fw-bold">
                                                    {{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}
                                                </small>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($module->trang_thai)
                                                <span class="badge bg-success rounded-pill px-3">Active</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-sm btn-info text-white" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.module-hoc.edit', $module->id) }}" class="btn btn-sm btn-warning text-white" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.module-hoc.toggle-status', $module->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="Đổi trạng thái">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger" title="Xóa" onclick="confirmDelete({{ $module->id }}, '{{ $module->ten_module }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            Chưa có module nào phù hợp với tìm kiếm.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($moduleHocs->hasPages())
                    <div class="vip-card-footer border-top p-3 d-flex justify-content-center">
                        {{ $moduleHocs->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
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
            <div class="modal-body p-4">
                <p>Bạn có chắc chắn muốn xóa module <strong id="deleteModuleName" class="text-danger"></strong> không?</p>
                <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Lưu ý: Hành động này sẽ không thể khôi phục nếu module không có ràng buộc dữ liệu.</p>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 text-white">
                        <i class="fas fa-trash-alt me-1"></i> Xác nhận xóa
                    </button>
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
@endsection
