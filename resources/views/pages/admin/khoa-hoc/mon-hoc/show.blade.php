@extends('layouts.app')

@section('title', 'Chi tiết Nhóm ngành: ' . $nhomNganh->ten_nhom_nganh)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mon-hoc.index') }}">Nhóm ngành</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $nhomNganh->ma_nhom_nganh }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <h3 class="fw-bold mb-0 text-dark">{{ $nhomNganh->ten_nhom_nganh }}</h3>
                <span class="badge bg-{{ $nhomNganh->trang_thai ? 'success' : 'secondary' }} ms-3 px-3 shadow-xs">
                    {{ $nhomNganh->trang_thai ? 'Đang hoạt động' : 'Tạm dừng' }}
                </span>
            </div>
            <div class="mt-2 text-muted">
                Mã nhóm ngành: <code class="fw-bold text-primary">{{ $nhomNganh->ma_nhom_nganh }}</code>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.mon-hoc.edit', $nhomNganh->id) }}" class="btn btn-warning text-white fw-bold shadow-sm px-4">
                <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </a>
            <button class="btn btn-outline-danger fw-bold ms-1" onclick="confirmDelete({{ $nhomNganh->id }})">
                <i class="fas fa-trash me-1"></i> Xóa
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Cột trái: Thông tin & Hình ảnh -->
        <div class="col-lg-4">
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-body p-4 text-center">
                    <div class="rounded border bg-light overflow-hidden mb-4 shadow-xs mx-auto" style="width: 100%; height: 240px;">
                        @if($nhomNganh->hinh_anh)
                            <img src="{{ asset($nhomNganh->hinh_anh) }}" alt="{{ $nhomNganh->ten_nhom_nganh }}" class="img-fluid object-fit-cover w-100 h-100">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 opacity-25">
                                <i class="fas fa-layer-group fa-5x"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-start">
                        <h6 class="smaller fw-bold text-muted text-uppercase mb-2">Mô tả nhóm ngành</h6>
                        <div class="bg-light p-3 rounded border border-dashed text-dark small lh-lg">
                            {!! $nhomNganh->mo_ta ? nl2br(e($nhomNganh->mo_ta)) : '<span class="text-muted italic">Chưa có mô tả chi tiết.</span>' !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="vip-card shadow-sm border-0 bg-light">
                <div class="vip-card-body p-3 smaller">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngày khởi tạo:</span>
                        <span class="fw-bold">{{ $nhomNganh->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Cập nhật cuối:</span>
                        <span class="fw-bold">{{ $nhomNganh->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Danh sách khóa học liên quan -->
        <div class="col-lg-8">
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">🎓 Khóa học thuộc nhóm ngành này</h5>
                    <a href="{{ route('admin.khoa-hoc.create', ['nhom_nganh_id' => $nhomNganh->id]) }}" class="btn btn-primary btn-sm px-3 fw-bold">
                        <i class="fas fa-plus me-1"></i> Thêm khóa học
                    </a>
                </div>
                <div class="vip-card-body p-0">
                    @if($khoaHocs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light smaller text-muted">
                                    <tr>
                                        <th class="ps-4" width="120">Mã KH</th>
                                        <th>Tên khóa học</th>
                                        <th class="text-center">Cấp độ</th>
                                        <th class="text-center">Module</th>
                                        <th class="text-center">Loại</th>
                                        <th class="pe-4 text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($khoaHocs as $kh)
                                        <tr>
                                            <td class="ps-4"><code class="fw-bold">{{ $kh->ma_khoa_hoc }}</code></td>
                                            <td><span class="fw-bold text-dark">{{ $kh->ten_khoa_hoc }}</span></td>
                                            <td class="text-center">
                                                @php
                                                    $cd = [
                                                        'co_ban' => ['t' => 'Cơ bản', 'c' => 'info'],
                                                        'trung_binh' => ['t' => 'Trung bình', 'c' => 'warning text-dark'],
                                                        'nang_cao' => ['t' => 'Nâng cao', 'c' => 'danger']
                                                    ][$kh->cap_do] ?? ['t' => 'N/A', 'c' => 'secondary'];
                                                @endphp
                                                <span class="badge bg-{{ $cd['c'] }} smaller">{{ $cd['t'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border rounded-pill px-2">{{ $kh->module_hocs_count ?? $kh->moduleHocs()->count() }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="smaller fw-bold text-{{ $kh->loai === 'mau' ? 'info' : 'primary' }}">
                                                    {{ $kh->loai === 'mau' ? 'MẪU' : 'LỚP' }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-center">
                                                <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" class="btn btn-sm btn-outline-primary border-0">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 border-top d-flex justify-content-center">
                            {{ $khoaHocs->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5 text-muted small italic">
                            Chưa có khóa học nào thuộc nhóm ngành này.
                        </div>
                    @endif
                </div>
            </div>
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
                <p class="mb-1 fw-bold fs-5">Bạn có chắc chắn muốn xóa nhóm ngành này?</p>
                <p class="text-muted small mb-0">Tất cả dữ liệu khóa học và module liên quan sẽ bị xóa vĩnh viễn.</p>
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
        form.action = `/admin/nhom-nganh/${id}`;
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
    .border-dashed { border-style: dashed !important; }
</style>
@endsection
