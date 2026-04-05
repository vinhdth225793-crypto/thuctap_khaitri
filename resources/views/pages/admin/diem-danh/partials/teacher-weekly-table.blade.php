@php
    $queryParams = $queryParams ?? [];
@endphp

<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Buổi học</th>
                <th>Khóa học / Module</th>
                <th>Giảng viên</th>
                <th>Tình trạng</th>
                <th>Mốc giờ</th>
                <th class="text-end">Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
                @php
                    $attendance = $schedule->teacher_attendance_log;
                    $teacher = $schedule->assigned_teacher;
                    $attendanceLabel = $attendance?->trang_thai_label ?? 'Chưa có log';
                    $attendanceColor = $attendance?->trang_thai_color ?? 'secondary';
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold text-dark">Buổi #{{ $schedule->buoi_so }}</div>
                        <div class="small text-muted">
                            {{ $schedule->ngay_hoc?->format('d/m/Y') }} | {{ \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->gio_ket_thuc)->format('H:i') }}
                        </div>
                        <div class="small text-muted mt-1">{{ $schedule->hinh_thuc_label }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $schedule->khoaHoc?->ten_khoa_hoc ?? 'N/A' }}</div>
                        <div class="small text-muted">{{ $schedule->moduleHoc?->ten_module ?? 'Chưa có module' }}</div>
                    </td>
                    <td>
                        @if($teacher)
                            <div class="fw-semibold text-dark">{{ $teacher->nguoiDung?->ho_ten ?? 'N/A' }}</div>
                            <div class="small text-muted">Session: {{ $schedule->teaching_session_status_label }}</div>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Chưa gán giảng viên</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-2">
                            <span class="badge bg-{{ $attendanceColor }} px-3 py-2">{{ $attendanceLabel }}</span>
                            <span class="badge bg-{{ $schedule->teaching_session_status_color }}-soft text-{{ $schedule->teaching_session_status_color }} border border-{{ $schedule->teaching_session_status_color }}">
                                {{ $schedule->teaching_session_status_label }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="small">
                            <div><span class="text-muted">Vào:</span> {{ $attendance?->thoi_gian_bat_dau_day?->format('H:i d/m') ?? '--' }}</div>
                            <div><span class="text-muted">Ra:</span> {{ $attendance?->thoi_gian_ket_thuc_day?->format('H:i d/m') ?? '--' }}</div>
                            <div><span class="text-muted">Live:</span> {{ $attendance?->thoi_gian_mo_live?->format('H:i') ?? '--' }} / {{ $attendance?->thoi_gian_tat_live?->format('H:i') ?? '--' }}</div>
                        </div>
                    </td>
                    <td class="text-end">
                        @if($teacher)
                            <a href="{{ route('admin.diem-danh.giang-vien.show', array_merge([$schedule->id, $teacher->id], $queryParams)) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Xem log
                            </a>
                        @else
                            <span class="text-muted small">Chưa khả dụng</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">{{ $emptyMessage }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
