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

    <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
        <i class="fas fa-info-circle mt-1"></i>
        <div>
            <div class="fw-bold">Hướng dẫn tạo và cấu hình đề</div>
            <div class="small mb-0">
                Bấm <strong>Vào dạy</strong> ở từng khóa học, sau đó vào màn hình chi tiết để tạo bài kiểm tra mới hoặc mở đề đã có để cấu hình.
            </div>
        </div>
    </div>

    @if($khoaHocsChuaNhan->count() > 0 || $khoaHocsDaNhan->count() > 0 || $khoaHocsHoanThanh->count() > 0)
        {{-- TABS CATEGORY --}}
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded-pill shadow-sm border d-inline-flex" id="courseTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-bold position-relative" id="pending-tab" data-bs-toggle="pill" data-bs-target="#pending-courses" type="button" role="tab">
                    <i class="fas fa-clock-rotate-left me-2"></i> Chưa nhận dạy
                    @if($khoaHocsChuaNhan->count() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem;">
                            {{ $khoaHocsChuaNhan->count() }}
                        </span>
                    @endif
                </button>
            </li>
            <li class="nav-item ms-2" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="accepted-tab" data-bs-toggle="pill" data-bs-target="#accepted-courses" type="button" role="tab">
                    <i class="fas fa-chalkboard-user me-2"></i> Đang giảng dạy
                </button>
            </li>
            <li class="nav-item ms-2" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="completed-tab" data-bs-toggle="pill" data-bs-target="#completed-courses" type="button" role="tab">
                    <i class="fas fa-circle-check me-2"></i> Đã hoàn thành
                </button>
            </li>
        </ul>

        <div class="tab-content" id="courseTabsContent">
            {{-- TAB: CHƯA NHẬN DẠY --}}
            <div class="tab-pane fade show active" id="pending-courses" role="tabpanel">
                @if($khoaHocsChuaNhan->count() > 0)
                    <div class="row">
                        @foreach($khoaHocsChuaNhan as $khoaHoc)
                            @include('pages.giang-vien.phan-cong._course_card', ['khoaHoc' => $khoaHoc])
                        @endforeach
                    </div>
                @else
                    <div class="vip-card border-0 shadow-sm mb-5">
                        <div class="vip-card-body p-5 text-center text-muted">
                            <i class="fas fa-check-circle fa-4x mb-3 text-success opacity-25"></i>
                            <h5 class="fw-bold text-dark">Tuyệt vời!</h5>
                            <p class="mb-0">Bạn đã xác nhận tất cả các phân công hiện có.</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- TAB: ĐANG GIẢNG DẠY --}}
            <div class="tab-pane fade" id="accepted-courses" role="tabpanel">
                @if($khoaHocsDaNhan->count() > 0)
                    <div class="row">
                        @foreach($khoaHocsDaNhan as $khoaHoc)
                            @include('pages.giang-vien.phan-cong._course_card', ['khoaHoc' => $khoaHoc])
                        @endforeach
                    </div>
                @else
                    <div class="vip-card border-0 shadow-sm mb-5">
                        <div class="vip-card-body p-5 text-center text-muted">
                            <i class="fas fa-folder-open fa-4x mb-3 opacity-25"></i>
                            <h5 class="fw-bold text-dark">Chưa có khóa học nào đang giảng dạy</h5>
                            <p class="mb-0">Các khóa học bạn đã xác nhận sẽ xuất hiện tại đây.</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- TAB: ĐÃ HOÀN THÀNH --}}
            <div class="tab-pane fade" id="completed-courses" role="tabpanel">
                @if($khoaHocsHoanThanh->count() > 0)
                    <div class="row">
                        @foreach($khoaHocsHoanThanh as $khoaHoc)
                            @include('pages.giang-vien.phan-cong._course_card', ['khoaHoc' => $khoaHoc])
                        @endforeach
                    </div>
                @else
                    <div class="vip-card border-0 shadow-sm mb-5">
                        <div class="vip-card-body p-5 text-center text-muted">
                            <i class="fas fa-graduation-cap fa-4x mb-3 opacity-25"></i>
                            <h5 class="fw-bold text-dark">Chưa có khóa học nào hoàn thành</h5>
                            <p class="mb-0">Các khóa học kết thúc 100% chương trình sẽ được lưu trữ tại đây.</p>
                        </div>
                    </div>
                @endif
            </div>
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
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-dark-soft { background-color: rgba(33, 37, 41, 0.1); }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .btn-group .btn { border: none !important; transition: all 0.3s; }
    .btn-group .btn.active-view { box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3); }
    
    /* Tab Category Styles */
    #courseTabs .nav-link { color: #64748b; border: none; transition: all 0.3s; }
    #courseTabs .nav-link:hover { background-color: #f1f5f9; color: #1e293b; }
    #courseTabs .nav-link.active { background-color: #4361ee; color: #fff; box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3); }
    
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
