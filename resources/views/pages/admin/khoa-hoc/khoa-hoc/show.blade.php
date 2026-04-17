@extends('layouts.app')

@section('title', 'Chi tiết: ' . $khoaHoc->ma_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $khoaHoc->ma_khoa_hoc }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header & Actions -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <div class="d-flex align-items-center">
                <span class="badge bg-{{ $khoaHoc->loai === 'mau' ? 'info' : 'primary' }} me-3 px-3 py-2">
                    {{ $khoaHoc->loai === 'mau' ? 'KHÓA MẪU' : 'LỚP HOẠT ĐỘNG' }}
                </span>
                <h3 class="fw-bold mb-0">{{ $khoaHoc->ten_khoa_hoc }}</h3>
                <span class="badge bg-{{ $khoaHoc->trang_thai_hoc_tap_badge }}-soft text-{{ $khoaHoc->trang_thai_hoc_tap_badge }} border border-{{ $khoaHoc->trang_thai_hoc_tap_badge }} ms-3 px-3 py-2">
                    {{ $khoaHoc->trang_thai_hoc_tap_label }}
                </span>
            </div>
            <div class="mt-2 text-muted small">
                <i class="fas fa-barcode me-1"></i> Mã: <code class="fw-bold">{{ $khoaHoc->ma_khoa_hoc }}</code>
                <span class="mx-2">|</span>
                <i class="fas fa-layer-group me-1"></i> Nhóm ngành: <span class="fw-bold text-dark">{{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}</span>
            </div>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            @if($khoaHoc->loai === 'mau')
                <a href="{{ route('admin.khoa-hoc.mo-lop', $khoaHoc->id) }}" class="btn btn-success fw-bold shadow-sm px-4">
                    <i class="fas fa-rocket me-2"></i> MỞ LỚP TỪ MẪU NÀY
                </a>
                <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->id) }}" class="btn btn-outline-warning fw-bold ms-2">
                    <i class="fas fa-edit me-1"></i> Sửa mẫu
                </a>
            @else
                <button class="btn btn-outline-secondary fw-bold shadow-sm" disabled>
                    <i class="fas fa-lock me-2"></i> Đã mở lớp (K{{ str_pad($khoaHoc->lan_mo_thu, 2, '0', STR_PAD_LEFT) }})
                </button>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4 main-content-wrapper">
        <!-- Left: Core Info & Modules -->
        <div class="col-lg-8 scroll-column">
            {{-- THÔNG TIN CHI TIẾT --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="image-box rounded border bg-light overflow-hidden shadow-xs" style="height: 180px;">
                                <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" 
                                     class="img-fluid w-100 h-100 object-fit-cover">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="smaller fw-bold text-muted text-uppercase mb-2">Mô tả ngắn chương trình</h6>
                                <p class="mb-0 text-dark">{{ $khoaHoc->mo_ta_ngan ?: 'Chưa có mô tả ngắn.' }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">Cấp độ</span>
                                    <span class="fw-bold">{{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoaHoc->cap_do] ?? 'N/A' }}</span>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">Tổng Module</span>
                                    <span class="fw-bold">{{ $khoaHoc->tong_so_module }} bài học</span>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">Trạng thái</span>
                                    <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}">{{ $khoaHoc->label_trang_thai_van_hanh }}</span>
                                </div>
                            </div>
                            <div class="row g-3 mt-1 pt-3 border-top">
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">Module hoàn thành</span>
                                    <span class="fw-bold">{{ $khoaHoc->so_module_hoan_thanh }}/{{ $khoaHoc->moduleHocs->count() }}</span>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">Tiến độ học tập</span>
                                    <span class="fw-bold text-primary">{{ $khoaHoc->tien_do_hoc_tap }}%</span>
                                </div>
                                <div class="col-12 col-md-4">
                                    <span class="smaller text-muted d-block">Trạng thái học tập</span>
                                    <span class="badge bg-{{ $khoaHoc->trang_thai_hoc_tap_badge }}">{{ $khoaHoc->trang_thai_hoc_tap_label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($khoaHoc->mo_ta_chi_tiet)
                        <hr class="my-4">
                        <h6 class="smaller fw-bold text-muted text-uppercase mb-2">Nội dung chi tiết & Lộ trình</h6>
                        <div class="text-dark small lh-lg">{!! nl2br(e($khoaHoc->mo_ta_chi_tiet)) !!}</div>
                    @endif
                </div>
            </div>

            {{-- QUẢN LÝ HỌC VIÊN & LỊCH HỌC (Dời lên đây cho lớp hoạt động) --}}
            @if($khoaHoc->loai === 'hoat_dong')
                <div class="row mb-4">
                    <div class="col-md-6">
                        {{-- CARD QUẢN LÝ HỌC VIÊN --}}
                        <div class="vip-card shadow-sm border-0 h-100">
                            <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
                                    <i class="fas fa-users me-2"></i> Học viên
                                </h5>
                                <a href="{{ route('admin.khoa-hoc.hoc-vien.index', $khoaHoc->id) }}" class="btn btn-success btn-sm fw-bold">
                                    <i class="fas fa-cog me-1"></i> Quản lý
                                </a>
                            </div>
                            <div class="vip-card-body p-4">
                                <div class="row text-center g-2">
                                    <div class="col-4">
                                        <div class="fw-bold fs-4 text-success">
                                            {{ $khoaHoc->hocVienKhoaHocs()->where('trang_thai','dang_hoc')->count() }}
                                        </div>
                                        <div class="smaller text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Đang học</div>
                                    </div>
                                    <div class="col-4 border-start border-end">
                                        <div class="fw-bold fs-4 text-primary">
                                            {{ $khoaHoc->hocVienKhoaHocs()->where('trang_thai','hoan_thanh')->count() }}
                                        </div>
                                        <div class="smaller text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Xong</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold fs-4 text-danger">
                                            {{ $khoaHoc->hocVienKhoaHocs()->where('trang_thai','ngung_hoc')->count() }}
                                        </div>
                                        <div class="smaller text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Nghỉ</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- CARD QUẢN LÝ LỊCH HỌC --}}
                        <div class="vip-card shadow-sm border-0 h-100">
                            <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-info">
                                    <i class="fas fa-calendar-alt me-2"></i> Lịch học
                                </h5>
                                <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-info btn-sm fw-bold text-white">
                                    <i class="fas fa-edit me-1"></i> Quản lý
                                </a>
                            </div>
                            <div class="vip-card-body p-4">
                                @php 
                                    $tongLich = $khoaHoc->lichHocs()->count(); 
                                    $tongBuoiReq = $khoaHoc->moduleHocs()->sum('so_buoi'); 
                                    $assignedTeachersForPlanning = $khoaHoc->moduleHocs
                                        ->flatMap(function ($module) {
                                            return $module->phanCongGiangViens
                                                ->where('trang_thai', 'da_nhan')
                                                ->map(function ($assignment) {
                                                    return $assignment->giangVien;
                                                });
                                        })
                                        ->filter()
                                        ->unique('id')
                                        ->values();
                                    $pendingLeaveRequests = $assignedTeachersForPlanning->sum(function ($teacher) {
                                        return $teacher->donXinNghis->where('trang_thai', 'cho_duyet')->count();
                                    });
                                @endphp
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="small text-muted mb-1">Tiến độ: <strong>{{ $tongLich }} / {{ $tongBuoiReq }} buổi</strong></div>
                                    </div>
                                    <div>
                                        @if($tongLich < $tongBuoiReq)
                                            <span class="badge bg-warning text-dark px-2" style="font-size: 0.65rem;">Thiếu {{ $tongBuoiReq - $tongLich }} buổi</span>
                                        @else
                                            <span class="badge bg-success px-2" style="font-size: 0.65rem;">Đã đủ</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    @php $prog = $tongBuoiReq > 0 ? min(100, ($tongLich / $tongBuoiReq) * 100) : 0; @endphp
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $prog }}%"></div>
                                </div>
                                <div class="mt-3 p-3 bg-light rounded-3 border border-dashed">
                                    <h6 class="smaller fw-bold text-muted text-uppercase mb-2"><i class="fas fa-robot me-1"></i> Hệ thống tự động kiểm tra:</h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="smaller text-dark"><i class="fas fa-check-circle text-success me-1"></i> Đã phân công GV</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="smaller text-dark"><i class="fas fa-check-circle text-success me-1"></i> Khung giờ 07:30-20:45</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="smaller text-dark"><i class="fas fa-check-circle text-success me-1"></i> Đơn xin nghỉ của GV</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="smaller text-dark"><i class="fas fa-check-circle text-success me-1"></i> Xung đột lịch dạy khác</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top small text-muted italic">
                                        Giúp Admin tránh sai sót khi sắp xếp hàng chục lớp học cùng lúc.
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-info w-100 fw-bold text-white shadow-sm">
                                        <i class="fas fa-calendar-check me-2"></i> TRUY CẬP BỘ SẮP LỊCH THÔNG MINH
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- DANH SÁCH MODULE & GIẢNG VIÊN --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">📋 Cấu trúc Module học tập</h5>
                    @if($khoaHoc->loai === 'mau')
                        <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}" class="btn btn-primary btn-sm px-3 fw-bold shadow-xs">
                            <i class="fas fa-plus me-1"></i> Thêm module
                        </a>
                    @endif
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="ps-4 text-center" width="60">STT</th>
                                    <th>Tên Module học</th>
                                    <th class="text-center">TL (phút)</th>
                                    <th class="text-center">Tiến độ học</th>
                                    @if($khoaHoc->loai === 'hoat_dong')
                                        <th>Giảng viên phụ trách</th>
                                        <th class="text-center">Xác nhận</th>
                                    @endif
                                    <th class="pe-4 text-center" width="100">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($khoaHoc->moduleHocs as $index => $module)
                                    <tr>
                                        <td class="text-center ps-4 text-muted small fw-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                            <div class="smaller text-muted italic">{{ Str::limit($module->mo_ta, 60) }}</div>
                                        </td>
                                        <td class="text-center">{{ $module->thoi_luong_du_kien_label }}</td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <span class="badge bg-{{ $module->trang_thai_hoc_tap_badge }}-soft text-{{ $module->trang_thai_hoc_tap_badge }} border border-{{ $module->trang_thai_hoc_tap_badge }}">
                                                    {{ $module->trang_thai_hoc_tap_label }}
                                                </span>
                                                <span class="small text-muted">
                                                    {{ $module->so_buoi_hoan_thanh }}/{{ $module->so_buoi_hop_le }} buổi
                                                </span>
                                                @if($module->so_buoi_bi_huy > 0)
                                                    <span class="smaller text-warning">Hủy: {{ $module->so_buoi_bi_huy }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        
                                        @if($khoaHoc->loai === 'hoat_dong')
                                            @php $pc = $module->phanCongGiangViens->first(); @endphp
                                            <td>
                                                @if($pc)
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-mini rounded-circle bg-light me-2 border text-center" style="width: 30px; height: 30px; line-height: 30px;">
                                                                <i class="fas fa-user-tie text-primary small"></i>
                                                            </div>
                                                            <div class="lh-1">
                                                                <div class="small fw-bold text-primary">{{ $pc->giangVien->nguoiDung->ho_ten }}</div>
                                                                <div class="smaller text-muted mt-1">{{ $pc->giangVien->chuyen_nganh ?: 'Chuyên gia' }}</div>
                                                            </div>
                                                        </div>
                                                        {{-- Nút thay đổi nhanh --}}
                                                        <button type="button" class="btn btn-xs btn-outline-warning border-0 btn-replace-gv" 
                                                                data-pc-id="{{ $pc->id }}" 
                                                                data-module-name="{{ $module->ten_module }}"
                                                                data-current-gv="{{ $pc->giangVien->nguoiDung->ho_ten }}"
                                                                title="Thay đổi GV">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-muted border">Chưa gán GV</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($pc)
                                                    @php
                                                        $statusMap = [
                                                            'cho_xac_nhan' => ['bg'=>'warning','text'=>'Chờ'],
                                                            'da_nhan'     => ['bg'=>'success','text'=>'Đồng ý'],
                                                            'tu_choi'     => ['bg'=>'danger','text'=>'Từ chối']
                                                        ];
                                                        $s = $statusMap[$pc->trang_thai] ?? ['bg'=>'secondary','text'=>'?'];
                                                    @endphp
                                                    <span class="badge bg-{{ $s['bg'] }} smaller shadow-xs">{{ $s['text'] }}</span>
                                                @else — @endif
                                            </td>
                                        @endif

                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-sm btn-outline-info border-0" title="Chi tiết"><i class="fas fa-info-circle"></i></a>
                                                @if($khoaHoc->loai === 'mau')
                                                    <a href="{{ route('admin.module-hoc.edit', $module->id) }}" class="btn btn-sm btn-outline-warning border-0" title="Sửa"><i class="fas fa-edit"></i></a>
                                                    <form action="{{ route('admin.module-hoc.destroy', $module->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa module này?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-phan-cong" 
                                                            data-module-id="{{ $module->id }}" 
                                                            data-module-name="{{ $module->ten_module }}"
                                                            title="Phân công GV">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted small">Chưa có module nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- LỊCH HỌC CHI TIẾT (CHỈ CHO LỚP HOẠT ĐỘNG) --}}
            @if($khoaHoc->loai === 'hoat_dong')
                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">📅 Lịch học chi tiết các Module</h5>
                        <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-info btn-sm px-3 fw-bold text-white shadow-xs">
                            <i class="fas fa-calendar-alt me-1"></i> Quản lý lịch
                        </a>
                    </div>
                    <div class="vip-card-body p-0">
                        @foreach($khoaHoc->moduleHocs as $module)
                            @if($module->lichHocs->isNotEmpty())
                                <div class="bg-light px-4 py-2 border-bottom border-top small fw-bold text-primary">
                                    MODULE {{ $module->thu_tu_module }}: {{ $module->ten_module }}
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-white smaller">
                                            <tr>
                                                <th class="ps-4" width="60">Buổi</th>
                                                <th width="140">Thời gian</th>
                                                <th width="180">Nội dung & Tài nguyên</th>
                                                <th>Địa điểm / Giảng viên</th>
                                                <th class="text-center" width="120">Tiến trình</th>
                                                <th class="text-center" width="110">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($module->lichHocs as $lich)
                                                @php
                                                    $hasAttendance = $lich->diemDanhs->isNotEmpty();
                                                    $lectureCount = $lich->baiGiangs->count();
                                                    $resourceCount = $lich->taiNguyen->count();
                                                @endphp
                                                <tr class="{{ $lich->trang_thai === 'cho' ? '' : 'table-light' }}">
                                                    <td class="ps-4">
                                                        <div class="fw-bold text-dark">#{{ $lich->buoi_so }}</div>
                                                        <div class="smaller text-muted">{{ $lich->thu_label }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold"><i class="far fa-calendar-alt me-1 text-primary"></i>{{ $lich->ngay_hoc->format('d/m/Y') }}</div>
                                                        <div class="smaller text-muted mt-1">
                                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }}-{{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column gap-1">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge {{ $lectureCount > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-light text-muted border' }} px-2 py-1" style="font-size: 0.65rem;">
                                                                    <i class="fas fa-book-open me-1"></i>{{ $lectureCount }} bài giảng
                                                                </span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge {{ $resourceCount > 0 ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-light text-muted border' }} px-2 py-1" style="font-size: 0.65rem;">
                                                                    <i class="fas fa-paperclip me-1"></i>{{ $resourceCount }} tài liệu
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="mb-1">
                                                            @if($lich->hinh_thuc === 'online')
                                                                <span class="text-info small fw-bold"><i class="fas fa-video me-1"></i>Online</span>
                                                            @else
                                                                <span class="text-success small fw-bold"><i class="fas fa-map-marker-alt me-1"></i>{{ $lich->phong_hoc ?: 'Chưa gán' }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="avatar-xs bg-light rounded-circle text-center border" style="width: 22px; height: 22px; line-height: 20px;">
                                                                <i class="fas fa-user-tie text-muted" style="font-size: 0.6rem;"></i>
                                                            </div>
                                                            <span class="small text-dark">{{ $lich->giangVien?->nguoiDung?->ho_ten ?? 'Chưa gán' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($hasAttendance)
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.65rem;">
                                                                <i class="fas fa-check-circle me-1"></i>Xong
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.65rem;">
                                                                <i class="far fa-circle me-1"></i>Chưa
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex flex-column gap-1">
                                                            <span class="badge bg-{{ match($lich->trang_thai){'cho'=>'secondary','dang_hoc'=>'info','hoan_thanh'=>'success','huy'=>'danger',default=>'light'} }} w-100 py-1" style="font-size: 0.65rem;">
                                                                {{ $lich->trang_thai_label }}
                                                            </span>
                                                            @if($lich->giang_vien_id && $lich->trang_thai !== 'cho')
                                                                <a href="{{ route('admin.diem-danh.giang-vien.show', [$lich->id, $lich->giang_vien_id]) }}" class="smaller text-primary text-decoration-none fw-bold mt-1">
                                                                    <i class="fas fa-search-plus me-1"></i>Chi tiết log
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                        
                        @if($khoaHoc->lichHocs->isEmpty())
                            <div class="p-5 text-center text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Khóa học này chưa được lập lịch.</p>
                                <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-primary btn-sm mt-3">Lập lịch ngay</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Lifecycle & Meta -->
        <div class="col-lg-4 scroll-column">
            @if($khoaHoc->loai === 'hoat_dong')
                @if($khoaHoc->trang_thai_van_hanh === 'san_sang')
                    <div class="mb-4" id="kich-hoat-khoa-hoc">
                        <form action="{{ route('admin.khoa-hoc.xac-nhan-mo-lop', $khoaHoc->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 py-3 fw-bold shadow-sm">
                                <i class="fas fa-play me-2"></i> KÍCH HOẠT DẠY NGAY
                            </button>
                            <div class="p-2 mt-2 bg-success-soft rounded border border-success-soft text-center smaller text-success fw-bold">
                                <i class="fas fa-check-circle me-1"></i> Tất cả giảng viên đã xác nhận đồng ý
                            </div>
                        </form>
                    </div>
                @endif

                {{-- CARD LỊCH TRÌNH LỚP --}}
                <div class="vip-card mb-4 border-0 shadow-sm">
                    <div class="vip-card-header bg-primary text-white py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">📅 Lịch trình lớp học</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <div class="timeline-simple">
                            <div class="timeline-item mb-4 pb-1 border-start ps-4 position-relative">
                                <div class="timeline-point bg-info"></div>
                                <span class="smaller text-muted text-uppercase fw-bold d-block">Ngày khai giảng</span>
                                <span class="fw-bold fs-5">{{ optional($khoaHoc->ngay_khai_giang)->format('d/m/Y') ?: '--/--/----' }}</span>
                            </div>
                            <div class="timeline-item mb-4 pb-1 border-start ps-4 position-relative">
                                <div class="timeline-point bg-success"></div>
                                <span class="smaller text-muted text-uppercase fw-bold d-block">Ngày chính thức mở lớp</span>
                                <span class="fw-bold fs-5">{{ optional($khoaHoc->ngay_mo_lop)->format('d/m/Y') ?: '--/--/----' }}</span>
                            </div>
                            <div class="timeline-item pb-1 border-start ps-4 position-relative">
                                <div class="timeline-point bg-danger"></div>
                                <span class="smaller text-muted text-uppercase fw-bold d-block">Dự kiến kết thúc</span>
                                <span class="fw-bold fs-5">{{ optional($khoaHoc->ngay_ket_thuc)->format('d/m/Y') ?: '--/--/----' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- NGUỒN GỐC --}}
                <div class="vip-card mb-4 shadow-sm border-0 bg-light">
                    <div class="vip-card-body p-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-link fa-2x text-muted me-3 opacity-50"></i>
                            <div>
                                <span class="smaller text-muted fw-bold d-block text-uppercase">Gốc từ khóa mẫu</span>
                                @if($khoaHoc->khoaHocMau)
                                    <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->khoa_hoc_mau_id) }}" class="fw-bold text-decoration-none">
                                        {{ $khoaHoc->khoaHocMau->ten_khoa_hoc }}
                                    </a>
                                @else
                                    <span class="fw-bold text-dark">Khóa trực tiếp</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- STATS CHO KHÓA MẪU --}}
                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">📊 Hiệu quả đào tạo</h5>
                    </div>
                    <div class="vip-card-body p-4 text-center">
                        <div class="row g-0">
                            <div class="col-12 mb-3">
                                <div class="p-3 border rounded bg-light">
                                    <h2 class="fw-bold text-success mb-0">{{ $khoaHoc->lop_da_mo_count }}</h2>
                                    <span class="smaller text-muted text-uppercase fw-bold">Lần mở lớp thực tế</span>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info border-0 small text-start mb-0">
                            <i class="fas fa-info-circle me-1"></i> Khóa mẫu giúp chuẩn hóa quy trình dạy cho tất cả các lớp sau này.
                        </div>
                    </div>
                </div>
            @endif

            {{-- GHI CHÚ NỘI BỘ --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">📝 Ghi chú nội bộ</h5>
                </div>
                <div class="vip-card-body p-4">
                    <p class="text-dark small lh-base mb-0 italic">
                        {{ $khoaHoc->ghi_chu_noi_bo ?: 'Không có ghi chú nào dành cho quản trị viên.' }}
                    </p>
                </div>
            </div>

            {{-- META INFO --}}
            <div class="vip-card shadow-sm border-0 mb-4 bg-light">
                <div class="vip-card-body p-3 smaller">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngày khởi tạo:</span>
                        <span class="fw-bold">{{ $khoaHoc->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cập nhật cuối:</span>
                        <span class="fw-bold">{{ $khoaHoc->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Người tạo:</span>
                        <span class="fw-bold text-primary">{{ $khoaHoc->creator->ho_ten ?? 'Admin' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PHÂN CÔNG GIẢNG VIÊN --}}
<div class="modal fade shadow" id="modalPhanCong" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Phân công giảng viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modalPhanCongForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Module đang chọn:</label>
                        <div id="phanCong-moduleName" class="fw-bold fs-5 text-dark"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Chọn giảng viên *</label>
                        <select name="giang_vien_id" class="form-select vip-form-control" required>
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">
                                    {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'Chuyên gia' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Ghi chú phân công</label>
                        <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="Ghi chú về yêu cầu dạy, tài liệu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL THAY THẾ GIẢNG VIÊN --}}
<div class="modal fade shadow" id="modalReplaceGV" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exchange-alt me-2"></i> Thay đổi giảng viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modalReplaceGVForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 smaller mb-4">
                        Bạn đang thay thế giảng viên cho module: <strong id="replace-moduleName"></strong>.
                        <br>Giảng viên hiện tại: <strong id="replace-currentGV"></strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Chọn giảng viên thay thế *</label>
                        <select name="giang_vien_id" class="form-select vip-form-control" required>
                            <option value="">-- Chọn giảng viên mới --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">
                                    {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'Chuyên gia' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Lý do thay đổi / Ghi chú</label>
                        <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="Ghi chú cho GV mới..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white">Xác nhận thay thế</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal Phân công
        const modalPC = new bootstrap.Modal(document.getElementById('modalPhanCong'));
        const formPC = document.getElementById('modalPhanCongForm');
        const moduleNamePC = document.getElementById('phanCong-moduleName');

        document.querySelectorAll('.btn-phan-cong').forEach(btn => {
            btn.addEventListener('click', function() {
                const moduleId = this.dataset.moduleId;
                moduleNamePC.textContent = this.dataset.moduleName;
                formPC.action = `/admin/module-hoc/${moduleId}/assign`;
                modalPC.show();
            });
        });

        // Modal Thay thế
        const modalRep = new bootstrap.Modal(document.getElementById('modalReplaceGV'));
        const formRep = document.getElementById('modalReplaceGVForm');
        const moduleNameRep = document.getElementById('replace-moduleName');
        const currentGVRep = document.getElementById('replace-currentGV');

        document.querySelectorAll('.btn-replace-gv').forEach(btn => {
            btn.addEventListener('click', function() {
                const pcId = this.dataset.pcId;
                moduleNameRep.textContent = this.dataset.moduleName;
                currentGVRep.textContent = this.dataset.currentGv;
                formRep.action = `/admin/phan-cong/${pcId}/replace`;
                modalRep.show();
            });
        });
    });
</script>
@endpush

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .timeline-point {
        position: absolute; left: -5px; top: 0;
        width: 10px; height: 10px; border-radius: 50%;
    }
    .avatar-mini { font-size: 14px; }
    .object-fit-cover { object-fit: cover; }
    .italic { font-style: italic; }

    /* Split-Scroll Layout cho Desktop */
    @media (min-width: 992px) {
        body {
            overflow: hidden; /* Ẩn thanh cuộn chính của trang */
        }
        .main-content-wrapper {
            height: calc(100vh - 180px); /* Tính toán chiều cao còn lại của màn hình */
            margin-top: 0;
        }
        .scroll-column {
            height: 100%;
            overflow-y: auto;
            scrollbar-width: thin; /* Cho Firefox */
            padding-bottom: 50px;
        }
        /* Tùy chỉnh thanh cuộn cho Chrome/Safari/Edge */
        .scroll-column::-webkit-scrollbar {
            width: 6px;
        }
        .scroll-column::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .scroll-column::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
        .scroll-column::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
    }
</style>
@endsection



