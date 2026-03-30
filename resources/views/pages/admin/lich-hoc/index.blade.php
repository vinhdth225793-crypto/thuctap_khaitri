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
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-calendar-alt me-2 text-info"></i>
                Quản lý lịch học — {{ $khoaHoc->ten_khoa_hoc }}
            </h4>
            <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}">{{ $khoaHoc->label_trang_thai_van_hanh }}</span>
        </div>
        <div class="d-flex gap-2">
            <button id="btnBulkDelete" class="btn btn-danger btn-sm shadow-sm fw-bold d-none" onclick="submitBulkDelete()">
                <i class="fas fa-trash-alt me-1"></i> Xóa <span id="selectedCount">0</span> buổi đã chọn
            </button>
            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại chi tiết
            </a>
        </div>
    </div>

    @include('components.alert')

    <!-- Thống kê tổng quát -->
    <div class="row g-3 mb-4">
        @php
            $tongBuoiQuyDinh = $khoaHoc->moduleHocs->sum('so_buoi');
            $tongBuoiDaLen = $khoaHoc->lichHocs->count();
            $conThieu = max(0, $tongBuoiQuyDinh - $tongBuoiDaLen);
            $moduleDuLich = $khoaHoc->moduleHocs->filter(fn($m) => $m->lichHocs->count() >= $m->so_buoi)->count();
            $moduleThieuLich = $khoaHoc->moduleHocs->count() - $moduleDuLich;
        @endphp
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-info text-white">
                <div class="smaller text-uppercase fw-bold opacity-75">Đã lên lịch</div>
                <div class="fs-2 fw-bold">{{ $tongBuoiDaLen }}</div>
                <div class="small">buổi học thực tế</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm {{ $conThieu > 0 ? 'bg-warning' : 'bg-success text-white' }}">
                <div class="smaller text-uppercase fw-bold opacity-75">Cần bổ sung</div>
                <div class="fs-2 fw-bold">{{ $conThieu }}</div>
                <div class="small">buổi học còn thiếu</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-white border-start border-4 border-success">
                <div class="smaller text-muted text-uppercase fw-bold">Module đủ lịch</div>
                <div class="fs-2 fw-bold text-success">{{ $moduleDuLich }}</div>
                <div class="small text-muted">/ {{ $khoaHoc->moduleHocs->count() }} module</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-white border-start border-4 border-danger">
                <div class="smaller text-muted text-uppercase fw-bold">Module thiếu lịch</div>
                <div class="fs-2 fw-bold text-danger">{{ $moduleThieuLich }}</div>
                <div class="small text-muted">chưa hoàn thiện lịch</div>
            </div>
        </div>
    </div>

    <!-- Form Xóa Hàng Loạt (Bao phủ bảng nhưng không lồng vào các form khác) -->
    <form id="bulkDeleteForm" action="{{ route('admin.khoa-hoc.lich-hoc.destroy-bulk', $khoaHoc->id) }}" method="POST">
        @csrf @method('DELETE')
    </form>

    <!-- Loop qua từng module -->
    @foreach($khoaHoc->moduleHocs as $index => $module)
        @php
            // Lấy ngày kết thúc của module đứng trước đó (nếu có)
            $prevModule = $index > 0 ? $khoaHoc->moduleHocs[$index - 1] : null;
            $minDate = $prevModule ? $prevModule->ngay_ket_thuc_thuc_te : date('Y-m-d');
        @endphp
        <div class="vip-card mb-4 border-0 shadow-sm">
            <div class="vip-card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div style="flex: 1; min-width: 250px;">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-1 text-primary">
                        <i class="fas fa-cube me-2"></i> Module {{ $module->thu_tu_module }}: {{ $module->ten_module }}
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="small text-muted">
                            Quy định: <strong class="text-dark">{{ $module->so_buoi }} buổi</strong> | 
                            Đã lên: <strong class="{{ $module->lichHocs->count() < $module->so_buoi ? 'text-danger' : 'text-success' }}">{{ $module->lichHocs->count() }} buổi</strong>
                        </div>
                        @if($module->so_buoi > 0)
                            <div class="progress" style="width: 100px; height: 6px;">
                                @php $percent = min(100, ($module->lichHocs->count() / $module->so_buoi) * 100); @endphp
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                            </div>
                        @endif
                    </div>
                    @php
                        $assignedTeacherPayload = $module->assignedTeachers
                            ->unique('giang_vien_id')
                            ->map(function ($assignment) {
                                $teacher = $assignment->giangVien;

                                return [
                                    'id' => $assignment->giang_vien_id,
                                    'name' => $teacher?->nguoiDung?->ho_ten ?? 'N/A',
                                    'specialty' => $teacher?->chuyen_nganh,
                                    'availability_count' => $teacher?->donXinNghis?->where('trang_thai', 'cho_duyet')->count() ?? 0,
                                ];
                            })
                            ->values();
                    @endphp
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @forelse($assignedTeacherPayload as $teacherInfo)
                            <span class="badge rounded-pill bg-light text-dark border">
                                <i class="fas fa-user-check text-success me-1"></i>
                                {{ $teacherInfo['name'] }}
                                <span class="text-muted ms-1">({{ $teacherInfo['availability_count'] }} don cho duyet)</span>
                            </span>
                        @empty
                            <span class="badge rounded-pill bg-warning text-dark">
                                <i class="fas fa-exclamation-triangle me-1"></i>Chua co giang vien da nhan module nay
                            </span>
                        @endforelse
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Form lưu số buổi -->
                    <div class="d-flex gap-1 align-items-center me-2 border-end pe-3">
                        <form action="{{ route('admin.khoa-hoc.lich-hoc.update-so-buoi', [$khoaHoc->id, $module->id]) }}" method="POST" class="d-flex gap-1 align-items-center">
                            @csrf
                            <input type="number" name="so_buoi" value="{{ $module->so_buoi }}" class="form-control form-control-sm text-center" style="width: 55px;" min="1">
                            <button type="submit" class="btn btn-sm btn-light border" title="Lưu số buổi">
                                <i class="fas fa-save text-primary"></i>
                            </button>
                        </form>
                    </div>
                    
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
                            data-khoa-hoc-end="{{ optional($khoaHoc->ngay_ket_thuc)->format('Y-m-d') ?: now()->addMonths(6)->format('Y-m-d') }}"
                            data-min-date="{{ $minDate }}"
                            data-assigned-teachers="{{ e(json_encode($assignedTeacherPayload)) }}">
                        <i class="fas fa-magic me-1"></i> Sinh lịch tự động
                    </button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3 btn-add-single" 
                            data-module-id="{{ $module->id }}" 
                            data-module-name="{{ $module->ten_module }}"
                            data-min-date="{{ $minDate }}"
                            data-assigned-teachers="{{ e(json_encode($assignedTeacherPayload)) }}">
                        <i class="fas fa-plus me-1"></i> Thêm buổi lẻ
                    </button>
                </div>
            </div>
            <div class="vip-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light smaller">
                            <tr>
                                <th class="ps-4" width="40">
                                    <input type="checkbox" class="form-check-input check-all-module" data-module="{{ $module->id }}">
                                </th>
                                <th width="80">Thứ tự</th>
                                <th>Ngày học</th>
                                <th>Thứ</th>
                                <th class="text-center">Thời gian</th>
                                <th>Phòng / Link</th>
                                <th>Giảng viên</th>
                                <th class="text-center">Báo cáo</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-center" width="100">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($module->lichHocs as $index => $lich)
                                <tr class="{{ $lich->trang_thai === 'cho' ? 'row-selectable' : 'table-light opacity-75' }}">
                                    <td class="ps-4">
                                        @if($lich->trang_thai === 'cho')
                                            <!-- Chú ý: input checkbox này có thuộc tính form="bulkDeleteForm" để nó thuộc về form xóa hàng loạt nằm bên ngoài -->
                                            <input type="checkbox" name="ids[]" value="{{ $lich->id }}" form="bulkDeleteForm" class="form-check-input check-item module-{{ $module->id }}">
                                        @else
                                            <i class="fas fa-lock text-muted small" title="Không thể xóa buổi đã học/đang học"></i>
                                        @endif
                                    </td>
                                    <td class="text-muted">Buổi {{ $lich->buoi_so }}</td>
                                    <td class="fw-bold">{{ $lich->ngay_hoc->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $lich->thu_label }}</span></td>
                                    <td class="text-center">
                                        <div class="fw-bold text-dark">{{ $lich->schedule_range_label }}</div>
                                        <code class="text-muted">{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</code>
                                    </td>
                                    <td>
                                        @if($lich->hinh_thuc === 'online')
                                            <span class="text-info"><i class="fas fa-globe me-1"></i> Online</span>
                                        @else
                                            <span class="text-dark"><i class="fas fa-door-open me-1"></i> {{ $lich->phong_hoc ?: 'Chưa gán' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lich->giangVien)
                                            <div class="small fw-bold text-truncate" style="max-width: 120px;">{{ $lich->giangVien->nguoiDung->ho_ten }}</div>
                                        @else
                                            <span class="text-muted italic smaller">Chưa gán</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($lich->trang_thai_bao_cao === 'da_bao_cao')
                                            <button type="button" class="btn btn-xs btn-success fw-bold px-2 btn-view-report" 
                                                    data-content="{{ $lich->bao_cao_giang_vien }}"
                                                    data-time="{{ $lich->thoi_gian_bao_cao?->format('d/m/Y H:i') }}"
                                                    data-gv="{{ $lich->giangVien->nguoiDung->ho_ten ?? 'N/A' }}"
                                                    data-buoi="{{ $lich->buoi_so }}">
                                                <i class="fas fa-file-alt me-1"></i> Xem
                                            </button>
                                        @else
                                            <span class="text-muted smaller">Chưa có</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $color = match($lich->trang_thai) {
                                                'cho' => 'secondary',
                                                'dang_hoc' => 'info',
                                                'hoan_thanh' => 'success',
                                                'huy' => 'danger',
                                                default => 'light'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $lich->trang_thai_label }}</span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.khoa-hoc.lich-hoc.edit', [$khoaHoc->id, $lich->id]) }}" class="btn btn-sm btn-outline-warning border-0"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="confirmDeleteSingle('{{ route('admin.khoa-hoc.lich-hoc.destroy', [$khoaHoc->id, $lich->id]) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted italic">Module này chưa có lịch học nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Hidden Form for Module Delete --}}
<form id="deleteModuleForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

{{-- Hidden Form for Single Delete --}}
<form id="deleteSingleForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

{{-- MODAL XEM BÁO CÁO GIẢNG VIÊN (PHASE 7 ADD-ON) --}}
<div class="modal fade shadow" id="modalViewReport" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i> Báo cáo giảng dạy buổi <span id="view-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 d-flex justify-content-between align-items-start border-bottom pb-3">
                    <div>
                        <label class="smaller text-muted d-block fw-bold">Giảng viên báo cáo</label>
                        <span id="view-gv-name" class="fw-bold text-dark fs-6"></span>
                    </div>
                    <div class="text-end">
                        <label class="smaller text-muted d-block fw-bold">Thời gian gửi</label>
                        <span id="view-report-time" class="small text-muted"></span>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="smaller text-muted d-block fw-bold mb-2">Nội dung báo cáo</label>
                    <div id="view-report-content" class="p-3 bg-light rounded border small text-dark lh-base" style="min-height: 150px; white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 justify-content-center bg-light">
                <button type="button" class="btn btn-secondary px-5 fw-bold shadow-xs" data-bs-dismiss="modal">ĐÓNG</button>
            </div>
        </div>
    </div>
</div>

{{-- Các Modal (Sinh lịch, Thêm buổi lẻ) --}}
@include('pages.admin.lich-hoc.modals')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý Modal Xem báo cáo
    const modalViewReport = new bootstrap.Modal(document.getElementById('modalViewReport'));
    document.querySelectorAll('.btn-view-report').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('view-buoi-label').textContent = d.buoi;
            document.getElementById('view-gv-name').textContent = d.gv;
            document.getElementById('view-report-time').textContent = d.time;
            document.getElementById('view-report-content').textContent = d.content;
            modalViewReport.show();
        });
    });

    // Khởi tạo Bootstrap Modals
    const modalSingle = new bootstrap.Modal(document.getElementById('modalThemBuoi'));
    const modalAuto   = new bootstrap.Modal(document.getElementById('modalSinhTuDong'));
    const PERIODS = @json(\App\Support\Scheduling\TeachingPeriodCatalog::periods());
    const SESSIONS = @json(\App\Support\Scheduling\TeachingPeriodCatalog::sessions());

    // Hàm hỗ trợ cộng 1 ngày
    function getNextDay(dateString) {
        if (!dateString) return "{{ date('Y-m-d') }}";
        const date = new Date(dateString);
        date.setDate(date.getDate() + 1);
        return date.toISOString().split('T')[0];
    }

    function dbDayFromDate(dateString) {
        if (!dateString) return null;
        const date = new Date(dateString);
        const day = date.getDay();
        return day === 0 ? 8 : day + 1;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeTime(value) {
        return String(value || '').slice(0, 5);
    }

    function getPickerCheckboxes(prefix) {
        return Array.from(document.querySelectorAll(`#${prefix}-period-grid input[type="checkbox"]`));
    }

    function getSelectedPeriods(prefix) {
        return getPickerCheckboxes(prefix)
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => Number(checkbox.value))
            .sort((left, right) => left - right);
    }

    function buildPeriodLabel(startPeriod, endPeriod) {
        if (!startPeriod || !endPeriod) {
            return '';
        }

        return startPeriod === endPeriod
            ? `Tiet ${startPeriod}`
            : `Tiet ${startPeriod} - ${endPeriod}`;
    }

    function resolveSessionFromRange(startPeriod, endPeriod) {
        const match = Object.entries(SESSIONS).find(([, session]) => Number(session.start) <= startPeriod && Number(session.end) >= endPeriod);
        return match ? match[0] : '';
    }

    function buildTimeRange(startPeriod, endPeriod) {
        if (!startPeriod || !endPeriod || !PERIODS[startPeriod] || !PERIODS[endPeriod]) {
            return '';
        }

        return `${PERIODS[startPeriod].start} - ${PERIODS[endPeriod].end}`;
    }

    function buildPreviewLabel(startPeriod, endPeriod) {
        if (!startPeriod || !endPeriod) {
            return '';
        }

        const sessionKey = resolveSessionFromRange(startPeriod, endPeriod);
        const sessionLabel = sessionKey ? SESSIONS[sessionKey]?.label || '' : '';
        const periodLabel = buildPeriodLabel(startPeriod, endPeriod);
        const timeLabel = buildTimeRange(startPeriod, endPeriod);

        return [sessionLabel, periodLabel, timeLabel].filter(Boolean).join(' | ');
    }

    function findPeriodRangeByTime(startTime, endTime) {
        const normalizedStart = normalizeTime(startTime);
        const normalizedEnd = normalizeTime(endTime);
        const matches = Object.entries(PERIODS)
            .filter(([, period]) => period.start < normalizedEnd && period.end > normalizedStart)
            .map(([period]) => Number(period))
            .sort((left, right) => left - right);

        if (matches.length === 0) {
            return null;
        }

        return {
            start: matches[0],
            end: matches[matches.length - 1],
        };
    }

    function updateSessionButtons(prefix, sessionKey) {
        document.querySelectorAll(`.schedule-session-btn[data-prefix="${prefix}"]`).forEach((button) => {
            const isActive = button.dataset.session === sessionKey && sessionKey !== '';
            button.classList.toggle('active', isActive);
            button.classList.toggle('shadow-sm', isActive);
        });
    }

    function setHiddenScheduleFields(prefix, startPeriod, endPeriod, sessionKey, startTime, endTime, preview) {
        const startPeriodInput = document.getElementById(`${prefix}-tiet-bat-dau`);
        const endPeriodInput = document.getElementById(`${prefix}-tiet-ket-thuc`);
        const sessionInput = document.getElementById(`${prefix}-buoi-hoc`);
        const startTimeInput = document.getElementById(`${prefix}-start-time`);
        const endTimeInput = document.getElementById(`${prefix}-end-time`);
        const previewInput = document.getElementById(`${prefix}-time-preview`);

        if (startPeriodInput) startPeriodInput.value = startPeriod || '';
        if (endPeriodInput) endPeriodInput.value = endPeriod || '';
        if (sessionInput) sessionInput.value = sessionKey || '';
        if (startTimeInput) startTimeInput.value = startTime || '';
        if (endTimeInput) endTimeInput.value = endTime || '';
        if (previewInput) previewInput.value = preview || '';
    }

    function parseAssignedTeachers(raw) {
        try {
            return JSON.parse(raw || '[]');
        } catch (error) {
            return [];
        }
    }

    function syncSchedulePicker(prefix, options = {}) {
        const { triggerPlanning = true } = options;
        const checkboxes = getPickerCheckboxes(prefix);
        const selected = getSelectedPeriods(prefix);

        if (selected.length > 1) {
            const rangeStart = selected[0];
            const rangeEnd = selected[selected.length - 1];
            checkboxes.forEach((checkbox) => {
                const value = Number(checkbox.value);
                checkbox.checked = value >= rangeStart && value <= rangeEnd;
            });
        }

        const normalized = getSelectedPeriods(prefix);
        if (normalized.length === 0) {
            setHiddenScheduleFields(prefix, '', '', '', '', '', '');
            updateSessionButtons(prefix, '');
        } else {
            const startPeriod = normalized[0];
            const endPeriod = normalized[normalized.length - 1];
            const sessionKey = resolveSessionFromRange(startPeriod, endPeriod);
            const startTime = PERIODS[startPeriod]?.start || '';
            const endTime = PERIODS[endPeriod]?.end || '';

            setHiddenScheduleFields(
                prefix,
                startPeriod,
                endPeriod,
                sessionKey,
                startTime,
                endTime,
                buildPreviewLabel(startPeriod, endPeriod),
            );
            updateSessionButtons(prefix, sessionKey);
        }

        if (!triggerPlanning) {
            return;
        }

        if (prefix === 'single') {
            refreshSinglePlanning();
            return;
        }

        calculateExpectedEndDate();
        updateThuColors();
        refreshAutoPlanning();
    }

    function clearSchedulePicker(prefix, options = {}) {
        getPickerCheckboxes(prefix).forEach((checkbox) => {
            checkbox.checked = false;
        });

        syncSchedulePicker(prefix, options);
    }

    function setPeriodRange(prefix, startPeriod, endPeriod, options = {}) {
        const rangeStart = Number(startPeriod || 0);
        const rangeEnd = Number(endPeriod || 0);

        if (!rangeStart || !rangeEnd) {
            clearSchedulePicker(prefix, options);
            return;
        }

        getPickerCheckboxes(prefix).forEach((checkbox) => {
            const value = Number(checkbox.value);
            checkbox.checked = value >= rangeStart && value <= rangeEnd;
        });

        syncSchedulePicker(prefix, options);
    }

    function setPickerFromTimes(prefix, startTime, endTime, options = {}) {
        const periodRange = findPeriodRangeByTime(startTime, endTime);
        if (periodRange) {
            setPeriodRange(prefix, periodRange.start, periodRange.end, options);
            return;
        }

        clearSchedulePicker(prefix, { triggerPlanning: false });
        setHiddenScheduleFields(prefix, '', '', '', normalizeTime(startTime), normalizeTime(endTime), `${normalizeTime(startTime)} - ${normalizeTime(endTime)}`);
        updateSessionButtons(prefix, '');

        if (options.triggerPlanning === false) {
            return;
        }

        if (prefix === 'single') {
            refreshSinglePlanning();
        } else {
            calculateExpectedEndDate();
            updateThuColors();
            refreshAutoPlanning();
        }
    }

    function applySuggestionToPicker(prefix, button) {
        const startPeriod = Number(button.dataset.periodStart || 0);
        const endPeriod = Number(button.dataset.periodEnd || 0);

        if (prefix === 'single') {
            const singleDate = document.getElementById('single-date');
            if (singleDate && button.dataset.date) {
                singleDate.value = button.dataset.date;
            }
        } else {
            const autoDate = document.getElementById('auto-start-date');
            if (autoDate && button.dataset.date) {
                autoDate.value = button.dataset.date;
            }

            const suggestedDay = dbDayFromDate(button.dataset.date);
            const targetCheckbox = document.querySelector(`#container-thu-auto input[value="${suggestedDay}"]`);
            if (targetCheckbox) {
                targetCheckbox.checked = true;
            }
        }

        if (startPeriod && endPeriod) {
            setPeriodRange(prefix, startPeriod, endPeriod);
            return;
        }

        setPickerFromTimes(prefix, button.dataset.start, button.dataset.end);
    }

    function populateTeacherSelect(selectId, teachers) {
        const select = document.getElementById(selectId);
        if (!select) return;

        const currentValue = select.value;
        const options = ['<option value="">-- Chon giang vien da nhan module --</option>'];
        teachers.forEach(teacher => {
            const label = `${teacher.name}${teacher.specialty ? ' - ' + teacher.specialty : ''} (${teacher.availability_count} don cho duyet)`;
            const selected = String(currentValue) === String(teacher.id) ? 'selected' : '';
            options.push(`<option value="${teacher.id}" ${selected}>${escapeHtml(label)}</option>`);
        });

        select.innerHTML = options.join('');
        if (!currentValue && teachers.length > 0) {
            select.value = teachers[0].id;
        }
    }

    function renderPlanningPlaceholder(panelId, message) {
        const panel = document.getElementById(panelId);
        if (!panel) return;
        panel.innerHTML = `<div class="small text-muted mb-0">${escapeHtml(message)}</div>`;
    }

    function renderPlanningPanel(panelId, context, previewLabel = '') {
        const panel = document.getElementById(panelId);
        if (!panel) return;

        const assignment = context.assignment || {};
        const availability = context.availability || {};
        const conflicts = context.conflicts || {};
        const summary = availability.summary || { weekly: [], specific: [], active_count: 0 };
        const suggestions = context.suggestions || [];
        const matched = availability.matched_slots || [];

        const assignmentColor = assignment.ok === true ? 'success' : (assignment.ok === false ? 'danger' : 'secondary');
        const availabilityColor = availability.ok === true ? 'success' : (availability.ok === false ? 'danger' : 'secondary');
        const conflictColor = conflicts.ok === true ? 'success' : (conflicts.ok === false ? 'danger' : 'secondary');

        const matchedHtml = matched.length
            ? `<div class="mt-2">${matched.map(item => `<span class="badge bg-success-subtle text-success border border-success-subtle me-1 mb-1">${escapeHtml(item.label)} - ${escapeHtml(item.schedule || item.time || '-')}</span>`).join('')}</div>`
            : '';

        const summaryWeekly = summary.weekly && summary.weekly.length
            ? summary.weekly.map(item => `<li>${escapeHtml(item.label)} - ${escapeHtml(item.schedule || item.time || '-')}</li>`).join('')
            : '<li>Khong co don xin nghi nao canh bao trong tuan.</li>';

        const summarySpecific = summary.specific && summary.specific.length
            ? summary.specific.map(item => `<li>${escapeHtml(item.label)} - ${escapeHtml(item.schedule || item.time || '-')}</li>`).join('')
            : '<li>Khong co don xin nghi nao canh bao theo ngay.</li>';

        const conflictItems = conflicts.items && conflicts.items.length
            ? `<ul class="small ps-3 mt-2 mb-0">${conflicts.items.map(item => `<li>${escapeHtml(item.course_code)} / ${escapeHtml(item.module_name)} - ${escapeHtml(item.date)} - ${escapeHtml(item.schedule || item.time || '-')}</li>`).join('')}</ul>`
            : '';

        const suggestionsHtml = suggestions.length
            ? `<div class="d-flex flex-wrap gap-2 mt-2">${suggestions.map(item => {
                const suggestionLabel = item.session_label
                    ? `${item.date_label} - ${item.session_label} (${item.period_label})`
                    : `${item.date_label} - ${item.period_label || `${item.start_time} - ${item.end_time}`}`;

                return `<button type="button" class="btn btn-sm btn-outline-primary suggestion-btn" data-date="${item.date}" data-start="${item.start_time}" data-end="${item.end_time}" data-period-start="${item.period_start || ''}" data-period-end="${item.period_end || ''}" data-panel="${panelId}" title="${escapeHtml(item.source || '')}">${escapeHtml(suggestionLabel)}</button>`;
            }).join('')}</div>`
            : '<div class="small text-muted mt-2">Chua tim thay slot goi y trong 30 ngay toi.</div>';

        panel.innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-bold text-dark">Planning context ${previewLabel ? `<span class="small text-muted">(${escapeHtml(previewLabel)})</span>` : ''}</div>
                    <div class="small text-muted">Teacher: ${escapeHtml(context.teacher_name || 'Chua chon')}</div>
                </div>
                <span class="badge rounded-pill bg-${context.can_schedule ? 'success' : 'warning'}">${context.can_schedule ? 'Co the luu lich' : 'Can xu ly truoc khi luu'}</span>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-2">Assignment</div>
                        <span class="badge bg-${assignmentColor} mb-2">${assignment.ok === true ? 'Dat' : (assignment.ok === false ? 'Chua dat' : 'Cho chon')}</span>
                        <div class="small text-muted">${escapeHtml(assignment.message || 'Chua co du lieu')}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-2">Khung day chuan va don nghi</div>
                        <span class="badge bg-${availabilityColor} mb-2">${availability.ok === true ? 'Phu hop' : (availability.ok === false ? 'Khong phu hop' : 'Cho chon')}</span>
                        <div class="small text-muted">${escapeHtml(availability.message || 'Chua co du lieu')}</div>
                        ${matchedHtml}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-2">Xung dot</div>
                        <span class="badge bg-${conflictColor} mb-2">${conflicts.ok === true ? 'Khong trung lich' : (conflicts.ok === false ? 'Dang trung lich' : 'Cho kiem tra')}</span>
                        <div class="small text-muted">${escapeHtml(conflicts.message || 'Chua co du lieu')}</div>
                        ${conflictItems}
                    </div>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-2">Canh bao don nghi (${summary.active_count || 0})</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="fw-bold small mb-1">Theo tuan</div>
                                <ul class="small ps-3 mb-0">${summaryWeekly}</ul>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold small mb-1">Theo ngay</div>
                                <ul class="small ps-3 mb-0">${summarySpecific}</ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-2">Goi y slot</div>
                        <div class="small text-muted">Nhan vao 1 slot de dien nhanh ngay va khung tiet.</div>
                        ${suggestionsHtml}
                    </div>
                </div>
            </div>
        `;
    }

    async function fetchPlanningContext(panelId, payload, previewLabel = '') {
        const panel = document.getElementById(panelId);
        if (!panel) return;

        if (!payload.module_hoc_id || !payload.ngay_hoc || !payload.gio_bat_dau || !payload.gio_ket_thuc) {
            renderPlanningPlaceholder(panelId, 'Chon day du ngay hoc va khung tiet de he thong phan tich.');
            return;
        }

        if (!payload.giang_vien_id) {
            renderPlanningPlaceholder(panelId, 'Chon giang vien de kiem tra assignment, khung day chuan, don nghi va xung dot.');
            return;
        }

        panel.innerHTML = '<div class="small text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Dang kiem tra planning context...</div>';

        try {
            const response = await fetch(`${panel.dataset.endpoint}?${new URLSearchParams(payload).toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Planning context failed');
            }

            const context = await response.json();
            renderPlanningPanel(panelId, context, previewLabel);
        } catch (error) {
            renderPlanningPlaceholder(panelId, 'Khong the tai planning context luc nay. Vui long thu lai.');
        }
    }

    function refreshSinglePlanning() {
        fetchPlanningContext('single-planning-panel', {
            module_hoc_id: document.getElementById('single-module-id')?.value || '',
            ngay_hoc: document.getElementById('single-date')?.value || '',
            gio_bat_dau: document.getElementById('single-start-time')?.value || '',
            gio_ket_thuc: document.getElementById('single-end-time')?.value || '',
            tiet_bat_dau: document.getElementById('single-tiet-bat-dau')?.value || '',
            tiet_ket_thuc: document.getElementById('single-tiet-ket-thuc')?.value || '',
            buoi_hoc: document.getElementById('single-buoi-hoc')?.value || '',
            giang_vien_id: document.getElementById('single-teacher-id')?.value || '',
        });
    }

    function getFirstAutoPreviewDate() {
        const startDate = document.getElementById('auto-start-date')?.value;
        const selectedDays = Array.from(document.querySelectorAll('input[name="thu_trong_tuan[]"]:checked')).map(cb => Number(cb.value));

        if (!startDate || selectedDays.length === 0) return null;

        const probe = new Date(startDate);
        for (let index = 0; index < 14; index++) {
            const dbDay = probe.getDay() === 0 ? 8 : probe.getDay() + 1;
            if (selectedDays.includes(dbDay)) {
                return probe.toISOString().split('T')[0];
            }
            probe.setDate(probe.getDate() + 1);
        }

        return null;
    }

    function refreshAutoPlanning() {
        const previewDate = getFirstAutoPreviewDate();
        if (!previewDate) {
            renderPlanningPlaceholder('auto-planning-panel', 'Chon ngay bat dau va it nhat 1 thu trong tuan de preview buoi dau tien.');
            return;
        }

        fetchPlanningContext('auto-planning-panel', {
            module_hoc_id: document.getElementById('auto-module-id')?.value || '',
            ngay_hoc: previewDate,
            gio_bat_dau: document.getElementById('auto-start-time')?.value || '',
            gio_ket_thuc: document.getElementById('auto-end-time')?.value || '',
            tiet_bat_dau: document.getElementById('auto-tiet-bat-dau')?.value || '',
            tiet_ket_thuc: document.getElementById('auto-tiet-ket-thuc')?.value || '',
            buoi_hoc: document.getElementById('auto-buoi-hoc')?.value || '',
            giang_vien_id: document.getElementById('auto-teacher-id')?.value || '',
        }, `Preview buoi dau tien: ${previewDate.split('-').reverse().join('/')}`);
    }

    // Sự kiện mở Modal thêm buổi lẻ
    document.querySelectorAll('.btn-add-single').forEach(btn => {
        btn.addEventListener('click', function() {
            clearSchedulePicker('single', { triggerPlanning: false });
            document.getElementById('single-module-id').value = this.dataset.moduleId;
            document.getElementById('single-module-name').textContent = this.dataset.moduleName;
            populateTeacherSelect('single-teacher-id', parseAssignedTeachers(this.dataset.assignedTeachers));
            
            const minDate = this.dataset.minDate;
            const inputNgay = document.querySelector('#modalThemBuoi input[name="ngay_hoc"]');
            if (inputNgay && minDate) {
                const nextDay = getNextDay(minDate);
                inputNgay.min = nextDay;
                inputNgay.value = nextDay;
            }

            if (document.getElementById('single-teacher-id').options.length <= 1) {
                renderPlanningPlaceholder('single-planning-panel', 'Module nay chua co giang vien da nhan phan cong.');
            } else {
                refreshSinglePlanning();
            }
            
            modalSingle.show();
        });
    });

    // Biến lưu trữ dữ liệu module đang thao tác
    let currentModuleSoBuoi = 0;
    let currentCourseEndDate = "";

    // Sự kiện mở Modal sinh lịch tự động
    document.querySelectorAll('.btn-auto-schedule').forEach(btn => {
        btn.addEventListener('click', function() {
            clearSchedulePicker('auto', { triggerPlanning: false });
            const moduleId = this.dataset.moduleId;
            document.getElementById('auto-module-id').value = moduleId;
            document.getElementById('auto-module-name').textContent = this.dataset.moduleName;
            populateTeacherSelect('auto-teacher-id', parseAssignedTeachers(this.dataset.assignedTeachers));
            
            // Lưu thông tin phục vụ tính toán
            currentModuleSoBuoi = parseInt(this.dataset.soBuoi, 10) || 0;
            currentCourseEndDate = this.dataset.khoaHocEnd;
            
            document.getElementById('auto-so-buoi-text').textContent = `${currentModuleSoBuoi} buoi`;
            document.getElementById('auto-course-end-date').textContent = currentCourseEndDate ? new Date(currentCourseEndDate).toLocaleDateString('vi-VN') : '--/--/----';

            const minDate = this.dataset.minDate;
            const inputNgayBatDau = document.querySelector('#modalSinhTuDong input[name="ngay_bat_dau"]');
            if (inputNgayBatDau && minDate) {
                const nextDay = getNextDay(minDate);
                inputNgayBatDau.min = nextDay;
                inputNgayBatDau.value = nextDay;
            }

            calculateExpectedEndDate();
            updateThuColors();
            if (document.getElementById('auto-teacher-id').options.length <= 1) {
                renderPlanningPlaceholder('auto-planning-panel', 'Module nay chua co giang vien da nhan phan cong.');
            } else {
                refreshAutoPlanning();
            }
            modalAuto.show();
        });
    });

    // --- LOGIC TÍNH TOÁN LỘ TRÌNH DỰ KIẾN ---
    function calculateExpectedEndDate() {
        const startDateVal = document.getElementById('auto-start-date').value;
        const selectedDays = Array.from(document.querySelectorAll('input[name="thu_trong_tuan[]"]:checked')).map(cb => parseInt(cb.value));
        const endDateDisplay = document.getElementById('auto-end-date-text');
        const warningBox = document.getElementById('auto-conflict-warning');

        if (!startDateVal || selectedDays.length === 0 || currentModuleSoBuoi <= 0) {
            endDateDisplay.textContent = "--/--/----";
            warningBox.classList.add('d-none');
            return;
        }

        let currentDate = new Date(startDateVal);
        let count = 0;
        let safetyLoop = 0;

        while (count < currentModuleSoBuoi && safetyLoop < 1000) {
            safetyLoop++;
            let day = currentDate.getDay(); // 0 (Sun) to 6 (Sat)
            let dbDay = (day === 0) ? 8 : (day + 1);

            if (selectedDays.includes(dbDay)) {
                count++;
                if (count === currentModuleSoBuoi) break;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }

        const expectedEndDateStr = currentDate.toISOString().split('T')[0];
        endDateDisplay.textContent = currentDate.toLocaleDateString('vi-VN');

        // So sánh với ngày kết thúc khóa học
        if (expectedEndDateStr > currentCourseEndDate) {
            warningBox.classList.remove('d-none');
            endDateDisplay.classList.add('text-danger');
        } else {
            warningBox.classList.add('d-none');
            endDateDisplay.classList.remove('text-danger');
        }

        // Đồng thời cập nhật màu sắc các thứ
        updateThuColors();
    }

    // Lắng nghe sự kiện thay đổi để tính toán lại
    document.querySelectorAll('input[name="thu_trong_tuan[]"]').forEach(cb => {
        cb.addEventListener('change', function() {
            calculateExpectedEndDate();
            updateThuColors();
            refreshAutoPlanning();
        });
    });

    // --- LOGIC TÔ MÀU THỨ (MODAL SINH TỰ ĐỘNG) ---
    const ngayBatDauInput = document.querySelector('#modalSinhTuDong input[name="ngay_bat_dau"]');
    const thuLabels = document.querySelectorAll('#container-thu-auto .thu-label-box');

    function updateThuColors() {
        if (!ngayBatDauInput.value) return;

        const date = new Date(ngayBatDauInput.value);
        let carbonDay = date.getDay(); // 0 (Sun) to 6 (Sat)
        let startThu = (carbonDay === 0) ? 8 : (carbonDay + 1); // 2 to 8

        thuLabels.forEach(label => {
            let thuVal = parseInt(label.dataset.thu);
            label.classList.remove('bg-danger', 'bg-success', 'bg-warning', 'text-white', 'text-dark');

            if (thuVal === startThu) {
                label.classList.add('bg-danger', 'text-white');
            } else if (thuVal > startThu) {
                label.classList.add('bg-success', 'text-white');
            } else {
                label.classList.add('bg-warning', 'text-dark');
            }
        });
    }

    if (ngayBatDauInput) {
        ngayBatDauInput.addEventListener('change', updateThuColors);
        // Cập nhật ngay khi mở modal (vì có giá trị mặc định)
    }

    document.querySelectorAll('.schedule-session-btn').forEach((button) => {
        button.addEventListener('click', function() {
            const prefix = this.dataset.prefix;
            const sessionKey = this.dataset.session;
            const definition = SESSIONS[sessionKey];

            if (!definition) {
                return;
            }

            const startPeriod = Number(definition.start);
            const endPeriod = Number(definition.end);
            const currentStart = Number(document.getElementById(`${prefix}-tiet-bat-dau`)?.value || 0);
            const currentEnd = Number(document.getElementById(`${prefix}-tiet-ket-thuc`)?.value || 0);

            if (currentStart === startPeriod && currentEnd === endPeriod) {
                clearSchedulePicker(prefix);
                return;
            }

            setPeriodRange(prefix, startPeriod, endPeriod);
        });
    });

    ['single', 'auto'].forEach((prefix) => {
        getPickerCheckboxes(prefix).forEach((checkbox) => {
            checkbox.addEventListener('change', () => syncSchedulePicker(prefix));
        });
    });

    ['single-date', 'single-teacher-id'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', refreshSinglePlanning);
        }
    });

    ['auto-start-date', 'auto-teacher-id'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                calculateExpectedEndDate();
                updateThuColors();
                refreshAutoPlanning();
            });
        }
    });

    document.getElementById('single-planning-panel')?.addEventListener('click', function(event) {
        const button = event.target.closest('.suggestion-btn');
        if (!button) return;

        applySuggestionToPicker('single', button);
    });

    document.getElementById('auto-planning-panel')?.addEventListener('click', function(event) {
        const button = event.target.closest('.suggestion-btn');
        if (!button) return;

        applySuggestionToPicker('auto', button);
    });

    document.querySelectorAll('.check-item, .check-all-module').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.classList.contains('check-all-module')) {
                document.querySelectorAll(`.module-${this.dataset.module}`).forEach(item => {
                    item.checked = this.checked;
                });
            }

            const count = document.querySelectorAll('.check-item:checked').length;
            document.getElementById('selectedCount').textContent = count;
            document.getElementById('btnBulkDelete').classList.toggle('d-none', count === 0);
        });
    });
});

