@extends('layouts.app')

@section('title', 'Quản lý điểm danh')

@section('content')
@php
    $isTeacherTab = $activeTab === 'giang-vien';

    $dashboard = $teacherWeeklyDashboard ?? [
        'selected_week' => null,
        'summary' => ['total' => 0, 'pending' => 0, 'completed' => 0, 'retention_days' => 31],
        'pending_schedules' => collect(),
        'completed_schedules' => collect(),
        'history_weeks' => collect(),
        'retention_start' => null,
    ];
    $selectedWeek = $dashboard['selected_week'] ?? null;
    $teacherQueryParams = array_filter([
        'week_start'   => $selectedWeek['start_date'] ?? null,
        'khoa_hoc_id'  => $filters['khoa_hoc_id'] ?? null,
        'giang_vien_id'=> $filters['giang_vien_id'] ?? null,
        'trang_thai'   => $filters['trang_thai'] ?? null,
    ], fn ($v) => filled($v));

    $teacherFilterActive = !empty(array_filter([
        $filters['khoa_hoc_id'] ?? null,
        $filters['giang_vien_id'] ?? null,
        $filters['trang_thai'] ?? null,
    ]));
    $studentFilterActive = !empty(array_filter([
        $filters['khoa_hoc_id'] ?? null,
        $filters['lich_hoc_id'] ?? null,
        $filters['ngay_hoc'] ?? null,
        $filters['trang_thai'] ?? null,
    ]));
@endphp

