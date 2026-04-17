@extends('layouts.app', ['title' => 'Bài kiểm tra của học viên'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Bài kiểm tra của tôi</h2>
            <p class="text-muted mb-0">Danh sách bài kiểm tra thuộc các khóa học bạn đang tham gia.</p>
        </div>

        <a href="{{ route('hoc-vien.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Về dashboard
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Tổng bài kiểm tra</div>
                    <div class="stat-value">{{ $stats['tong'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Đang mở</div>
                    <div class="stat-value text-success">{{ $stats['dang_mo'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Sắp mở</div>
                    <div class="stat-value text-warning">{{ $stats['sap_mo'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Đã nộp</div>
                    <div class="stat-value text-primary">{{ $stats['da_nop'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($baiKiemTras as $baiKiemTra)
            @php
                $baiLam = $baiKiemTra->baiLams->first();
                $activeBaiLam = $baiKiemTra->baiLams->firstWhere('trang_thai', 'dang_lam');
                $attemptsUsed = $baiKiemTra->baiLams->count();
                $remainingAttempts = max(0, (int) $baiKiemTra->so_lan_duoc_lam - $attemptsUsed);
                $canStartNewAttempt = $baiKiemTra->can_student_start && !$activeBaiLam && $remainingAttempts > 0;
            @endphp
            <div class="col-xl-4 col-lg-6">
                <div class="card vip-card h-100 test-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1">{{ $baiKiemTra->tieu_de }}</h5>
                                <div class="small text-muted">
                                    {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Chưa xác định khóa học' }}
                                </div>
                            </div>
                            <span class="badge bg-{{ $baiKiemTra->access_status_color }}">{{ $baiKiemTra->access_status_label }}</span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-dark border">{{ $baiKiemTra->pham_vi_label }}</span>
                            <span class="badge bg-light text-dark border">{{ $baiKiemTra->thoi_gian_lam_bai }} phút</span>
                            <span class="badge {{ $baiKiemTra->co_giam_sat ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-light text-dark border' }}">
                                {{ $baiKiemTra->co_giam_sat ? 'Giám sát nâng cao' : 'Bài thường' }}
                            </span>
                            @if($baiLam)
                                <span class="badge bg-{{ $baiLam->trang_thai_color }}">{{ $baiLam->trang_thai_label }}</span>
                            @endif
                        </div>

                        <div class="small text-muted mb-2">
                            <i class="fas fa-layer-group me-1"></i>
                            {{ $baiKiemTra->moduleHoc->ten_module ?? 'Chưa gán module' }}
                        </div>

                        @if($baiKiemTra->lichHoc)
                            <div class="small text-muted mb-2">
                                <i class="fas fa-calendar-day me-1"></i>
                                Buổi {{ $baiKiemTra->lichHoc->buoi_so ?: '#' }} -
                                {{ optional($baiKiemTra->lichHoc->ngay_hoc)->format('d/m/Y') ?: 'Chưa có ngày học' }}
                            </div>
                        @endif

                        <div class="small text-muted mb-2">
                            <i class="far fa-clock me-1"></i>
                            Mở:
                            {{ $baiKiemTra->ngay_mo ? $baiKiemTra->ngay_mo->format('d/m/Y H:i') : 'Mở ngay' }}
                        </div>
                        <div class="small text-muted mb-3">
                            <i class="fas fa-hourglass-end me-1"></i>
                            Đóng:
                            {{ $baiKiemTra->ngay_dong ? $baiKiemTra->ngay_dong->format('d/m/Y H:i') : 'Chưa đặt lịch đóng' }}
                        </div>

                        <div class="small text-muted mb-3">
                            <i class="fas fa-rotate-right me-1"></i>
                            Lượt làm: <strong>{{ $attemptsUsed }}/{{ (int) $baiKiemTra->so_lan_duoc_lam }}</strong>
                            @if($remainingAttempts > 0)
                                <span class="text-success">còn {{ $remainingAttempts }} lượt</span>
                            @else
                                <span class="text-muted">hết lượt</span>
                            @endif
                        </div>

                        <p class="text-muted small mb-4">
                            {{ \Illuminate\Support\Str::limit($baiKiemTra->mo_ta ?: 'Chưa có mô tả cho bài kiểm tra này.', 130) }}
                        </p>

                        <div class="mt-auto d-flex flex-wrap gap-2">
                            <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-outline-primary">
                                Xem chi tiết
                            </a>

                            @if($baiKiemTra->lich_hoc_id)
                                <a href="{{ route('hoc-vien.buoi-hoc.show', $baiKiemTra->lich_hoc_id) }}" class="btn btn-outline-secondary">
                                    Về buổi học
                                </a>
                            @endif

                            @if($activeBaiLam)
                                <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-primary">
                                    Tiếp tục làm bài
                                </a>
                            @elseif($canStartNewAttempt && $baiKiemTra->co_giam_sat)
                                <a href="{{ route('hoc-vien.bai-kiem-tra.precheck', $baiKiemTra->id) }}" class="btn btn-warning">
                                    {{ $baiLam ? 'Pre-check để làm lại' : 'Pre-check trước khi thi' }}
                                </a>
                            @elseif($canStartNewAttempt)
                                <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $baiKiemTra->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        {{ $baiLam ? 'Làm lần tiếp theo' : 'Bắt đầu làm bài' }}
                                    </button>
                                </form>
                            @elseif($baiLam && $baiLam->is_submitted)
                                <span class="btn btn-success disabled">Đã nộp bài</span>
                            @else
                                <span class="btn btn-secondary disabled">{{ $baiKiemTra->access_status_label }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card vip-card">
                    <div class="card-body text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="fas fa-file-circle-question"></i>
                        </div>
                        <h5 class="fw-semibold">Chưa có bài kiểm tra nào</h5>
                        <p class="text-muted mb-0">Khi giảng viên tạo bài kiểm tra cho khóa học bạn tham gia, danh sách sẽ xuất hiện tại đây.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    .stat-card-lite .card-body {
        padding: 1.5rem;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.05;
        color: #0f172a;
    }

    .test-card {
        border: 1px solid #e2e8f0;
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: #eff6ff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
</style>
@endpush
@endsection
