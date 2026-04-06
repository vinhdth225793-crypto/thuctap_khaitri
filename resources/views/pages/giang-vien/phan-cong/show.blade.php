@extends('layouts.app')

@section('title', 'Phiên dạy: ' . $phanCong->moduleHoc->ten_module)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-white p-3 rounded-4 shadow-xs border">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}" class="text-decoration-none"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc') }}" class="text-decoration-none">Lộ trình dạy</a></li>
                    <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Phiên điều hành</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-md me-4" style="width: 64px; height: 64px; font-size: 1.5rem;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h2 class="fw-extrabold mb-1 text-dark letter-spacing-tight">{{ $phanCong->moduleHoc->ten_module }}</h2>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small">Khóa học: <span class="fw-bold text-primary">{{ $khoaHoc->ten_khoa_hoc }}</span></span>
                        <span class="text-silver">|</span>
                        <span class="badge bg-{{ $phanCong->moduleHoc->trang_thai_hoc_tap_badge }}-soft text-{{ $phanCong->moduleHoc->trang_thai_hoc_tap_badge }} border border-{{ $phanCong->moduleHoc->trang_thai_hoc_tap_badge }} rounded-pill px-3">
                            {{ $phanCong->moduleHoc->trang_thai_hoc_tap_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if($phanCong->trang_thai === 'cho_xac_nhan')
                <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $phanCong->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="hanh_dong" value="da_nhan">
                    <button type="submit" class="btn btn-success fw-bold px-5 py-3 rounded-4 shadow-md transition-all">
                        <i class="fas fa-check-double me-2"></i> XÁC NHẬN DẠY NGAY
                    </button>
                </form>
            @else
                <div class="badge bg-success-soft text-success border border-success px-4 py-3 shadow-sm rounded-4 fs-6">
                    <i class="fas fa-shield-check me-2"></i> Bạn đã nhận bài dạy này
                </div>
            @endif
        </div>
    </div>

    @include('components.alert')

    <div class="row" id="main-layout-row">
        <!-- Cột trái: Lịch dạy & Nội dung -->
        <div class="col-lg-8" id="teaching-roadmap-column">
            {{-- LỘ TRÌNH BUỔI HỌC --}}
            <div class="mb-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 overflow-hidden position-relative">
                            <div class="position-absolute end-0 top-0 p-3 opacity-10">
                                <i class="fas fa-calendar-alt fa-3x"></i>
                            </div>
                            <div class="smaller text-muted text-uppercase fw-bold mb-1">Buổi hợp lệ</div>
                            <div class="fs-3 fw-bold text-dark">{{ $phanCong->moduleHoc->so_buoi_hop_le }}</div>
                            <div class="smaller text-muted mt-1">Buổi trong kế hoạch</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 overflow-hidden position-relative">
                            <div class="position-absolute end-0 top-0 p-3 opacity-10">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                            </div>
                            <div class="smaller text-muted text-uppercase fw-bold mb-1">Đã hoàn thành</div>
                            <div class="fs-3 fw-bold text-success">{{ $phanCong->moduleHoc->so_buoi_hoan_thanh }}</div>
                            <div class="smaller text-muted mt-1">Buổi đã dạy xong</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 overflow-hidden position-relative">
                            <div class="position-absolute end-0 top-0 p-3 opacity-10">
                                <i class="fas fa-clock fa-3x text-primary"></i>
                            </div>
                            <div class="smaller text-muted text-uppercase fw-bold mb-1">Sắp tới</div>
                            <div class="fs-3 fw-bold text-primary">{{ $phanCong->moduleHoc->learning_progress_snapshot['upcoming_schedules'] }}</div>
                            <div class="smaller text-muted mt-1">Buổi chờ lên lớp</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 overflow-hidden position-relative">
                            <div class="position-absolute end-0 top-0 p-3 opacity-10">
                                <i class="fas fa-chart-line fa-3x text-info"></i>
                            </div>
                            <div class="smaller text-muted text-uppercase fw-bold mb-1">Tiến độ</div>
                            <div class="fs-3 fw-bold text-info">{{ $phanCong->moduleHoc->tien_do_hoc_tap }}%</div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: {{ $phanCong->moduleHoc->tien_do_hoc_tap }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                        <span class="bg-primary text-white p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        Lộ trình giảng dạy theo từng buổi
                        <button class="btn btn-sm btn-outline-primary ms-3 d-none" id="btn-show-sidebar" title="Mở lại thông tin khóa học">
                            <i class="fas fa-expand-alt me-1"></i> Xem thông tin khóa học
                        </button>
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('giang-vien.khoa-hoc.ket-qua', $phanCong->id) }}" class="btn btn-sm btn-primary shadow-sm px-3">
                            <i class="fas fa-poll-h me-1"></i> Quản lý kết quả
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-danger dropdown-toggle shadow-sm px-3" type="button" id="createExamDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus-circle me-1"></i> Tạo bài kiểm tra
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="createExamDropdown">
                                <li><h6 class="dropdown-header">Chọn phạm vi</h6></li>
                                <li>
                                    <button class="dropdown-item py-2 btn-add-test-module" type="button">
                                        <i class="fas fa-layer-group me-2 text-primary"></i> Kiểm tra cuối module
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item py-2 btn-add-test-course" type="button">
                                        <i class="fas fa-graduation-cap me-2 text-danger"></i> Kiểm tra toàn khóa
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <span class="badge bg-white text-primary border border-primary px-3 shadow-sm d-flex align-items-center">{{ $lichDays->count() }} buổi dạy</span>
                    </div>
                </div>

                <div class="session-overview-banner mb-4">
                    <div class="session-overview-banner__title">Mỗi buổi học là một phiên điều hành hoàn chỉnh</div>
                    <div class="session-overview-banner__text">
                        Card buổi học đã được chuẩn hóa theo 4 cụm: thông tin buổi học, điều hành lớp học, điểm danh và nội dung buổi học. Các nút cũ vẫn giữ nguyên đường dẫn để tiếp tục triển khai ở các phase sau.
                    </div>
                </div>

                @php
                    $otherExams = \App\Models\BaiKiemTra::where('khoa_hoc_id', $khoaHoc->id)
                        ->where(function($q) use ($phanCong) {
                            $q->where('pham_vi', 'cuoi_khoa')
                              ->orWhere(function($q2) use ($phanCong) {
                                  $q2->where('pham_vi', 'module')
                                     ->where('module_hoc_id', $phanCong->module_hoc_id);
                              });
                        })
                        ->get();
                @endphp

                @if($otherExams->isNotEmpty())
                    <div class="mb-4">
                        <div class="fw-bold small mb-2 text-danger text-uppercase"><i class="fas fa-file-invoice me-1"></i> Bài kiểm tra Module & Khóa học</div>
                        <div class="row g-3">
                            @foreach($otherExams as $test)
                                <div class="col-md-6">
                                    <div class="p-3 rounded border border-danger border-opacity-25 bg-danger bg-opacity-10 d-flex align-items-center justify-content-between shadow-sm">
                                        <div>
                                            <div class="fw-bold text-danger">{{ $test->tieu_de }}</div>
                                            <div class="smaller text-muted">
                                                <span class="badge bg-danger text-white me-1">{{ $test->pham_vi_label }}</span>
                                                {{ $test->thoi_gian_lam_bai }} phút | {{ $test->chi_tiet_cau_hois_count ?? $test->chiTietCauHois()->count() }} câu
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('giang-vien.bai-kiem-tra.edit', $test->id) }}" class="btn btn-sm btn-outline-danger px-3 shadow-xs" title="Cấu hình đề">
                                                <i class="fas fa-cog me-1"></i> Cấu hình
                                            </a>
                                            <a href="{{ route('giang-vien.bai-kiem-tra.surveillance.edit', $test->id) }}" class="btn btn-sm btn-outline-warning px-3 shadow-xs" title="Cấu hình giám sát">
                                                <i class="fas fa-shield-alt me-1"></i> Giám sát
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    $focusedLichHocId = (int) request('focus_lich_hoc_id', 0);
                @endphp

                @forelse($timelineItems as $timelineItem)
                    @include('pages.giang-vien.phan-cong.partials.timeline-session-card', [
                        'timelineItem' => $timelineItem,
                        'phanCong' => $phanCong,
                        'focusedLichHocId' => $focusedLichHocId,
                    ])
                @empty
                    <div class="vip-card p-5 text-center text-muted border-0 shadow-sm">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Chưa có lịch dạy cụ thể cho bài dạy này.</p>
                    </div>
                @endforelse
            </div>

                @if(false)
                @forelse($lichDays as $index => $lich)
                    <div id="session-{{ $lich->id }}" class="session-block mb-4 shadow-sm border border-2 border-light-subtle rounded-3 overflow-hidden bg-white {{ $focusedLichHocId === (int) $lich->id ? 'session-block-focused' : '' }}" style="border-left: 5px solid #0d6efd !important;">
                        {{-- Header của buổi học --}}
                        <div class="session-header p-3 d-flex flex-wrap align-items-center justify-content-between bg-light border-bottom border-primary border-3 border-top-0 border-end-0 border-start-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="session-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                    <span class="fw-bold fs-5">#{{ $lich->buoi_so }}</span>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $lich->ngay_hoc->format('d/m/Y') }} ({{ $lich->thu_label }})</div>
                                    <div class="smaller text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <span class="text-primary fw-bold">{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</span>
                                        <span class="mx-2 text-silver">|</span>
                                        <span class="badge bg-{{ $lich->trang_thai_color }}-soft text-dark border-0">
                                            {{ $lich->trang_thai_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-1 mt-2 mt-md-0">
                                <button type="button" class="btn btn-sm btn-outline-warning btn-icon-custom btn-edit-lich" 
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        data-hinhthuc="{{ $lich->hinh_thuc }}" data-nentang="{{ $lich->nen_tang }}"
                                        data-link="{{ $lich->link_online }}" data-meetingid="{{ $lich->meeting_id }}"
                                        data-pass="{{ $lich->mat_khau_cuoc_hop }}" data-phong="{{ $lich->phong_hoc }}"
                                        title="Cập nhật Link/Phòng">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button type="button" class="btn btn-sm btn-outline-success btn-icon-custom btn-add-resource"
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        title="Đăng tài liệu">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-primary btn-icon-custom btn-diem-danh"
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        title="Điểm danh">
                                    <i class="fas fa-user-check"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon-custom btn-add-test"
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        title="Tạo bài kiểm tra">
                                    <i class="fas fa-file-signature"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Body của buổi học --}}
                        <div class="session-body p-3">
                            <div class="row g-3">
                                {{-- Cột trái: Thông tin địa điểm --}}
                                <div class="col-md-12">
                                    <div class="p-2 rounded bg-light border-start border-4 border-info">
                                        @if($lich->hinh_thuc === 'online')
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-info fw-bold">ONLINE</span>
                                                    @if($lich->link_online)
                                                        <a href="{{ $lich->link_online }}" target="_blank" class="text-info fw-bold text-decoration-none small text-truncate d-block" style="max-width: 300px;">
                                                            <i class="fas fa-video me-1"></i> {{ $lich->link_online }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted italic small">Chưa cập nhật link họp</span>
                                                    @endif
                                                </div>
                                                @if($lich->link_online)
                                                    <button type="button" class="btn btn-xs btn-white border shadow-xs btn-copy-link" data-link="{{ $lich->link_online }}">
                                                        <i class="far fa-copy me-1"></i> Sao chép link
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-dark fw-bold">TRỰC TIẾP</span>
                                                <span class="text-dark fw-bold small"><i class="fas fa-door-open me-1 text-muted"></i>Phòng: {{ $lich->phong_hoc ?: 'Chưa gán phòng' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Khu vực Bài kiểm tra --}}
                                @include('pages.giang-vien.phan-cong.partials.teacher-attendance-card', ['lich' => $lich, 'phanCong' => $phanCong])

                                @if($lich->baiKiemTras->count() > 0)
                                    <div class="col-12 mt-3">
                                        <div class="fw-bold small mb-2 text-danger text-uppercase"><i class="fas fa-file-alt me-1"></i> Bài kiểm tra</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($lich->baiKiemTras as $test)
                                                <div class="p-2 rounded border border-danger border-opacity-25 bg-danger bg-opacity-10 d-flex align-items-center justify-content-between" style="min-width: 250px;">
                                                    <div class="me-3">
                                                        <div class="fw-bold smaller text-danger">{{ $test->tieu_de }}</div>
                                                        <div class="smaller text-muted">{{ $test->thoi_gian_lam_bai }} phút</div>
                                                    </div>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('giang-vien.bai-kiem-tra.edit', $test->id) }}" class="btn btn-xs btn-outline-danger p-1" title="Cấu hình đề"><i class="fas fa-tasks"></i></a>
                                                        <a href="{{ route('giang-vien.bai-kiem-tra.surveillance.edit', $test->id) }}" class="btn btn-xs btn-outline-warning p-1" title="Cấu hình giám sát"><i class="fas fa-shield-alt"></i></a>
                                                        <form action="{{ route('giang-vien.bai-kiem-tra.destroy', $test->id) }}" method="POST" onsubmit="return confirm('Xóa bài kiểm tra này?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-link p-1 text-danger"><i class="fas fa-trash-alt"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Khu vực Tài liệu --}}
                                <div class="col-12 mt-3">
                                    <div class="fw-bold small mb-2 text-primary text-uppercase border-top pt-3"><i class="fas fa-folder-open me-1"></i> Tài liệu học tập ({{ $lich->taiNguyen->count() }})</div>
                                    @if($lich->taiNguyen->count() > 0)
                                        <div class="row g-2">
                                            @foreach($lich->taiNguyen->sortBy('thu_tu_hien_thi') as $tn)
                                                <div class="col-md-6">
                                                    @php
                                                        $taiNguyenUrl = $tn->link_ngoai ?: asset('storage/' . ltrim((string) $tn->duong_dan_file, '/'));
                                                    @endphp
                                                    <div class="resource-card p-2 rounded border bg-white shadow-xs d-flex align-items-center hover-bg-light transition-all h-100">
                                                        {{-- Icon loại tài liệu --}}
                                                        <div class="bg-{{ $tn->loai_color }}-soft text-{{ $tn->loai_color }} rounded d-flex align-items-center justify-content-center me-3 shadow-xs" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                            <i class="fas {{ $tn->loai_icon }} fa-lg"></i>
                                                        </div>

                                                        {{-- Nội dung tiêu đề --}}
                                                        <div class="flex-grow-1 min-w-0 me-2">
                                                            <div class="fw-bold smaller text-dark text-truncate" title="{{ $tn->tieu_de }}">
                                                                {{ $tn->tieu_de }}
                                                            </div>
                                                            <div class="smaller d-flex align-items-center gap-2 mt-1">
                                                                @if($tn->trang_thai_hien_thi === 'an')
                                                                    <span class="badge bg-secondary-soft text-secondary border-0 px-2" style="font-size: 0.6rem;">
                                                                        <i class="fas fa-eye-slash me-1"></i> Ẩn
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-success-soft text-success border-0 px-2" style="font-size: 0.6rem;">
                                                                        <i class="fas fa-eye me-1"></i> Hiện
                                                                    </span>
                                                                @endif
                                                                <span class="text-muted" style="font-size: 0.6rem;">
                                                                    <i class="fas fa-sort-numeric-down me-1"></i> STT: {{ $tn->thu_tu_hien_thi }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        {{-- Nhóm nút chức năng --}}
                                                        <div class="d-flex gap-1 align-items-center flex-shrink-0 border-start ps-2">
                                                            <a href="{{ $taiNguyenUrl }}" target="_blank" class="btn btn-icon-xs text-primary" title="Xem file">
                                                                <i class="fas fa-link"></i>
                                                            </a>

                                                            {{-- Toggle Status --}}
                                                            <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.toggle', $tn->id) }}" method="POST" class="d-inline">
                                                                @csrf @method('PATCH')
                                                                <button type="submit" class="btn btn-icon-xs {{ $tn->trang_thai_hien_thi === 'hien' ? 'text-success' : 'text-secondary' }}" 
                                                                        title="{{ $tn->trang_thai_hien_thi === 'hien' ? 'Ẩn' : 'Hiện' }}">
                                                                    <i class="fas {{ $tn->trang_thai_hien_thi === 'hien' ? 'fa-toggle-on' : 'fa-toggle-off' }} fa-lg"></i>
                                                                </button>
                                                            </form>

                                                            <button type="button" class="btn btn-icon-xs text-primary btn-preview-file" 
                                                                    data-url="{{ $taiNguyenUrl }}" data-title="{{ $tn->tieu_de }}" title="Xem">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            
                                                            <button type="button" class="btn btn-icon-xs text-warning btn-edit-resource"
                                                                    data-id="{{ $tn->id }}" data-type="{{ $tn->loai_tai_nguyen }}"
                                                                    data-title="{{ $tn->tieu_de }}" data-desc="{{ $tn->mo_ta }}"
                                                                    data-link="{{ $tn->link_ngoai }}" data-status="{{ $tn->trang_thai_hien_thi }}"
                                                                    data-order="{{ $tn->thu_tu_hien_thi }}" title="Sửa">
                                                                <i class="fas fa-edit"></i>
                                                            </button>

                                                            <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.destroy', $tn->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa tài liệu này?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-icon-xs text-danger" title="Xóa">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-muted italic smaller py-2 ps-2 border-start border-3 ms-1">Chưa có tài liệu cho buổi học này.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="vip-card p-5 text-center text-muted border-0 shadow-sm">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Chưa có lịch dạy cụ thể cho bài dạy này.</p>
                    </div>
                @endforelse
            </div>

           

            {{-- MÔ TẢ MODULE --}}
            @endif

            <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="card-title fw-bold text-dark mb-0 d-flex align-items-center">
                        <span class="bg-info text-white p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        Mô tả nội dung bài dạy
                    </h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="bg-light p-4 rounded-4 border border-dashed text-dark lh-lg shadow-inner">
                        {!! $phanCong->moduleHoc->mo_ta ? nl2br(e($phanCong->moduleHoc->mo_ta)) : '<span class="text-muted italic">Chưa có mô tả chi tiết cho bài học này.</span>' !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin Khóa học & Học viên -->
        <div class="col-lg-4" id="course-info-column">
            {{-- CARD KHÓA HỌC --}}
            <div class="vip-card mb-4 shadow-sm border-0 overflow-hidden" id="course-info-card">
                <div class="vip-card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Thông tin khóa học</h5>
                    <button class="btn btn-xs btn-light fw-bold shadow-sm" id="btn-toggle-sidebar" title="Thu gọn/Mở rộng">
                        <i class="fas fa-compress-alt"></i>
                    </button>
                </div>
                <div class="vip-card-body p-4">
                    <div class="text-center mb-3">
                        <div class="rounded border bg-light overflow-hidden mx-auto shadow-xs" style="width: 120px; height: 120px;">
                            <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" 
                                 class="img-fluid w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                    <div class="mb-3 text-center">
                        <span class="badge bg-info-soft text-info border border-info mb-2">{{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}</span>
                        <h6 class="fw-bold text-dark mb-0">{{ $khoaHoc->ten_khoa_hoc }}</h6>
                        <code class="smaller">{{ $khoaHoc->ma_khoa_hoc }}</code>
                    </div>
                    <hr class="my-3">
                    <div class="row g-2 small">
                        <div class="col-6 text-muted">Trình độ:</div>
                        <div class="col-6 text-end fw-bold">{{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoaHoc->cap_do] ?? 'N/A' }}</div>
                        
                        <div class="col-6 text-muted">Khai giảng:</div>
                        <div class="col-6 text-end fw-bold">{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? '—' }}</div>
                        
                        <div class="col-6 text-muted">Kết thúc dự kiến:</div>
                        <div class="col-6 text-end fw-bold">{{ $khoaHoc->ngay_ket_thuc?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div class="p-3 rounded border bg-light mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="smaller text-muted text-uppercase fw-bold">Tiến độ khóa học</span>
                            <span class="badge bg-{{ $khoaHoc->trang_thai_hoc_tap_badge }}-soft text-{{ $khoaHoc->trang_thai_hoc_tap_badge }} border border-{{ $khoaHoc->trang_thai_hoc_tap_badge }}">
                                {{ $khoaHoc->trang_thai_hoc_tap_label }}
                            </span>
                        </div>
                        <div class="fw-bold text-dark">{{ $khoaHoc->so_module_hoan_thanh }}/{{ $khoaHoc->moduleHocs->count() }} module hoàn thành</div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $khoaHoc->tien_do_hoc_tap }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD HỌC VIÊN (FLOW 3 - PHASE 1) --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
                        <i class="fas fa-users me-2"></i> Danh sách học viên
                    </h5>
                    <span class="badge bg-success-soft text-success rounded-pill px-3">{{ $khoaHoc->hocVienKhoaHocs->count() }} HV</span>
                </div>
                <div class="vip-card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @forelse($khoaHoc->hocVienKhoaHocs as $bghv)
                            <div class="list-group-item px-3 py-3 hover-bg-light">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-mini rounded-circle bg-info-soft text-info d-flex align-items-center justify-content-center me-3 shadow-xs" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <span class="fw-bold">{{ substr($bghv->hocVien->ho_ten, 0, 1) }}</span>
                                    </div>
                                    <div class="flex-fill min-w-0">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <div class="fw-bold text-dark text-truncate" title="{{ $bghv->hocVien->ho_ten }}">
                                                {{ $bghv->hocVien->ho_ten }}
                                            </div>
                                            <code class="smaller text-muted">#{{ $bghv->hocVien->ma_nguoi_dung }}</code>
                                        </div>
                                        
                                        <div class="smaller text-muted d-flex flex-column gap-1">
                                            <span class="text-truncate"><i class="far fa-envelope me-1"></i>{{ $bghv->hocVien->email }}</span>
                                            <span><i class="fas fa-phone-alt me-1"></i>{{ $bghv->hocVien->so_dien_thoai ?: 'Chưa cập nhật' }}</span>
                                            <span class="fw-bold"><i class="far fa-calendar-alt me-1"></i>Tham gia: {{ $bghv->ngay_tham_gia?->format('d/m/Y') ?? 'N/A' }}</span>
                                            
                                            {{-- Thống kê chuyên cần (Phase 2) --}}
                                            @php
                                                $myDiemDanh = $bghv->hocVien->diemDanhs->whereIn('lich_hoc_id', $lichHocIds);
                                                $coMat = $myDiemDanh->where('trang_thai', 'co_mat')->count();
                                                $vang  = $myDiemDanh->where('trang_thai', 'vang_mat')->count();
                                                $tre   = $myDiemDanh->where('trang_thai', 'vao_tre')->count();
                                            @endphp
                                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                                <span class="badge bg-success-soft text-success border-0 px-2" style="font-size: 0.6rem;" title="Có mặt">
                                                    <i class="fas fa-check-circle me-1"></i>{{ $coMat }}
                                                </span>
                                                <span class="badge bg-danger-soft text-danger border-0 px-2" style="font-size: 0.6rem;" title="Vắng mặt">
                                                    <i class="fas fa-times-circle me-1"></i>{{ $vang }}
                                                </span>
                                                <span class="badge bg-warning-soft text-warning border-0 px-2" style="font-size: 0.6rem;" title="Vào trễ">
                                                    <i class="fas fa-clock me-1"></i>{{ $tre }}
                                                </span>
                                            </div>

                                            @if($bghv->ghi_chu)
                                                <span class="text-warning italic mt-1 border-start border-warning ps-2">
                                                    <i class="fas fa-comment-dots me-1"></i> {{ $bghv->ghi_chu }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ms-2 text-end">
                                        <span class="badge {{ $bghv->trang_thai_badge }} shadow-xs mb-1" style="font-size: 0.65rem;">
                                            {{ $bghv->trang_thai_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small italic">
                                <i class="fas fa-users-slash fa-2x mb-2 d-block opacity-25"></i>
                                Chưa có học viên nào ghi danh vào lớp này.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="vip-card-footer bg-light p-3 text-center border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold w-100" data-bs-toggle="modal" data-bs-target="#modalRequestStudent">
                        <i class="fas fa-user-edit me-1"></i> Yêu cầu thay đổi học viên
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ĐIỂM DANH (PHASE 7) --}}
<div class="modal fade shadow" id="modalDiemDanh" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-check me-2"></i> Điểm danh buổi <span id="dd-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Nav Tabs trong Modal -->
            <ul class="nav nav-tabs nav-fill bg-light" id="diemDanhModalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold small py-3" id="tab-attendance-list" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button" role="tab">
                        <i class="fas fa-list me-1"></i> DANH SÁCH ĐIỂM DANH
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold small py-3" id="tab-report-admin" data-bs-toggle="tab" data-bs-target="#report-pane" type="button" role="tab">
                        <i class="fas fa-paper-plane me-1"></i> CHỐT ATTENDANCE
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab 1: Danh sách điểm danh -->
                <div class="tab-pane fade show active" id="attendance-pane" role="tabpanel">
                    <form id="formDiemDanh" method="POST" action="">
                        @csrf
                        <div class="modal-body p-0">
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <span class="small text-muted fw-bold">Ngày dạy: <span id="dd-ngay-label" class="text-dark"></span></span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-xs btn-outline-success" onclick="checkAllAttendance('co_mat')">Tất cả Có mặt</button>
                                    <button type="button" class="btn btn-xs btn-outline-warning" onclick="checkAllAttendance('co_phep')">Tất cả Có phép</button>
                                </div>
                            </div>
                            <div class="px-3 py-2 border-bottom bg-white">
                                <div id="attendance-summary" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light sticky-top shadow-xs">
                                        <tr>
                                            <th class="ps-4">Học viên</th>
                                            <th class="text-center" width="120">Trạng thái</th>
                                            <th class="pe-4">Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendance-list">
                                        {{-- Load bằng JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 justify-content-center gap-2 bg-light">
                            <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">HỦY BỎ</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">LƯU ĐIỂM DANH</button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Báo cáo gửi Admin -->
                <div class="tab-pane fade" id="report-pane" role="tabpanel">
                    <form id="formBaoCao" method="POST" action="">
                        @csrf
                        <div class="modal-body p-4">
                            <div id="report-status-badge" class="mb-3 text-center"></div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nội dung chốt attendance / báo cáo buổi dạy *</label>
                                <textarea name="bao_cao_giang_vien" id="dd-bao-cao-content" class="form-control vip-form-control" rows="8" 
                                          placeholder="Nhập nội dung báo cáo cho Admin (VD: Tình hình lớp học, các vấn đề phát sinh, nhận xét chung về buổi học...)" required></textarea>
                            </div>
                            
                            <div class="alert alert-warning border-0 small mb-0">
                                <i class="fas fa-info-circle me-1"></i> 
                                <b>Lưu ý:</b> Hành động này được dùng như bước chốt attendance cuối buổi và gửi báo cáo trực tiếp đến Ban quản lý.
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 justify-content-center gap-2 bg-light">
                            <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">HỦY BỎ</button>
                            <button type="submit" id="btn-submit-report" class="btn btn-success px-4 fw-bold shadow-sm">CHỐT ATTENDANCE VÀ GỬI BÁO CÁO</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL YÊU CẦU HỌC VIÊN (PHASE 6) --}}
<div class="modal fade shadow" id="modalRequestStudent" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i> Gửi yêu cầu về học viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('giang-vien.khoa-hoc.gui-yeu-cau-hoc-vien', $khoaHoc->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Loại yêu cầu *</label>
                        <select name="loai_yeu_cau" id="req-type" class="form-select vip-form-control" required>
                            <option value="them">Thêm học viên mới vào lớp</option>
                            <option value="xoa">Xóa học viên khỏi lớp</option>
                            <option value="sua">Thay đổi trạng thái học viên</option>
                        </select>
                    </div>

                    <div id="req-group-add">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Họ tên học viên *</label>
                            <input type="text" name="ten_hoc_vien" class="form-control vip-form-control" placeholder="Nhập đầy đủ họ tên">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email học viên *</label>
                            <input type="email" name="email_hoc_vien" class="form-control vip-form-control" placeholder="Nhập email để hệ thống định danh">
                        </div>
                    </div>

                    <div id="req-group-select" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Chọn học viên từ danh sách lớp *</label>
                            <select name="hoc_vien_id" class="form-select vip-form-control">
                                <option value="">-- Chọn học viên --</option>
                                @foreach($khoaHoc->hocVienKhoaHocs as $bghv)
                                    <option value="{{ $bghv->hocVien->ma_nguoi_dung }}">
                                        {{ $bghv->hocVien->ho_ten }} (#{{ $bghv->hocVien->ma_nguoi_dung }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Lý do & Nội dung chi tiết *</label>
                        <textarea name="ly_do" class="form-control vip-form-control" rows="4" placeholder="Giải thích chi tiết yêu cầu của bạn..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm border-0">GỬI YÊU CẦU</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL ĐĂNG TÀI NGUYÊN (PHASE 4) --}}
<div class="modal fade shadow" id="modalAddResource" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-cloud-upload-alt me-2"></i> Đăng tài liệu buổi <span id="res-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddResource" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="selected_lich_hoc_id" id="selected-lich-hoc-id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Loại tài nguyên *</label>
                            <select name="loai_tai_nguyen" class="form-select vip-form-control" required>
                                <option value="bai_giang">Bài giảng (Slide/Video)</option>
                                <option value="tai_lieu">Tài liệu tham khảo</option>
                                <option value="bai_tap">Bài tập về nhà</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Thứ tự hiển thị</label>
                            <input type="number" name="thu_tu_hien_thi" class="form-control vip-form-control" value="0" min="0">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Tiêu đề tài liệu *</label>
                        <input type="text" name="tieu_de" class="form-control vip-form-control" placeholder="VD: Slide buổi 1 - Giới thiệu Laravel" required>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Mô tả ngắn</label>
                        <textarea name="mo_ta" class="form-control vip-form-control" rows="2" placeholder="Tóm tắt nội dung tài liệu..."></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Link ngoài (Youtube/Drive/...)</label>
                        <input type="url" name="link_ngoai" class="form-control vip-form-control" placeholder="https://...">
                        <div class="smaller text-muted mt-1 italic">Nhập link ngoài nếu không tải file lên.</div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Tải lên file (Tối đa 10MB)</label>
                        <input type="file" name="file_dinh_kem" id="add-res-file" class="form-control vip-form-control">
                        <div class="smaller text-muted mt-1 italic">Hỗ trợ: PDF, Word, PowerPoint, Excel, ZIP, RAR</div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border border-primary-soft">
                        <label class="form-label small fw-bold d-block mb-2 text-primary"><i class="fas fa-cog me-1"></i> Tùy chọn lưu trữ</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="save_format" id="save_original" value="original" checked>
                            <label class="form-check-label small" for="save_original">
                                <b>Giữ nguyên file gốc:</b> Lưu đúng định dạng bạn tải lên.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="save_format" id="save_pdf" value="pdf">
                            <label class="form-check-label small" for="save_pdf">
                                <b>Chuyển sang PDF:</b> Giúp học viên xem trực tiếp ngay trên web (Khuyên dùng).
                            </label>
                        </div>
                        <div id="pdf-warning" class="mt-2 smaller text-danger d-none italic">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            Hệ thống đang chạy Local, bạn nên tự chuyển file sang PDF trước khi tải lên để đảm bảo hiển thị tốt nhất.
                        </div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border">
                        <label class="form-label small fw-bold d-block mb-2">Trạng thái công khai</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="status_an" value="an" checked>
                            <label class="form-check-label small" for="status_an">Lưu nháp (Ẩn với học viên)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="status_hien" value="hien">
                            <label class="form-check-label small" for="status_hien">Công khai ngay</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">HỦY BỎ</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">ĐĂNG TÀI NGUYÊN</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL SỬA TÀI NGUYÊN (PHASE 4) --}}
<div class="modal fade shadow" id="modalEditResource" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Chỉnh sửa tài nguyên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditResource" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Loại tài nguyên *</label>
                            <select name="loai_tai_nguyen" id="edit-res-type" class="form-select vip-form-control" required>
                                <option value="bai_giang">Bài giảng (Slide/Video)</option>
                                <option value="tai_lieu">Tài liệu tham khảo</option>
                                <option value="bai_tap">Bài tập về nhà</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Thứ tự hiển thị</label>
                            <input type="number" name="thu_tu_hien_thi" id="edit-res-order" class="form-control vip-form-control" min="0">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Tiêu đề tài liệu *</label>
                        <input type="text" name="tieu_de" id="edit-res-title" class="form-control vip-form-control" required>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Mô tả ngắn</label>
                        <textarea name="mo_ta" id="edit-res-desc" class="form-control vip-form-control" rows="2"></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Link ngoài (Youtube/Drive/...)</label>
                        <input type="url" name="link_ngoai" id="edit-res-link" class="form-control vip-form-control">
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Thay đổi file (Để trống nếu giữ nguyên)</label>
                        <input type="file" name="file_dinh_kem" id="edit-res-file" class="form-control vip-form-control">
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border border-warning-soft">
                        <label class="form-label small fw-bold d-block mb-2 text-warning"><i class="fas fa-cog me-1"></i> Tùy chọn lưu trữ</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="save_format" id="edit_save_original" value="original" checked>
                            <label class="form-check-label small" for="edit_save_original">
                                <b>Giữ nguyên file gốc</b>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="save_format" id="edit_save_pdf" value="pdf">
                            <label class="form-check-label small" for="edit_save_pdf">
                                <b>Chuyển sang PDF</b>
                            </label>
                        </div>
                        <div id="edit-pdf-warning" class="mt-2 smaller text-danger d-none italic">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            Bạn nên tự chuyển file sang PDF trước khi tải lên.
                        </div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border">
                        <label class="form-label small fw-bold d-block mb-2">Trạng thái công khai</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="edit-status-an" value="an">
                            <label class="form-check-label small" for="edit-status-an">Lưu nháp</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="edit-status-hien" value="hien">
                            <label class="form-check-label small" for="edit-status-hien">Công khai</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">HỦY BỎ</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white">LƯU THAY ĐỔI</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CẬP NHẬT LINK ONLINE / PHÒNG HỌC --}}
