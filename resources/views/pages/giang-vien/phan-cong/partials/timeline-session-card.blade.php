@php
    $lich = $timelineItem['lich'];
    $teacherLiveRoom = $timelineItem['teacherLiveRoom'];
    $teacherLiveLecture = $timelineItem['teacherLiveLecture'];
    $sessionStatus = $timelineItem['sessionStatus'];
    $teachingStatus = $timelineItem['teachingStatus'];
    $isFocused = $focusedLichHocId === (int) $lich->id;
@endphp

<div id="session-{{ $lich->id }}" class="session-block mb-4 {{ $isFocused ? 'session-block-focused' : '' }}">
    <div class="session-shell shadow-sm">
        <div class="session-shell__header">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="session-shell__number">
                        {{ $lich->buoi_so }}
                    </div>
                    <div>
                        <div class="session-shell__title">
                            Buổi {{ $lich->buoi_so }} - Phiên điều hành giảng dạy
                        </div>
                        <div class="small text-muted d-flex align-items-center flex-wrap gap-2 mt-1">
                            <span><i class="far fa-calendar-alt me-1"></i>{{ $lich->ngay_hoc->format('d/m/Y') }} ({{ $lich->thu_label }})</span>
                            <span class="text-silver">|</span>
                            <span><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</span>
                            <span class="text-silver">|</span>
                            <span class="fw-semibold text-dark">{{ $phanCong->moduleHoc->ten_module }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if($isFocused)
                        <span class="badge bg-warning-soft text-warning border border-warning rounded-pill px-3 py-2">
                            <i class="fas fa-bullseye me-1"></i> Buổi đang được chọn
                        </span>
                    @endif

                    <button type="button" class="btn btn-sm btn-outline-warning btn-edit-lich"
                            data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                            data-hinhthuc="{{ $lich->hinh_thuc }}" data-nentang="{{ $lich->nen_tang }}"
                            data-link="{{ $lich->link_online }}" data-meetingid="{{ $lich->meeting_id }}"
                            data-pass="{{ $lich->mat_khau_cuoc_hop }}" data-phong="{{ $lich->phong_hoc }}">
                        <i class="fas fa-edit me-1"></i> Sửa buổi
                    </button>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top border-light">
                <span class="badge bg-{{ $lich->hinh_thuc_color }}-soft text-{{ $lich->hinh_thuc_color }} border border-{{ $lich->hinh_thuc_color }} rounded-pill">
                    <i class="fas {{ $lich->hinh_thuc === 'online' ? 'fa-video' : 'fa-door-open' }} me-1"></i>
                    {{ $lich->hinh_thuc === 'online' ? 'Buổi online' : 'Buổi trực tiếp' }}
                </span>
                <span class="badge bg-{{ $sessionStatus['color'] }}-soft text-{{ $sessionStatus['color'] }} border border-{{ $sessionStatus['color'] }} rounded-pill">
                    {{ $sessionStatus['label'] }}
                </span>
                <span class="badge bg-light text-secondary border rounded-pill">
                    {{ $lich->schedule_range_label }}
                </span>
                @if($phanCong->trang_thai !== 'da_nhan')
                    <span class="badge bg-warning-soft text-warning border border-warning rounded-pill">
                        Chờ giảng viên xác nhận nhận dạy
                    </span>
                @endif
            </div>

            <div class="session-operating-note mt-3">
                Mỗi card dưới đây là một phiên điều hành hoàn chỉnh của buổi học: thông tin buổi, điều hành lớp, điểm danh và nội dung buổi học.
            </div>
        </div>

        <div class="session-shell__body">
            <div class="row g-3">
                <div class="col-xl-4 col-md-6">
                    <div class="session-cluster-card">
                        <div class="session-cluster-card__header">
                            <div class="session-cluster-card__eyebrow text-primary">Cụm 1</div>
                            <div class="bg-primary-soft p-2 rounded-circle">
                                <i class="fas fa-info-circle text-primary"></i>
                            </div>
                        </div>

                        <div class="session-card-title">
                            <i class="fas fa-calendar-day text-primary opacity-50"></i>
                            Thông tin buổi học
                        </div>

                        <div class="session-info-list mt-3">
                            <div class="session-info-row">
                                <span>Phiên bản</span>
                                <strong>Buổi {{ $lich->buoi_so }}</strong>
                            </div>
                            <div class="session-info-row">
                                <span>Ngày học</span>
                                <strong>{{ $lich->ngay_hoc->format('d/m/Y') }}</strong>
                            </div>
                            <div class="session-info-row">
                                <span>Khung giờ</span>
                                <strong class="text-primary">{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</strong>
                            </div>
                            <div class="session-info-row">
                                <span>Hình thức</span>
                                <span class="badge bg-{{ $lich->hinh_thuc_color }}-soft text-{{ $lich->hinh_thuc_color }} fw-bold">
                                    {{ $lich->hinh_thuc_label }}
                                </span>
                            </div>
                            <div class="session-info-row">
                                <span>Trạng thái</span>
                                <span class="badge bg-{{ $sessionStatus['color'] }}-soft text-{{ $sessionStatus['color'] }} fw-bold">
                                    {{ $sessionStatus['label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="session-note mt-3 p-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <span class="fw-bold text-dark smaller">Vận hành buổi dạy</span>
                                @if($sessionStatus['can_start'])
                                    <form action="{{ route('giang-vien.buoi-hoc.start', $lich->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-primary fw-bold shadow-sm px-3">
                                            <i class="fas fa-play-circle me-1"></i> Bắt đầu buổi học
                                        </button>
                                    </form>
                                @elseif($sessionStatus['can_finish'])
                                    <form action="{{ route('giang-vien.buoi-hoc.finish', $lich->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success fw-bold shadow-sm px-3">
                                            <i class="fas fa-flag-checkered me-1"></i> Kết thúc buổi
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="smaller text-muted lh-base">
                                <i class="fas fa-lightbulb text-warning me-1"></i> {{ $sessionStatus['hint'] }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="session-cluster-card">
                        <div class="session-cluster-card__header">
                            <div class="session-cluster-card__eyebrow text-info">Cụm 2</div>
                            <div class="bg-info-soft p-2 rounded-circle">
                                <i class="fas fa-broadcast-tower text-info"></i>
                            </div>
                        </div>

                        <div class="session-card-title">
                            <i class="fas fa-tasks text-info opacity-50"></i>
                            Điều hành lớp học
                        </div>

                        @if($lich->hinh_thuc === 'online')
                            <div class="session-note mb-3 p-3">
                                <div class="fw-bold text-dark smaller mb-2 d-flex align-items-center gap-2">
                                    <img src="https://www.gstatic.com/meet/google_meet_primary_horizontal_2020q4_logo_be4af2a2e12edcabc6408e7da36d6a57.svg" height="14">
                                    Link họp hiện tại
                                </div>
                                @if($lich->link_online)
                                    <a href="{{ $lich->link_online }}" target="_blank" class="small fw-bold text-primary text-decoration-none d-block text-truncate p-2 bg-primary-soft rounded border border-primary border-opacity-10">
                                        <i class="fas fa-external-link-alt me-1"></i>{{ $lich->link_online }}
                                    </a>
                                @else
                                    <div class="small text-danger italic p-2 bg-danger-soft rounded border border-danger border-opacity-10">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Chưa có link học online.
                                    </div>
                                @endif
                            </div>

                            <div class="session-note mb-3 p-3">
                                <div class="fw-bold text-dark smaller mb-2">
                                    <i class="fas fa-video me-1 text-info"></i> Phòng live nội bộ
                                </div>
                                @if($teacherLiveRoom)
                                    <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded border border-info border-opacity-10">
                                        <code class="text-info fw-bold">{{ data_get($teacherLiveRoom->du_lieu_nen_tang_json, 'room_code', '---') }}</code>
                                        <span class="badge bg-{{ $teachingStatus['room_status_color'] }}-soft text-{{ $teachingStatus['room_status_color'] }} border-0">
                                            {{ $teachingStatus['room_status_label'] }}
                                        </span>
                                    </div>
                                @else
                                    <div class="smaller text-muted italic">Chưa khởi tạo phòng live cho buổi này.</div>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @if($teachingStatus['can_create_room'])
                                    <form action="{{ route('giang-vien.live-room.schedule.create', $lich->id) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info text-white fw-bold shadow-sm w-100 py-2">
                                            <i class="fas fa-plus-circle me-1"></i> Tạo phòng Live
                                        </button>
                                    </form>
                                @endif

                                @if($teachingStatus['can_enter_room'] && $teacherLiveLecture)
                                    <a href="{{ route('giang-vien.live-room.schedule.show', $lich->id) }}" class="btn btn-sm btn-outline-info fw-bold flex-fill py-2">
                                        <i class="fas fa-door-open me-1"></i> Vào giảng dạy
                                    </a>
                                @endif

                                @if($teachingStatus['can_end_room'])
                                    <form action="{{ route('giang-vien.live-room.schedule.end', $lich->id) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold w-100 py-2">
                                            <i class="fas fa-stop-circle me-1"></i> Kết thúc
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <div class="session-note p-3 h-100 mb-3 border-info border-opacity-25 bg-info bg-opacity-10 shadow-none">
                                <div class="fw-bold text-dark smaller mb-2">
                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> Địa điểm lớp học
                                </div>
                                <div class="fs-6 text-dark fw-bold mb-2">
                                    {{ $lich->phong_hoc ?: 'Chưa cập nhật phòng học' }}
                                </div>
                                <div class="smaller text-muted lh-base">
                                    <i class="fas fa-info-circle me-1"></i> Đây là buổi học trực tiếp tại trung tâm. Các thao tác live-stream sẽ không hiển thị.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @include('pages.giang-vien.phan-cong.partials.teacher-attendance-card', [
                    'lich' => $lich,
                    'phanCong' => $phanCong,
                    'timelineItem' => $timelineItem,
                ])

                @include('pages.giang-vien.phan-cong.partials.session-content-card', [
                    'lich' => $lich,
                    'phanCong' => $phanCong,
                    'timelineItem' => $timelineItem,
                ])
            </div>
        </div>
    </div>
</div>
