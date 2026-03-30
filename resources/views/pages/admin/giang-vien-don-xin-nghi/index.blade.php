@extends('layouts.app')

@section('title', 'Don xin nghi giang vien')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Teacher Leave Requests</div>
            <h4 class="fw-bold mb-1">Danh sach don xin nghi giang vien</h4>
            <div class="text-muted">Admin duyet, tu choi va theo doi cac buoi hoc can xu ly tiep sau khi duyet.</div>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Giang vien</label>
                    <select name="giang_vien_id" class="form-select">
                        <option value="">Tat ca</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) ($filters['giang_vien_id'] ?? '') === (string) $teacher->id)>{{ $teacher->nguoiDung?->ho_ten }}</option>
                        @endforeach
                    </select>
                </div>
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
                    <a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="btn btn-light border flex-fill">Dat lai</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Giang vien</th>
                            <th>Ngay</th>
                            <th>Khung nghi</th>
                            <th>Khoa hoc / Module</th>
                            <th>Trang thai</th>
                            <th class="pe-4 text-end">Chi tiet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $item->giangVien?->nguoiDung?->ho_ten }}</div>
                                    <div class="small text-muted">{{ $item->giangVien?->chuyen_nganh ?: 'Chua cap nhat' }}</div>
                                </td>
                                <td class="fw-bold">{{ $item->ngay_xin_nghi?->format('d/m/Y') }}</td>
                                <td>
                                    <div>{{ $item->schedule_range_label }}</div>
                                    <div class="small text-muted">{{ $item->tiet_range_label }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->khoaHoc?->ma_khoa_hoc ?: ($item->lichHoc?->khoaHoc?->ma_khoa_hoc ?? 'Khong gan buoi hoc') }}</div>
                                    <div class="small text-muted">{{ $item->moduleHoc?->ten_module ?: ($item->lichHoc?->moduleHoc?->ten_module ?? '-') }}</div>
                                </td>
                                <td><span class="badge bg-{{ $item->trang_thai_color }}">{{ $item->trang_thai_label }}</span></td>
                                <td class="pe-4 text-end"><a href="{{ route('admin.giang-vien-don-xin-nghi.show', $item->id) }}" class="btn btn-sm btn-outline-primary">Mo chi tiet</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">Chua co don xin nghi nao.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-top">{{ $leaveRequests->links() }}</div>
        </div>
    </div>
</div>
@endsection
