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
    @endphp

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <div class="d-flex flex-column align-items-center mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <a href="{{ request()->fullUrlWithQuery(['week_start' => $prevWeek]) }}" class="btn btn-outline-primary border-2 rounded-circle shadow-sm p-2" title="Tuần trước" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    
                    <div class="bg-primary bg-gradient text-white px-4 py-2 rounded-pill shadow d-flex align-items-center gap-3 mx-2 border border-white border-3">
                        <div class="text-center">
                            <div class="fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px; opacity: 0.9;">Tuần {{ \Carbon\Carbon::parse($scheduleView['week_start'])->weekOfYear }}</div>
                            <div class="h5 mb-0 fw-bolder" style="letter-spacing: 0.5px;">
                                {{ \Carbon\Carbon::parse($scheduleView['week_start'])->format('d/m') }} - {{ \Carbon\Carbon::parse($scheduleView['week_end'])->format('d/m/Y') }}
                            </div>
                        </div>
                        @if($scheduleView['week_start'] === $currentWeek)
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px;" title="Tuần hiện tại">
                                <i class="fas fa-star fa-xs"></i>
                            </div>
                        @endif
                    </div>

                    <a href="{{ request()->fullUrlWithQuery(['week_start' => $nextWeek]) }}" class="btn btn-outline-primary border-2 rounded-circle shadow-sm p-2" title="Tuần sau" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                @if($scheduleView['week_start'] !== $currentWeek)
                    <a href="{{ request()->fullUrlWithQuery(['week_start' => $currentWeek]) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold">
                        <i class="fas fa-undo-alt me-1"></i> Trở về tuần hiện tại
                    </a>
                @else
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill" style="font-size: 0.7rem;">
                        <i class="fas fa-check-circle me-1"></i> ĐANG TRONG TUẦN NÀY
                    </span>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-4 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-2 small">
                    <span class="legend-dot bg-primary"></span>
                    <span class="text-muted fw-medium">Lịch dạy</span>
                </div>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="legend-dot bg-warning"></span>
                    <span class="text-muted fw-medium">Chờ duyệt nghỉ</span>
                </div>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="legend-dot bg-success"></span>
                    <span class="text-muted fw-medium">Đã duyệt nghỉ</span>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive schedule-wrapper">
                <table class="table table-bordered schedule-board-table mb-0 border-0">
                    <thead>
                        <tr class="text-center border-top-0">
                            <th class="sticky-column py-2 border-start-0 bg-light" style="width: 70px; min-width: 70px; font-size: 0.75rem;">Tiết</th>
                            @foreach($scheduleView['days'] as $day)
                                @php
                                    $isToday = $day['date'] === today()->toDateString();
                                @endphp
                                <th class="py-2 {{ $isToday ? 'bg-primary-subtle border-primary-subtle' : 'bg-light' }}" style="min-width: 130px; font-size: 0.8rem;">
                                    <div class="fw-bold {{ $isToday ? 'text-primary' : 'text-dark' }}">{{ $day['thu_label'] }}</div>
                                    <div class="small {{ $isToday ? 'text-primary' : 'text-muted' }} fw-normal" style="font-size: 0.7rem;">{{ $day['label'] }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduleView['grid'] as $row)
                            <tr>
                                <td class="sticky-column text-center py-2 bg-light border-start-0 border-bottom">
                                    <div class="fw-bold text-primary mb-0" style="font-size: 0.8rem;">{{ $row['period'] }}</div>
                                    <div class="text-muted" style="font-size: 0.65rem;">{{ $row['time'] }}</div>
                                </td>
                                @foreach($scheduleView['days'] as $day)
                                    @php
                                        $cell = $row['cells'][$day['date']] ?? ['scheduled' => [], 'leave_requests' => [], 'occupied' => false, 'has_leave_request' => false];
                                        $isToday = $day['date'] === today()->toDateString();
                                    @endphp
                                    <td class="schedule-cell p-1 {{ $isToday ? 'bg-today' : '' }} border-bottom">
                                        @foreach($cell['scheduled'] as $item)
                                            <div class="schedule-item scheduled shadow-sm mb-1 p-1 rounded-1 border-start border-2 border-primary bg-white">
                                                <div class="fw-bold text-dark text-truncate mb-0" title="{{ $item['module_name'] }}" style="font-size: 0.75rem; line-height: 1.2;">
                                                    {{ $item['module_name'] }}
                                                </div>
                                                <div class="text-muted text-truncate" style="font-size: 0.65rem;">
                                                    {{ $item['course_code'] }}
                                                </div>
                                                @if($item['leave_status_label'])
                                                    <div class="mt-1 pt-1 border-top">
                                                        <span class="badge rounded-pill bg-{{ $item['leave_status_color'] }}-subtle text-{{ $item['leave_status_color'] }} border border-{{ $item['leave_status_color'] }}-subtle w-100 py-0" style="font-size: 0.6rem;">
                                                            {{ $item['leave_status_label'] }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach

                                        @foreach($cell['leave_requests'] as $item)
                                            <div class="schedule-item leave-request shadow-sm mb-1 p-1 rounded-1 border-start border-2 border-{{ $item['status_color'] }} bg-white">
                                                <div class="fw-bold text-{{ $item['status_color'] }} mb-0" style="font-size: 0.7rem; line-height: 1.2;">
                                                    {{ $item['status_label'] }}
                                                </div>
                                                <div class="text-muted text-truncate" style="font-size: 0.65rem;">
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold">Danh sách buổi dạy tuần này</h6>
            </div>
            @if(!empty($scheduleView['scheduled_items']))
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                    {{ count($scheduleView['scheduled_items']) }} buổi
                </span>
            @endif
        </div>
        <div class="card-body p-3 pt-0">
            @if(!empty($scheduleView['scheduled_items']))
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
                            @foreach($scheduleView['scheduled_items'] as $item)
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

    @if(!empty($scheduleView['leave_request_items']))
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
                            @foreach($scheduleView['leave_request_items'] as $item)
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
        .schedule-wrapper {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
        }

        .schedule-board-table th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8f9fa;
        }

        .sticky-column {
            position: sticky;
            left: 0;
            z-index: 5;
            border-right: 1px solid #dee2e6 !important;
        }

        .schedule-cell {
            min-height: 60px;
            vertical-align: top !important;
            min-width: 130px;
            background-color: #fff;
        }

        .bg-today {
            background-color: rgba(13, 110, 253, 0.02) !important;
        }

        .schedule-item {
            transition: all 0.15s ease;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .btn-white {
            background-color: #fff;
            color: #444;
        }
        
        .btn-xs {
            padding: 0.1rem 0.3rem;
            font-size: 0.75rem;
        }

        .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.06) !important; }
        .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.06) !important; }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.06) !important; }
        
        .text-primary { color: #0d6efd !important; }
        .text-warning { color: #856404 !important; }
        .text-success { color: #155724 !important; }
    </style>
@endif
