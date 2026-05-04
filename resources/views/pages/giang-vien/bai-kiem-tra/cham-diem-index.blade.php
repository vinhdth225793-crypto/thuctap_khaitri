@extends('layouts.app', ['title' => 'Chấm điểm tự luận'])

@section('content')
@php
    $tongChoCham = $baiLams->total();
    $allItems    = $baiLams->getCollection();
    $uniqueExams    = $allItems->pluck('bai_kiem_tra_id')->unique()->count();
    $uniqueStudents = $allItems->pluck('hoc_vien_id')->unique()->count();
    $now = now();
    $overdueCount = $allItems->filter(function ($bl) use ($now) {
        return $bl->nop_luc && $bl->nop_luc->diffInHours($now) >= 24;
    })->count();
@endphp

<div class="container-fluid admin-page-x cd-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome cd-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-marker"></i></div>
        <div class="apx-welcome-text">
            <div class="cd-tag-row">
                <span class="cd-loai-badge">
                    <i class="fas fa-pen-fancy"></i> CHẤM ĐIỂM TỰ LUẬN
                </span>
                <span class="cd-status-badge">
                    <i class="fas fa-hourglass-half"></i>
                    {{ $tongChoCham }} bài chờ
                </span>
                @if($overdueCount > 0)
                    <span class="cd-pending-badge">
                        <i class="fas fa-bell"></i>
                        {{ $overdueCount }} bài chờ &gt; 24h
                    </span>
                @endif
            </div>
            <h4>Hàng đợi chấm tay</h4>
            <p>
                <span><i class="fas fa-file-pen"></i> {{ $tongChoCham }} bài đang chờ</span>
                <span class="cd-sep">·</span>
                <span><i class="fas fa-clipboard-check"></i> {{ $uniqueExams }} đề kiểm tra</span>
                <span class="cd-sep">·</span>
                <span><i class="fas fa-user-graduate"></i> {{ $uniqueStudents }} học viên</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="apx-view-toggle">
                <i class="fas fa-clipboard-list"></i> <span>Quản lý đề thi</span>
            </a>
            <a href="{{ route('giang-vien.diem-kiem-tra.index') }}" class="btn btn-light text-primary fw-bold shadow-sm cd-create-btn">
                <i class="fas fa-chart-column me-1"></i> Bảng điểm
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- ========== ① Tổng quan ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan hàng đợi</h2>
                    <p>Bốn chỉ số nhanh để bạn nắm khối lượng bài cần chấm tay.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $tongChoCham }}</strong> bài chờ</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="aps-text">
                        <strong>{{ $tongChoCham }}</strong>
                        <small>Bài đang chờ chấm</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $uniqueExams }}</strong>
                        <small>Đề kiểm tra liên quan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="aps-text">
                        <strong>{{ $uniqueStudents }}</strong>
                        <small>Học viên đang chờ</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-danger">
                    <div class="aps-icon"><i class="fas fa-bell"></i></div>
                    <div class="aps-text">
                        <strong>{{ $overdueCount }}</strong>
                        <small>Đã chờ trên 24h</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Danh sách bài chờ chấm ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách bài chờ chấm</h2>
                    <p>Mở từng bài để chấm điểm tự luận và ghi nhận xét cho học viên.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $tongChoCham }}</strong> bài</span>
            </div>
        </header>

        <div class="cd-table-wrap">
            @if($baiLams->isEmpty())
                <div class="cd-empty">
                    <div class="cd-empty-icon"><i class="fas fa-circle-check"></i></div>
                    <h5>Tuyệt vời! Hàng đợi đã trống</h5>
                    <p>Không có bài làm nào đang chờ chấm tay. Bài tự luận mới sẽ xuất hiện ở đây sau khi học viên nộp.</p>
                    <a href="{{ route('giang-vien.diem-kiem-tra.index') }}" class="btn btn-primary fw-bold">
                        <i class="fas fa-arrow-right me-1"></i> Xem bảng điểm
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 cd-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Học viên</th>
                                <th>Bài kiểm tra</th>
                                <th>Khóa / Module</th>
                                <th class="text-center">Nộp lúc</th>
                                <th class="text-center">Lần làm</th>
                                <th class="pe-4 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baiLams as $bl)
                                @php
                                    $hocVien = $bl->hocVien;
                                    $bkt     = $bl->baiKiemTra;
                                    $hoTen   = $hocVien->ho_ten ?? 'Học viên';
                                    $email   = $hocVien->email ?? '—';
                                    $idColor = ($hocVien->ma_nguoi_dung ?? $bl->id) % 6;
                                    $gradients = [
                                        'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                        'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                        'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                        'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                        'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                        'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                                    ];
                                    $initial = mb_strtoupper(mb_substr(trim($hoTen), 0, 1, 'UTF-8'), 'UTF-8');
                                    $isOverdue = $bl->nop_luc && $bl->nop_luc->diffInHours($now) >= 24;
                                @endphp
                                <tr class="{{ $isOverdue ? 'is-overdue' : '' }}">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="cd-avatar" style="background: {{ $gradients[$idColor] }};">
                                                {{ $initial ?: 'H' }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="cd-name">{{ $hoTen }}</div>
                                                <div class="cd-email">
                                                    <i class="fas fa-envelope"></i> {{ $email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cd-exam">{{ $bkt->tieu_de ?? '—' }}</div>
                                        <span class="cd-status-pill is-warning">
                                            <i class="fas fa-pen-fancy"></i> Tự luận · Chờ chấm
                                        </span>
                                    </td>
                                    <td>
                                        <div class="cd-course">{{ $bkt->khoaHoc->ten_khoa_hoc ?? '—' }}</div>
                                        <div class="cd-module">
                                            <i class="fas fa-cube"></i>
                                            {{ $bkt->moduleHoc->ten_module ?? 'Không gán module' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="cd-time">
                                            <i class="far fa-clock"></i>
                                            {{ $bl->nop_luc?->format('d/m/Y H:i') ?? 'Chưa nộp' }}
                                        </div>
                                        @if($isOverdue)
                                            <span class="cd-status-pill is-danger mt-1">
                                                <i class="fas fa-bell"></i> Quá 24h
                                            </span>
                                        @else
                                            <span class="cd-time-rel">
                                                {{ $bl->nop_luc?->diffForHumans() ?? '—' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="cd-attempt-pill">
                                            <i class="fas fa-redo"></i> Lần {{ $bl->lan_lam_thu }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('giang-vien.cham-diem.show', $bl->id) }}"
                                           class="cd-grade-btn">
                                            <i class="fas fa-marker"></i>
                                            <span>Mở bài chấm</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($baiLams->hasPages())
                    <div class="cd-pagination">
                        {{ $baiLams->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .cd-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .cd-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .cd-page .apx-section-title h2 i { color: #dc2626; }
    .cd-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .cd-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .cd-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .cd-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .cd-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        border-radius: 999px;
    }
    .cd-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .cd-status-badge i { font-size: 0.65rem; opacity: 0.85; }
    .cd-pending-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        animation: cdPendingPulse 1.6s ease-out infinite;
    }
    @keyframes cdPendingPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }
    .apx-welcome.cd-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.cd-welcome p i { color: #fef3c7; margin-right: 4px; }
    .cd-sep { opacity: 0.5; }
    .cd-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== Bảng ===== */
    .cd-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .cd-table thead {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #7f1d1d;
        letter-spacing: 0.5px;
    }
    .cd-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #fecaca;
    }
    .cd-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .cd-table tbody tr:last-child td { border-bottom: 0; }
    .cd-table tbody tr:hover { background: #fafafa; }
    .cd-table tbody tr.is-overdue { background: linear-gradient(90deg, #fef2f2 0%, #ffffff 100%); }
    .cd-table tbody tr.is-overdue:hover { background: #fee2e2; }

    /* Avatar */
    .cd-avatar {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 50%;
        color: #fff;
        font-weight: 800;
        font-size: 1rem;
        display: grid; place-items: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .cd-name {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
    }
    .cd-email {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 3px;
    }
    .cd-email i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .cd-exam {
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: 6px;
    }
    .cd-course {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .cd-module {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 3px;
    }
    .cd-module i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .cd-time {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
    }
    .cd-time i { color: #1d4ed8; margin-right: 4px; font-size: 0.7rem; }
    .cd-time-rel {
        display: block;
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 3px;
    }

    /* Status pill */
    .cd-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        font-size: 0.68rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .cd-status-pill i { font-size: 0.6rem; }
    .cd-status-pill.is-warning { background: #fef3c7; color: #c2410c; }
    .cd-status-pill.is-danger  { background: #fee2e2; color: #b91c1c; }

    .cd-attempt-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.74rem;
        font-weight: 800;
        border-radius: 999px;
    }
    .cd-attempt-pill i { font-size: 0.66rem; }

    /* Grade button */
    .cd-grade-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.82rem;
        font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        transition: all 0.2s ease;
    }
    .cd-grade-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.35);
        color: #fff;
    }
    .cd-grade-btn i { font-size: 0.78rem; }

    /* Pagination */
    .cd-pagination {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: center;
    }
    .cd-pagination nav { margin: 0; }

    /* Empty state */
    .cd-empty {
        padding: 60px 30px;
        text-align: center;
    }
    .cd-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #16a34a;
        font-size: 2rem;
    }
    .cd-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .cd-empty p {
        font-size: 0.9rem; color: #94a3b8;
        max-width: 460px; margin: 0 auto 20px;
        line-height: 1.55;
    }

    .min-w-0 { min-width: 0; }

    @media (max-width: 991.98px) {
        .cd-table { font-size: 0.85rem; }
        .cd-table thead th, .cd-table tbody td { padding: 10px 8px; }
        .cd-avatar { width: 38px; height: 38px; font-size: 0.85rem; }
    }
</style>
@endsection
