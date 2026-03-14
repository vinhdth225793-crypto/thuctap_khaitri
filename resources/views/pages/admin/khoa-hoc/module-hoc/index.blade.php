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
                Danh sách Module học
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.module-hoc.create') }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm module mới
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
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

    <!-- Search & Filter -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.module-hoc.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                               placeholder="Tên hoặc mã module..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="khoa_hoc_id" class="form-select vip-form-control">
                        <option value="">-- Tất cả khóa học --</option>
                        @foreach($khoaHocs as $kh)
                            <option value="{{ $kh->id }}" {{ $khoaHocId == $kh->id ? 'selected' : '' }}>
                                [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }} ({{ $kh->monHoc->ten_mon_hoc ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100 fw-bold">Lọc dữ liệu</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-light w-100 fw-bold border">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="vip-card shadow-sm border-0">
        <div class="vip-card-header bg-white border-bottom py-3">
            <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
                <i class="fas fa-list me-2"></i> Danh sách modules
            </h5>
        </div>
        <div class="vip-card-body p-0">
            @if($moduleHocs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light smaller text-muted text-uppercase">
                            <tr>
                                <th class="ps-4 text-center" width="60">STT</th>
                                <th>Mã module</th>
                                <th>Tên module</th>
                                <th>Khóa học / Môn học</th>
                                <th class="text-center">Thứ tự</th>
                                <th class="text-center">Thời lượng</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-center" width="180">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($moduleHocs as $index => $module)
                                <tr>
                                    <td class="text-center ps-4 text-muted small">{{ $moduleHocs->firstItem() + $index }}</td>
                                    <td><code class="fw-bold text-primary">{{ $module->ma_module }}</code></td>
                                    <td><span class="fw-bold text-dark">{{ $module->ten_module }}</span></td>
                                    <td>
                                        <div class="small fw-bold text-secondary">{{ $module->khoaHoc->ten_khoa_hoc }}</div>
                                        <div class="smaller text-muted mt-1"><i class="fas fa-book me-1"></i>{{ $module->khoaHoc->monHoc->ten_mon_hoc ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border shadow-xs">{{ $module->thu_tu_module }}</span>
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
                                            <span class="badge bg-success px-3">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary px-3">Tạm dừng</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.module-hoc.show', $module->id) }}" 
                                               class="btn btn-sm btn-primary action-btn" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.module-hoc.edit', $module->id) }}" 
                                               class="btn btn-sm btn-warning text-white action-btn" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.module-hoc.toggle-status', $module->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary action-btn" title="Thay đổi trạng thái">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.module-hoc.destroy', $module->id) }}" method="POST" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa module này?')" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger action-btn" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $moduleHocs->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-cubes fa-3x mb-3 opacity-25"></i>
                    <p>Không tìm thấy module nào.</p>
                    <a href="{{ route('admin.module-hoc.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Thêm module mới
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0; }
    .vip-form-control:focus { box-shadow: none; border-color: #0d6efd; }
</style>
@endsection