<div class="modal fade shadow" id="modalEditLich" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Cập nhật buổi học <span id="edit-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditLich" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Hình thức dạy học *</label>
                        <select name="hinh_thuc" id="edit-hinh-thuc" class="form-select vip-form-control" required>
                            <option value="truc_tiep">Trực tiếp (Tại trung tâm)</option>
                            <option value="online">Online (Qua link họp)</option>
                        </select>
                    </div>

                    {{-- Nhóm trường cho Online --}}
                    <div id="group-online" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nền tảng học Online</label>
                            <select name="nen_tang" id="edit-nen-tang" class="form-select vip-form-control">
                                <option value="">-- Chọn nền tảng --</option>
                                <option value="Zoom">Zoom</option>
                                <option value="Google Meet">Google Meet</option>
                                <option value="Microsoft Teams">Microsoft Teams</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Link học Online (URL) *</label>
                            <input type="url" name="link_online" id="edit-link" class="form-control vip-form-control" placeholder="https://zoom.us/j/...">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Meeting ID</label>
                                <input type="text" name="meeting_id" id="edit-meeting-id" class="form-control vip-form-control" placeholder="123 456 789">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mật khẩu cuộc họp</label>
                                <input type="text" name="mat_khau_cuoc_hop" id="edit-pass" class="form-control vip-form-control" placeholder="abcdef">
                            </div>
                        </div>
                    </div>

                    <div id="group-offline" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Phòng học *</label>
                            <input type="text" name="phong_hoc" id="edit-phong" class="form-control vip-form-control" placeholder="VD: Phòng 101, Tầng 2">
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="apply_to_all_online" value="1" id="applyAll">
                        <label class="form-check-label small text-primary fw-bold" for="applyAll">
                            Áp dụng link này cho tất cả buổi Online của khóa
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white border-0">CẬP NHẬT</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TẠO BÀI KIỂM TRA (PHASE 8) --}}
<div class="modal fade shadow" id="modalAddTest" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-signature me-2"></i> Tạo bài kiểm tra buổi <span id="test-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('giang-vien.bai-kiem-tra.store') }}" method="POST">
                @csrf
                <input type="hidden" name="khoa_hoc_id" value="{{ $khoaHoc->id }}">
                <input type="hidden" name="module_hoc_id" value="{{ $phanCong->module_hoc_id }}">
                <input type="hidden" name="lich_hoc_id" id="test-lich-id">
                <input type="hidden" name="pham_vi" value="buoi_hoc">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tiêu đề bài kiểm tra *</label>
                        <input type="text" name="tieu_de" class="form-control vip-form-control" placeholder="VD: Kiểm tra nhanh buổi 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Thời gian làm bài (Phút) *</label>
                        <input type="number" name="thoi_gian_lam_bai" class="form-control vip-form-control" value="15" min="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Ghi chú / Mô tả</label>
                        <textarea name="mo_ta" class="form-control vip-form-control" rows="2" placeholder="Nội dung tóm tắt..."></textarea>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label small fw-bold d-block">Chế độ bài kiểm tra</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="co_giam_sat" value="1" id="test-co-giam-sat">
                            <label class="form-check-label fw-semibold" for="test-co-giam-sat">
                                Bật giám sát nâng cao
                            </label>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-md-6">
                                <label class="form-label">Ngưỡng vi phạm</label>
                                <input type="number" name="so_lan_vi_pham_toi_da" class="form-control vip-form-control" value="3" min="1" max="20">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Chu kỳ snapshot (giây)</label>
                                <input type="number" name="chu_ky_snapshot_giay" class="form-control vip-form-control" value="30" min="10" max="300">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="bat_buoc_fullscreen" value="1" id="test-fullscreen">
                                    <label class="form-check-label" for="test-fullscreen">Bắt buộc fullscreen</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="bat_buoc_camera" value="1" id="test-camera">
                                    <label class="form-check-label" for="test-camera">Bắt buộc camera</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tu_dong_nop_khi_vi_pham" value="1" id="test-auto-submit">
                                    <label class="form-check-label" for="test-auto-submit">Tự động nộp khi vượt ngưỡng</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="chan_copy_paste" value="1" id="test-copy-paste">
                                    <label class="form-check-label" for="test-copy-paste">Chặn copy/paste</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="chan_chuot_phai" value="1" id="test-right-click">
                                    <label class="form-check-label" for="test-right-click">Chặn chuột phải</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm">TẠO BÀI KIỂM TRA</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW TÀI LIỆU (PHASE 4 UPGRADE) --}}
