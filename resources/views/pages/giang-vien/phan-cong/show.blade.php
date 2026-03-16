@extends('layouts.app')

@section('title', 'Chi tiết giảng dạy: ' . $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc') }}">Phân công dạy học</a></li>
            <li class="breadcrumb-item active">{{ $khoaHoc->ma_khoa_hoc }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                Chi tiết lớp học: {{ $khoaHoc->ten_khoa_hoc }}
            </h4>
            <div class="small text-muted">
                Mã lớp: <code class="fw-bold">{{ $khoaHoc->ma_khoa_hoc }}</code> |
                Module bạn phụ trách: <span class="fw-bold text-dark">{{ $phanCong->moduleHoc->ten_module }}</span>
            </div>
        </div>
        <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Cột trái: Danh sách học viên & Lịch dạy -->
        <div class="col-lg-8">
            {{-- CARD LỊCH DẠY CỦA BẠN --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-primary">
                        <i class="fas fa-calendar-check me-2"></i> Lịch dạy của bạn
                    </h5>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="ps-4" width="80">Buổi số</th>
                                    <th>Ngày dạy</th>
                                    <th>Thứ</th>
                                    <th class="text-center">Thời gian</th>
                                    <th>Địa điểm / Link</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lichDays as $lich)
                                    <tr>
                                        <td class="ps-4 fw-bold">#{{ $lich->buoi_so }}</td>
                                        <td class="fw-bold">{{ $lich->ngay_hoc->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $lich->thu_label }}</span></td>
                                        <td class="text-center">
                                            <code class="text-dark">{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</code>
                                        </td>
                                        <td>
                                            @if($lich->hinh_thuc === 'online')
                                                <span class="text-info"><i class="fas fa-globe me-1"></i> Online</span>
                                                @if($lich->link_online)
                                                    <a href="{{ $lich->link_online }}" target="_blank" class="ms-1 small text-decoration-none">[Vào lớp]</a>
                                                @endif
                                            @else
                                                <span class="text-dark"><i class="fas fa-door-open me-1"></i> {{ $lich->phong_hoc ?: 'Chưa gán phòng' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $lich->trang_thai === 'hoan_thanh' ? 'success' : ($lich->trang_thai === 'dang_hoc' ? 'info' : 'secondary') }}">
                                                {{ $lich->trang_thai_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted italic">Chưa có lịch dạy cụ thể cho module này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CARD DANH SÁCH HỌC VIÊN --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
                        <i class="fas fa-users me-2"></i> Danh sách học viên lớp học
                    </h5>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="ps-4 text-center" width="60">STT</th>
                                    <th>Họ tên học viên</th>
                                    <th>Số điện thoại</th>
                                    <th>Email</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($khoaHoc->hocVienKhoaHocs as $index => $bghv)
                                    <tr>
                                        <td class="text-center ps-4 text-muted">{{ $index + 1 }}</td>
                                        <td><span class="fw-bold text-dark">{{ $bghv->hocVien->ho_ten ?? 'N/A' }}</span></td>
                                        <td>{{ $bghv->hocVien->so_dien_thoai ?? '─' }}</td>
                                        <td>{{ $bghv->hocVien->email ?? '─' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $bghv->trang_thai_badge }} shadow-xs">
                                                {{ $bghv->trang_thai_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted italic">Lớp học hiện chưa có học viên.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin khóa học & Trạng thái phân công -->
        <div class="col-lg-4">
            {{-- CARD TRẠNG THÁI PHÂN CÔNG --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Xác nhận của bạn</h5>
                </div>
                <div class="vip-card-body p-4">
                    @if($phanCong->trang_thai === 'cho_xac_nhan')
                        <div class="alert alert-warning border-0 smaller mb-4">
                            Bạn vui lòng xác nhận việc đảm nhận giảng dạy module này để Admin chính thức mở lớp.
                        </div>
                        <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $phanCong->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Ghi chú phản hồi</label>
                                <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="Lời nhắn gửi tới Admin..."></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="hanh_dong" value="da_nhan" class="btn btn-primary fw-bold py-2 shadow-sm">
                                    <i class="fas fa-check-circle me-1"></i> ĐỒNG Ý DẠY
                                </button>
                                <button type="submit" name="hanh_dong" value="tu_choi" class="btn btn-outline-danger fw-bold py-2" onclick="return confirm('Bạn chắc chắn muốn từ chối?')">
                                    <i class="fas fa-times-circle me-1"></i> TỪ CHỐI
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-2">
                            <div class="mb-3">
                                <span class="badge {{ $phanCong->trang_thai === 'da_nhan' ? 'bg-success' : 'bg-danger' }} fs-6 px-4 py-2 shadow-sm">
                                    {{ $phanCong->trang_thai === 'da_nhan' ? 'Đã xác nhận đồng ý dạy' : 'Đã từ chối' }}
                                </span>
                            </div>
                            @if($phanCong->ghi_chu)
                                <div class="bg-light p-3 rounded border italic small text-muted">
                                    "{{ $phanCong->ghi_chu }}"
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- CARD THÔNG TIN KHÓA HỌC --}}
            <div class="vip-card mb-4 border-0 shadow-sm overflow-hidden">
                <div class="vip-card-header bg-dark text-white py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Thông tin khóa học</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="mb-3">
                        <span class="smaller text-muted fw-bold d-block text-uppercase">Nhóm ngành</span>
                        <span class="small fw-bold text-dark">{{ $khoaHoc->nhomNganh->ten_nhom_nganh }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="smaller text-muted fw-bold d-block text-uppercase">Khai giảng</span>
                        <span class="small fw-bold text-dark">{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? '─' }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="smaller text-muted fw-bold d-block text-uppercase">Ngày mở lớp</span>
                        <span class="small fw-bold text-success">{{ $khoaHoc->ngay_mo_lop?->format('d/m/Y') ?? '─' }}</span>
                    </div>
                    <div class="mb-0">
                        <span class="smaller text-muted fw-bold d-block text-uppercase">Ngày kết thúc</span>
                        <span class="small fw-bold text-danger">{{ $khoaHoc->ngay_ket_thuc?->format('d/m/Y') ?? '─' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .italic { font-style: italic; }
    .smaller { font-size: 0.75rem; }
</style>
@endsection