<div class="container-fluid admin-page-x dd-page">
    <div class="apx-welcome dd-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-user-check"></i></div>
        <div class="apx-welcome-text">
            <div class="dd-tag-row">
                <span class="dd-loai-badge"><i class="fas fa-clipboard-check"></i> QUẢN LÝ ĐIỂM DANH</span>
                @if($isTeacherTab)
                    <span class="dd-status-badge"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</span>
                    @if(($dashboard['summary']['pending'] ?? 0) > 0)
                        <span class="dd-pending-badge"><i class="fas fa-hourglass-half"></i> {{ $dashboard['summary']['pending'] }} cần kiểm tra</span>
                    @endif
                @else
                    <span class="dd-status-badge"><i class="fas fa-user-graduate"></i> Học viên</span>
                    @if($studentAttendances)
                        <span class="dd-status-badge"><i class="fas fa-list"></i> {{ $studentAttendances->total() }} lượt</span>
                    @endif
                @endif
            </div>
            <h4>{{ $isTeacherTab ? 'Điểm danh giảng viên' : 'Điểm danh học viên' }}</h4>
            <p>
                @if($isTeacherTab)
                    <span><i class="fas fa-calendar-week"></i> {{ $selectedWeek['label'] ?? 'Tuần hiện tại' }}</span>
                    <span class="dd-sep">·</span>
                    <span><i class="fas fa-circle-check"></i> {{ $dashboard['summary']['completed'] }} đã điểm danh</span>
                    <span class="dd-sep">·</span>
                    <span><i class="fas fa-clock"></i> Lịch sử giữ {{ $dashboard['summary']['retention_days'] }} ngày</span>
                @else
                    <span><i class="fas fa-list"></i> Tra cứu lịch sử điểm danh học viên theo khóa, buổi, ngày, trạng thái</span>
                @endif
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- Tabs chính --}}
    <div class="dd-tabs">
        <a href="{{ route('admin.diem-danh.index', ['tab' => 'giang-vien']) }}"
           class="dd-tab {{ $isTeacherTab ? 'is-active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Điểm danh giảng viên</span>
        </a>
        <a href="{{ route('admin.diem-danh.index', ['tab' => 'hoc-vien']) }}"
           class="dd-tab {{ !$isTeacherTab ? 'is-active' : '' }}">
            <i class="fas fa-user-graduate"></i>
            <span>Điểm danh học viên</span>
        </a>
    </div>

    @if($isTeacherTab)
        {{-- ========== ① Tổng quan tuần ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">1</span>
                    <div>
                        <h2><i class="fas fa-chart-pie"></i> Tổng quan tuần đang xem</h2>
                        <p>Bốn chỉ số nhanh về điểm danh giảng viên trong tuần.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill">
                        <i class="fas fa-calendar-day"></i>
                        <strong>{{ $selectedWeek['label'] ?? 'Hiện tại' }}</strong>
                    </span>
                </div>
            </header>

            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-primary">
                        <div class="aps-icon"><i class="fas fa-calendar-week"></i></div>
                        <div class="aps-text">
                            <strong>{{ ($dashboard['summary']['pending'] ?? 0) + ($dashboard['summary']['completed'] ?? 0) }}</strong>
                            <small>Tổng buổi trong tuần</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-warning">
                        <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="aps-text">
                            <strong>{{ $dashboard['summary']['pending'] }}</strong>
                            <small>Cần kiểm tra</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-success">
                        <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                        <div class="aps-text">
                            <strong>{{ $dashboard['summary']['completed'] }}</strong>
                            <small>Đã điểm danh</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-info">
                        <div class="aps-icon"><i class="fas fa-clock"></i></div>
                        <div class="aps-text">
                            <strong>{{ $dashboard['summary']['retention_days'] }}</strong>
                            <small>Ngày lưu lịch sử</small>
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
                        <h2><i class="fas fa-filter"></i> Bộ lọc</h2>
                        <p>Tìm theo khóa học, giảng viên, trạng thái buổi học.</p>
                    </div>
                </div>
                @if($teacherFilterActive)
                    <div class="apx-section-meta">
                        <span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">
                            <i class="fas fa-filter"></i> Đang lọc
                        </span>
                    </div>
                @endif
            </header>

            <div class="dd-filter-card">
                <form method="GET" action="{{ route('admin.diem-danh.index') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="giang-vien">
                    <input type="hidden" name="week_start" value="{{ $selectedWeek['start_date'] ?? '' }}">

                    <div class="col-lg-4 col-md-6">
                        <label class="dd-flabel">Khóa học</label>
                        <select name="khoa_hoc_id" class="form-select">
                            <option value="">Tất cả khóa học</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(($filters['khoa_hoc_id'] ?? null) == $course->id)>
                                    {{ $course->ma_khoa_hoc }} · {{ $course->ten_khoa_hoc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="dd-flabel">Giảng viên</label>
                        <select name="giang_vien_id" class="form-select">
                            <option value="">Tất cả giảng viên</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(($filters['giang_vien_id'] ?? null) == $teacher->id)>
                                    {{ $teacher->nguoiDung?->ho_ten ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="dd-flabel">Trạng thái buổi học</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="chua_bat_dau" @selected(($filters['trang_thai'] ?? null) === 'chua_bat_dau')>Chưa bắt đầu</option>
                            <option value="dang_day"     @selected(($filters['trang_thai'] ?? null) === 'dang_day')>Đang dạy</option>
                            <option value="da_ket_thuc"  @selected(($filters['trang_thai'] ?? null) === 'da_ket_thuc')>Đã kết thúc</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold flex-fill">
                            <i class="fas fa-search me-1"></i> Lọc
                        </button>
                        <a href="{{ route('admin.diem-danh.index', ['tab' => 'giang-vien']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </div>
        </section>

        {{-- ========== ③ Quản lý theo tuần ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">3</span>
                    <div>
                        <h2><i class="fas fa-calendar-week"></i> Quản lý theo tuần</h2>
                        <p>Chọn tuần để xem riêng buổi cần kiểm tra và buổi đã điểm danh.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill">
                        <i class="fas fa-clock"></i>
                        Lịch sử từ <strong>{{ $dashboard['retention_start'] ? \Carbon\Carbon::parse($dashboard['retention_start'])->format('d/m/Y') : '—' }}</strong>
                    </span>
                </div>
            </header>

            <div class="dd-week-grid">
                @forelse($dashboard['history_weeks'] as $week)
                    @php
                        $weekLinkFilters = array_filter([
                            'tab' => 'giang-vien',
                            'week_start' => $week['start_date'],
                            'khoa_hoc_id' => $filters['khoa_hoc_id'] ?? null,
                            'giang_vien_id' => $filters['giang_vien_id'] ?? null,
                            'trang_thai' => $filters['trang_thai'] ?? null,
                        ], fn ($v) => filled($v));
                    @endphp
                    <a href="{{ route('admin.diem-danh.index', $weekLinkFilters) }}"
                       class="dd-week-card {{ $week['is_selected'] ? 'is-active' : '' }} {{ $week['is_current'] ? 'is-current' : '' }}">
                        <div class="dd-week-head">
                            <div class="dd-week-label">{{ $week['label'] }}</div>
                            @if($week['is_current'])
                                <span class="dd-week-tag">Hiện tại</span>
                            @endif
                            @if($week['is_selected'])
                                <span class="dd-week-tag is-active"><i class="fas fa-eye"></i> Đang xem</span>
                            @endif
                        </div>
                        <div class="dd-week-stats">
                            <div class="dd-week-stat">
                                <span>Tổng</span>
                                <strong>{{ $week['total'] }}</strong>
                            </div>
                            <div class="dd-week-stat is-warning">
                                <span>Cần KT</span>
                                <strong>{{ $week['pending'] }}</strong>
                            </div>
                            <div class="dd-week-stat is-success">
                                <span>Xong</span>
                                <strong>{{ $week['completed'] }}</strong>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="dd-week-empty">
                        <i class="fas fa-calendar-xmark"></i>
                        <p>Chưa có dữ liệu tuần nào trong khoảng lưu lịch sử.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ========== ④ Cần kiểm tra ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">4</span>
                    <div>
                        <h2><i class="fas fa-triangle-exclamation"></i> Buổi cần admin kiểm tra</h2>
                        <p>Ưu tiên xử lý các buổi chưa có log hoặc điểm danh dang dở.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">
                        <strong>{{ $dashboard['summary']['pending'] }}</strong> buổi
                    </span>
                </div>
            </header>

            <div class="dd-table-card">
                @include('pages.admin.diem-danh.partials.teacher-weekly-table', [
                    'schedules' => $dashboard['pending_schedules'],
                    'emptyMessage' => 'Không có buổi nào cần admin kiểm tra trong tuần đang xem.',
                    'queryParams' => $teacherQueryParams,
                ])
            </div>
        </section>

        {{-- ========== ⑤ Đã điểm danh ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">5</span>
                    <div>
                        <h2><i class="fas fa-circle-check"></i> Buổi đã điểm danh</h2>
                        <p>Các buổi đã được điểm danh hoàn tất, dùng để đối chiếu và tra cứu.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill" style="background:#dcfce7;color:#166534;border-color:#a7f3d0;">
                        <strong>{{ $dashboard['summary']['completed'] }}</strong> buổi
                    </span>
                </div>
            </header>

            <div class="dd-table-card">
                @include('pages.admin.diem-danh.partials.teacher-weekly-table', [
                    'schedules' => $dashboard['completed_schedules'],
                    'emptyMessage' => 'Chưa có buổi nào hoàn tất điểm danh trong tuần đang xem.',
                    'queryParams' => $teacherQueryParams,
                ])
            </div>
        </section>

    @else
        {{-- ========== TAB HỌC VIÊN ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">1</span>
                    <div>
                        <h2><i class="fas fa-filter"></i> Bộ lọc điểm danh học viên</h2>
                        <p>Tra cứu theo khóa, buổi, ngày học, trạng thái.</p>
                    </div>
                </div>
                @if($studentFilterActive)
                    <div class="apx-section-meta">
                        <span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">
                            <i class="fas fa-filter"></i> Đang lọc
                        </span>
                    </div>
                @endif
            </header>

            <div class="dd-filter-card">
                <form method="GET" action="{{ route('admin.diem-danh.index') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="hoc-vien">
                    <div class="col-md-3">
                        <label class="dd-flabel">Khóa học</label>
                        <select name="khoa_hoc_id" class="form-select">
                            <option value="">Tất cả khóa học</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(($filters['khoa_hoc_id'] ?? null) == $course->id)>
                                    {{ $course->ma_khoa_hoc }} · {{ $course->ten_khoa_hoc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="dd-flabel">Buổi học</label>
                        <select name="lich_hoc_id" class="form-select">
                            <option value="">Tất cả buổi học</option>
                            @foreach($scheduleOptions as $scheduleOption)
                                <option value="{{ $scheduleOption->id }}" @selected(($filters['lich_hoc_id'] ?? null) == $scheduleOption->id)>
                                    Buổi #{{ $scheduleOption->buoi_so }} · {{ $scheduleOption->ngay_hoc?->format('d/m/Y') }} · {{ $scheduleOption->moduleHoc?->ten_module ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="dd-flabel">Ngày học</label>
                        <input type="date" name="ngay_hoc" value="{{ $filters['ngay_hoc'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="dd-flabel">Trạng thái</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="co_mat"   @selected(($filters['trang_thai'] ?? null) === 'co_mat')>Có mặt</option>
                            <option value="vang_mat" @selected(($filters['trang_thai'] ?? null) === 'vang_mat')>Vắng mặt</option>
                            <option value="vao_tre"  @selected(($filters['trang_thai'] ?? null) === 'vao_tre')>Vào trễ</option>
                            <option value="co_phep"  @selected(($filters['trang_thai'] ?? null) === 'co_phep')>Có phép</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold flex-fill">
                            <i class="fas fa-search me-1"></i> Lọc
                        </button>
                        <a href="{{ route('admin.diem-danh.index', ['tab' => 'hoc-vien']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">2</span>
                    <div>
                        <h2><i class="fas fa-list"></i> Danh sách điểm danh học viên</h2>
                        <p>Lịch sử điểm danh chi tiết theo từng học viên / buổi học.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill"><strong>{{ $studentAttendances?->total() ?? 0 }}</strong> lượt</span>
                </div>
            </header>

            <div class="dd-table-card">
                @if(!$studentAttendances || $studentAttendances->isEmpty())
                    <div class="dd-empty">
                        <div class="dd-empty-icon"><i class="fas fa-clipboard-list"></i></div>
                        <h5>Không có dữ liệu điểm danh</h5>
                        <p>Hãy thử đổi bộ lọc hoặc chờ giảng viên ghi nhận điểm danh.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 dd-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Khóa / Module</th>
                                    <th>Buổi học</th>
                                    <th>Học viên</th>
                                    <th>Giảng viên phụ trách</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="pe-4">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($studentAttendances as $attendance)
                                    @php
                                        $schedule = $attendance->lichHoc;
                                        $teacher = $schedule?->assigned_teacher;
                                        $statusMap = [
                                            'co_mat'   => ['label' => 'Có mặt',   'class' => 'is-success', 'icon' => 'fa-check-circle'],
                                            'vao_tre'  => ['label' => 'Vào trễ',  'class' => 'is-warning', 'icon' => 'fa-clock'],
                                            'vang_mat' => ['label' => 'Vắng mặt', 'class' => 'is-danger',  'icon' => 'fa-times-circle'],
                                            'co_phep'  => ['label' => 'Có phép',  'class' => 'is-info',    'icon' => 'fa-circle-info'],
                                        ];
                                        $st = $statusMap[$attendance->trang_thai] ?? ['label' => ucfirst($attendance->trang_thai), 'class' => 'is-secondary', 'icon' => 'fa-circle-question'];

                                        $idColor = ($attendance->hoc_vien_id ?? 0) % 6;
                                        $gradients = [
                                            'linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)',
                                            'linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)',
                                            'linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)',
                                        ];
                                        $hocVienTen = $attendance->hocVien?->nguoiDung?->ho_ten ?? 'N/A';
                                        $initialHV = mb_strtoupper(mb_substr(trim($hocVienTen), 0, 1, 'UTF-8'), 'UTF-8');
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="dd-course">{{ $schedule?->khoaHoc?->ten_khoa_hoc ?? 'N/A' }}</div>
                                            <div class="dd-module">
                                                <i class="fas fa-cube"></i> {{ $schedule?->moduleHoc?->ten_module ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dd-session">Buổi #{{ $schedule?->buoi_so ?? '--' }}</div>
                                            <div class="dd-session-sub">
                                                <i class="far fa-calendar-alt"></i>
                                                {{ $schedule?->ngay_hoc?->format('d/m/Y') ?? '--' }}
                                                @if($schedule?->gio_bat_dau)
                                                    · {{ \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="dd-avatar" style="background: {{ $gradients[$idColor] }};">{{ $initialHV ?: '?' }}</div>
                                                <div>
                                                    <div class="dd-name">{{ $hocVienTen }}</div>
                                                    <div class="dd-name-sub">#{{ $attendance->hoc_vien_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dd-teacher">{{ $teacher?->nguoiDung?->ho_ten ?? '—' }}</div>
                                            @if(!$teacher)
                                                <div class="dd-teacher-warn"><i class="fas fa-triangle-exclamation"></i> Chưa gán</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="dd-status-pill {{ $st['class'] }}">
                                                <i class="fas {{ $st['icon'] }}"></i> {{ $st['label'] }}
                                            </span>
                                        </td>
                                        <td class="pe-4">
                                            @if($attendance->ghi_chu)
                                                <div class="dd-note">
                                                    <i class="fas fa-comment-dots"></i> {{ $attendance->ghi_chu }}
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($studentAttendances->hasPages())
                        <div class="dd-pagination">{{ $studentAttendances->links('pagination::bootstrap-5') }}</div>
                    @endif
                @endif
            </div>
        </section>
    @endif
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .dd-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .dd-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .dd-page .apx-section-title h2 i { color: #dc2626; }
    .dd-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .dd-page .apx-meta-pill strong { color: #b91c1c; }

    .dd-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .dd-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .dd-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .dd-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .dd-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: ddPulse 1.6s ease-out infinite; }
    @keyframes ddPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.dd-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.dd-welcome p i { color: #fef3c7; margin-right: 4px; }
    .dd-sep { opacity: 0.5; }

    /* Tabs */
    .dd-tabs {
        display: flex; gap: 8px;
        padding: 6px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .dd-tab {
        flex: 1 1 240px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 18px;
        background: #fafafa;
        border: 1px solid transparent;
        color: #475569;
        font-size: 0.88rem;
        font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .dd-tab:hover { background: #fef2f2; color: #dc2626; }
    .dd-tab.is-active {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
    }
    .dd-tab i { font-size: 0.95rem; }

    /* Filter card */
    .dd-filter-card {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 14px; padding: 18px 20px;
    }
    .dd-flabel {
        display: block; font-weight: 800; color: #7f1d1d;
        font-size: 0.78rem; text-transform: uppercase;
        letter-spacing: 0.4px; margin-bottom: 6px;
    }

    /* Week grid */
    .dd-week-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }
    .dd-week-card {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        position: relative;
    }
    .dd-week-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(15,23,42,0.08);
        border-color: #fecaca;
    }
    .dd-week-card.is-active {
        border-color: #dc2626;
        box-shadow: 0 4px 12px rgba(220,38,38,0.18);
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
    }
    .dd-week-card.is-current::before {
        content: '';
        position: absolute;
        top: -1.5px; left: 12px; right: 12px;
        height: 3px;
        background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
        border-radius: 0 0 3px 3px;
    }
    .dd-week-head { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; margin-bottom: 12px; }
    .dd-week-label {
        font-size: 0.92rem; font-weight: 800; color: #0f172a;
        flex: 1; min-width: 0;
    }
    .dd-week-tag {
        padding: 2px 8px;
        background: #dcfce7; color: #166534;
        font-size: 0.66rem; font-weight: 800;
        border-radius: 999px;
    }
    .dd-week-tag.is-active { background: #dc2626; color: #fff; }
    .dd-week-tag.is-active i { font-size: 0.6rem; margin-right: 3px; }
    .dd-week-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .dd-week-stat {
        padding: 6px 8px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        text-align: center;
    }
    .dd-week-stat span {
        display: block; font-size: 0.66rem; color: #64748b;
        font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px;
    }
    .dd-week-stat strong {
        display: block;
        font-size: 1.05rem; font-weight: 900; color: #0f172a;
        line-height: 1.1; margin-top: 2px;
    }
    .dd-week-stat.is-warning strong { color: #b45309; }
    .dd-week-stat.is-success strong { color: #166534; }
    .dd-week-stat.is-warning { background: #fef3c7; border-color: #fde68a; }
    .dd-week-stat.is-success { background: #dcfce7; border-color: #a7f3d0; }
    .dd-week-empty {
        grid-column: 1 / -1;
        padding: 40px 20px;
        text-align: center;
        background: #fff;
        border: 1px dashed #fecaca;
        border-radius: 14px;
        color: #94a3b8;
    }
    .dd-week-empty i { font-size: 2rem; opacity: 0.5; display: block; margin-bottom: 8px; color: #dc2626; }
    .dd-week-empty p { font-size: 0.9rem; margin: 0; }

    /* Table card */
    .dd-table-card {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 14px; overflow: hidden;
    }
    .dd-table thead {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem; text-transform: uppercase;
        color: #7f1d1d; letter-spacing: 0.5px;
    }
    .dd-table thead th { padding: 12px 10px; font-weight: 800; border-bottom: 1px solid #fecaca; }
    .dd-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .dd-table tbody tr:last-child td { border-bottom: 0; }
    .dd-table tbody tr:hover { background: #fafafa; }

    .dd-course { font-size: 0.86rem; font-weight: 800; color: #0f172a; }
    .dd-module { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .dd-module i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .dd-session { font-size: 0.86rem; font-weight: 800; color: #0f172a; }
    .dd-session-sub { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .dd-session-sub i { color: #dc2626; margin-right: 4px; font-size: 0.66rem; }

    .dd-avatar {
        flex-shrink: 0; width: 36px; height: 36px;
        border-radius: 50%;
        color: #fff; font-weight: 800; font-size: 0.82rem;
        display: grid; place-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .dd-name { font-size: 0.86rem; font-weight: 800; color: #0f172a; }
    .dd-name-sub { font-size: 0.7rem; color: #94a3b8; font-weight: 600; font-family: monospace; }

    .dd-teacher { font-size: 0.85rem; font-weight: 700; color: #0f172a; }
    .dd-teacher-warn {
        font-size: 0.72rem;
        color: #b45309;
        font-weight: 700;
        margin-top: 3px;
    }
    .dd-teacher-warn i { margin-right: 3px; font-size: 0.66rem; }

    .dd-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .dd-status-pill i { font-size: 0.62rem; }
    .dd-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .dd-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .dd-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .dd-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .dd-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .dd-note {
        font-size: 0.78rem; color: #475569;
        max-width: 280px;
    }
    .dd-note i { color: #f59e0b; margin-right: 5px; font-size: 0.7rem; }

    .dd-pagination {
        padding: 14px 18px; background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex; justify-content: center;
    }
    .dd-pagination nav { margin: 0; }

    .dd-empty { padding: 60px 30px; text-align: center; }
    .dd-empty-icon {
        width: 88px; height: 88px; margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%; display: grid; place-items: center;
        color: #dc2626; font-size: 2rem;
    }
    .dd-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .dd-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
