@php
    $teacherAttendance = $timelineItem['teacherAttendance'] ?? $lich->teacher_attendance_log;
    $attendanceStatus = $timelineItem['attendanceStatus'] ?? [
        'label' => $teacherAttendance?->trang_thai_label ?? 'Chưa điểm danh',
        'color' => $teacherAttendance?->trang_thai_color ?? 'secondary',
        'can_check_in' => !$teacherAttendance?->has_checked_in,
        'can_check_out' => ($teacherAttendance?->has_checked_in ?? false) && !$teacherAttendance?->has_checked_out,
    ];
    $assignedTeacher = $lich->assigned_teacher;
@endphp

<div class="col-xl-3 col-md-6">
    <div class="session-cluster-card border border-{{ $attendanceStatus['color'] }} border-opacity-25 h-100">
        <div class="session-cluster-card__header">
            <div class="session-cluster-card__eyebrow text-{{ $attendanceStatus['color'] }}">
                Điểm danh GV
            </div>
            <span class="badge bg-{{ $attendanceStatus['color'] }}-soft text-{{ $attendanceStatus['color'] }} border border-{{ $attendanceStatus['color'] }}">
                {{ $attendanceStatus['label'] }}
            </span>
        </div>

        <div class="smaller text-muted mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-user-tie opacity-50"></i>
            <span>GV: <span class="fw-bold text-dark">{{ $assignedTeacher?->nguoiDung?->ho_ten ?? auth()->user()->ho_ten }}</span></span>
        </div>

        <div class="session-metric-grid">
            <div class="session-metric">
                <div class="session-metric__label">Vào dạy</div>
                <div class="session-metric__value">
                    {{ $teacherAttendance?->check_in_at?->format('H:i') ?? '--:--' }}
                </div>
            </div>
            <div class="session-metric">
                <div class="session-metric__label">Kết thúc</div>
                <div class="session-metric__value">
                    {{ $teacherAttendance?->check_out_at?->format('H:i') ?? '--:--' }}
                </div>
            </div>
            <div class="session-metric">
                <div class="session-metric__label">Mở Live</div>
                <div class="session-metric__value">
                    @if($lich->hinh_thuc === 'online')
                        {{ $teacherAttendance?->thoi_gian_mo_live?->format('H:i') ?? '--:--' }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="session-metric">
                <div class="session-metric__label">Thời lượng</div>
                <div class="session-metric__value">
                    {{ $teacherAttendance?->tong_thoi_luong_day_phut ? $teacherAttendance->tong_thoi_luong_day_phut . 'p' : '0p' }}
                </div>
            </div>
        </div>

        @if($teacherAttendance?->ghi_chu)
            <div class="session-note mt-2 p-2 bg-white smaller border-light shadow-none">
                <i class="fas fa-comment-alt me-1 opacity-50"></i> {!! nl2br(e(Str::limit($teacherAttendance->ghi_chu, 50))) !!}
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-3">
            @if($phanCong->trang_thai !== 'da_nhan')
                <div class="alert alert-warning p-2 mb-0 smaller w-100 border-0 shadow-none">
                    <i class="fas fa-info-circle me-1"></i> Cần xác nhận nhận dạy.
                </div>
            @elseif($attendanceStatus['can_check_in'])
                <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.check-in', $lich->id) }}" method="POST" class="w-100">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary fw-bold w-100 shadow-sm py-2">
                        <i class="fas fa-sign-in-alt me-1"></i> BẮT ĐẦU DẠY
                    </button>
                </form>
            @elseif($attendanceStatus['can_check_out'])
                <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.check-out', $lich->id) }}" method="POST" class="w-100">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success fw-bold w-100 shadow-sm py-2">
                        <i class="fas fa-sign-out-alt me-1"></i> KẾT THÚC DẠY
                    </button>
                </form>
            @else
                <div class="alert alert-success p-2 mb-0 smaller w-100 border-0 shadow-none text-center fw-bold">
                    <i class="fas fa-check-circle me-1"></i> ĐÃ HOÀN TẤT
                </div>
            @endif
        </div>
    </div>
</div>
