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
                Khóa học & Bài dạy của tôi
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-3">
            {{-- VIEW MODE SWITCHER --}}
            <div class="btn-group shadow-sm bg-white p-1 rounded-pill border" role="group">
                <button type="button" class="btn btn-sm rounded-pill px-3 active-view" id="btn-list-view" title="Xem danh sách gọn">
                    <i class="fas fa-list me-1"></i> Gọn
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3" id="btn-detail-view" title="Xem tổng thể module">
                    <i class="fas fa-th-large me-1"></i> Tổng thể
                </button>
            </div>

            @if($phanCongChoXacNhan > 0)
                <div class="badge bg-warning text-dark px-3 py-2 shadow-sm rounded-pill">
                    <i class="fas fa-bell me-1 animate-bell"></i> {{ $phanCongChoXacNhan }} phân công mới
                </div>
            @endif
        </div>
    </div>

    @include('components.alert')

    @if($khoaHocs->count() > 0)
        <div class="row" id="view-container">
            @foreach($khoaHocs as $khoaHoc)
                <div class="col-12 course-card mb-4" data-mode="list">
                    <div class="vip-card border-0 shadow-sm overflow-hidden">
                        <div class="vip-card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="rounded-pill bg-primary-soft text-primary px-3 py-1 fw-bold smaller me-3 border border-primary">
                                    {{ $khoaHoc->ma_khoa_hoc }}
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                                    <div class="smaller text-muted mt-1">
                                        <i class="fas fa-layer-group me-1"></i> Ngành: {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}
                                        <span class="mx-2 d-none d-md-inline">|</span>
                                        <i class="fas fa-calendar-check me-1"></i> Khai giảng: {{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? 'Chưa định ngày' }}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}-soft text-{{ $khoaHoc->badge_trang_thai }} border border-{{ $khoaHoc->badge_trang_thai }} px-3 shadow-xs d-none d-md-inline-block">
                                    {{ $khoaHoc->label_trang_thai_van_hanh }}
                                </span>
                                {{-- Nút xem nhanh ở chế độ List --}}
                                <div class="list-actions">
                                    <a href="{{ route('giang-vien.khoa-hoc.show', $khoaHoc->moduleHocs->first()->phanCongGiangViens->first()->id) }}" class="btn btn-sm btn-primary fw-bold px-3 rounded-pill shadow-xs">
                                        Vào dạy <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- PHẦN CHI TIẾT MODULE (Ẩn mặc định ở chế độ Gọn) --}}
                        <div class="vip-card-body p-0 detail-section d-none">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light smaller text-muted text-uppercase">
                                        <tr>
                                            <th class="ps-4" width="60">STT</th>
                                            <th>Tên bài dạy (Module)</th>
                                            <th class="text-center">Số buổi</th>
                                            <th class="text-center">Trạng thái</th>
                                            <th class="pe-4 text-center" width="180">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($khoaHoc->moduleHocs as $module)
                                            @php 
                                                $pc = $module->phanCongGiangViens->first();
                                                $rowBg = match($pc->trang_thai) {
                                                    'cho_xac_nhan' => 'bg-warning-soft',
                                                    'da_nhan'      => 'bg-success-soft',
                                                    'tu_choi'      => 'bg-danger-soft',
                                                    default        => ''
                                                };
                                            @endphp
                                            <tr class="{{ $rowBg }}">
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
                                                        <span class="badge bg-warning-soft text-warning border border-warning px-3">Chờ xác nhận</span>
                                                    @elseif($pc->trang_thai === 'da_nhan')
                                                        <span class="badge bg-success-soft text-success border border-success px-3">Đã nhận</span>
                                                    @else
                                                        <span class="badge bg-danger-soft text-danger border border-danger px-3">Từ chối</span>
                                                    @endif
                                                </td>
                                                <td class="pe-4 text-center">
                                                    @if($pc->trang_thai === 'cho_xac_nhan')
                                                        <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $pc->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="hanh_dong" value="da_nhan">
                                                            <button type="submit" class="btn btn-xs btn-success fw-bold px-2">Nhận</button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-xs btn-outline-primary fw-bold px-2">Chi tiết</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="vip-card border-0 shadow-sm">
            <div class="vip-card-body p-5 text-center text-muted">
                <i class="fas fa-book-reader fa-4x mb-3 opacity-25"></i>
                <h5 class="fw-bold text-dark">Bạn chưa có bài dạy nào được phân công</h5>
                <p class="mb-0">Các khóa học và module bạn phụ trách sẽ xuất hiện tại đây.</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnList = document.getElementById('btn-list-view');
    const btnDetail = document.getElementById('btn-detail-view');
    const detailSections = document.querySelectorAll('.detail-section');
    const listActions = document.querySelectorAll('.list-actions');

    function switchToListView() {
        btnList.classList.add('btn-primary', 'text-white', 'active-view');
        btnList.classList.remove('btn-light');
        btnDetail.classList.remove('btn-primary', 'text-white', 'active-view');
        btnDetail.classList.add('btn-light');
        
        detailSections.forEach(el => el.classList.add('d-none'));
        listActions.forEach(el => el.classList.remove('d-none'));
        
        localStorage.setItem('giangVienViewMode', 'list');
    }

    function switchToDetailView() {
        btnDetail.classList.add('btn-primary', 'text-white', 'active-view');
        btnDetail.classList.remove('btn-light');
        btnList.classList.remove('btn-primary', 'text-white', 'active-view');
        btnList.classList.add('btn-light');
        
        detailSections.forEach(el => el.classList.remove('d-none'));
        listActions.forEach(el => el.classList.add('d-none'));
        
        localStorage.setItem('giangVienViewMode', 'detail');
    }

    btnList.addEventListener('click', switchToListView);
    btnDetail.addEventListener('click', switchToDetailView);

    // Khôi phục trạng thái từ bộ nhớ trình duyệt
    const savedMode = localStorage.getItem('giangVienViewMode');
    if (savedMode === 'detail') switchToDetailView();
    else switchToListView();
});
</script>

<style>
    .smaller { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .btn-group .btn { border: none !important; transition: all 0.3s; }
    .btn-group .btn.active-view { box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3); }
    
    .btn-xs { padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 4px; }
    
    @keyframes bell-shake {
        0% { transform: rotate(0); }
        15% { transform: rotate(10deg); }
        30% { transform: rotate(-10deg); }
        45% { transform: rotate(5deg); }
        60% { transform: rotate(-5deg); }
        100% { transform: rotate(0); }
    }
    .animate-bell { display: inline-block; animation: bell-shake 2s infinite; }
</style>
@endpush
@endsection
