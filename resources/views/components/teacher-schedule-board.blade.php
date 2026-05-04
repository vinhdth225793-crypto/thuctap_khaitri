@php
    $scheduleView = $scheduleView ?? null;
@endphp

@if($scheduleView)
    @php
        $weekStart = \Carbon\Carbon::parse($scheduleView['week_start']);
        $prevWeek = $weekStart->copy()->subWeek()->toDateString();
        $nextWeek = $weekStart->copy()->addWeek()->toDateString();
        $currentWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $isTeacherView = request()->routeIs('giang-vien.*');
        $scheduledItems = collect($scheduleView['scheduled_items'] ?? []);
        $leaveRequestItems = collect($scheduleView['leave_request_items'] ?? []);
        $scheduledByDate = $scheduledItems->groupBy('date');
        $leaveRequestByDate = $leaveRequestItems->groupBy('date');

        // Tổng số phút giảng dạy trong tuần (ước tính theo period_label hoặc grid time)
        $totalMinutes = $scheduledItems->sum(function ($item) {
            // Lấy time từ format "07:00 - 09:30" nếu có
            $time = $item['time'] ?? null;
            if (!$time) return 0;
            if (preg_match('/(\d{1,2}):(\d{2})\s*[-–]\s*(\d{1,2}):(\d{2})/', $time, $m)) {
                return (((int)$m[3] * 60 + (int)$m[4]) - ((int)$m[1] * 60 + (int)$m[2]));
            }
            return 0;
        });
        $totalHours = $totalMinutes > 0 ? round($totalMinutes / 60, 1) : null;
    @endphp

    <div class="schedule-board-compact mb-4">
        <h6 class="visually-hidden">Thời khóa biểu theo tuần — Danh sách buổi dạy tuần này</h6>
        {{-- ===== Header: tuần navigation + thống kê ===== --}}
        <div class="sbc-head">
            <div class="sbc-week-nav">
                <a href="{{ request()->fullUrlWithQuery(['week_start' => $prevWeek]) }}" class="sbc-nav-btn" title="Tuần trước">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <div class="sbc-week-label">
                    <span class="sbc-week-num">Tuần {{ $weekStart->weekOfYear }}</span>
                    <strong>{{ $weekStart->format('d/m') }} – {{ \Carbon\Carbon::parse($scheduleView['week_end'])->format('d/m/Y') }}</strong>
                </div>
                <a href="{{ request()->fullUrlWithQuery(['week_start' => $nextWeek]) }}" class="sbc-nav-btn" title="Tuần sau">
                    <i class="fas fa-chevron-right"></i>
                </a>
                @if($scheduleView['week_start'] !== $currentWeek)
                    <a href="{{ request()->fullUrlWithQuery(['week_start' => $currentWeek]) }}" class="sbc-today-btn">
                        <i class="fas fa-arrow-rotate-left"></i> Tuần này
                    </a>
                @endif
            </div>

            <div class="sbc-summary">
                <span class="sbc-pill pill-primary">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <strong>{{ $scheduledItems->count() }}</strong> buổi
                </span>
                @if($totalHours)
                    <span class="sbc-pill pill-info">
                        <i class="fas fa-stopwatch"></i>
                        <strong>{{ $totalHours }}</strong> giờ
                    </span>
                @endif
                @if($leaveRequestItems->isNotEmpty())
                    <span class="sbc-pill pill-warning">
                        <i class="fas fa-calendar-minus"></i>
                        <strong>{{ $leaveRequestItems->count() }}</strong> đơn nghỉ
                    </span>
                @endif
            </div>
        </div>

        {{-- ===== Desktop: Grid 7 ngày × tiết ===== --}}
        <div class="sbc-grid-wrap d-none d-lg-block">
            <table class="sbc-grid">
                <thead>
                    <tr>
                        <th class="sbc-period-head">Tiết</th>
                        @foreach($scheduleView['days'] as $day)
                            @php $isToday = $day['date'] === today()->toDateString(); @endphp
                            <th class="sbc-day-head {{ $isToday ? 'is-today' : '' }}">
                                <span class="sbc-day-thu">{{ $day['thu_label'] }}</span>
                                <span class="sbc-day-date">{{ $day['label'] }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($scheduleView['grid'] as $row)
                        <tr>
                            <td class="sbc-period-cell">
                                <strong>{{ $row['period'] }}</strong>
                                <small>{{ $row['time'] }}</small>
                            </td>
                            @foreach($scheduleView['days'] as $day)
                                @php
                                    $cell = $row['cells'][$day['date']] ?? ['scheduled' => [], 'leave_requests' => []];
                                    $isToday = $day['date'] === today()->toDateString();
                                @endphp
                                <td class="sbc-cell {{ $isToday ? 'is-today' : '' }}">
                                    @foreach($cell['scheduled'] as $item)
                                        @php
                                            $sessionType = $item['session'] ?? 'default';
                                            $shortName = \Illuminate\Support\Str::limit($item['module_name'] ?: $item['course_name'], 30);
                                            $hoverDetail = ($item['module_name'] ?: $item['course_name'])
                                                . "\n" . ($item['course_code'] ?? '')
                                                . ($item['summary'] ? "\n" . $item['summary'] : '');
                                        @endphp
                                        <a href="{{ $isTeacherView ? route('giang-vien.khoa-hoc.show', $item['course_id']) : '#' }}"
                                           class="sbc-item session-{{ $sessionType }}"
                                           title="{{ trim($hoverDetail) }}">
                                            <span class="sbc-item-time">
                                                <i class="far fa-clock"></i>
                                                {{ \Illuminate\Support\Str::of($item['time'] ?? '')->replaceMatches('/\s*[-–]\s*/', '–') }}
                                            </span>
                                            <strong class="sbc-item-name">{{ $shortName }}</strong>
                                            <span class="sbc-item-meta">
                                                <span class="sbc-code">{{ $item['course_code'] }}</span>
                                                @if($item['buoi_so'])
                                                    <span class="sbc-buoi">B{{ $item['buoi_so'] }}</span>
                                                @endif
                                            </span>
                                            @if($item['leave_status_label'])
                                                <span class="sbc-leave-tag tag-{{ $item['leave_status_color'] }}">
                                                    <i class="fas fa-calendar-minus"></i> {{ $item['leave_status_label'] }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach

                                    @foreach($cell['leave_requests'] as $item)
                                        <div class="sbc-item leave-only border-{{ $item['status_color'] }}" title="{{ $item['summary'] }}">
                                            <span class="sbc-item-time"><i class="far fa-calendar-minus"></i></span>
                                            <strong class="text-{{ $item['status_color'] }}">{{ $item['status_label'] }}</strong>
                                            <span class="sbc-item-meta text-muted">{{ \Illuminate\Support\Str::limit($item['summary'], 28) }}</span>
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ===== Mobile: list từng ngày ===== --}}
        <div class="sbc-mobile d-lg-none">
            @foreach($scheduleView['days'] as $day)
                @php
                    $isToday = $day['date'] === today()->toDateString();
                    $dayItems = $scheduledByDate->get($day['date'], collect());
                    $dayLeaves = $leaveRequestByDate->get($day['date'], collect());
                @endphp
                <div class="sbc-day-row {{ $isToday ? 'is-today' : '' }}">
                    <div class="sbc-day-strip">
                        <strong>{{ $day['thu_label'] }}</strong>
                        <small>{{ $day['label'] }}</small>
                        @if($dayItems->count() + $dayLeaves->count() > 0)
                            <span class="sbc-day-count">{{ $dayItems->count() + $dayLeaves->count() }}</span>
                        @endif
                    </div>
                    <div class="sbc-day-items">
                        @forelse($dayItems as $item)
                            @php $sessionType = $item['session'] ?? 'default'; @endphp
                            <a href="{{ $isTeacherView ? route('giang-vien.khoa-hoc.show', $item['course_id']) : '#' }}"
                               class="sbc-item session-{{ $sessionType }}">
                                <span class="sbc-item-time"><i class="far fa-clock"></i> {{ $item['time'] ?? $item['period_label'] }}</span>
                                <strong class="sbc-item-name">{{ \Illuminate\Support\Str::limit($item['module_name'] ?: $item['course_name'], 40) }}</strong>
                                <span class="sbc-item-meta">
                                    <span class="sbc-code">{{ $item['course_code'] }}</span>
                                    @if($item['buoi_so'])<span class="sbc-buoi">B{{ $item['buoi_so'] }}</span>@endif
                                </span>
                            </a>
                        @empty
                            @if($dayLeaves->isEmpty())
                                <div class="sbc-day-empty">— Không có lịch —</div>
                            @endif
                        @endforelse

                        @foreach($dayLeaves as $item)
                            <div class="sbc-item leave-only border-{{ $item['status_color'] }}">
                                <strong class="text-{{ $item['status_color'] }}"><i class="fas fa-calendar-minus"></i> {{ $item['status_label'] }}</strong>
                                <span class="sbc-item-meta text-muted">{{ $item['summary'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        /* ===== Compact schedule board ===== */
        .schedule-board-compact {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        }

        .sbc-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }

        .sbc-week-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sbc-nav-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #1d4ed8;
            display: grid;
            place-items: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .sbc-nav-btn:hover { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }

        .sbc-week-label {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            text-align: center;
            min-width: 140px;
        }
        .sbc-week-num {
            font-size: 0.65rem;
            font-weight: 800;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .sbc-week-label strong { font-size: 0.92rem; color: #0f172a; font-weight: 800; }

        .sbc-today-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 12px;
            background: #fff;
            border: 1px solid #1d4ed8;
            border-radius: 999px;
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .sbc-today-btn:hover { background: #1d4ed8; color: #fff; }

        .sbc-summary {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .sbc-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #fff;
            border: 1px solid;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .sbc-pill strong { font-weight: 800; }
        .sbc-pill.pill-primary  { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }
        .sbc-pill.pill-info     { color: #0369a1; border-color: #bae6fd; background: #f0f9ff; }
        .sbc-pill.pill-warning  { color: #c2410c; border-color: #fed7aa; background: #fff7ed; }

        /* ===== Grid desktop ===== */
        .sbc-grid-wrap { overflow-x: auto; }

        .sbc-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.82rem;
        }

        .sbc-grid th, .sbc-grid td {
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }
        .sbc-grid th:last-child, .sbc-grid td:last-child { border-right: 0; }
        .sbc-grid tr:last-child td { border-bottom: 0; }

        .sbc-period-head, .sbc-day-head {
            background: #f8fafc;
            padding: 10px 8px;
            text-align: center;
            font-weight: 700;
            color: #475569;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .sbc-period-head {
            min-width: 80px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .sbc-day-head.is-today {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
        }

        .sbc-day-thu { display: block; font-weight: 800; font-size: 0.82rem; }
        .sbc-day-date { display: block; font-size: 0.7rem; font-weight: 600; opacity: 0.75; margin-top: 1px; }

        .sbc-period-cell {
            background: #f8fafc;
            text-align: center;
            padding: 8px 6px;
            min-width: 80px;
        }
        .sbc-period-cell strong {
            display: block;
            font-size: 0.85rem;
            color: #1d4ed8;
            font-weight: 800;
        }
        .sbc-period-cell small {
            display: block;
            font-size: 0.68rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .sbc-cell {
            vertical-align: top;
            padding: 4px;
            min-width: 130px;
            min-height: 60px;
            position: relative;
        }
        .sbc-cell.is-today { background: rgba(29, 78, 216, 0.03); }

        /* ===== Item card trong cell ===== */
        .sbc-item {
            display: block;
            padding: 6px 8px;
            margin-bottom: 4px;
            border-radius: 8px;
            border-left: 3px solid #94a3b8;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            text-decoration: none !important;
            color: #0f172a !important;
            transition: all 0.18s ease;
            position: relative;
        }

        .sbc-item:last-child { margin-bottom: 0; }

        .sbc-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.1);
            z-index: 5;
        }

        .sbc-item-time {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 0.68rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2px;
        }
        .sbc-item-time i { font-size: 0.62rem; }

        .sbc-item-name {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 3px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .sbc-item-meta {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.66rem;
            font-weight: 600;
        }

        .sbc-code {
            color: #1d4ed8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .sbc-buoi {
            margin-left: auto;
            padding: 1px 6px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 4px;
            font-size: 0.62rem;
            font-weight: 800;
        }

        /* Session colors — border-left + tint background */
        .session-sang   { border-left-color: #0d6efd; background: linear-gradient(135deg, #eff6ff 0%, #fff 60%); }
        .session-chieu  { border-left-color: #fd7e14; background: linear-gradient(135deg, #fff7ed 0%, #fff 60%); }
        .session-toi    { border-left-color: #6610f2; background: linear-gradient(135deg, #f5f3ff 0%, #fff 60%); }
        .session-default{ border-left-color: #475569; background: #fff; }

        .session-sang:hover  { border-left-color: #0d6efd; background: #eff6ff; }
        .session-chieu:hover { border-left-color: #fd7e14; background: #fff7ed; }
        .session-toi:hover   { border-left-color: #6610f2; background: #f5f3ff; }

        .sbc-leave-tag {
            display: block;
            margin-top: 4px;
            padding: 2px 6px;
            font-size: 0.6rem;
            font-weight: 700;
            border-radius: 4px;
            text-align: center;
        }
        .tag-warning { background: #fef3c7; color: #c2410c; }
        .tag-success { background: #dcfce7; color: #16a34a; }
        .tag-secondary { background: #f1f5f9; color: #475569; }
        .tag-danger { background: #fee2e2; color: #b91c1c; }

        .sbc-item.leave-only {
            background: #fff7ed !important;
            border: 1px dashed #fed7aa;
            border-left: 3px solid #f97316 !important;
        }
        .sbc-item.leave-only.border-success { border-left-color: #16a34a !important; background: #dcfce7 !important; border-color: #6ee7b7; }
        .sbc-item.leave-only.border-danger  { border-left-color: #dc2626 !important; background: #fee2e2 !important; border-color: #fca5a5; }
        .sbc-item.leave-only.border-warning { border-left-color: #d97706 !important; }
        .sbc-item.leave-only.border-secondary{ border-left-color: #475569 !important; background: #f1f5f9 !important; border-color: #cbd5e1; }

        /* ===== Mobile list ===== */
        .sbc-mobile { padding: 10px; display: flex; flex-direction: column; gap: 8px; }

        .sbc-day-row {
            display: flex;
            gap: 10px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .sbc-day-row.is-today {
            background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%);
            border-color: #93c5fd;
        }

        .sbc-day-strip {
            flex-shrink: 0;
            min-width: 70px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding-right: 10px;
            border-right: 2px dashed #cbd5e1;
            position: relative;
        }
        .sbc-day-strip strong { font-size: 0.85rem; font-weight: 800; color: #0f172a; }
        .sbc-day-strip small { font-size: 0.7rem; color: #64748b; }

        .sbc-day-count {
            position: absolute;
            top: 0;
            right: 8px;
            min-width: 20px;
            padding: 1px 6px;
            background: #1d4ed8;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 800;
            border-radius: 999px;
            text-align: center;
        }

        .sbc-day-items {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .sbc-day-empty {
            font-size: 0.78rem;
            color: #94a3b8;
            font-style: italic;
            padding: 6px 0;
        }

        @media (max-width: 720px) {
            .sbc-head { padding: 10px 12px; }
            .sbc-summary { width: 100%; justify-content: flex-start; }
        }
    </style>
@endif
