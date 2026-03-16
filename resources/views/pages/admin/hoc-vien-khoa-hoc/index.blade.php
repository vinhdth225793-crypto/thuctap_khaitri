@extends('layouts.app')

@section('title', 'Quản lý học viên — ' . $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ma_khoa_hoc }}</a></li>
            <li class="breadcrumb-item active">Học viên</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-users me-2 text-success"></i>
                Học viên khóa học: {{ $khoaHoc->ten_khoa_hoc }}
            </h4>
            <div class="small text-muted">
                Mã lớp: <code class="fw-bold">{{ $khoaHoc->ma_khoa_hoc }}</code> |
                Nhóm ngành: <span class="fw-bold text-dark">{{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddHocVien">
                <i class="fas fa-user-plus me-1"></i> THÊM HỌC VIÊN
            </button>
            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại chi tiết
            </a>
        </div>
    </div>

    @include('components.alert')

    <!-- Thống kê nhanh -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-primary text-white">
                <div class="smaller text-uppercase fw-bold opacity-75">Tổng học viên</div>
                <div class="fs-2 fw-bold">{{ $stats['tong'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-success text-white">
                <div class="smaller text-uppercase fw-bold opacity-75">Đang học</div>
                <div class="fs-2 fw-bold">{{ $stats['dang_hoc'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-info text-white">
                <div class="smaller text-uppercase fw-bold opacity-75">Hoàn thành</div>
                <div class="fs-2 fw-bold">{{ $stats['hoan_thanh'] }}</div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách học viên -->
    <div class="vip-card shadow-sm border-0">
        <div class="vip-card-header bg-white border-bottom py-3">
            <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Danh sách học viên trong lớp</h5>
        </div>
        <div class="vip-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light smaller text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 text-center" width="60">STT</th>
                            <th>Học viên</th>
                            <th>Thông tin liên hệ</th>
                            <th class="text-center">Ngày ghi danh</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="pe-4 text-center" width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hocViens as $index => $bghv)
                            <tr>
                                <td class="text-center ps-4 text-muted small">{{ $hocViens->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-mini rounded-circle bg-light border text-center me-2" style="width: 35px; height: 35px; line-height: 35px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $bghv->hocVien->ho_ten ?? 'N/A' }}</div>
                                            <code class="smaller">#{{ $bghv->hocVien->ma_nguoi_dung ?? 'N/A' }}</code>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><i class="far fa-envelope me-1 text-muted"></i>{{ $bghv->hocVien->email ?? 'N/A' }}</div>
                                    <div class="small mt-1"><i class="fas fa-phone-alt me-1 text-muted"></i>{{ $bghv->hocVien->so_dien_thoai ?? 'N/A' }}</div>
                                </td>
                                <td class="text-center small">
                                    {{ $bghv->ngay_tham_gia ? $bghv->ngay_tham_gia->format('d/m/Y') : '─' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $bghv->trang_thai_badge }} shadow-xs">
                                        {{ $bghv->trang_thai_label }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning border-0 btn-edit-enroll" 
                                                data-id="{{ $bghv->id }}"
                                                data-name="{{ $bghv->hocVien->ho_ten }}"
                                                data-date="{{ $bghv->ngay_tham_gia ? $bghv->ngay_tham_gia->format('Y-m-d') : '' }}"
                                                data-status="{{ $bghv->trang_thai }}"
                                                data-note="{{ $bghv->ghi_chu }}"
                                                title="Sửa ghi danh">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.khoa-hoc.hoc-vien.destroy', [$khoaHoc->id, $bghv->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa học viên khỏi khóa học này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Xóa khỏi lớp">
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted small italic">
                                    Khóa học này hiện chưa có học viên nào tham gia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $hocViens->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL THÊM HỌC VIÊN (PHASE 3) --}}
<div class="modal fade shadow" id="modalAddHocVien" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Thêm học viên vào lớp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.hoc-vien.store', $khoaHoc->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold">1. Chọn học viên từ hệ thống <span class="text-danger">*</span></label>
                        <div class="alert alert-info border-0 smaller mb-3">
                            <i class="fas fa-info-circle me-1"></i> Danh sách dưới đây chỉ hiển thị những học viên chưa tham gia khóa học này.
                        </div>
                        <div class="table-responsive border rounded" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="text-center" width="40">Chọn</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>SĐT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($availableStudents as $student)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="hoc_vien_ids[]" value="{{ $student->ma_nguoi_dung }}" class="form-check-input">
                                            </td>
                                            <td class="fw-bold small">{{ $student->ho_ten }}</td>
                                            <td class="small">{{ $student->email }}</td>
                                            <td class="small text-muted">{{ $student->so_dien_thoai }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted small italic">Không còn học viên nào khả dụng để thêm.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">2. Ngày tham gia</label>
                            <input type="date" name="ngay_tham_gia" class="form-control vip-form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">3. Ghi chú (Nếu có)</label>
                            <textarea name="ghi_chu" class="form-control vip-form-control" rows="2" placeholder="VD: Học viên chuyển lớp từ khóa khác sang..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">XÁC NHẬN THÊM</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL SỬA GHI DANH (PHASE 5) --}}
<div class="modal fade shadow" id="modalEditEnrollment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Chỉnh sửa ghi danh</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditEnrollment" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="smaller text-muted text-uppercase fw-bold mb-1">Học viên:</label>
                        <div id="edit-hv-name" class="fw-bold fs-5 text-dark border-bottom pb-2"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Ngày tham gia</label>
                            <input type="date" name="ngay_tham_gia" id="edit-ngay-tham-gia" class="form-control vip-form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Trạng thái học tập</label>
                            <select name="trang_thai" id="edit-trang-thai" class="form-select vip-form-control" required>
                                <option value="dang_hoc">Đang học</option>
                                <option value="hoan_thanh">Hoàn thành</option>
                                <option value="ngung_hoc">Ngừng học</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Ghi chú</label>
                            <textarea name="ghi_chu" id="edit-ghi-chu" class="form-control vip-form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white">CẬP NHẬT</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý mở Modal Sửa
    const modalEdit = new bootstrap.Modal(document.getElementById('modalEditEnrollment'));
    const formEdit = document.getElementById('formEditEnrollment');
    const nameLabel = document.getElementById('edit-hv-name');
    const dateInput = document.getElementById('edit-ngay-tham-gia');
    const statusSelect = document.getElementById('edit-trang-thai');
    const noteTextarea = document.getElementById('edit-ghi-chu');

    document.querySelectorAll('.btn-edit-enroll').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            nameLabel.textContent = this.dataset.name;
            dateInput.value = this.dataset.date;
            statusSelect.value = this.dataset.status;
            noteTextarea.value = this.dataset.note;
            
            formEdit.action = `{{ url('admin/khoa-hoc/'.$khoaHoc->id.'/hoc-vien') }}/${id}`;
            modalEdit.show();
        });
    });
});
</script>
@endpush

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .italic { font-style: italic; }
</style>
@endsection
