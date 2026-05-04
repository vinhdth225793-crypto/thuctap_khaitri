@extends('layouts.app')

@section('title', 'Quản lý lịch học — ' . $khoaHoc->ten_khoa_hoc)

@section('content')
@php
    $allSchedules = $khoaHoc->moduleHocs->flatMap(fn ($module) => $module->lichHocs);
    $totalModules = $khoaHoc->moduleHocs->count();
    $totalSessions = $allSchedules->count();
    $pendingSessions = $allSchedules->where('trang_thai', 'cho')->count();
    $assignedTeacherCount = $khoaHoc->moduleHocs
        ->flatMap(fn ($module) => $module->assignedTeachers->pluck('giang_vien_id'))
        ->filter()
        ->unique()
        ->count();
@endphp

<div class="container-fluid admin-page-x lh-page">
    <nav aria-label="breadcrumb" class="lh-breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ma_khoa_hoc }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Lịch học</li>
        </ol>
    </nav>

    <div class="apx-welcome lh-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="apx-welcome-text">
            <h4>Planner lịch học cho {{ $khoaHoc->ten_khoa_hoc }}</h4>
            <p>
                Theo dõi tiến độ mở buổi, phân công giảng viên và kiểm soát các buổi đang chờ trên cùng một màn hình.
                Khóa học hiện có <strong>{{ $totalModules }} module</strong>, đã tạo <strong>{{ $totalSessions }} buổi</strong>
                và còn <strong>{{ $pendingSessions }} buổi chờ triển khai</strong>.
            </p>
            <div class="lh-welcome-badges">
                <span class="badge bg-{{ $khoaHoc->badge_trang_thai }} lh-course-status">{{ $khoaHoc->label_trang_thai_van_hanh }}</span>
                <span class="lh-inline-note"><i class="fas fa-layer-group"></i> {{ $khoaHoc->nhomNganh?->ten_nhom_nganh ?? 'Chưa gán nhóm ngành' }}</span>
            </div>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Chi tiết khóa học
            </a>
            <a href="{{ route('admin.khoa-hoc.index') }}" class="apx-view-toggle">
                <i class="fas fa-table-list"></i>
                <span>Danh sách khóa học</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="apx-stat tone-primary">
                <div class="aps-icon"><i class="fas fa-cubes"></i></div>
                <div class="aps-text">
                    <strong>{{ $totalModules }}</strong>
                    <small>Module đang quản lý</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="apx-stat tone-info">
                <div class="aps-icon"><i class="fas fa-calendar-days"></i></div>
                <div class="aps-text">
                    <strong>{{ $totalSessions }}</strong>
                    <small>Buổi học đã tạo</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="apx-stat tone-warning">
                <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="aps-text">
                    <strong>{{ $pendingSessions }}</strong>
                    <small>Buổi đang chờ</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="apx-stat tone-success">
                <div class="aps-icon"><i class="fas fa-user-check"></i></div>
                <div class="aps-text">
                    <strong>{{ $assignedTeacherCount }}</strong>
                    <small>Giảng viên đã nhận lớp</small>
                </div>
            </div>
        </div>
    </div>

    <div class="lh-toolbar">
        <div class="lh-toolbar-main">
            <div class="lh-toolbar-copy">
                <strong>Chọn nhanh các buổi có trạng thái "Chờ"</strong>
                <span>Xóa hàng loạt chỉ áp dụng cho các buổi chưa diễn ra để tránh tác động tới dữ liệu đang vận hành.</span>
            </div>
            <label class="lh-global-toggle" for="checkAllGlobal">
                <input type="checkbox" id="checkAllGlobal" class="form-check-input ms-0" title="Chọn tất cả các buổi học đang chờ">
                <span>Chọn tất cả buổi chờ</span>
            </label>
        </div>
        <div class="lh-toolbar-actions">
            <div class="lh-bulk-counter">
                Đang chọn <strong id="selectedCount">0</strong> buổi
            </div>
            <button id="btnBulkDelete" class="btn btn-danger btn-sm shadow-sm fw-bold d-none" onclick="submitBulkDelete()">
                <i class="fas fa-trash-alt me-1"></i> Xóa các buổi đã chọn
            </button>
        </div>
    </div>

    <form id="bulkDeleteForm" action="{{ route('admin.khoa-hoc.lich-hoc.destroy-bulk', $khoaHoc->id) }}" method="POST">
        @csrf @method('DELETE')
    </form>

    @forelse($khoaHoc->moduleHocs as $index => $module)
        @php
            $prevModule = $index > 0 ? $khoaHoc->moduleHocs[$index - 1] : null;
            $minDate = $prevModule ? $prevModule->ngay_ket_thuc_thuc_te : date('Y-m-d');
            $teacherOptions = $module->phanCongGiangViens->map(function ($pc) {
                $gv = $pc->giangVien;

                return [
                    'id' => $pc->giang_vien_id,
                    'name' => $gv?->nguoiDung?->ho_ten ?? 'N/A',
                    'pending_leave_count' => $gv?->donXinNghis?->where('trang_thai', 'cho_duyet')->count() ?? 0,
                ];
            })->values();
            $assignedTeacherNames = $module->assignedTeachers
                ->map(fn ($assignment) => $assignment->giangVien?->nguoiDung?->ho_ten)
                ->filter()
                ->values();
            $sessionCount = $module->lichHocs->count();
            $pendingCount = $module->lichHocs->where('trang_thai', 'cho')->count();
            $activeCount = $module->lichHocs->where('trang_thai', 'dang_hoc')->count();
            $doneCount = $module->lichHocs->where('trang_thai', 'hoan_thanh')->count();
            $progressPercent = $module->so_buoi > 0 ? min(100, round(($sessionCount / $module->so_buoi) * 100)) : 0;
            $moduleLectureCount = $module->lichHocs->sum(fn ($schedule) => $schedule->baiGiangs->count());
            $moduleResourceCount = $module->lichHocs->sum(fn ($schedule) => $schedule->taiNguyen->count());
            $nextPlanningDate = \Carbon\Carbon::parse($minDate)->format('d/m/Y');
        @endphp
        <section class="apx-section lh-module-section" id="module-{{ $module->id }}">
            <header class="apx-section-head lh-module-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">{{ str_pad($module->thu_tu_module, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h2><i class="fas fa-cube"></i> {{ $module->ten_module }}</h2>
                        <p>{{ \Illuminate\Support\Str::limit($module->mo_ta ?: 'Chưa có mô tả chi tiết cho module này.', 140) }}</p>
                    </div>
                </div>
                <div class="apx-section-meta lh-module-pills">
                    <span class="apx-meta-pill"><strong>{{ $sessionCount }}/{{ $module->so_buoi }}</strong> buổi</span>
                    <span class="apx-meta-pill"><strong>{{ $pendingCount }}</strong> chờ</span>
                    <span class="apx-meta-pill"><strong>{{ $doneCount }}</strong> hoàn thành</span>
                </div>
            </header>

            <div class="lh-module-card">
                <div class="lh-module-toolbar">
                    <div class="lh-progress-card">
                        <div class="lh-progress-copy">
                            <span class="lh-progress-label">Tiến độ planner</span>
                            <strong>{{ $sessionCount }} / {{ $module->so_buoi }} buổi đã lên lịch</strong>
                            <small>
                                {{ $pendingCount }} buổi chờ duyệt, {{ $activeCount }} buổi đang học
                                @if($module->thoi_luong_du_kien_label)
                                    · Thời lượng dự kiến {{ $module->thoi_luong_du_kien_label }}
                                @endif
                            </small>
                        </div>
                        <div class="lh-progress-track" aria-hidden="true">
                            <span class="lh-progress-fill" style="width: {{ $progressPercent }}%"></span>
                        </div>
                    </div>

                    <div class="lh-module-actions">
                        <form action="{{ route('admin.khoa-hoc.lich-hoc.update-so-buoi', [$khoaHoc->id, $module->id]) }}" method="POST" class="lh-session-target-form">
                            @csrf
                            <label for="so-buoi-{{ $module->id }}" class="lh-field-inline">Số buổi mục tiêu</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Target</span>
                                <input type="number" id="so-buoi-{{ $module->id }}" name="so_buoi" value="{{ $module->so_buoi }}" class="form-control text-center fw-bold" min="1">
                                <button type="submit" class="btn btn-primary" title="Lưu số buổi">
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </form>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-danger fw-bold lh-action-btn" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-trash-alt me-1"></i> Xóa nhanh
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 lh-dropdown">
                                <li>
                                    <button type="button" class="dropdown-item text-danger small py-2" onclick='confirmDeleteModule({{ $module->id }}, @json($module->ten_module))'>
                                        <i class="fas fa-eraser me-2"></i> Xóa tất cả buổi "Chờ"
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <button type="button" class="btn btn-sm btn-success fw-bold px-3 btn-auto-schedule lh-action-btn lh-action-success"
                                data-module-id="{{ $module->id }}"
                                data-module-name="{{ $module->ten_module }}"
                                data-so-buoi="{{ $module->so_buoi }}"
                                data-min-date="{{ $minDate }}"
                                data-teachers='@json($teacherOptions)'
                                data-existing-days='@json($module->lichHocs->map(fn ($l) => $l->ngay_hoc->dayOfWeek === 0 ? 8 : $l->ngay_hoc->dayOfWeek + 1)->unique()->values())'>
                            <i class="fas fa-magic me-1"></i> Sinh lịch tự động
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-bold px-3 btn-add-single lh-action-btn lh-action-primary"
                                data-module-id="{{ $module->id }}"
                                data-module-name="{{ $module->ten_module }}"
                                data-min-date="{{ $minDate }}"
                                data-teachers='@json($teacherOptions)'>
                            <i class="fas fa-plus me-1"></i> Thêm buổi lẻ
                        </button>
                    </div>
                </div>

                <div class="lh-module-insights">
                    <div class="lh-info-tile">
                        <span class="lh-info-label">Giảng viên đã nhận</span>
                        <strong>{{ $assignedTeacherNames->count() }}</strong>
                        <small>{{ $assignedTeacherNames->isNotEmpty() ? $assignedTeacherNames->take(2)->implode(', ') . ($assignedTeacherNames->count() > 2 ? ' +' . ($assignedTeacherNames->count() - 2) : '') : 'Chưa có giảng viên xác nhận' }}</small>
                    </div>
                    <div class="lh-info-tile">
                        <span class="lh-info-label">Tài nguyên theo module</span>
                        <strong>{{ $moduleLectureCount }} bài giảng · {{ $moduleResourceCount }} tài liệu</strong>
                        <small>Dùng để kiểm tra mức độ hoàn thiện nội dung trước khi khóa học chạy chính thức.</small>
                    </div>
                    <div class="lh-info-tile">
                        <span class="lh-info-label">Ngày có thể xếp tiếp</span>
                        <strong>{{ $nextPlanningDate }}</strong>
                        <small>{{ $index === 0 ? 'Module đầu tiên có thể lên lịch từ hôm nay.' : 'Mốc này nối tiếp module trước để hạn chế chồng lịch.' }}</small>
                    </div>
                </div>

                <div class="lh-table-shell">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 lh-table">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="46">
                                        <input type="checkbox" class="form-check-input check-all-module" data-module="{{ $module->id }}">
                                    </th>
                                    <th width="82">Buổi</th>
                                    <th width="168">Thời gian</th>
                                    <th width="210">Nội dung</th>
                                    <th>Địa điểm / Giảng viên</th>
                                    <th class="text-center" width="138">Điểm danh</th>
                                    <th class="text-center" width="136">Trạng thái</th>
                                    <th class="pe-4 text-center" width="90">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($module->lichHocs as $lich)
                                    @php
                                        $hasAttendance = $lich->diemDanhs->isNotEmpty();
                                        $sessionLectureCount = $lich->baiGiangs->count();
                                        $sessionResourceCount = $lich->taiNguyen->count();
                                        $statusTone = match($lich->trang_thai) {
                                            'dang_hoc' => 'info',
                                            'hoan_thanh' => 'success',
                                            'huy' => 'danger',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <tr class="lh-session-row {{ $lich->trang_thai === 'cho' ? 'is-pending' : 'is-muted' }}">
                                        <td class="ps-4">
                                            @if($lich->trang_thai === 'cho')
                                                <input type="checkbox" name="ids[]" value="{{ $lich->id }}" form="bulkDeleteForm" class="form-check-input check-item module-{{ $module->id }}">
                                            @else
                                                <i class="fas fa-lock text-muted smaller" title="Buổi học đã bắt đầu hoặc kết thúc, không thể chọn xóa nhanh"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="lh-session-order">#{{ $lich->buoi_so }}</div>
                                            <div class="lh-session-sub">{{ $lich->thu_label }}</div>
                                        </td>
                                        <td>
                                            <div class="lh-session-date"><i class="far fa-calendar-alt"></i>{{ $lich->ngay_hoc->format('d/m/Y') }}</div>
                                            <div class="lh-session-time">
                                                <i class="far fa-clock"></i>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}
                                                <span>{{ $lich->buoi_hoc_label }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="lh-chip-stack">
                                                <span class="lh-data-chip {{ $sessionLectureCount > 0 ? 'tone-info' : 'tone-neutral' }}">
                                                    <i class="fas fa-book-open"></i>{{ $sessionLectureCount }} bài giảng
                                                </span>
                                                <span class="lh-data-chip {{ $sessionResourceCount > 0 ? 'tone-warning' : 'tone-neutral' }}">
                                                    <i class="fas fa-paperclip"></i>{{ $sessionResourceCount }} tài liệu
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="lh-location-line">
                                                @if($lich->hinh_thuc === 'online')
                                                    <span class="lh-mode-badge tone-info"><i class="fas fa-video"></i>Online</span>
                                                    @if($lich->link_online)
                                                        <a href="{{ $lich->link_online }}" target="_blank" class="lh-inline-link">
                                                            <i class="fas fa-external-link-alt"></i> Mở link
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="lh-mode-badge tone-success"><i class="fas fa-location-dot"></i>{{ $lich->phong_hoc ?: 'Chưa gán phòng' }}</span>
                                                @endif
                                            </div>
                                            <div class="lh-teacher-line">
                                                <span class="lh-avatar-mini"><i class="fas fa-user-tie"></i></span>
                                                <span>{{ $lich->giangVien?->nguoiDung?->ho_ten ?? 'Chưa gán giảng viên' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="lh-state-pill {{ $hasAttendance ? 'tone-success' : 'tone-neutral' }}">
                                                <i class="fas {{ $hasAttendance ? 'fa-check-circle' : 'fa-circle' }}"></i>
                                                {{ $hasAttendance ? 'Đã điểm danh' : 'Chưa điểm danh' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="lh-status-wrap">
                                                <span class="lh-status-pill tone-{{ $statusTone }}">{{ $lich->trang_thai_label }}</span>
                                                @if($lich->giang_vien_id && $lich->trang_thai !== 'cho')
                                                    <a href="{{ route('admin.diem-danh.giang-vien.show', [$lich->id, $lich->giang_vien_id]) }}" class="lh-inline-link">
                                                        <i class="fas fa-search-plus"></i> Log dạy
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="lh-row-actions">
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
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted lh-empty-row">
                                            <i class="fas fa-calendar-xmark mb-2 d-block"></i>
                                            Chưa có buổi học nào cho module này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    @empty
        <section class="apx-section">
            <div class="lh-empty-state">
                <i class="fas fa-cubes"></i>
                <h3>Khóa học này chưa có module để lên lịch</h3>
                <p>Hãy quay lại trang chi tiết khóa học và tạo module trước khi dùng planner lịch học.</p>
                <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-primary fw-bold px-4">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại khóa học
                </a>
            </div>
        </section>
    @endforelse
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

    function updateSessionButtonState(prefix, sessionKey) {
        document.querySelectorAll(`.schedule-session-btn[data-prefix="${prefix}"]`).forEach(button => {
            button.classList.toggle('is-active', button.dataset.session === sessionKey);
        });
    }

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
        renderAutoPlanningPlaceholder('Chưa có dữ liệu kiem tra trung lich.');
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
            : 'Chưa có lịch cố định';
        const selectedText = selectedDays.length > 0
            ? selectedDays.map(formatThuLabel).join(', ')
            : 'Chưa chọn thu nao';
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
        const isLọcked = lockThuSelection.checked;
        const existingSet = new Set(currentExistingDays);
        const conflictSet = new Set(currentConflictDays);

        thuContainer.classList.toggle('selection-locked', isLọcked);

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
            label.classList.toggle('thu-locked', isLọcked);
            label.classList.toggle('thu-conflict', conflictSet.has(thu));

            if (conflictSet.has(thu) && isSelected) {
                stateEl.textContent = 'Trung lich';
            } else if (isExisting && isSelected) {
                stateEl.textContent = 'Da co + chon';
            } else if (isExisting) {
                stateEl.textContent = 'Da co lich';
            } else if (isSelected) {
                stateEl.textContent = 'Đang chọn';
            } else {
                stateEl.textContent = 'Chưa chọn';
            }
        });

        btnUseExistingDays.disabled = isLọcked || currentExistingDays.length === 0;
        btnUseDefaultDays.disabled = isLọcked;
        btnClearDays.disabled = isLọcked;

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
            return { item, hasConflict: false, canSchedule: true, message: 'Không có panel kiem tra.' };
        }

        if (!item.date || !item.session || !item.startTime || !item.endTime || item.periodStart === null || item.periodEnd === null) {
            return {
                item,
                context: null,
                hasConflict: false,
                canSchedule: false,
                message: 'Buổi preview chưa đủ thông tin ngày học hoặc ca học.',
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
                message: context?.conflicts?.message || context?.errors?.gio_bat_dau || 'Không có xung dot.',
            };
        } catch (error) {
            return {
                item,
                context: null,
                hasConflict: false,
                canSchedule: false,
                message: 'Không thể kiểm tra xung đột lúc này.',
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
                    <div class="fw-bold text-dark">Kiểm tra trùng lịch</div>
                    <div class="small text-muted">Đã quét ${results.length} buổi trong lộ trình xem trước.</div>
                </div>
                <span class="badge rounded-pill bg-${conflictItems.length > 0 ? 'danger' : 'success'}">${conflictItems.length > 0 ? `${conflictItems.length} buổi bị trùng` : 'Không bị trùng lịch'}</span>
            </div>
            <div class="small text-muted mb-2">${invalidItems.length > 0 ? `Có ${invalidItems.length} buổi cần xử lý trước khi lưu.` : 'Tất cả buoi hop le de tiep tuc luu lich.'}</div>
            ${conflictItems.length > 0 ? `
                <div class="d-flex flex-wrap gap-2">
                    ${conflictItems.slice(0, 8).map(result => `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">${result.item.date} - ${escapeHtml(formatThuLabel(result.item.thu))}</span>`).join('')}
                </div>
            ` : ''}
            ${errorItems.length > 0 ? `<div class="small text-warning mt-3">Có một vài buổi chưa kiểm tra được planning context. Vui lòng thử lại.</div>` : ''}
        `;
    }

    async function highlightAutoPreviewConflicts(previewList) {
        renderAutoPlanningPlaceholder('<span class="spinner-border spinner-border-sm me-2"></span>Đang kiểm tra trùng lịch...', 'muted');

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

            // Set default session (toi)
            const def = SESSIONS['toi'];
            document.getElementById('auto-tiet-bat-dau').value = def.start;
            document.getElementById('auto-tiet-ket-thuc').value = def.end;
            document.getElementById('auto-buoi-hoc').value = 'toi';
            document.getElementById('auto-time-preview').value = `${def.label} | Tiết ${def.start}-${def.end}`;
            document.getElementById('auto-start-time').value = def.start_time || '';
            document.getElementById('auto-end-time').value = def.end_time || '';
            updateSessionButtonState('auto', 'toi');
            
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

            // Set default session (toi)
            const def = SESSIONS['toi'];
            document.getElementById('single-tiet-bat-dau').value = def.start;
            document.getElementById('single-tiet-ket-thuc').value = def.end;
            document.getElementById('single-buoi-hoc').value = 'toi';
            document.getElementById('single-time-preview').value = `${def.label} | Tiết ${def.start}-${def.end}`;
            updateSessionButtonState('single', 'toi');

            modalSingle.show();
        });
    });

    // Xem trước lộ trình
    btnPreviewAuto.addEventListener('click', async function() {
        const startTiết = document.getElementById('auto-tiet-bat-dau').value;
        const previewList = buildPreviewList();
        
        if (!ngayBatDauInput.value || previewList.length === 0 || !startTiết) {
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
                        ${Object.entries(SESSIONS).map(([k, v]) => `<option value="${k}" ${parseInt(startTiết, 10) === v.start ? 'selected' : ''}>${v.label} (T${v.start}-${v.end})</option>`).join('')}
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
            updateSessionButtonState(prefix, this.dataset.session);
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

@include('pages.admin.partials._admin-page-styles')

<style>
    .smaller {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.55px;
    }

    .lh-page { padding-bottom: 12px; }

    .lh-breadcrumb {
        margin-bottom: 14px;
    }

    .lh-breadcrumb .breadcrumb {
        margin: 0;
        padding: 12px 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .lh-breadcrumb .breadcrumb-item,
    .lh-breadcrumb .breadcrumb-item a {
        color: #64748b;
        font-weight: 600;
        text-decoration: none;
    }

    .lh-breadcrumb .breadcrumb-item.active {
        color: #0f172a;
        font-weight: 700;
    }

    .lh-welcome {
        margin-bottom: 16px;
    }

    .lh-welcome-badges {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .lh-course-status {
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    }

    .lh-inline-note {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        color: #fff;
        backdrop-filter: blur(6px);
    }

    .lh-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #dbeafe;
        border-radius: 14px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        flex-wrap: wrap;
    }

    .lh-toolbar-main,
    .lh-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .lh-toolbar-copy {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .lh-toolbar-copy strong {
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 800;
    }

    .lh-toolbar-copy span {
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.45;
    }

    .lh-global-toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        color: #1e40af;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
    }

    .lh-global-toggle .form-check-input {
        width: 1.15rem;
        height: 1.15rem;
        cursor: pointer;
        margin-top: 0;
    }

    .lh-bulk-counter {
        padding: 8px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .lh-bulk-counter strong {
        color: #0f172a;
        font-weight: 900;
    }

    .lh-module-section {
        margin-bottom: 22px;
    }

    .lh-module-head {
        align-items: flex-start;
    }

    .lh-module-pills {
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .lh-module-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.04);
    }

    .lh-module-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        padding: 22px 22px 18px;
        border-bottom: 1px solid #eef2ff;
        flex-wrap: wrap;
    }

    .lh-progress-card {
        flex: 1 1 320px;
        min-width: 280px;
    }

    .lh-progress-copy {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 12px;
    }

    .lh-progress-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .lh-progress-copy strong {
        font-size: 1.03rem;
        line-height: 1.3;
        color: #0f172a;
        font-weight: 900;
    }

    .lh-progress-copy small {
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .lh-progress-track {
        position: relative;
        width: 100%;
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .lh-progress-fill {
        position: absolute;
        inset: 0 auto 0 0;
        background: linear-gradient(135deg, #16a34a 0%, #0ea5e9 100%);
        border-radius: inherit;
    }

    .lh-module-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1 1 420px;
    }

    .lh-session-target-form {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .lh-field-inline {
        margin: 0;
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.55px;
    }

    .lh-session-target-form .input-group {
        flex-wrap: nowrap;
    }

    .lh-session-target-form .input-group-text,
    .lh-session-target-form .form-control,
    .lh-session-target-form .btn {
        border-color: #dbeafe;
        box-shadow: none;
    }

    .lh-session-target-form .input-group-text {
        background: #fff;
        color: #1d4ed8;
        font-weight: 800;
    }

    .lh-session-target-form .form-control {
        min-width: 72px;
        background: #fff;
    }

    .lh-action-btn {
        min-height: 40px;
        border-radius: 12px;
        padding-inline: 16px;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
    }

    .lh-action-primary {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
        border: 0 !important;
    }

    .lh-action-success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        border: 0 !important;
    }

    .lh-dropdown {
        border-radius: 12px;
        overflow: hidden;
    }

    .lh-module-insights {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        padding: 0 22px 20px;
    }

    .lh-info-tile {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 16px 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        min-height: 120px;
    }

    .lh-info-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.55px;
    }

    .lh-info-tile strong {
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 800;
        line-height: 1.4;
    }

    .lh-info-tile small {
        color: #64748b;
        line-height: 1.5;
        font-size: 0.8rem;
    }

    .lh-table-shell {
        padding: 0 14px 14px;
    }

    .lh-table {
        min-width: 980px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .lh-table thead th {
        padding: 14px 12px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.65px;
        white-space: nowrap;
    }

    .lh-table thead th:first-child {
        border-top-left-radius: 14px;
    }

    .lh-table thead th:last-child {
        border-top-right-radius: 14px;
    }

    .lh-table tbody td {
        padding: 15px 12px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        background: #fff;
    }

    .lh-session-row.is-muted td {
        background: #fcfdff;
    }

    .lh-session-row:hover td {
        background: #f8fbff;
    }

    .lh-session-order {
        font-size: 1rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
    }

    .lh-session-sub {
        margin-top: 4px;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.55px;
    }

    .lh-session-date,
    .lh-session-time {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.45;
    }

    .lh-session-time {
        margin-top: 6px;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .lh-session-time span {
        padding: 3px 8px;
        background: #eef2ff;
        border-radius: 999px;
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .lh-chip-stack {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .lh-data-chip,
    .lh-mode-badge,
    .lh-state-pill,
    .lh-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 800;
        line-height: 1.2;
        border: 1px solid transparent;
    }

    .lh-data-chip {
        justify-content: flex-start;
        width: fit-content;
    }

    .lh-data-chip.tone-info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .lh-data-chip.tone-warning {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #c2410c;
    }

    .lh-data-chip.tone-neutral,
    .lh-state-pill.tone-neutral,
    .lh-status-pill.tone-neutral {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
    }

    .lh-mode-badge.tone-info {
        background: #ecfeff;
        border-color: #a5f3fc;
        color: #0f766e;
    }

    .lh-mode-badge.tone-success,
    .lh-state-pill.tone-success,
    .lh-status-pill.tone-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #15803d;
    }

    .lh-status-pill.tone-info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .lh-status-pill.tone-danger {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #be123c;
    }

    .lh-location-line {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }

    .lh-teacher-line {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0f172a;
        font-size: 0.84rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .lh-avatar-mini {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        display: inline-grid;
        place-items: center;
        border: 1px solid #bfdbfe;
        flex-shrink: 0;
    }

    .lh-status-wrap {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: center;
    }

    .lh-inline-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #1d4ed8;
        font-size: 0.76rem;
        font-weight: 800;
        text-decoration: none;
    }

    .lh-inline-link:hover {
        color: #1e3a8a;
    }

    .lh-row-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .lh-row-actions .btn {
        width: 34px;
        height: 34px;
        display: inline-grid;
        place-items: center;
        border-radius: 10px;
    }

    .lh-empty-row {
        font-size: 0.92rem;
        background: #fff;
    }

    .lh-empty-row i {
        font-size: 1.6rem;
        color: #94a3b8;
    }

    .lh-empty-state {
        padding: 34px 26px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.04);
    }

    .lh-empty-state i {
        font-size: 2rem;
        color: #1d4ed8;
        margin-bottom: 14px;
    }

    .lh-empty-state h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .lh-empty-state p {
        color: #64748b;
        max-width: 480px;
        margin: 0 auto 18px;
        line-height: 1.6;
    }

    .planning-panel {
        min-height: 80px;
        background: linear-gradient(135deg, #f8fbff 0%, #eff6ff 100%) !important;
        border: 1px solid #dbeafe !important;
        border-radius: 14px !important;
        color: #334155;
    }

    .planning-panel .badge {
        font-weight: 800;
        border-radius: 999px;
    }

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

    #modalThemBuoi .modal-dialog,
    #modalSinhTuDong .modal-dialog {
        margin-top: 2rem;
        margin-bottom: 2rem;
    }

    #modalThemBuoi .modal-content,
    #modalSinhTuDong .modal-content {
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.2);
    }

    #modalThemBuoi .modal-header,
    #modalSinhTuDong .modal-header {
        padding: 18px 24px;
    }

    #modalThemBuoi .modal-header {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
    }

    #modalSinhTuDong .modal-header {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
    }

    #modalThemBuoi .modal-body,
    #modalSinhTuDong .modal-body {
        background: #f8fbff;
    }

    #modalSinhTuDong .col-lg-5 {
        background: linear-gradient(180deg, #f8fbff 0%, #f8fafc 100%) !important;
    }

    #modalThemBuoi .form-control,
    #modalThemBuoi .form-select,
    #modalSinhTuDong .form-control,
    #modalSinhTuDong .form-select {
        border: 1px solid #dbeafe !important;
        border-radius: 12px;
        background: #fff !important;
        box-shadow: none !important;
        min-height: 44px;
    }

    #modalThemBuoi .form-control:focus,
    #modalThemBuoi .form-select:focus,
    #modalSinhTuDong .form-control:focus,
    #modalSinhTuDong .form-select:focus {
        border-color: #60a5fa !important;
        box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.16) !important;
    }

    #modalThemBuoi .schedule-session-btn,
    #modalSinhTuDong .schedule-session-btn {
        border-radius: 999px;
        font-weight: 800;
        border-width: 1px;
        background: #fff;
        transition: all 0.2s ease;
    }

    #modalThemBuoi .schedule-session-btn {
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    #modalSinhTuDong .schedule-session-btn {
        color: #15803d;
        border-color: #bbf7d0;
    }

    #modalThemBuoi .schedule-session-btn.is-active,
    #modalThemBuoi .schedule-session-btn:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
    }

    #modalSinhTuDong .schedule-session-btn.is-active,
    #modalSinhTuDong .schedule-session-btn:hover {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 10px 20px rgba(22, 163, 74, 0.24);
    }

    #single-time-preview,
    #auto-time-preview {
        border: 1px solid #dbeafe !important;
        border-radius: 12px;
        padding-inline: 14px;
        background: #f8fbff !important;
    }

    #modalThemBuoi .modal-footer,
    #modalSinhTuDong .border-top.bg-light {
        background: #f8fafc !important;
    }

    #modalSinhTuDong #tablePreviewAuto thead th {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.55px;
        color: #64748b;
    }

    #modalSinhTuDong #tablePreviewAuto tbody td,
    #modalThemBuoi .modal-body {
        color: #334155;
    }

    #modalSinhTuDong .lh-auto-dialog {
        max-width: min(1280px, calc(100vw - 32px));
    }

    #modalSinhTuDong .lh-auto-content {
        border-radius: 18px;
        background: #fff;
    }

    #modalSinhTuDong .lh-auto-header {
        position: relative;
        padding: 18px 24px;
        background: linear-gradient(135deg, #16a34a 0%, #0f766e 48%, #4361ee 100%) !important;
    }

    .lh-auto-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .lh-auto-header-icon {
        width: 44px;
        height: 44px;
        display: inline-grid;
        place-items: center;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #fff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.18);
        flex-shrink: 0;
    }

    .lh-auto-kicker {
        display: block;
        margin-bottom: 2px;
        color: rgba(255, 255, 255, 0.78);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }

    .lh-auto-body {
        background: #f8fbff !important;
    }

    .lh-auto-layout {
        min-height: min(76vh, 760px);
    }

    #modalSinhTuDong .lh-auto-config {
        background: linear-gradient(180deg, #f8fbff 0%, #f8fafc 100%) !important;
        border-right: 1px solid #e2e8f0;
    }

    .lh-auto-scroll,
    .lh-auto-preview-scroll {
        max-height: 76vh;
        overflow-y: auto;
    }

    .lh-auto-scroll {
        padding: 20px;
    }

    .lh-auto-preview-scroll {
        flex: 1;
        padding: 20px 22px;
    }

    .lh-auto-block {
        padding: 16px;
        margin-bottom: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .lh-auto-module-block {
        background: linear-gradient(135deg, #ffffff 0%, #eef2ff 100%);
        border-color: #c7d2fe;
    }

    .lh-auto-block-head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .lh-auto-step {
        width: 32px;
        height: 32px;
        display: inline-grid;
        place-items: center;
        border-radius: 10px;
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 100%);
        color: #fff;
        font-size: 0.74rem;
        font-weight: 900;
        box-shadow: 0 8px 16px rgba(67, 97, 238, 0.24);
        flex-shrink: 0;
    }

    .lh-auto-block-head h6,
    .lh-auto-preview-head h6 {
        margin: 0;
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 900;
        line-height: 1.25;
    }

    .lh-auto-block-head p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .lh-auto-label,
    .lh-auto-field-label {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.55px;
        text-transform: uppercase;
    }

    .lh-auto-module-name {
        margin-top: 4px;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
        line-height: 1.35;
    }

    .lh-auto-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .lh-auto-metric {
        padding: 12px;
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid #dbeafe;
        border-radius: 10px;
    }

    .lh-auto-metric span {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .lh-auto-metric strong {
        color: #1d4ed8;
        font-size: 1.3rem;
        line-height: 1;
        font-weight: 900;
    }

    .lh-day-tools,
    .lh-session-tools {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .lh-day-tools .btn {
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .lh-lock-row {
        display: flex;
        justify-content: flex-end;
        margin: -4px 0 12px;
        color: #64748b;
    }

    .lh-auto-days-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(72px, 1fr));
        gap: 8px;
    }

    #modalSinhTuDong .lh-auto-days-grid .thu-label-box {
        width: 100%;
        min-height: 62px;
        border-radius: 10px;
    }

    .lh-auto-legend {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 12px;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.45px;
        text-transform: uppercase;
    }

    .lh-auto-note {
        margin-top: 10px;
        padding: 10px 12px;
        background: #f8fafc;
        border-left: 3px solid #4361ee;
        border-radius: 8px;
        color: #475569;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    #modalSinhTuDong #auto-time-preview {
        margin-top: 10px;
        color: #15803d;
    }

    .lh-auto-preview-btn {
        min-height: 48px;
        border: 0;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
        box-shadow: 0 14px 28px rgba(29, 78, 216, 0.24);
    }

    .lh-auto-preview-btn:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 18px 34px rgba(29, 78, 216, 0.32);
    }

    .lh-auto-preview {
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .lh-auto-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 14px;
        margin-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    .lh-auto-preview-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        color: #15803d;
        font-size: 0.72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .lh-auto-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    #modalSinhTuDong #tablePreviewAuto thead {
        background: #f8fafc;
    }

    #modalSinhTuDong #tablePreviewAuto td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .lh-auto-empty-preview {
        min-height: 430px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        padding: 32px;
        color: #64748b;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
    }

    .lh-auto-empty-icon {
        width: 58px;
        height: 58px;
        display: inline-grid;
        place-items: center;
        margin-bottom: 14px;
        border-radius: 16px;
        background: #eff6ff;
        color: #4361ee;
        font-size: 1.35rem;
    }

    .lh-auto-empty-preview strong {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 900;
    }

    .lh-auto-empty-preview p {
        max-width: 420px;
        margin: 8px auto 0;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .lh-auto-footer {
        padding: 18px 22px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .lh-auto-save-btn {
        min-height: 48px;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        box-shadow: 0 14px 28px rgba(22, 163, 74, 0.22);
        text-transform: none;
    }

    .lh-auto-save-btn.disabled,
    .lh-auto-save-btn:disabled {
        opacity: 0.58;
        box-shadow: none;
    }

    @media (max-width: 1199.98px) {
        .lh-module-insights {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .lh-toolbar {
            align-items: stretch;
        }

        .lh-toolbar-main,
        .lh-toolbar-actions {
            width: 100%;
            justify-content: space-between;
        }

        .lh-module-toolbar {
            padding-bottom: 16px;
        }

        .lh-module-actions {
            justify-content: flex-start;
        }

        .lh-table-shell {
            padding-inline: 0;
        }

        #modalSinhTuDong .lh-auto-config {
            border-right: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .lh-auto-layout {
            min-height: auto;
        }

        .lh-auto-scroll,
        .lh-auto-preview-scroll {
            max-height: none;
        }

        .lh-auto-preview-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .lh-auto-empty-preview {
            min-height: 260px;
        }
    }

    @media (max-width: 767.98px) {
        .lh-breadcrumb .breadcrumb {
            padding: 10px 12px;
        }

        .lh-toolbar-main,
        .lh-toolbar-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .lh-global-toggle,
        .lh-bulk-counter,
        .lh-toolbar-actions #btnBulkDelete {
            width: 100%;
            justify-content: center;
        }

        .lh-module-head {
            flex-direction: column;
        }

        .lh-module-pills {
            justify-content: flex-start;
        }

        .lh-session-target-form,
        .lh-progress-card {
            min-width: 100%;
        }

        #modalSinhTuDong .lh-auto-dialog {
            max-width: calc(100vw - 16px);
            margin-inline: 8px;
        }

        #modalSinhTuDong .lh-auto-config {
            border-right: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .lh-auto-layout,
        .lh-auto-scroll,
        .lh-auto-preview-scroll {
            max-height: none;
        }

        .lh-auto-preview-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .lh-auto-empty-preview {
            min-height: 260px;
        }
    }
</style>
@endsection
