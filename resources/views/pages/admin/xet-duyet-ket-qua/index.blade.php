@extends('layouts.app', ['title' => 'Phiếu xét duyệt kết quả'])

@section('content')
@php
    $hasFilter = !empty($status);
@endphp

<div class="container-fluid admin-page-x xdk-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome xdk-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-stamp"></i></div>
        <div class="apx-welcome-text">
            <div class="xdk-tag-row">
                <span class="xdk-loai-badge">
                    <i class="fas fa-file-circle-check"></i> XÉT DUYỆT KẾT QUẢ
                </span>
                <span class="xdk-status-badge">
                    <i class="fas fa-layer-group"></i>
                    {{ ($summary['submitted'] ?? 0) + ($summary['reviewing'] ?? 0) + ($summary['approved'] ?? 0) + ($summary['finalized'] ?? 0) }} phiếu
                </span>
                @if(($summary['submitted'] ?? 0) > 0)
                    <span class="xdk-pending-badge">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $summary['submitted'] }} chờ duyệt
                    </span>
                @endif
            </div>
            <h4>Phiếu xét duyệt kết quả</h4>
            <p>
                <span><i class="fas fa-eye"></i> {{ $summary['reviewing'] ?? 0 }} đang xem</span>
                <span class="xdk-sep">·</span>
                <span><i class="fas fa-circle-check"></i> {{ $summary['approved'] ?? 0 }} đã duyệt</span>
                <span class="xdk-sep">·</span>
                <span><i class="fas fa-lock"></i> {{ $summary['finalized'] ?? 0 }} đã chốt</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
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
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan phiếu xét duyệt</h2>
                    <p>Bốn trạng thái phiếu giúp bạn nắm hàng đợi xử lý.</p>
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="aps-text">
                        <strong>{{ $summary['submitted'] ?? 0 }}</strong>
                        <small>Chờ duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-eye"></i></div>
                    <div class="aps-text">
                        <strong>{{ $summary['reviewing'] ?? 0 }}</strong>
                        <small>Đang xem</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $summary['approved'] ?? 0 }}</strong>
                        <small>Đã duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-lock"></i></div>
                    <div class="aps-text">
                        <strong>{{ $summary['finalized'] ?? 0 }}</strong>
                        <small>Đã chốt</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Bộ lọc ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-filter"></i> Bộ lọc trạng thái</h2>
                    <p>Lọc theo trạng thái xử lý của phiếu xét duyệt.</p>
                </div>
            </div>
            @if($hasFilter)
                <div class="apx-section-meta">
                    <span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">
                        <i class="fas fa-filter"></i> Đang lọc
                    </span>
                </div>
            @endif
        </header>

        <div class="xdk-filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="xdk-flabel">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach(['submitted' => 'Chờ duyệt', 'reviewing' => 'Đang xem', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'finalized' => 'Đã chốt', 'draft' => 'Nháp'] as $value => $label)
                            <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary fw-bold" type="submit">
                        <i class="fas fa-search me-1"></i> Lọc
                    </button>
                    <a href="{{ route('admin.xet-duyet-ket-qua.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left me-1"></i> Xóa lọc
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- ========== ③ Danh sách phiếu ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách phiếu xét duyệt</h2>
                    <p>Bấm "Xem chi tiết" để duyệt, từ chối hoặc chốt điểm.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $tickets->total() }}</strong> phiếu</span>
            </div>
        </header>

        <div class="xdk-table-wrap">
            @if($tickets->isEmpty())
                <div class="xdk-empty">
                    <div class="xdk-empty-icon"><i class="fas fa-inbox"></i></div>
                    <h5>Chưa có phiếu xét duyệt nào</h5>
                    <p>Phiếu sẽ xuất hiện ở đây khi giảng viên gửi kết quả cuối khóa lên cho admin xét duyệt.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 xdk-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Phiếu</th>
                                <th>Khóa học</th>
                                <th>Giảng viên</th>
                                <th class="text-center">Học viên</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                @php
                                    $idColor = $ticket->id % 6;
                                    $gradients = [
                                        'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                        'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                        'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                        'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                        'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                        'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                                    ];
                                    $tenGV = $ticket->nguoiLap?->ho_ten ?? '?';
                                    $gvInitial = mb_strtoupper(mb_substr(trim($tenGV), 0, 1, 'UTF-8'), 'UTF-8');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="xdk-thumb" style="background: {{ $gradients[$idColor] }};">
                                                #{{ $ticket->id }}
                                            </div>
                                            <div>
                                                <div class="xdk-name">Phiếu #{{ $ticket->id }}</div>
                                                <div class="xdk-meta">
                                                    <i class="fas fa-tag"></i> {{ $ticket->phuong_an_label }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="xdk-course">{{ $ticket->khoaHoc?->ten_khoa_hoc ?? '—' }}</div>
                                        <div class="xdk-course-sub">
                                            <i class="fas fa-graduation-cap"></i> {{ $ticket->khoaHoc?->ma_khoa_hoc ?? '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="xdk-avatar" style="background: {{ $gradients[(($ticket->nguoiLap?->ma_nguoi_dung ?? 0)) % 6] }};">
                                                {{ $gvInitial ?: '?' }}
                                            </div>
                                            <div>
                                                <div class="xdk-gv">{{ $tenGV }}</div>
                                                <div class="xdk-gv-time">
                                                    <i class="far fa-clock"></i>
                                                    {{ $ticket->submitted_at?->format('d/m/Y H:i') ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="xdk-count-pill">
                                            <i class="fas fa-users"></i> {{ $ticket->chi_tiets_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $ticket->trang_thai_color }} px-3 py-2">
                                            {{ $ticket->trang_thai_label }}
                                        </span>
                                        @if($ticket->reject_reason)
                                            <div class="xdk-reject-reason mt-2">
                                                <i class="fas fa-circle-exclamation"></i> {{ $ticket->reject_reason }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('admin.xet-duyet-ket-qua.show', $ticket) }}" class="xdk-detail-btn">
                                            <i class="fas fa-magnifying-glass"></i>
                                            <span>Xem chi tiết</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($tickets->hasPages())
                    <div class="xdk-pagination">
                        {{ $tickets->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .xdk-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .xdk-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .xdk-page .apx-section-title h2 i { color: #dc2626; }
    .xdk-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .xdk-page .apx-meta-pill strong { color: #b91c1c; }

    .xdk-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .xdk-tag-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
    .xdk-loai-badge {
        display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px;
        background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px); color: #fff;
        font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px;
    }
    .xdk-status-badge {
        display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px;
        background: rgba(255,255,255,0.14); color: #fff;
        font-size: 0.72rem; font-weight: 700; border-radius: 999px;
    }
    .xdk-pending-badge {
        display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px;
        animation: xdkPulse 1.6s ease-out infinite;
    }
    @keyframes xdkPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }
    .apx-welcome.xdk-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem;
    }
    .apx-welcome.xdk-welcome p i { color: #fef3c7; margin-right: 4px; }
    .xdk-sep { opacity: 0.5; }

    .xdk-filter-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 18px 20px;
    }
    .xdk-flabel {
        display: block; font-weight: 800; color: #7f1d1d;
        font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    .xdk-table-wrap {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;
    }
    .xdk-table thead {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem; text-transform: uppercase; color: #7f1d1d; letter-spacing: 0.5px;
    }
    .xdk-table thead th {
        padding: 12px 10px; font-weight: 800; border-bottom: 1px solid #fecaca;
    }
    .xdk-table tbody td {
        padding: 14px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .xdk-table tbody tr:last-child td { border-bottom: 0; }
    .xdk-table tbody tr:hover { background: #fafafa; }

    .xdk-thumb {
        flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px;
        color: #fff; display: grid; place-items: center;
        font-size: 0.78rem; font-weight: 800;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .xdk-name { font-size: 0.92rem; font-weight: 800; color: #0f172a; line-height: 1.3; }
    .xdk-meta { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .xdk-meta i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .xdk-course { font-size: 0.86rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
    .xdk-course-sub { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .xdk-course-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .xdk-avatar {
        flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%;
        color: #fff; font-weight: 800; font-size: 0.82rem;
        display: grid; place-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .xdk-gv { font-size: 0.84rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
    .xdk-gv-time { font-size: 0.72rem; color: #94a3b8; font-weight: 600; margin-top: 2px; }
    .xdk-gv-time i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }

    .xdk-count-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 14px;
        background: #eff6ff; color: #1d4ed8;
        font-size: 0.86rem; font-weight: 800; border-radius: 999px;
    }
    .xdk-count-pill i { font-size: 0.74rem; }

    .xdk-reject-reason {
        display: inline-flex; align-items: flex-start; gap: 5px;
        padding: 4px 10px;
        background: #fef2f2; color: #b91c1c;
        font-size: 0.74rem; font-weight: 600; border-radius: 8px;
        max-width: 240px;
    }

    .xdk-detail-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 16px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff; font-size: 0.8rem; font-weight: 800; border-radius: 10px;
        text-decoration: none; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
        transition: all 0.2s ease;
    }
    .xdk-detail-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(220, 38, 38, 0.32); color: #fff; }

    .xdk-pagination {
        padding: 14px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0;
        display: flex; justify-content: center;
    }
    .xdk-pagination nav { margin: 0; }

    .xdk-empty { padding: 60px 30px; text-align: center; }
    .xdk-empty-icon {
        width: 88px; height: 88px; margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%; display: grid; place-items: center;
        color: #dc2626; font-size: 2rem;
    }
    .xdk-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .xdk-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
