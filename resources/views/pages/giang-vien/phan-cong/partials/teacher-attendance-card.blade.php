@php
    $teacherAttendance = $timelineItem['teacherAttendance'] ?? $lich->teacher_attendance_log;
    $sessionStatus = $timelineItem['sessionStatus'] ?? null;
    $attendanceStatus = $timelineItem['teacherAttendanceStatus']
        ?? $timelineItem['attendanceStatus']
        ?? [
            'value' => $teacherAttendance?->display_status ?? 'chua_diem_danh',
            'label' => $teacherAttendance?->trang_thai_label ?? 'Chưa điểm danh',
            'color' => $teacherAttendance?->trang_thai_color ?? 'secondary',
            'check_in_time' => $teacherAttendance?->check_in_at,
            'check_out_time' => $teacherAttendance?->check_out_at,
            'duration_minutes' => $teacherAttendance?->tong_thoi_luong_day_phut,
            'can_check_in' => !$teacherAttendance?->has_checked_in,
            'can_check_out' => ($teacherAttendance?->has_checked_in ?? false) && !$teacherAttendance?->has_checked_out,
            'status_hint' => $teacherAttendance?->status_hint ?? 'Giảng viên chưa thực hiện điểm danh cho buổi học này.',
            'log_hint' => null,
            'is_completed' => (bool) ($teacherAttendance?->has_checked_out ?? false),
        ];
    $studentAttendanceStatus = $timelineItem['studentAttendanceStatus'] ?? [
        'label' => 'Chưa chốt điểm danh',
        'color' => 'secondary',
        'total_students' => $phanCong->khoaHoc->hocVienKhoaHocs->count(),
        'marked_students' => $lich->diemDanhs->count(),
        'present_count' => $lich->diemDanhs->where('trang_thai', 'co_mat')->count(),
        'late_count' => $lich->diemDanhs->where('trang_thai', 'vao_tre')->count(),
        'absent_count' => $lich->diemDanhs->where('trang_thai', 'vang_mat')->count(),
        'excused_count' => $lich->diemDanhs->where('trang_thai', 'co_phep')->count(),
        'can_manage' => true,
        'is_finalized' => false,
        'status_hint' => 'Giảng viên có thể cập nhật điểm danh nhiều lần trong buổi học rồi chốt lại khi hoàn tất.',
    ];
    $assignedTeacher = $lich->assigned_teacher;
    $studentCount = $phanCong->khoaHoc->hocVienKhoaHocs->count();
    $durationLabel = filled($attendanceStatus['duration_minutes'] ?? null)
        ? $attendanceStatus['duration_minutes'] . ' phút'
        : '0 phút';
@endphp

<div class="col-xl-4 col-md-6">
    <div class="session-cluster-card border border-{{ $attendanceStatus['color'] }} border-opacity-25 h-100">
        <div class="session-cluster-card__header">
            <div class="session-cluster-card__eyebrow text-warning">Cụm 3</div>
            <div class="bg-warning-soft p-2 rounded-circle">
                <i class="fas fa-user-check text-warning"></i>
            </div>
        </div>

        <div class="session-card-title">
            <i class="fas fa-clipboard-list text-warning opacity-50"></i>
            Điểm danh & Hiện diện
        </div>

        <div class="smaller text-muted mb-3 d-flex align-items-center gap-2 bg-light p-2 rounded">
            <i class="fas fa-user-tie text-primary"></i>
            <span>Giảng viên: <span class="fw-bold text-dark">{{ $assignedTeacher?->nguoiDung?->ho_ten ?? auth()->user()->ho_ten }}</span></span>
        </div>

        <div class="session-metric-grid">
            <div class="session-metric">
                <div class="session-metric__label"><i class="fas fa-sign-in-alt me-1 text-primary"></i> Giờ vào</div>
                <div class="session-metric__value">
                    {{ $attendanceStatus['check_in_time']?->format('H:i') ?? '--:--' }}
                </div>
            </div>
            <div class="session-metric">
                <div class="session-metric__label"><i class="fas fa-sign-out-alt me-1 text-success"></i> Giờ ra</div>
                <div class="session-metric__value">
                    {{ $attendanceStatus['check_out_time']?->format('H:i') ?? '--:--' }}
                </div>
            </div>
            <div class="session-metric">
                <div class="session-metric__label"><i class="fas fa-users me-1 text-info"></i> Sĩ số</div>
                <div class="session-metric__value">{{ $studentCount }} học viên</div>
            </div>
            <div class="session-metric">
                <div class="session-metric__label"><i class="fas fa-hourglass-half me-1 text-warning"></i> Dạy</div>
                <div class="session-metric__value">{{ $durationLabel }}</div>
            </div>
        </div>

        <div class="session-note mt-3 p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <span class="fw-bold text-dark smaller">Điểm danh giảng viên</span>
                <span class="badge bg-{{ $attendanceStatus['color'] }}-soft text-{{ $attendanceStatus['color'] }} border-0 fw-bold">
                    {{ $attendanceStatus['label'] }}
                </span>
            </div>
            
            @if($attendanceStatus['can_check_in'])
                <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.check-in', $lich->id) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary fw-bold w-100 shadow-sm py-2">
                        <i class="fas fa-fingerprint me-1"></i> Bắt đầu dạy (Check-in)
                    </button>
                </form>
            @elseif($attendanceStatus['can_check_out'])
                <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.check-out', $lich->id) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success fw-bold w-100 shadow-sm py-2">
                        <i class="fas fa-door-open me-1"></i> Kết thúc dạy (Check-out)
                    </button>
                </form>
            @endif

            <div class="smaller text-muted lh-base italic">
                <i class="fas fa-info-circle me-1"></i> {{ $attendanceStatus['status_hint'] }}
            </div>
        </div>

        <div class="session-note mt-3 p-3 bg-light border-0 shadow-none">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="fw-bold text-dark smaller"><i class="fas fa-user-graduate me-1 text-success"></i> Hiện diện học viên</div>
                <span class="badge bg-{{ $studentAttendanceStatus['color'] }}-soft text-{{ $studentAttendanceStatus['color'] }} border-0 fw-bold">
                    {{ $studentAttendanceStatus['label'] }}
                </span>
            </div>

            <div class="row g-2 text-center mb-3">
                <div class="col-3">
                    <div class="smaller text-muted mb-1">Cần chấm</div>
                    <div class="fw-bold text-dark">{{ $studentAttendanceStatus['total_students'] }}</div>
                </div>
                <div class="col-3">
                    <div class="smaller text-success mb-1">Có mặt</div>
                    <div class="fw-bold text-success">{{ $studentAttendanceStatus['present_count'] }}</div>
                </div>
                <div class="col-3">
                    <div class="smaller text-danger mb-1">Vắng</div>
                    <div class="fw-bold text-danger">{{ $studentAttendanceStatus['absent_count'] }}</div>
                </div>
                <div class="col-3">
                    <div class="smaller text-warning mb-1">Trễ/Phép</div>
                    <div class="fw-bold text-warning">{{ $studentAttendanceStatus['late_count'] + $studentAttendanceStatus['excused_count'] }}</div>
                </div>
            </div>

            @if($studentAttendanceStatus['can_manage'])
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold w-100 btn-diem-danh py-2">
                    <i class="fas fa-user-check me-1"></i> Mở bảng điểm danh
                </button>
            @endif
        </div>
    </div>
</div>
