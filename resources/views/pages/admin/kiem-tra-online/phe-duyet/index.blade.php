@extends('layouts.app', ['title' => 'Phê duyệt bài kiểm tra'])

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-clipboard-check me-2 text-primary"></i>Phê duyệt bài kiểm tra</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Phê duyệt đề thi</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-light border-0" placeholder="Tiêu đề đề thi, nội dung mô tả...">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select bg-light border-0">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(['nhap' => 'Nháp', 'cho_duyet' => 'Chờ duyệt', 'da_duyet' => 'Đã duyệt', 'tu_choi' => 'Từ chối'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_duyet') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Trạng thái phát hành</label>
                    <select name="trang_thai_phat_hanh" class="form-select bg-light border-0">
                        <option value="">Tất cả phát hành</option>
                        @foreach(['nhap' => 'Nháp', 'phat_hanh' => 'Phát hành', 'dong' => 'Đóng'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_phat_hanh') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-light border-0 bg-light w-100 fw-bold rounded-3" title="Đặt lại">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table List -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Thông tin đề thi</th>
                        <th class="py-3">Phạm vi / Khóa học</th>
                        <th class="py-3 text-center">GV tạo</th>
                        <th class="py-3 text-center">Câu hỏi</th>
                        <th class="py-3 text-center">Duyệt</th>
                        <th class="py-3 text-center">Phát hành</th>
                        <th class="pe-4 py-3 text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($baiKiemTras as $baiKiemTra)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark fs-6">{{ $baiKiemTra->tieu_de }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-soft-info text-info rounded-pill px-2" style="font-size: 0.7rem;">
                                        <i class="far fa-clock me-1"></i>{{ $baiKiemTra->thoi_gian_lam_bai }} phút
                                    </span>
                                    <span class="badge bg-soft-secondary text-secondary rounded-pill px-2" style="font-size: 0.7rem;">
                                        <i class="fas fa-tag me-1"></i>{{ $baiKiemTra->loai_noi_dung_label }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small text-truncate" style="max-width: 250px;">
                                    {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $baiKiemTra->moduleHoc->ten_module ?? 'Đề cuối khóa / dùng chung' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ substr($baiKiemTra->nguoiTao->ho_ten ?? '?', 0, 1) }}
                                    </div>
                                    <div class="text-start">
                                        <div class="fw-bold small">{{ $baiKiemTra->nguoiTao->ho_ten ?? 'Không rõ' }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $baiKiemTra->updated_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($baiKiemTra->content_mode_key === 'tu_luan_tu_do')
                                    <span class="badge bg-soft-primary text-primary border border-primary-subtle px-2">Tự luận</span>
                                @else
                                    <span class="fw-bold text-primary">{{ $baiKiemTra->chi_tiet_cau_hois_count }}</span>
                                    <div class="text-muted small">câu hỏi</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $duyetClass = match($baiKiemTra->trang_thai_duyet) {
                                        'da_duyet' => 'bg-success',
                                        'cho_duyet' => 'bg-warning',
                                        'tu_choi' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $duyetClass }} rounded-pill px-3 py-2 shadow-xs">
                                    {{ $baiKiemTra->trang_thai_duyet_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $phatHanhClass = match($baiKiemTra->trang_thai_phat_hanh) {
                                        'phat_hanh' => 'text-success',
                                        'dong' => 'text-danger',
                                        default => 'text-muted'
                                    };
                                    $phatHanhIcon = match($baiKiemTra->trang_thai_phat_hanh) {
                                        'phat_hanh' => 'fa-check-circle',
                                        'dong' => 'fa-times-circle',
                                        default => 'fa-pause-circle'
                                    };
                                @endphp
                                <div class="{{ $phatHanhClass }} fw-bold small">
                                    <i class="fas {{ $phatHanhIcon }} me-1"></i>
                                    {{ $baiKiemTra->trang_thai_phat_hanh_label }}
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.kiem-tra-online.phe-duyet.show', $baiKiemTra->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                    Chi tiết <i class="fas fa-chevron-right ms-1 small"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted opacity-50 mb-3">
                                    <i class="fas fa-inbox fa-4x"></i>
                                </div>
                                <h5 class="fw-bold text-muted">Không tìm thấy bài kiểm tra nào</h5>
                                <p class="text-muted small">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm của bạn.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($baiKiemTras->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $baiKiemTras->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .table thead th { border-bottom: none; }
    .table tbody tr { transition: all 0.2s ease; }
    .table tbody tr:hover { background-color: #f8f9fa; }
    
    .badge { font-weight: 600; letter-spacing: 0.3px; }
</style>
@endsection
