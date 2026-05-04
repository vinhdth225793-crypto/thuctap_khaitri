@extends('layouts.app')

@section('title', 'Quản lý khóa học')

@section('content')
@php
    $totalKhoaHoc = $khoaHocMau->total() + $khoaHocDangDay->total() + $khoaHocChoGV->total() + $khoaHocSanSang->total() + $khoaHocHoanThanh->total();
@endphp

<div class="container-fluid admin-page-x">
    {{-- ========== Welcome banner ========== --}}
    <div class="apx-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="apx-welcome-text">
            <h4>Quản lý Khóa học & Lớp học</h4>
            <p>
                Khóa học là <strong>chương trình đào tạo cụ thể</strong> thuộc một nhóm ngành. Bạn có thể tạo
                <strong>khóa mẫu</strong> để nhân bản nhiều lần thành các <strong>lớp hoạt động</strong>, theo dõi
                tình trạng vận hành theo từng tab (đang dạy, chờ GV, sẵn sàng, đã kết thúc).
            </p>
        </div>
        <div class="apx-welcome-cta">
            <button type="button" id="apxViewModeBtn" class="apx-view-toggle" data-mode="full" aria-label="Đổi chế độ hiển thị">
                <i class="fas fa-compress-alt"></i>
                <span>Thu gọn</span>
            </button>
            <a href="{{ route('admin.khoa-hoc.create') }}" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Tạo khóa học mẫu
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- ========== ① Tổng quan ========== --}}
    <section class="apx-section" data-collapsible>
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan khóa học</h2>
                    <p>Năm chỉ số nhanh giúp bạn nắm tình trạng vận hành chương trình đào tạo.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ number_format($totalKhoaHoc) }}</strong> khóa</span>
            </div>
        </header>

        <div class="apx-section-body">
            <div class="row g-3">
                <div class="col-xl col-md-4 col-6">
                    <div class="apx-stat tone-info">
                        <div class="aps-icon"><i class="fas fa-copy"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($khoaHocMau->total()) }}</strong>
                            <small>Khóa mẫu</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="apx-stat tone-success">
                        <div class="aps-icon"><i class="fas fa-play-circle"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($khoaHocDangDay->total()) }}</strong>
                            <small>Đang giảng dạy</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="apx-stat tone-warning">
                        <div class="aps-icon"><i class="fas fa-clock"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($khoaHocChoGV->total()) }}</strong>
                            <small>Chờ GV xác nhận</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="apx-stat tone-primary">
                        <div class="aps-icon"><i class="fas fa-check-double"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($khoaHocSanSang->total()) }}</strong>
                            <small>Sẵn sàng mở</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="apx-stat tone-dark">
                        <div class="aps-icon"><i class="fas fa-flag-checkered"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($khoaHocHoanThanh->total()) }}</strong>
                            <small>Đã hoàn thành</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Tìm kiếm & danh sách ========== --}}
    <section class="apx-section" data-collapsible>
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách khóa học theo trạng thái</h2>
                    <p>Lọc, tìm kiếm và quản lý khóa mẫu lẫn lớp đang vận hành theo tab.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ number_format($totalKhoaHoc) }}</strong> kết quả</span>
            </div>
        </header>

        <div class="apx-section-body">
            <div class="vip-card mb-3 border-0 shadow-sm apx-filter-card">
                <div class="vip-card-body p-3">
                    <form method="GET" action="{{ route('admin.khoa-hoc.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <div class="col-md-5">
                            <label class="apx-field-label">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 vip-form-control"
                                       placeholder="Nhập tên hoặc mã khóa học..." value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="apx-field-label">Nhóm ngành</label>
                            <select name="nhom_nganh_id" class="form-select vip-form-control">
                                <option value="">-- Tất cả --</option>
                                @foreach($nhomNganhs as $nhomNganh)
                                    <option value="{{ $nhomNganh->id }}" {{ $nhomNganhId === $nhomNganh->id ? 'selected' : '' }}>
                                        [{{ $nhomNganh->ma_nhom_nganh }}] {{ $nhomNganh->ten_nhom_nganh }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="apx-field-label invisible">Lọc</label>
                            <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i> Lọc dữ liệu</button>
                        </div>
                        <div class="col-md-2">
                            <label class="apx-field-label invisible">Đặt lại</label>
                            <a href="{{ route('admin.khoa-hoc.index', ['tab' => $activeTab]) }}" class="btn btn-light w-100 fw-bold border"><i class="fas fa-rotate-left me-1"></i> Đặt lại</a>
                        </div>
                    </form>
                </div>
            </div>

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
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'ket_thuc' ? 'active fw-bold' : 'text-muted' }}"
                            id="ket_thuc-tab" data-bs-toggle="tab" data-bs-target="#ket_thuc" type="button" role="tab" data-tab="ket_thuc">
                        <i class="fas fa-flag-checkered me-1 text-dark"></i> Đã hoàn thành
                        <span class="badge bg-dark ms-1">{{ $khoaHocHoanThanh->total() }}</span>
                    </button>
                </li>
            </ul>

            <div class="vip-card border-top-0 shadow-sm" style="border-top-left-radius: 0;">
                <div class="vip-card-body p-0">
                    <div class="tab-content" id="khoaHocTabsContent">
                        <div class="tab-pane fade {{ $activeTab === 'dang_day' ? 'show active' : '' }}" id="dang_day" role="tabpanel">
                            @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-hoat-dong', ['data' => $khoaHocDangDay, 'tab' => 'dang_day', 'search' => $search])
                        </div>
                        <div class="tab-pane fade {{ $activeTab === 'cho_gv' ? 'show active' : '' }}" id="cho_gv" role="tabpanel">
                            @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-hoat-dong', ['data' => $khoaHocChoGV, 'tab' => 'cho_gv', 'search' => $search])
                        </div>
                        <div class="tab-pane fade {{ $activeTab === 'san_sang' ? 'show active' : '' }}" id="san_sang" role="tabpanel">
                            @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-hoat-dong', ['data' => $khoaHocSanSang, 'tab' => 'san_sang', 'search' => $search])
                        </div>
                        <div class="tab-pane fade {{ $activeTab === 'ket_thuc' ? 'show active' : '' }}" id="ket_thuc" role="tabpanel">
                            @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-hoat-dong', ['data' => $khoaHocHoanThanh, 'tab' => 'ket_thuc', 'search' => $search])
                        </div>
                        <div class="tab-pane fade {{ $activeTab === 'mau' ? 'show active' : '' }}" id="mau" role="tabpanel">
                            @include('pages.admin.khoa-hoc.khoa-hoc.partials.table-mau', ['data' => $khoaHocMau, 'tab' => 'mau', 'search' => $search])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', (e) => {
                const tab = e.target.getAttribute('data-tab');
                if (!tab) return;
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
                document.querySelectorAll('input[name="tab"]').forEach(input => {
                    input.value = tab;
                });
            });
        });

        // Toggle "Thu gọn / Tổng thể"
        const btn = document.getElementById('apxViewModeBtn');
        if (btn) {
            const sections = document.querySelectorAll('.apx-section[data-collapsible]');
            const STORAGE_KEY = 'khoaHocViewMode';
            const applyMode = (mode) => {
                const isCompact = mode === 'compact';
                sections.forEach(s => s.classList.toggle('is-collapsed', isCompact));
                btn.dataset.mode = mode;
                btn.querySelector('span').textContent = isCompact ? 'Tổng thể' : 'Thu gọn';
                btn.querySelector('i').className = isCompact ? 'fas fa-expand-alt' : 'fas fa-compress-alt';
            };
            applyMode(localStorage.getItem(STORAGE_KEY) || 'full');
            btn.addEventListener('click', () => {
                const next = btn.dataset.mode === 'compact' ? 'full' : 'compact';
                applyMode(next);
                localStorage.setItem(STORAGE_KEY, next);
            });
        }
    });
</script>

@include('pages.admin.partials._admin-page-styles')

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .bg-dark-soft { background-color: rgba(33, 37, 41, 0.12); }
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .nav-tabs .nav-link { color: #6c757d; border-top: 3px solid transparent; border-bottom: 1px solid #dee2e6; padding: 1rem 1.5rem; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-top-color: #0d6efd; border-bottom-color: transparent; background-color: #fff; }
    .nav-tabs .nav-link:hover:not(.active) { background-color: #f8f9fa; border-top-color: #eee; }
</style>
@endsection
