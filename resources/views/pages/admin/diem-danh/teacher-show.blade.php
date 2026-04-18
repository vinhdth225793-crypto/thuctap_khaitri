@extends('layouts.app')

@section('title', 'Chi tiết điểm danh giảng viên')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.diem-danh.index', $backLinkParams ?? ['tab' => 'giang-vien']) }}">Điểm danh</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết giảng viên</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-1">
                    <i class="fas fa-user-clock me-2 text-primary"></i>Chi tiết điểm danh giảng viên
                </h4>
                @if($schedule->teacher_monitoring_status === 'vi_pham')
                    <span class="badge bg-danger px-2"><i class="fas fa-exclamation-triangle me-1"></i>Hệ thống phát hiện vi phạm</span>
                @elseif($schedule->teacher_monitoring_status === 'nghi_van')
                    <span class="badge bg-warning text-dark px-2"><i class="fas fa-question-circle me-1"></i>Có nghi vấn</span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2"><i class="fas fa-check-circle me-1"></i>Vận hành bình thường</span>
                @endif
            </div>
            <div class="text-muted">
                {{ $teacher->nguoiDung?->ho_ten ?? 'N/A' }} | {{ $schedule->khoaHoc?->ten_khoa_hoc }} | Buổi #{{ $schedule->buoi_so }}
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($schedule->teacher_monitoring_status !== 'binh_thuong')
                <button class="btn btn-success" onclick="confirm('Xác nhận buổi học này đã được xử lý?') ? document.getElementById('form-resolve').submit() : null">
                    <i class="fas fa-check me-1"></i>Đánh dấu đã xử lý
                </button>
                <form id="form-resolve" action="{{ route('admin.diem-danh.giang-vien.resolve', $schedule->id) }}" method="POST" style="display:none;">@csrf</form>
            @endif
            <a href="{{ route('admin.diem-danh.index', $backLinkParams ?? ['tab' => 'giang-vien']) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Quay lại
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Khóa học</div>
                    <div class="fw-bold fs-5">{{ $schedule->khoaHoc?->ten_khoa_hoc ?? 'N/A' }}</div>
                    <div class="small text-muted mt-1">{{ $schedule->moduleHoc?->ten_module ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Lịch dự kiến</div>
                    <div class="fw-bold fs-5">{{ $schedule->ngay_hoc?->format('d/m/Y') }}</div>
                    <div class="small text-muted mt-1">
                        <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->gio_ket_thuc)->format('H:i') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Thời gian thực tế</div>
                    @if($schedule->actual_started_at)
                        <div class="fw-bold fs-5 text-primary">
                            {{ $schedule->actual_started_at->format('H:i') }} 
                            @if($schedule->actual_finished_at)
                                - {{ $schedule->actual_finished_at->format('H:i') }}
                            @else
                                <span class="badge bg-info ms-1" style="font-size: 0.6rem;">Đang dạy</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">
                            @if($attendance?->late_minutes > 0)
                                <span class="text-danger">Vào trễ {{ $attendance->late_minutes }}p</span>
                            @endif
                            @if($attendance?->early_leave_minutes > 0)
                                <span class="text-danger mx-1">|</span>
                                <span class="text-danger">Về sớm {{ $attendance->early_leave_minutes }}p</span>
                            @endif
                            @if(!$attendance?->late_minutes && !$attendance?->early_leave_minutes)
                                <span class="text-success">Đúng giờ</span>
                            @endif
                        </div>
                    @else
                        <div class="fw-bold fs-5 text-muted">--:--</div>
                        <div class="small text-muted mt-1 italic">Chưa ghi nhận bắt đầu</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Hồ sơ cuối buổi</div>
                    @php
                        $statusLabel = $attendance?->trang_thai_label ?? 'Chưa bắt đầu';
                        $statusColor = $attendance?->trang_thai_color ?? 'secondary';
                    @endphp
                    <div class="mt-2">
                        <span class="badge bg-{{ $statusColor }} px-3 py-2">{{ $statusLabel }}</span>
                    </div>
                    <div class="small text-muted mt-2 d-flex flex-column gap-1">
                        @if($schedule->trang_thai_bao_cao === 'da_bao_cao')
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Đã nộp báo cáo</span>
                        @elseif($schedule->trang_thai_bao_cao === 'da_bao_cao_muon')
                            <span class="text-warning"><i class="fas fa-exclamation-circle me-1"></i>Báo cáo nộp muộn</span>
                        @else
                            <span class="text-muted italic"><i class="far fa-circle me-1"></i>Chưa nộp báo cáo</span>
                        @endif
                        
                        @if($schedule->diemDanhs->isNotEmpty())
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Đã điểm danh học viên</span>
                        @else
                            <span class="text-muted italic"><i class="far fa-circle me-1"></i>Chưa điểm danh học viên</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            {{-- CẢNH BÁO HỆ THỐNG --}}
            @if($schedule->teachingSessionAlerts->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 text-danger fw-bold"><i class="fas fa-bell me-2"></i>Cảnh báo giám sát ({{ $schedule->teachingSessionAlerts->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($schedule->teachingSessionAlerts as $alert)
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-bold text-dark">
                                            <span class="badge bg-{{ $alert->severity === 'danger' ? 'danger' : 'warning' }} me-2">{{ $alert->alert_type }}</span>
                                            {{ $alert->tieu_de }}
                                        </div>
                                        <div class="small text-muted">{{ $alert->created_at->format('H:i d/m/Y') }}</div>
                                    </div>
                                    <div class="mt-1 small text-muted">{{ $alert->noi_dung }}</div>
                                    @if($alert->status === 'resolved')
                                        <div class="mt-2 text-success smaller fw-bold">
                                            <i class="fas fa-check-double me-1"></i>Đã xử lý lúc {{ $alert->resolved_at?->format('H:i d/m/Y') }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Nhật ký vận hành & Timeline</h5>
                    <span class="badge bg-light text-dark border">Timeline buổi học</span>
                </div>
                <div class="card-body">
                    @if($attendance || $schedule->actual_started_at)
                        <div class="timeline-v2">
                            @if($schedule->actual_started_at)
                                <div class="timeline-v2-item pb-3 ps-4 border-start position-relative">
                                    <div class="timeline-v2-marker bg-primary"></div>
                                    <div class="fw-bold small text-primary">BẮT ĐẦU BUỔI HỌC</div>
                                    <div class="fw-bold">{{ $schedule->actual_started_at->format('H:i:s') }}</div>
                                    <div class="small text-muted">Giảng viên thực hiện Check-in / Bắt đầu dạy.</div>
                                </div>
                            @endif

                            @php
                                $liveEvents = [];
                                foreach($schedule->baiGiangs as $lecture) {
                                    if ($lecture->phongHocLive) {
                                        if ($lecture->phongHocLive->thoi_gian_bat_dau) 
                                            $liveEvents[] = ['time' => $lecture->phongHocLive->thoi_gian_bat_dau, 'label' => 'Mở phòng Live: ' . $lecture->tieu_de, 'type' => 'live_start'];
                                        if ($lecture->phongHocLive->thoi_gian_ket_thuc) 
                                            $liveEvents[] = ['time' => $lecture->phongHocLive->thoi_gian_ket_thuc, 'label' => 'Đóng phòng Live: ' . $lecture->tieu_de, 'type' => 'live_end'];
                                    }
                                }
                                usort($liveEvents, fn($a, $b) => $a['time'] <=> $b['time']);
                            @endphp

                            @foreach($liveEvents as $event)
                                <div class="timeline-v2-item pb-3 ps-4 border-start position-relative">
                                    <div class="timeline-v2-marker bg-info"></div>
                                    <div class="fw-bold small text-info text-uppercase">{{ $event['type'] === 'live_start' ? 'Mở Live' : 'Kết thúc Live' }}</div>
                                    <div class="fw-bold">{{ $event['time']->format('H:i:s') }}</div>
                                    <div class="small text-muted">{{ $event['label'] }}</div>
                                </div>
                            @endforeach

                            @if($schedule->actual_finished_at)
                                <div class="timeline-v2-item pb-3 ps-4 border-start position-relative">
                                    <div class="timeline-v2-marker bg-success"></div>
                                    <div class="fw-bold small text-success">KẾT THÚC BUỔI HỌC</div>
                                    <div class="fw-bold">{{ $schedule->actual_finished_at->format('H:i:s') }}</div>
                                    <div class="small text-muted">Giảng viên thực hiện Check-out / Kết thúc dạy.</div>
                                </div>
                            @endif

                            @if($schedule->trang_thai_bao_cao !== 'chua_bao_cao')
                                <div class="timeline-v2-item pb-0 ps-4 position-relative">
                                    <div class="timeline-v2-marker bg-warning"></div>
                                    <div class="fw-bold small text-warning">BÁO CÁO & ĐIỂM DANH</div>
                                    <div class="fw-bold">Hoàn tất</div>
                                    <div class="small text-muted">Giảng viên đã chốt báo cáo và điểm danh học viên.</div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-top">
                            <div class="small text-muted text-uppercase mb-2">Ghi chú từ giảng viên</div>
                            <div class="border rounded-3 bg-light p-3 small text-dark">
                                {!! nl2br(e($schedule->bao_cao_giang_vien ?: 'Giảng viên chưa gửi báo cáo nội dung.')) !!}
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="small text-muted text-uppercase mb-2">Log kỹ thuật điểm danh</div>
                            <div class="border rounded-3 bg-light p-3 smaller text-muted italic">
                                {!! nl2br(e($attendance->ghi_chu ?: 'Không có log kỹ thuật.')) !!}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0 py-5 text-center">
                            <i class="fas fa-clock fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Buổi học này chưa có dữ liệu nhật ký vận hành.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ĐIỂM DANH HỌC VIÊN --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Điểm danh học viên ({{ $studentAttendances->count() }})</h5>
                    @if($studentAttendances->isNotEmpty())
                        <div class="d-flex gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Hiện diện: {{ $studentAttendances->where('trang_thai', 'co_mat')->count() }}</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Vắng: {{ $studentAttendances->where('trang_thai', 'vang_mat')->count() }}</span>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Trễ: {{ $studentAttendances->where('trang_thai', 'vao_tre')->count() }}</span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">Phép: {{ $studentAttendances->where('trang_thai', 'co_phep')->count() }}</span>
                        </div>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">#</th>
                                    <th>Học viên</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($studentAttendances as $index => $stAttendance)
                                    @php
                                        $user = $stAttendance->hocVien?->nguoiDung;
                                        $statusClass = match($stAttendance->trang_thai) {
                                            'co_mat' => 'success',
                                            'vang_mat' => 'danger',
                                            'co_phep' => 'info',
                                            'vao_tre' => 'warning',
                                            default => 'secondary'
                                        };
                                        $statusLabel = match($stAttendance->trang_thai) {
                                            'co_mat' => 'Có mặt',
                                            'vang_mat' => 'Vắng mặt',
                                            'co_phep' => 'Có phép',
                                            'vao_tre' => 'Vào trễ',
                                            default => $stAttendance->trang_thai
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ mb_strtoupper(mb_substr($user->ho_ten ?? 'H', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $user->ho_ten ?? 'N/A' }}</div>
                                                    <div class="smaller text-muted">{{ $user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }}-subtle px-3">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $stAttendance->ghi_chu ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-users-slash fa-3x mb-3 opacity-25 d-block"></i>
                                            Chưa có dữ liệu điểm danh học viên cho buổi này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Phòng học Live ({{ $liveLectures->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($liveLectures as $lecture)
                        @php $room = $lecture->phongHocLive; @endphp
                        <div class="border rounded-3 p-3 mb-3 bg-light position-relative overflow-hidden">
                            <div class="fw-bold text-dark">{{ $lecture->tieu_de }}</div>
                            <div class="small text-muted mt-1"><i class="fas fa-desktop me-1"></i>Nền tảng: <span class="fw-bold text-primary">{{ $room->platform_label }}</span></div>
                            <div class="small text-muted"><i class="fas fa-info-circle me-1"></i>Trạng thái: {{ $room->timeline_trang_thai_label }}</div>
                            <div class="small text-muted"><i class="fas fa-users me-1"></i>Tham gia: <span class="fw-bold">{{ $room->participant_count }} người</span></div>
                            @if($room->thoi_gian_bat_dau)
                                <div class="mt-2 pt-2 border-top smaller text-muted">
                                    Dạy từ: {{ $room->thoi_gian_bat_dau->format('H:i') }}
                                    @if($room->thoi_gian_ket_thuc)
                                         đến {{ $room->thoi_gian_ket_thuc->format('H:i') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-light border mb-0 small italic">
                            Buổi học này không sử dụng phòng học Live nội bộ.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 small text-uppercase fw-bold mb-3">Tóm tắt cuối buổi</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Thời lượng dự kiến:</span>
                        <span class="fw-bold">120 phút</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Thời lượng thực tế:</span>
                        <span class="fw-bold">{{ $attendance?->tong_thoi_luong_day_phut ?? 0 }} phút</span>
                    </div>
                    <hr class="border-white-50">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small">Tỷ lệ hoàn thành:</div>
                        <div class="fs-4 fw-bold">
                            @php
                                $expected = 120; // Giả định
                                $actual = $attendance?->tong_thoi_luong_day_phut ?? 0;
                                $rate = $expected > 0 ? round(($actual / $expected) * 100) : 0;
                            @endphp
                            {{ $rate }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline-v2-marker {
        position: absolute; left: -6px; top: 0;
        width: 12px; height: 12px; border-radius: 50%;
        border: 2px solid #fff; box-shadow: 0 0 0 2px currentColor;
    }
    .smaller { font-size: 0.75rem; }
    .italic { font-style: italic; }
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
</style>
@endsection
