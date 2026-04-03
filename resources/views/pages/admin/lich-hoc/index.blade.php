@extends('layouts.app')

@section('title', 'Quản lý lịch học — ' . $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ma_khoa_hoc }}</a></li>
            <li class="breadcrumb-item active">Lịch học</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check p-0 ms-2">
                <input type="checkbox" id="checkAllGlobal" class="form-check-input ms-0" style="width: 1.2rem; height: 1.2rem; cursor: pointer;" title="Chọn tất cả các buổi học đang chờ">
            </div>
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fas fa-calendar-alt me-2 text-info"></i>
                    Quản lý lịch học — {{ $khoaHoc->ten_khoa_hoc }}
                </h4>
                <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}">{{ $khoaHoc->label_trang_thai_van_hanh }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button id="btnBulkDelete" class="btn btn-danger btn-sm shadow-sm fw-bold d-none" onclick="submitBulkDelete()">
                <i class="fas fa-trash-alt me-1"></i> Xóa <span id="selectedCount">0</span> buổi
            </button>
            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    @include('components.alert')

    <!-- Form Xóa Hàng Loạt -->
    <form id="bulkDeleteForm" action="{{ route('admin.khoa-hoc.lich-hoc.destroy-bulk', $khoaHoc->id) }}" method="POST">
        @csrf @method('DELETE')
    </form>

    <!-- Loop qua từng module -->
    @foreach($khoaHoc->moduleHocs as $index => $module)
        @php
            $prevModule = $index > 0 ? $khoaHoc->moduleHocs[$index - 1] : null;
            $minDate = $prevModule ? $prevModule->ngay_ket_thuc_thuc_te : date('Y-m-d');
            
            // Lấy danh sách giảng viên đã được phân công cho module này
            $assignedTeachers = $module->phanCongGiangViens->map(function($pc) {
                $gv = $pc->giangVien;
                return [
                    'id' => $pc->giang_vien_id,
                    'name' => $gv?->nguoiDung?->ho_ten ?? 'N/A',
                    'pending_leave_count' => $gv?->donXinNghis?->where('trang_thai', 'cho_duyet')->count() ?? 0
                ];
            })->values();
        @endphp
        <div class="vip-card mb-4 border-0 shadow-sm overflow-hidden">
            <div class="vip-card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div style="flex: 1;">
                    <h5 class="small fw-bold text-uppercase mb-1 text-primary">
                        <i class="fas fa-cube me-2"></i> Module {{ $module->thu_tu_module }}: {{ $module->ten_module }}
                    </h5>
                    <div class="d-flex align-items-center gap-3 small">
                        <span>Quy định: <strong>{{ $module->so_buoi }}</strong> | Đã lên: <strong class="{{ $module->lichHocs->count() < $module->so_buoi ? 'text-danger' : 'text-success' }}">{{ $module->lichHocs->count() }}</strong></span>
                        @if($module->so_buoi > 0)
                            <div class="progress" style="width: 80px; height: 5px;">
                                <div class="progress-bar bg-success" style="width: {{ min(100, ($module->lichHocs->count() / $module->so_buoi) * 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Form lưu số buổi (ĐÃ KHÔI PHỤC) -->
                    <div class="d-flex gap-1 align-items-center me-2 border-end pe-3">
                        <form action="{{ route('admin.khoa-hoc.lich-hoc.update-so-buoi', [$khoaHoc->id, $module->id]) }}" method="POST" class="d-flex gap-1 align-items-center">
                            @csrf
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-0 smaller fw-bold text-muted">SỐ BUỔI:</span>
                                <input type="number" name="so_buoi" value="{{ $module->so_buoi }}" class="form-control text-center fw-bold text-primary border-0 bg-light" style="width: 50px;" min="1">
                                <button type="submit" class="btn btn-primary border-0" title="Lưu số buổi">
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Nút Xóa nhanh (ĐÃ KHÔI PHỤC) -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-danger dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-trash-alt me-1"></i> Xóa
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <button type="button" class="dropdown-item text-danger small py-2" onclick="confirmDeleteModule('{{ $module->id }}', '{{ $module->ten_module }}')">
                                    <i class="fas fa-eraser me-2"></i> Xóa tất cả các buổi "Chờ"
                                </button>
                            </li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-sm btn-success fw-bold px-3 btn-auto-schedule" 
                            data-module-id="{{ $module->id }}" 
                            data-module-name="{{ $module->ten_module }}"
                            data-so-buoi="{{ $module->so_buoi }}"
                            data-min-date="{{ $minDate }}"
                            data-teachers="{{ json_encode($assignedTeachers) }}"
                            data-existing-days="{{ json_encode($module->lichHocs->map(fn($l) => $l->ngay_hoc->dayOfWeek === 0 ? 8 : $l->ngay_hoc->dayOfWeek + 1)->unique()->values()) }}">
                        <i class="fas fa-magic me-1"></i> Sinh lịch tự động
                    </button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3 btn-add-single" 
                            data-module-id="{{ $module->id }}" 
                            data-module-name="{{ $module->ten_module }}"
                            data-min-date="{{ $minDate }}"
                            data-teachers="{{ json_encode($assignedTeachers) }}">
                        <i class="fas fa-plus me-1"></i> Buổi lẻ
                    </button>
                </div>
            </div>
            <div class="vip-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light smaller">
                            <tr>
                                <th class="ps-4" width="40"><input type="checkbox" class="form-check-input check-all-module" data-module="{{ $module->id }}"></th>
                                <th width="70">Buổi</th>
                                <th width="140">Thời gian</th>
                                <th width="180">Nội dung & Tài nguyên</th>
                                <th>Địa điểm / Giảng viên</th>
                                <th class="text-center" width="120">Tiến trình</th>
                                <th class="text-center" width="110">Trạng thái</th>
                                <th class="pe-4 text-center" width="80">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($module->lichHocs as $lich)
                                @php
                                    $hasAttendance = $lich->diemDanhs->isNotEmpty();
                                    $lectureCount = $lich->baiGiangs->count();
                                    $resourceCount = $lich->taiNguyen->count();
                                @endphp
                                <tr class="{{ $lich->trang_thai === 'cho' ? '' : 'table-light' }}">
                                    <td class="ps-4">
                                        @if($lich->trang_thai === 'cho')
                                            <input type="checkbox" name="ids[]" value="{{ $lich->id }}" form="bulkDeleteForm" class="form-check-input check-item module-{{ $module->id }}">
                                        @else
                                            <i class="fas fa-lock text-muted smaller" title="Buổi học đã bắt đầu hoặc kết thúc, không thể chọn xóa nhanh"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">#{{ $lich->buoi_so }}</div>
                                        <div class="smaller text-muted">{{ $lich->thu_label }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><i class="far fa-calendar-alt me-1 text-primary"></i>{{ $lich->ngay_hoc->format('d/m/Y') }}</div>
                                        <div class="smaller text-muted mt-1">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }}-{{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}
                                            <span class="ms-1">({{ $lich->buoi_hoc_label }})</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $lectureCount > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-light text-muted border' }} px-2 py-1">
                                                    <i class="fas fa-book-open me-1"></i>{{ $lectureCount }} bài giảng
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $resourceCount > 0 ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-light text-muted border' }} px-2 py-1">
                                                    <i class="fas fa-paperclip me-1"></i>{{ $resourceCount }} tài liệu
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            @if($lich->hinh_thuc === 'online')
                                                <span class="text-info fw-bold"><i class="fas fa-video me-1"></i>Online</span>
                                                @if($lich->link_online)
                                                    <a href="{{ $lich->link_online }}" target="_blank" class="smaller text-decoration-none ms-1"><i class="fas fa-external-link-alt"></i></a>
                                                @endif
                                            @else
                                                <span class="text-success fw-bold"><i class="fas fa-map-marker-alt me-1"></i>{{ $lich->phong_hoc ?: 'Chưa gán phòng' }}</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs bg-light rounded-circle text-center border" style="width: 24px; height: 24px; line-height: 22px;">
                                                <i class="fas fa-user-tie text-muted smaller"></i>
                                            </div>
                                            <span class="text-dark">{{ $lich->giangVien?->nguoiDung?->ho_ten ?? 'Chưa gán giảng viên' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($hasAttendance)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" title="Đã thực hiện điểm danh">
                                                <i class="fas fa-check-circle me-1"></i>Đã điểm danh
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">
                                                <i class="far fa-circle me-1"></i>Chưa điểm danh
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ match($lich->trang_thai){'cho'=>'secondary','dang_hoc'=>'info','hoan_thanh'=>'success','huy'=>'danger',default=>'light'} }} w-100 py-2">
                                            {{ $lich->trang_thai_label }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('admin.khoa-hoc.lich-hoc.edit', [$khoaHoc->id, $lich->id]) }}" class="btn btn-sm btn-outline-primary border-0" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="confirmDeleteSingle('{{ route('admin.khoa-hoc.lich-hoc.destroy', [$khoaHoc->id, $lich->id]) }}')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-4 text-muted italic">Chưa có lịch.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>

