@extends('layouts.app')

@section('title', 'Danh sách Module học')

@section('content')
<div class="container-fluid">
    @include('pages.admin.khoa-hoc.partials.training-breadcrumb', [
        'icon' => 'fas fa-cubes',
        'current' => 'Module học',
        'accent' => '#7c3aed',
        'soft' => 'rgba(124, 58, 237, 0.12)',
        'chip' => 'Cau truc noi dung',
        'note' => 'Theo doi module, thoi luong va lien ket voi tung khoa hoc theo mot bo cuc thong nhat.',
    ])

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-cubes me-2 text-primary"></i>
                Module học theo khóa học
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.module-hoc.create') }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm module mới
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @include('components.alert')

    <!-- Search & Filter -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.module-hoc.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                               placeholder="Tìm tên hoặc mã module..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="khoa_hoc_id" class="form-select vip-form-control">
                        <option value="">-- Tất cả khóa học --</option>
                        @foreach($khoaHocsAll as $kh)
                            <option value="{{ $kh->id }}" {{ $khoaHocId == $kh->id ? 'selected' : '' }}>
                                [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Lọc dữ liệu</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-light w-100 fw-bold border">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    @php
        $tabs = [
            ['id' => 'mau',       'title' => 'Khóa mẫu',          'data' => $khoaHocsMau,        'icon' => 'fas fa-copy',             'color' => 'info'],
            ['id' => 'teaching',  'title' => 'Đang giảng dạy',    'data' => $khoaHocsDangDay,    'icon' => 'fas fa-play-circle',      'color' => 'success'],
            ['id' => 'pending',   'title' => 'Chờ GV xác nhận',   'data' => $khoaHocsChoXacNhan, 'icon' => 'fas fa-clock',            'color' => 'warning'],
            ['id' => 'ready',     'title' => 'Sẵn sàng mở',       'data' => $khoaHocsSanSang,    'icon' => 'fas fa-check-double',     'color' => 'primary'],
            ['id' => 'completed', 'title' => 'Đã hoàn thành',     'data' => $khoaHocsHoanThanh,  'icon' => 'fas fa-flag-checkered',   'color' => 'dark'],
        ];
    @endphp

    {{-- TABS NAVIGATION --}}
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
                                                            </div>                                                    </div>
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

<style>
    .nav-tabs .nav-link { color: #6c757d; border-top: 3px solid transparent; border-bottom: 1px solid #dee2e6; padding: 0.8rem 1.5rem; transition: all 0.2s; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-top-color: #0d6efd; border-bottom-color: transparent; background-color: #fff; font-weight: bold; }
    .nav-tabs .nav-link:hover:not(.active) { background-color: #f8f9fa; border-top-color: #eee; }

    .bg-soft-primary { background-color: #eff6ff; }
    .bg-soft-warning { background-color: #fffbeb; }
    .bg-soft-info { background-color: #f0f9ff; }
    .bg-soft-success { background-color: #f0fdf4; }
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
