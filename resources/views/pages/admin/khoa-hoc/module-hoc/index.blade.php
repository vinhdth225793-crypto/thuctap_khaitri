@extends('layouts.app')

@section('title', 'Danh sách Module học')

@section('content')
@php
    $tabs = [
        ['id' => 'mau',       'title' => 'Khóa mẫu',          'data' => $khoaHocsMau,        'icon' => 'fas fa-copy',             'color' => 'info'],
        ['id' => 'teaching',  'title' => 'Đang giảng dạy',    'data' => $khoaHocsDangDay,    'icon' => 'fas fa-play-circle',      'color' => 'success'],
        ['id' => 'pending',   'title' => 'Chờ GV xác nhận',   'data' => $khoaHocsChoXacNhan, 'icon' => 'fas fa-clock',            'color' => 'warning'],
        ['id' => 'ready',     'title' => 'Sẵn sàng mở',       'data' => $khoaHocsSanSang,    'icon' => 'fas fa-check-double',     'color' => 'primary'],
        ['id' => 'completed', 'title' => 'Đã hoàn thành',     'data' => $khoaHocsHoanThanh,  'icon' => 'fas fa-flag-checkered',   'color' => 'dark'],
    ];

    // Tổng số module qua tất cả khóa
    $totalModules = collect($tabs)->sum(fn ($t) => $t['data']->sum('module_hocs_count'));
    $totalKhoa = collect($tabs)->sum(fn ($t) => $t['data']->count());
    // Module có giảng viên (tổng phân công đã nhận)
    $assignedModules = 0;
    $unassignedModules = 0;
    foreach ($tabs as $tab) {
        foreach ($tab['data'] as $kh) {
            foreach ($kh->moduleHocs as $module) {
                $hasAccepted = $module->phanCongGiangViens->where('trang_thai', 'da_nhan')->isNotEmpty();
                if ($hasAccepted) $assignedModules++; else $unassignedModules++;
            }
        }
    }
@endphp

