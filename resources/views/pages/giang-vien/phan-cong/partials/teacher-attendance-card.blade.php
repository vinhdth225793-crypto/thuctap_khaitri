@php
    $teacherAttendance = $lich->teacher_attendance_log;
    $assignedTeacher = $lich->assigned_teacher;
    $teacherAttendanceStatusLabel = $teacherAttendance?->trang_thai_label ?? 'Chưa bắt đầu';
    $teacherAttendanceStatusColor = $teacherAttendance?->trang_thai_color ?? 'secondary';
@endphp

@if($lich->hinh_thuc === 'online')
    <div class="col-12">
        <div class="teacher-attendance-panel p-3 rounded border border-{{ $teacherAttendanceStatusColor }} border-opacity-25 bg-{{ $teacherAttendanceStatusColor }} bg-opacity-10">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="fw-bold text-dark text-uppercase smaller">
                        <i class="fas fa-user-clock me-1 text-primary"></i> Điểm danh giảng viên
                    </div>
                    <div class="small text-muted mt-1">
                        Giảng viên phụ trách:
                        <span class="fw-bold text-dark">{{ $assignedTeacher?->nguoiDung?->ho_ten ?? auth()->user()->ho_ten }}</span>
                    </div>
                </div>
                <span class="badge bg-{{ $teacherAttendanceStatusColor }} text-white px-3 py-2">
                    {{ $teacherAttendanceStatusLabel }}
                </span>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-3 col-sm-6">
                    <div class="teacher-attendance-metric">
                        <div class="smaller text-muted text-uppercase">Bắt đầu dạy</div>
                        <div class="fw-semibold text-dark">
                            {{ $teacherAttendance?->thoi_gian_bat_dau_day?->format('H:i d/m/Y') ?? 'Chưa ghi nhận' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="teacher-attendance-metric">
                        <div class="smaller text-muted text-uppercase">Kết thúc dạy</div>
                        <div class="fw-semibold text-dark">
                            {{ $teacherAttendance?->thoi_gian_ket_thuc_day?->format('H:i d/m/Y') ?? 'Chưa ghi nhận' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="teacher-attendance-metric">
                        <div class="smaller text-muted text-uppercase">Mở / Tắt live</div>
                        <div class="fw-semibold text-dark">
                            {{ $teacherAttendance?->thoi_gian_mo_live?->format('H:i') ?? '--:--' }}
                            <span class="text-muted mx-1">/</span>
                            {{ $teacherAttendance?->thoi_gian_tat_live?->format('H:i') ?? '--:--' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="teacher-attendance-metric">
                        <div class="smaller text-muted text-uppercase">Thời lượng</div>
                        <div class="fw-semibold text-dark">
                            {{ $teacherAttendance?->tong_thoi_luong_day_phut ? $teacherAttendance->tong_thoi_luong_day_phut . ' phút' : 'Chưa tính' }}
                        </div>
                    </div>
                </div>
            </div>

            @if($teacherAttendance?->ghi_chu)
                <div class="small text-muted mt-3 teacher-attendance-note">
                    {!! nl2br(e($teacherAttendance->ghi_chu)) !!}
                </div>
            @endif

            <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
                @if($phanCong->trang_thai !== 'da_nhan')
                    <span class="small text-muted">
                        Hãy xác nhận nhận dạy trước khi thực hiện điểm danh giảng viên cho buổi online.
                    </span>
                @elseif(!$teacherAttendance || !$teacherAttendance->thoi_gian_bat_dau_day)
                    <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.start', $lich->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 shadow-xs">
                            <i class="fas fa-play-circle me-1"></i> Bắt đầu buổi học
                        </button>
                    </form>
                @elseif(!$teacherAttendance->thoi_gian_ket_thuc_day)
                    <form action="{{ route('giang-vien.buoi-hoc.teacher-attendance.finish', $lich->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success fw-bold px-3 shadow-xs">
                            <i class="fas fa-flag-checkered me-1"></i> Kết thúc buổi học
                        </button>
                    </form>
                    <span class="small text-muted">
                        Hệ thống đã ghi nhận bạn vào lớp. Khi dạy xong, bấm nút kết thúc để chốt log cho admin.
                    </span>
                @else
                    <span class="small text-success fw-semibold">
                        Buổi học này đã hoàn tất điểm danh giảng viên và sẵn sàng cho admin theo dõi.
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif
