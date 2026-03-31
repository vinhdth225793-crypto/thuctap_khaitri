@extends('layouts.app', ['title' => 'Chi tiết bài kiểm tra'])

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-file-invoice me-2 text-primary"></i>Chi tiết đề thi</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}">Phê duyệt đề thi</a></li>
                    <li class="breadcrumb-item active">Chi tiết đề</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-light border-0 shadow-sm rounded-pill px-4 fw-bold">
            <i class="fas fa-arrow-left me-2"></i> Quay lại danh sách
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar: Information & Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Thông tin chung</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h6 class="text-dark fw-bold mb-3">{{ $baiKiemTra->tieu_de }}</h6>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted small">Khóa học</span>
                                <span class="fw-bold small text-end" style="max-width: 200px;">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted small">Module</span>
                                <span class="fw-bold small text-end" style="max-width: 200px;">{{ $baiKiemTra->moduleHoc->ten_module ?? 'Dùng chung' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted small">Người tạo</span>
                                <span class="badge bg-soft-primary text-primary px-2">{{ $baiKiemTra->nguoiTao->ho_ten ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted small">Thời gian</span>
                                <span class="fw-bold text-dark"><i class="far fa-clock me-1 text-muted"></i>{{ $baiKiemTra->thoi_gian_lam_bai }} phút</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted small">Tổng điểm</span>
                                <span class="fw-bold text-success fs-5">{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted small">Số lần làm bài</span>
                                <span class="fw-bold text-dark">{{ $baiKiemTra->so_lan_duoc_lam }} lần</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Trạng thái hiện tại</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="text-muted smaller mb-1">Duyệt</div>
                                    @php
                                        $duyetBadge = match($baiKiemTra->trang_thai_duyet) {
                                            'da_duyet' => 'text-success',
                                            'cho_duyet' => 'text-warning',
                                            'tu_choi' => 'text-danger',
                                            default => 'text-muted'
                                        };
                                    @endphp
                                    <div class="fw-bold {{ $duyetBadge }}">{{ $baiKiemTra->trang_thai_duyet_label }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="text-muted smaller mb-1">Phát hành</div>
                                    @php
                                        $phatHanhBadge = match($baiKiemTra->trang_thai_phat_hanh) {
                                            'phat_hanh' => 'text-success',
                                            'dong' => 'text-danger',
                                            default => 'text-muted'
                                        };
                                    @endphp
                                    <div class="fw-bold {{ $phatHanhBadge }}">{{ $baiKiemTra->trang_thai_phat_hanh_label }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Actions Area -->
                    <div class="actions-area">
                        <h6 class="fw-bold mb-3"><i class="fas fa-tasks me-2 text-primary"></i>Thao tác phê duyệt</h6>
                        
                        @if($baiKiemTra->trang_thai_duyet === 'da_duyet')
                            <div class="mb-3">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">GHI CHÚ DUYỆT</label>
                                    <textarea rows="3" class="form-control bg-light border-0" readonly>{{ $baiKiemTra->ghi_chu_duyet }}</textarea>
                                </div>
                                <button type="button" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm" disabled>
                                    <i class="fas fa-check-double me-2"></i> ĐÃ DUYỆT
                                </button>
                            </div>
                        @else
                            <form action="{{ route('admin.kiem-tra-online.phe-duyet.approve', $baiKiemTra->id) }}" method="POST" class="mb-3" onsubmit="return confirm('Bạn chắc chắn muốn duyệt đề thi này?')">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">GHI CHÚ DUYỆT (NẾU CÓ)</label>
                                    <textarea name="ghi_chu_duyet" rows="3" class="form-control bg-light border-0" placeholder="Nội dung nhắn gửi đến giảng viên...">{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i> DUYỆT ĐỀ THI
                                </button>
                            </form>
                        @endif

                        <button class="btn btn-outline-danger w-100 fw-bold py-2 rounded-3 mb-4" data-bs-toggle="collapse" data-bs-target="#rejectCollapse">
                            <i class="fas fa-times-circle me-2"></i> TỪ CHỐI ĐỀ THI
                        </button>

                        <div class="collapse mb-4" id="rejectCollapse">
                            <form action="{{ route('admin.kiem-tra-online.phe-duyet.reject', $baiKiemTra->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn từ chối đề thi này?')">
                                @csrf
                                <div class="card card-body bg-soft-danger border-danger border-opacity-25 rounded-3 p-3">
                                    <label class="form-label small fw-bold text-danger">LÝ DO TỪ CHỐI (BẮT BUỘC)</label>
                                    <textarea name="ghi_chu_duyet" rows="3" class="form-control border-danger border-opacity-25 mb-3" placeholder="Nhập lý do cần sửa đổi..." required>{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                                    <button type="submit" class="btn btn-danger w-100 fw-bold">XÁC NHẬN TỪ CHỐI</button>
                                </div>
                            </form>
                        </div>

                        <div class="d-grid gap-2 border-top pt-4">
                            @if($baiKiemTra->trang_thai_duyet === 'da_duyet' && $baiKiemTra->trang_thai_phat_hanh !== 'phat_hanh')
                                <form action="{{ route('admin.kiem-tra-online.phe-duyet.publish', $baiKiemTra->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-3">
                                        <i class="fas fa-paper-plane me-2"></i> PHÁT HÀNH ĐẾN HỌC VIÊN
                                    </button>
                                </form>
                            @endif
                            
                            @if($baiKiemTra->trang_thai_phat_hanh === 'phat_hanh')
                                <form action="{{ route('admin.kiem-tra-online.phe-duyet.close', $baiKiemTra->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2 rounded-3 shadow-sm" onsubmit="return confirm('Bạn muốn đóng đề thi này? Học viên sẽ không thể làm bài nữa.')">
                                        <i class="fas fa-lock me-2"></i> ĐÓNG ĐỀ THI
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Area: Questions and History -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol me-2 text-primary"></i>Danh sách câu hỏi ({{ $baiKiemTra->chiTietCauHois->count() }})</h5>
                    <span class="badge bg-soft-info text-info rounded-pill px-3 fw-bold">{{ $baiKiemTra->loai_noi_dung_label }}</span>
                </div>
                <div class="card-body p-4 question-list-scroll">
                    @forelse($baiKiemTra->chiTietCauHois as $index => $chiTiet)
                        <div class="question-item mb-4 pb-4 border-bottom last-child-no-border">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div class="d-flex gap-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; flex-shrink: 0;">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-2 fs-6">{!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Nội dung không xác định')) !!}</div>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="fas fa-layer-group me-1"></i>{{ $chiTiet->cauHoi->loai_cau_hoi_label ?? 'N/A' }}
                                            </span>
                                            <span class="badge bg-light text-primary border px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="fas fa-star me-1"></i>{{ number_format((float) $chiTiet->diem_so, 2) }} điểm
                                            </span>
                                            <span class="badge bg-soft-{{ $chiTiet->cauHoi->muc_do === 'de' ? 'success' : ($chiTiet->cauHoi->muc_do === 'trung_binh' ? 'warning' : 'danger') }} text-{{ $chiTiet->cauHoi->muc_do === 'de' ? 'success' : ($chiTiet->cauHoi->muc_do === 'trung_binh' ? 'warning' : 'danger') }} px-2 py-1" style="font-size: 0.7rem;">
                                                {{ $chiTiet->cauHoi->muc_do_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(optional($chiTiet->cauHoi)->loai_cau_hoi === 'trac_nghiem')
                                <div class="row g-2 mt-2 ms-md-5">
                                    @foreach($chiTiet->cauHoi->dapAns as $dapAn)
                                        <div class="col-md-6">
                                            <div class="approval-answer-card p-3 rounded-3 border d-flex align-items-center gap-3 {{ $dapAn->is_dap_an_dung ? 'border-success bg-soft-success' : 'bg-light border-0' }}">
                                                <div class="bg-white border rounded-circle d-flex align-items-center justify-content-center fw-bold text-muted" style="width: 28px; height: 28px; flex-shrink: 0;">
                                                    {{ $dapAn->ky_hieu }}
                                                </div>
                                                <div class="flex-grow-1 small {{ $dapAn->is_dap_an_dung ? 'fw-bold text-success' : '' }}">
                                                    {{ $dapAn->noi_dung }}
                                                </div>
                                                @if($dapAn->is_dap_an_dung)
                                                    <i class="fas fa-check-circle text-success fs-5"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(optional($chiTiet->cauHoi)->loai_cau_hoi === 'tu_luan')
                                <div class="ms-md-5 mt-2">
                                    <div class="alert alert-soft-info border-0 rounded-3 py-2 px-3 small mb-0">
                                        <i class="fas fa-info-circle me-2"></i> Câu hỏi tự luận: Học viên sẽ trả lời bằng văn bản và giảng viên chấm sau.
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted bg-light rounded-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Đề thi này hiện chưa có nội dung câu hỏi.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-info"></i>Thống kê & Bài làm</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3">Học viên</th>
                                    <th class="py-3 text-center">Lần thi</th>
                                    <th class="py-3 text-center">Nộp lúc</th>
                                    <th class="py-3 text-center">Trạng thái</th>
                                    <th class="pe-4 py-3 text-end">Điểm số</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($baiKiemTra->baiLams as $baiLam)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark small">{{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</div>
                                            <div class="text-muted smaller">{{ $baiLam->hocVien->email ?? 'N/A' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border rounded-pill px-2 fw-bold">#{{ $baiLam->lan_lam_thu }}</span>
                                        </td>
                                        <td class="text-center small text-muted">
                                            {{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? '—' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $baiLam->trang_thai === 'hoan_thanh' ? 'success' : 'warning' }} rounded-pill px-2">
                                                {{ $baiLam->trang_thai_label }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <span class="fw-bold fs-6 {{ $baiLam->diem_so !== null ? 'text-primary' : 'text-muted' }}">
                                                {{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Chưa chấm' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            Chưa có dữ liệu bài làm cho đề thi này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-danger { background-color: rgba(231, 74, 59, 0.1); }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .last-child-no-border:last-child { border-bottom: none !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }
    
    .approval-answer-card { transition: all 0.2s ease; cursor: default; }
    .approval-answer-card:hover { transform: scale(1.01); }

    .question-list-scroll {
        max-height: 72vh;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    .question-list-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .question-list-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    
    .breadcrumb-item + .breadcrumb-item::before { content: "\f105"; font-family: "Font Awesome 6 Free"; font-weight: 900; font-size: 0.7rem; color: #adb5bd; }
    
    .badge { font-weight: 600; }
    .smaller { font-size: 0.75rem; }
    
    textarea:focus { box-shadow: none !important; border-color: var(--bs-primary) !important; }
</style>
@endsection

