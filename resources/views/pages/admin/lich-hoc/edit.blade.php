@extends('layouts.app')

@section('title', 'Sua buoi hoc')

@section('content')
@php
    use App\Support\Scheduling\TeachingPeriodCatalog;

    $assignedTeacherPayload = $lichHoc->moduleHoc->phanCongGiangViens
        ->where('trang_thai', 'da_nhan')
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
    $currentTeacherAssigned = $assignedTeacherPayload->contains(fn ($teacherInfo) => (int) $teacherInfo['id'] === (int) $lichHoc->giang_vien_id);
    $periodDefinitions = TeachingPeriodCatalog::periods();
    $sessionOptions = TeachingPeriodCatalog::sessions();
    $selectedPeriods = old('selected_tiets', ($lichHoc->tiet_bat_dau && $lichHoc->tiet_ket_thuc) ? range($lichHoc->tiet_bat_dau, $lichHoc->tiet_ket_thuc) : []);
@endphp
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chu</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khoa hoc</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $lichHoc->khoa_hoc_id) }}">{{ $lichHoc->khoaHoc->ma_khoa_hoc }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.lich-hoc.index', $lichHoc->khoa_hoc_id) }}">Lich hoc</a></li>
            <li class="breadcrumb-item active">Sua buoi {{ $lichHoc->buoi_so }}</li>
        </ol>
    </nav>

    @include('components.alert')

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="vip-card shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
                        <i class="fas fa-edit me-2 text-warning"></i> Cap nhat thong tin buoi hoc
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    <form action="{{ route('admin.khoa-hoc.lich-hoc.update', [$lichHoc->khoa_hoc_id, $lichHoc->id]) }}" method="POST" id="edit-schedule-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tiet_bat_dau" id="edit-tiet-bat-dau" value="{{ old('tiet_bat_dau', $lichHoc->tiet_bat_dau) }}">
                        <input type="hidden" name="tiet_ket_thuc" id="edit-tiet-ket-thuc" value="{{ old('tiet_ket_thuc', $lichHoc->tiet_ket_thuc) }}">
                        <input type="hidden" name="buoi_hoc" id="edit-buoi-hoc" value="{{ old('buoi_hoc', $lichHoc->buoi_hoc) }}">

                        <div class="mb-4">
                            <label class="smaller text-muted text-uppercase fw-bold mb-1">Module</label>
                            <div class="fw-bold fs-5 text-dark border-bottom pb-2">{{ $lichHoc->moduleHoc->ten_module }} (Buoi {{ $lichHoc->buoi_so }})</div>
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

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ngay hoc *</label>
                                <input type="date" name="ngay_hoc" id="edit-date" class="form-control vip-form-control @error('ngay_hoc') is-invalid @enderror" value="{{ old('ngay_hoc', $lichHoc->ngay_hoc->format('Y-m-d')) }}" required>
                                @error('ngay_hoc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Khung tiet</label>
                                <input type="text" id="edit-period-preview" class="form-control vip-form-control" value="{{ $lichHoc->schedule_range_label }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Khung gio map tu dong</label>
                                <input type="text" id="edit-time-preview" class="form-control vip-form-control" value="{{ \Carbon\Carbon::parse($lichHoc->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lichHoc->gio_ket_thuc)->format('H:i') }}" readonly>
                                <input type="hidden" name="gio_bat_dau" id="edit-start-time" value="{{ old('gio_bat_dau', \Carbon\Carbon::parse($lichHoc->gio_bat_dau)->format('H:i')) }}">
                                <input type="hidden" name="gio_ket_thuc" id="edit-end-time" value="{{ old('gio_ket_thuc', \Carbon\Carbon::parse($lichHoc->gio_ket_thuc)->format('H:i')) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold d-block">Chon nhanh theo buoi</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($sessionOptions as $session => $definition)
                                        <button type="button" class="btn btn-outline-warning edit-session-btn" data-session="{{ $session }}">
                                            {{ $definition['label'] }} (Tiet {{ $definition['start'] }}-{{ $definition['end'] }})
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold d-block">Hoac tick tung tiet</label>
                                <div class="d-flex flex-wrap gap-2" id="edit-period-grid">
                                    @foreach($periodDefinitions as $period => $definition)
                                        <div class="form-check p-0 m-0">
                                            <input class="form-check-input d-none" type="checkbox" name="selected_tiets[]" value="{{ $period }}" id="edit_period_{{ $period }}" {{ in_array($period, $selectedPeriods, true) ? 'checked' : '' }}>
                                            <label class="period-box" for="edit_period_{{ $period }}">
                                                <span class="fw-bold d-block">Tiet {{ $period }}</span>
                                                <span class="small">{{ $definition['start'] }} - {{ $definition['end'] }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Trang thai buoi hoc *</label>
                                <select name="trang_thai" id="edit-status" class="form-select vip-form-control" required>
                                    <option value="cho" {{ old('trang_thai', $lichHoc->trang_thai) == 'cho' ? 'selected' : '' }}>Cho</option>
                                    <option value="dang_hoc" {{ old('trang_thai', $lichHoc->trang_thai) == 'dang_hoc' ? 'selected' : '' }}>Dang hoc</option>
                                    <option value="hoan_thanh" {{ old('trang_thai', $lichHoc->trang_thai) == 'hoan_thanh' ? 'selected' : '' }}>Hoan thanh</option>
                                    <option value="huy" {{ old('trang_thai', $lichHoc->trang_thai) == 'huy' ? 'selected' : '' }}>Da huy</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hinh thuc *</label>
                                <select name="hinh_thuc" class="form-select vip-form-control" required id="edit-hinh-thuc">
                                    <option value="truc_tiep" {{ old('hinh_thuc', $lichHoc->hinh_thuc) == 'truc_tiep' ? 'selected' : '' }}>Truc tiep</option>
                                    <option value="online" {{ old('hinh_thuc', $lichHoc->hinh_thuc) == 'online' ? 'selected' : '' }}>Online</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phong hoc / Link hop</label>
                                <input type="text" name="phong_hoc" class="form-control vip-form-control shadow-sm" value="{{ old('phong_hoc', $lichHoc->hinh_thuc === 'online' ? $lichHoc->link_online : $lichHoc->phong_hoc) }}" placeholder="Phong hoc hoac link hop">
                                <div class="mt-2" id="box-apply-all" style="display: {{ $lichHoc->hinh_thuc === 'online' ? 'block' : 'none' }};">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="apply_to_all_online" value="1" id="applyAllOnline">
                                        <label class="form-check-label small text-primary fw-bold" for="applyAllOnline">
                                            Ap dung link nay cho tat ca buoi hoc online cua khoa hoc nay
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Giang vien day buoi nay *</label>
                                <select name="giang_vien_id" id="edit-teacher-id" class="form-select vip-form-control @error('giang_vien_id') is-invalid @enderror">
                                    <option value="">-- Chon giang vien --</option>
                                    @foreach($teacherOptions as $gv)
                                        <option value="{{ $gv->id }}" {{ old('giang_vien_id', $lichHoc->giang_vien_id) == $gv->id ? 'selected' : '' }}>
                                            {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'N/A' }}) - {{ $gv->donXinNghis->where('trang_thai', 'cho_duyet')->count() }} don cho duyet
                                        </option>
                                    @endforeach
                                </select>
                                @error('giang_vien_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @if($lichHoc->giangVien && !$currentTeacherAssigned)
                                    <div class="alert alert-warning border-0 mt-3 mb-0 small">
                                        Buoi hoc nay dang gan voi giang vien khong con o trang thai da nhan cho module.
                                        Hay chon lai giang vien hop le truoc khi luu.
                                    </div>
                                @else
                                    <div class="small text-muted mt-2">Dropdown nay uu tien cac giang vien da nhan module. Panel ben duoi se canh bao neu assignment, khung day chuan, don nghi hoac xung dot khong hop le.</div>
                                @endif
                            </div>

                            <div class="col-12">
                                <div id="edit-planning-panel" class="planning-panel border rounded-3 p-3 bg-light" data-endpoint="{{ route('admin.khoa-hoc.lich-hoc.teacher-context', $lichHoc->khoa_hoc_id) }}">
                                    <div class="small text-muted mb-0">Dang tai planning context...</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Ghi chu cho buoi nay</label>
                                <textarea name="ghi_chu" class="form-control vip-form-control" rows="3">{{ old('ghi_chu', $lichHoc->ghi_chu) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-warning px-5 fw-bold shadow-sm text-white border-0">
                                <i class="fas fa-save me-2"></i> CAP NHAT
                            </button>
                            <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $lichHoc->khoa_hoc_id) }}" class="btn btn-outline-secondary px-4 fw-bold">
                                HUY BO
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('edit-planning-panel');
    const initialContext = @json($planningContext);
    const periodDefinitions = @json($periodDefinitions);
    const sessionDefinitions = @json($sessionOptions);

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getSelectedPeriods() {
        return Array.from(document.querySelectorAll('#edit-period-grid input[name="selected_tiets[]"]:checked'))
            .map(input => Number(input.value))
            .sort((a, b) => a - b);
    }

    function syncEditPeriodSummary() {
        const periods = getSelectedPeriods();
        const periodPreview = document.getElementById('edit-period-preview');
        const timePreview = document.getElementById('edit-time-preview');
        const periodStart = document.getElementById('edit-tiet-bat-dau');
        const periodEnd = document.getElementById('edit-tiet-ket-thuc');
        const sessionInput = document.getElementById('edit-buoi-hoc');
        const hiddenStartTime = document.getElementById('edit-start-time');
        const hiddenEndTime = document.getElementById('edit-end-time');

        if (periods.length === 0) {
            periodPreview.value = '';
            timePreview.value = '';
            periodStart.value = '';
            periodEnd.value = '';
            sessionInput.value = '';
            hiddenStartTime.value = '';
            hiddenEndTime.value = '';
            return;
        }

        const start = periods[0];
        const end = periods[periods.length - 1];
        periodStart.value = start;
        periodEnd.value = end;
        hiddenStartTime.value = periodDefinitions[start].start;
        hiddenEndTime.value = periodDefinitions[end].end;

        const matchedSession = Object.entries(sessionDefinitions).find(([session, definition]) => {
            return start >= definition.start && end <= definition.end;
        });

        sessionInput.value = matchedSession ? matchedSession[0] : '';
        const sessionLabel = matchedSession ? matchedSession[1].label : null;
        periodPreview.value = sessionLabel ? `${sessionLabel} (Tiet ${start} - ${end})` : `Tiet ${start} - ${end}`;
        timePreview.value = `${hiddenStartTime.value} - ${hiddenEndTime.value}`;
    }

    function selectSession(sessionKey) {
        const definition = sessionDefinitions[sessionKey];
        if (!definition) return;

        document.querySelectorAll('#edit-period-grid input[name="selected_tiets[]"]').forEach(input => {
            const period = Number(input.value);
            input.checked = period >= definition.start && period <= definition.end;
        });

        syncEditPeriodSummary();
        refreshPlanning();
    }

    function renderPlaceholder(message) {
        panel.innerHTML = `<div class="small text-muted mb-0">${escapeHtml(message)}</div>`;
    }

    function renderContext(context) {
        const assignment = context.assignment || {};
        const availability = context.availability || {};
        const conflicts = context.conflicts || {};
        const suggestions = context.suggestions || [];
        const matched = availability.matched_slots || [];

        const assignmentColor = assignment.ok === true ? 'success' : (assignment.ok === false ? 'danger' : 'secondary');
        const availabilityColor = availability.ok === true ? 'success' : (availability.ok === false ? 'danger' : 'secondary');
        const conflictColor = conflicts.ok === true ? 'success' : (conflicts.ok === false ? 'danger' : 'secondary');

        panel.innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <div class="fw-bold text-dark">Planning context</div>
                    <div class="small text-muted">Teacher: ${escapeHtml(context.teacher_name || 'Chua chon')}</div>
                </div>
                <span class="badge rounded-pill bg-${context.can_schedule ? 'success' : 'warning'}">${context.can_schedule ? 'Co the luu lich' : 'Can xu ly truoc khi luu'}</span>
            </div>
            <div class="row g-3">
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
                        ${matched.length ? `<div class="mt-2">${matched.map(item => `<span class="badge bg-success-subtle text-success border border-success-subtle me-1 mb-1">${escapeHtml(item.label)} - ${escapeHtml(item.schedule || item.time)}</span>`).join('')}</div>` : ''}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-2">Xung dot</div>
                        <span class="badge bg-${conflictColor} mb-2">${conflicts.ok === true ? 'Khong trung lich' : (conflicts.ok === false ? 'Dang trung lich' : 'Cho kiem tra')}</span>
                        <div class="small text-muted">${escapeHtml(conflicts.message || 'Chua co du lieu')}</div>
                    </div>
                </div>
            </div>
            <div class="border rounded-3 p-3 bg-white mt-3">
                <div class="small text-uppercase fw-bold text-muted mb-2">Goi y slot</div>
                ${suggestions.length ? `<div class="d-flex flex-wrap gap-2">${suggestions.map(item => `<button type="button" class="btn btn-sm btn-outline-primary edit-suggestion-btn" data-date="${item.date}" data-start="${item.start_time}" data-end="${item.end_time}" data-period-start="${item.period_start || ''}" data-period-end="${item.period_end || ''}">${escapeHtml(item.date_label)} - ${escapeHtml(item.period_label || (item.start_time + ' - ' + item.end_time))}</button>`).join('')}</div>` : '<div class="small text-muted">Chua tim thay slot goi y trong 30 ngay toi.</div>'}
            </div>
        `;
    }

    async function refreshPlanning() {
        const payload = {
            module_hoc_id: '{{ $lichHoc->module_hoc_id }}',
            ngay_hoc: document.getElementById('edit-date').value,
            gio_bat_dau: document.getElementById('edit-start-time').value,
            gio_ket_thuc: document.getElementById('edit-end-time').value,
            tiet_bat_dau: document.getElementById('edit-tiet-bat-dau').value,
            tiet_ket_thuc: document.getElementById('edit-tiet-ket-thuc').value,
            buoi_hoc: document.getElementById('edit-buoi-hoc').value,
            giang_vien_id: document.getElementById('edit-teacher-id').value,
            ignore_lich_hoc_id: '{{ $lichHoc->id }}',
        };

        if (document.getElementById('edit-status').value === 'huy') {
            renderPlaceholder('Buoi hoc dang o trang thai huy, he thong khong bat buoc kiem tra assignment va khung day chuan va don nghi.');
            return;
        }

        if (!payload.giang_vien_id) {
            renderPlaceholder('Chon giang vien de kiem tra planning context.');
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

            renderContext(await response.json());
        } catch (error) {
            renderPlaceholder('Khong the tai planning context luc nay. Vui long thu lai.');
        }
    }

    document.getElementById('edit-hinh-thuc')?.addEventListener('change', function () {
        document.getElementById('box-apply-all').style.display = this.value === 'online' ? 'block' : 'none';
    });

    document.querySelectorAll('#edit-period-grid input[name="selected_tiets[]"]').forEach(input => {
        input.addEventListener('change', function () {
            syncEditPeriodSummary();
            refreshPlanning();
        });
    });

    document.querySelectorAll('.edit-session-btn').forEach(button => {
        button.addEventListener('click', function () {
            selectSession(this.dataset.session);
        });
    });

    ['edit-date', 'edit-teacher-id', 'edit-status'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', refreshPlanning);
        }
    });

    panel.addEventListener('click', function (event) {
        const button = event.target.closest('.edit-suggestion-btn');
        if (!button) return;

        document.getElementById('edit-date').value = button.dataset.date;
        if (button.dataset.periodStart && button.dataset.periodEnd) {
            const start = Number(button.dataset.periodStart);
            const end = Number(button.dataset.periodEnd);

            document.querySelectorAll('#edit-period-grid input[name="selected_tiets[]"]').forEach(input => {
                const period = Number(input.value);
                input.checked = period >= start && period <= end;
            });

            syncEditPeriodSummary();
        } else {
            document.getElementById('edit-start-time').value = button.dataset.start;
            document.getElementById('edit-end-time').value = button.dataset.end;
        }
        refreshPlanning();
    });

    syncEditPeriodSummary();

    if (initialContext) {
        renderContext(initialContext);
    } else {
        refreshPlanning();
    }
});
</script>
@endpush

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.1); border-color: #ffc107; }
    .planning-panel { min-height: 140px; }
    .period-box {
        display: inline-flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 105px;
        min-height: 72px;
        padding: 10px;
        border-radius: 14px;
        border: 2px solid #e9ecef;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }
    .period-box:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.06); }
    input.form-check-input:checked + .period-box { border-color: #ffc107; background: rgba(255, 193, 7, 0.1); color: #946200; }
</style>
@endsection




