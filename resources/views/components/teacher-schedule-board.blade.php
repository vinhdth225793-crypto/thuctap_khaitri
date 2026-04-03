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
        $activeDayCount = collect($scheduleView['days'])
            ->filter(function (array $day) use ($scheduledByDate, $leaveRequestByDate) {
                return $scheduledByDate->has($day['date']) || $leaveRequestByDate->has($day['date']);
            })
            ->count();
    @endphp

    <div class="card border-0 shadow-sm mb-4 overflow-hidden schedule-board-card">
        <div class="card-header bg-white border-0 p-4 pb-0">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                <div class="pe-xl-3">
                    <h5 class="mb-1 fw-bold">Thời khóa biểu theo tuần</h5>
                    <p class="text-muted small mb-0">Theo dõi lịch giảng dạy và các ca học trong tuần.</p>
                </div>

                <div class="schedule-week-toolbar d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center bg-light rounded-pill p-1 border shadow-xs">
                        <a href="{{ request()->fullUrlWithQuery(['week_start' => $prevWeek]) }}" class="btn btn-icon-round btn-white border-0" title="Tuần trước">
                            <i class="fas fa-chevron-left"></i>
                        </a>

                        <div class="px-4 text-center border-start border-end">
                            <div class="smaller text-uppercase fw-bold text-primary mb-0" style="letter-spacing: 1px;">Tuần {{ $weekStart->weekOfYear }}</div>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                {{ $weekStart->format('d/m') }} - {{ \Carbon\Carbon::parse($scheduleView['week_end'])->format('d/m/Y') }}
                            </div>
                        </div>

                        <a href="{{ request()->fullUrlWithQuery(['week_start' => $nextWeek]) }}" class="btn btn-icon-round btn-white border-0" title="Tuần sau">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <div class="d-flex gap-2">
                        @if($scheduleView['week_start'] !== $currentWeek)
                            <a href="{{ request()->fullUrlWithQuery(['week_start' => $currentWeek]) }}" class="btn btn-outline-primary rounded-pill px-3 fw-bold btn-sm">
                                <i class="fas fa-undo-alt me-1"></i> Tuần này
                            </a>
                        @endif
                        <div class="badge bg-primary text-white rounded-pill px-3 py-2 d-flex align-items-center shadow-sm">
                            <i class="fas fa-chalkboard-teacher me-2"></i> {{ $scheduledItems->count() }} BUỔI DẠY
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-4 pt-3 mt-3 border-top schedule-legend">
                <div class="d-flex align-items-center gap-2 small">
                    <span class="legend-box bg-sang"></span>
                    <span class="text-muted fw-bold">Ca Sáng</span>
                </div>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="legend-box bg-chieu"></span>
                    <span class="text-muted fw-bold">Ca Chiều</span>
                </div>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="legend-box bg-toi"></span>
                    <span class="text-muted fw-bold">Ca Tối</span>
                </div>
                <div class="ms-auto d-flex gap-3">
                    <div class="d-flex align-items-center gap-2 small">
                        <span class="legend-dot bg-warning"></span>
                        <span class="text-muted">Chờ duyệt nghỉ</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 small">
                        <span class="legend-dot bg-success"></span>
                        <span class="text-muted">Đã duyệt nghỉ</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0 mt-3">
            <div class="d-lg-none p-3 pt-0">
                <div class="row g-3">
                    @foreach($scheduleView['days'] as $day)
                        @php
                            $isToday = $day['date'] === today()->toDateString();
                            $dayScheduledItems = $scheduledByDate->get($day['date'], collect());
                            $dayLeaveItems = $leaveRequestByDate->get($day['date'], collect());
                            $dayTotal = $dayScheduledItems->count() + $dayLeaveItems->count();
                        @endphp
                        <div class="col-12">
                            <div class="schedule-day-card {{ $isToday ? 'is-today' : '' }}">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <div class="fw-bold">{{ $day['thu_label'] }}</div>
                                        <div class="small text-muted">{{ $day['label'] }}</div>
                                    </div>
                                    <span class="badge rounded-pill {{ $isToday ? 'bg-primary text-white' : 'text-bg-light border' }}">
                                        {{ $dayTotal }} mục
                                    </span>
                                </div>

                                @forelse($dayScheduledItems as $item)
                                    <div class="schedule-mobile-item scheduled">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div class="fw-semibold text-dark schedule-line-clamp-2">{{ $item['module_name'] ?: $item['course_name'] }}</div>
                                            @if($item['buoi_so'])
                                                <span class="badge text-bg-light border flex-shrink-0">B{{ $item['buoi_so'] }}</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted mt-1">{{ $item['course_code'] }} @if($item['summary']) • {{ $item['summary'] }} @endif</div>
                                    </div>
                                @empty
                                @endforelse

                                @foreach($dayLeaveItems as $item)
                                    <div class="schedule-mobile-item leave-request border-{{ $item['status_color'] }}">
                                        <div class="fw-semibold text-{{ $item['status_color'] }}">{{ $item['status_label'] }}</div>
                                        <div class="small text-muted mt-1">{{ $item['summary'] }}</div>
                                    </div>
                                @endforeach

                                @if($dayTotal === 0)
                                    <div class="small text-muted">Không có lịch hoặc đơn nghỉ trong ngày này.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-none d-lg-block">
                <div class="table-responsive schedule-wrapper">
                    <table class="table table-bordered schedule-board-table mb-0 border-0">
                        <thead>
                            <tr class="text-center border-top-0">
                                <th class="sticky-column schedule-period-head">Tiết</th>
                                @foreach($scheduleView['days'] as $day)
                                    @php
                                        $isToday = $day['date'] === today()->toDateString();
                                    @endphp
                                    <th class="schedule-day-head {{ $isToday ? 'is-today' : '' }}">
                                        <div class="fw-bold {{ $isToday ? 'text-primary' : 'text-dark' }}">{{ $day['thu_label'] }}</div>
                                        <div class="small {{ $isToday ? 'text-primary' : 'text-muted' }}">{{ $day['label'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduleView['grid'] as $row)
                                <tr>
                                    <td class="sticky-column schedule-period-cell text-center">
                                        <div class="fw-bold text-primary">{{ $row['period'] }}</div>
                                        <div class="text-muted small">{{ $row['time'] }}</div>
                                    </td>

                                    @foreach($scheduleView['days'] as $day)
                                        @php
                                            $cell = $row['cells'][$day['date']] ?? ['scheduled' => [], 'leave_requests' => [], 'occupied' => false, 'has_leave_request' => false];
                                            $isToday = $day['date'] === today()->toDateString();
                                            $isEmptyCell = empty($cell['scheduled']) && empty($cell['leave_requests']);
                                        @endphp
                                        <td class="schedule-cell {{ $isToday ? 'bg-today' : '' }}">
                                            @if($isEmptyCell)
                                                <div class="schedule-cell-empty"></div>
                                            @endif

                                            @foreach($cell['scheduled'] as $item)
                                                @php
                                                    $sessionType = $item['session'] ?? null;
                                                    $sessionClass = match($sessionType) {
                                                        'sang' => 'scheduled-sang',
                                                        'chieu' => 'scheduled-chieu',
                                                        'toi' => 'scheduled-toi',
                                                        default => 'scheduled-default'
                                                    };
                                                    $sessionIcon = match($sessionType) {
                                                        'sang' => 'fa-sun text-warning',
                                                        'chieu' => 'fa-cloud-sun text-orange',
                                                        'toi' => 'fa-moon text-indigo',
                                                        default => 'fa-chalkboard'
                                                    };
                                                @endphp
                                                <div class="schedule-item scheduled {{ $sessionClass }}">
                                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                                        <div class="schedule-item-title" title="{{ $item['module_name'] }}">
                                                            <i class="fas {{ $sessionIcon }} me-1 small"></i>
                                                            {{ $item['module_name'] ?: $item['course_name'] }}
                                                        </div>
                                                        @if($item['buoi_so'])
                                                            <span class="badge bg-white text-dark border flex-shrink-0 shadow-xs" style="font-size: 0.6rem;">B{{ $item['buoi_so'] }}</span>
                                                        @endif
                                                    </div>

                                                    <div class="schedule-item-meta d-flex align-items-center gap-1" title="{{ $item['course_code'] }}">
                                                        <span class="text-truncate">{{ $item['course_code'] }}</span>
                                                    </div>

                                                    @if($item['summary'])
                                                        <div class="schedule-item-submeta text-truncate" title="{{ $item['summary'] }}">
                                                            <i class="fas fa-map-marker-alt me-1 opacity-50"></i>{{ $item['summary'] }}
                                                        </div>
                                                    @endif

                                                    @if($isTeacherView)
                                                        <div class="schedule-actions">
                                                            <a href="{{ $item['routes']['attendance'] }}" class="btn btn-white border" title="Điểm danh">
                                                                <i class="fas fa-user-check text-primary"></i>
                                                            </a>
                                                            <a href="{{ $item['routes']['resources'] }}" class="btn btn-white border" title="Tài nguyên">
                                                                <i class="fas fa-folder-open text-success"></i>
                                                            </a>
                                                            <a href="{{ $item['routes']['exams'] }}" class="btn btn-white border" title="Kiểm tra">
                                                                <i class="fas fa-file-alt text-warning"></i>
                                                            </a>
                                                            @if($item['can_leave'])
                                                                <a href="{{ $item['routes']['leave_request'] }}" class="btn btn-white border" title="Xin nghỉ">
                                                                    <i class="fas fa-calendar-minus text-danger"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($item['leave_status_label'])
                                                        <div class="mt-2 pt-2 border-top">
                                                            <span class="badge rounded-pill bg-{{ $item['leave_status_color'] }}-subtle text-{{ $item['leave_status_color'] }} border border-{{ $item['leave_status_color'] }}-subtle w-100 py-1">
                                                                {{ $item['leave_status_label'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach

                                            @foreach($cell['leave_requests'] as $item)
                                                <div class="schedule-item leave-request border-{{ $item['status_color'] }}">
                                                    <div class="fw-semibold text-{{ $item['status_color'] }} schedule-line-clamp-2">
                                                        {{ $item['status_label'] }}
                                                    </div>
                                                    <div class="schedule-item-submeta mt-1" title="{{ $item['summary'] }}">
                                                        {{ $item['summary'] }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
            <div>
                <span class="visually-hidden">Danh sách lịch dạy trong tuần</span>
                <h6 class="mb-0 fw-bold">Danh sách buổi dạy tuần này</h6>
            </div>
            @if($scheduledItems->isNotEmpty())
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                    {{ $scheduledItems->count() }} buổi
                </span>
            @endif
        </div>
        <div class="card-body p-3 pt-0">
            @if($scheduledItems->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-2">Thời gian</th>
                                <th class="border-0 py-2">Khóa học</th>
                                <th class="border-0 py-2">Module</th>
                                <th class="border-0 py-2 text-center">Trạng thái</th>
                                <th class="border-0 py-2 text-end">Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduledItems as $item)
                                <tr>
                                    <td class="py-2">
                                        <div class="fw-bold text-dark">{{ $item['date_label'] }}</div>
                                        <div class="small text-muted">{{ $item['weekday_label'] }} | {{ $item['period_label'] }}</div>
                                    </td>
                                    <td class="py-2">
                                        <div class="fw-bold text-primary">{{ $item['course_code'] }}</div>
                                    </td>
                                    <td class="py-2 text-truncate" style="max-width: 150px;">{{ $item['module_name'] }}</td>
                                    <td class="py-2 text-center">
                                        <span class="badge bg-{{ $item['status_color'] }}-subtle text-{{ $item['status_color'] }} border border-{{ $item['status_color'] }}-subtle px-2 py-1" style="font-size: 0.7rem;">
                                            {{ $item['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if($isTeacherView)
                                                <a href="{{ route('giang-vien.khoa-hoc.show', $item['course_id']) }}" class="btn btn-xs btn-white border px-2 py-1" title="Vào lớp">
                                                    <i class="fas fa-external-link-alt text-primary fa-xs"></i>
                                                </a>
                                                <a href="{{ route('giang-vien.don-xin-nghi.create', ['lich_hoc_id' => $item['id']]) }}" class="btn btn-xs btn-white border px-2 py-1" title="Xin nghỉ">
                                                    <i class="fas fa-calendar-minus text-warning fa-xs"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-3">
                    <p class="text-muted mb-0 small">Không có lịch dạy thực tế.</p>
                </div>
            @endif
        </div>
    </div>

    @if($leaveRequestItems->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3 px-3">
                <h6 class="mb-0 fw-bold">Đơn xin nghỉ trong tuần</h6>
            </div>
            <div class="card-body p-3 pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-1">Ngày</th>
                                <th class="border-0 py-1 text-center">Trạng thái</th>
                                <th class="border-0 py-1">Lý do</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequestItems as $item)
                                <tr>
                                    <td class="py-1">
                                        <div class="fw-bold text-dark">{{ $item['date_label'] }}</div>
                                        <div class="small text-muted">{{ $item['period_label'] }}</div>
                                    </td>
                                    <td class="py-1 text-center">
                                        <span class="badge bg-{{ $item['status_color'] }}-subtle text-{{ $item['status_color'] }} border border-{{ $item['status_color'] }}-subtle px-2 py-1" style="font-size: 0.65rem;">
                                            {{ $item['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-1">
                                        <div class="small text-muted text-truncate" style="max-width: 150px;">
                                            {{ $item['reason'] }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <style>
        .schedule-board-card { border-radius: 1.25rem; }
        .btn-icon-round { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
        
        /* Session Colors */
        .bg-sang { background-color: #0d6efd; }
        .bg-chieu { background-color: #fd7e14; }
        .bg-toi { background-color: #6610f2; }
        
        .scheduled-sang { border-left-color: #0d6efd !important; background-color: rgba(13, 110, 253, 0.03); }
        .scheduled-chieu { border-left-color: #fd7e14 !important; background-color: rgba(253, 126, 20, 0.03); }
        .scheduled-toi { border-left-color: #6610f2 !important; background-color: rgba(102, 16, 242, 0.03); }
        
        .legend-box { width: 14px; height: 14px; border-radius: 3px; display: inline-block; }
        .text-orange { color: #fd7e14; }
        .text-indigo { color: #6610f2; }

        .schedule-week-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
        }

        .schedule-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 45rem;
            padding: 0 1rem 1rem;
        }

        .schedule-board-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .schedule-board-table th,
        .schedule-board-table td {
            border: 1px solid #e2e8f0 !important;
        }

        .schedule-board-table thead th {
            position: sticky;
            top: 0;
            z-index: 6;
            background: #f8fafc;
            padding: 1rem 0.5rem !important;
        }

        .sticky-column {
            position: sticky;
            left: 0;
            z-index: 5;
            background: #f8fafc !important;
            width: 5.5rem;
            min-width: 5.5rem;
        }

        .schedule-period-cell {
            padding: 0.75rem 0.25rem !important;
        }

        .schedule-day-head.is-today {
            background: rgba(13, 110, 253, 0.05) !important;
            box-shadow: inset 0 -3px 0 #0d6efd;
        }

        .schedule-cell {
            padding: 0.5rem !important;
            vertical-align: top;
            height: 7.5rem;
            min-width: 140px;
            background: #fff;
        }

        .schedule-cell-empty {
            min-height: 100%;
            border: 1px dashed #e2e8f0;
            border-radius: 0.5rem;
            background: #fafafa;
        }

        .bg-today {
            background: rgba(13, 110, 253, 0.02) !important;
        }

        .schedule-item {
            padding: 0.6rem;
            border: 1px solid #e2e8f0;
            border-left-width: 4px !important;
            border-radius: 0.6rem;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        
        .schedule-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            z-index: 10;
            position: relative;
        }

        .schedule-item + .schedule-item {
            margin-top: 0.5rem;
        }

        .schedule-item-title {
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.3;
            color: #1e293b;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .schedule-item-meta,
        .schedule-item-submeta {
            font-size: 0.65rem;
            line-height: 1.2;
        }

        .schedule-item-meta {
            color: #64748b;
            margin-top: 0.25rem;
            font-weight: 600;
        }

        .schedule-item-submeta {
            color: #94a3b8;
            margin-top: 0.15rem;
        }

        .schedule-actions {
            margin-top: 0.6rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
        }

        .schedule-actions .btn {
            width: 1.6rem;
            height: 1.6rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.4rem;
            background: #fff;
        }

        .schedule-actions .btn i { font-size: 0.7rem; }

        .schedule-day-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem;
            background: #fff;
        }

        .schedule-mobile-item {
            border: 1px solid #e2e8f0;
            border-left-width: 4px;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #fff;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.08) !important; }
        .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }

        @media (max-width: 1199.98px) {
            .schedule-week-toolbar { justify-content: flex-start; }
        }
    </style>
@endif
