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
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-success rounded-circle p-2 me-3 shadow-sm" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Thêm học viên vào lớp</h5>
                        <p class="small mb-0 opacity-75">Chọn học viên từ danh sách hệ thống để ghi danh</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.hoc-vien.store', $khoaHoc->id) }}" method="POST" id="formAddHocVien">
                @csrf
                <div class="modal-body p-0">
                    <div class="row g-0 h-100">
                        <!-- Cột bên trái: Danh sách học viên -->
                        <div class="col-lg-8 border-end d-flex flex-column" style="height: 600px;">
                            <div class="p-3 bg-light border-bottom sticky-top">
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="searchHocVien" class="form-control border-start-0 ps-0 py-2" placeholder="Tìm kiếm theo tên, email hoặc số điện thoại...">
                                    <button class="btn btn-outline-secondary btn-sm px-3" type="button" id="btnClearSearch">Xóa</button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                    <div class="small text-muted">
                                        Hiển thị: <span id="visibleCount" class="fw-bold text-dark">{{ count($availableStudents) }}</span> / {{ count($availableStudents) }} học viên
                                    </div>
                                    <div class="form-check small mb-0">
                                        <input class="form-check-input" type="checkbox" id="checkAllHocVien">
                                        <label class="form-check-label fw-bold text-success cursor-pointer" for="checkAllHocVien">
                                            CHỌN TẤT CẢ HIỂN THỊ
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex-grow-1 overflow-auto bg-white">
                                <table class="table table-hover align-middle mb-0" id="tableAvailableHocVien">
                                    <thead class="bg-light smaller text-muted text-uppercase sticky-top" style="top: -1px; z-index: 10;">
                                        <tr>
                                            <th class="text-center" width="50">#</th>
                                            <th>Thông tin học viên</th>
                                            <th class="text-center">Liên hệ</th>
                                            <th class="text-center" width="80">Chọn</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($availableStudents as $student)
                                            <tr class="hoc-vien-row pointer-row" data-search="{{ strtolower($student->ho_ten . ' ' . $student->email . ' ' . $student->so_dien_thoai) }}">
                                                <td class="text-center text-muted smaller">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $student->ho_ten }}</div>
                                                    <div class="smaller text-muted italic">ID: #{{ $student->ma_nguoi_dung }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="smaller text-dark">{{ $student->email }}</div>
                                                    <div class="smaller text-muted">{{ $student->so_dien_thoai }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input type="checkbox" name="hoc_vien_ids[]" value="{{ $student->ma_nguoi_dung }}" 
                                                               class="form-check-input hoc-vien-checkbox shadow-none border-secondary"
                                                               data-name="{{ $student->ho_ten }}">
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted small italic">
                                                    Không có học viên nào khả dụng để thêm vào lớp.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Cột bên phải: Cấu hình và tóm tắt -->
                        <div class="col-lg-4 bg-light d-flex flex-column" style="height: 600px;">
                            <div class="p-4 flex-grow-1 overflow-auto">
                                <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="fas fa-cog me-2"></i>CẤU HÌNH GHI DANH</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Ngày tham gia</label>
                                    <input type="date" name="ngay_tham_gia" class="form-control vip-form-control border-0 shadow-sm" value="{{ date('Y-m-d') }}">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Ghi chú mặc định</label>
                                    <textarea name="ghi_chu" class="form-control vip-form-control border-0 shadow-sm" rows="3" placeholder="Ghi chú chung cho các học viên được chọn..."></textarea>
                                </div>

                                <div class="card border-0 shadow-sm rounded-3 mb-3">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small fw-bold text-uppercase">Đã chọn:</span>
                                            <span class="badge bg-success rounded-pill px-3 py-2 fs-6 shadow-sm" id="selectedCountDisplay">0</span>
                                        </div>
                                        <div id="selectedNamesContainer" class="mt-3 overflow-auto" style="max-height: 200px;">
                                            <div class="text-center text-muted smaller py-4 italic" id="emptySelectedText">
                                                Chưa có học viên nào được chọn
                                            </div>
                                            <div id="selectedList" class="d-flex flex-wrap gap-1"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning border-0 smaller">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Bạn có thể thêm nhiều học viên cùng lúc. Hệ thống sẽ tự động bỏ qua các trường hợp đã tồn tại trong lớp.
                                </div>
                            </div>

                            <div class="p-4 border-top mt-auto bg-white">
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow disabled" id="btnConfirmAdd">
                                    XÁC NHẬN THÊM VÀO LỚP
                                </button>
                                <button type="button" class="btn btn-link text-muted w-100 mt-2 smaller text-decoration-none" data-bs-dismiss="modal">Đóng cửa sổ</button>
                            </div>
                        </div>
                    </div>
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
    // 1. XỬ LÝ MODAL THÊM HỌC VIÊN
    const modalAdd = document.getElementById('modalAddHocVien');
    if (modalAdd) {
        const searchInput = document.getElementById('searchHocVien');
        const btnClearSearch = document.getElementById('btnClearSearch');
        const checkAll = document.getElementById('checkAllHocVien');
        const hocVienRows = document.querySelectorAll('.hoc-vien-row');
        const hocVienCheckboxes = document.querySelectorAll('.hoc-vien-checkbox');
        const selectedCountDisplay = document.getElementById('selectedCountDisplay');
        const selectedList = document.getElementById('selectedList');
        const emptyText = document.getElementById('emptySelectedText');
        const btnConfirm = document.getElementById('btnConfirmAdd');
        const visibleCountSpan = document.getElementById('visibleCount');

        // Hàm cập nhật trạng thái UI (số lượng chọn, danh sách tên, nút xác nhận)
        function updateSelectionUI() {
            const selectedBoxes = document.querySelectorAll('.hoc-vien-checkbox:checked');
            const count = selectedBoxes.length;
            
            selectedCountDisplay.textContent = count;
            
            if (count > 0) {
                emptyText.classList.add('d-none');
                btnConfirm.classList.remove('disabled');
                selectedCountDisplay.classList.replace('bg-secondary', 'bg-success');
            } else {
                emptyText.classList.remove('d-none');
                btnConfirm.classList.add('disabled');
                selectedCountDisplay.classList.replace('bg-success', 'bg-secondary');
            }

            // Cập nhật danh sách tag tên học viên đã chọn (tối đa hiện 10 người cho đỡ rối)
            selectedList.innerHTML = '';
            selectedBoxes.forEach((box, index) => {
                if (index < 10) {
                    const span = document.createElement('span');
                    span.className = 'badge bg-white text-dark border shadow-sm smaller fw-normal mb-1 me-1 px-2 py-1';
                    span.innerHTML = `<i class="fas fa-check text-success me-1"></i> ${box.dataset.name}`;
                    selectedList.appendChild(span);
                }
            });
            
            if (count > 10) {
                const more = document.createElement('span');
                more.className = 'smaller text-muted ms-1';
                more.textContent = `và ${count - 10} người khác...`;
                selectedList.appendChild(more);
            }
        }

        // Tìm kiếm nhanh
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            let visibleCount = 0;

            hocVienRows.forEach(row => {
                const searchText = row.dataset.search;
                if (searchText.includes(term)) {
                    row.classList.remove('d-none');
                    visibleCount++;
                } else {
                    row.classList.add('d-none');
                    // Nếu dòng bị ẩn, bỏ check (tùy chọn - ở đây tôi giữ check nhưng check-all sẽ chỉ áp dụng cho dòng hiện)
                }
            });

            visibleCountSpan.textContent = visibleCount;
            // Bỏ check-all nếu đang check mà kết quả tìm kiếm thay đổi
            checkAll.checked = false;
        });

        // Xóa tìm kiếm
        btnClearSearch.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        });

        // Chọn tất cả (chỉ các dòng đang hiển thị)
        checkAll.addEventListener('change', function() {
            const isChecked = this.checked;
            hocVienRows.forEach(row => {
                if (!row.classList.contains('d-none')) {
                    const cb = row.querySelector('.hoc-vien-checkbox');
                    if (cb) cb.checked = isChecked;
                }
            });
            updateSelectionUI();
        });

        // Click vào dòng để chọn
        hocVienRows.forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const cb = this.querySelector('.hoc-vien-checkbox');
                    if (cb) {
                        cb.checked = !cb.checked;
                        updateSelectionUI();
                    }
                }
            });
        });

        // Thay đổi checkbox lẻ
        hocVienCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectionUI);
        });
    }

    // 2. XỬ LÝ MODAL SỬA GHI DANH
    const modalEditEl = document.getElementById('modalEditEnrollment');
    if (modalEditEl) {
        const modalEdit = new bootstrap.Modal(modalEditEl);
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
    }
});
</script>
@endpush

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .italic { font-style: italic; }
    .cursor-pointer { cursor: pointer; }
    .pointer-row { cursor: pointer; transition: all 0.2s; }
    .pointer-row:hover { background-color: rgba(25, 135, 84, 0.05) !important; }
    .hoc-vien-checkbox { width: 1.2rem; height: 1.2rem; cursor: pointer; }
    .sticky-top { z-index: 10; }
</style>
@endsection
