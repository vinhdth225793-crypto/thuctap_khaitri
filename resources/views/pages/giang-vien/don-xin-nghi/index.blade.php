@extends('layouts.app')

@section('title', 'Don xin nghi cua toi')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Leave Requests</div>
            <h4 class="fw-bold mb-1">Don xin nghi / phan hoi lich day</h4>
            <div class="text-muted">Gui don cho tung buoi hoc da duoc sap hoac xin off theo ngay, buoi, tiet.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.lich-giang.index') }}" class="btn btn-outline-secondary">Ve lich day</a>
            <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-primary fw-bold">Tao don moi</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('giang-vien.don-xin-nghi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Trang thai</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tat ca</option>
                        <option value="cho_duyet" @selected(($filters['trang_thai'] ?? null) === 'cho_duyet')>Cho duyet</option>
                        <option value="da_duyet" @selected(($filters['trang_thai'] ?? null) === 'da_duyet')>Da duyet</option>
                        <option value="tu_choi" @selected(($filters['trang_thai'] ?? null) === 'tu_choi')>Tu choi</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill fw-bold">Loc</button>
                    <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-light border flex-fill">Dat lai</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($leaveRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Ngay</th>
                                <th>Khung nghi</th>
                                <th>Khoa hoc / Module</th>
                                <th>Ly do</th>
                                <th>Trang thai</th>
                                <th class="pe-4">Phan hoi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequests as $item)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $item->ngay_xin_nghi?->format('d/m/Y') }}</td>
                                    <td>
                                        <div>{{ $item->schedule_range_label }}</div>
                                        <div class="small text-muted">{{ $item->tiet_range_label }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $item->khoaHoc?->ma_khoa_hoc ?: ($item->lichHoc?->khoaHoc?->ma_khoa_hoc ?? 'Khong gan buoi hoc') }}</div>
                                        <div class="small text-muted">{{ $item->moduleHoc?->ten_module ?: ($item->lichHoc?->moduleHoc?->ten_module ?? '-') }}</div>
                                    </td>
                                    <td class="small text-muted">{{ $item->ly_do }}</td>
                                    <td><span class="badge bg-{{ $item->trang_thai_color }}">{{ $item->trang_thai_label }}</span></td>
                                    <td class="pe-4 small text-muted">{{ $item->ghi_chu_phan_hoi ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-top">
                    {{ $leaveRequests->links() }}
                </div>
            @else
                <div class="p-5 text-center text-muted">Chua co don xin nghi nao phu hop bo loc hien tai.</div>
            @endif
        </div>
    </div>
</div>
@endsection
