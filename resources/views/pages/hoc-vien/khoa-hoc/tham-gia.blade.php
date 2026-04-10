@extends('layouts.app')

@section('title', 'Tham gia khóa học')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tham gia khóa học</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-user-plus fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">Khóa học có thể tham gia</h3>
                    <div class="text-muted small mt-1">Gửi yêu cầu tham gia khóa học để admin xem xét và phê duyệt.</div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="vip-card border-0 shadow-sm p-3 h-100">
                <div class="text-muted text-uppercase smaller fw-bold">Có thể tham gia</div>
                <div class="fs-3 fw-bold text-dark">{{ $stats['co_the_tham_gia'] }}</div>
                <div class="small text-muted">Khóa học đang mở để gửi yêu cầu tham gia</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="vip-card border-0 shadow-sm p-3 h-100">
                <div class="text-muted text-uppercase smaller fw-bold">Đang chờ duyệt</div>
                <div class="fs-3 fw-bold text-warning">{{ $stats['dang_cho_duyet'] }}</div>
                <div class="small text-muted">Yêu cầu tham gia chưa được xử lý</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="vip-card border-0 shadow-sm p-3 h-100">
                <div class="text-muted text-uppercase smaller fw-bold">Đã gửi yêu cầu</div>
                <div class="fs-3 fw-bold text-primary">{{ $stats['da_gui'] }}</div>
                <div class="small text-muted">Tổng số yêu cầu tham gia đã gửi</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-graduation-cap me-2 text-primary"></i>Danh sách khóa học mở
                </h5>
                <span class="badge bg-light text-dark border">{{ $khoaHocs->total() }} khóa học</span>
            </div>

            <div class="row">
                @forelse($khoaHocs as $khoaHoc)
                    @php $dangCho = in_array($khoaHoc->id, $dangChoDuyetIds); @endphp
                    <div class="col-xl-6 mb-4">
                        <div class="card vip-card border-0 shadow-sm h-100 overflow-hidden hover-lift">
                            <div class="position-relative">
                                <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}"
                                     alt="{{ $khoaHoc->ten_khoa_hoc }}"
                                     class="card-img-top object-fit-cover" style="height: 190px;">
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-{{ $khoaHoc->badge_trang_thai }} shadow-sm px-3 py-2">
                                        {{ $khoaHoc->label_trang_thai_van_hanh }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-primary-soft text-primary small border-0">
                                        {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'Chưa phân nhóm' }}
                                    </span>
                                </div>

                                <h5 class="fw-bold text-dark mb-2">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                                <div class="small text-muted mb-3">
                                    <i class="fas fa-fingerprint me-1"></i>{{ $khoaHoc->ma_khoa_hoc }}
                                </div>

                                <p class="text-muted small mb-3 line-clamp-3">
                                    {{ $khoaHoc->mo_ta_ngan ?: 'Khóa học đang mở cho học viên gửi yêu cầu tham gia.' }}
                                </p>

                                <div class="row g-2 small text-muted mb-4">
                                    <div class="col-6"><i class="far fa-calendar-alt me-1"></i> Khai giảng:</div>
                                    <div class="col-6 text-end fw-bold text-dark">{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?: '—' }}</div>

                                    <div class="col-6"><i class="fas fa-layer-group me-1"></i> Module:</div>
                                    <div class="col-6 text-end fw-bold text-dark">{{ $khoaHoc->module_hocs_count }}</div>

                                    <div class="col-6"><i class="fas fa-users me-1"></i> Học viên:</div>
                                    <div class="col-6 text-end fw-bold text-dark">{{ $khoaHoc->hoc_vien_dang_hoc_count }}</div>
                                </div>

                                <div class="mt-auto d-grid gap-2">
                                    @if($dangCho)
                                        <button type="button" class="btn btn-secondary fw-bold" disabled>
                                            <i class="fas fa-clock me-2"></i>Đã gửi yêu cầu
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalXinThamGia{{ $khoaHoc->id }}">
                                            <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu tham gia
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @unless($dangCho)
                        <div class="modal fade shadow" id="modalXinThamGia{{ $khoaHoc->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0">
                                    <div class="modal-header bg-primary text-white border-0">
                                        <h5 class="modal-title fw-bold">Gửi yêu cầu tham gia khóa học</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('hoc-vien.khoa-hoc.gui-yeu-cau-tham-gia', $khoaHoc->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Khóa học</label>
                                                <input type="text" class="form-control" value="{{ $khoaHoc->ten_khoa_hoc }}" readonly>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label small fw-bold">Lý do xin tham gia *</label>
                                                <textarea name="ly_do" class="form-control vip-form-control" rows="4" placeholder="Ví dụ: Em muốn tham gia để bổ sung kiến thức và theo học cùng lớp..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                                            <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">GỬI YÊU CẦU</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endunless
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="bg-light d-inline-block p-4 rounded-circle mb-3">
                            <i class="fas fa-inbox fa-3x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted">Hiện chưa có khóa học nào đang mở để xin tham gia.</h5>
                        <p class="text-muted small mb-0">Bạn có thể quay lại sau hoặc liên hệ admin để được hỗ trợ ghi danh.</p>
                    </div>
                @endforelse
            </div>

            @if($khoaHocs->hasPages())
                <div class="d-flex justify-content-center mt-2">
                    {{ $khoaHocs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card vip-card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Lịch sử yêu cầu của bạn</h6>
                </div>
                <div class="card-body p-0">
                    @forelse($yeuCauDaGui as $yeuCau)
                        <div class="p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-bold text-dark">{{ $yeuCau->khoaHoc->ten_khoa_hoc ?? 'Khóa học không còn tồn tại' }}</div>
                                    <div class="small text-muted mt-1">{{ $yeuCau->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <span class="badge bg-{{ $yeuCau->trang_thai_badge }}">{{ $yeuCau->trang_thai_label }}</span>
                            </div>

                            <div class="small text-muted mt-2">
                                <div><strong>Lý do:</strong> {{ $yeuCau->ly_do }}</div>
                                @if($yeuCau->phan_hoi_admin)
                                    <div class="mt-1"><strong>Phản hồi admin:</strong> {{ $yeuCau->phan_hoi_admin }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted small">
                            Bạn chưa gửi yêu cầu tham gia khóa học nào.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; }
    .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