<div class="modal fade shadow" id="modalPreviewResource" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                        <i class="fas fa-eye fa-sm"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="preview-title">Xem trước tài liệu</h5>
                        <small class="text-light opacity-75" id="preview-filename"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10" style="height: 80vh; overflow: hidden;">
                <div id="preview-container" class="w-100 h-100 d-flex align-items-center justify-content-center">
                    {{-- Nội dung preview sẽ được load bằng JS --}}
                    <div class="text-center p-5" id="preview-loading">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted fw-bold">Đang tải nội dung...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-0 py-3 justify-content-between">
                <div class="text-muted small italic">
                    <i class="fas fa-info-circle me-1"></i> 
                    Nếu không xem được trực tiếp, vui lòng bấm <b>"Tải về"</b> hoặc <b>"Mở tab mới"</b>.
                </div>
                <div class="d-flex gap-2">
                    <a id="preview-open-btn" href="#" target="_blank" class="btn btn-outline-dark fw-bold px-4">
                        <i class="fas fa-external-link-alt me-1"></i> MỞ TRONG TAB MỚI
                    </a>
                    <a id="preview-download-btn" href="#" download class="btn btn-success fw-bold px-4 shadow-sm">
                        <i class="fas fa-download me-1"></i> TẢI VỀ MÁY
                    </a>
                    <button type="button" class="btn btn-light border fw-bold px-4" data-bs-dismiss="modal">ĐÓNG</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CHI TIẾT TÀI LIỆU (PHASE 4) --}}
