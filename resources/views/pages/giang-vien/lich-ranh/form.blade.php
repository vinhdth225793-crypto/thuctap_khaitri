@extends('layouts.app')

@section('title', $title)

@php
    use App\Models\GiangVienLichRanh;
    use App\Support\Scheduling\TeachingPeriodCatalog;

    $sessionOptions = TeachingPeriodCatalog::sessions();
    $selectedDates = collect(old('ngay_ap_dung', $availability->ngay_cu_the ? [$availability->ngay_cu_the->format('Y-m-d')] : []))
        ->filter()
        ->values()
        ->all();
    $selectedPeriods = old('selected_tiets');

    if ($selectedPeriods === null && $availability->tiet_bat_dau && $availability->tiet_ket_thuc) {
        $selectedPeriods = range($availability->tiet_bat_dau, $availability->tiet_ket_thuc);
    }

    $selectedPeriods = collect($selectedPeriods ?? [])->map(fn ($value) => (int) $value)->filter()->values()->all();
@endphp

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <div class="small text-muted text-uppercase fw-bold mb-1">Teacher Schedule Registration</div>
                    <h4 class="fw-bold mb-1">{{ $title }}</h4>
                    <div class="text-muted">Dang ky khung ngay day theo ca hoc hoac tung tiet de he thong sap lich theo thoi khoa bieu.</div>
                </div>
                <a href="{{ route('giang-vien.lich-ranh.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lai
                </a>
            </div>

            @include('components.alert')

            @if($impactedSchedules->isNotEmpty())
                <div class="alert alert-warning border-0 shadow-sm">
                    <div class="fw-bold mb-2">Canh bao anh huong lich day hien tai</div>
                    <div class="small mb-3">
                        Neu ban luu thay doi nay, cac buoi hoc ben duoi se khong con duoc bao phu boi khung lich dang ky nay.
                    </div>
                    <ul class="small mb-0 ps-3">
                        @foreach($impactedSchedules->take(6) as $schedule)
                            <li>
                                {{ $schedule->khoaHoc?->ma_khoa_hoc }} / {{ $schedule->moduleHoc?->ten_module }}
                                - {{ $schedule->ngay_hoc?->format('d/m/Y') }}
                                - {{ $schedule->schedule_range_label }}
                            </li>
                        @endforeach
                    </ul>
                    @if($impactedSchedules->count() > 6)
                        <div class="small mt-2 text-muted">Con {{ $impactedSchedules->count() - 6 }} buoi hoc nua se bi anh huong.</div>
                    @endif
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <form action="{{ $formAction }}" method="POST" class="row g-4" id="availability-form">
                        @csrf
                        @if($formMethod !== 'POST')
                            @method($formMethod)
                        @endif

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Loai dang ky *</label>
                            <select name="loai_lich_ranh" id="availability-type" class="form-select @error('loai_lich_ranh') is-invalid @enderror" required>
                                <option value="{{ GiangVienLichRanh::LOAI_THEO_NGAY }}" @selected(old('loai_lich_ranh', $availability->loai_lich_ranh) === GiangVienLichRanh::LOAI_THEO_NGAY)>Theo ngay cu the</option>
                                <option value="{{ GiangVienLichRanh::LOAI_THEO_TUAN }}" @selected(old('loai_lich_ranh', $availability->loai_lich_ranh) === GiangVienLichRanh::LOAI_THEO_TUAN)>Lap lai hang tuan</option>
                            </select>
                            @error('loai_lich_ranh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Trang thai *</label>
                            <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror" required>
                                <option value="{{ GiangVienLichRanh::TRANG_THAI_HOAT_DONG }}" @selected(old('trang_thai', $availability->trang_thai) === GiangVienLichRanh::TRANG_THAI_HOAT_DONG)>Hoat dong</option>
                                <option value="{{ GiangVienLichRanh::TRANG_THAI_TAM_AN }}" @selected(old('trang_thai', $availability->trang_thai) === GiangVienLichRanh::TRANG_THAI_TAM_AN)>Tam an</option>
                            </select>
                            @error('trang_thai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12" id="multi-date-group">
                            <label class="form-label small fw-bold">Ngay ap dung *</label>
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <input type="date" id="date-picker" class="form-control" min="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" id="btn-add-date" class="btn btn-outline-primary w-100 fw-bold">
                                            <i class="fas fa-plus me-1"></i> Them ngay
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted">Ban co the them nhieu ngay cho cung mot khung tiet.</div>
                                    </div>
                                </div>
                                <div id="selected-dates" class="d-flex flex-wrap gap-2 mt-3"></div>
                                <div id="selected-dates-inputs"></div>
                            </div>
                            @error('ngay_ap_dung')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            @error('ngay_ap_dung.*')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="single-date-group">
                            <label class="form-label small fw-bold">Ngay cu the *</label>
                            <input
                                type="date"
                                name="ngay_cu_the"
                                min="{{ now()->toDateString() }}"
                                value="{{ old('ngay_cu_the', $availability->ngay_cu_the?->format('Y-m-d')) }}"
                                class="form-control @error('ngay_cu_the') is-invalid @enderror"
                            >
                            @error('ngay_cu_the')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="weekday-group">
                            <label class="form-label small fw-bold">Thu trong tuan *</label>
                            <select name="thu_trong_tuan" class="form-select @error('thu_trong_tuan') is-invalid @enderror">
                                <option value="">Chon thu</option>
                                @foreach(\App\Models\LichHoc::$thuLabels as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old('thu_trong_tuan', $availability->thu_trong_tuan) === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('thu_trong_tuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Chon nhanh theo buoi</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($sessionOptions as $session => $definition)
                                    <button type="button" class="btn btn-outline-primary session-quick-btn" data-session="{{ $session }}">
                                        {{ $definition['label'] }} (Tiet {{ $definition['start'] }}-{{ $definition['end'] }})
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="buoi_hoc" id="buoi-hoc-input" value="{{ old('buoi_hoc', $availability->buoi_hoc ?: $availability->ca_day) }}">
                            @error('buoi_hoc')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Hoac tick tung tiet trong ngay</label>
                            <div class="d-flex flex-wrap gap-2" id="period-grid">
                                @foreach(TeachingPeriodCatalog::periods() as $period => $definition)
                                    <div class="form-check p-0 m-0">
                                        <input
                                            class="form-check-input d-none"
                                            type="checkbox"
                                            name="selected_tiets[]"
                                            value="{{ $period }}"
                                            id="period_{{ $period }}"
                                            {{ in_array($period, $selectedPeriods, true) ? 'checked' : '' }}
                                        >
                                        <label class="period-box" for="period_{{ $period }}">
                                            <span class="fw-bold d-block">Tiet {{ $period }}</span>
                                            <span class="small">{{ $definition['start'] }} - {{ $definition['end'] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('selected_tiets')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            @error('selected_tiets.*')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tiet bat dau</label>
                            <input type="number" name="tiet_bat_dau" id="period-start" class="form-control @error('tiet_bat_dau') is-invalid @enderror" min="1" max="12" value="{{ old('tiet_bat_dau', $availability->tiet_bat_dau) }}" readonly>
                            @error('tiet_bat_dau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tiet ket thuc</label>
                            <input type="number" name="tiet_ket_thuc" id="period-end" class="form-control @error('tiet_ket_thuc') is-invalid @enderror" min="1" max="12" value="{{ old('tiet_ket_thuc', $availability->tiet_ket_thuc) }}" readonly>
                            @error('tiet_ket_thuc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Khung gio map tu dong</label>
                            <input type="text" id="time-preview" class="form-control" value="{{ old('gio_bat_dau', substr((string) $availability->gio_bat_dau, 0, 5)) && old('gio_ket_thuc', substr((string) $availability->gio_ket_thuc, 0, 5)) ? old('gio_bat_dau', substr((string) $availability->gio_bat_dau, 0, 5)) . ' - ' . old('gio_ket_thuc', substr((string) $availability->gio_ket_thuc, 0, 5)) : '' }}" readonly>
                            <input type="hidden" name="gio_bat_dau" id="gio-bat-dau-hidden" value="{{ old('gio_bat_dau', substr((string) $availability->gio_bat_dau, 0, 5)) }}">
                            <input type="hidden" name="gio_ket_thuc" id="gio-ket-thuc-hidden" value="{{ old('gio_ket_thuc', substr((string) $availability->gio_ket_thuc, 0, 5)) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Ghi chu</label>
                            <textarea name="ghi_chu" rows="4" class="form-control @error('ghi_chu') is-invalid @enderror" placeholder="Vi du: Co the day online, uu tien buoi toi, co the day tang cuong...">{{ old('ghi_chu', $availability->ghi_chu) }}</textarea>
                            @error('ghi_chu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="rounded-3 bg-light border p-3 small text-muted">
                                He thong dang su dung chuan 12 tiet/ngay va map sang khung gio hoc de tiep tuc phuc vu cac flow hien co nhu cap nhat link hoc, dang tai nguyen, tao bai giang, tao bai kiem tra va diem danh.
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="fas fa-save me-1"></i> Luu lich dang ky
                            </button>
                            <a href="{{ route('giang-vien.lich-ranh.index') }}" class="btn btn-light border px-4">
                                Huy
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
    const periodDefinitions = @json(TeachingPeriodCatalog::periods());
    const sessionDefinitions = @json($sessionOptions);
    const selectedDates = @json($selectedDates);
    const typeSelect = document.getElementById('availability-type');
    const multiDateGroup = document.getElementById('multi-date-group');
    const singleDateGroup = document.getElementById('single-date-group');
    const weekdayGroup = document.getElementById('weekday-group');
    const selectedDatesBox = document.getElementById('selected-dates');
    const selectedDatesInputs = document.getElementById('selected-dates-inputs');
    const datePicker = document.getElementById('date-picker');
    const periodStart = document.getElementById('period-start');
    const periodEnd = document.getElementById('period-end');
    const timePreview = document.getElementById('time-preview');
    const hiddenStartTime = document.getElementById('gio-bat-dau-hidden');
    const hiddenEndTime = document.getElementById('gio-ket-thuc-hidden');
    const sessionInput = document.getElementById('buoi-hoc-input');

    let workingDates = [...selectedDates];

    function renderDates() {
        selectedDatesBox.innerHTML = '';
        selectedDatesInputs.innerHTML = '';

        workingDates.forEach((dateValue, index) => {
            const badge = document.createElement('button');
            badge.type = 'button';
            badge.className = 'btn btn-sm btn-outline-primary';
            badge.innerHTML = `${dateValue.split('-').reverse().join('/')} <i class="fas fa-times ms-1"></i>`;
            badge.addEventListener('click', function () {
                workingDates.splice(index, 1);
                renderDates();
            });
            selectedDatesBox.appendChild(badge);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ngay_ap_dung[]';
            input.value = dateValue;
            selectedDatesInputs.appendChild(input);
        });
    }

    function syncVisibility() {
        const type = typeSelect.value;
        const isWeekly = type === '{{ GiangVienLichRanh::LOAI_THEO_TUAN }}';
        const isCreate = '{{ $formMethod }}' === 'POST';

        weekdayGroup.style.display = isWeekly ? '' : 'none';
        multiDateGroup.style.display = !isWeekly && isCreate ? '' : 'none';
        singleDateGroup.style.display = !isWeekly && !isCreate ? '' : 'none';
    }

    function getSelectedPeriods() {
        return Array.from(document.querySelectorAll('input[name="selected_tiets[]"]:checked'))
            .map(input => Number(input.value))
            .sort((a, b) => a - b);
    }

    function syncPeriodSummary() {
        const periods = getSelectedPeriods();
        if (periods.length === 0) {
            periodStart.value = '';
            periodEnd.value = '';
            timePreview.value = '';
            hiddenStartTime.value = '';
            hiddenEndTime.value = '';
            sessionInput.value = '';
            return;
        }

        periodStart.value = periods[0];
        periodEnd.value = periods[periods.length - 1];
        hiddenStartTime.value = periodDefinitions[periods[0]].start;
        hiddenEndTime.value = periodDefinitions[periods[periods.length - 1]].end;
        timePreview.value = `${hiddenStartTime.value} - ${hiddenEndTime.value}`;

        const matchedSession = Object.entries(sessionDefinitions).find(([session, definition]) => {
            return periods[0] >= definition.start && periods[periods.length - 1] <= definition.end;
        });

        sessionInput.value = matchedSession ? matchedSession[0] : '';
    }

    function selectSession(sessionKey) {
        const definition = sessionDefinitions[sessionKey];
        if (!definition) return;

        document.querySelectorAll('input[name="selected_tiets[]"]').forEach(input => {
            const period = Number(input.value);
            input.checked = period >= definition.start && period <= definition.end;
        });

        sessionInput.value = sessionKey;
        syncPeriodSummary();
    }

    document.getElementById('btn-add-date')?.addEventListener('click', function () {
        const value = datePicker.value;
        if (!value || workingDates.includes(value)) {
            return;
        }

        workingDates.push(value);
        workingDates.sort();
        renderDates();
        datePicker.value = '';
    });

    document.querySelectorAll('.session-quick-btn').forEach(button => {
        button.addEventListener('click', function () {
            selectSession(this.dataset.session);
        });
    });

    document.querySelectorAll('input[name="selected_tiets[]"]').forEach(input => {
        input.addEventListener('change', syncPeriodSummary);
    });

    if (typeSelect) {
        syncVisibility();
        typeSelect.addEventListener('change', syncVisibility);
    }

    renderDates();
    syncPeriodSummary();
});
</script>
@endpush

<style>
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

    .period-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
    }

    input.form-check-input:checked + .period-box {
        border-color: #0d6efd;
        background: rgba(13, 110, 253, 0.08);
        color: #0a58ca;
    }
</style>
@endsection
