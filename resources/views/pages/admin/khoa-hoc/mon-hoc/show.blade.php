@extends('layouts.app')

@section('title', 'Chi tiết môn học')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('admin.mon-hoc.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="vip-card">
                <div class="vip-card-body text-center">
                    @if($monHoc->hinh_anh)
                        <img src="{{ asset($monHoc->hinh_anh) }}" alt="{{ $monHoc->ten_mon_hoc }}" class="img-fluid mb-3" style="max-height: 300px;">
                    @else
                        <div class="bg-light p-5 mb-3 rounded">
                            <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                        </div>
                    @endif
                    <h4>{{ $monHoc->ten_mon_hoc }}</h4>
                    <p class="text-muted">
                        <strong>Mã:</strong> {{ $monHoc->ma_mon_hoc }}
                    </p>
                    <div class="mb-3">
                        @if($monHoc->trang_thai)
                            <span class="badge bg-success">Hoạt động</span>
                        @else
                            <span class="badge bg-danger">Tạm dừng</span>
                        @endif
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.mon-hoc.edit', $monHoc->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <button class="btn btn-danger" onclick="confirmDelete({{ $monHoc->id }})">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="vip-card mb-4">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">
                        <i class="fas fa-info-circle"></i> Thông tin chi tiết
                    </h5>
                </div>
                <div class="vip-card-body">
                    <p>
                        <strong>Mô tả:</strong>
                    </p>
                    <p>{{ $monHoc->mo_ta ?? 'Chưa có mô tả' }}</p>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Ngày tạo:</strong><br>
                                {{ $monHoc->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Ngày cập nhật:</strong><br>
                                {{ $monHoc->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="vip-card">
                <div class="vip-card-header d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title">
                        <i class="fas fa-list"></i> Các khóa học ({{ $khoaHocs->total() }})
                    </h5>
                    <a href="{{ route('admin.khoa-hoc.create') }}?mon_hoc_id={{ $monHoc->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Thêm khóa học cho môn này
                    </a>
                </div>
                <div class="vip-card-body">
                    @if($khoaHocs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã khóa học</th>
                                        <th>Tên khóa học</th>
                                        <th>Cấp độ</th>
                                        <th>Module</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($khoaHocs as $khoaHoc)
                                        <tr>
                                            <td><strong>{{ $khoaHoc->ma_khoa_hoc }}</strong></td>
                                            <td>{{ $khoaHoc->ten_khoa_hoc }}</td>
                                            <td>
                                                @if($khoaHoc->cap_do === 'co_ban')
                                                    <span class="badge bg-info">Cơ bản</span>
                                                @elseif($khoaHoc->cap_do === 'trung_binh')
                                                    <span class="badge bg-warning">Trung bình</span>
                                                @else
                                                    <span class="badge bg-danger">Nâng cao</span>
                                                @endif
                                            </td>
                                            <td>{{ $khoaHoc->moduleHocs()->count() }}</td>
                                            <td>
                                                @if($khoaHoc->trang_thai)
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Tạm dừng</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $khoaHocs->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">Chưa có khóa học nào.</p>
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
                    Tất cả khóa học và module thuộc môn học này cũng sẽ bị xóa.
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
