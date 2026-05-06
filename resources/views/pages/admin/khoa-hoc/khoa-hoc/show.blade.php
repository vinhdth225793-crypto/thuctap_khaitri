@extends('layouts.app')

@section('title', 'Chi tiết: ' . $khoaHoc->ma_khoa_hoc)

@section('content')
@php
    $isMau = $khoaHoc->loai === 'mau';
    $isHoatDong = $khoaHoc->loai === 'hoat_dong';
    $hocVienDangHoc = $isHoatDong ? $khoaHoc->hocVienKhoaHocs()->where('trang_thai','dang_hoc')->count() : 0;
    $hocVienHoanThanh = $isHoatDong ? $khoaHoc->hocVienKhoaHocs()->where('trang_thai','hoan_thanh')->count() : 0;
    $hocVienNghi = $isHoatDong ? $khoaHoc->hocVienKhoaHocs()->where('trang_thai','ngung_hoc')->count() : 0;
    $tongLich = $isHoatDong ? $khoaHoc->lichHocs()->count() : 0;
    $tongBuoiReq = $isHoatDong ? $khoaHoc->moduleHocs()->sum('so_buoi') : 0;
    $progLichPercent = $tongBuoiReq > 0 ? min(100, ($tongLich / $tongBuoiReq) * 100) : 0;
@endphp

