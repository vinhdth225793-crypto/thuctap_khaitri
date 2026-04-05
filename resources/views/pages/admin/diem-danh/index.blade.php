@extends('layouts.app')

@section('title', 'Quản lý điểm danh')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Điểm danh</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-user-check me-2 text-primary"></i>Quản lý điểm danh
            </h4>
            <div class="text-muted">Tách riêng phần quản lý theo tuần để admin dễ kiểm tra các buổi còn thiếu attendance và tra cứu lịch sử gần đây.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.diem-danh.index', ['tab' => 'giang-vien']) }}" class="btn btn-outline-primary {{ $activeTab === 'giang-vien' ? 'active' : '' }}">
                Điểm danh giảng viên
            </a>
            <a href="{{ route('admin.diem-danh.index', ['tab' => 'hoc-vien']) }}" class="btn btn-outline-secondary {{ $activeTab === 'hoc-vien' ? 'active' : '' }}">
                Điểm danh học viên
            </a>
        </div>
    </div>

    @include('components.alert')

    @if($activeTab === 'giang-vien')
        @php
            $dashboard = $teacherWeeklyDashboard ?? [
                'selected_week' => null,
                'summary' => ['total' => 0, 'pending' => 0, 'completed' => 0, 'retention_days' => 31],
                'pending_schedules' => collect(),
                'completed_schedules' => collect(),
                'history_weeks' => collect(),
                'retention_start' => null,
            ];
            $selectedWeek = $dashboard['selected_week'];
            $teacherQueryParams = array_filter([
                'week_start' => $selectedWeek['start_date'] ?? null,
                'khoa_hoc_id' => $filters['khoa_hoc_id'] ?? null,
                'giang_vien_id' => $filters['giang_vien_id'] ?? null,
                'trang_thai' => $filters['trang_thai'] ?? null,
            ], fn ($value) => filled($value));
        @endphp

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.diem-danh.index') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="giang-vien">
                    <input type="hidden" name="week_start" value="{{ $selectedWeek['start_date'] ?? '' }}">

                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-semibold">Khóa học</label>
                        <select name="khoa_hoc_id" class="form-select">
                            <option value="">Tất cả khóa học</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(($filters['khoa_hoc_id'] ?? null) == $course->id)>
                                    {{ $course->ma_khoa_hoc }} - {{ $course->ten_khoa_hoc }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Giảng viên</label>
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
                        <label class="form-label fw-semibold">Trạng thái attendance</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="chua_bat_dau" @selected(($filters['trang_thai'] ?? null) === 'chua_bat_dau')>Chưa bắt đầu</option>
                            <option value="dang_day" @selected(($filters['trang_thai'] ?? null) === 'dang_day')>Đang dạy</option>
                            <option value="da_ket_thuc" @selected(($filters['trang_thai'] ?? null) === 'da_ket_thuc')>Đã kết thúc</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                        <a href="{{ route('admin.diem-danh.index', ['tab' => 'giang-vien']) }}" class="btn btn-light border">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted text-uppercase">Tuần đang xem</div>
                        <div class="fw-bold fs-5">{{ $selectedWeek['label'] ?? 'Tuần hiện tại' }}</div>
                        <div class="small text-muted mt-1">
                            {{ ($selectedWeek['is_current'] ?? false) ? 'Ưu tiên xử lý các buổi tuần hiện tại.' : 'Đây là tuần lịch sử còn trong thời hạn lưu.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted text-uppercase">Cần kiểm tra</div>
                        <div class="fw-bold fs-3 text-warning">{{ $dashboard['summary']['pending'] }}</div>
                        <div class="small text-muted mt-1">Buổi chưa có log hoặc attendance còn dang dở</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted text-uppercase">Đã điểm danh</div>
                        <div class="fw-bold fs-3 text-success">{{ $dashboard['summary']['completed'] }}</div>
                        <div class="small text-muted mt-1">Buổi đã có attendance hoàn tất trong tuần</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted text-uppercase">Lịch sử giữ lại</div>
                        <div class="fw-bold fs-3 text-primary">{{ $dashboard['summary']['retention_days'] }}</div>
                        <div class="small text-muted mt-1">Log giảng viên cũ hơn 1 tháng sẽ được dọn khỏi khu lịch sử này</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">Quản lý theo tuần</h5>
                        <div class="text-muted small">Chọn tuần để xem riêng danh sách cần kiểm tra và danh sách đã điểm danh.</div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                        Giới hạn lịch sử từ {{ $dashboard['retention_start'] ? \Carbon\Carbon::parse($dashboard['retention_start'])->format('d/m/Y') : '--' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($dashboard['history_weeks'] as $week)
                        @php
                            $weekLinkFilters = array_filter([
                                'tab' => 'giang-vien',
                                'week_start' => $week['start_date'],
                                'khoa_hoc_id' => $filters['khoa_hoc_id'] ?? null,
                                'giang_vien_id' => $filters['giang_vien_id'] ?? null,
                                'trang_thai' => $filters['trang_thai'] ?? null,
                            ], fn ($value) => filled($value));
                        @endphp
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('admin.diem-danh.index', $weekLinkFilters) }}"
                               class="card h-100 text-decoration-none shadow-sm {{ $week['is_selected'] ? 'border-primary border-2' : 'border-light' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $week['label'] }}</div>
                                            <div class="small text-muted mt-1">
                                                {{ $week['is_current'] ? 'Tuần hiện tại' : 'Lịch sử tuần' }}
                                            </div>
                                        </div>
                                        @if($week['is_selected'])
                                            <span class="badge bg-primary">Đang xem</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">Tổng buổi: <strong class="text-dark">{{ $week['total'] }}</strong></div>
                                    <div class="small text-muted">Cần kiểm tra: <strong class="text-warning">{{ $week['pending'] }}</strong></div>
                                    <div class="small text-muted">Đã điểm danh: <strong class="text-success">{{ $week['completed'] }}</strong></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1">Danh sách cần quản lý kiểm tra</h5>
                    <div class="text-muted small">Admin nên xử lý nhóm này trước để tránh sót buổi chưa xác nhận attendance.</div>
                </div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                    {{ $dashboard['summary']['pending'] }} buổi
                </span>
            </div>
            <div class="card-body">
                @include('pages.admin.diem-danh.partials.teacher-weekly-table', [
                    'schedules' => $dashboard['pending_schedules'],
                    'emptyMessage' => 'Không có buổi nào cần admin kiểm tra trong tuần đang xem.',
                    'queryParams' => $teacherQueryParams,
                ])
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1">Danh sách đã điểm danh trong tuần</h5>
                    <div class="text-muted small">Các buổi đã có attendance hoàn tất được đẩy xuống đây để tiện đối chiếu và tra cứu.</div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    {{ $dashboard['summary']['completed'] }} buổi
                </span>
            </div>
            <div class="card-body">
                @include('pages.admin.diem-danh.partials.teacher-weekly-table', [
                    'schedules' => $dashboard['completed_schedules'],
                    'emptyMessage' => 'Chưa có buổi nào hoàn tất attendance trong tuần đang xem.',
                    'queryParams' => $teacherQueryParams,
                ])
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.diem-danh.index') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="hoc-vien">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Khóa học</label>
                        <select name="khoa_hoc_id" class="form-select">
                            <option value="">Tất cả khóa học</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(($filters['khoa_hoc_id'] ?? null) == $course->id)>
                                    {{ $course->ma_khoa_hoc }} - {{ $course->ten_khoa_hoc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Buổi học</label>
                        <select name="lich_hoc_id" class="form-select">
                            <option value="">Tất cả buổi học</option>
                            @foreach($scheduleOptions as $scheduleOption)
                                <option value="{{ $scheduleOption->id }}" @selected(($filters['lich_hoc_id'] ?? null) == $scheduleOption->id)>
                                    #{{ $scheduleOption->buoi_so }} - {{ $scheduleOption->ngay_hoc?->format('d/m/Y') }} - {{ $scheduleOption->moduleHoc?->ten_module ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ngày học</label>
                        <input type="date" name="ngay_hoc" value="{{ $filters['ngay_hoc'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="co_mat" @selected(($filters['trang_thai'] ?? null) === 'co_mat')>Có mặt</option>
                            <option value="vang_mat" @selected(($filters['trang_thai'] ?? null) === 'vang_mat')>Vắng mặt</option>
                            <option value="vao_tre" @selected(($filters['trang_thai'] ?? null) === 'vao_tre')>Vào trễ</option>
                            <option value="co_phep" @selected(($filters['trang_thai'] ?? null) === 'co_phep')>Có phép</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                        <a href="{{ route('admin.diem-danh.index', ['tab' => 'hoc-vien']) }}" class="btn btn-light border">
                            Mặc định
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Danh sách điểm danh học viên</h5>
                    <div class="text-muted small">Giữ nguyên flow cũ của học viên để không làm thay đổi ngoài phạm vi quản lý theo tuần của giảng viên.</div>
                </div>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                    {{ $studentAttendances?->total() ?? 0 }} lượt điểm danh
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Khóa học / Module</th>
                                <th>Buổi học</th>
                                <th>Học viên</th>
                                <th>Giảng viên phụ trách</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentAttendances as $attendance)
                                @php
                                    $schedule = $attendance->lichHoc;
                                    $teacher = $schedule?->assigned_teacher;
                                    $studentStatusColor = match($attendance->trang_thai) {
                                        'co_mat' => 'success',
                                        'vao_tre' => 'warning',
                                        'vang_mat' => 'danger',
                                        'co_phep' => 'info',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $schedule?->khoaHoc?->ten_khoa_hoc ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $schedule?->moduleHoc?->ten_module ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">Buổi #{{ $schedule?->buoi_so ?? '--' }}</div>
                                        <div class="small text-muted">
                                            {{ $schedule?->ngay_hoc?->format('d/m/Y') ?? '--' }} | {{ $schedule?->gio_bat_dau ? \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') : '--' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $attendance->hocVien?->ho_ten ?? 'N/A' }}</div>
                                        <div class="small text-muted">#{{ $attendance->hoc_vien_id }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $teacher?->nguoiDung?->ho_ten ?? 'Chưa gán giảng viên' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $studentStatusColor }} px-3 py-2">{{ str_replace('_', ' ', ucfirst($attendance->trang_thai)) }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $attendance->ghi_chu ?: 'Không có ghi chú' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu điểm danh học viên phù hợp bộ lọc hiện tại.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($studentAttendances)
                <div class="card-footer bg-white border-0">
                    {{ $studentAttendances->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
