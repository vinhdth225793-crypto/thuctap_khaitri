@php
    $lich = $timelineItem['lich'];
    $teacherLiveRoom = $timelineItem['teacherLiveRoom'];
    $teacherLiveLecture = $timelineItem['teacherLiveLecture'];
    $sessionStatus = $timelineItem['sessionStatus'];
    $teachingStatus = $timelineItem['teachingStatus'];
    $isFocused = $focusedLichHocId === (int) $lich->id;
@endphp

<div id="session-{{ $lich->id }}" class="session-block mb-4 {{ $isFocused ? 'session-block-focused' : '' }}">
    <div class="session-shell shadow-sm border-0 rounded-4 overflow-hidden">
        {{-- Header: Thông tin chính --}}
        <div class="session-shell__header bg-white py-3 px-4 border-bottom">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="session-shell__number bg-primary text-white rounded-3 fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        {{ $lich->buoi_so }}
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Buổi {{ $lich->buoi_so }}: {{ $lich->ngay_hoc->format('d/m/Y') }} ({{ $lich->thu_label }})</h6>
                        <div class="smaller text-muted d-flex align-items-center gap-2 mt-1">
                            <span><i class="far fa-clock me-1 text-primary"></i>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</span>
                            <span class="text-silver">|</span>
                            <span class="badge bg-{{ $lich->hinh_thuc_color }}-soft text-{{ $lich->hinh_thuc_color }} border-0">{{ $lich->hinh_thuc_label }}</span>
                            <span class="text-silver">|</span>
                            <span class="badge bg-{{ $sessionStatus['color'] }}-soft text-{{ $sessionStatus['color'] }} border-0">{{ $sessionStatus['label'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if($isFocused)
                        <span class="badge bg-warning-soft text-warning border border-warning rounded-pill px-2 py-1 smaller">
                            <i class="fas fa-bullseye me-1"></i> Đang chọn
                        </span>
                    @endif
                    <button type="button" class="btn btn-xs btn-outline-warning btn-edit-lich border-0 shadow-none"
                            data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                            data-hinhthuc="{{ $lich->hinh_thuc }}" data-nentang="{{ $lich->nen_tang }}"
                            data-link="{{ $lich->link_online }}" data-meetingid="{{ $lich->meeting_id }}"
                            data-pass="{{ $lich->mat_khau_cuoc_hop }}" data-phong="{{ $lich->phong_hoc }}">
                        <i class="fas fa-edit me-1"></i> Sửa
                    </button>
                    <button class="btn btn-xs btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#session-detail-{{ $lich->id }}">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="session-detail-{{ $lich->id }}" class="collapse show">
            <div class="session-shell__body p-3 bg-white">
                {{-- Khu vực điều hành - Gộp Cụm 1 & 2 --}}
                <div class="row g-3 mb-3">
                    {{-- Cụm 1 & 2: Vận hành & Điều hành --}}
                    <div class="col-lg-7">
                        <div class="p-3 rounded-4 bg-light border border-light h-100">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-5 border-end border-light pe-3">
                                    <div class="fw-bold smaller text-muted text-uppercase mb-2"><i class="fas fa-play-circle me-1 text-primary"></i> Vận hành buổi dạy</div>
                                    <div class="d-flex flex-column gap-2">
                                        @if($sessionStatus['can_start'])
                                            <form action="{{ route('giang-vien.buoi-hoc.start', $lich->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary fw-bold w-100 shadow-sm py-2">
                                                    <i class="fas fa-play me-1"></i> Bắt đầu buổi học
                                                </button>
                                            </form>
                                        @elseif($sessionStatus['can_finish'])
                                            <form action="{{ route('giang-vien.buoi-hoc.finish', $lich->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success fw-bold w-100 shadow-sm py-2">
                                                    <i class="fas fa-check-double me-1"></i> Kết thúc buổi dạy
                                                </button>
                                            </form>
                                        @else
                                            <div class="p-2 bg-white rounded border text-center smaller fw-bold text-{{ $sessionStatus['color'] }}">
                                                {{ $sessionStatus['label'] }}
                                            </div>
                                        @endif
                                        <div class="smaller text-muted italic mt-1 lh-sm" style="font-size: 0.7rem;">
                                            <i class="fas fa-info-circle me-1"></i> {{ $sessionStatus['hint'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7 ps-3">
                                    <div class="fw-bold smaller text-muted text-uppercase mb-2"><i class="fas fa-broadcast-tower me-1 text-info"></i> Điều hành lớp học</div>
                                    
                                    @if($lich->hinh_thuc === 'online')
                                        <div class="d-flex flex-column gap-2">
                                            @if($lich->link_online)
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-link text-primary"></i></span>
                                                    <input type="text" class="form-control bg-white border-start-0 smaller" value="{{ $lich->link_online }}" readonly>
                                                    <a href="{{ $lich->link_online }}" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt"></i></a>
                                                </div>
                                            @endif

                                            <div class="d-flex gap-2">
                                                @if($teachingStatus['can_create_room'])
                                                    <form action="{{ route('giang-vien.live-room.schedule.create', $lich->id) }}" method="POST" class="flex-grow-1">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-info text-white fw-bold w-100 py-2 shadow-xs">
                                                            <i class="fas fa-video me-1"></i> Tạo Live
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($teachingStatus['can_enter_room'] && $teacherLiveLecture)
                                                    <a href="{{ route('giang-vien.live-room.schedule.show', $lich->id) }}" class="btn btn-sm btn-outline-info fw-bold flex-grow-1 py-2">
                                                        <i class="fas fa-door-open me-1"></i> Vào dạy
                                                    </a>
                                                @endif
                                                @if($teachingStatus['can_end_room'])
                                                    <form action="{{ route('giang-vien.live-room.schedule.end', $lich->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold py-2"><i class="fas fa-power-off"></i></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-2 bg-white rounded border border-info border-opacity-10 d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="smaller text-muted d-block">Phòng học trực tiếp:</span>
                                                <span class="fw-bold text-dark">{{ $lich->phong_hoc ?: 'Chưa gán' }}</span>
                                            </div>
                                            <i class="fas fa-map-marker-alt text-danger fa-lg me-2"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cụm 3: Điểm danh --}}
                    @include('pages.giang-vien.phan-cong.partials.teacher-attendance-card', [
                        'lich' => $lich,
                        'phanCong' => $phanCong,
                        'timelineItem' => $timelineItem,
                    ])
                </div>

                {{-- Cụm 4: Nội dung --}}
                @include('pages.giang-vien.phan-cong.partials.session-content-card', [
                    'lich' => $lich,
                    'phanCong' => $phanCong,
                    'timelineItem' => $timelineItem,
                ])
            </div>
        </div>
    </div>
</div>
