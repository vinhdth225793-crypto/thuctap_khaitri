@extends('layouts.app', ['title' => 'Chi tiết buổi học'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}">Khóa học của tôi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $lichHoc->khoa_hoc_id) }}">{{ $lichHoc->khoaHoc->ten_khoa_hoc }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Buổi {{ $lichHoc->buoi_so ?: '#' }}</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge rounded-pill bg-light text-dark border">{{ $lichHoc->moduleHoc->ten_module ?? 'Chưa gán module' }}</span>
                <span class="badge rounded-pill bg-{{ $lichHoc->trang_thai_color }}">{{ $lichHoc->trang_thai_label }}</span>
                <span class="badge rounded-pill bg-{{ $lichHoc->hinh_thuc_color }}">{{ $lichHoc->hinh_thuc_label }}</span>
            </div>
            <h2 class="fw-bold mb-2">Buổi {{ $lichHoc->buoi_so ?: '#' }} • {{ $lichHoc->khoaHoc->ten_khoa_hoc }}</h2>
            <p class="text-muted mb-0">
                {{ $lichHoc->ngay_hoc?->format('d/m/Y') ?: 'Chưa có ngày học' }}
                • {{ substr((string) $lichHoc->gio_bat_dau, 0, 5) ?: '--:--' }}
                @if($lichHoc->gio_ket_thuc)
                    - {{ substr((string) $lichHoc->gio_ket_thuc, 0, 5) }}
                @endif
                • {{ $lichHoc->giangVien?->nguoiDung?->ho_ten ?? 'Chưa phân công giảng viên' }}
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $lichHoc->khoa_hoc_id) }}#lich-hoc" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Về lịch học
            </a>
            @if($liveLecture)
                <a href="{{ route('hoc-vien.live-room.show', $liveLecture->id) }}" class="btn btn-primary">
                    <i class="fas fa-video me-2"></i>{{ $lichHoc->can_join_online ? 'Vào live room' : 'Xem live room' }}
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-box h-100">
                                <div class="info-label">Thông tin buổi học</div>
                                <div class="fw-semibold text-dark mb-2">{{ $lichHoc->hinh_thuc_label }}</div>
                                <div class="small text-muted mb-2">{{ $lichHoc->schedule_range_label ?: 'Chưa cập nhật khung giờ' }}</div>
                                <div class="small text-muted">
                                    {{ $lichHoc->ghi_chu ?: 'Buổi học này chưa có ghi chú bổ sung.' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box h-100">
                                <div class="info-label">Truy cập buổi học</div>
                                <div class="fw-semibold text-dark mb-2">{{ $lichHoc->trang_thai_label }}</div>
                                <div class="small text-muted">{{ $lichHoc->hinh_thuc === 'online' ? $lichHoc->online_join_message : 'Buổi học diễn ra trực tiếp tại lớp.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Tài nguyên buổi học</h5>
                        <p class="text-muted small mb-0">Tài nguyên gắn trực tiếp với buổi học và đã mở cho học viên.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $lichHoc->taiNguyen->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($lichHoc->taiNguyen as $taiNguyen)
                        <div class="content-row">
                            <div>
                                <div class="fw-semibold text-dark">{{ $taiNguyen->tieu_de }}</div>
                                <div class="small text-muted">{{ $taiNguyen->loai_label }} • {{ $taiNguyen->file_status_message }}</div>
                            </div>
                            @if($taiNguyen->file_url)
                                <a href="{{ $taiNguyen->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    Mở tài nguyên
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state-box">Buổi học này chưa có tài nguyên trực tiếp nào được công bố.</div>
                    @endforelse
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Bài giảng liên quan</h5>
                        <p class="text-muted small mb-0">Bao gồm bài giảng tài liệu và live room của buổi học này.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $relatedLectures->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($relatedLectures as $baiGiang)
                            <div class="col-md-6">
                                <div class="lecture-card h-100">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-light text-dark border">{{ $baiGiang->moduleHoc->ten_module ?? 'Chưa gán module' }}</span>
                                        @if($baiGiang->isLive() && $baiGiang->phongHocLive)
                                            <span class="badge bg-info text-white">{{ $baiGiang->phongHocLive->platform_label }}</span>
                                        @endif
                                    </div>
                                    <h6 class="fw-semibold mb-2">{{ $baiGiang->tieu_de }}</h6>
                                    <p class="small text-muted mb-3">{{ \Illuminate\Support\Str::limit($baiGiang->mo_ta ?: 'Nội dung đã được mở cho học viên.', 110) }}</p>
                                    <a href="{{ $baiGiang->isLive() && $baiGiang->phongHocLive ? route('hoc-vien.live-room.show', $baiGiang->id) : route('hoc-vien.bai-giang.show', $baiGiang->id) }}" class="btn btn-sm btn-outline-primary">
                                        {{ $baiGiang->isLive() ? 'Vào live room' : 'Xem bài giảng' }}
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state-box">Buổi học này chưa có bài giảng nào được công bố.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Bài kiểm tra liên quan</h5>
                        <p class="text-muted small mb-0">Chỉ hiển thị bài kiểm tra đã phát hành cho chính buổi học này.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $relatedExams->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($relatedExams as $baiKiemTra)
                        <div class="content-row">
                            <div>
                                <div class="fw-semibold text-dark">{{ $baiKiemTra->tieu_de }}</div>
                                <div class="small text-muted">{{ $baiKiemTra->thoi_gian_lam_bai }} phút • {{ $baiKiemTra->question_count }} câu hỏi</div>
                            </div>
                            <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-sm btn-outline-primary">
                                Xem bài kiểm tra
                            </a>
                        </div>
                    @empty
                        <div class="empty-state-box">Buổi học này chưa có bài kiểm tra nào được phát hành.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card vip-card border-0 shadow-sm mb-4 sticky-top" style="top: 1.5rem;">
                <div class="card-header border-0 bg-white py-3">
                    <h6 class="fw-semibold mb-0">Điều hướng buổi học</h6>
                </div>
                <div class="card-body">
                    @foreach($courseSchedules as $session)
                        <a href="{{ route('hoc-vien.buoi-hoc.show', $session->id) }}" class="timeline-link {{ (int) $session->id === (int) $lichHoc->id ? 'is-active' : '' }}">
                            <span class="timeline-link__title">Buổi {{ $session->buoi_so ?: '#' }}</span>
                            <span class="timeline-link__meta">{{ $session->moduleHoc->ten_module ?? 'Chưa gán module' }} • {{ $session->ngay_hoc?->format('d/m/Y') ?: 'Chưa rõ ngày' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm">
                <div class="card-header border-0 bg-white py-3">
                    <h6 class="fw-semibold mb-0">Bản ghi buổi học</h6>
                </div>
                <div class="card-body">
                    @forelse($recordings as $recording)
                        <div class="content-row">
                            <div>
                                <div class="fw-semibold text-dark">{{ $recording->tieu_de }}</div>
                                <div class="small text-muted">{{ $recording->duration_label }}</div>
                            </div>
                            @if($recording->playback_url)
                                <a href="{{ $recording->playback_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    Xem bản ghi
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state-box">Chưa có bản ghi nào được công bố cho buổi học này.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-box, .lecture-card { border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; padding: 1rem 1.1rem; }
    .info-label { color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.4rem; }
    .content-row { padding: 1rem 0; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
    .content-row:first-child { padding-top: 0; }
    .content-row:last-child { padding-bottom: 0; border-bottom: none; }
    .timeline-link { display: block; padding: 0.9rem 1rem; border-radius: 16px; border: 1px solid #e2e8f0; color: #0f172a; text-decoration: none; margin-bottom: 0.75rem; background: #fff; }
    .timeline-link:last-child { margin-bottom: 0; }
    .timeline-link.is-active { border-color: #9ec5fe; background: #eff6ff; }
    .timeline-link__title { display: block; font-weight: 600; margin-bottom: 0.25rem; }
    .timeline-link__meta { display: block; color: #64748b; font-size: 0.88rem; }
    .empty-state-box { border: 1px dashed #cbd5e1; border-radius: 18px; padding: 1.25rem; background: #f8fafc; color: #64748b; text-align: center; }
</style>
@endpush
@endsection
