@extends('layouts.app')

@section('title', 'Quản lý khóa học')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-layer-group me-2 text-primary"></i>
                Quản lý khóa học & lớp học
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.khoa-hoc.create') }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Tạo khóa học mẫu
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @include('components.alert')

    <!-- Search Bar -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.khoa-hoc.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                               placeholder="Tìm theo tên, mã khóa học..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Tìm kiếm</button>
                </div>
                @if($search)
                    <div class="col-md-1">
                        <a href="{{ route('admin.khoa-hoc.index', ['tab' => $activeTab]) }}" class="btn btn-link text-muted small p-0 text-decoration-none">Xóa lọc</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Nav Tabs (Đã dời Đang giảng dạy lên đầu) -->
    <ul class="nav nav-tabs border-bottom-0 mb-0" id="khoaHocTabs" role="tablist">
         <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'mau' ? 'active fw-bold' : 'text-muted' }}" 
                    id="mau-tab" data-bs-toggle="tab" data-bs-target="#mau" type="button" role="tab" data-tab="mau">
                <i class="fas fa-copy me-1 text-info"></i> Khóa mẫu
                <span class="badge bg-info ms-1">{{ $khoaHocMau->total() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'dang_day' ? 'active fw-bold' : 'text-muted' }}" 
                    id="dang_day-tab" data-bs-toggle="tab" data-bs-target="#dang_day" type="button" role="tab" data-tab="dang_day">
                <i class="fas fa-play-circle me-1 text-success"></i> Đang giảng dạy
                <span class="badge bg-success ms-1">{{ $khoaHocDangDay->total() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'cho_gv' ? 'active fw-bold' : 'text-muted' }}" 
                    id="cho_gv-tab" data-bs-toggle="tab" data-bs-target="#cho_gv" type="button" role="tab" data-tab="cho_gv">
                <i class="fas fa-clock me-1 text-warning"></i> Chờ GV xác nhận
                <span class="badge bg-warning text-dark ms-1">{{ $khoaHocChoGV->total() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'san_sang' ? 'active fw-bold' : 'text-muted' }}" 
                    id="san_sang-tab" data-bs-toggle="tab" data-bs-target="#san_sang" type="button" role="tab" data-tab="san_sang">
                <i class="fas fa-check-double me-1 text-primary"></i> Sẵn sàng mở
                <span class="badge bg-primary ms-1">{{ $khoaHocSanSang->total() }}</span>
            </button>
        </li>
       
    </ul>

    <div class="vip-card border-top-0 shadow-sm" style="border-top-left-radius: 0;">
        <div class="vip-card-body p-0">
            <div class="tab-content" id="khoaHocTabsContent">
                
                {{-- TAB: ĐANG GIẢNG DẠY --}}
                <div class="tab-pane fade {{ $activeTab === 'dang_day' ? 'show active' : '' }}" id="dang_day" role="tabpanel">
                    @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-hoat-dong', ['data' => $khoaHocDangDay, 'tab' => 'dang_day', 'search' => $search])
                </div>

                {{-- TAB: CHỜ GV XÁC NHẬN --}}
                <div class="tab-pane fade {{ $activeTab === 'cho_gv' ? 'show active' : '' }}" id="cho_gv" role="tabpanel">
                    @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-hoat-dong', ['data' => $khoaHocChoGV, 'tab' => 'cho_gv', 'search' => $search])
                </div>

                {{-- TAB: SẴN SÀNG --}}
                <div class="tab-pane fade {{ $activeTab === 'san_sang' ? 'show active' : '' }}" id="san_sang" role="tabpanel">
                    @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-hoat-dong', ['data' => $khoaHocSanSang, 'tab' => 'san_sang', 'search' => $search])
                </div>

                {{-- TAB: KHÓA HỌC MẪU --}}
                <div class="tab-pane fade {{ $activeTab === 'mau' ? 'show active' : '' }}" id="mau" role="tabpanel">
                    @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-mau', ['data' => $khoaHocMau, 'tab' => 'mau', 'search' => $search])
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', (e) => {
                const tab = e.target.getAttribute('data-tab');
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
                document.querySelectorAll('input[name="tab"]').forEach(input => { input.value = tab; });
            });
        });
    });
</script>

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .nav-tabs .nav-link { color: #6c757d; border-top: 3px solid transparent; border-bottom: 1px solid #dee2e6; padding: 1rem 1.5rem; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-top-color: #0d6efd; border-bottom-color: transparent; background-color: #fff; }
    .nav-tabs .nav-link:hover:not(.active) { background-color: #f8f9fa; border-top-color: #eee; }
</style>
@endsection
