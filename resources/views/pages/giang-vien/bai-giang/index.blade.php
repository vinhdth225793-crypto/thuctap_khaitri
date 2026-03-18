@extends('layouts.app')

@section('title', 'Trung tâm quản lý Bài giảng & Tài liệu')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Quản lý bài giảng</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-chalkboard fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">Quản lý Bài giảng & Tài liệu</h3>
                    <div class="text-muted small mt-1">Danh sách các module bạn đang phụ trách nội dung</div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        @forelse($phanCongs as $pc)
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card vip-card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-primary-soft text-primary smaller border-0 mb-2">
                                {{ $pc->khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}
                            </span>
                            <span class="badge bg-success-soft text-success smaller border-0">
                                Đã nhận dạy
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1 line-clamp-1" title="{{ $pc->khoaHoc->ten_khoa_hoc }}">
                            {{ $pc->khoaHoc->ten_khoa_hoc }}
                        </h6>
                        <div class="smaller text-muted">Mã: {{ $pc->khoaHoc->ma_khoa_hoc }}</div>
                    </div>
                    <div class="card-body p-4">
                        <div class="module-info bg-light p-3 rounded-3 mb-3 border-start border-4 border-warning">
                            <label class="smaller text-muted d-block text-uppercase fw-bold mb-1">Module phụ trách</label>
                            <div class="fw-bold text-dark">{{ $pc->moduleHoc->ten_module }}</div>
                        </div>

                        <div class="stats-area row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 border rounded text-center">
                                    <div class="h5 fw-bold mb-0 text-primary">{{ $pc->tong_tai_nguyen }}</div>
                                    <div class="smaller text-muted">Tổng tài liệu</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded text-center">
                                    <div class="h5 fw-bold mb-0 text-warning">{{ $pc->tai_nguyen_cho }}</div>
                                    <div class="smaller text-muted">Đang ẩn</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-outline-primary fw-bold py-2 shadow-xs">
                                <i class="fas fa-tasks me-2"></i> QUẢN LÝ TÀI LIỆU
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="bg-light d-inline-block p-4 rounded-circle mb-3">
                    <i class="fas fa-folder-open fa-3x text-muted opacity-25"></i>
                </div>
                <h5 class="text-muted">Bạn chưa có bài dạy nào được phân công.</h5>
                <p class="text-muted small">Vui lòng kiểm tra mục "Lộ trình giảng dạy" để xác nhận các module mới.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
