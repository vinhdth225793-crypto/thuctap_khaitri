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

                                            {{-- Hiển thị tài nguyên đã đăng (Phase 4) --}}
                                            @if($lich->taiNguyen->count() > 0)
                                                <div class="mt-2 pt-2 border-top border-light">
                                                    @foreach($lich->taiNguyen as $tn)
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <a href="{{ $tn->duong_dan_file ? asset('storage/'.$tn->duong_dan_file) : ($tn->link_ngoai ?: '#') }}" 
                                                               target="_blank" class="smaller text-dark text-decoration-none">
                                                                @php
                                                                    $icon = match($tn->loai_tai_nguyen) {
                                                                        'bai_giang' => 'fa-chalkboard',
                                                                        'tai_lieu'  => 'fa-file-alt',
                                                                        'bai_tap'   => 'fa-pencil-alt',
                                                                        default     => 'fa-paperclip'
                                                                    };
                                                                @endphp
                                                                <i class="fas {{ $icon }} text-muted me-1"></i> {{ $tn->tieu_de }}
                                                            </a>
                                                            <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.destroy', $tn->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa tài liệu này?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-link p-0 text-danger smaller"><i class="fas fa-times"></i></button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
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
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Loại tài nguyên *</label>
                        <select name="loai_tai_nguyen" class="form-select vip-form-control" required>
                            <option value="bai_giang">Bài giảng (Slide/Video)</option>
                            <option value="tai_lieu">Tài liệu tham khảo</option>
                            <option value="bai_tap">Bài tập về nhà</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tiêu đề tài liệu *</label>
                        <input type="text" name="tieu_de" class="form-control vip-form-control" placeholder="VD: Slide buổi 1 - Giới thiệu Laravel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mô tả ngắn</label>
                        <textarea name="mo_ta" class="form-control vip-form-control" rows="2" placeholder="Tóm tắt nội dung tài liệu..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Link ngoài (Youtube/Drive/...)</label>
                        <input type="url" name="link_ngoai" class="form-control vip-form-control" placeholder="https://...">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Tải lên file (Tối đa 10MB)</label>
                        <input type="file" name="file_dinh_kem" class="form-control vip-form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">ĐĂNG TÀI LIỆU</button>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});

function checkAllAttendance(status) {
    document.querySelectorAll('.att-select').forEach(sel => sel.value = status);
}

// Xử lý Modal TẠO BÀI KIỂM TRA
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
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
