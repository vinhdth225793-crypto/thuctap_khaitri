@extends('layouts.app')

@section('title', $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}">Lộ trình học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết khóa học</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Course Header -->
    <div class="card vip-card border-0 shadow-sm overflow-hidden mb-4 rounded-4">
        <div class="bg-primary p-4 text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white text-primary mb-2 shadow-sm px-3 py-2 fw-bold">
                        {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}
                    </span>
                    <h2 class="fw-bold mb-1">{{ $khoaHoc->ten_khoa_hoc }}</h2>
                    <p class="mb-0 opacity-75 small">Mã khóa học: <strong>{{ $khoaHoc->ma_khoa_hoc }}</strong></p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex justify-content-md-end gap-2">
                        <div class="text-center bg-white bg-opacity-10 rounded-3 p-2 px-3">
                            <h5 class="mb-0 fw-bold">{{ $stats['tong_module'] }}</h5>
                            <small class="smaller opacity-75">Module</small>
                        </div>
                        <div class="text-center bg-white bg-opacity-10 rounded-3 p-2 px-3">
                            <h5 class="mb-0 fw-bold">{{ $stats['tong_buoi_hoc'] }}</h5>
                            <small class="smaller opacity-75">Buổi học</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content: Timeline of Sessions -->
        <div class="col-lg-8">
            <h5 class="fw-bold mb-3 d-flex align-items-center">
                <i class="fas fa-stream me-2 text-primary"></i> Danh sách buổi học và tài liệu công khai
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="vip-card border-0 shadow-sm p-3 h-100">
                        <div class="text-muted text-uppercase smaller fw-bold">Tổng module</div>
                        <div class="fs-4 fw-bold text-dark">{{ $stats['tong_module'] }}</div>
                        <div class="small text-muted">Trong khóa học này</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="vip-card border-0 shadow-sm p-3 h-100">
                        <div class="text-muted text-uppercase smaller fw-bold">Module có lịch</div>
                        <div class="fs-4 fw-bold text-primary">{{ $stats['module_co_lich'] }}</div>
                        <div class="small text-muted">Đã có buổi học cụ thể</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="vip-card border-0 shadow-sm p-3 h-100">
                        <div class="text-muted text-uppercase smaller fw-bold">Buổi online</div>
                        <div class="fs-4 fw-bold text-info">{{ $stats['buoi_online'] }}</div>
                        <div class="small text-muted">Có link học trực tuyến</div>
                    </div>
                </div>
            </div>

            <div class="session-timeline">
                @forelse($khoaHoc->moduleHocs as $module)
                    <div class="module-group mb-4">
                        <div class="module-header bg-light p-3 rounded-3 border-start border-4 border-primary mb-3 shadow-xs">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="smaller text-muted mb-1">
                                        <i class="fas fa-hashtag me-1"></i>{{ $module->ma_module }}
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="fas fa-folder-open me-2 text-warning"></i> Module: {{ $module->ten_module }}
                                    </h6>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary-soft text-primary border-0 shadow-xs">
                                        {{ $module->lichHocs->count() }} buổi
                                    </span>
                                    @if(!is_null($module->so_buoi))
                                        <div class="smaller text-muted mt-1">Kế hoạch: {{ $module->so_buoi }} buổi</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="session-list">
                            @forelse($module->lichHocs as $lich)
                                <div class="card vip-card border-0 shadow-sm mb-3 session-item overflow-hidden">
                                    <div class="card-body p-0">
                                        <div class="d-flex align-items-stretch">
                                            <div class="session-date bg-light p-3 text-center d-flex flex-column justify-content-center border-end" style="min-width: 90px;">
                                                <span class="fw-bold h5 mb-0">{{ $lich->ngay_hoc->format('d') }}</span>
                                                <span class="small text-muted text-uppercase">{{ $lich->ngay_hoc->format('M, Y') }}</span>
                                                <span class="badge bg-primary-soft text-primary smaller mt-1">{{ $lich->thu_label }}</span>
                                            </div>
                                            <div class="p-3 flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="fw-bold text-dark mb-1">Buổi #{{ $lich->buoi_so }}</h6>
                                                        <div class="smaller text-muted">
                                                            <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-{{ $lich->trang_thai_color }}-soft text-dark smaller border">
                                                            {{ $lich->trang_thai_label }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="row g-3 smaller text-muted">
                                                    <div class="col-md-4">
                                                        <div class="fw-bold text-dark mb-1">Hình thức học</div>
                                                        <span class="badge bg-{{ $lich->hinh_thuc_color }}-soft text-{{ $lich->hinh_thuc_color }} border-0">
                                                            <i class="fas {{ $lich->hinh_thuc === 'online' ? 'fa-video' : 'fa-door-open' }} me-1"></i>{{ $lich->hinh_thuc_label }}
                                                        </span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="fw-bold text-dark mb-1">{{ $lich->hinh_thuc === 'online' ? 'Trạng thái phòng học' : 'Phòng học' }}</div>
                                                        @if($lich->hinh_thuc === 'online')
                                                            <span class="badge bg-{{ $lich->online_join_state_color }}-soft text-{{ $lich->online_join_state_color }} border-0">
                                                                {{ $lich->online_join_state_label }}
                                                            </span>
                                                            <div class="mt-2">{{ $lich->online_join_message }}</div>
                                                        @else
                                                            <span>{{ $lich->phong_hoc ?: 'Chưa cập nhật phòng học' }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="fw-bold text-dark mb-1">Ghi chú</div>
                                                        <span>{{ $lich->ghi_chu ?: 'Chưa có ghi chú cho buổi học này' }}</span>
                                                    </div>
                                                </div>

                                                @if($lich->hinh_thuc === 'online')
                                                    <div class="alert alert-{{ $lich->online_join_state_color }} py-3 px-3 small border-0 mt-3 mb-0 shadow-xs">
                                                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold mb-1">
                                                                    <i class="fas fa-broadcast-tower me-2"></i>Lớp học online
                                                                </div>
                                                                <div>{{ $lich->online_join_message }}</div>

                                                                <div class="d-flex flex-wrap gap-3 mt-2 smaller">
                                                                    <span><i class="fas fa-layer-group me-1"></i>Nền tảng: {{ $lich->nen_tang_label }}</span>
                                                                    @if($lich->can_join_online && $lich->meeting_id)
                                                                        <span><i class="fas fa-id-card me-1"></i>Meeting ID: {{ $lich->meeting_id }}</span>
                                                                    @endif
                                                                    @if($lich->can_join_online && $lich->mat_khau_cuoc_hop)
                                                                        <span><i class="fas fa-key me-1"></i>Mật khẩu: {{ $lich->mat_khau_cuoc_hop }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            @if($lich->can_join_online)
                                                                <a href="{{ $lich->link_online }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-info text-white fw-bold px-3">
                                                                    VÀO PHÒNG HỌC
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="resource-area mt-3 pt-3 border-top border-light">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <p class="smaller fw-bold text-muted mb-0">
                                                            <i class="fas fa-chalkboard-teacher me-1"></i> Bài giảng và tài liệu
                                                        </p>
                                                        <span class="badge bg-light text-dark border">{{ $lich->baiGiangs->count() }}</span>
                                                    </div>

                                                    @if($lich->baiGiangs->isNotEmpty())
                                                        <div class="row g-3">
                                                            @foreach($lich->baiGiangs as $bg)
                                                                <div class="col-md-6">
                                                                    <div class="p-3 rounded-3 border bg-white h-100 shadow-xs d-flex align-items-start resource-card position-relative">
                                                                        <div class="bg-primary-soft rounded-circle text-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                                            <i class="fas fa-book-reader"></i>
                                                                        </div>
                                                                        <div class="min-w-0 flex-grow-1">
                                                                            <div class="mb-1">
                                                                                <span class="badge bg-light text-dark border smaller py-1 px-2">{{ $bg->loai_bai_giang }}</span>
                                                                            </div>
                                                                            <h6 class="small fw-bold text-dark mb-2">{{ $bg->tieu_de }}</h6>
                                                                            <p class="smaller text-muted mb-3 line-clamp-2">
                                                                                {{ $bg->mo_ta ?: 'Click để xem chi tiết bài giảng này.' }}
                                                                            </p>
                                                                            <a href="{{ route('hoc-vien.bai-giang.show', $bg->id) }}" class="btn btn-sm btn-primary fw-bold px-3 stretched-link">
                                                                                <i class="fas fa-arrow-right me-1"></i> Vào học
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="smaller text-muted italic">Buổi học này chưa có bài giảng công bố.</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-light border shadow-xs smaller text-muted mb-3">
                                    <i class="fas fa-calendar-times me-2"></i> Module này chưa có buổi học nào được lên lịch.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light border shadow-sm text-muted">
                        <i class="fas fa-folder-open me-2"></i> Khóa học này hiện chưa có module nào để hiển thị.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            {{-- COURSE INFO CARD --}}
            <div class="card vip-card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Về khóa học này</h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" 
                             class="rounded-3 shadow-sm img-fluid" style="max-height: 150px;">
                    </div>
                    
                    <div class="row g-3 small">
                        <div class="col-12">
                            <label class="text-muted d-block smaller">Mô tả tóm tắt</label>
                            <p class="mb-0 text-dark lh-base">{{ $khoaHoc->mo_ta_ngan ?: 'Khóa học đào tạo chuyên sâu về kỹ năng.' }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted d-block smaller">Khai giảng</label>
                            <span class="fw-bold text-dark">{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?: '—' }}</span>
                        </div>
                        <div class="col-6">
                            <label class="text-muted d-block smaller">Trình độ</label>
                            <span class="fw-bold text-dark text-capitalize">{{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoaHoc->cap_do] ?? 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <label class="text-muted d-block smaller">Trạng thái ghi danh</label>
                            <span class="badge {{ $ghiDanh->trang_thai_badge }}">{{ $ghiDanh->trang_thai_label }}</span>
                        </div>
                        <div class="col-6">
                            <label class="text-muted d-block smaller">Ngày ghi danh</label>
                            <span class="fw-bold text-dark">{{ $ghiDanh->ngay_tham_gia?->format('d/m/Y') ?: '—' }}</span>
                        </div>
                        <div class="col-12">
                            <hr class="my-2 opacity-50">
                        </div>
                        <div class="col-12">
                            <a href="#" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                                <i class="fas fa-question-circle me-1"></i> Cần hỗ trợ học tập?
                            </a>
                        </div>
                    </div>
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
                    <label class="smaller text-muted d-block mb-1">Tiêu đề bài học/tài liệu</label>
                    <h5 id="res-detail-title" class="fw-bold text-dark mb-0"></h5>
                    <span id="res-detail-badge" class="badge mt-2 border-0 smaller py-1 px-2"></span>
                </div>

                <div class="mb-0">
                    <label class="smaller text-muted d-block mb-1">Hướng dẫn / Mô tả nội dung</label>
                    <div id="res-detail-desc" class="p-3 bg-light rounded border small text-dark lh-base"></div>
                </div>

                <div class="mt-3">
                    <label class="smaller text-muted d-block mb-1">Nguồn tài nguyên</label>
                    <div id="res-detail-source" class="small text-dark fw-semibold"></div>
                </div>

                <div class="mt-3">
                    <label class="smaller text-muted d-block mb-1">Trạng thái truy cập</label>
                    <div id="res-detail-status" class="small text-muted"></div>
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
    const modalViewRes = new bootstrap.Modal(document.getElementById('modalViewResource'));

    document.querySelectorAll('.btn-view-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            
            // Đổ dữ liệu
            document.getElementById('res-detail-title').textContent = d.title;
            document.getElementById('res-detail-desc').innerHTML = d.desc.replace(/\n/g, '<br>');
            document.getElementById('res-detail-source').textContent = d.source;
            document.getElementById('res-detail-status').textContent = d.status;
            
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
            if (d.openable === 'true') {
                openBtn.style.display = 'inline-block';
                openBtn.href = d.url;
            } else {
                openBtn.style.display = 'none';
                openBtn.removeAttribute('href');
            }
            
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
});
</script>
@endpush

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .transition-all { transition: all 0.2s ease-in-out; }
    .hover-bg-white:hover { background-color: #fff !important; transform: scale(1.02); }
    
    .session-item { border-radius: 12px; }
    .session-date { background: #f8f9fa; }
    .resource-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .resource-card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important; }
    
    .module-group .module-header {
        position: relative;
    }
</style>
@endsection
