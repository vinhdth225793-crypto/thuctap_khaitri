@extends('layouts.app')

@section('title', 'Chi tiết bài giảng: ' . $phanCong->moduleHoc->ten_module)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc') }}">Lộ trình dạy</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết bài dạy</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-book-open fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $phanCong->moduleHoc->ten_module }}</h3>
                    <div class="text-muted small mt-1">
                        Thuộc khóa học: <span class="fw-bold text-primary">{{ $khoaHoc->ten_khoa_hoc }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if($phanCong->trang_thai === 'cho_xac_nhan')
                <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $phanCong->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="hanh_dong" value="da_nhan">
                    <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm">XÁC NHẬN DẠY</button>
                </form>
            @else
                <div class="badge bg-success-soft text-success border border-success px-3 py-2 shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> Bạn đã nhận bài dạy này
                </div>
            @endif
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <!-- Cột trái: Lịch dạy & Nội dung -->
        <div class="col-lg-8">
            {{-- LỊCH DẠY CHI TIẾT --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-primary">
                        <i class="fas fa-calendar-check me-2"></i> Lịch dạy của bạn
                    </h5>
                    <span class="badge bg-light text-dark border">{{ $lichDays->count() }} buổi dạy</span>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4" width="80">Buổi</th>
                                    <th>Ngày dạy</th>
                                    <th>Thứ</th>
                                    <th class="text-center">Thời gian</th>
                                    <th>Phòng / Link họp</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="pe-4 text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lichDays as $index => $lich)
                                    <tr>
                                        <td class="ps-4 fw-bold text-muted">#{{ $lich->buoi_so }}</td>
                                        <td class="fw-bold">{{ $lich->ngay_hoc->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-light text-dark border px-2">{{ $lich->thu_label }}</span></td>
                                        <td class="text-center">
                                            <code class="text-primary fw-bold">{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</code>
                                        </td>
                                        <td>
                                            @if($lich->hinh_thuc === 'online')
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($lich->link_online)
                                                        <a href="{{ $lich->link_online }}" target="_blank" class="text-info fw-bold text-decoration-none small text-truncate" style="max-width: 120px;" title="{{ $lich->link_online }}">
                                                            <i class="fas fa-video me-1"></i> {{ $lich->link_online }}
                                                        </a>
                                                        <button type="button" class="btn btn-xs btn-light border shadow-xs btn-copy-link" data-link="{{ $lich->link_online }}" title="Copy link">
                                                            <i class="far fa-copy text-primary"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted italic smaller">Chưa cập nhật link</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-dark small"><i class="fas fa-door-open me-1 text-muted"></i>{{ $lich->phong_hoc ?: 'Chưa gán' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $lich->trang_thai_color }}-soft text-dark smaller">
                                                {{ $lich->trang_thai_label }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-1">
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
                                        </td>
                                    </tr>
                                    {{-- Hiển thị bài kiểm tra của buổi này (nếu có) --}}
                                    @if($lich->baiKiemTras->count() > 0)
                                        <tr class="table-light">
                                            <td colspan="7" class="ps-5 py-2">
                                                @foreach($lich->baiKiemTras as $test)                                                    <div class="d-flex align-items-center justify-content-between smaller border-bottom pb-1 mb-1">
                                                        <span class="text-danger fw-bold">
                                                            <i class="fas fa-file-alt me-1"></i> BÀI KIỂM TRA: {{ $test->tieu_de }} 
                                                            <span class="text-muted fw-normal">({{ $test->thoi_gian_lam_bai }} phút)</span>
                                                        </span>
                                                        <div class="d-flex gap-2">
                                                            <a href="#" class="text-primary text-decoration-none"><i class="fas fa-tasks me-1"></i>Câu hỏi</a>
                                                            <form action="{{ route('giang-vien.bai-kiem-tra.destroy', $test->id) }}" method="POST" onsubmit="return confirm('Xóa bài kiểm tra này?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-link p-0 text-danger smaller"><i class="fas fa-trash-alt"></i></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted italic">
                                            <i class="fas fa-calendar-times fa-2x mb-2 d-block opacity-25"></i>
                                            Chưa có lịch dạy cụ thể cho bài dạy này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

           

            {{-- DANH SÁCH TÀI LIỆU (Dời từ bảng lịch dạy xuống đây) --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-primary">
                        <i class="fas fa-folder-open me-2"></i> Danh sách tài liệu học tập
                    </h5>
                    @php
                        $totalResources = $lichDays->sum(fn($l) => $l->taiNguyen->count());
                    @endphp
                    <span class="badge bg-primary-soft text-primary border border-primary px-3">{{ $totalResources }} tài liệu</span>
                </div>
                <div class="vip-card-body p-4">
                    @if($totalResources > 0)
                        <div class="row g-3">
                            @foreach($lichDays as $lich)
                                @if($lich->taiNguyen->count() > 0)
                                    @foreach($lich->taiNguyen->sortBy('thu_tu_hien_thi') as $tn)
                                        <div class="col-12">
                                            <div class="d-flex align-items-start p-3 rounded border bg-white shadow-xs hover-bg-light transition-all">
                                                <div class="bg-{{ $tn->loai_color }}-soft rounded text-{{ $tn->loai_color }} d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; flex-shrink: 0;">
                                                    <i class="fas {{ $tn->loai_icon }} fa-lg"></i>
                                                </div>
                                                <div class="flex-fill min-w-0 me-3">
                                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                        <span class="badge bg-dark text-white border-0 smaller py-1 px-2">Buổi #{{ $lich->buoi_so }}</span>
                                                        <span class="badge bg-{{ $tn->loai_color }}-soft text-{{ $tn->loai_color }} border-0 smaller py-1 px-2">{{ $tn->loai_label }}</span>
                                                        <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $tn->tieu_de }}</h6>
                                                        @if($tn->trang_thai_hien_thi === 'an')
                                                            <span class="badge bg-secondary-soft text-secondary border-0 px-2" style="font-size: 0.65rem;"><i class="fas fa-eye-slash me-1"></i> Đang ẩn</span>
                                                        @else
                                                            <span class="badge bg-success-soft text-success border-0 px-2" style="font-size: 0.65rem;"><i class="fas fa-eye me-1"></i> Công khai</span>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="d-flex align-items-center flex-wrap gap-3 mt-1">
                                                        @if($tn->original_file_name)
                                                            <small class="smaller text-muted" title="File gốc: {{ $tn->original_file_name }}">
                                                                <i class="fas fa-paperclip me-1"></i> {{ \Illuminate\Support\Str::limit($tn->original_file_name, 30) }}
                                                            </small>
                                                        @elseif($tn->link_ngoai)
                                                            <small class="smaller text-info text-truncate" style="max-width: 250px;" title="{{ $tn->link_ngoai }}">
                                                                <i class="fas fa-link me-1"></i> {{ $tn->link_ngoai }}
                                                            </small>
                                                        @endif
                                                        <small class="smaller text-muted"><i class="far fa-clock me-1"></i> Cập nhật: {{ $tn->updated_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                    
                                                    <div class="d-flex align-items-center gap-2 mt-3">
                                                        <button type="button" class="btn btn-xs btn-primary fw-bold py-1 px-3 btn-view-resource"
                                                                data-title="{{ $tn->tieu_de }}"
                                                                data-loai="{{ $tn->loai_label }}"
                                                                data-desc="{{ $tn->mo_ta ?: 'Không có mô tả.' }}"
                                                                data-url="{{ $tn->file_url }}"
                                                                data-path="{{ $tn->storage_path }}"
                                                                data-color="{{ $tn->loai_color }}"
                                                                data-icon="{{ $tn->loai_icon }}"
                                                                data-downloadable="{{ $tn->is_downloadable ? 'true' : 'false' }}"
                                                                data-filename="{{ $tn->original_file_name }}">
                                                            <i class="fas fa-info-circle me-1"></i> Xem chi tiết
                                                        </button>
                                                        <button type="button" class="btn btn-xs btn-outline-primary fw-bold py-1 px-3 btn-preview-file"
                                                                data-url="{{ $tn->file_url }}"
                                                                data-title="{{ $tn->tieu_de }}"
                                                                data-extension="{{ pathinfo($tn->file_url, PATHINFO_EXTENSION) }}">
                                                            <i class="fas fa-eye me-1"></i> Xem file
                                                        </button>
                                                        @if($tn->is_downloadable)
                                                            <a href="{{ $tn->file_url }}" download="{{ $tn->original_file_name }}" class="btn btn-xs btn-outline-success fw-bold py-1 px-3">
                                                                <i class="fas fa-download me-1"></i> Tải về
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ms-auto align-items-center bg-light p-2 rounded">
                                                    <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.toggle', $tn->id) }}" method="POST">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="btn btn-link p-0 {{ $tn->trang_thai_hien_thi === 'hien' ? 'text-success' : 'text-secondary' }}" 
                                                                title="{{ $tn->trang_thai_hien_thi === 'hien' ? 'Nhấn để ẩn' : 'Nhấn để hiện' }}">
                                                            <i class="fas {{ $tn->trang_thai_hien_thi === 'hien' ? 'fa-toggle-on' : 'fa-toggle-off' }} fa-lg"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-link p-0 text-warning btn-edit-resource" 
                                                            data-id="{{ $tn->id }}" 
                                                            data-type="{{ $tn->loai_tai_nguyen }}"
                                                            data-title="{{ $tn->tieu_de }}"
                                                            data-desc="{{ $tn->mo_ta }}"
                                                            data-link="{{ $tn->link_ngoai }}"
                                                            data-status="{{ $tn->trang_thai_hien_thi }}"
                                                            data-order="{{ $tn->thu_tu_hien_thi }}"
                                                            title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.destroy', $tn->id) }}" method="POST" onsubmit="return confirm('Xóa tài liệu này?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-link p-0 text-danger" title="Xóa bỏ"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted italic">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
                            Chưa có tài liệu nào được đăng cho các buổi học.
                        </div>
                    @endif
                </div>
            </div>
             {{-- MÔ TẢ MODULE --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-dark">
                        <i class="fas fa-info-circle me-2 text-info"></i> Mô tả nội dung bài dạy
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="bg-light p-3 rounded border border-dashed text-dark lh-lg">
                        {!! $phanCong->moduleHoc->mo_ta ? nl2br(e($phanCong->moduleHoc->mo_ta)) : '<span class="text-muted italic">Chưa có mô tả chi tiết cho bài học này.</span>' !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin Khóa học & Học viên -->
        <div class="col-lg-4">
            {{-- CARD KHÓA HỌC --}}
            <div class="vip-card mb-4 shadow-sm border-0 overflow-hidden">
                <div class="vip-card-header bg-primary text-white py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Thông tin khóa học</h5>
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
                </div>
            </div>

            {{-- CARD HỌC VIÊN (Phase 5 Upgrade) --}}
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
                                        <div class="fw-bold text-dark text-truncate mb-0" title="{{ $bghv->hocVien->ho_ten }}">
                                            {{ $bghv->hocVien->ho_ten }}
                                        </div>
                                        <div class="smaller text-muted d-flex flex-column gap-1 mt-1">
                                            <span class="text-truncate"><i class="far fa-envelope me-1"></i>{{ $bghv->hocVien->email }}</span>
                                            <span><i class="fas fa-phone-alt me-1"></i>{{ $bghv->hocVien->so_dien_thoai ?: 'N/A' }}</span>
                                            <span class="fw-bold"><i class="far fa-calendar-alt me-1"></i>Tham gia: {{ $bghv->ngay_tham_gia?->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <span class="badge {{ $bghv->trang_thai_badge }} shadow-xs" style="font-size: 0.6rem;">
                                            {{ $bghv->trang_thai_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small italic">Chưa có học viên ghi danh.</div>
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
            <form id="formDiemDanh" method="POST" action="">
                @csrf
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <span class="small text-muted fw-bold">Ngày dạy: <span id="dd-ngay-label" class="text-dark"></span></span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-xs btn-outline-success" onclick="checkAllAttendance('co_mat')">Tất cả Có mặt</button>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
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
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Loại tài nguyên *</label>
                            <select name="loai_tai_nguyen" class="form-select vip-form-control" required>
                                <option value="bai_giang">Bài giảng (Slide/Video)</option>
                                <option value="tai_lieu">Tài liệu tham khảo</option>
                                <option value="bai_tap">Bài tập về nhà</option>
                                <option value="link_ngoai">Link liên kết ngoài</option>
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
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Tải lên file (Tối đa 20MB) *</label>
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
                                <option value="link_ngoai">Link liên kết ngoài</option>
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

    document.querySelectorAll('.btn-add-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('res-buoi-label').textContent = d.buoi;
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

    // Xử lý Modal ĐIỂM DANH (PHASE 7)
    const modalDD = new bootstrap.Modal(document.getElementById('modalDiemDanh'));
    const formDD = document.getElementById('formDiemDanh');
    const attendanceList = document.getElementById('attendance-list');

    document.querySelectorAll('.btn-diem-danh').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('dd-buoi-label').textContent = this.dataset.buoi;
            formDD.action = "{{ route('giang-vien.buoi-hoc.diem-danh.store', ':id') }}".replace(':id', id);
            
            // Load danh sách điểm danh hiện tại bằng AJAX
            const fetchUrl = "{{ route('giang-vien.buoi-hoc.diem-danh.show', ':id') }}".replace(':id', id);
            fetch(fetchUrl)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('dd-ngay-label').textContent = res.ngay;
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
                                            <option value="co_mat" ${hv.trang_thai === 'co_mat' ? 'selected' : ''}>Có mặt</option>
                                            <option value="vang_mat" ${hv.trang_thai === 'vang_mat' ? 'selected' : ''}>Vắng</option>
                                            <option value="vao_tre" ${hv.trang_thai === 'vao_tre' ? 'selected' : ''}>Trễ</option>
                                        </select>
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" name="attendance[${i}][ghi_chu]" value="${hv.ghi_chu}" class="form-control form-control-sm" placeholder="...">
                                    </td>
                                </tr>
                            `;
                        });
                        attendanceList.innerHTML = html;
                        modalDD.show();
                    }
                });
        });
    });

    // Xử lý Modal TẠO BÀI KIỂM TRA
    const modalAddTestElement = document.getElementById('modalAddTest');
    if (modalAddTestElement) {
        const modalTest = new bootstrap.Modal(modalAddTestElement);
        document.querySelectorAll('.btn-add-test').forEach(btn => {
            btn.addEventListener('click', function() {
                const d = this.dataset;
                document.getElementById('test-buoi-label').textContent = d.buoi;
                document.getElementById('test-lich-id').value = d.id;
                modalTest.show();
            });
        });
    }

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
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .border-dashed { border-style: dashed !important; }
    .object-fit-cover { object-fit: cover; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; }
    .smaller { font-size: 0.75rem; }
    .italic { font-style: italic; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .btn-icon-custom {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 6px;
    }
</style>
@endsection