function confirmDeleteSingle(url) {
    if (confirm('Bạn chắc chắn muốn xóa buổi học này?')) {
        const form = document.getElementById('deleteSingleForm');
        form.action = url;
        form.submit();
    }
}

function confirmDeleteModule(moduleId, moduleName) {
    if (confirm(`Bạn chắc chắn muốn xóa TOÀN BỘ các buổi học đang ở trạng thái "Chờ" của module: ${moduleName}?`)) {
        const form = document.getElementById('deleteModuleForm');
        form.action = `{{ url('admin/khoa-hoc/'.$khoaHoc->id.'/lich-hoc/module') }}/${moduleId}`;
        form.submit();
    }
}

function submitBulkDelete() {
    if (confirm('Bạn chắc chắn muốn xóa toàn bộ các buổi học đã chọn?')) {
        const form = document.getElementById('bulkDeleteForm');
        form.submit();
    }
}
</script>
@endpush

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .row-selectable:hover { background-color: rgba(13, 110, 253, 0.02); }
    .italic { font-style: italic; }
    .planning-panel { min-height: 140px; }

    /* Style cho ô chọn Thứ - VIP Upgrade */
    .thu-label-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        border: 2px solid #edf2f7;
        cursor: pointer;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        background-color: #fff;
        color: #4a5568;
        position: relative;
    }
    
    .thu-label-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #cbd5e0;
    }

    input.form-check-input:checked + .thu-label-box {
        border-color: #0d6efd;
        color: #0d6efd;
        background-color: #f0f7ff;
        transform: scale(1.05);
        z-index: 2;
    }

    input.form-check-input:checked + .thu-label-box::after {
        content: '\f058';
        font-family: "Font Awesome 6 Free";
        position: absolute;
        top: -8px;
        right: -8px;
        background: #fff;
        border-radius: 50%;
        font-size: 14px;
        color: #0d6efd;
    }

    /* Màu sắc theo logic tuần */
    .thu-label-box.bg-danger { background-color: #fff5f5 !important; color: #c53030 !important; border-color: #feb2b2 !important; }
    .thu-label-box.bg-success { background-color: #f0fff4 !important; color: #2f855a !important; border-color: #9ae6b4 !important; }
    .thu-label-box.bg-warning { background-color: #fffaf0 !important; color: #975a16 !important; border-color: #fbd38d !important; }

    /* Hiệu ứng nháy khi tự động thay đổi */
    @keyframes pulse-highlight {
        0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }
    .auto-filled {
        animation: pulse-highlight 1.5s infinite;
        border-color: #0d6efd !important;
    }

    .legend-box {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 4px;
        margin-right: 6px;
        vertical-align: middle;
    }
</style>
@endsection



