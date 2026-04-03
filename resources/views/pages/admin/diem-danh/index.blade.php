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
            <div class="text-muted">Theo dõi riêng điểm danh giảng viên online và điểm danh học viên theo từng buổi học.</div>
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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.diem-danh.index') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="giang-vien">
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
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ngày học</label>
                        <input type="date" name="ngay_hoc" value="{{ $filters['ngay_hoc'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="chua_bat_dau" @selected(($filters['trang_thai'] ?? null) === 'chua_bat_dau')>Chưa bắt đầu</option>
                            <option value="dang_day" @selected(($filters['trang_thai'] ?? null) === 'dang_day')>Đang dạy</option>
                            <option value="da_ket_thuc" @selected(($filters['trang_thai'] ?? null) === 'da_ket_thuc')>Đã kết thúc</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                        <a href="{{ route('admin.diem-danh.index', ['tab' => 'giang-vien']) }}" class="btn btn-light border">
                            Mặc định
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Danh sách điểm danh giảng viên</h5>
                    <div class="text-muted small">Hiển thị cả các buổi online chưa được giảng viên xác nhận bắt đầu.</div>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                    {{ $teacherAttendances?->total() ?? 0 }} buổi online
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Khóa học / Module</th>
                                <th>Buổi học</th>
                                <th>Giảng viên</th>
                                <th>Bắt đầu / Kết thúc</th>
                                <th>Live</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teacherAttendances as $schedule)
                                @php
                                    $attendance = $schedule->teacher_attendance_log;
                                    $teacher = $schedule->assigned_teacher;
                                    $statusLabel = $attendance?->trang_thai_label ?? 'Chưa bắt đầu';
                                    $statusColor = $attendance?->trang_thai_color ?? 'secondary';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $schedule->khoaHoc?->ten_khoa_hoc }}</div>
                                        <div class="small text-muted">{{ $schedule->moduleHoc?->ten_module ?? 'Chưa có module' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">Buổi #{{ $schedule->buoi_so }}</div>
                                        <div class="small text-muted">
                                            {{ $schedule->ngay_hoc?->format('d/m/Y') }} | {{ \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->gio_ket_thuc)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $teacher?->nguoiDung?->ho_ten ?? 'Chưa gán giảng viên' }}</div>
                                        <div class="small text-muted">{{ strtoupper($schedule->hinh_thuc_label) }}</div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><span class="text-muted">Bắt đầu:</span> {{ $attendance?->thoi_gian_bat_dau_day?->format('H:i d/m/Y') ?? '--' }}</div>
                                            <div><span class="text-muted">Kết thúc:</span> {{ $attendance?->thoi_gian_ket_thuc_day?->format('H:i d/m/Y') ?? '--' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><span class="text-muted">Mở:</span> {{ $attendance?->thoi_gian_mo_live?->format('H:i d/m/Y') ?? '--' }}</div>
                                            <div><span class="text-muted">Tắt:</span> {{ $attendance?->thoi_gian_tat_live?->format('H:i d/m/Y') ?? '--' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }} px-3 py-2">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($teacher)
                                            <a href="{{ route('admin.diem-danh.giang-vien.show', [$schedule->id, $teacher->id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Xem
                                            </a>
                                        @else
                                            <span class="text-muted small">Chưa có giảng viên</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Không có dữ liệu điểm danh giảng viên phù hợp bộ lọc hiện tại.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($teacherAttendances)
                <div class="card-footer bg-white border-0">
                    {{ $teacherAttendances->links() }}
                </div>
            @endif
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
                    <div class="text-muted small">Tận dụng dữ liệu điểm danh học viên hiện có, không thay đổi flow cũ của giảng viên.</div>
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
