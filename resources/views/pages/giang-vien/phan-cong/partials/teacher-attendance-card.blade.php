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
        'label' => 'Chưa chốt',
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
@endphp

<div class="col-lg-5">
    <div class="p-3 rounded-4 border border-{{ $attendanceStatus['color'] }} border-opacity-10 bg-{{ $attendanceStatus['color'] }} bg-opacity-10 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold smaller text-muted text-uppercase"><i class="fas fa-user-check me-1 text-warning"></i> Điểm danh & Hiện diện</div>
            <span class="badge bg-{{ $attendanceStatus['color'] }} text-white border-0 smaller">{{ $attendanceStatus['label'] }}</span>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-6">
                <div class="bg-white p-2 rounded border border-light text-center shadow-xs">
                    <div class="smaller text-muted" style="font-size: 0.65rem;">CHECK-IN</div>
                    <div class="fw-bold smaller text-dark">{{ $attendanceStatus['check_in_time']?->format('H:i') ?? '--:--' }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="bg-white p-2 rounded border border-light text-center shadow-xs">
                    <div class="smaller text-muted" style="font-size: 0.65rem;">CHECK-OUT</div>
                    <div class="fw-bold smaller text-dark">{{ $attendanceStatus['check_out_time']?->format('H:i') ?? '--:--' }}</div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-3">
            @if($attendanceStatus['can_check_in'])
                <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.check-in', $lich->id) }}" method="POST" class="flex-grow-1">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary fw-bold w-100 shadow-sm py-2">
                        <i class="fas fa-fingerprint me-1"></i> Check-in
                    </button>
                </form>
            @elseif($attendanceStatus['can_check_out'])
                <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.check-out', $lich->id) }}" method="POST" class="flex-grow-1">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success fw-bold w-100 shadow-sm py-2">
                        <i class="fas fa-door-open me-1"></i> Check-out
                    </button>
                </form>
            @endif
            @if($studentAttendanceStatus['is_finalized'])
                <button type="button" class="btn btn-sm btn-success fw-bold btn-diem-danh px-3 py-2 flex-grow-1 shadow-sm" data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}">
                    <i class="fas fa-check-double me-1"></i> Đã điểm danh HV
                </button>
            @else
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold btn-diem-danh px-3 py-2" data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}">
                    <i class="fas fa-users me-1"></i> Điểm danh HV
                </button>
            @endif
        </div>

        <div class="p-2 rounded bg-white bg-opacity-50 border border-white">
            <div class="d-flex align-items-center justify-content-between smaller mb-1">
                <span class="text-muted fw-bold">Học viên: {{ $studentAttendanceStatus['marked_students'] }}/{{ $studentAttendanceStatus['total_students'] }}</span>
                <span class="text-{{ $studentAttendanceStatus['color'] }} fw-bold">{{ $studentAttendanceStatus['label'] }}</span>
            </div>
            <div class="d-flex gap-3 justify-content-center">
                <span class="smaller text-success fw-bold" title="Có mặt"><i class="fas fa-check-circle me-1"></i>{{ $studentAttendanceStatus['present_count'] }}</span>
                <span class="smaller text-danger fw-bold" title="Vắng mặt"><i class="fas fa-times-circle me-1"></i>{{ $studentAttendanceStatus['absent_count'] }}</span>
                <span class="smaller text-warning fw-bold" title="Trễ/Phép"><i class="fas fa-clock me-1"></i>{{ $studentAttendanceStatus['late_count'] + $studentAttendanceStatus['excused_count'] }}</span>
            </div>
        </div>
    </div>
</div>