<form id="deleteSingleForm" method="POST" style="display: none;">@csrf @method('DELETE')</form>
<form id="deleteModuleForm" method="POST" style="display: none;">@csrf @method('DELETE')</form>

@include('pages.admin.lich-hoc.modals')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalSingle = new bootstrap.Modal(document.getElementById('modalThemBuoi'));
    const modalAuto   = new bootstrap.Modal(document.getElementById('modalSinhTuDong'));
    
    const previewContainer = document.getElementById('auto-preview-container');
    const emptyPreview = document.getElementById('auto-empty-preview');
    const previewBody = document.getElementById('auto-preview-body');
    const btnConfirmAutoSave = document.getElementById('btnConfirmAutoSave');
    const btnPreviewAuto = document.getElementById('btnPreviewAuto');
    const ngayBatDauInput = document.getElementById('auto-start-date');
    const thuLabels = Array.from(document.querySelectorAll('.thu-label-box'));
    const autoPlanningPanel = document.getElementById('auto-planning-panel');
    const autoTeacherInput = document.getElementById('auto-teacher-id');
    const autoPhongHocInput = document.querySelector('#auto-schedule-form input[name="phong_hoc"]');
    const autoHinhThucInput = document.querySelector('#auto-schedule-form select[name="hinh_thuc"]');
    const lockThuSelection = document.getElementById('lockThuSelection');
    const thuContainer = document.getElementById('container-thu-auto');
    const thuInputs = Array.from(document.querySelectorAll('#container-thu-auto input[name="thu_trong_tuan[]"]'));
    const autoEndDateText = document.getElementById('auto-end-date-text');
    const autoExistingDaysNote = document.getElementById('auto-existing-days-note');
    const btnUseExistingDays = document.getElementById('btnUseExistingDays');
    const btnUseDefaultDays = document.getElementById('btnUseDefaultDays');
    const btnClearDays = document.getElementById('btnClearDays');

    const SESSIONS = @json(\App\Support\Scheduling\TeachingPeriodCatalog::sessions());
    const defaultThuSelection = thuInputs
        .filter(input => input.checked)
        .map(input => parseInt(input.value, 10));
    const PATTERN_246 = [2, 4, 6];
    const PATTERN_357 = [3, 5, 7];
    let currentModuleSoBuoi = 0;
    let currentExistingDays = [];
    let currentConflictDays = [];

    btnConfirmAutoSave.disabled = true;

    function populateTeachers(selectId, data) {
        const select = document.getElementById(selectId);
        let html = '<option value="">-- Chọn giảng viên --</option>';
        data.forEach(t => {
            html += `<option value="${t.id}">${t.name} (${t.pending_leave_count} đơn nghỉ)</option>`;
        });
        select.innerHTML = html;
        if (data.length > 0) select.value = data[0].id;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeThuValues(values) {
        return [...new Set((values || [])
            .map(value => parseInt(value, 10))
            .filter(value => Number.isInteger(value) && value >= 2 && value <= 8))]
            .sort((a, b) => a - b);
    }

    function formatDateValue(date) {
        const year = date.getFullYear();
        const month = `${date.getMonth() + 1}`.padStart(2, '0');
        const day = `${date.getDate()}`.padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function formatThuLabel(thu) {
        return thu === 8 ? 'Chu nhat' : `Thu ${thu}`;
    }

    function resolveThuFromDate(dateValue) {
        if (!dateValue) {
            return null;
        }

        const date = new Date(`${dateValue}T00:00:00`);
        if (Number.isNaN(date.getTime())) {
            return null;
        }

        return date.getDay() === 0 ? 8 : date.getDay() + 1;
    }

    function getSessionDefinition(sessionKey) {
        return sessionKey ? (SESSIONS[sessionKey] || null) : null;
    }

    function updatePreviewRowThuLabel(row, thu) {
        const thuLabel = row.querySelector('.preview-thu-label') || row.children[2];
        if (!thuLabel) {
            return;
        }

        thuLabel.textContent = thu !== null ? formatThuLabel(thu) : '--';
    }

    function collectPreviewRows() {
        return Array.from(previewBody.querySelectorAll('tr[data-preview-date]')).map((row, index) => {
            const dateInput = row.querySelector('input[name="preview_dates[]"]');
            const sessionSelect = row.querySelector('select[name="preview_sessions[]"]');
            const date = dateInput?.value || '';
            const session = sessionSelect?.value || '';
            const thu = resolveThuFromDate(date);
            const sessionDefinition = getSessionDefinition(session);

            row.dataset.previewDate = date;
            row.dataset.previewThu = thu ?? '';
            row.dataset.previewSession = session;
            updatePreviewRowThuLabel(row, thu);

            return {
                rowIndex: index,
                row,
                date,
                thu,
                session,
                buoi: index + 1,
                startTime: sessionDefinition?.start_time || '',
                endTime: sessionDefinition?.end_time || '',
                periodStart: sessionDefinition?.start ?? null,
                periodEnd: sessionDefinition?.end ?? null,
            };
        });
    }

    function getSelectedThuValues() {
        return normalizeThuValues(
            thuInputs.filter(input => input.checked).map(input => input.value)
        );
    }

    function renderAutoPlanningPlaceholder(message, tone = 'muted') {
        if (!autoPlanningPanel) {
            return;
        }

        autoPlanningPanel.innerHTML = `<div class="small text-${tone} mb-0">${message}</div>`;
    }

    function invalidateAutoPreview() {
        previewBody.innerHTML = '';
        previewContainer.classList.add('d-none');
        emptyPreview.classList.remove('d-none');
        btnConfirmAutoSave.classList.add('disabled');
        btnConfirmAutoSave.disabled = true;
        currentConflictDays = [];
        syncThuSelectionState();
        renderAutoPlanningPlaceholder('Chua co du lieu kiem tra trung lich.');
    }

    function buildPreviewList() {
        if (!ngayBatDauInput.value || currentModuleSoBuoi <= 0) {
            return [];
        }

        const selectedDays = getSelectedThuValues();
        if (selectedDays.length === 0) {
            return [];
        }

        const list = [];
        const currentDate = new Date(`${ngayBatDauInput.value}T00:00:00`);
        let count = 0;
        let safety = 0;

        while (count < currentModuleSoBuoi && safety < 500) {
            safety++;
            const day = currentDate.getDay();
            const dbDay = day === 0 ? 8 : day + 1;

            if (selectedDays.includes(dbDay)) {
                count++;
                list.push({
                    buoi: count,
                    date: formatDateValue(currentDate),
                    thu: dbDay,
                });
            }

            currentDate.setDate(currentDate.getDate() + 1);
        }

        return list;
    }

    function updateAutoEndDateText() {
        const previewList = buildPreviewList();

        if (previewList.length === 0) {
            autoEndDateText.textContent = '--/--';
            return;
        }

        const [, month, day] = previewList[previewList.length - 1].date.split('-');
        autoEndDateText.textContent = `${day}/${month}`;
    }

    function updateThuSummary() {
        const selectedDays = getSelectedThuValues();
        const conflictDays = normalizeThuValues(currentConflictDays);
        const existingText = currentExistingDays.length > 0
            ? currentExistingDays.map(formatThuLabel).join(', ')
            : 'Chua co lich co dinh';
        const selectedText = selectedDays.length > 0
            ? selectedDays.map(formatThuLabel).join(', ')
            : 'Chua chon thu nao';
        const conflictText = conflictDays.length > 0
            ? ` Thu dang bi trung: ${conflictDays.map(formatThuLabel).join(', ')}.`
            : '';

        autoExistingDaysNote.textContent = `Module dang co: ${existingText}. Ban dang chon sinh lich: ${selectedText}.${conflictText}`;
    }

    function updateThuUI() {
        if (!ngayBatDauInput.value) {
            thuLabels.forEach(label => {
                label.dataset.position = 'none';
            });
            updateAutoEndDateText();
            return;
        }

        const start = new Date(`${ngayBatDauInput.value}T00:00:00`);
        const day = start.getDay();
        const startThu = day === 0 ? 8 : day + 1;
        const endOfWeek = new Date(start);
        endOfWeek.setDate(start.getDate() + (day === 0 ? 0 : 7 - day));

        thuLabels.forEach(label => {
            const thu = parseInt(label.dataset.thu, 10);

            if (thu === startThu) {
                label.dataset.position = 'start';
                return;
            }

            let diff = thu - startThu;
            if (diff < 0) diff += 7;

            const target = new Date(start);
            target.setDate(start.getDate() + diff);
            label.dataset.position = target <= endOfWeek ? 'same-week' : 'next-week';
        });

        updateAutoEndDateText();
    }

    function syncThuSelectionState() {
        const isLocked = lockThuSelection.checked;
        const existingSet = new Set(currentExistingDays);
        const conflictSet = new Set(currentConflictDays);

        thuContainer.classList.toggle('selection-locked', isLocked);

        thuInputs.forEach(input => {
            const thu = parseInt(input.value, 10);
            const label = thuContainer.querySelector(`label[for="${input.id}"]`);
            const stateEl = label?.querySelector('.thu-state');
            const isExisting = existingSet.has(thu);
            const isSelected = input.checked;

            input.disabled = false;

            if (!label || !stateEl) {
                return;
            }

            label.classList.toggle('thu-existing', isExisting);
            label.classList.toggle('thu-selected', isSelected);
            label.classList.toggle('thu-locked', isLocked);
            label.classList.toggle('thu-conflict', conflictSet.has(thu));

            if (conflictSet.has(thu) && isSelected) {
                stateEl.textContent = 'Trung lich';
            } else if (isExisting && isSelected) {
                stateEl.textContent = 'Da co + chon';
            } else if (isExisting) {
                stateEl.textContent = 'Da co lich';
            } else if (isSelected) {
                stateEl.textContent = 'Dang chon';
            } else {
                stateEl.textContent = 'Chua chon';
            }
        });

        btnUseExistingDays.disabled = isLocked || currentExistingDays.length === 0;
        btnUseDefaultDays.disabled = isLocked;
        btnClearDays.disabled = isLocked;

        updateThuSummary();
        updateThuUI();
    }

    function applyThuSelection(values) {
        const normalizedValues = new Set(normalizeThuValues(values));

        thuInputs.forEach(input => {
            input.checked = normalizedValues.has(parseInt(input.value, 10));
        });

        syncThuSelectionState();
        invalidateAutoPreview();
    }

    async function inspectAutoPreviewItem(item) {
        if (!autoPlanningPanel) {
            return { item, hasConflict: false, canSchedule: true, message: 'Khong co panel kiem tra.' };
        }

        if (!item.date || !item.session || !item.startTime || !item.endTime || item.periodStart === null || item.periodEnd === null) {
            return {
                item,
                context: null,
                hasConflict: false,
                canSchedule: false,
                message: 'Buoi preview chua du thong tin ngay hoc hoac ca hoc.',
            };
        }

        const payload = new URLSearchParams({
            module_hoc_id: document.getElementById('auto-module-id').value,
            ngay_hoc: item.date,
            giang_vien_id: autoTeacherInput.value,
            gio_bat_dau: item.startTime,
            gio_ket_thuc: item.endTime,
            tiet_bat_dau: String(item.periodStart),
            tiet_ket_thuc: String(item.periodEnd),
            buoi_hoc: item.session,
        });

        try {
            const response = await fetch(`${autoPlanningPanel.dataset.endpoint}?${payload.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('planning-check-failed');
            }

            const context = await response.json();

            return {
                item,
                context,
                hasConflict: context?.conflicts?.ok === false,
                canSchedule: context?.can_schedule !== false,
                message: context?.conflicts?.message || context?.errors?.gio_bat_dau || 'Khong co xung dot.',
            };
        } catch (error) {
            return {
                item,
                context: null,
                hasConflict: false,
                canSchedule: false,
                message: 'Khong the kiem tra xung dot luc nay.',
                hasError: true,
            };
        }
    }

    function renderAutoPlanningSummary(results) {
        if (!autoPlanningPanel) {
            return;
        }

        const conflictItems = results.filter(result => result.hasConflict);
        const invalidItems = results.filter(result => result.canSchedule === false);
        const errorItems = results.filter(result => result.hasError);

        autoPlanningPanel.innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <div class="fw-bold text-dark">Kiem tra trung lich</div>
                    <div class="small text-muted">Da quet ${results.length} buoi trong lo trinh xem truoc.</div>
                </div>
                <span class="badge rounded-pill bg-${conflictItems.length > 0 ? 'danger' : 'success'}">${conflictItems.length > 0 ? `${conflictItems.length} buoi bi trung` : 'Khong bi trung lich'}</span>
            </div>
            <div class="small text-muted mb-2">${invalidItems.length > 0 ? `Co ${invalidItems.length} buoi can xu ly truoc khi luu.` : 'Tat ca buoi hop le de tiep tuc luu lich.'}</div>
            ${conflictItems.length > 0 ? `
                <div class="d-flex flex-wrap gap-2">
                    ${conflictItems.slice(0, 8).map(result => `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">${result.item.date} - ${escapeHtml(formatThuLabel(result.item.thu))}</span>`).join('')}
                </div>
            ` : ''}
            ${errorItems.length > 0 ? `<div class="small text-warning mt-3">Co mot vai buoi chua kiem tra duoc planning context. Vui long thu lai.</div>` : ''}
        `;
    }

    async function highlightAutoPreviewConflicts(previewList) {
        renderAutoPlanningPlaceholder('<span class="spinner-border spinner-border-sm me-2"></span>Dang kiem tra trung lich...', 'muted');

        const results = await Promise.all(previewList.map(inspectAutoPreviewItem));
        const rowNodes = Array.from(previewBody.querySelectorAll('tr'));

        currentConflictDays = normalizeThuValues(
            results
                .filter(result => result.hasConflict && result.item.thu !== null)
                .map(result => result.item.thu)
        );

        rowNodes.forEach((row, index) => {
            const result = results[index];
            const note = row.querySelector('.preview-conflict-note');

            row.classList.remove('table-danger', 'table-warning');

            if (!result) {
                return;
            }

            if (result.hasConflict) {
                row.classList.add('table-danger');
                if (note) {
                    note.textContent = result.message || 'Buoi nay dang trung lich.';
                    note.classList.remove('d-none', 'text-warning');
                    note.classList.add('text-danger');
                }
                return;
            }

            if (result.canSchedule === false) {
                row.classList.add('table-warning');
                if (note) {
                    note.textContent = result.message || 'Buoi nay can kiem tra them.';
                    note.classList.remove('d-none', 'text-danger');
                    note.classList.add('text-warning');
                }
                return;
            }

            if (note) {
                note.textContent = '';
                note.classList.add('d-none');
                note.classList.remove('text-danger', 'text-warning');
            }
        });

        syncThuSelectionState();
        renderAutoPlanningSummary(results);

        const hasBlockingIssue = results.some(result => result.canSchedule === false);
        btnConfirmAutoSave.disabled = hasBlockingIssue;
        btnConfirmAutoSave.classList.toggle('disabled', hasBlockingIssue);
    }

    async function refreshPreviewChecksFromTable() {
        if (previewContainer.classList.contains('d-none')) {
            return;
        }

        const previewRows = collectPreviewRows();
        if (previewRows.length === 0) {
            invalidateAutoPreview();
            return;
        }

        btnConfirmAutoSave.disabled = true;
        btnConfirmAutoSave.classList.add('disabled');
        await highlightAutoPreviewConflicts(previewRows);
    }

    // Sự kiện mở modal Sinh tự động
    document.querySelectorAll('.btn-auto-schedule').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('auto-module-id').value = this.dataset.moduleId;
            document.getElementById('auto-module-name').textContent = this.dataset.moduleName;
            currentModuleSoBuoi = parseInt(this.dataset.soBuoi, 10);
            document.getElementById('auto-so-buoi-text').textContent = currentModuleSoBuoi;
            
            ngayBatDauInput.value = this.dataset.minDate;
            ngayBatDauInput.min = this.dataset.minDate;
            
            populateTeachers('auto-teacher-id', JSON.parse(this.dataset.teachers));
            
            // Đánh dấu các Thứ đã có lịch học
            currentExistingDays = normalizeThuValues(JSON.parse(this.dataset.existingDays || '[]'));
            thuLabels.forEach(lbl => {
                const v = parseInt(lbl.dataset.thu);
                lbl.classList.toggle('thu-existing', currentExistingDays.includes(v));
                const icon = lbl.querySelector('.existing-icon');
                if (currentExistingDays.includes(v)) {
                    if (!icon) lbl.insertAdjacentHTML('beforeend', '<i class="fas fa-check-double existing-icon"></i>');
                } else {
                    if (icon) icon.remove();
                }
            });

            // Mặc định không khóa khi mới mở
            lockThuSelection.checked = false;
            applyThuSelection(currentExistingDays.length > 0 ? currentExistingDays : defaultThuSelection);
            modalAuto.show();
        });
    });

    // Xử lý Khóa lựa chọn Thứ
    lockThuSelection.addEventListener('change', function() {
        syncThuSelectionState();
        invalidateAutoPreview();
    });
    btnUseExistingDays.addEventListener('click', function() {
        if (currentExistingDays.length === 0) {
            return;
        }

        applyThuSelection(currentExistingDays);
    });

    btnUseDefaultDays.addEventListener('click', function() {
        applyThuSelection(PATTERN_246);
    });

    const btnUse357Days = document.getElementById('btnUse357Days');
    if (btnUse357Days) {
        btnUse357Days.addEventListener('click', function() {
            applyThuSelection(PATTERN_357);
        });
    }

    btnClearDays.addEventListener('click', function() {
        applyThuSelection([]);
    });

    // Sự kiện mở modal Buổi lẻ
    document.querySelectorAll('.btn-add-single').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('single-module-id').value = this.dataset.moduleId;
            document.getElementById('single-module-name').textContent = this.dataset.moduleName;
            document.getElementById('single-date').value = this.dataset.minDate;
            document.getElementById('single-date').min = this.dataset.minDate;
            populateTeachers('single-teacher-id', JSON.parse(this.dataset.teachers));
            modalSingle.show();
        });
    });

    // Xem trước lộ trình
    btnPreviewAuto.addEventListener('click', async function() {
        const startTiet = document.getElementById('auto-tiet-bat-dau').value;
        const previewList = buildPreviewList();
        
        if (!ngayBatDauInput.value || previewList.length === 0 || !startTiet) {
            alert('Vui lòng chọn Ngày bắt đầu, Giảng viên, ít nhất 1 Thứ và Ca học!');
            return;
        }
        previewBody.innerHTML = previewList.map(item => `
            <tr data-preview-date="${item.date}" data-preview-thu="${item.thu}">
                <td class="text-center fw-bold text-muted">${item.buoi}</td>
                <td><input type="date" name="preview_dates[]" value="${item.date}" class="form-control form-control-sm border-0 bg-light"></td>
                <td class="text-center small">${item.thu === 8 ? 'Chủ nhật' : 'Thứ ' + item.thu}</td>
                <td>
                    <select name="preview_sessions[]" class="form-select form-select-sm border-0 bg-light select-preview-session">
                        ${Object.entries(SESSIONS).map(([k, v]) => `<option value="${k}" ${parseInt(startTiet, 10) === v.start ? 'selected' : ''}>${v.label} (T${v.start}-${v.end})</option>`).join('')}
                    </select>
                    <div class="preview-conflict-note small mt-1 d-none"></div>
                </td>
            </tr>`).join('');

        const previewRows = collectPreviewRows();

        previewContainer.classList.remove('d-none');
        emptyPreview.classList.add('d-none');
        btnConfirmAutoSave.classList.remove('disabled');
        btnConfirmAutoSave.disabled = true;
        await highlightAutoPreviewConflicts(previewRows);
    });

    previewBody.addEventListener('change', async function(event) {
        if (!event.target.matches('input[name="preview_dates[]"], select[name="preview_sessions[]"]')) {
            return;
        }

        await refreshPreviewChecksFromTable();
    });

    // Chọn nhanh ca học
    document.querySelectorAll('.schedule-session-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const prefix = this.dataset.prefix;
            const def = SESSIONS[this.dataset.session];
            document.getElementById(`${prefix}-tiet-bat-dau`).value = def.start;
            document.getElementById(`${prefix}-tiet-ket-thuc`).value = def.end;
            document.getElementById(`${prefix}-buoi-hoc`).value = this.dataset.session;
            document.getElementById(`${prefix}-time-preview`).value = `${def.label} | Tiết ${def.start}-${def.end}`;
            if (prefix === 'auto') {
                document.getElementById('auto-start-time').value = def.start_time || '';
                document.getElementById('auto-end-time').value = def.end_time || '';
                invalidateAutoPreview();
            }
        });
    });

    thuInputs.forEach(input => {
        input.addEventListener('change', function() {
            syncThuSelectionState();
            invalidateAutoPreview();
        });
    });

    [ngayBatDauInput, autoTeacherInput, autoPhongHocInput, autoHinhThucInput].forEach(element => {
        element?.addEventListener('change', function() {
            updateThuUI();
            invalidateAutoPreview();
        });
    });

    // Duy trì vị trí cuộn
    const pos = localStorage.getItem('lichHocScrollPos');
    if (pos) { window.scrollTo(0, parseInt(pos)); localStorage.removeItem('lichHocScrollPos'); }
    document.querySelectorAll('form').forEach(f => f.addEventListener('submit', () => localStorage.setItem('lichHocScrollPos', window.scrollY)));
    
    // Checkbox tất cả
    const checkAllGlobal = document.getElementById('checkAllGlobal');
    if (checkAllGlobal) {
        checkAllGlobal.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.check-item').forEach(item => item.checked = isChecked);
            document.querySelectorAll('.check-all-module').forEach(item => item.checked = isChecked);
            updateBulkBtn();
        });
    }

    document.querySelectorAll('.check-all-module').forEach(cb => {
        cb.addEventListener('change', function() {
            document.querySelectorAll(`.module-${this.dataset.module}`).forEach(item => item.checked = this.checked);
            updateBulkBtn();
            updateGlobalCheckState();
        });
    });

    function updateGlobalCheckState() {
        if (!checkAllGlobal) return;
        const totalItems = document.querySelectorAll('.check-item').length;
        const checkedItems = document.querySelectorAll('.check-item:checked').length;
        checkAllGlobal.checked = totalItems > 0 && totalItems === checkedItems;
        checkAllGlobal.indeterminate = checkedItems > 0 && checkedItems < totalItems;
    }

    function updateBulkBtn() {
        const count = document.querySelectorAll('.check-item:checked').length;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('btnBulkDelete').classList.toggle('d-none', count === 0);
        updateGlobalCheckState();
    }
    document.querySelectorAll('.check-item').forEach(cb => cb.addEventListener('change', updateBulkBtn));
});

