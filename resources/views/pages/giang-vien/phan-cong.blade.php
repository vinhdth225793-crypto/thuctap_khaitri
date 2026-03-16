@extends('layouts.app')

@section('title', 'Lộ trình giảng dạy của tôi')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lộ trình giảng dạy</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                Lộ trình giảng dạy của tôi
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            @if($phanCongChoXacNhan > 0)
                <div class="badge bg-warning text-dark px-3 py-2 shadow-sm">
                    <i class="fas fa-bell me-1 animate-bell"></i> Bạn có {{ $phanCongChoXacNhan }} phân công mới cần xác nhận
                </div>
            @endif
        </div>
    </div>

    @include('components.alert')

    @if($khoaHocs->count() > 0)
        @foreach($khoaHocs as $khoaHoc)
            <div class="vip-card mb-4 border-0 shadow-sm overflow-hidden">
                <div class="vip-card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="rounded-pill bg-primary-soft text-primary px-3 py-1 fw-bold smaller me-3 border border-primary">
                            {{ $khoaHoc->ma_khoa_hoc }}
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                            <div class="smaller text-muted mt-1">
                                <i class="fas fa-layer-group me-1"></i> Ngành: {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-calendar-day me-1"></i> Khai giảng: {{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? 'Chưa định ngày' }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}-soft text-{{ $khoaHoc->badge_trang_thai }} border border-{{ $khoaHoc->badge_trang_thai }} shadow-xs px-3">
                            {{ $khoaHoc->label_trang_thai_van_hanh }}
                        </span>
                    </div>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4" width="60">STT</th>
                                    <th>Tên bài dạy (Module)</th>
                                    <th class="text-center">Số buổi</th>
                                    <th class="text-center">Trạng thái xác nhận</th>
                                    <th class="pe-4 text-center" width="180">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($khoaHoc->moduleHocs as $index => $module)
                                    @php $pc = $module->phanCongGiangViens->first(); @endphp
                                    <tr>
                                        <td class="ps-4 text-muted small fw-bold">#{{ $module->thu_tu_module }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                            <div class="smaller text-muted italic">{{ Str::limit($module->mo_ta, 80) }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $module->so_buoi ?? 0 }} buổi</span>
                                        </td>
                                        <td class="text-center">
                                            @if($pc->trang_thai === 'cho_xac_nhan')
                                                <span class="badge bg-warning-soft text-warning border border-warning px-3">Đang chờ bạn</span>
                                            @elseif($pc->trang_thai === 'da_nhan')
                                                <span class="badge bg-success-soft text-success border border-success px-3">Đã xác nhận</span>
                                            @else
                                                <span class="badge bg-danger-soft text-danger border border-danger px-3">Từ chối</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-center">
                                            @if($pc->trang_thai === 'cho_xac_nhan')
                                                <div class="d-flex justify-content-center gap-1">
                                                    <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $pc->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success fw-bold px-3 shadow-xs">Đồng ý</button>
                                                    </form>
                                                    <form action="{{ route('giang-vien.khoa-hoc.tu-choi', $pc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn từ chối bài dạy này?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold shadow-xs">Từ chối</button>
                                                    </form>
                                                </div>
                                            @else
                                                <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-sm btn-outline-primary fw-bold px-3 shadow-xs">
                                                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="vip-card border-0 shadow-sm">
            <div class="vip-card-body p-5 text-center text-muted">
                <i class="fas fa-book-reader fa-4x mb-3 opacity-25"></i>
                <h5 class="fw-bold">Bạn chưa có phân công nào</h5>
                <p class="mb-0">Các bài dạy được Admin chỉ định sẽ xuất hiện tại đây.</p>
            </div>
        </div>
    @endif
</div>

<style>
    .smaller { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    @keyframes bell-shake {
        0% { transform: rotate(0); }
        15% { transform: rotate(10deg); }
        30% { transform: rotate(-10deg); }
        45% { transform: rotate(5deg); }
        60% { transform: rotate(-5deg); }
        100% { transform: rotate(0); }
    }
    .animate-bell {
        display: inline-block;
        animation: bell-shake 2s infinite;
    }
</style>
@endsection