<div class="container-fluid admin-page-x kh-detail-page">
    {{-- ========== Welcome banner: title + badges + actions ========== --}}
    <div class="apx-welcome kh-welcome {{ $isMau ? 'is-mau' : 'is-hoat-dong' }}">
        <div class="apx-welcome-icon">
            <i class="fas {{ $isMau ? 'fa-clone' : 'fa-graduation-cap' }}"></i>
        </div>
        <div class="apx-welcome-text">
            <div class="kh-tag-row">
                <span class="kh-loai-badge {{ $isMau ? 'is-mau' : 'is-hoat-dong' }}">
                    <i class="fas {{ $isMau ? 'fa-copy' : 'fa-play-circle' }}"></i>
                    {{ $isMau ? 'KHÓA MẪU' : 'LỚP HOẠT ĐỘNG' }}
                </span>
                <span class="kh-status-badge">
                    <i class="fas fa-circle"></i>
                    {{ $khoaHoc->label_trang_thai_van_hanh ?: $khoaHoc->trang_thai_hoc_tap_label }}
                </span>
                @if($isHoatDong)
                    <span class="kh-status-badge">
                        <i class="fas fa-hashtag"></i>
                        Khóa lần thứ {{ $khoaHoc->lan_mo_thu }}
                    </span>
                @endif
            </div>
            <h4>{{ $khoaHoc->ten_khoa_hoc }}</h4>
            <p>
                <span><i class="fas fa-barcode"></i> Mã: <code>{{ $khoaHoc->ma_khoa_hoc }}</code></span>
                <span class="kh-sep">·</span>
                <span><i class="fas fa-shapes"></i> {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}</span>
                <span class="kh-sep">·</span>
                <span><i class="fas fa-cubes"></i> {{ $tongModule }} module</span>
                <span class="kh-sep">·</span>
                <span><i class="fas fa-signal"></i> {{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoaHoc->cap_do] ?? 'Tổng hợp' }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            @if($isMau)
                <a href="{{ route('admin.khoa-hoc.index', ['tab' => 'mau']) }}" class="apx-view-toggle">
                    <i class="fas fa-arrow-left"></i> <span>Về danh sách</span>
                </a>
                <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->id) }}" class="btn btn-light text-warning fw-bold shadow-sm">
                    <i class="fas fa-edit me-1"></i> Sửa mẫu
                </a>
                <a href="{{ route('admin.khoa-hoc.mo-lop', $khoaHoc->id) }}"
                   class="btn btn-success fw-bold shadow-sm px-4 btn-create-class">
                    <i class="fas fa-rocket me-2"></i> Tạo lớp ngay
                </a>
            @else
                <a href="{{ route('admin.khoa-hoc.index', ['tab' => 'mau']) }}" class="apx-view-toggle">
                    <i class="fas fa-arrow-left"></i> <span>Về danh sách</span>
                </a>
                @if($khoaHoc->khoaHocMau)
                    <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->khoaHocMau->id) }}" class="apx-view-toggle">
                        <i class="fas fa-clone"></i> <span>Xem mẫu gốc</span>
                    </a>
                @endif
                @if($khoaHoc->trang_thai_van_hanh === 'san_sang')
                    <form action="{{ route('admin.khoa-hoc.xac-nhan-mo-lop', $khoaHoc->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning fw-bold shadow-sm px-4 btn-activate-class">
                            <i class="fas fa-play me-2"></i> Kích hoạt dạy ngay
                        </button>
                    </form>
                @else
                    <span class="btn btn-light fw-bold shadow-sm disabled">
                        <i class="fas fa-lock me-1"></i> K{{ str_pad($khoaHoc->lan_mo_thu, 2, '0', STR_PAD_LEFT) }} đang chạy
                    </span>
                @endif
            @endif
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        {{-- ============== CỘT TRÁI: Nội dung chính ============== --}}
        <div class="col-lg-8">

            {{-- ① Thông tin khóa học --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">1</span>
                        <div>
                            <h2><i class="fas fa-circle-info"></i> Thông tin khóa học</h2>
                            <p>Ảnh đại diện, mô tả chương trình và các thông số cơ bản.</p>
                        </div>
                    </div>
                </header>

                <div class="kh-info-card">
                    <div class="kh-info-image">
                        <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}"
                             alt="{{ $khoaHoc->ten_khoa_hoc }}">
                    </div>
                    <div class="kh-info-body">
                        <div class="kh-desc">
                            <span class="kh-label">Mô tả ngắn</span>
                            <p>{{ $khoaHoc->mo_ta_ngan ?: 'Chưa có mô tả ngắn cho khóa học này.' }}</p>
                        </div>
                        <div class="kh-info-stats">
                            <div class="kh-info-stat">
                                <span class="kh-label">Cấp độ</span>
                                <strong>{{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoaHoc->cap_do] ?? 'Tổng hợp' }}</strong>
                            </div>
                            <div class="kh-info-stat">
                                <span class="kh-label">Tổng module</span>
                                <strong>{{ $khoaHoc->tong_so_module }} bài</strong>
                            </div>
                            @php $sesProg = $khoaHoc->session_progress_snapshot; @endphp
                            <div class="kh-info-stat">
                                <span class="kh-label">Tiến độ học tập</span>
                                <strong class="text-primary">{{ $sesProg['percent'] }}%</strong>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $sesProg['completed'] }}/{{ $sesProg['total'] }} buổi đã xong</small>
                            </div>
                            <div class="kh-info-stat">
                                <span class="kh-label">Module hoàn thành</span>
                                <strong>{{ $khoaHoc->so_module_hoan_thanh }}/{{ $khoaHoc->moduleHocs->count() }}</strong>
                            </div>
                        </div>
                        @if($khoaHoc->mo_ta_chi_tiet)
                            <details class="kh-detail-collapse">
                                <summary>
                                    <i class="fas fa-book-open"></i> Mô tả chi tiết & lộ trình
                                    <i class="fas fa-chevron-down kh-collapse-toggle"></i>
                                </summary>
                                <div class="kh-detail-content">
                                    {!! nl2br(e($khoaHoc->mo_ta_chi_tiet)) !!}
                                </div>
                            </details>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ② Học viên & Lịch học (chỉ lớp hoạt động) --}}
            @if($isHoatDong)
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">2</span>
                            <div>
                                <h2><i class="fas fa-users"></i> Học viên & Lịch học</h2>
                                <p>Quản lý học viên ghi danh và lập lịch các buổi học của lớp.</p>
                            </div>
                        </div>
                    </header>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="kh-mini-card">
                                <div class="kh-mini-head">
                                    <span class="kh-mini-icon" style="background: #dcfce7; color: #16a34a;">
                                        <i class="fas fa-users"></i>
                                    </span>
                                    <div>
                                        <strong>Học viên</strong>
                                        <small>{{ $hocVienDangHoc + $hocVienHoanThanh + $hocVienNghi }} ghi danh</small>
                                    </div>
                                    <a href="{{ route('admin.khoa-hoc.hoc-vien.index', $khoaHoc->id) }}" class="btn btn-success btn-sm fw-bold">
                                        <i class="fas fa-cog me-1"></i> Quản lý
                                    </a>
                                </div>
                                <div class="kh-mini-stats">
                                    <div class="kh-mini-stat">
                                        <strong class="text-success">{{ $hocVienDangHoc }}</strong>
                                        <small>Đang học</small>
                                    </div>
                                    <div class="kh-mini-stat">
                                        <strong class="text-primary">{{ $hocVienHoanThanh }}</strong>
                                        <small>Hoàn thành</small>
                                    </div>
                                    <div class="kh-mini-stat">
                                        <strong class="text-danger">{{ $hocVienNghi }}</strong>
                                        <small>Nghỉ học</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="kh-mini-card">
                                <div class="kh-mini-head">
                                    <span class="kh-mini-icon" style="background: #e0f2fe; color: #0ea5e9;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <div>
                                        <strong>Lịch học</strong>
                                        <small>Tiến độ {{ $tongLich }}/{{ $tongBuoiReq }} buổi</small>
                                    </div>
                                    <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-info btn-sm fw-bold text-white">
                                        <i class="fas fa-edit me-1"></i> Lập lịch
                                    </a>
                                </div>
                                <div class="kh-mini-progress">
                                    @if($tongLich < $tongBuoiReq)
                                        <span class="badge bg-warning text-dark">Còn thiếu {{ $tongBuoiReq - $tongLich }} buổi</span>
                                    @else
                                        <span class="badge bg-success">Đã đủ buổi</span>
                                    @endif
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: {{ $progLichPercent }}%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-robot text-info me-1"></i>
                                        Tự kiểm tra: phân công GV · khung 07:30–20:45 · đơn nghỉ · xung đột lịch
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            {{-- ③ Cấu trúc module --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">{{ $isHoatDong ? 3 : 2 }}</span>
                        <div>
                            <h2><i class="fas fa-cubes"></i> Cấu trúc {{ $tongModule }} module</h2>
                            <p>{{ $isMau ? 'Khung chương trình của khóa mẫu — sẽ được sao chép khi mở lớp.' : 'Module và phân công giảng viên cho lớp này.' }}</p>
                        </div>
                    </div>
                    <div class="apx-section-meta">
                        @if($isHoatDong)
                            <span class="apx-meta-pill"><strong>{{ $moduleCoGv }}/{{ $tongModule }}</strong> đã có GV</span>
                        @endif
                        @if($isMau)
                            <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}" class="btn btn-primary btn-sm fw-bold shadow-sm">
                                <i class="fas fa-plus me-1"></i> Thêm module
                            </a>
                        @endif
                    </div>
                </header>

                <div class="kh-module-list">
                    @forelse($khoaHoc->moduleHocs as $index => $module)
                        @php
                            $pc = $module->phanCongGiangViens->first();
                            $statusMap = [
                                'cho_xac_nhan' => ['bg'=>'warning','text'=>'Chờ GV','icon'=>'clock'],
                                'da_nhan'      => ['bg'=>'success','text'=>'Đã nhận','icon'=>'check-circle'],
                                'tu_choi'      => ['bg'=>'danger','text'=>'Từ chối','icon'=>'times-circle'],
                            ];
                            $st = $pc ? ($statusMap[$pc->trang_thai] ?? null) : null;
                            $gvInit = $pc ? mb_strtoupper(mb_substr($pc->giangVien->nguoiDung->ho_ten, 0, 1)) : null;
                        @endphp
                        <div class="kh-module-row">
                            <div class="kh-module-num">M{{ str_pad($module->thu_tu_module ?? ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="kh-module-info">
                                <strong class="kh-module-name">{{ $module->ten_module }}</strong>
                                <small class="kh-module-meta">
                                    <span><i class="fas fa-clock"></i> {{ $module->thoi_luong_du_kien_label }}</span>
                                    <span class="dot-sep">·</span>
                                    <span class="badge bg-{{ $module->trang_thai_hoc_tap_badge }}-soft text-{{ $module->trang_thai_hoc_tap_badge }} border-0">
                                        {{ $module->so_buoi_hoan_thanh }}/{{ $module->so_buoi_hop_le }} buổi
                                    </span>
                                </small>
                                @if($module->mo_ta)
                                    <p class="kh-module-desc">{{ \Illuminate\Support\Str::limit($module->mo_ta, 80) }}</p>
                                @endif
                            </div>

                            @if($isHoatDong)
                                <div class="kh-module-gv">
                                    @if($pc)
                                        <span class="kh-gv-avatar" data-gv-id="{{ $pc->giangVien->id }}">{{ $gvInit }}</span>
                                        <div class="kh-gv-info">
                                            <strong>{{ $pc->giangVien->nguoiDung->ho_ten }}</strong>
                                            <small>{{ $pc->giangVien->chuyen_nganh ?: 'Giảng viên' }}</small>
                                        </div>
                                        <span class="kh-gv-status status-{{ $st['bg'] }}">
                                            <i class="fas fa-{{ $st['icon'] }}"></i> {{ $st['text'] }}
                                        </span>
                                    @else
                                        <span class="kh-gv-empty">
                                            <i class="fas fa-user-slash"></i> Chưa có GV
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="kh-module-actions">
                                <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="kh-action-btn" title="Chi tiết module">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($isMau)
                                    <a href="{{ route('admin.module-hoc.edit', $module->id) }}" class="kh-action-btn warning" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.module-hoc.destroy', $module->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa module này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kh-action-btn danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                    </form>
                                @else
                                    @if($pc)
                                        <button type="button" class="kh-action-btn warning btn-replace-gv"
                                                data-pc-id="{{ $pc->id }}"
                                                data-module-name="{{ $module->ten_module }}"
                                                data-current-gv="{{ $pc->giangVien->nguoiDung->ho_ten }}"
                                                title="Thay đổi GV">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    @else
                                        <button type="button" class="kh-action-btn primary btn-phan-cong"
                                                data-module-id="{{ $module->id }}"
                                                data-module-name="{{ $module->ten_module }}"
                                                title="Phân công GV">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="kh-empty-state">
                            <i class="fas fa-cubes"></i>
                            <strong>Chưa có module nào</strong>
                            <p>{{ $isMau ? 'Hãy bắt đầu bằng cách thêm module đầu tiên cho khóa mẫu.' : 'Lớp này chưa có cấu trúc module.' }}</p>
                            @if($isMau)
                                <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Thêm module đầu tiên
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- ④ Lịch học chi tiết (lớp hoạt động) --}}
            @if($isHoatDong)
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">4</span>
                            <div>
                                <h2><i class="fas fa-calendar-week"></i> Lịch học chi tiết theo module</h2>
                                <p>Tổng hợp các buổi học đã được lập lịch — bao gồm điểm danh, bài giảng và tài nguyên.</p>
                            </div>
                        </div>
                        <div class="apx-section-meta">
                            <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-info btn-sm fw-bold text-white shadow-sm">
                                <i class="fas fa-calendar-alt me-1"></i> Quản lý lịch
                            </a>
                        </div>
                    </header>

                    @if($khoaHoc->lichHocs->isEmpty())
                        <div class="kh-empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <strong>Lớp chưa được lập lịch</strong>
                            <p>Bắt đầu lập lịch để xem chi tiết các buổi học theo từng module.</p>
                            <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-calendar-plus me-1"></i> Lập lịch ngay
                            </a>
                        </div>
                    @else
                        @foreach($khoaHoc->moduleHocs as $module)
                            @if($module->lichHocs->isNotEmpty())
                                <details class="kh-module-schedule" {{ $loop->first ? 'open' : '' }}>
                                    <summary>
                                        <span class="kh-mod-tag">M{{ str_pad($module->thu_tu_module, 2, '0', STR_PAD_LEFT) }}</span>
                                        <strong>{{ $module->ten_module }}</strong>
                                        <small>{{ $module->lichHocs->count() }} buổi đã lập</small>
                                        <i class="fas fa-chevron-down kh-collapse-toggle"></i>
                                    </summary>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 kh-schedule-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Buổi</th>
                                                    <th>Thời gian</th>
                                                    <th>Nội dung</th>
                                                    <th>Địa điểm / GV</th>
                                                    <th class="text-center">Tiến trình</th>
                                                    <th class="text-center">Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($module->lichHocs as $lich)
                                                    @php
                                                        $hasAttendance = $lich->diemDanhs->isNotEmpty();
                                                        $lectureCount = $lich->baiGiangs->count();
                                                        $resourceCount = $lich->taiNguyen->count();
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <strong>#{{ $lich->buoi_so }}</strong>
                                                            <small class="d-block text-muted">{{ $lich->thu_label }}</small>
                                                        </td>
                                                        <td>
                                                            <strong><i class="far fa-calendar-alt text-primary me-1"></i>{{ $lich->ngay_hoc->format('d/m/Y') }}</strong>
                                                            <small class="d-block text-muted"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }}–{{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info-subtle text-info border me-1"><i class="fas fa-book-open me-1"></i>{{ $lectureCount }}</span>
                                                            <span class="badge bg-warning-subtle text-warning border"><i class="fas fa-paperclip me-1"></i>{{ $resourceCount }}</span>
                                                        </td>
                                                        <td>
                                                            @if($lich->hinh_thuc === 'online')
                                                                <span class="text-info small fw-bold"><i class="fas fa-video me-1"></i>Online</span>
                                                            @else
                                                                <span class="text-success small fw-bold"><i class="fas fa-map-marker-alt me-1"></i>{{ $lich->phong_hoc ?: 'Chưa gán' }}</span>
                                                            @endif
                                                            <small class="d-block text-muted">{{ $lich->giangVien?->nguoiDung?->ho_ten ?? 'Chưa gán GV' }}</small>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($hasAttendance)
                                                                <span class="badge bg-success-subtle text-success border">
                                                                    <i class="fas fa-check-circle me-1"></i>Đã điểm danh
                                                                </span>
                                                            @else
                                                                <span class="badge bg-light text-muted border"><i class="far fa-circle me-1"></i>Chưa</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ match($lich->trang_thai){'cho'=>'secondary','dang_hoc'=>'info','hoan_thanh'=>'success','huy'=>'danger',default=>'light'} }}">
                                                                {{ $lich->trang_thai_label }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @endif
                        @endforeach
                    @endif
                </section>
            @endif
        </div>

        {{-- ============== CỘT PHẢI: Sidebar ============== --}}
        <div class="col-lg-4">
            <div class="kh-sidebar">
                {{-- Lịch trình --}}
                @if($isHoatDong)
                    <div class="kh-side-card">
                        <div class="kh-side-head">
                            <i class="fas fa-stream"></i>
                            <strong>Mốc thời gian</strong>
                        </div>
                        <div class="kh-side-body">
                            <div class="kh-timeline">
                                <div class="kh-tl-item">
                                    <span class="kh-tl-dot bg-info"></span>
                                    <span class="kh-tl-label">Khai giảng</span>
                                    <strong>{{ optional($khoaHoc->ngay_khai_giang)->format('d/m/Y') ?: '—' }}</strong>
                                </div>
                                <div class="kh-tl-item">
                                    <span class="kh-tl-dot bg-success"></span>
                                    <span class="kh-tl-label">Mở lớp</span>
                                    <strong>{{ optional($khoaHoc->ngay_mo_lop)->format('d/m/Y') ?: '—' }}</strong>
                                </div>
                                <div class="kh-tl-item">
                                    <span class="kh-tl-dot bg-danger"></span>
                                    <span class="kh-tl-label">Kết thúc</span>
                                    <strong>{{ optional($khoaHoc->ngay_ket_thuc)->format('d/m/Y') ?: '—' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Hiệu quả khóa mẫu --}}
                    <div class="kh-side-card kh-impact-card">
                        <div class="kh-side-head">
                            <i class="fas fa-chart-bar"></i>
                            <strong>Hiệu quả đào tạo</strong>
                        </div>
                        <div class="kh-side-body">
                            <div class="kh-impact-num">{{ $khoaHoc->lop_da_mo_count ?? 0 }}</div>
                            <div class="kh-impact-label">lần mở lớp đã thực hiện</div>
                            <small class="d-block mt-3 text-muted">
                                <i class="fas fa-circle-info"></i>
                                Khóa mẫu giúp chuẩn hoá quy trình dạy cho mọi lớp được mở từ nó.
                            </small>
                        </div>
                    </div>
                @endif

                {{-- Nguồn gốc --}}
                @if($isHoatDong && $khoaHoc->khoaHocMau)
                    <div class="kh-side-card">
                        <div class="kh-side-head">
                            <i class="fas fa-link"></i>
                            <strong>Khóa mẫu gốc</strong>
                        </div>
                        <div class="kh-side-body">
                            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->khoa_hoc_mau_id) }}" class="kh-source-link">
                                <i class="fas fa-clone"></i>
                                <div>
                                    <strong>{{ $khoaHoc->khoaHocMau->ten_khoa_hoc }}</strong>
                                    <small>{{ $khoaHoc->khoaHocMau->ma_khoa_hoc }}</small>
                                </div>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Ghi chú nội bộ --}}
                <div class="kh-side-card">
                    <div class="kh-side-head">
                        <i class="fas fa-pen-to-square"></i>
                        <strong>Ghi chú nội bộ</strong>
                    </div>
                    <div class="kh-side-body">
                        <p class="kh-note-text {{ $khoaHoc->ghi_chu_noi_bo ? '' : 'kh-note-empty' }}">
                            {{ $khoaHoc->ghi_chu_noi_bo ?: 'Chưa có ghi chú dành cho admin.' }}
                        </p>
                    </div>
                </div>

                {{-- Meta info --}}
                <div class="kh-side-card kh-meta-card">
                    <div class="kh-side-head">
                        <i class="fas fa-info"></i>
                        <strong>Thông tin hệ thống</strong>
                    </div>
                    <div class="kh-side-body">
                        <div class="kh-meta-row">
                            <span><i class="fas fa-calendar-plus"></i> Tạo lúc</span>
                            <strong>{{ $khoaHoc->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="kh-meta-row">
                            <span><i class="fas fa-clock-rotate-left"></i> Cập nhật</span>
                            <strong>{{ $khoaHoc->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="kh-meta-row">
                            <span><i class="fas fa-user-pen"></i> Người tạo</span>
                            <strong class="text-primary">{{ $khoaHoc->creator->ho_ten ?? 'Admin' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============== Modal Phân công GV ============== --}}
<div class="modal fade" id="modalPhanCong" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Phân công giảng viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modalPhanCongForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Module:</label>
                        <div id="phanCong-moduleName" class="fw-bold fs-5 text-dark"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Chọn giảng viên *</label>
                        <select name="giang_vien_id" class="form-select vip-form-control" required>
                            <option value="">— Chọn giảng viên —</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">
                                    {{ $gv->nguoiDung->ho_ten }}{{ $gv->chuyen_nganh ? ' · ' . $gv->chuyen_nganh : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Ghi chú phân công</label>
                        <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="Yêu cầu dạy, tài liệu cần chuẩn bị..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============== Modal Thay thế GV ============== --}}
<div class="modal fade" id="modalReplaceGV" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exchange-alt me-2"></i> Thay đổi giảng viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modalReplaceGVForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 small mb-4">
                        Module: <strong id="replace-moduleName"></strong>
                        <br>GV hiện tại: <strong id="replace-currentGV"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">GV thay thế *</label>
                        <select name="giang_vien_id" class="form-select vip-form-control" required>
                            <option value="">— Chọn giảng viên mới —</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">
                                    {{ $gv->nguoiDung->ho_ten }}{{ $gv->chuyen_nganh ? ' · ' . $gv->chuyen_nganh : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Lý do / Ghi chú</label>
                        <textarea name="ghi_chu" class="form-control vip-form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold text-white">Xác nhận thay</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal phân công
    const modalPC = new bootstrap.Modal(document.getElementById('modalPhanCong'));
    const formPC = document.getElementById('modalPhanCongForm');
    const moduleNamePC = document.getElementById('phanCong-moduleName');
    document.querySelectorAll('.btn-phan-cong').forEach(btn => {
        btn.addEventListener('click', function() {
            const moduleId = this.dataset.moduleId;
            moduleNamePC.textContent = this.dataset.moduleName;
            formPC.action = `{{ url('admin/module-hoc') }}/${moduleId}/assign`;
            modalPC.show();
        });
    });

    // Modal thay thế GV
    const modalRep = new bootstrap.Modal(document.getElementById('modalReplaceGV'));
    const formRep = document.getElementById('modalReplaceGVForm');
    const moduleNameRep = document.getElementById('replace-moduleName');
    const currentGVRep = document.getElementById('replace-currentGV');
    document.querySelectorAll('.btn-replace-gv').forEach(btn => {
        btn.addEventListener('click', function() {
            const pcId = this.dataset.pcId;
            moduleNameRep.textContent = this.dataset.moduleName;
            currentGVRep.textContent = this.dataset.currentGv;
            formRep.action = `{{ url('admin/phan-cong') }}/${pcId}/replace`;
            modalRep.show();
        });
    });

    // Auto-color avatar GV theo ID
    const gradients = [
        'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
        'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
        'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
        'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
        'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
        'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
    ];
    document.querySelectorAll('.kh-gv-avatar[data-gv-id]').forEach(el => {
        const id = parseInt(el.dataset.gvId, 10) || 0;
        el.style.background = gradients[id % gradients.length];
    });
});
</script>
@endpush

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .kh-detail-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .kh-detail-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .kh-detail-page .apx-section-title h2 i { color: #dc2626; }
    .kh-detail-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .kh-detail-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner — variant cho khóa hoạt động vs mẫu ===== */
    .kh-welcome.is-mau {
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 52%, #1e40af 100%) !important;
        box-shadow: 0 16px 36px rgba(67, 97, 238, 0.24) !important;
    }
    .kh-welcome.is-hoat-dong {
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 50%, #2f46c9 100%) !important;
        box-shadow: 0 16px 36px rgba(67, 97, 238, 0.24) !important;
    }

    .kh-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .kh-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.35);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        border-radius: 999px;
    }

    .kh-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .kh-status-badge i { font-size: 0.55rem; opacity: 0.85; }

    .apx-welcome.kh-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.kh-welcome p i { color: #fef3c7; margin-right: 4px; }
    .apx-welcome.kh-welcome p code { background: rgba(255,255,255,0.18); color: #fff; padding: 1px 8px; border-radius: 5px; font-size: 0.82rem; }
    .kh-sep { opacity: 0.5; }

    /* Action buttons — đặc thù cho khóa */
    .btn-create-class {
        position: relative;
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 100%) !important;
        border: 0 !important;
        color: #fff !important;
        overflow: hidden;
    }
    .btn-create-class:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(67, 97, 238, 0.36);
        color: #fff !important;
    }

    .btn-activate-class {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        border: 0 !important;
        color: #fff !important;
    }
    .btn-activate-class:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(245, 158, 11, 0.4);
        color: #fff !important;
    }

    .kh-detail-page .apx-welcome-cta .btn-light:not(.disabled) {
        color: #4361ee !important;
    }

    .kh-detail-page .btn-success:not(.btn-activate-class),
    .kh-detail-page .btn-info {
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 100%) !important;
        border-color: transparent !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(67, 97, 238, 0.2);
    }

    .kh-detail-page .btn-success:not(.btn-activate-class):hover,
    .kh-detail-page .btn-info:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
        color: #fff !important;
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(67, 97, 238, 0.28);
    }

    /* ===== Info card — Section 1 ===== */
    .kh-info-card {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 20px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }

    .kh-info-image {
        border-radius: 10px;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        aspect-ratio: 4/3;
    }
    .kh-info-image img { width: 100%; height: 100%; object-fit: cover; }

    .kh-info-body { display: flex; flex-direction: column; gap: 12px; min-width: 0; }

    .kh-label {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .kh-desc p { margin: 0; color: #0f172a; line-height: 1.6; font-size: 0.9rem; }

    .kh-info-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        padding-top: 12px;
        border-top: 1px dashed #e2e8f0;
    }

    .kh-info-stat { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .kh-info-stat strong { font-size: 0.95rem; font-weight: 800; color: #0f172a; }

    .kh-detail-collapse { margin-top: 6px; }
    .kh-detail-collapse summary {
        list-style: none; cursor: pointer;
        padding: 8px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex; align-items: center; gap: 8px;
        font-size: 0.85rem;
        color: #1d4ed8;
        font-weight: 700;
    }
    .kh-detail-collapse summary::-webkit-details-marker { display: none; }
    .kh-detail-collapse summary > i:first-child { color: #1d4ed8; }
    .kh-collapse-toggle { margin-left: auto; transition: transform 0.25s ease; font-size: 0.72rem; }
    .kh-detail-collapse[open] .kh-collapse-toggle { transform: rotate(180deg); }
    .kh-detail-content {
        padding: 12px 14px; margin-top: 8px;
        background: #f8fafc; border-radius: 8px;
        font-size: 0.88rem; line-height: 1.7; color: #334155;
    }

    /* ===== Mini cards — Section 2 ===== */
    .kh-mini-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        height: 100%;
        transition: all 0.2s ease;
    }
    .kh-mini-card:hover { box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06); }

    .kh-mini-head {
        display: flex; align-items: center; gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e2e8f0;
        margin-bottom: 12px;
    }
    .kh-mini-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: grid; place-items: center;
        font-size: 1rem;
        flex-shrink: 0;
        background: #eef2ff !important;
        color: #4361ee !important;
    }
    .kh-mini-head > div { flex: 1; min-width: 0; }
    .kh-mini-head strong { display: block; font-size: 0.95rem; color: #0f172a; }
    .kh-mini-head small { display: block; color: #64748b; font-size: 0.74rem; }

    .kh-mini-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        text-align: center;
        gap: 8px;
    }
    .kh-mini-stat strong { display: block; font-size: 1.4rem; font-weight: 800; line-height: 1; }
    .kh-mini-stat strong.text-success,
    .kh-mini-stat strong.text-primary { color: #4361ee !important; }
    .kh-mini-stat small { display: block; font-size: 0.7rem; color: #64748b; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .kh-mini-stat:nth-child(2) { border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }

    .kh-mini-progress { padding: 4px 0; }
    .kh-mini-progress .progress-bar.bg-info {
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 100%) !important;
    }
    .kh-mini-progress .badge.bg-success {
        background: #4361ee !important;
        color: #fff !important;
    }

    /* ===== Module list — Section 3 ===== */
    .kh-module-list { display: flex; flex-direction: column; gap: 10px; }

    .kh-module-row {
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        gap: 14px;
        align-items: center;
        padding: 14px 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .kh-module-row:hover {
        border-color: #93c5fd;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.08);
        transform: translateX(2px);
    }

    .kh-module-num {
        flex-shrink: 0;
        padding: 6px 12px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        color: #fff;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 900;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 10px rgba(29, 78, 216, 0.25);
        min-width: 50px;
        text-align: center;
    }

    .kh-module-info { min-width: 0; }
    .kh-module-name {
        display: block;
        font-size: 0.95rem;
        color: #0f172a;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .kh-module-meta {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.75rem; color: #64748b; font-weight: 600;
        flex-wrap: wrap;
    }
    .kh-module-meta i { color: #1d4ed8; margin-right: 3px; }
    .kh-module-meta .badge { font-size: 0.68rem; padding: 2px 8px; }
    .dot-sep { opacity: 0.4; }

    .kh-module-desc {
        margin: 6px 0 0;
        font-size: 0.78rem;
        color: #94a3b8;
        font-style: italic;
        line-height: 1.4;
    }

    .kh-module-gv {
        display: flex; align-items: center; gap: 10px;
        padding: 6px 10px;
        background: #f8fafc;
        border-radius: 10px;
        min-width: 220px;
    }

    .kh-gv-avatar {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #4361ee 0%, #2f46c9 100%);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 800;
        font-size: 0.85rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    }

    .kh-gv-info { flex: 1; min-width: 0; }
    .kh-gv-info strong {
        display: block;
        font-size: 0.82rem; color: #0f172a; font-weight: 700;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .kh-gv-info small {
        display: block;
        font-size: 0.7rem; color: #64748b; font-weight: 600;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .kh-gv-status {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px;
        font-size: 0.68rem; font-weight: 700;
        border-radius: 999px;
        flex-shrink: 0;
    }
    .kh-gv-status.status-success { background: #dcfce7; color: #16a34a; }
    .kh-gv-status.status-warning { background: #fef3c7; color: #c2410c; }
    .kh-gv-status.status-danger  { background: #fee2e2; color: #b91c1c; }

    .kh-gv-empty {
        padding: 8px 12px;
        background: #fef3c7;
        color: #c2410c;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .kh-gv-empty i { margin-right: 4px; }

    .kh-module-actions {
        display: flex; gap: 4px;
        flex-shrink: 0;
    }

    .kh-action-btn {
        width: 32px; height: 32px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        display: grid; place-items: center;
        font-size: 0.78rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .kh-action-btn:hover { border-color: #1d4ed8; color: #1d4ed8; transform: translateY(-1px); }
    .kh-action-btn.warning:hover { border-color: #d97706; color: #d97706; }
    .kh-action-btn.danger:hover { border-color: #dc2626; color: #dc2626; }
    .kh-action-btn.primary { border-color: #1d4ed8; color: #1d4ed8; background: #eff6ff; }
    .kh-action-btn.primary:hover { background: #1d4ed8; color: #fff; }

    /* Empty state */
    .kh-empty-state {
        text-align: center;
        padding: 40px 24px;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
    }
    .kh-empty-state i { font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; }
    .kh-empty-state strong { display: block; font-size: 1rem; color: #0f172a; margin-bottom: 4px; }
    .kh-empty-state p { font-size: 0.85rem; color: #94a3b8; margin: 0 0 14px; }

    /* ===== Schedule details per module — Section 4 ===== */
    .kh-module-schedule {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .kh-module-schedule[open] { border-color: #93c5fd; box-shadow: 0 6px 16px rgba(29, 78, 216, 0.05); }

    .kh-module-schedule summary {
        list-style: none;
        cursor: pointer;
        padding: 12px 16px;
        display: flex; align-items: center; gap: 12px;
        background: #f8fafc;
        border-bottom: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .kh-module-schedule summary::-webkit-details-marker { display: none; }
    .kh-module-schedule[open] summary { border-bottom-color: #e2e8f0; background: #eff6ff; }
    .kh-module-schedule summary:hover { background: #eff6ff; }

    .kh-mod-tag {
        padding: 4px 10px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }
    .kh-module-schedule summary strong { font-size: 0.92rem; color: #0f172a; flex: 1; min-width: 0; }
    .kh-module-schedule summary small { color: #64748b; font-size: 0.75rem; font-weight: 600; }
    .kh-module-schedule[open] .kh-collapse-toggle { transform: rotate(180deg); }

    .kh-schedule-table { font-size: 0.85rem; }
    .kh-schedule-table thead { background: #f8fafc; font-size: 0.7rem; text-transform: uppercase; color: #64748b; }
    .kh-schedule-table th { padding: 8px 10px; }
    .kh-schedule-table td { padding: 10px; }

    /* ===== Sidebar phải ===== */
    .kh-sidebar {
        position: sticky; top: 20px;
        display: flex; flex-direction: column; gap: 14px;
    }

    .kh-side-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .kh-side-head {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.85rem;
    }
    .kh-side-head i:first-child {
        width: 24px; height: 24px;
        border-radius: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        display: grid; place-items: center;
        font-size: 0.78rem;
    }
    .kh-side-head strong { color: #0f172a; font-weight: 800; }

    .kh-side-body { padding: 16px; }

    /* Timeline */
    .kh-timeline {
        position: relative;
        padding-left: 14px;
    }
    .kh-timeline::before {
        content: '';
        position: absolute;
        left: 4px; top: 8px; bottom: 8px;
        width: 2px;
        background: linear-gradient(180deg, #93c5fd 0%, #4361ee 50%, #1d4ed8 100%);
    }
    .kh-tl-item {
        position: relative;
        padding: 6px 0 14px;
    }
    .kh-tl-item:last-child { padding-bottom: 0; }
    .kh-tl-dot {
        position: absolute;
        left: -14px; top: 8px;
        width: 10px; height: 10px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e2e8f0;
    }
    .kh-tl-dot.bg-info,
    .kh-tl-dot.bg-success,
    .kh-tl-dot.bg-danger { background: #4361ee; }
    .kh-tl-label { display: block; font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .kh-tl-item strong { display: block; font-size: 1.05rem; color: #0f172a; font-weight: 800; margin-top: 2px; }

    /* Impact card */
    .kh-impact-card .kh-side-body { text-align: center; padding: 24px 16px; }
    .kh-impact-num {
        font-size: 2.6rem;
        font-weight: 900;
        color: #4361ee;
        line-height: 1;
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .kh-impact-label { font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

    /* Source link */
    .kh-source-link {
        display: flex; align-items: center; gap: 12px;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        color: #0f172a;
        transition: all 0.2s ease;
    }
    .kh-source-link:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #0f172a;
        transform: translateX(2px);
    }
    .kh-source-link > i:first-child {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #4361ee 0%, #1d4ed8 100%);
        color: #fff;
        display: grid; place-items: center;
        flex-shrink: 0;
    }
    .kh-source-link > div { flex: 1; min-width: 0; }
    .kh-source-link strong { display: block; font-size: 0.88rem; color: #0f172a; }
    .kh-source-link small { display: block; font-size: 0.72rem; color: #64748b; font-weight: 600; }
    .kh-source-link > i:last-child { color: #94a3b8; }

    /* Note text */
    .kh-note-text {
        font-size: 0.85rem;
        color: #334155;
        line-height: 1.6;
        margin: 0;
    }
    .kh-note-empty { color: #94a3b8; font-style: italic; }

    /* Meta rows */
    .kh-meta-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 0;
        font-size: 0.82rem;
        border-bottom: 1px dashed #f1f5f9;
    }
    .kh-meta-row:last-child { border-bottom: 0; }
    .kh-meta-row span { color: #64748b; font-weight: 600; }
    .kh-meta-row span i { color: #1d4ed8; margin-right: 4px; font-size: 0.78rem; }
    .kh-meta-row strong { color: #0f172a; font-weight: 800; }

    /* ===== Responsive ===== */
    @media (max-width: 991.98px) {
        .kh-info-card { grid-template-columns: 1fr; }
        .kh-info-image { max-width: 320px; margin: 0 auto; }
        .kh-info-stats { grid-template-columns: repeat(2, 1fr); }
        .kh-module-row {
            grid-template-columns: auto 1fr;
            gap: 10px;
        }
        .kh-module-gv, .kh-module-actions {
            grid-column: 1 / -1;
            margin-top: 6px;
        }
        .kh-sidebar { position: static; }
    }

    @media (max-width: 720px) {
        .kh-info-stats { grid-template-columns: 1fr 1fr; gap: 10px; }
        .apx-welcome.kh-welcome p { font-size: 0.78rem; }
    }

    /* Bootstrap subtle backgrounds (đảm bảo có) */
    .bg-info-subtle    { background-color: rgba(8, 145, 178, 0.12); }
    .bg-warning-subtle { background-color: rgba(217, 119, 6, 0.12); }
    .bg-success-subtle { background-color: rgba(22, 163, 74, 0.12); }
    .bg-danger-subtle  { background-color: rgba(220, 38, 38, 0.12); }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .border-info-subtle { border-color: rgba(8, 145, 178, 0.3) !important; }
    .border-warning-subtle { border-color: rgba(217, 119, 6, 0.3) !important; }
    .border-success-subtle { border-color: rgba(22, 163, 74, 0.3) !important; }
</style>
@endsection
