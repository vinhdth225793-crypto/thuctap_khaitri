@extends('layouts.app')

@section('title', 'Quản lý khóa học')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Quản lý khóa học</h3>
                <a href="{{ route('admin.khoa-hoc.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm khóa học mới
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="row mb-4">
        <div class="col-12">
            <form method="GET" action="{{ route('admin.khoa-hoc.index') }}" class="row g-3">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        class="form-control vip-form-control"
                        placeholder="Tìm kiếm theo tên hoặc mã khóa học..."
                        value="{{ $search ?? '' }}"
                    >
                </div>
                <div class="col-md-4">
                    <select name="mon_hoc_id" class="form-select vip-form-control">
                        <option value="">Tất cả môn học</option>
                        @foreach($monHocs as $monHoc)
                            <option value="{{ $monHoc->id }}" {{ $monHocId == $monHoc->id ? 'selected' : '' }}>
                                {{ $monHoc->ten_mon_hoc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">
                        <i class="fas fa-graduation-cap"></i> Danh sách khóa học
                    </h5>
                </div>
                <div class="vip-card-body">
                    @if($khoaHocs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Hình ảnh</th>
                                        <th>Mã KH</th>
                                        <th>Tên khóa học</th>
                                        <th>Môn học</th>
                                        <th>Cấp độ</th>
                                        <th>Modules</th>
                                        <th>Giảng viên</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($khoaHocs as $index => $khoaHoc)
                                        <tr>
                                            <td>{{ $khoaHocs->firstItem() + $index }}</td>
                                            <td>
                                                @if($khoaHoc->hinh_anh)
                                                    <img src="{{ asset($khoaHoc->hinh_anh) }}" alt="{{ $khoaHoc->ten_khoa_hoc }}" class="img-thumbnail" style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light p-1 rounded text-center" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image" style="color: #ccc;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><strong>{{ $khoaHoc->ma_khoa_hoc }}</strong></td>
                                            <td>
                                                <div>{{ $khoaHoc->ten_khoa_hoc }}</div>
                                                @if($khoaHoc->mo_ta_ngan)
                                                    <small class="text-muted d-block">{{ Str::limit($khoaHoc->mo_ta_ngan, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $khoaHoc->monHoc->ten_mon_hoc ?? 'N/A' }}</td>
                                            <td>
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
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $khoaHoc->tong_so_module }} module</span>
                                            </td>
                                            <td>
                                                {{ $khoaHoc->giangViens()->distinct()->count() }} GV
                                            </td>
                                            <td>
                                                @if($khoaHoc->trang_thai)
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-danger">Tạm dừng</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->id) }}" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.khoa-hoc.toggle-status', $khoaHoc->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary" title="Thay đổi trạng thái">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete({{ $khoaHoc->id }})" title="Xóa">
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
                            {{ $khoaHocs->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-graduation-cap" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">Không có khóa học nào.</p>
                            <a href="{{ route('admin.khoa-hoc.create') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> Thêm khóa học mới
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
                <p>Bạn có chắc muốn xóa khóa học này không?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Tất cả module và phân công giảng viên thuộc khóa học này cũng sẽ bị xóa.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
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
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `{{ url('admin/khoa-hoc') }}/${khoaHocId}`;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection
