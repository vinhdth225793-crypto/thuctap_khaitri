@extends('layouts.app')

@section('title', 'Quản lý học viên — ' . $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ma_khoa_hoc }}</a></li>
            <li class="breadcrumb-item active">Học viên</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-users me-2 text-success"></i>Học viên khóa học: {{ $khoaHoc->ten_khoa_hoc }}</h4>
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

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="vip-card p-3 text-center border-0 shadow-sm bg-primary text-white"><div class="smaller text-uppercase fw-bold opacity-75">Tổng học viên</div><div class="fs-2 fw-bold">{{ $stats['tong'] }}</div></div></div>
        <div class="col-md-4"><div class="vip-card p-3 text-center border-0 shadow-sm bg-success text-white"><div class="smaller text-uppercase fw-bold opacity-75">Đang học</div><div class="fs-2 fw-bold">{{ $stats['dang_hoc'] }}</div></div></div>
        <div class="col-md-4"><div class="vip-card p-3 text-center border-0 shadow-sm bg-info text-white"><div class="smaller text-uppercase fw-bold opacity-75">Hoàn thành</div><div class="fs-2 fw-bold">{{ $stats['hoan_thanh'] }}</div></div></div>
    </div>

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
                                <td><div class="d-flex align-items-center"><div class="avatar-mini rounded-circle bg-light border text-center me-2" style="width:35px;height:35px;line-height:35px;"><i class="fas fa-user text-muted"></i></div><div><div class="fw-bold text-dark">{{ $bghv->hocVien->nguoiDung->ho_ten ?? 'N/A' }}</div><code class="smaller">#{{ $bghv->hocVien->ma_hoc_vien ?? $bghv->hocVien->id }}</code></div></div></td>
                                <td><div class="small"><i class="far fa-envelope me-1 text-muted"></i>{{ $bghv->hocVien->nguoiDung->email ?? 'N/A' }}</div><div class="small mt-1"><i class="fas fa-phone-alt me-1 text-muted"></i>{{ $bghv->hocVien->nguoiDung->so_dien_thoai ?? 'N/A' }}</div></td>
                                <td class="text-center small">{{ $bghv->ngay_tham_gia ? $bghv->ngay_tham_gia->format('d/m/Y') : '—' }}</td>
                                <td class="text-center"><span class="badge {{ $bghv->trang_thai_badge }} shadow-xs">{{ $bghv->trang_thai_label }}</span></td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning border-0 btn-edit-enroll" data-id="{{ $bghv->id }}" data-name="{{ $bghv->hocVien->nguoiDung->ho_ten ?? 'N/A' }}" data-date="{{ $bghv->ngay_tham_gia ? $bghv->ngay_tham_gia->format('Y-m-d') : '' }}" data-status="{{ $bghv->trang_thai }}" data-note="{{ $bghv->ghi_chu }}" title="Sửa ghi danh"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('admin.khoa-hoc.hoc-vien.destroy', [$khoaHoc->id, $bghv->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa học viên khỏi khóa học này?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Xóa khỏi lớp"><i class="fas fa-user-times"></i></button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted small italic">Khóa học này hiện chưa có học viên nào tham gia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top d-flex justify-content-center">{{ $hocViens->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>

<div class="modal fade shadow" id="modalAddHocVien" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-success rounded-circle p-2 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width:45px;height:45px;"><i class="fas fa-user-plus fs-5"></i></div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Thêm học viên vào lớp</h5>
                        <p class="small mb-0 opacity-75">Tìm nhanh theo ký tự nhập và chọn nhiều học viên cùng lúc</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.hoc-vien.store', $khoaHoc->id) }}" method="POST" id="formAddHocVien">
                @csrf
                <div class="modal-body p-0">
                    <div class="row g-0 h-100">
                        <div class="col-lg-7 border-end d-flex flex-column" style="height:600px;">
                            <div class="p-3 bg-light border-bottom">
                                <label for="searchHocVien" class="form-label small fw-bold text-uppercase text-muted mb-2">Tìm học viên từ hệ thống</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="searchHocVien" class="form-control border-start-0 ps-0 py-2" placeholder="Nhập tên, email, số điện thoại hoặc mã học viên..." autocomplete="off">
                                    <button class="btn btn-outline-secondary px-3" type="button" id="btnClearSearch">Xóa</button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 gap-2 flex-wrap">
                                    <div class="small text-muted" id="searchMetaText">Gõ ký tự bất kỳ, hệ thống sẽ đề xuất ngay và ưu tiên kết quả theo tên học viên trước.</div>
                                    <span class="badge bg-white text-success border shadow-sm" id="resultCountBadge">0 gợi ý</span>
                                </div>
                            </div>
                            <div class="flex-grow-1 overflow-auto bg-white p-3">
                                <div id="searchInitialState" class="search-state text-center text-muted py-5">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;"><i class="fas fa-user-search fa-lg text-success"></i></div>
                                    <div class="fw-bold text-dark mb-2">Tìm nhanh học viên theo ký tự nhập vào</div>
                                    <div class="small mx-auto" style="max-width:420px;">Kết quả sẽ ưu tiên tên học viên, sau đó mới đến email, số điện thoại và mã học viên để bạn chọn nhanh hơn.</div>
                                </div>
                                <div id="searchLoadingState" class="search-state text-center text-muted py-5 d-none">
                                    <div class="spinner-border text-success mb-3" role="status"></div>
                                    <div class="fw-bold text-dark">Đang tìm gợi ý học viên...</div>
                                    <div class="small">Danh sách sẽ cập nhật ngay khi có kết quả</div>
                                </div>
                                <div id="searchEmptyState" class="search-state text-center text-muted py-5 d-none">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;"><i class="fas fa-user-slash fa-lg text-secondary"></i></div>
                                    <div class="fw-bold text-dark mb-2" id="searchEmptyTitle">Không có gợi ý phù hợp</div>
                                    <div class="small mx-auto" id="searchEmptyText" style="max-width:420px;">Thử đổi từ khóa khác hoặc kiểm tra lại thông tin học viên cần tìm.</div>
                                </div>
                                <div id="searchResults" class="d-grid gap-2"></div>
                            </div>
                        </div>
                        <div class="col-lg-5 bg-light d-flex flex-column" style="height:600px;">
                            <div class="p-4 flex-grow-1 overflow-auto">
                                <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="fas fa-cog me-2"></i>CẤU HÌNH GHI DANH</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Ngày tham gia</label>
                                    <input type="date" name="ngay_tham_gia" class="form-control vip-form-control border-0 shadow-sm" value="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Ghi chú mặc định</label>
                                    <textarea name="ghi_chu" class="form-control vip-form-control border-0 shadow-sm" rows="3" placeholder="Ghi chú chung cho các học viên được chọn..."></textarea>
                                </div>
                                <div class="card border-0 shadow-sm rounded-3 mb-3">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small fw-bold text-uppercase">Đã chọn:</span>
                                            <span class="badge bg-secondary rounded-pill px-3 py-2 fs-6 shadow-sm" id="selectedCountDisplay">0</span>
                                        </div>
                                        <div id="selectedNamesContainer" class="mt-3 overflow-auto" style="max-height:220px;">
                                            <div class="text-center text-muted smaller py-4 italic" id="emptySelectedText">Chưa có học viên nào được chọn</div>
                                            <div id="selectedList" class="d-flex flex-wrap gap-2"></div>
                                        </div>
                                        <div id="selectedHocVienInputs"></div>
                                    </div>
                                </div>
                                <div class="alert alert-warning border-0 smaller mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Bạn có thể tìm và thêm nhiều học viên cùng lúc. Hệ thống sẽ tự động ẩn những học viên đã có trong khóa này.</div>
                            </div>
                            <div class="p-4 border-top mt-auto bg-white">
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow disabled" id="btnConfirmAdd" disabled>XÁC NHẬN THÊM VÀO LỚP</button>
                                <button type="button" class="btn btn-link text-muted w-100 mt-2 smaller text-decoration-none" data-bs-dismiss="modal">Đóng cửa sổ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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
document.addEventListener('DOMContentLoaded', function () {
    const modalAdd = document.getElementById('modalAddHocVien');

    if (modalAdd) {
        const formAdd = document.getElementById('formAddHocVien');
        const searchInput = document.getElementById('searchHocVien');
        const btnClearSearch = document.getElementById('btnClearSearch');
        const searchMetaText = document.getElementById('searchMetaText');
        const resultCountBadge = document.getElementById('resultCountBadge');
        const searchResults = document.getElementById('searchResults');
        const searchInitialState = document.getElementById('searchInitialState');
        const searchLoadingState = document.getElementById('searchLoadingState');
        const searchEmptyState = document.getElementById('searchEmptyState');
        const searchEmptyTitle = document.getElementById('searchEmptyTitle');
        const searchEmptyText = document.getElementById('searchEmptyText');
        const selectedCountDisplay = document.getElementById('selectedCountDisplay');
        const selectedList = document.getElementById('selectedList');
        const emptySelectedText = document.getElementById('emptySelectedText');
        const selectedHocVienInputs = document.getElementById('selectedHocVienInputs');
        const btnConfirm = document.getElementById('btnConfirmAdd');
        const searchEndpoint = @json(route('admin.khoa-hoc.hoc-vien.search', $khoaHoc->id));
        const selectedStudents = new Map();
        const defaultDateValue = formAdd.querySelector('[name="ngay_tham_gia"]').value;
        let currentResults = [];
        let debounceTimer = null;
        let activeController = null;
        let currentKeyword = '';

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function setSearchState(state) {
            searchInitialState.classList.toggle('d-none', state !== 'initial');
            searchLoadingState.classList.toggle('d-none', state !== 'loading');
            searchEmptyState.classList.toggle('d-none', state !== 'empty');
            searchResults.classList.toggle('d-none', state !== 'results');
        }

        function setSearchMeta(text, count) {
            searchMetaText.textContent = text;
            resultCountBadge.textContent = `${count} gợi ý`;
        }

        function renderSelectedStudents() {
            const students = Array.from(selectedStudents.values());
            const count = students.length;

            selectedCountDisplay.textContent = count;
            selectedCountDisplay.classList.toggle('bg-success', count > 0);
            selectedCountDisplay.classList.toggle('bg-secondary', count === 0);
            emptySelectedText.classList.toggle('d-none', count > 0);
            btnConfirm.disabled = count === 0;
            btnConfirm.classList.toggle('disabled', count === 0);
            selectedHocVienInputs.innerHTML = students.map((student) => `<input type="hidden" name="hoc_vien_ids[]" value="${student.id}">`).join('');
            selectedList.innerHTML = students.map((student) => `
                <span class="selected-student-chip">
                    <span class="fw-semibold text-dark">${escapeHtml(student.name)}</span>
                    <span class="small text-muted">#${escapeHtml(student.id)}</span>
                    <button type="button" class="selected-student-remove" data-remove-student="${escapeHtml(student.id)}" aria-label="Xóa học viên đã chọn">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
            `).join('');
        }

        function renderSearchResults() {
            if (!currentKeyword) {
                searchResults.innerHTML = '';
                setSearchState('initial');
                setSearchMeta('Gõ ký tự bất kỳ, hệ thống sẽ đề xuất ngay và ưu tiên kết quả theo tên học viên trước.', 0);
                return;
            }

            if (currentResults.length === 0) {
                searchResults.innerHTML = '';
                searchEmptyTitle.textContent = `Không tìm thấy học viên cho "${currentKeyword}"`;
                searchEmptyText.textContent = 'Thử đổi từ khóa khác hoặc tìm bằng email, số điện thoại, mã học viên.';
                setSearchState('empty');
                setSearchMeta(`Chưa có kết quả phù hợp cho "${currentKeyword}".`, 0);
                return;
            }

            searchResults.innerHTML = currentResults.map((student) => {
                const isSelected = selectedStudents.has(String(student.id));
                const displayPhone = student.phone ? escapeHtml(student.phone) : 'Chưa cập nhật';

                return `
                    <button type="button" class="student-suggestion ${isSelected ? 'is-selected' : ''}" data-select-student="${escapeHtml(student.id)}" data-student-name="${escapeHtml(student.name)}" data-student-email="${escapeHtml(student.email)}" data-student-phone="${escapeHtml(student.phone ?? '')}" ${isSelected ? 'disabled' : ''}>
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="fw-bold text-dark mb-1">${escapeHtml(student.name)}</div>
                                <div class="small text-muted mb-2">Mã học viên: #${escapeHtml(student.id)}</div>
                                <div class="small text-dark text-break"><i class="far fa-envelope me-2 text-muted"></i>${escapeHtml(student.email)}</div>
                                <div class="small text-muted mt-1"><i class="fas fa-phone-alt me-2"></i>${displayPhone}</div>
                            </div>
                            <div class="text-end"><span class="badge ${isSelected ? 'bg-success' : 'bg-light text-success border'}">${isSelected ? 'Đã chọn' : 'Chọn nhanh'}</span></div>
                        </div>
                    </button>
                `;
            }).join('');

            setSearchState('results');
            setSearchMeta(`Tìm thấy ${currentResults.length} gợi ý. Kết quả đang ưu tiên theo tên học viên.`, currentResults.length);
        }

        function performSearch(keyword) {
            if (activeController) {
                activeController.abort();
            }

            const controller = new AbortController();
            activeController = controller;
            currentKeyword = keyword;
            setSearchState('loading');
            setSearchMeta(`Đang tìm "${keyword}"...`, 0);

            fetch(`${searchEndpoint}?q=${encodeURIComponent(keyword)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('search_failed');
                    }
                    return response.json();
                })
                .then((payload) => {
                    if (searchInput.value.trim() !== keyword) {
                        return;
                    }
                    currentResults = Array.isArray(payload.data) ? payload.data : [];
                    currentKeyword = keyword;
                    renderSearchResults();
                })
                .catch((error) => {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    currentResults = [];
                    currentKeyword = keyword;
                    searchEmptyTitle.textContent = 'Không thể tải gợi ý lúc này';
                    searchEmptyText.textContent = 'Vui lòng thử lại sau ít phút hoặc tải lại trang.';
                    setSearchState('empty');
                    setSearchMeta('Tạm thời chưa tải được danh sách gợi ý.', 0);
                })
                .finally(() => {
                    if (activeController === controller) {
                        activeController = null;
                    }
                });
        }

        function queueSearch() {
            const keyword = searchInput.value.trim();
            currentKeyword = keyword;
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            if (keyword === '') {
                if (activeController) {
                    activeController.abort();
                }
                currentResults = [];
                renderSearchResults();
                return;
            }
            debounceTimer = setTimeout(() => performSearch(keyword), 220);
        }

        function resetAddModal() {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            if (activeController) {
                activeController.abort();
            }
            formAdd.reset();
            formAdd.querySelector('[name="ngay_tham_gia"]').value = defaultDateValue;
            searchInput.value = '';
            currentKeyword = '';
            currentResults = [];
            selectedStudents.clear();
            searchEmptyTitle.textContent = 'Không có gợi ý phù hợp';
            searchEmptyText.textContent = 'Thử đổi từ khóa khác hoặc kiểm tra lại thông tin học viên cần tìm.';
            renderSelectedStudents();
            renderSearchResults();
        }

        btnClearSearch.addEventListener('click', function () {
            searchInput.value = '';
            currentKeyword = '';
            currentResults = [];
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            if (activeController) {
                activeController.abort();
            }
            renderSearchResults();
            searchInput.focus();
        });

        searchInput.addEventListener('input', queueSearch);
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        searchResults.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-select-student]');
            if (!trigger || trigger.disabled) {
                return;
            }
            selectedStudents.set(String(trigger.dataset.selectStudent), {
                id: trigger.dataset.selectStudent,
                name: trigger.dataset.studentName,
                email: trigger.dataset.studentEmail,
                phone: trigger.dataset.studentPhone,
            });
            renderSelectedStudents();
            renderSearchResults();
        });

        selectedList.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-remove-student]');
            if (!trigger) {
                return;
            }
            selectedStudents.delete(String(trigger.dataset.removeStudent));
            renderSelectedStudents();
            renderSearchResults();
        });

        modalAdd.addEventListener('hidden.bs.modal', resetAddModal);
        renderSelectedStudents();
        renderSearchResults();
    }

    const modalEditEl = document.getElementById('modalEditEnrollment');
    if (modalEditEl) {
        const modalEdit = new bootstrap.Modal(modalEditEl);
        const formEdit = document.getElementById('formEditEnrollment');
        const nameLabel = document.getElementById('edit-hv-name');
        const dateInput = document.getElementById('edit-ngay-tham-gia');
        const statusSelect = document.getElementById('edit-trang-thai');
        const noteTextarea = document.getElementById('edit-ghi-chu');

        document.querySelectorAll('.btn-edit-enroll').forEach((btn) => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                nameLabel.textContent = this.dataset.name;
                dateInput.value = this.dataset.date;
                statusSelect.value = this.dataset.status;
                noteTextarea.value = this.dataset.note;
                formEdit.action = `{{ url('admin/khoa-hoc/' . $khoaHoc->id . '/hoc-vien') }}/${id}`;
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
    .search-state { min-height: 100%; }
    .student-suggestion {
        width: 100%;
        border: 1px solid rgba(25, 135, 84, 0.15);
        border-radius: 1rem;
        background: #fff;
        padding: 1rem;
        text-align: left;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .student-suggestion:hover {
        transform: translateY(-1px);
        border-color: rgba(25, 135, 84, 0.35);
        box-shadow: 0 0.75rem 1.5rem rgba(25, 135, 84, 0.08);
    }
    .student-suggestion.is-selected,
    .student-suggestion:disabled {
        background: #f8f9fa;
        box-shadow: none;
        transform: none;
        cursor: not-allowed;
        opacity: 0.85;
    }
    .selected-student-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #fff;
        border: 1px solid rgba(25, 135, 84, 0.18);
        border-radius: 999px;
        padding: 0.45rem 0.75rem;
        box-shadow: 0 0.35rem 0.8rem rgba(0, 0, 0, 0.05);
    }
    .selected-student-remove {
        border: 0;
        background: transparent;
        color: #dc3545;
        padding: 0;
        line-height: 1;
    }
    .selected-student-remove:hover { color: #bb2d3b; }
</style>
@endsection