function confirmDeleteSingle(url) { if (confirm('Xóa buổi học này?')) { const f = document.getElementById('deleteSingleForm'); f.action = url; f.submit(); } }
function confirmDeleteModule(moduleId, moduleName) {
    if (confirm('Bạn có chắc chắn muốn xóa TẤT CẢ các buổi học có trạng thái "Chờ" của module "' + moduleName + '" không?')) {
        const form = document.getElementById('deleteModuleForm');
        // Tạo URL dựa trên pattern route Laravel
        let url = '{{ route("admin.khoa-hoc.lich-hoc.destroy-module", [$khoaHoc->id, ":moduleId"]) }}';
        url = url.replace(':moduleId', moduleId);
        form.action = url;
        form.submit();
    }
}
function submitBulkDelete() {
    const checkedItems = document.querySelectorAll('.check-item:checked');
    if (checkedItems.length === 0) {
        alert('Vui lòng chọn ít nhất một buổi học để xóa.');
        return;
    }
    if (confirm('Xóa ' + checkedItems.length + ' buổi đã chọn?')) {
        const form = document.getElementById('bulkDeleteForm');
        // Xóa các input ids[] cũ nếu có
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        // Thêm các ID đã chọn vào form
        checkedItems.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        form.submit();
    }
}
</script>
@endpush

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .vip-card { border-radius: 12px; margin-bottom: 1.5rem; transition: transform 0.2s; }
    .vip-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important; }
    .planning-panel { min-height: 80px; }
    .thu-label-box {
        position: relative;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 78px;
        min-height: 58px;
        padding: 10px 8px 8px;
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        font-weight: 700;
        background: #fff;
        transition: all 0.2s ease;
        gap: 3px;
        overflow: hidden;
    }
    .thu-label-box::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: transparent;
    }
    .thu-title {
        font-size: 0.95rem;
        line-height: 1;
    }
    .thu-state {
        font-size: 0.56rem;
        line-height: 1.15;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        color: #64748b;
        text-align: center;
    }
    input:checked + .thu-label-box,
    .thu-label-box.thu-selected {
        border-color: #0d6efd;
        background: #eff6ff;
        color: #0d6efd;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(13, 110, 253, 0.12);
    }
    .thu-label-box.thu-existing {
        border-color: #198754;
        background: #f0fff4;
        box-shadow: inset 0 0 0 1px rgba(25, 135, 84, 0.18);
    }
    .thu-label-box.thu-existing .thu-state {
        color: #198754;
    }
    .thu-label-box.thu-selected.thu-existing {
        border-color: #157347;
        background: linear-gradient(180deg, #f0fff4 0%, #e6ffed 100%);
        color: #157347;
    }
    .thu-label-box.thu-conflict {
        border-color: #dc3545 !important;
        background: linear-gradient(180deg, #fff5f5 0%, #ffe3e3 100%) !important;
        color: #b42318 !important;
        box-shadow: 0 6px 14px rgba(220, 53, 69, 0.12);
    }
    .thu-label-box.thu-conflict .thu-state {
        color: #b42318 !important;
    }
    .thu-label-box[data-position="start"]::before { background: #dc3545; }
    .thu-label-box[data-position="same-week"]::before { background: #198754; }
    .thu-label-box[data-position="next-week"]::before { background: #0d6efd; }
    .selection-locked .thu-label-box,
    .thu-label-box.thu-locked,
    .thu-check-item input:disabled + .thu-label-box {
        cursor: not-allowed;
        opacity: 0.68;
        transform: none;
        box-shadow: none;
    }
    .selection-locked .thu-label-box,
    .thu-label-box.thu-locked {
        pointer-events: none;
    }
    .existing-icon { display: none !important; }
    .legend-box { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 4px; vertical-align: middle; }
    .legend-existing { background-color: #d1fae5; border: 1px solid #198754; }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    #auto-existing-days-note { line-height: 1.5; }
    .preview-conflict-note { line-height: 1.35; }
</style>
@endsection
