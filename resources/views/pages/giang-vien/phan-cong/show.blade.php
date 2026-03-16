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
                <div class="badge bg-success-soft text-success border border-success px-3 py-2">
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
                        <i class="fas fa-calendar-check me-2"></i> Lịch dạy của bạn cho Module này
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
                                                    <a href="{{ $lich->link_online }}" target="_blank" class="text-info fw-bold text-decoration-none small text-truncate" style="max-width: 150px;" title="{{ $lich->link_online }}">
                                                        <i class="fas fa-video me-1"></i> {{ $lich->link_online }}
                                                    </a>
                                                    <button type="button" class="btn btn-xs btn-light border shadow-xs btn-copy-link" data-link="{{ $lich->link_online }}" title="Copy link">
                                                        <i class="far fa-copy text-primary"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-dark small"><i class="fas fa-door-open me-1 text-muted"></i>{{ $lich->phong_hoc ?: 'Chưa gán' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $lich->trang_thai_color }}-soft text-{{ $lich->trang_thai_color }} smaller">
                                                {{ $lich->trang_thai_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted italic">
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

            {{-- CARD HỌC VIÊN --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
                        <i class="fas fa-users me-2"></i> Danh sách lớp
                    </h5>
                    <span class="badge bg-success-soft text-success rounded-pill">{{ $khoaHoc->hocVienKhoaHocs->count() }} HV</span>
                </div>
                <div class="vip-card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @forelse($khoaHoc->hocVienKhoaHocs as $bghv)
                            <div class="list-group-item px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-mini rounded-circle bg-light border text-center me-3" style="width: 35px; height: 35px; line-height: 35px;">
                                        <i class="fas fa-user text-muted small"></i>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="small fw-bold text-dark">{{ $bghv->hocVien->ho_ten }}</div>
                                        <div class="smaller text-muted">{{ $bghv->hocVien->email }}</div>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $bghv->trang_thai === 'dang_hoc' ? 'success' : 'secondary' }} rounded-circle p-1" title="{{ $bghv->trang_thai_label }}">
                                            <span class="visually-hidden">Trạng thái</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small italic">Chưa có học viên ghi danh.</div>
                        @endforelse
                    </div>
                </div>
            </div>
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
                // Hiển thị tooltip hoặc đổi icon tạm thời
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                
                icon.className = 'fas fa-check text-success';
                this.classList.add('btn-success', 'bg-opacity-10');
                
                setTimeout(() => {
                    icon.className = originalClass;
                    this.classList.remove('btn-success', 'bg-opacity-10');
                }, 2000);
            }).catch(err => {
                alert('Không thể copy link: ' + err);
            });
        });
    });
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
</style>
@endsection
