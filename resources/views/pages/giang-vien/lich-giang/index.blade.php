@extends('layouts.app')

@section('title', 'Lịch dạy của tôi')

@section('content')
@php
    $today = \Carbon\Carbon::today();
    $tomorrow = $today->copy()->addDay();
    $endOfWeek = $today->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);

    $todaySchedules = $upcomingSchedules->filter(fn ($s) => $s->ngay_hoc && $s->ngay_hoc->isSameDay($today));
    $tomorrowSchedules = $upcomingSchedules->filter(fn ($s) => $s->ngay_hoc && $s->ngay_hoc->isSameDay($tomorrow));
    $thisWeekSchedules = $upcomingSchedules->filter(fn ($s) => $s->ngay_hoc && $s->ngay_hoc->between($today, $endOfWeek));
    $onlineCount = $upcomingSchedules->where('hinh_thuc', 'online')->count();
    $offlineCount = $upcomingSchedules->where('hinh_thuc', 'truc_tiep')->count();

    $relativeBadge = function ($date) use ($today, $tomorrow) {
        if (!$date) return ['Chưa rõ', 'secondary'];
        if ($date->isSameDay($today)) return ['Hôm nay', 'danger'];
        if ($date->isSameDay($tomorrow)) return ['Ngày mai', 'warning'];
        $diff = (int) $today->diffInDays($date, false);
        if ($diff > 0 && $diff <= 7) return ['Trong tuần', 'info'];
        return [$date->format('d/m'), 'secondary'];
    };

    $sessionStatus = function ($schedule) {
        $now = now();
        $startsAt = $schedule->starts_at ?? null;
        $endsAt = $schedule->ends_at ?? null;
        if ($schedule->trang_thai === 'huy') return ['Đã hủy', 'secondary', 'fa-ban'];
        if ($schedule->trang_thai === 'hoan_thanh') return ['Đã kết thúc', 'success', 'fa-check'];
        if ($startsAt && $endsAt && $now->between($startsAt, $endsAt)) return ['Đang diễn ra', 'success', 'fa-circle-play'];
        if ($startsAt && $now->lessThan($startsAt)) {
            $minutesUntil = (int) $now->diffInMinutes($startsAt, false);
            if ($minutesUntil <= 30) return ['Sắp bắt đầu (' . $minutesUntil . ' phút)', 'warning', 'fa-bell'];
            if ($minutesUntil <= 180) return ['Còn ' . round($minutesUntil / 60, 1) . ' giờ', 'info', 'fa-hourglass-half'];
        }
        return ['Chưa bắt đầu', 'secondary', 'fa-clock'];
    };
@endphp

