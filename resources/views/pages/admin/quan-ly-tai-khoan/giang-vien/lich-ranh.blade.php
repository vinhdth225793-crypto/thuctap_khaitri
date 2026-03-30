@extends('layouts.app')

@section('title', 'Lich giang giang vien')

@php
    use App\Models\GiangVienLichRanh;
@endphp

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.giang-vien.index') }}">Giang vien</a></li>
            <li class="breadcrumb-item active">{{ $teacher->nguoiDung->ho_ten }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Teacher Availability</div>
            <h4 class="fw-bold mb-1">Lich giang cua {{ $teacher->nguoiDung->ho_ten }}</h4>
            <div class="text-muted">
                {{ $teacher->chuyen_nganh ?: 'Chua cap nhat chuyen nganh' }}
                <span class="mx-2">|</span>
                {{ $teacher->nguoiDung->email }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.giang-vien.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Danh sach giang vien
            </a>
            <a href="{{ route('admin.tai-khoan.edit', $teacher->nguoi_dung_id) }}" class="btn btn-outline-warning fw-bold">
                <i class="fas fa-user-edit me-1"></i> Sua ho so
            </a>
        </div>
    </div>

    @include('components.alert')

    @include('components.teacher-schedule-board', [
        'scheduleView' => $scheduleView,
    ])

    @include('components.teacher-availability-overview', [
        'availabilityOverview' => $availabilityOverview,
    ])

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Tong khung gio</div>
                    <div class="display-6 fw-bold text-dark">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Dang hoat dong</div>
                    <div class="display-6 fw-bold text-success">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Theo tuan</div>
                    <div class="display-6 fw-bold text-primary">{{ $stats['weekly'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Theo ngay</div>
                    <div class="display-6 fw-bold text-warning">{{ $stats['specific'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Module da nhan</div>
                    <div class="display-6 fw-bold text-info">{{ $stats['assigned_modules'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Buoi sap toi</div>
                    <div class="display-6 fw-bold text-secondary">{{ $stats['upcoming_schedules'] }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($stats['active'] === 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="fw-bold mb-1">Giang vien nay chua co khung lich giang dang hoat dong.</div>
            <div class="small mb-0">Neu admin sap lich cho cac module da nhan, panel planning se canh bao va tu choi cac khung day khong hop le.</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.giang-vien.lich-ranh.show', $teacher->id) }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Loai lich</label>
                            <select name="loai_lich_ranh" class="form-select">
                                <option value="">Tat ca</option>
                                <option value="{{ GiangVienLichRanh::LOAI_THEO_TUAN }}" @selected(($filters['loai_lich_ranh'] ?? null) === GiangVienLichRanh::LOAI_THEO_TUAN)>Theo tuan</option>
                                <option value="{{ GiangVienLichRanh::LOAI_THEO_NGAY }}" @selected(($filters['loai_lich_ranh'] ?? null) === GiangVienLichRanh::LOAI_THEO_NGAY)>Theo ngay</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Trang thai</label>
                            <select name="trang_thai" class="form-select">
                                <option value="">Tat ca</option>
                                <option value="{{ GiangVienLichRanh::TRANG_THAI_HOAT_DONG }}" @selected(($filters['trang_thai'] ?? null) === GiangVienLichRanh::TRANG_THAI_HOAT_DONG)>Hoat dong</option>
                                <option value="{{ GiangVienLichRanh::TRANG_THAI_TAM_AN }}" @selected(($filters['trang_thai'] ?? null) === GiangVienLichRanh::TRANG_THAI_TAM_AN)>Tam an</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Thu trong tuan</label>
                            <select name="thu_trong_tuan" class="form-select">
                                <option value="">Tat ca</option>
                                @foreach(\App\Models\LichHoc::$thuLabels as $value => $label)
                                    <option value="{{ $value }}" @selected((string) ($filters['thu_trong_tuan'] ?? '') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <input type="hidden" name="week_start" value="{{ request('week_start', $scheduleView['week_start'] ?? now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString()) }}">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fas fa-filter me-1"></i> Loc lich giang
                            </button>
                            <a href="{{ route('admin.giang-vien.lich-ranh.show', $teacher->id) }}" class="btn btn-light border">
                                Dat lai
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Danh sach lich giang dang ky</h5>
                    <div class="text-muted small">Admin chi xem va doi chieu. Giang vien se tu quan ly tao, sua, tam an hoac xoa.</div>
                </div>
                <div class="card-body p-0">
                    @if($availabilities->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Khung lich</th>
                                        <th>Loai</th>
                                        <th>Tiet/Buoi</th>
                                        <th>Thoi gian</th>
                                        <th>Trang thai</th>
                                        <th>Ghi chu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availabilities as $availability)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark">{{ $availability->display_date_or_day }}</div>
                                                <div class="small text-muted">Cap nhat {{ $availability->updated_at?->format('d/m/Y H:i') }}</div>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-{{ $availability->loai_lich_ranh === GiangVienLichRanh::LOAI_THEO_TUAN ? 'primary' : 'warning text-dark' }}">
                                                    {{ $availability->loai_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $availability->schedule_range_label }}</div>
                                                <div class="small text-muted">{{ $availability->tiet_range_label }}</div>
                                            </td>
                                            <td><span class="fw-bold">{{ $availability->time_range }}</span></td>
                                            <td>
                                                <span class="badge rounded-pill bg-{{ $availability->trang_thai === GiangVienLichRanh::TRANG_THAI_HOAT_DONG ? 'success' : 'secondary' }}">
                                                    {{ $availability->trang_thai_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small text-muted availability-note">{{ $availability->ghi_chu ?: '-' }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-top">
                            {{ $availabilities->links() }}
                        </div>
                    @else
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-calendar-times fa-3x opacity-25 mb-3"></i>
                            <div class="fw-bold text-dark mb-2">Khong co khung gio nao phu hop bo loc hien tai.</div>
                            <div class="small mb-0">Ban co the xem tat ca de kiem tra lai lich giang cua giang vien nay.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Buoi hoc sap toi</h5>
                    <div class="text-muted small">Dung de doi chieu nhanh khi cap nhat lich giang hoac lap lich moi.</div>
                </div>
                <div class="card-body pt-0">
                    @forelse($upcomingSchedules as $schedule)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold text-dark">{{ $schedule->moduleHoc->ten_module }}</div>
                                    <div class="small text-muted">{{ $schedule->moduleHoc->khoaHoc->ten_khoa_hoc }}</div>
                                </div>
                                <span class="badge bg-light text-dark border">Buoi {{ $schedule->buoi_so }}</span>
                            </div>
                            <div class="small text-muted mb-2">
                                {{ $schedule->ngay_hoc->format('d/m/Y') }} | {{ \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->gio_ket_thuc)->format('H:i') }}
                            </div>
                            <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $schedule->khoa_hoc_id) }}" class="btn btn-sm btn-outline-primary">
                                Mo lich hoc khoa
                            </a>
                        </div>
                    @empty
                        <div class="text-muted small">Chua co buoi hoc tuong lai nao duoc gan cho giang vien nay.</div>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Module da nhan</h5>
                    <div class="text-muted small">Day la cac module admin co the mo bo sap lich va planning context.</div>
                </div>
                <div class="card-body pt-0">
                    @forelse($acceptedAssignments as $assignment)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-bold text-dark">{{ $assignment->moduleHoc->ten_module ?? 'N/A' }}</div>
                            <div class="small text-muted mb-2">{{ $assignment->moduleHoc->khoaHoc->ten_khoa_hoc ?? 'Khong xac dinh' }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.khoa-hoc.show', $assignment->khoa_hoc_id) }}" class="btn btn-sm btn-outline-secondary">
                                    Xem khoa hoc
                                </a>
                                <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $assignment->khoa_hoc_id) }}" class="btn btn-sm btn-outline-success">
                                    Mo planner
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Giang vien nay chua co module nao o trang thai da nhan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .availability-note {
        max-width: 260px;
        white-space: normal;
    }
</style>
@endsection
