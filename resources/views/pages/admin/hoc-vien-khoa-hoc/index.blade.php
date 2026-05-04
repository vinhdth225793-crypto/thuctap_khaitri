@extends('layouts.app')

@section('title', 'Quản lý học viên — ' . $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid admin-page-x hvkh-page">
    {{-- Welcome banner --}}
    <div class="apx-welcome hvkh-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-users"></i></div>
        <div class="apx-welcome-text">
            <div class="hvkh-tag-row">
                <span class="hvkh-loai-badge"><i class="fas fa-graduation-cap"></i> HỌC VIÊN KHÓA HỌC</span>
                <span class="hvkh-status-badge"><i class="fas fa-fingerprint"></i> {{ $khoaHoc->ma_khoa_hoc }}</span>
                @if($khoaHoc->nhomNganh)
                    <span class="hvkh-status-badge"><i class="fas fa-tag"></i> {{ $khoaHoc->nhomNganh->ten_nhom_nganh }}</span>
                @endif
                <span class="hvkh-status-badge"><i class="fas fa-users"></i> {{ $stats['tong'] }} học viên</span>
            </div>
            <h4>{{ $khoaHoc->ten_khoa_hoc }}</h4>
            <p>
                <span><i class="fas fa-circle-play"></i> {{ $stats['dang_hoc'] }} đang học</span>
                <span class="hvkh-sep">·</span>
                <span><i class="fas fa-circle-check"></i> {{ $stats['hoan_thanh'] }} hoàn thành</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Chi tiết khóa</span>
            </a>
            <button type="button" class="btn btn-light text-primary fw-bold shadow-sm hvkh-add-btn" data-bs-toggle="modal" data-bs-target="#modalAddHocVien">
                <i class="fas fa-user-plus me-1"></i> Thêm học viên
            </button>
        </div>
    </div>

    @include('components.alert')

    {{-- ① Tổng quan --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan học viên</h2>
                    <p>Ba chỉ số nhanh theo trạng thái học tập trong khóa.</p>
                </div>
            </div>
        </header>
        <div class="row g-3">
            <div class="col-md-4 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-users"></i></div>
                    <div class="aps-text"><strong>{{ $stats['tong'] }}</strong><small>Tổng học viên</small></div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-play"></i></div>
                    <div class="aps-text"><strong>{{ $stats['dang_hoc'] }}</strong><small>Đang học</small></div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text"><strong>{{ $stats['hoan_thanh'] }}</strong><small>Hoàn thành</small></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ② Danh sách HV compact --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách học viên trong lớp</h2>
                    <p>Bấm vào dòng để xem thông tin liên hệ chi tiết. Sử dụng nút "Thêm học viên" để ghi danh thêm.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill">
                    <strong>{{ $hocViens->firstItem() ?? 0 }}–{{ $hocViens->lastItem() ?? 0 }}</strong> / {{ $hocViens->total() }}
                </span>
            </div>
        </header>

        <div class="hvkh-list">
            @if($hocViens->count() === 0)
                <div class="hvkh-empty">
                    <div class="hvkh-empty-icon"><i class="fas fa-user-slash"></i></div>
                    <h5>Khóa học chưa có học viên</h5>
                    <p>Bấm "Thêm học viên" để ghi danh học viên mới vào lớp.</p>
                    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddHocVien">
                        <i class="fas fa-user-plus me-1"></i> Thêm học viên đầu tiên
                    </button>
                </div>
            @else
                <div class="hvkh-row hvkh-row-header">
                    <div class="hvkh-col-stt">#</div>
                    <div class="hvkh-col-name">Học viên</div>
                    <div class="hvkh-col-contact">Liên hệ</div>
                    <div class="hvkh-col-date">Ngày ghi danh</div>
                    <div class="hvkh-col-status">Trạng thái</div>
                    <div class="hvkh-col-action">Thao tác</div>
                </div>

                @foreach($hocViens as $index => $bghv)
                    @php
                        $hv = $bghv->hocVien;
                        $nd = $hv?->nguoiDung;
                        $tenHV = $nd?->ho_ten ?? 'N/A';
                        $idHV  = $hv?->ma_hoc_vien ?? $hv?->id ?? '?';
                        $idColor = ($hv?->id ?? $loop->index) % 6;
                        $gradients = [
                            'linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)',
                            'linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)',
                            'linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)',
                        ];
                        $initial = mb_strtoupper(mb_substr(trim($tenHV), 0, 1, 'UTF-8'), 'UTF-8');

                        $statusClass = match($bghv->trang_thai) {
                            'dang_hoc'   => 'is-success',
                            'hoan_thanh' => 'is-info',
                            'ngung_hoc'  => 'is-warning',
                            default      => 'is-secondary',
                        };
                        $statusIcon = match($bghv->trang_thai) {
                            'dang_hoc'   => 'fa-circle-play',
                            'hoan_thanh' => 'fa-circle-check',
                            'ngung_hoc'  => 'fa-pause-circle',
                            default      => 'fa-circle',
                        };
                    @endphp
                    <div class="hvkh-row">
                        <div class="hvkh-col-stt">{{ $hocViens->firstItem() + $index }}</div>
                        <div class="hvkh-col-name">
                            <div class="hvkh-avatar" style="background: {{ $gradients[$idColor] }};">
                                {{ $initial ?: '?' }}
                            </div>
                            <div class="hvkh-name-block">
                                <div class="hvkh-name">{{ $tenHV }}</div>
                                <div class="hvkh-id"><i class="fas fa-hashtag"></i> {{ $idHV }}</div>
                            </div>
                        </div>
                        <div class="hvkh-col-contact">
                            <div class="hvkh-contact-line">
                                <i class="fas fa-envelope"></i>
                                <span>{{ $nd?->email ?? '—' }}</span>
                            </div>
                            <div class="hvkh-contact-line">
                                <i class="fas fa-phone"></i>
                                <span>{{ $nd?->so_dien_thoai ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="hvkh-col-date">
                            @if($bghv->ngay_tham_gia)
                                <div class="hvkh-date">{{ $bghv->ngay_tham_gia->format('d/m/Y') }}</div>
                                <div class="hvkh-date-rel">{{ $bghv->ngay_tham_gia->diffForHumans() }}</div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </div>
                        <div class="hvkh-col-status">
                            <span class="hvkh-status-pill {{ $statusClass }}">
                                <i class="fas {{ $statusIcon }}"></i> {{ $bghv->trang_thai_label }}
                            </span>
                        </div>
                        <div class="hvkh-col-action">
                            <button type="button" class="hvkh-action-btn btn-edit-enroll"
                                    data-id="{{ $bghv->id }}"
                                    data-name="{{ $tenHV }}"
                                    data-date="{{ $bghv->ngay_tham_gia ? $bghv->ngay_tham_gia->format('Y-m-d') : '' }}"
                                    data-status="{{ $bghv->trang_thai }}"
                                    data-note="{{ $bghv->ghi_chu }}"
                                    title="Sửa ghi danh">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.khoa-hoc.hoc-vien.destroy', [$khoaHoc->id, $bghv->id]) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Xóa học viên này khỏi khóa học?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="hvkh-action-btn danger" title="Xóa khỏi lớp">
                                    <i class="fas fa-user-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif

            @if($hocViens->hasPages())
                <div class="hvkh-pagination">
                    {{ $hocViens->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>
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

    /* ===== Welcome banner xanh dương + đề mục đỏ ===== */
    .hvkh-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .hvkh-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .hvkh-page .apx-section-title h2 i { color: #dc2626; }
    .hvkh-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .hvkh-page .apx-meta-pill strong { color: #b91c1c; }

    .hvkh-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .hvkh-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .hvkh-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .hvkh-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .apx-welcome.hvkh-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.hvkh-welcome p i { color: #fef3c7; margin-right: 4px; }
    .hvkh-sep { opacity: 0.5; }
    .hvkh-add-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    /* Compact list */
    .hvkh-list {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .hvkh-row {
        display: grid;
        grid-template-columns: 50px minmax(220px, 2fr) minmax(220px, 2fr) 130px 140px 120px;
        gap: 10px;
        align-items: center;
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease;
    }
    .hvkh-row:hover:not(.hvkh-row-header) { background: #fafafa; }
    .hvkh-row-header {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem; font-weight: 800;
        color: #7f1d1d; text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 1px solid #fecaca;
        padding: 12px 14px;
    }

    .hvkh-col-stt { font-size: 0.78rem; color: #94a3b8; font-weight: 800; text-align: center; }
    .hvkh-col-name { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .hvkh-avatar {
        flex-shrink: 0;
        width: 38px; height: 38px;
        border-radius: 50%;
        color: #fff; font-weight: 800; font-size: 0.95rem;
        display: grid; place-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .hvkh-name-block { flex: 1; min-width: 0; }
    .hvkh-name {
        font-size: 0.9rem; font-weight: 800; color: #0f172a;
        line-height: 1.3;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .hvkh-id {
        font-size: 0.72rem; color: #94a3b8;
        font-weight: 700; font-family: monospace;
    }
    .hvkh-id i { color: #1d4ed8; margin-right: 3px; font-size: 0.62rem; }

    .hvkh-col-contact { font-size: 0.78rem; min-width: 0; }
    .hvkh-contact-line {
        display: flex; align-items: center; gap: 6px;
        color: #475569; font-weight: 600;
        line-height: 1.4;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .hvkh-contact-line i {
        color: #1d4ed8; font-size: 0.7rem;
        flex-shrink: 0;
    }
    .hvkh-contact-line span {
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    .hvkh-col-date { font-size: 0.84rem; }
    .hvkh-date { font-weight: 800; color: #0f172a; }
    .hvkh-date-rel { font-size: 0.7rem; color: #94a3b8; font-weight: 600; margin-top: 2px; }

    .hvkh-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.7rem; font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .hvkh-status-pill i { font-size: 0.6rem; }
    .hvkh-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .hvkh-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .hvkh-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .hvkh-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .hvkh-col-action { display: flex; gap: 4px; justify-content: flex-end; }
    .hvkh-action-btn {
        display: inline-grid; place-items: center;
        width: 32px; height: 32px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.78rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .hvkh-action-btn:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .hvkh-action-btn.danger { color: #dc2626; }
    .hvkh-action-btn.danger:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

    .hvkh-pagination {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex; justify-content: center;
    }
    .hvkh-pagination nav { margin: 0; }

    .hvkh-empty { padding: 60px 30px; text-align: center; }
    .hvkh-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626;
        font-size: 2rem;
    }
    .hvkh-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .hvkh-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto 16px; line-height: 1.55; }

    /* Responsive */
    @media (max-width: 991.98px) {
        .hvkh-row {
            grid-template-columns: 40px 1fr 100px;
            grid-template-areas:
                "stt name action"
                "stt contact contact"
                "stt date status";
            gap: 6px 10px;
        }
        .hvkh-row-header { display: none; }
        .hvkh-col-stt { grid-area: stt; align-self: start; padding-top: 4px; }
        .hvkh-col-name { grid-area: name; }
        .hvkh-col-contact { grid-area: contact; }
        .hvkh-col-date { grid-area: date; }
        .hvkh-col-status { grid-area: status; }
        .hvkh-col-action { grid-area: action; justify-content: flex-end; }
    }
</style>

@include('pages.admin.partials._admin-page-styles')
@endsection
