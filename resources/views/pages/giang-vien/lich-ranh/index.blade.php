@extends('layouts.app')

@section('title', 'Lich giang cua toi')

@php
    use App\Models\GiangVienLichRanh;
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Teacher Availability</div>
            <h4 class="fw-bold mb-1">Lich giang cua toi</h4>
            <div class="text-muted">Dang ky ngay day, buoi hoc va khung tiet de admin sap lich day theo thoi khoa bieu.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="{{ route('giang-vien.lich-ranh.create') }}" class="btn btn-primary fw-bold">
                <i class="fas fa-plus me-1"></i> Dang ky lich giang
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Tong khung gio</div>
                    <div class="display-6 fw-bold text-dark">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Dang hoat dong</div>
                    <div class="display-6 fw-bold text-success">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Lap lai hang tuan</div>
                    <div class="display-6 fw-bold text-primary">{{ $stats['weekly'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Theo ngay cu the</div>
                    <div class="display-6 fw-bold text-warning">{{ $stats['specific'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('giang-vien.lich-ranh.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Loai lich</label>
                    <select name="loai_lich_ranh" class="form-select">
                        <option value="">Tat ca</option>
                        <option value="{{ GiangVienLichRanh::LOAI_THEO_TUAN }}" @selected(($filters['loai_lich_ranh'] ?? null) === GiangVienLichRanh::LOAI_THEO_TUAN)>Theo tuan</option>
                        <option value="{{ GiangVienLichRanh::LOAI_THEO_NGAY }}" @selected(($filters['loai_lich_ranh'] ?? null) === GiangVienLichRanh::LOAI_THEO_NGAY)>Theo ngay</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Trang thai</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tat ca</option>
                        <option value="{{ GiangVienLichRanh::TRANG_THAI_HOAT_DONG }}" @selected(($filters['trang_thai'] ?? null) === GiangVienLichRanh::TRANG_THAI_HOAT_DONG)>Hoat dong</option>
                        <option value="{{ GiangVienLichRanh::TRANG_THAI_TAM_AN }}" @selected(($filters['trang_thai'] ?? null) === GiangVienLichRanh::TRANG_THAI_TAM_AN)>Tam an</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Thu trong tuan</label>
                    <select name="thu_trong_tuan" class="form-select">
                        <option value="">Tat ca</option>
                        @foreach(\App\Models\LichHoc::$thuLabels as $value => $label)
                            <option value="{{ $value }}" @selected((string) ($filters['thu_trong_tuan'] ?? '') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <input type="hidden" name="week_start" value="{{ request('week_start', $scheduleView['week_start'] ?? now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString()) }}">
                    <button type="submit" class="btn btn-primary flex-fill fw-bold">
                        <i class="fas fa-filter me-1"></i> Loc
                    </button>
                    <a href="{{ route('giang-vien.lich-ranh.index') }}" class="btn btn-light border flex-fill">
                        Dat lai
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($stats['active'] === 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="fw-bold mb-1">Ban chua co khung lich giang dang hoat dong.</div>
            <div class="small mb-0">Admin van co the thu sap lich, nhung he thong se canh bao va tu choi neu khung day khong nam trong lich giang ban da dang ky.</div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-1 fw-bold">Danh sach khung gio</h5>
            <div class="text-muted small">Quan ly lich lap lai hang tuan va lich theo ngay cu the.</div>
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
                                <th class="text-end pe-4">Thao tac</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($availabilities as $availability)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $availability->display_date_or_day }}</div>
                                        <div class="small text-muted">
                                            Cap nhat {{ $availability->updated_at?->format('d/m/Y H:i') }}
                                        </div>
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
                                    <td>
                                        <span class="fw-bold">{{ $availability->time_range }}</span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $availability->trang_thai === GiangVienLichRanh::TRANG_THAI_HOAT_DONG ? 'success' : 'secondary' }}">
                                            {{ $availability->trang_thai_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-muted availability-note">{{ $availability->ghi_chu ?: '-' }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('giang-vien.lich-ranh.edit', $availability->id) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('giang-vien.lich-ranh.toggle-status', $availability->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $availability->trang_thai === GiangVienLichRanh::TRANG_THAI_HOAT_DONG ? 'secondary' : 'success' }}">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('giang-vien.lich-ranh.destroy', $availability->id) }}" method="POST" onsubmit="return confirm('Xoa lich giang dang ky nay?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
                    <div class="fw-bold text-dark mb-2">Chua co lich giang nao phu hop bo loc hien tai.</div>
                    <div class="small mb-3">Ban co the tao moi lich dang ky theo ngay hoac theo mau lap lai hang tuan.</div>
                    <a href="{{ route('giang-vien.lich-ranh.create') }}" class="btn btn-primary fw-bold">
                        <i class="fas fa-plus me-1"></i> Tao lich dang ky dau tien
                    </a>
                </div>
            @endif
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
