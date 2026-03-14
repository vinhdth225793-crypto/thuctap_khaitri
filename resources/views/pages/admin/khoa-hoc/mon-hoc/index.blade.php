@extends('layouts.app')

@section('title', 'Quản lý Môn học')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-book me-2 text-primary"></i>
                Quản lý Môn học
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.mon-hoc.create') }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm môn học mới
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

    <!-- Search Bar -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.mon-hoc.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                               placeholder="Tìm theo tên hoặc mã môn học..." value="{{ $search ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100 fw-bold">Tìm kiếm</button>
                </div>
                @if($search ?? false)
                    <div class="col-md-1">
                        <a href="{{ route('admin.mon-hoc.index') }}" class="btn btn-link text-muted small p-0 text-decoration-none">Xóa lọc</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="vip-card shadow-sm border-0">
        <div class="vip-card-header bg-white border-bottom py-3">
            <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
                <i class="fas fa-list me-2"></i> Danh sách môn học
            </h5>
        </div>
        <div class="vip-card-body p-0">
            @if($monHocs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light smaller text-muted text-uppercase">
                            <tr>
                                <th class="ps-4 text-center" width="60">STT</th>
                                <th width="100">Hình ảnh</th>
                                <th>Mã môn học</th>
                                <th>Tên môn học</th>
                                <th class="text-center">Số khóa học</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-center" width="180">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monHocs as $index => $monHoc)
                                <tr>
                                    <td class="text-center ps-4 text-muted small">{{ $monHocs->firstItem() + $index }}</td>
                                    <td>
                                        <div class="rounded border bg-light overflow-hidden shadow-xs d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            @if($monHoc->hinh_anh)
                                                <img src="{{ asset($monHoc->hinh_anh) }}" alt="{{ $monHoc->ten_mon_hoc }}" class="img-fluid object-fit-cover w-100 h-100">
                                            @else
                                                <i class="fas fa-image text-muted opacity-25 fa-lg"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td><code class="fw-bold text-primary">{{ $monHoc->ma_mon_hoc }}</code></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $monHoc->ten_mon_hoc }}</div>
                                        @if($monHoc->mo_ta)
                                            <div class="smaller text-muted italic text-truncate" style="max-width: 300px;">{{ $monHoc->mo_ta }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill px-3 shadow-xs">
                                            {{ $monHoc->khoaHocs()->count() }} khóa
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($monHoc->trang_thai)
                                            <span class="badge bg-success px-3">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary px-3">Tạm dừng</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.mon-hoc.show', $monHoc->id) }}" 
                                               class="btn btn-sm btn-primary action-btn" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.mon-hoc.edit', $monHoc->id) }}" 
                                               class="btn btn-sm btn-warning text-white action-btn" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.mon-hoc.toggle-status', $monHoc->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary action-btn" title="Thay đổi trạng thái">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-danger action-btn" onclick="confirmDelete({{ $monHoc->id }})" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $monHocs->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <p>Không tìm thấy môn học nào.</p>
                    <a href="{{ route('admin.mon-hoc.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Thêm môn học mới
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade shadow" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-danger"><i class="fas fa-trash fa-3x opacity-25"></i></div>
                <p class="mb-1 fw-bold fs-5">Bạn có chắc chắn muốn xóa?</p>
                <p class="text-muted small mb-0">Tất cả dữ liệu khóa học liên quan đến môn học này cũng sẽ bị xóa vĩnh viễn.</p>
            </div>
            <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteForm" method="POST" action="" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Đồng ý xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(id) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/mon-hoc/${id}`;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>
@endpush

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .object-fit-cover { object-fit: cover; }
    .italic { font-style: italic; }
    .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0; }
    .vip-form-control:focus { box-shadow: none; border-color: #0d6efd; }
</style>
@endsection