<div class="container-fluid py-4 teacher-schedule-page">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}">Giảng viên</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lịch giảng</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1"><i class="fas fa-calendar-days text-primary me-2"></i>Lịch dạy của tôi</h4>
            <p class="text-muted mb-0">Theo dõi thời khóa biểu, trạng thái buổi học và quản lý đơn xin nghỉ giảng.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-light border shadow-sm px-3">
                <i class="fas fa-book-open me-2"></i>Lộ trình giảng dạy
            </a>
            <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-primary shadow-sm px-3">
                <i class="fas fa-paper-plane me-2"></i>Gửi đơn xin nghỉ
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- Highlight: BUỔI HỌC HÔM NAY --}}
    @if($todaySchedules->isNotEmpty())
        <div class="today-highlight mb-4">
            <div class="today-highlight-head">
                <div class="th-icon"><i class="fas fa-fire"></i></div>
                <div>
                    <strong>Buổi học hôm nay</strong>
                    <small>{{ $todaySchedules->count() }} buổi cần dạy — {{ $today->format('l, d/m/Y') }}</small>
                </div>
                <span class="th-pulse-dot" aria-hidden="true"></span>
            </div>
            <div class="today-highlight-list">
                @foreach($todaySchedules as $schedule)
                    @php [$statusLabel, $statusColor, $statusIcon] = $sessionStatus($schedule); @endphp
                    <div class="today-card">
                        <div class="tc-time">
                            <strong>{{ substr((string) $schedule->gio_bat_dau, 0, 5) }}</strong>
                            <span>—</span>
                            <strong>{{ substr((string) $schedule->gio_ket_thuc, 0, 5) }}</strong>
                        </div>
                        <div class="tc-info">
                            <h6>{{ $schedule->moduleHoc?->ten_module ?? 'Buổi học' }}</h6>
                            <small><i class="fas fa-graduation-cap"></i> {{ $schedule->khoaHoc?->ten_khoa_hoc }}</small>
                            <div class="tc-tags">
                                <span class="tc-status status-{{ $statusColor }}"><i class="fas {{ $statusIcon }}"></i> {{ $statusLabel }}</span>
                                @if($schedule->hinh_thuc === 'online')
                                    <span class="tc-mode mode-online"><i class="fas fa-video"></i> Online</span>
                                @else
                                    <span class="tc-mode mode-offline"><i class="fas fa-school"></i> {{ $schedule->phong_hoc ?: 'Trực tiếp' }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="tc-actions">
                            <a href="{{ $schedule->phan_cong_id ? route('giang-vien.khoa-hoc.show', $schedule->phan_cong_id) : route('giang-vien.khoa-hoc') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-door-open me-1"></i> Vào lớp
                            </a>
                            @if($schedule->hinh_thuc === 'online' && filled($schedule->link_online))
                                <a href="{{ $schedule->link_online }}" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i> Mở meet
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ① Tổng quan tuần này --}}
    <header class="teacher-section-head">
        <div class="tsh-title">
            <span class="tsh-num">1</span>
            <div>
                <h2><i class="fas fa-chart-pie"></i> Tổng quan công việc</h2>
                <p>6 chỉ số quan trọng giúp bạn nắm nhanh khối lượng giảng dạy & đơn từ.</p>
            </div>
        </div>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="schedule-stat tone-primary">
                <div class="ss-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="ss-text">
                    <strong>{{ $todaySchedules->count() }}</strong>
                    <small>Hôm nay</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="schedule-stat tone-info">
                <div class="ss-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="ss-text">
                    <strong>{{ $thisWeekSchedules->count() }}</strong>
                    <small>Trong tuần</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="schedule-stat tone-success">
                <div class="ss-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="ss-text">
                    <strong>{{ $stats['upcoming_schedules'] }}</strong>
                    <small>Sắp tới</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="schedule-stat tone-cyan">
                <div class="ss-icon"><i class="fas fa-video"></i></div>
                <div class="ss-text">
                    <strong>{{ $onlineCount }}</strong>
                    <small>Buổi online</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="schedule-stat tone-warning">
                <div class="ss-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="ss-text">
                    <strong>{{ $stats['leave_requests_pending'] }}</strong>
                    <small>Đơn chờ duyệt</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="schedule-stat tone-emerald">
                <div class="ss-icon"><i class="fas fa-check-circle"></i></div>
                <div class="ss-text">
                    <strong>{{ $stats['leave_requests_approved'] }}</strong>
                    <small>Đơn đã duyệt</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ② Lịch tuần (board) --}}
    <header class="teacher-section-head">
        <div class="tsh-title">
            <span class="tsh-num">2</span>
            <div>
                <h2><i class="fas fa-table-cells-large"></i> Lịch tuần dạy</h2>
                <p>Xem tổng thể các buổi học trong tuần. Click vào buổi để xem chi tiết.</p>
            </div>
        </div>
    </header>

    @include('components.teacher-schedule-board', [
        'scheduleView' => $scheduleView,
    ])

    {{-- ③ Chi tiết buổi sắp tới + đơn xin nghỉ --}}
    <header class="teacher-section-head">
        <div class="tsh-title">
            <span class="tsh-num">3</span>
            <div>
                <h2><i class="fas fa-clipboard-list"></i> Chi tiết buổi dạy & đơn từ</h2>
                <p>Danh sách buổi học sắp tới có badge thời gian, trạng thái và nhanh gửi đơn xin nghỉ.</p>
            </div>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Buổi dạy sắp tới</h5>
                        <p class="text-muted small mb-0">{{ $upcomingSchedules->count() }} buổi tiếp theo trong lộ trình giảng dạy.</p>
                    </div>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-sm btn-light border px-3">
                        <i class="fas fa-list me-1"></i> Xem tất cả
                    </a>
                </div>
                <div class="card-body p-3 p-lg-4 pt-2">
                    @forelse($upcomingSchedules as $schedule)
                        @php
                            [$badgeLabel, $badgeColor] = $relativeBadge($schedule->ngay_hoc);
                            [$statusLabel, $statusColor, $statusIcon] = $sessionStatus($schedule);
                        @endphp
                        <div class="upcoming-card border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <span class="rel-badge rel-{{ $badgeColor }}">{{ $badgeLabel }}</span>
                                        <span class="rel-badge rel-{{ $statusColor }}"><i class="fas {{ $statusIcon }} me-1"></i>{{ $statusLabel }}</span>
                                        <span class="text-muted small"><i class="fas fa-hashtag"></i> Buổi {{ $schedule->buoi_so ?? '?' }}</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">{{ $schedule->moduleHoc?->ten_module ?? 'Buổi học' }}</h6>
                                    <div class="small text-muted">
                                        <i class="fas fa-graduation-cap text-primary"></i> {{ $schedule->khoaHoc?->ten_khoa_hoc }}
                                    </div>
                                </div>
                                <div class="upcoming-time">
                                    <i class="far fa-clock text-primary"></i>
                                    <span>{{ substr((string) $schedule->gio_bat_dau, 0, 5) }} - {{ substr((string) $schedule->gio_ket_thuc, 0, 5) }}</span>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 small text-muted mb-3">
                                <span class="upcoming-meta"><i class="far fa-calendar-alt text-primary"></i> {{ $schedule->ngay_hoc?->format('l, d/m/Y') }}</span>
                                @if($schedule->hinh_thuc === 'online')
                                    <span class="upcoming-meta"><i class="fas fa-video text-info"></i> Online</span>
                                    @if(filled($schedule->link_online))
                                        <span class="upcoming-meta upcoming-link" title="{{ $schedule->link_online }}">
                                            <i class="fas fa-link text-info"></i>
                                            <span class="text-truncate" style="max-width: 220px; display: inline-block; vertical-align: middle;">{{ $schedule->link_online }}</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="upcoming-meta"><i class="fas fa-school text-warning"></i> {{ $schedule->phong_hoc ?: 'Trực tiếp' }}</span>
                                @endif
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ $schedule->phan_cong_id ? route('giang-vien.khoa-hoc.show', $schedule->phan_cong_id) : route('giang-vien.khoa-hoc') }}" class="btn btn-sm btn-primary px-3">
                                    <i class="fas fa-door-open me-1"></i> Vào lớp học
                                </a>
                                @if($schedule->hinh_thuc === 'online' && filled($schedule->link_online))
                                    <a href="{{ $schedule->link_online }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info px-3">
                                        <i class="fas fa-external-link-alt me-1"></i> Mở meet
                                    </a>
                                @endif
                                <a href="{{ route('giang-vien.don-xin-nghi.create', ['lich_hoc_id' => $schedule->id]) }}" class="btn btn-sm btn-outline-warning px-3">
                                    <i class="fas fa-user-clock me-1"></i> Xin nghỉ
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="empty-state-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h6 class="fw-bold mt-3">Không có buổi dạy sắp tới</h6>
                            <p class="text-muted small mb-3">Bạn đang trong khoảng nghỉ giữa các khóa, hoặc chưa nhận lịch giảng mới.</p>
                            <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-book-open me-1"></i> Xem lộ trình giảng dạy
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Đơn xin nghỉ gần đây</h5>
                        <p class="text-muted small mb-0">Trạng thái phản hồi từ quản trị viên.</p>
                    </div>
                    <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-sm btn-light border px-3">Xem tất cả</a>
                </div>
                <div class="card-body p-3 p-lg-4 pt-2">
                    @forelse($recentLeaveRequests as $item)
                        <div class="leave-card border rounded-3 p-3 mb-3 border-start border-4 border-{{ $item->trang_thai_color }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold text-dark">
                                        <i class="far fa-calendar-alt text-{{ $item->trang_thai_color }} me-1"></i>
                                        {{ $item->ngay_xin_nghi?->format('d/m/Y') }}
                                    </div>
                                    <div class="small text-muted">{{ optional($item->created_at)->diffForHumans() }}</div>
                                </div>
                                <span class="badge rounded-pill bg-{{ $item->trang_thai_color }}-subtle text-{{ $item->trang_thai_color }} border border-{{ $item->trang_thai_color }}-subtle px-3 py-2">
                                    {{ $item->trang_thai_label }}
                                </span>
                            </div>
                            <div class="small fw-semibold text-primary mb-2">
                                <i class="fas fa-cube"></i> {{ $item->moduleHoc?->ten_module ?: 'Nghỉ cả ngày' }}
                                @if($item->khoaHoc)
                                    · {{ $item->khoaHoc->ten_khoa_hoc }}
                                @endif
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-quote-left"></i> {{ $item->ly_do }}
                            </div>
                            @if($item->ghi_chu_phan_hoi)
                                <div class="leave-feedback">
                                    <i class="fas fa-comment-dots text-secondary me-1"></i>
                                    <strong>Phản hồi:</strong> {{ $item->ghi_chu_phan_hoi }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="empty-state-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <h6 class="fw-bold mt-3">Chưa có đơn xin nghỉ</h6>
                            <p class="text-muted small mb-3">Khi cần nghỉ một buổi dạy, bạn có thể gửi đơn để admin xem xét.</p>
                            <a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Gửi đơn ngay
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== Section heading (giống dashboard giảng viên) ===== */
    .teacher-schedule-page {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .teacher-section-head {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        margin-bottom: 14px;
        margin-top: 8px;
        background: linear-gradient(135deg, #ffffff 0%, #dbeafe 100%);
        border: 1px solid #bfdbfe;
        border-left: 4px solid #1d4ed8;
        border-radius: 12px;
    }

    .tsh-title { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }

    .tsh-num {
        flex-shrink: 0;
        width: 36px; height: 36px;
        border-radius: 12px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 900; font-size: 1rem;
        box-shadow: 0 6px 16px rgba(29, 78, 216, 0.3);
    }

    .tsh-title h2 { font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 2px; display: inline-flex; align-items: center; gap: 8px; }
    .tsh-title h2 i { font-size: 0.95rem; color: #1d4ed8; }
    .tsh-title p { margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4; }

    /* ===== Today highlight ===== */
    .today-highlight {
        background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);
        border: 1px solid #fdba74;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(249, 115, 22, 0.12);
    }

    .today-highlight-head {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #fff;
        position: relative;
    }

    .th-icon {
        width: 40px; height: 40px;
        background: rgba(255,255,255,0.22);
        border-radius: 12px;
        display: grid; place-items: center;
        font-size: 1.15rem;
        backdrop-filter: blur(8px);
    }

    .today-highlight-head strong { display: block; font-size: 1rem; font-weight: 800; }
    .today-highlight-head small { display: block; font-size: 0.78rem; opacity: 0.92; margin-top: 2px; }

    .th-pulse-dot {
        margin-left: auto;
        width: 12px; height: 12px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 0 0 0 rgba(255,255,255,0.7);
        animation: thPulse 1.6s infinite;
    }

    @keyframes thPulse {
        0% { box-shadow: 0 0 0 0 rgba(255,255,255,0.7); }
        70% { box-shadow: 0 0 0 12px rgba(255,255,255,0); }
        100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
    }

    .today-highlight-list {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .today-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid #fed7aa;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .today-card:hover {
        border-color: #f97316;
        transform: translateX(2px);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.15);
    }

    .tc-time {
        flex-shrink: 0;
        text-align: center;
        padding-right: 14px;
        border-right: 2px dashed #fed7aa;
        min-width: 90px;
    }
    .tc-time strong { display: block; font-size: 1.05rem; font-weight: 800; color: #c2410c; }
    .tc-time span { display: block; font-size: 0.7rem; color: #94a3b8; line-height: 1; margin: 2px 0; }

    .tc-info { flex: 1; min-width: 0; }
    .tc-info h6 { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0 0 3px; }
    .tc-info small { display: block; font-size: 0.78rem; color: #64748b; margin-bottom: 6px; }
    .tc-info small i { color: #1d4ed8; margin-right: 4px; }

    .tc-tags { display: flex; gap: 6px; flex-wrap: wrap; }
    .tc-status, .tc-mode {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }

    .status-success  { background: #dcfce7; color: #16a34a; }
    .status-warning  { background: #fef3c7; color: #c2410c; }
    .status-info     { background: #e0f2fe; color: #0369a1; }
    .status-danger   { background: #fee2e2; color: #b91c1c; }
    .status-secondary{ background: #f1f5f9; color: #475569; }

    .mode-online  { background: #dbeafe; color: #1d4ed8; }
    .mode-offline { background: #fef3c7; color: #c2410c; }

    .tc-actions { flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; align-items: stretch; }
    .tc-actions .btn { white-space: nowrap; }

    /* ===== Schedule stats ===== */
    .schedule-stat {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        height: 100%;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .schedule-stat::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: var(--ss-color, #4361ee);
        transform: scaleY(0);
        transform-origin: top center;
        transition: transform 0.2s ease;
    }

    .schedule-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
        border-color: var(--ss-color, #4361ee);
    }

    .schedule-stat:hover::before { transform: scaleY(1); }

    .schedule-stat.tone-primary  { --ss-color: #ef4444; }
    .schedule-stat.tone-info     { --ss-color: #1d4ed8; }
    .schedule-stat.tone-success  { --ss-color: #16a34a; }
    .schedule-stat.tone-cyan     { --ss-color: #0891b2; }
    .schedule-stat.tone-warning  { --ss-color: #d97706; }
    .schedule-stat.tone-emerald  { --ss-color: #059669; }

    .ss-icon {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 12px;
        display: grid; place-items: center;
        background: color-mix(in srgb, var(--ss-color) 12%, white);
        color: var(--ss-color);
        font-size: 1.05rem;
    }

    .schedule-stat:hover .ss-icon {
        background: var(--ss-color);
        color: #fff;
        transform: scale(1.05);
    }

    .ss-text strong {
        display: block;
        font-size: 1.4rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
    }

    .ss-text small {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    /* ===== Upcoming card ===== */
    .upcoming-card {
        background: #fff;
        transition: all 0.25s ease;
    }

    .upcoming-card:hover {
        border-color: #1d4ed8 !important;
        box-shadow: 0 10px 24px rgba(29, 78, 216, 0.1);
        transform: translateX(2px);
    }

    .upcoming-time {
        flex-shrink: 0;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .upcoming-meta {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px;
        background: #f8fafc;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .upcoming-meta i { font-size: 0.74rem; }
    .upcoming-link { max-width: 320px; }

    .rel-badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .rel-danger    { background: #fee2e2; color: #b91c1c; }
    .rel-warning   { background: #fef3c7; color: #c2410c; }
    .rel-info      { background: #e0f2fe; color: #0369a1; }
    .rel-success   { background: #dcfce7; color: #16a34a; }
    .rel-secondary { background: #f1f5f9; color: #475569; }

    /* ===== Leave card ===== */
    .leave-card {
        background: #fff;
        transition: all 0.2s ease;
    }

    .leave-card:hover {
        background: #f8fafc;
        transform: translateX(2px);
    }

    .leave-feedback {
        margin-top: 8px;
        padding: 8px 12px;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 0.78rem;
        color: #475569;
        line-height: 1.45;
    }

    /* ===== Empty state ===== */
    .empty-state-icon {
        width: 72px; height: 72px;
        margin: 0 auto;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: grid; place-items: center;
        font-size: 1.8rem;
    }

    /* ===== Bootstrap subtle backgrounds (đảm bảo có) ===== */
    .bg-primary-subtle  { background-color: rgba(29, 78, 216, 0.1); }
    .bg-warning-subtle  { background-color: rgba(217, 119, 6, 0.12); }
    .bg-success-subtle  { background-color: rgba(22, 163, 74, 0.12); }
    .bg-info-subtle     { background-color: rgba(8, 145, 178, 0.12); }
    .bg-danger-subtle   { background-color: rgba(220, 38, 38, 0.12); }
    .bg-secondary-subtle{ background-color: rgba(100, 116, 139, 0.12); }

    /* ===== Responsive ===== */
    @media (max-width: 720px) {
        .teacher-section-head { padding: 12px 14px; }
        .tsh-title h2 { font-size: 0.95rem; }
        .tsh-title p { font-size: 0.76rem; }
        .tsh-num { width: 32px; height: 32px; font-size: 0.9rem; }

        .today-card { flex-direction: column; align-items: flex-start; }
        .tc-time { border-right: 0; border-bottom: 2px dashed #fed7aa; padding: 0 0 8px; min-width: 0; width: 100%; text-align: left; display: flex; gap: 6px; align-items: center; }
        .tc-time span { display: inline; }
        .tc-actions { flex-direction: row; width: 100%; }
        .tc-actions .btn { flex: 1; }

        .upcoming-time { font-size: 0.78rem; padding: 4px 10px; }
        .ss-text strong { font-size: 1.15rem; }
    }
</style>
@endsection
