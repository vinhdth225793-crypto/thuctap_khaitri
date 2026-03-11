@extends('layouts.app')

@section('title', 'Quản lý môn học')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Quản lý môn học</h3>
                <a href="{{ route('admin.mon-hoc.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm môn học mới
                </a>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <form method="GET" action="{{ route('admin.mon-hoc.index') }}" class="row g-3">
                <div class="col-md-9">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control vip-form-control" 
                        placeholder="Tìm kiếm theo tên hoặc mã môn học..." 
                        value="{{ $search ?? '' }}"
                    >
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">
                        <i class="fas fa-book"></i> Danh sách môn học
                    </h5>
                </div>
                <div class="vip-card-body">
                    @if($monHocs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Hình ảnh</th>
                                        <th>Mã môn học</th>
                                        <th>Tên môn học</th>
                                        <th>Số khóa học</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monHocs as $index => $monHoc)
                                        <tr>
                                            <td>{{ $monHocs->firstItem() + $index }}</td>
                                            <td>
                                                @if($monHoc->hinh_anh)
                                                    <img src="{{ asset($monHoc->hinh_anh) }}" alt="{{ $monHoc->ten_mon_hoc }}" class="img-thumbnail" style="max-width: 80px; max-height: 80px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light p-2 rounded text-center" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image" style="color: #ccc;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><strong>{{ $monHoc->ma_mon_hoc }}</strong></td>
                                            <td>
                                                <div>{{ $monHoc->ten_mon_hoc }}</div>
                                                @if($monHoc->mo_ta)
                                                    <small class="text-muted d-block">{{ Str::limit($monHoc->mo_ta, 80) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $monHoc->khoaHocs()->count() }} khóa
                                                </span>
                                            </td>
                                            <td>
                                                @if($monHoc->trang_thai)
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-danger">Tạm dừng</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.mon-hoc.show', $monHoc->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.mon-hoc.edit', $monHoc->id) }}" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.mon-hoc.toggle-status', $monHoc->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary" title="Thay đổi trạng thái">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete({{ $monHoc->id }})" title="Xóa">
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $monHocs->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">Không có môn học nào.</p>
                            <a href="{{ route('admin.mon-hoc.create') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> Thêm môn học mới
                            </a>
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
                <p>Bạn có chắc muốn xóa môn học này không?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Tất cả khóa học thuộc môn học này cũng sẽ bị xóa.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" action="" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
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
@endsection
