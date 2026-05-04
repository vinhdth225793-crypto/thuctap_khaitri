@extends('layouts.app')

@section('title', 'Đơn xin nghỉ của tôi')

@section('content')
@php
    $baseQuery = $teacher->donXinNghis();
    $stats = [
        'tong'      => (clone $baseQuery)->count(),
        'cho_duyet' => (clone $baseQuery)->where('trang_thai', 'cho_duyet')->count(),
        'da_duyet'  => (clone $baseQuery)->where('trang_thai', 'da_duyet')->count(),
        'tu_choi'   => (clone $baseQuery)->where('trang_thai', 'tu_choi')->count(),
    ];
    $hasFilter = !empty($filters['trang_thai']);
@endphp

<div class="container-fluid admin-page-x dxn-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome dxn-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-calendar-xmark"></i></div>
        <div class="apx-welcome-text">
            <div class="dxn-tag-row">
                <span class="dxn-loai-badge">
                    <i class="fas fa-clipboard-list"></i> ĐƠN XIN NGHỈ
                </span>
                <span class="dxn-status-badge">
                    <i class="fas fa-layer-group"></i>
                    {{ $stats['tong'] }} đơn
                </span>
                @if($stats['cho_duyet'] > 0)
                    <span class="dxn-pending-badge">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $stats['cho_duyet'] }} chờ duyệt
                    </span>
                @endif
            </div>
            <h4>Đơn xin nghỉ / phản hồi lịch dạy</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['da_duyet'] }} đã duyệt</span>
                <span class="dxn-sep">·</span>
                <span><i class="fas fa-times-circle"></i> {{ $stats['tu_choi'] }} bị từ chối</span>
                <span class="dxn-sep">·</span>
                <span><i class="fas fa-pen"></i> Gửi đơn cho từng buổi học hoặc xin off theo ngày, buổi, tiết</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.lich-giang.index') }}" class="apx-view-toggle">
                <i class="fas fa-calendar-days"></i> <span>Lịch dạy</span>
            </a>
            <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-light text-primary fw-bold shadow-sm dxn-create-btn">
                <i class="fas fa-plus me-1"></i> Tạo đơn mới
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
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan đơn xin nghỉ</h2>
                    <p>Bốn chỉ số nhanh giúp bạn nắm tình trạng đơn đã gửi.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $stats['tong'] }}</strong> tổng đơn</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['tong'] }}</strong>
                        <small>Tổng số đơn</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['cho_duyet'] }}</strong>
                        <small>Chờ admin duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['da_duyet'] }}</strong>
                        <small>Đã được duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-danger">
                    <div class="aps-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['tu_choi'] }}</strong>
                        <small>Bị từ chối</small>
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
                    <p>Lọc danh sách theo trạng thái duyệt của từng đơn.</p>
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

        <div class="dxn-filter-card">
            <form method="GET" action="{{ route('giang-vien.don-xin-nghi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="dxn-flabel">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="cho_duyet" @selected(($filters['trang_thai'] ?? null) === 'cho_duyet')>Chờ duyệt</option>
                        <option value="da_duyet"  @selected(($filters['trang_thai'] ?? null) === 'da_duyet')>Đã duyệt</option>
                        <option value="tu_choi"   @selected(($filters['trang_thai'] ?? null) === 'tu_choi')>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fas fa-search me-1"></i> Lọc
                    </button>
                    <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left me-1"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- ========== ③ Danh sách đơn ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách đơn đã gửi</h2>
                    <p>Theo dõi trạng thái duyệt và phản hồi của admin cho từng đơn.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $leaveRequests->total() }}</strong> kết quả</span>
            </div>
        </header>

        <div class="dxn-table-wrap">
            @if($leaveRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0 dxn-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Ngày nghỉ</th>
                                <th>Khung nghỉ</th>
                                <th>Khóa học / Module</th>
                                <th>Lý do</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4">Phản hồi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequests as $item)
                                @php
                                    $statusClass = match($item->trang_thai) {
                                        'da_duyet'  => 'is-success',
                                        'tu_choi'   => 'is-danger',
                                        'cho_duyet' => 'is-warning',
                                        default     => 'is-secondary',
                                    };
                                    $statusIcon = match($item->trang_thai) {
                                        'da_duyet'  => 'fa-check-circle',
                                        'tu_choi'   => 'fa-times-circle',
                                        'cho_duyet' => 'fa-hourglass-half',
                                        default     => 'fa-circle-question',
                                    };
                                    $maKH = $item->khoaHoc?->ma_khoa_hoc ?: $item->lichHoc?->khoaHoc?->ma_khoa_hoc;
                                    $tenModule = $item->moduleHoc?->ten_module ?: $item->lichHoc?->moduleHoc?->ten_module;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="dxn-date">
                                            <i class="far fa-calendar-alt"></i>
                                            {{ $item->ngay_xin_nghi?->format('d/m/Y') ?? '—' }}
                                        </div>
                                        <div class="dxn-date-rel">
                                            {{ $item->ngay_xin_nghi?->isoFormat('dddd') ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dxn-frame">{{ $item->schedule_range_label }}</div>
                                        <div class="dxn-frame-sub">
                                            <i class="fas fa-clock"></i> {{ $item->tiet_range_label }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dxn-course">
                                            @if($maKH)
                                                <i class="fas fa-graduation-cap"></i> {{ $maKH }}
                                            @else
                                                <span class="text-muted fst-italic">Không gắn buổi học</span>
                                            @endif
                                        </div>
                                        @if($tenModule)
                                            <div class="dxn-module">
                                                <i class="fas fa-cube"></i> {{ $tenModule }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dxn-reason">{{ $item->ly_do }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="dxn-status-pill {{ $statusClass }}">
                                            <i class="fas {{ $statusIcon }}"></i> {{ $item->trang_thai_label }}
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        @if($item->ghi_chu_phan_hoi)
                                            <div class="dxn-feedback">
                                                <i class="fas fa-comment-dots"></i>
                                                {{ $item->ghi_chu_phan_hoi }}
                                            </div>
                                        @else
                                            <span class="dxn-no-feedback">— Chưa có phản hồi —</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($leaveRequests->hasPages())
                    <div class="dxn-pagination">
                        {{ $leaveRequests->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="dxn-empty">
                    <div class="dxn-empty-icon"><i class="fas fa-calendar-xmark"></i></div>
                    <h5>Chưa có đơn xin nghỉ nào</h5>
                    <p>Bạn chưa gửi đơn xin nghỉ nào phù hợp bộ lọc hiện tại. Tạo đơn mới để xin off khi cần.</p>
                    <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-primary fw-bold">
                        <i class="fas fa-plus me-1"></i> Tạo đơn xin nghỉ
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .dxn-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .dxn-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .dxn-page .apx-section-title h2 i { color: #dc2626; }
    .dxn-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .dxn-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .dxn-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .dxn-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .dxn-loai-badge {
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
    .dxn-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .dxn-status-badge i { font-size: 0.65rem; opacity: 0.85; }
    .dxn-pending-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        animation: dxnPendingPulse 1.6s ease-out infinite;
    }
    @keyframes dxnPendingPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }
    .apx-welcome.dxn-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.dxn-welcome p i { color: #fef3c7; margin-right: 4px; }
    .dxn-sep { opacity: 0.5; }
    .dxn-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== Filter card ===== */
    .dxn-filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .dxn-flabel {
        display: block;
        font-weight: 800;
        color: #7f1d1d;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    /* ===== Bảng ===== */
    .dxn-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .dxn-table thead {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #7f1d1d;
        letter-spacing: 0.5px;
    }
    .dxn-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #fecaca;
    }
    .dxn-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .dxn-table tbody tr:last-child td { border-bottom: 0; }
    .dxn-table tbody tr:hover { background: #fafafa; }

    .dxn-date {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
    }
    .dxn-date i { color: #dc2626; margin-right: 5px; font-size: 0.78rem; }
    .dxn-date-rel {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 3px;
        text-transform: capitalize;
    }

    .dxn-frame {
        font-size: 0.86rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .dxn-frame-sub {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 3px;
    }
    .dxn-frame-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .dxn-course {
        font-size: 0.86rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .dxn-course i { color: #1d4ed8; margin-right: 5px; font-size: 0.74rem; }
    .dxn-module {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 3px;
    }
    .dxn-module i { color: #16a34a; margin-right: 4px; font-size: 0.66rem; }

    .dxn-reason {
        font-size: 0.82rem;
        color: #1e293b;
        line-height: 1.45;
        max-width: 280px;
    }

    /* Status pill */
    .dxn-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .dxn-status-pill i { font-size: 0.66rem; }
    .dxn-status-pill.is-success   { background: #dcfce7; color: #16a34a; }
    .dxn-status-pill.is-warning   { background: #fef3c7; color: #c2410c; }
    .dxn-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .dxn-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    /* Feedback */
    .dxn-feedback {
        font-size: 0.82rem;
        color: #1e293b;
        line-height: 1.45;
        padding: 8px 12px;
        background: #eff6ff;
        border-left: 3px solid #1d4ed8;
        border-radius: 8px;
        max-width: 300px;
    }
    .dxn-feedback i { color: #1d4ed8; margin-right: 5px; font-size: 0.74rem; }
    .dxn-no-feedback {
        font-size: 0.78rem;
        color: #cbd5e1;
        font-style: italic;
    }

    /* Pagination */
    .dxn-pagination {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: center;
    }
    .dxn-pagination nav { margin: 0; }

    /* Empty state */
    .dxn-empty {
        padding: 60px 30px;
        text-align: center;
    }
    .dxn-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626;
        font-size: 2rem;
    }
    .dxn-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .dxn-empty p {
        font-size: 0.9rem; color: #94a3b8;
        max-width: 460px; margin: 0 auto 20px;
        line-height: 1.55;
    }

    @media (max-width: 991.98px) {
        .dxn-table { font-size: 0.85rem; }
        .dxn-table thead th, .dxn-table tbody td { padding: 10px 8px; }
        .dxn-reason, .dxn-feedback { max-width: none; }
    }
</style>
@endsection
