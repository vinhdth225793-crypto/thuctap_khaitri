@extends('layouts.app')

@section('title', 'Danh sách Module học')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
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
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-cubes me-2 text-primary"></i>
                Module học theo khóa học
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.module-hoc.create') }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm module mới
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @include('components.alert')

    <!-- Search & Filter -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.module-hoc.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                               placeholder="Tìm tên hoặc mã module..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="khoa_hoc_id" class="form-select vip-form-control">
                        <option value="">-- Tất cả khóa học --</option>
                        @foreach($khoaHocsAll as $kh)
                            <option value="{{ $kh->id }}" {{ $khoaHocId == $kh->id ? 'selected' : '' }}>
                                [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Lọc dữ liệu</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-light w-100 fw-bold border">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    @if($khoaHocsPaginated->count() > 0)
        @foreach($khoaHocsPaginated as $khoaHoc)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary me-2">{{ $khoaHoc->ma_khoa_hoc }}</span>
                        <h5 class="d-inline-block mb-0 fw-bold">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-layer-group me-1"></i> Nhóm ngành: {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-layer-group me-1"></i> {{ $khoaHoc->module_hocs_count }} modules
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Thêm module
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4 text-center" width="60">TT</th>
                                    <th width="150">Mã module</th>
                                    <th>Tên module</th>
                                    <th class="text-center" width="150">Thời lượng</th>
                                    <th class="text-center" width="150">Trạng thái</th>
                                    <th class="pe-4 text-center" width="180">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($khoaHoc->moduleHocs as $module)
                                    <tr>
                                        <td class="text-center ps-4 text-muted small">{{ $module->thu_tu_module }}</td>
                                        <td><code class="fw-bold text-primary">{{ $module->ma_module }}</code></td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $module->ten_module }}</span>
                                            @if($search && (stripos($module->ten_module, $search) !== false || stripos($module->ma_module, $search) !== false))
                                                <span class="badge bg-warning text-dark smaller ms-1">Khớp tìm kiếm</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($module->thoi_luong_du_kien)
                                                @php
                                                    $hours = floor($module->thoi_luong_du_kien / 60);
                                                    $mins = $module->thoi_luong_du_kien % 60;
                                                @endphp
                                                <span class="small">{{ $hours > 0 ? $hours.'h ' : '' }}{{ $mins.'p' }}</span>
                                            @else — @endif
                                        </td>
                                        <td class="text-center">
                                            @if($module->trang_thai)
                                                <span class="badge bg-success-soft text-success px-3 border border-success">Hoạt động</span>
                                            @else
                                                <span class="badge bg-secondary-soft text-secondary px-3 border border-secondary">Tạm dừng</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.module-hoc.show', $module->id) }}" 
                                                   class="btn btn-sm btn-outline-info action-btn" 
                                                   title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.module-hoc.edit', $module->id) }}" 
                                                   class="btn btn-sm btn-outline-warning action-btn" 
                                                   title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.module-hoc.toggle-status', $module->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary action-btn" title="Bật/Tắt">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.module-hoc.destroy', $module->id) }}" method="POST" 
                                                      onsubmit="return confirm('Xóa module này?')" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger action-btn" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted italic">
                                            Chưa có module nào được tạo cho khóa học này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $khoaHocsPaginated->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="vip-card shadow-sm border-0">
            <div class="vip-card-body p-5 text-center text-muted">
                <i class="fas fa-cubes fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">Không tìm thấy khóa học hoặc module nào phù hợp với điều kiện lọc.</p>
                <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-primary mt-3">Xem tất cả</a>
            </div>
        </div>
    @endif
</div>

<style>
    .smaller { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .action-btn { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; padding: 0; }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .vip-form-control:focus { box-shadow: none; border-color: #0d6efd; }
    .italic { font-style: italic; }
</style>
@endsection