<div class="container-fluid admin-page-x">
    {{-- ========== Welcome banner ========== --}}
    <div class="apx-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-cubes"></i></div>
        <div class="apx-welcome-text">
            <h4>Quản lý Module học</h4>
            <p>
                Module là <strong>đơn vị nhỏ nhất</strong> của khóa học — mỗi khóa được chia thành nhiều module để
                phân công giảng viên và tổ chức nội dung. Tại đây bạn có thể <strong>tạo, sửa, gán giảng viên</strong>
                và theo dõi tiến độ phân công theo từng khóa.
            </p>
        </div>
        <div class="apx-welcome-cta">
            <button type="button" id="apxViewModeBtn" class="apx-view-toggle" data-mode="full" aria-label="Đổi chế độ hiển thị">
                <i class="fas fa-compress-alt"></i>
                <span>Thu gọn</span>
            </button>
            <a href="{{ route('admin.module-hoc.create') }}" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm module mới
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
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan module</h2>
                    <p>Bốn chỉ số nhanh giúp bạn nắm tình trạng cấu trúc nội dung đào tạo.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ number_format($totalModules) }}</strong> module</span>
            </div>
        </header>

        <div class="apx-section-body">
            <div class="row g-3">
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-violet">
                        <div class="aps-icon"><i class="fas fa-cubes"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($totalModules) }}</strong>
                            <small>Tổng module</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-primary">
                        <div class="aps-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($totalKhoa) }}</strong>
                            <small>Khóa học liên quan</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-success">
                        <div class="aps-icon"><i class="fas fa-user-check"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($assignedModules) }}</strong>
                            <small>Đã có giảng viên</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-warning">
                        <div class="aps-icon"><i class="fas fa-user-clock"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format($unassignedModules) }}</strong>
                            <small>Chưa có giảng viên</small>
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
                    <h2><i class="fas fa-list"></i> Module theo khóa học</h2>
                    <p>Lọc theo nhóm khóa học, mở khóa để xem chi tiết module bên trong.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ number_format($totalKhoa) }}</strong> khóa</span>
            </div>
        </header>

        <div class="apx-section-body">
            <div class="vip-card mb-3 border-0 shadow-sm apx-filter-card">
                <div class="vip-card-body p-3">
                    <form method="GET" action="{{ route('admin.module-hoc.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <label class="apx-field-label">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 vip-form-control"
                                       placeholder="Nhập tên hoặc mã module..." value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="apx-field-label">Khóa học</label>
                            <select name="khoa_hoc_id" class="form-select vip-form-control">
                                <option value="">-- Tất cả --</option>
                                @foreach($khoaHocsAll as $kh)
                                    <option value="{{ $kh->id }}" {{ $khoaHocId == $kh->id ? 'selected' : '' }}>
                                        [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
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
                            <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-light w-100 fw-bold border"><i class="fas fa-rotate-left me-1"></i> Đặt lại</a>
                        </div>
                    </form>
                </div>
            </div>

            <ul class="nav nav-tabs border-bottom-0 mb-0" id="moduleTabs" role="tablist">
                @foreach($tabs as $index => $tab)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active fw-bold' : 'text-muted' }} px-4"
                                id="{{ $tab['id'] }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $tab['id'] }}-content" type="button" role="tab">
                            <i class="{{ $tab['icon'] }} me-1 text-{{ $tab['color'] }}"></i>
                            <span>{{ $tab['title'] }}</span>
                            @if($tab['data']->count() > 0)
                                <span class="badge bg-{{ $tab['color'] }} {{ $tab['color'] === 'warning' ? 'text-dark' : '' }} ms-1" style="font-size: 0.65rem;">{{ $tab['data']->count() }}</span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="vip-card border-top-0 shadow-sm mb-5" style="border-top-left-radius: 0;">
                <div class="vip-card-body p-4">
                    <div class="tab-content" id="moduleTabsContent">
                        @foreach($tabs as $index => $tab)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}-content" role="tabpanel">
                                @if($tab['data']->count() > 0)
                                    <div class="accordion accordion-custom" id="accordion{{ $tab['id'] }}">
                                        @foreach($tab['data'] as $kIndex => $khoaHoc)
                                            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                                                <div class="card-header bg-white p-0 border-bottom-0">
                                                    <button class="btn btn-accordion w-100 d-flex justify-content-between align-items-center p-3 text-start collapsed"
                                                            type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tab['id'] }}{{ $khoaHoc->id }}">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="course-icon bg-soft-{{ $tab['color'] }} text-{{ $tab['color'] }}">
                                                                <i class="{{ $tab['icon'] }}"></i>
                                                            </div>
                                                            <div>
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <span class="badge bg-{{ $tab['color'] }} smaller px-2">{{ $khoaHoc->ma_khoa_hoc }}</span>
                                                                    <h6 class="mb-0 fw-bold text-dark">{{ $khoaHoc->ten_khoa_hoc }}</h6>
                                                                </div>
                                                                <div class="text-muted smaller">
                                                                    <i class="fas fa-layer-group me-1"></i> {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}
                                                                    <span class="mx-2 text-silver">|</span>
                                                                    <i class="fas fa-cubes me-1"></i> <strong>{{ $khoaHoc->module_hocs_count }}</strong> modules
                                                                    <span class="mx-2 text-silver">|</span>
                                                                    Tiến độ phân công: <strong class="{{ $khoaHoc->tien_do_phan_cong == 100 ? 'text-success' : 'text-primary' }}">{{ $khoaHoc->tien_do_phan_cong }}%</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="accordion-arrow">
                                                            <i class="fas fa-chevron-down text-muted small"></i>
                                                        </div>
                                                    </button>
                                                </div>

                                                <div id="collapse{{ $tab['id'] }}{{ $khoaHoc->id }}" class="collapse" data-bs-parent="#accordion{{ $tab['id'] }}">
                                                    <div class="card-body p-0 border-top">
                                                        <div class="bg-light px-4 py-2 d-flex justify-content-between align-items-center border-bottom">
                                                            <span class="smaller fw-bold text-muted text-uppercase">Chi tiết các Module</span>
                                                            <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}"
                                                               class="btn btn-xs btn-primary fw-bold px-3">
                                                                <i class="fas fa-plus-circle me-1"></i> Thêm Module
                                                            </a>
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-hover align-middle mb-0">
                                                                <thead class="bg-white smaller text-muted">
                                                                    <tr>
                                                                        <th class="ps-4 text-center" width="60">STT</th>
                                                                        <th width="140">Mã Module</th>
                                                                        <th>Tên Module</th>
                                                                        <th class="text-center" width="140">Thời lượng</th>
                                                                        <th class="text-center" width="140">Trạng thái</th>
                                                                        <th class="pe-4 text-center" width="180">Hành động</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($khoaHoc->moduleHocs as $module)
                                                                        <tr>
                                                                            <td class="text-center ps-4 text-muted small fw-bold">{{ $module->thu_tu_module }}</td>
                                                                            <td><span class="fw-bold text-primary">{{ $module->ma_module }}</span></td>
                                                                            <td>
                                                                                <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                                                                @php $pcCho = $module->phanCongGiangViens->where('trang_thai', 'cho_xac_nhan')->first(); @endphp
                                                                                @if($pcCho)
                                                                                    <span class="badge bg-warning-soft text-warning smaller mt-1">Đang chờ GV xác nhận</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-center">
                                                                                @if($module->thoi_luong_du_kien)
                                                                                    <div class="badge bg-light text-dark border smaller">
                                                                                        {{ $module->thoi_luong_du_kien_label }}
                                                                                    </div>
                                                                                @else — @endif
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <div class="form-check form-switch d-flex justify-content-center p-0">
                                                                                    <form action="{{ route('admin.module-hoc.toggle-status', $module->id) }}" method="POST">
                                                                                        @csrf
                                                                                        <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch"
                                                                                               {{ $module->trang_thai ? 'checked' : '' }} onchange="this.form.submit()">
                                                                                    </form>
                                                                                </div>
                                                                            </td>
                                                                            <td class="pe-4 text-center">
                                                                                <div class="btn-group shadow-xs rounded-3 border">
                                                                                    <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-sm btn-white border-0 text-info px-3"><i class="fas fa-eye"></i></a>
                                                                                    <a href="{{ route('admin.module-hoc.edit', $module->id) }}" class="btn btn-sm btn-white border-0 text-warning px-3 border-start"><i class="fas fa-edit"></i></a>
                                                                                    <form action="{{ route('admin.module-hoc.destroy', $module->id) }}" method="POST" class="d-inline">
                                                                                        @csrf @method('DELETE')
                                                                                        <button type="submit" class="btn btn-sm btn-white border-0 text-danger px-3 border-start" onclick="return confirm('Xóa?')"><i class="fas fa-trash-can"></i></button>
                                                                                    </form>
                                                                                </div>
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
                                    <div class="text-center py-5 text-muted">
                                        <i class="{{ $tab['icon'] }} fa-4x mb-3 opacity-25 text-{{ $tab['color'] }}"></i>
                                        <h5 class="fw-bold text-dark">Trống</h5>
                                        <p class="mb-0">Không có khóa học nào thuộc danh mục <strong>{{ $tab['title'] }}</strong>.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('apxViewModeBtn');
        if (btn) {
            const sections = document.querySelectorAll('.apx-section[data-collapsible]');
            const STORAGE_KEY = 'moduleHocViewMode';
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
    .nav-tabs .nav-link { color: #6c757d; border-top: 3px solid transparent; border-bottom: 1px solid #dee2e6; padding: 0.8rem 1.5rem; transition: all 0.2s; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-top-color: #0d6efd; border-bottom-color: transparent; background-color: #fff; font-weight: bold; }
    .nav-tabs .nav-link:hover:not(.active) { background-color: #f8f9fa; border-top-color: #eee; }

    .bg-soft-primary { background-color: #eff6ff; }
    .bg-soft-warning { background-color: #fffbeb; }
    .bg-soft-info { background-color: #f0f9ff; }
    .bg-soft-success { background-color: #f0fdf4; }
    .bg-soft-dark { background-color: #f1f5f9; }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1); }

    .accordion-custom .card { transition: all 0.3s ease; border-radius: 12px !important; }
    .btn-accordion { border: none !important; box-shadow: none !important; border-radius: 12px !important; }
    .btn-accordion:not(.collapsed) { background-color: #f8fafc; }
    .btn-accordion:not(.collapsed) .accordion-arrow { transform: rotate(180deg); }

    .course-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .accordion-arrow { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f1f5f9; transition: 0.3s; }

    .text-silver { color: #cbd5e1; }
    .smaller { font-size: 0.75rem; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8fafc; }
    .btn-xs { padding: 0.25rem 0.75rem; font-size: 0.75rem; border-radius: 6px; }
</style>
@endsection