<div class="modal fade shadow" id="modalViewResource" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div id="res-detail-header" class="modal-header text-white border-0">
                <h5 class="modal-title fw-bold"><i id="res-detail-icon" class="fas me-2"></i> Chi tiết tài liệu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="smaller text-muted d-block mb-1">Tiêu đề</label>
                    <h5 id="res-detail-title" class="fw-bold text-dark mb-0"></h5>
                    <span id="res-detail-badge" class="badge mt-2 border-0 smaller py-1 px-2"></span>
                </div>

                <div class="mb-4">
                    <label class="smaller text-muted d-block mb-1">Mô tả</label>
                    <div id="res-detail-desc" class="p-3 bg-light rounded border small text-dark lh-base"></div>
                </div>

                <div class="mb-4">
                    <label class="smaller text-muted d-block mb-1">URL truy cập (Public URL)</label>
                    <div class="input-group">
                        <input type="text" id="res-detail-url" class="form-control form-control-sm bg-white" readonly>
                        <button class="btn btn-sm btn-outline-primary fw-bold" type="button" id="btn-copy-res-url">
                            <i class="far fa-copy me-1"></i> Copy
                        </button>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="smaller text-muted d-block mb-1">Đường dẫn thực tế trên Server (Debug)</label>
                    <code id="res-detail-path" class="d-block p-2 bg-dark text-light rounded smaller text-break"></code>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 justify-content-center gap-2 bg-light">
                <a id="res-detail-open" href="#" target="_blank" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="fas fa-external-link-alt me-1"></i> MỞ TÀI LIỆU
                </a>
                <a id="res-detail-download" href="#" download class="btn btn-success px-4 fw-bold shadow-sm">
                    <i class="fas fa-download me-1"></i> TẢI VỀ
                </a>
                <button type="button" class="btn btn-light px-4 fw-bold shadow-xs border" data-bs-dismiss="modal">ĐÓNG</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // SIDEBAR TOGGLE (Thu gọn thông tin khóa học)
    // ==========================================
    const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
    const btnShowSidebar = document.getElementById('btn-show-sidebar');
    const courseInfoColumn = document.getElementById('course-info-column');
    const roadmapColumn = document.getElementById('teaching-roadmap-column');
    
    if (btnToggleSidebar && btnShowSidebar) {
        btnToggleSidebar.addEventListener('click', function() {
            courseInfoColumn.classList.add('d-none');
            roadmapColumn.classList.replace('col-lg-8', 'col-lg-12');
            btnShowSidebar.classList.remove('d-none');
            
            // Lưu trạng thái vào localStorage nếu muốn giữ khi reload
            localStorage.setItem('giangvien_sidebar_collapsed', 'true');
        });
        
        btnShowSidebar.addEventListener('click', function() {
            courseInfoColumn.classList.remove('d-none');
            roadmapColumn.classList.replace('col-lg-12', 'col-lg-8');
            btnShowSidebar.classList.add('d-none');
            
            localStorage.setItem('giangvien_sidebar_collapsed', 'false');
        });
        
        // Khôi phục trạng thái từ localStorage
        if (localStorage.getItem('giangvien_sidebar_collapsed') === 'true') {
            courseInfoColumn.classList.add('d-none');
            roadmapColumn.classList.replace('col-lg-8', 'col-lg-12');
            btnShowSidebar.classList.remove('d-none');
        }
    }

    const focusedLichHocId = @json((int) request('focus_lich_hoc_id', 0));
    const quickAction = @json(request('quick_action'));
    // Logic cảnh báo PDF cho Modal Thêm
    const addFileInp = document.getElementById('add-res-file');
    const addPdfRadio = document.getElementById('save_pdf');
    const addPdfWarning = document.getElementById('pdf-warning');

    function checkAddPdfSupport() {
        if (addPdfRadio.checked && addFileInp.files.length > 0) {
            const ext = addFileInp.files[0].name.split('.').pop().toLowerCase();
            if (ext !== 'pdf' && !['jpg', 'jpeg', 'png'].includes(ext)) {
                addPdfWarning.classList.remove('d-none');
            } else {
                addPdfWarning.classList.add('d-none');
            }
        } else {
            addPdfWarning.classList.add('d-none');
        }
    }

    if (addFileInp) {
        addFileInp.addEventListener('change', checkAddPdfSupport);
        document.querySelectorAll('input[name="save_format"]').forEach(r => r.addEventListener('change', checkAddPdfSupport));
    }

    // Logic cảnh báo PDF cho Modal Sửa
    const editFileInp = document.getElementById('edit-res-file');
    const editPdfRadio = document.getElementById('edit_save_pdf');
    const editPdfWarning = document.getElementById('edit-pdf-warning');

    function checkEditPdfSupport() {
        if (editPdfRadio.checked && editFileInp.files.length > 0) {
            const ext = editFileInp.files[0].name.split('.').pop().toLowerCase();
            if (ext !== 'pdf' && !['jpg', 'jpeg', 'png'].includes(ext)) {
                editPdfWarning.classList.remove('d-none');
            } else {
                editPdfWarning.classList.add('d-none');
            }
        } else {
            editPdfWarning.classList.add('d-none');
        }
    }

    if (editFileInp) {
        editFileInp.addEventListener('change', checkEditPdfSupport);
        document.querySelectorAll('#modalEditResource input[name="save_format"]').forEach(r => r.addEventListener('change', checkEditPdfSupport));
    }

    // Xử lý Modal Xem chi tiết tài nguyên
    const modalViewRes = new bootstrap.Modal(document.getElementById('modalViewResource'));
    const btnCopyUrl = document.getElementById('btn-copy-res-url');

    document.querySelectorAll('.btn-view-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            
            // Đổ dữ liệu
            document.getElementById('res-detail-title').textContent = d.title;
            document.getElementById('res-detail-desc').innerHTML = d.desc.replace(/\n/g, '<br>');
            document.getElementById('res-detail-url').value = d.url;
            document.getElementById('res-detail-path').textContent = d.path;
            
            // UI
            const badge = document.getElementById('res-detail-badge');
            badge.textContent = d.loai;
            badge.className = `badge mt-2 border-0 smaller py-1 px-2 bg-${d.color}-soft text-${d.color}`;
            
            const header = document.getElementById('res-detail-header');
            header.className = `modal-header text-white border-0 bg-${d.color}`;
            
            const icon = document.getElementById('res-detail-icon');
            icon.className = `fas ${d.icon} me-2`;

            // Link actions
            const openBtn = document.getElementById('res-detail-open');
            openBtn.href = d.url;
            
            const downloadBtn = document.getElementById('res-detail-download');
            if (d.downloadable === 'true') {
                downloadBtn.style.display = 'inline-block';
                downloadBtn.href = d.url;
                downloadBtn.setAttribute('download', d.filename);
            } else {
                downloadBtn.style.display = 'none';
            }

            modalViewRes.show();
        });
    });

    // Copy URL trong modal
    if (btnCopyUrl) {
        btnCopyUrl.addEventListener('click', function() {
            const urlInput = document.getElementById('res-detail-url');
            urlInput.select();
            document.execCommand('copy');
            
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check me-1"></i> Đã Copy';
            this.classList.replace('btn-outline-primary', 'btn-success');
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.replace('btn-success', 'btn-outline-primary');
            }, 2000);
        });
    }

    // Xử lý Copy Link
    document.querySelectorAll('.btn-copy-link').forEach(btn => {
        btn.addEventListener('click', function() {
            const link = this.dataset.link;
            if (!link) return;
            navigator.clipboard.writeText(link).then(() => {
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check text-success';
                this.classList.add('btn-success', 'bg-opacity-10');
                setTimeout(() => {
                    icon.className = originalClass;
                    this.classList.remove('btn-success', 'bg-opacity-10');
                }, 2000);
            });
        });
    });

    // Xử lý Modal Sửa lịch
    const modalEdit = new bootstrap.Modal(document.getElementById('modalEditLich'));
    const formEdit = document.getElementById('formEditLich');
    const hinhThucSelect = document.getElementById('edit-hinh-thuc');
    const groupOnline = document.getElementById('group-online');
    const groupOffline = document.getElementById('group-offline');

    function toggleHinhThuc() {
        if (hinhThucSelect.value === 'online') {
            groupOnline.style.display = 'block';
            groupOffline.style.display = 'none';
        } else {
            groupOnline.style.display = 'none';
            groupOffline.style.display = 'block';
        }
    }

    hinhThucSelect.addEventListener('change', toggleHinhThuc);

    document.querySelectorAll('.btn-edit-lich').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('edit-buoi-label').textContent = `#${d.buoi}`;
            document.getElementById('edit-hinh-thuc').value = d.hinhthuc;
            document.getElementById('edit-nen-tang').value = d.nentang || '';
            document.getElementById('edit-link').value = d.link || '';
            document.getElementById('edit-meeting-id').value = d.meetingid || '';
            document.getElementById('edit-pass').value = d.pass || '';
            document.getElementById('edit-phong').value = d.phong || '';
            
            formEdit.action = "{{ route('giang-vien.buoi-hoc.update-link', ':id') }}".replace(':id', d.id);
            toggleHinhThuc();
            modalEdit.show();
        });
    });

    // Xử lý Modal Đăng tài nguyên
    const modalRes = new bootstrap.Modal(document.getElementById('modalAddResource'));
    const formRes = document.getElementById('formAddResource');
    const selectedLichHocInput = document.getElementById('selected-lich-hoc-id');

    document.querySelectorAll('.btn-add-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            formRes.reset();
            document.getElementById('res-buoi-label').textContent = d.buoi;
            if (selectedLichHocInput) {
                selectedLichHocInput.value = d.id;
            }
            formRes.action = "{{ route('giang-vien.buoi-hoc.tai-nguyen.store', ':id') }}".replace(':id', d.id);
            modalRes.show();
        });
    });

    // Xử lý Modal Sửa tài nguyên (PHASE 4)
    const modalEditRes = new bootstrap.Modal(document.getElementById('modalEditResource'));
    const formEditRes = document.getElementById('formEditResource');

    document.querySelectorAll('.btn-edit-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('edit-res-type').value = d.type;
            document.getElementById('edit-res-title').value = d.title;
            document.getElementById('edit-res-desc').value = d.desc || '';
            document.getElementById('edit-res-link').value = d.link || '';
            document.getElementById('edit-res-order').value = d.order || 0;
            
            // Set radio status
            if (d.status === 'hien') {
                document.getElementById('edit-status-hien').checked = true;
            } else {
                document.getElementById('edit-status-an').checked = true;
            }

            formEditRes.action = "{{ route('giang-vien.buoi-hoc.tai-nguyen.update', ':id') }}".replace(':id', d.id);
            modalEditRes.show();
        });
    });

    // Xử lý Modal Yêu cầu học viên
    const reqTypeSelect = document.getElementById('req-type');
    const groupAdd = document.getElementById('req-group-add');
    const groupSelect = document.getElementById('req-group-select');

    if (reqTypeSelect) {
        reqTypeSelect.addEventListener('change', function() {
            if (this.value === 'them') {
                groupAdd.style.display = 'block';
                groupSelect.style.display = 'none';
            } else {
                groupAdd.style.display = 'none';
                groupSelect.style.display = 'block';
            }
        });
    }

    const modalDD = new bootstrap.Modal(document.getElementById('modalDiemDanh'));
    const formDD = document.getElementById('formDiemDanh');
    const attendanceList = document.getElementById('attendance-list');
    const attendanceSummary = document.getElementById('attendance-summary');
    const btnSaveDD = document.querySelector('#modalDiemDanh button[type="submit"]');

    document.querySelectorAll('.btn-diem-danh').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('dd-buoi-label').textContent = this.dataset.buoi;
            formDD.action = "{{ route('giang-vien.buoi-hoc.diem-danh.store', ':id') }}".replace(':id', id);
            
            // Hiển thị loading
            attendanceList.innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Đang tải danh sách...</td></tr>';
            if (btnSaveDD) btnSaveDD.style.display = 'none';
            modalDD.show();

            // Load danh sách điểm danh hiện tại bằng AJAX
            const fetchUrl = "{{ route('giang-vien.buoi-hoc.diem-danh.show', ':id') }}".replace(':id', id);
            
            // Cập nhật action cho form báo cáo
            document.getElementById('formBaoCao').action = "{{ route('giang-vien.buoi-hoc.diem-danh.report', ':id') }}".replace(':id', id);

            fetch(fetchUrl)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('dd-ngay-label').textContent = res.ngay;
                        
                        // Đổ dữ liệu báo cáo
                        const baoCaoContent = document.getElementById('dd-bao-cao-content');
                        const statusBadge = document.getElementById('report-status-badge');
                        const btnSubmitReport = document.getElementById('btn-submit-report');
                        const summary = res.summary || {};
                        
                        baoCaoContent.value = res.bao_cao || '';
                        
                        if (res.trang_thai_bao_cao === 'da_bao_cao') {
                            statusBadge.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fw-bold"><i class="fas fa-check-circle me-1"></i> ĐÃ GỬI BÁO CÁO</span>';
                            btnSubmitReport.innerHTML = '<i class="fas fa-sync-alt me-1"></i> CẬP NHẬT CHỐT ATTENDANCE';
                            btnSubmitReport.classList.replace('btn-success', 'btn-warning');
                        } else {
                            statusBadge.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 fw-bold"><i class="fas fa-clock me-1"></i> CHƯA GỬI BÁO CÁO</span>';
                            btnSubmitReport.innerHTML = 'CHỐT ATTENDANCE VÀ GỬI BÁO CÁO';
                            btnSubmitReport.classList.replace('btn-warning', 'btn-success');
                        }

                        if (attendanceSummary) {
                            attendanceSummary.innerHTML = `
                                <span class="badge bg-light text-dark border">Đã chấm ${summary.marked_students || 0}/${summary.total_students || 0}</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Có mặt ${summary.co_mat || 0}</span>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Trễ ${summary.vao_tre || 0}</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Vắng ${summary.vang_mat || 0}</span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Có phép ${summary.co_phep || 0}</span>
                            `;
                        }

                        if (res.data.length > 0) {
                            if (btnSaveDD) btnSaveDD.style.display = 'block';
                            let html = '';
                            res.data.forEach((hv, i) => {
                                html += `
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark small">${hv.ho_ten}</div>
                                            <code class="smaller">#${hv.ma_nguoi_dung}</code>
                                            <input type="hidden" name="attendance[${i}][hoc_vien_id]" value="${hv.ma_nguoi_dung}">
                                        </td>
                                        <td class="text-center">
                                            <select name="attendance[${i}][trang_thai]" class="form-select form-select-sm att-select">
                                                <option value="" ${!hv.trang_thai ? 'selected' : ''}>Chọn trạng thái</option>
                                                <option value="co_mat" ${hv.trang_thai === 'co_mat' ? 'selected' : ''}>Có mặt</option>
                                                <option value="vang_mat" ${hv.trang_thai === 'vang_mat' ? 'selected' : ''}>Vắng</option>
                                                <option value="vao_tre" ${hv.trang_thai === 'vao_tre' ? 'selected' : ''}>Trễ</option>
                                                <option value="co_phep" ${hv.trang_thai === 'co_phep' ? 'selected' : ''}>Có phép</option>
                                            </select>
                                        </td>
                                        <td class="pe-4">
                                            <input type="text" name="attendance[${i}][ghi_chu]" value="${hv.ghi_chu || ''}" class="form-control form-control-sm" placeholder="...">
                                        </td>
                                    </tr>
                                `;
                            });
                            attendanceList.innerHTML = html;
                        } else {
                            attendanceList.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">Không có học viên nào trong khóa học này.</td></tr>';
                            if (btnSaveDD) btnSaveDD.style.display = 'none';
                        }
                    }
                })
                .catch(err => {
                    console.error('Attendance Load Error:', err);
                    attendanceList.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-danger">Không thể tải danh sách. Vui lòng thử lại.</td></tr>';
                    if (btnSaveDD) btnSaveDD.style.display = 'none';
                });
        });
    });

    // Hàm chọn tất cả có mặt
    window.checkAllAttendance = function(status) {
        document.querySelectorAll('.att-select').forEach(select => {
            select.value = status;
        });
    }

    // Xử lý Modal TẠO BÀI KIỂM TRA
    const modalAddTestElement = document.getElementById('modalAddTest');
    if (modalAddTestElement) {
        const modalTest = new bootstrap.Modal(modalAddTestElement);
        const testBuoiLabel = document.getElementById('test-buoi-label');
        const testLichId = document.getElementById('test-lich-id');
        const testPhamVi = document.querySelector('#modalAddTest input[name="pham_vi"]');
        const testModuleId = document.querySelector('#modalAddTest input[name="module_hoc_id"]');

        document.querySelectorAll('.btn-add-test').forEach(btn => {
            btn.addEventListener('click', function() {
                const d = this.dataset;
                testBuoiLabel.textContent = d.buoi;
                testLichId.value = d.id;
                testPhamVi.value = 'buoi_hoc';
                modalTest.show();
            });
        });

        document.querySelectorAll('.btn-add-test-module').forEach(btn => {
            btn.addEventListener('click', function() {
                testBuoiLabel.textContent = 'Cuối Module: {{ $phanCong->moduleHoc->ten_module }}';
                testLichId.value = '';
                testPhamVi.value = 'module';
                modalTest.show();
            });
        });

        document.querySelectorAll('.btn-add-test-course').forEach(btn => {
            btn.addEventListener('click', function() {
                testBuoiLabel.textContent = 'Cuối Khóa: {{ $khoaHoc->ten_khoa_hoc }}';
                testLichId.value = '';
                testPhamVi.value = 'cuoi_khoa';
                modalTest.show();
            });
        });
    }

    function consumeQuickActionParam() {
        if (!quickAction || !window.history.replaceState) {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.delete('quick_action');
        window.history.replaceState({}, document.title, url.toString());
    }

    function openQuickActionModal() {
        if (!focusedLichHocId || !quickAction) {
            return;
        }

        const selectorMap = {
            attendance: `.btn-diem-danh[data-id="${focusedLichHocId}"]`,
            resources: `.btn-add-resource[data-id="${focusedLichHocId}"]`,
            exams: `.btn-add-test[data-id="${focusedLichHocId}"]`,
        };

        const target = document.querySelector(selectorMap[quickAction] || '');

        if (!target) {
            return;
        }

        const sessionBlock = document.getElementById(`session-${focusedLichHocId}`);
        sessionBlock?.scrollIntoView({ behavior: 'smooth', block: 'start' });

        window.setTimeout(() => {
            target.click();
            consumeQuickActionParam();
        }, 180);
    }

    openQuickActionModal();

    // ==========================================
    // XỬ LÝ PREVIEW TÀI LIỆU (PHASE 4 UPGRADE)
    // ==========================================
    const modalPreviewElement = document.getElementById('modalPreviewResource');
    if (modalPreviewElement) {
        const modalPreview = new bootstrap.Modal(modalPreviewElement);
        const previewContainer = document.getElementById('preview-container');
        const previewTitle = document.getElementById('preview-title');
        const previewFilename = document.getElementById('preview-filename');
        const previewOpenBtn = document.getElementById('preview-open-btn');
        const previewDownloadBtn = document.getElementById('preview-download-btn');
        const loadingHtml = document.getElementById('preview-loading') ? document.getElementById('preview-loading').outerHTML : '<p>Loading...</p>';

        document.querySelectorAll('.btn-preview-file').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.dataset.url;
                const title = this.dataset.title;
                let ext = this.dataset.extension ? this.dataset.extension.toLowerCase() : '';
                
                if (!ext) {
                    ext = url.split('.').pop().split(/\#|\?/)[0].toLowerCase();
                }

                console.log('Previewing File:', { title, url, ext });

                previewTitle.textContent = title;
                previewFilename.textContent = url.split('/').pop();
                previewOpenBtn.href = url;
                previewDownloadBtn.href = url;
                previewDownloadBtn.setAttribute('download', title + '.' + ext);

                previewContainer.innerHTML = loadingHtml;
                modalPreview.show();

                setTimeout(() => {
                    let content = '';
                    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) {
                        content = `<div class="p-3 text-center w-100 h-100 d-flex align-items-center justify-content-center">
                                    <img src="${url}" class="img-fluid shadow-sm rounded bg-white" style="max-height: 100%; object-fit: contain;">
                                   </div>`;
                    } else if (ext === 'pdf') {
                        content = `<embed src="${url}#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="100%" height="100%" />`;
                    } else if (['mp4', 'webm', 'ogg'].includes(ext)) {
                        content = `<div class="p-3 w-100 h-100 d-flex align-items-center justify-content-center">
                                    <video controls class="mw-100 mh-100 shadow rounded bg-black" autoplay><source src="${url}" type="video/${ext === 'mp4' ? 'mp4' : ext}">Trình duyệt không hỗ trợ.</video>
                                   </div>`;
                    } else if (['mp3', 'wav', 'ogg'].includes(ext)) {
                        content = `<div class="text-center p-5"><i class="fas fa-file-audio fa-6x text-primary mb-4 d-block"></i><audio controls class="w-100 shadow-sm" style="max-width: 500px;"><source src="${url}" type="audio/mpeg"></audio></div>`;
                    } 
                    // 5. File Văn bản (Word, Excel, WPS...)
                    else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'wps', 'txt'].includes(ext)) {
                        content = `
                            <div class="text-center p-5 bg-white rounded shadow-sm border m-3" style="max-width: 600px;">
                                <div class="mb-4">
                                    <span class="fa-stack fa-4x">
                                        <i class="fas fa-file fa-stack-2x text-primary opacity-10"></i>
                                        <i class="fas fa-file-word fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <h4 class="fw-bold text-dark">Tài liệu .${ext.toUpperCase()}</h4>
                                <p class="text-muted mb-4">
                                    Trình duyệt không hỗ trợ xem trực tiếp định dạng <b>.${ext}</b>.<br>
                                    Vui lòng tải về máy để mở bằng <b>WPS Office</b> hoặc <b>Microsoft Office</b>.
                                </p>
                                <div class="d-grid gap-3">
                                    <a href="${url}" download class="btn btn-primary btn-lg fw-bold shadow-sm">
                                        <i class="fas fa-download me-2"></i> TẢI XUỐNG NGAY
                                    </a>
                                    <div class="text-muted small italic">
                                        <i class="fas fa-lightbulb me-1 text-warning"></i> 
                                        <b>Mẹo:</b> Bạn nên chuyển file sang định dạng <b>PDF</b> trước khi đăng để học viên có thể xem trực tiếp trên web.
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    else {
                        content = `<div class="text-center p-5 bg-white rounded shadow-sm border m-3" style="max-width: 500px;"><div class="mb-4"><span class="fa-stack fa-3x"><i class="fas fa-file fa-stack-2x text-light"></i><i class="fas fa-file-download fa-stack-1x text-primary"></i></span></div><h5 class="fw-bold text-dark">Định dạng .${ext.toUpperCase()}</h5><p class="text-muted small mb-4">Trình duyệt không hỗ trợ xem trực tiếp hoặc đang chạy Local.</p><div class="d-grid gap-2"><a href="${url}" download class="btn btn-success fw-bold py-2 shadow-sm"><i class="fas fa-download me-2"></i>TẢI VỀ</a><a href="${url}" target="_blank" class="btn btn-outline-primary fw-bold py-2"><i class="fas fa-external-link-alt me-2"></i>MỞ TAB MỚI</a></div></div>`;
                    }
                    previewContainer.innerHTML = content;
                }, 500);
            });
        });
    }
});</script>
@endpush

<style>
    :root {
        --primary-soft: rgba(13, 110, 253, 0.08);
        --success-soft: rgba(25, 135, 84, 0.08);
        --info-soft: rgba(13, 202, 240, 0.08);
        --warning-soft: rgba(255, 193, 7, 0.08);
        --danger-soft: rgba(220, 53, 69, 0.08);
        --secondary-soft: rgba(108, 117, 125, 0.08);
        --border-color: #eef2f7;
    }

    .bg-primary-soft { background-color: var(--primary-soft); }
    .bg-success-soft { background-color: var(--success-soft); }
    .bg-info-soft { background-color: var(--info-soft); }
    .bg-warning-soft { background-color: var(--warning-soft); }
    .bg-danger-soft { background-color: var(--danger-soft); }
    .bg-secondary-soft { background-color: var(--secondary-soft); }
    
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.03) !important; }
    .shadow-sm { box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05) !important; }
    .shadow-md { box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important; }
    
    .border-dashed { border-style: dashed !important; }
    .object-fit-cover { object-fit: cover; }
    .btn-xs { padding: 0.25rem 0.6rem; font-size: 0.75rem; border-radius: 6px; }
    .smaller { font-size: 0.75rem; }
    .italic { font-style: italic; }
    .hover-bg-light:hover { background-color: #f8fafc; }
    
    .btn-icon-custom {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .btn-icon-xs {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    /* Session Block & Shell */
    .session-block-focused {
        scroll-margin-top: 100px;
    }
    .session-block-focused .session-shell {
        border: 2px solid #0d6efd !important;
        box-shadow: 0 1rem 3rem rgba(13, 110, 253, 0.15) !important;
    }

    .session-shell {
        border-radius: 1.25rem;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--border-color);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .session-shell:hover {
        transform: translateY(-4px);
        box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, 0.08) !important;
    }

    .session-shell__header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(to right, #fcfdfe, #f8fafc);
    }

    .session-shell__number {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #0d6efd;
        font-weight: 800;
        font-size: 1.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .session-shell__title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.02em;
    }

    .session-shell__body {
        padding: 2rem;
        background-color: #fff;
    }

    /* Cluster Cards */
    .session-cluster-card {
        height: 100%;
        padding: 1.5rem;
        border-radius: 1.25rem;
        border: 1px solid #f1f5f9;
        background: #fbfcfd;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .session-cluster-card:hover {
        background: #fff;
        border-color: #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .session-cluster-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        opacity: 0.6;
    }
    .col-xl-4:nth-child(1) .session-cluster-card::before { background-color: #0d6efd; }
    .col-xl-4:nth-child(2) .session-cluster-card::before { background-color: #0dcaf0; }
    .col-xl-4:nth-child(3) .session-cluster-card::before { background-color: #ffc107; }
    .col-12 .session-cluster-card::before { background-color: #6c757d; }

    .session-cluster-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .session-cluster-card__eyebrow {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.1em;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        background: rgba(0,0,0,0.03);
    }

    .session-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .session-info-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .session-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed #f1f5f9;
        font-size: 0.9rem;
        color: #64748b;
    }
    .session-info-row:last-child { border-bottom: none; }
    .session-info-row strong { color: #1e293b; font-weight: 700; }

    .session-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .session-metric {
        padding: 0.85rem;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
    }
    .session-metric:hover { border-color: #cbd5e1; transform: translateY(-2px); }

    .session-metric__label {
        font-size: 0.65rem;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 0.35rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }
    .session-metric__value {
        color: #1e293b;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .session-note {
        padding: 1.15rem;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #f1f5f9;
        font-size: 0.85rem;
        color: #475569;
        line-height: 1.6;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.01);
    }
    
    .session-note--soft {
        background: rgba(248, 250, 252, 0.6);
        border: 1px dashed #cbd5e1;
    }

    .resource-card {
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        background: #fff;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .resource-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.08);
        transform: translateX(4px);
    }

    .resource-card__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.25rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .exam-pill {
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid #fee2e2;
        background: linear-gradient(to right, #fef2f2, #fff);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        transition: all 0.3s ease;
    }
    .exam-pill:hover {
        border-color: #f87171;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.08);
        transform: translateX(4px);
    }

    .session-overview-banner {
        padding: 1.5rem;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    .session-overview-banner::after {
        content: "\f133";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        right: -10px;
        bottom: -20px;
        font-size: 8rem;
        opacity: 0.03;
        transform: rotate(-15deg);
    }

    .vip-card {
        border-radius: 1.25rem;
        overflow: hidden;
        border: 1px solid var(--border-color);
        background: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    #teaching-roadmap-column {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    #course-info-column.d-none + #teaching-roadmap-column {
        flex: 0 0 100%;
        max-width: 100%;
    }

    @media (max-width: 767.98px) {
        .session-shell__header { padding: 1.25rem 1.5rem; }
        .session-shell__body { padding: 1.25rem; }
        .session-cluster-card { padding: 1.25rem; }
        .session-metric-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection
