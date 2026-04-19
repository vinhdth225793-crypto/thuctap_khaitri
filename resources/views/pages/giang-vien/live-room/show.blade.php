@extends('layouts.app', ['title' => 'Phòng học live'])

@section('content')
@php
    $timelineStatus = $phongHocLive->timeline_trang_thai;
    $showRoute = route('giang-vien.live-room.show', $lectureId);
    $hostViewRoute = route('giang-vien.live-room.show', ['id' => $lectureId, 'player' => 'host']);
    $startRoute = route('giang-vien.live-room.start', $lectureId);
    $leaveRoute = route('giang-vien.live-room.leave', $lectureId);
    $endRoute = route('giang-vien.live-room.end', $lectureId);
    $updateMeetLinkRoute = $updateMeetLinkRoute ?? route('giang-vien.live-room.google-meet-link.update', $lectureId);
    $linkHistories = $linkHistories ?? collect();
    $formErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $platformPayload = $phongHocLive->du_lieu_nen_tang_json ?? [];
    $scheduleOnlineUrl = \App\Support\OnlineMeetingUrl::normalize($baiGiang->lichHoc?->link_online);
    $schedulePlatform = strtolower((string) $baiGiang->lichHoc?->nen_tang);
    $roomExternalUrl = \App\Support\OnlineMeetingUrl::normalize($phongHocLive->effective_external_meeting_url ?: ($phongHocLive->start_url ?: $phongHocLive->join_url));
    $externalLaunchUrl = $roomExternalUrl ?: $scheduleOnlineUrl;
    $hasExternalLaunch = filled($externalLaunchUrl);
    $isGoogleMeetLaunch = $phongHocLive->nen_tang_live === \App\Models\PhongHocLive::PLATFORM_GOOGLE_MEET
        || str_contains(strtolower((string) $externalLaunchUrl), 'meet.google.com')
        || str_contains($schedulePlatform, 'google')
        || str_contains($schedulePlatform, 'meet');
    $meetingIdentifier = $platformPayload['meeting_id'] ?? $platformPayload['meeting_code'] ?? null;
    $meetingPasscode = $platformPayload['passcode'] ?? $baiGiang->lichHoc?->mat_khau_cuoc_hop;
    $meetingIdentifier = $meetingIdentifier ?: $baiGiang->lichHoc?->meeting_id;
    if (!$meetingIdentifier && $isGoogleMeetLaunch && $externalLaunchUrl) {
        $meetingIdentifier = \App\Support\OnlineMeetingUrl::meetingCode($externalLaunchUrl);
    }
    $platformLabel = match ($phongHocLive->nen_tang_live) {
        \App\Models\PhongHocLive::PLATFORM_GOOGLE_MEET => 'Google Meet',
        \App\Models\PhongHocLive::PLATFORM_INTERNAL => 'Live nội bộ',
        default => $phongHocLive->platform_label,
    };
    $externalPlatformLabel = $isGoogleMeetLaunch ? 'Google Meet' : ($hasExternalLaunch ? $platformLabel : 'Nền tảng live');
    $externalLaunchText = 'Mở ' . $externalPlatformLabel;
    $isInternalRoom = $phongHocLive->nen_tang_live === \App\Models\PhongHocLive::PLATFORM_INTERNAL;
    $displayPlatformLabel = $isInternalRoom && $hasExternalLaunch
        ? $platformLabel . ' + ' . $externalPlatformLabel
        : ($hasExternalLaunch ? $externalPlatformLabel : $platformLabel);
    $canTeacherStart = $canManageRoom
        && ($isInternalRoom || filled($phongHocLive->start_url) || filled($phongHocLive->join_url))
        && !in_array($timelineStatus, [\App\Models\PhongHocLive::ROOM_STATE_DANG_DIEN_RA, \App\Models\PhongHocLive::ROOM_STATE_DA_KET_THUC, \App\Models\PhongHocLive::ROOM_STATE_DA_HUY], true);
    $canTeacherReopen = $canManageRoom
        && $phongHocLive->isDangDienRa()
        && ($isInternalRoom || filled($phongHocLive->start_url) || filled($phongHocLive->join_url));
@endphp

<div class="container-fluid">
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden teacher-live-hero">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="small text-white-50 text-uppercase mb-2">Phòng điều hành giảng viên</div>
                    <h2 class="fw-bold mb-2 text-white">{{ $phongHocLive->tieu_de }}</h2>
                    <div class="text-white-50 mb-3">{{ $baiGiang->khoaHoc->ten_khoa_hoc }} / {{ $baiGiang->moduleHoc->ten_module }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-{{ $phongHocLive->timeline_trang_thai_color }}">{{ $phongHocLive->timeline_trang_thai_label }}</span>
                        <span class="badge bg-light text-dark">{{ $platformLabel }}</span>
                        @if($isInternalRoom && $hasExternalLaunch)
                            <span class="badge bg-success text-white">{{ $externalPlatformLabel }} sẵn sàng</span>
                        @endif
                        <span class="badge bg-light text-dark">{{ $phongHocLive->thoi_luong_phut }} phút</span>
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="small text-white-50">Bắt đầu lúc</div>
                    <div class="fw-bold fs-5">{{ $phongHocLive->thoi_gian_bat_dau->format('d/m/Y H:i') }}</div>
                    <div
                        id="teacher-live-countdown"
                        class="small text-white mt-1 fw-bold"
                        data-open-at="{{ $phongHocLive->join_opens_at->toIso8601String() }}"
                        data-start-at="{{ $phongHocLive->thoi_gian_bat_dau->toIso8601String() }}"
                        data-timeline="{{ $timelineStatus }}"
                        data-player-mode="{{ $playerMode }}"
                    ></div>
                    <div class="d-flex flex-wrap justify-content-md-end gap-2 mt-3">
                        @if($hasExternalLaunch)
                            <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener" class="btn btn-light btn-sm fw-bold px-3">
                                <i class="fas fa-external-link-alt me-1"></i> {{ $externalLaunchText }}
                            </a>
                        @endif
                        <a href="{{ $backUrl }}" class="btn btn-outline-light btn-sm fw-bold px-3">
                            <i class="fas fa-arrow-left me-1"></i> Về buổi học
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            @include('components.alert')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4 overflow-hidden teacher-live-overview-card">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                                <div class="flex-grow-1">
                                    <div class="teacher-live-eyebrow">Bảng điều hành</div>
                                    <h5 class="fw-bold mb-2">Tổng quan phòng học</h5>
                                    <p class="text-muted mb-0">{{ $phongHocLive->mo_ta ?: ($baiGiang->mo_ta ?: 'Chưa có mô tả chi tiết cho phòng học này.') }}</p>
                                </div>
                                <div class="teacher-live-status-pill">
                                    <span class="teacher-live-status-pill__dot"></span>
                                    {{ $phongHocLive->timeline_trang_thai_label }}
                                </div>
                            </div>

                            <div class="row g-3 teacher-live-metrics">
                                <div class="col-sm-6 col-xl-3">
                                    <div class="teacher-live-metric">
                                        <span>Nền tảng</span>
                                        <strong>{{ $displayPlatformLabel }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="teacher-live-metric">
                                        <span>Điều phối</span>
                                        <strong>{{ $phongHocLive->moderator->ho_ten ?? 'Chưa cập nhật' }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="teacher-live-metric">
                                        <span>Thời lượng</span>
                                        <strong>{{ $phongHocLive->thoi_luong_phut }} phút</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="teacher-live-metric">
                                        <span>Tham gia</span>
                                        <strong>{{ $phongHocLive->participant_count }} người</strong>
                                    </div>
                                </div>
                            </div>

                            @if($hasExternalLaunch)
                                <div class="teacher-live-meeting-strip mt-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="teacher-live-meeting-strip__icon">
                                            <i class="fas fa-video"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $externalPlatformLabel }} đã sẵn sàng</div>
                                            <div class="small text-muted">
                                                Link được lấy từ {{ $roomExternalUrl ? 'cấu hình live room' : 'lịch học' }}. Bấm nút bên cạnh để mở phòng họp ở tab mới.
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener" class="btn btn-success fw-bold px-4">
                                        <i class="fas fa-external-link-alt me-2"></i>{{ $externalLaunchText }}
                                    </a>
                                </div>
                            @elseif($baiGiang->lichHoc?->hinh_thuc === 'online')
                                <div class="teacher-live-meeting-strip teacher-live-meeting-strip--warning mt-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="teacher-live-meeting-strip__icon">
                                            <i class="fas fa-unlink"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Chưa có link Google Meet</div>
                                            <div class="small text-muted">Lịch học online này chưa có link online, nên hệ thống chưa thể hiển thị nút mở Meet.</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm overflow-hidden teacher-live-console">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <div class="teacher-live-eyebrow mb-1">Control room</div>
                                <h6 class="fw-bold mb-0">Khung live room chuyên nghiệp</h6>
                            </div>
                            @if($playerMode === 'host')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Đang điều hành</span>
                            @elseif($canTeacherStart)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Sẵn sàng bắt đầu</span>
                            @elseif($canTeacherReopen)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Có thể mở lại</span>
                            @else
                                <span class="badge bg-light text-dark border">Chưa mở</span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="teacher-live-player">
                                @if($playerMode === 'host' && $playerUrl && $playerSupportsEmbed)
                                    <iframe
                                        src="{{ $playerUrl }}"
                                        title="{{ $phongHocLive->tieu_de }}"
                                        allow="camera; microphone; fullscreen; display-capture; autoplay"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen
                                        class="teacher-live-player__frame"></iframe>
                                @elseif($playerMode === 'host' && $isInternalRoom)
                                    <div class="teacher-live-internal">
                                        <div class="teacher-live-internal__stage">
                                            <div class="teacher-live-internal__badge">LIVE NỘI BỘ</div>
                                            <h3 class="fw-bold text-white mb-3">{{ $phongHocLive->tieu_de }}</h3>
                                            <p class="text-white-50 mb-4">
                                                Bạn đang điều hành buổi học ngay trong hệ thống. Nếu buổi học có Google Meet, nút mở Meet luôn nằm ở khu vực thao tác bên phải và phía trên khung này.
                                            </p>
                                            <div class="teacher-live-internal__stats">
                                                <div class="teacher-live-internal__stat">
                                                    <span>Phòng</span>
                                                    <strong>{{ data_get($platformPayload, 'room_code', 'NOI-BO') }}</strong>
                                                </div>
                                                <div class="teacher-live-internal__stat">
                                                    <span>Người tham gia</span>
                                                    <strong>{{ $phongHocLive->participant_count }}</strong>
                                                </div>
                                                <div class="teacher-live-internal__stat">
                                                    <span>Trạng thái</span>
                                                    <strong>{{ $phongHocLive->timeline_trang_thai_label }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="teacher-live-internal__sidebar">
                                            <div class="teacher-live-panel">
                                                <div class="teacher-live-panel__title">Ghi chú nhanh</div>
                                                <ul class="teacher-live-panel__list">
                                                    <li>Xác nhận mục tiêu của buổi học và mở đầu nội dung.</li>
                                                    <li>Nhắc học viên điểm danh và đặt câu hỏi qua khu vực chat.</li>
                                                    <li>Kết thúc buổi học bằng thao tác "Kết thúc buổi học" để đồng bộ điểm danh.</li>
                                                </ul>
                                            </div>
                                            <div class="teacher-live-panel">
                                                <div class="teacher-live-panel__title">Chat / thảo luận</div>
                                                <div class="teacher-live-panel__placeholder">
                                                    Khung chat placeholder cho đồ án. Có thể mở rộng thành realtime chat trong phase sau.
                                                </div>
                                            </div>
                                            <div class="teacher-live-panel">
                                                <div class="teacher-live-panel__title">Danh sách học viên</div>
                                                <div class="teacher-live-panel__placeholder">
                                                    Hiện tại đang sử dụng room nội bộ để demo điều phối lớp học. Số người tham gia đã ghi nhận: {{ $phongHocLive->participant_count }}.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($playerMode === 'host' && $playerUrl)
                                    <div class="teacher-live-launcher text-center p-5">
                                        <div class="teacher-live-platform-orb mx-auto mb-3">
                                            <i class="fas fa-video"></i>
                                        </div>
                                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle mb-3">{{ $externalPlatformLabel }}</div>
                                        <h4 class="fw-bold mb-2">Sẵn sàng mở {{ $externalPlatformLabel }}</h4>
                                        <p class="text-muted mb-4">Nền tảng này mở ở cửa sổ mới để camera, micro và chia sẻ màn hình hoạt động ổn định hơn.</p>
                                        <a href="{{ $playerUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-lg px-5 fw-bold">
                                            <i class="fas fa-external-link-alt me-2"></i>{{ $externalLaunchText }}
                                        </a>
                                    </div>
                                @elseif($hasExternalLaunch)
                                    <div class="teacher-live-external-ready p-4 p-md-5 text-center">
                                        <div class="teacher-live-platform-orb mx-auto mb-3">
                                            <i class="fas fa-video"></i>
                                        </div>
                                        <div class="small text-uppercase fw-bold text-success mb-2">{{ $externalPlatformLabel }}</div>
                                        <h3 class="fw-bold text-white mb-3">Phòng {{ $externalPlatformLabel }} đã sẵn sàng</h3>
                                        <p class="text-white-50 mb-4 mx-auto">
                                            Để hệ thống ghi nhận đúng trạng thái lớp học, bạn nên bấm "Bắt đầu buổi học" trước, sau đó mở {{ $externalPlatformLabel }} ở tab mới.
                                        </p>

                                        <div class="teacher-live-steps mb-4">
                                            <div class="teacher-live-step">
                                                <strong>1</strong>
                                                <span>Bắt đầu buổi học để đồng bộ trạng thái và điểm danh giảng viên.</span>
                                            </div>
                                            <div class="teacher-live-step">
                                                <strong>2</strong>
                                                <span>Bấm "{{ $externalLaunchText }}" để vào phòng họp thật.</span>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            @if($canTeacherStart)
                                                <form action="{{ $startRoute }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-light btn-lg fw-bold px-4">
                                                        <i class="fas fa-play-circle me-2"></i>Bắt đầu buổi học
                                                    </button>
                                                </form>
                                            @elseif($canTeacherReopen && $playerMode !== 'host')
                                                <a href="{{ $hostViewRoute }}" class="btn btn-outline-light btn-lg fw-bold px-4">
                                                    <i class="fas fa-video me-2"></i>Mở chế độ điều hành
                                                </a>
                                            @endif
                                            <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-lg fw-bold px-4">
                                                <i class="fas fa-external-link-alt me-2"></i>{{ $externalLaunchText }}
                                            </a>
                                        </div>

                                        @if($meetingIdentifier)
                                            <div class="teacher-live-meeting-code mt-4 mx-auto">
                                                <span>Mã phòng</span>
                                                <strong>{{ $meetingIdentifier }}</strong>
                                                @if($meetingPasscode)
                                                    <span>Mật khẩu</span>
                                                    <strong>{{ $meetingPasscode }}</strong>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="teacher-live-placeholder p-5 text-center">
                                        <i class="fas fa-video-slash fa-4x text-muted opacity-25 mb-3"></i>
                                        <h5 class="fw-bold text-dark">Phòng học chưa mở</h5>
                                        <p class="text-muted mb-0">
                                            @if($canTeacherStart)
                                                Bấm "Bắt đầu buổi học" để mở phòng và ghi nhận trạng thái buổi dạy.
                                            @elseif($canTeacherReopen)
                                                Buổi học đang diễn ra. Bấm "Mở phòng điều hành" để quay lại phòng.
                                            @else
                                                Hệ thống đang chờ đến mốc mở phòng hoặc chờ người điều phối bắt đầu.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top teacher-live-action-card" style="top: 1.5rem;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <div class="teacher-live-eyebrow">Giảng viên</div>
                                    <h5 class="fw-bold mb-1">Trung tâm thao tác</h5>
                                    <p class="text-muted small mb-0">Điều hành lớp, mở Meet và quay lại tài nguyên buổi học.</p>
                                </div>
                                <span class="teacher-live-action-card__badge">{{ $phongHocLive->timeline_trang_thai_label }}</span>
                            </div>

                            @if($hasExternalLaunch)
                                <div class="teacher-live-meet-cta mb-4">
                                    <div class="small text-uppercase fw-bold text-success mb-2">{{ $externalPlatformLabel }}</div>
                                    <h6 class="fw-bold mb-2">{{ $externalLaunchText }}</h6>
                                    <p class="small text-muted mb-3">Nút này mở phòng họp thật ở tab mới. Link đang lấy từ {{ $roomExternalUrl ? 'live room' : 'lịch học' }}.</p>
                                    <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-lg w-100 fw-bold shadow-sm">
                                        <i class="fas fa-external-link-alt me-2"></i>{{ $externalLaunchText }}
                                    </a>
                                </div>
                            @endif

                            <div class="teacher-live-link-update mb-4">
                                <h6 class="fw-bold mb-2">Cap nhat link Google Meet</h6>
                                <p class="small text-muted mb-3">Dung khi link Meet cu hong hoac doi phong hop. Hoc vien va admin se thay link moi ngay sau khi luu.</p>
                                <form action="{{ $updateMeetLinkRoute }}" method="POST" class="d-grid gap-2">
                                    @csrf
                                    <input
                                        type="url"
                                        name="google_meet_url"
                                        value="{{ old('google_meet_url', $externalLaunchUrl) }}"
                                        class="form-control {{ $formErrors->has('google_meet_url') ? 'is-invalid' : '' }}"
                                        placeholder="https://meet.google.com/abc-defg-hij"
                                        required>
                                    @if($formErrors->has('google_meet_url'))
                                        <div class="invalid-feedback d-block">{{ $formErrors->first('google_meet_url') }}</div>
                                    @endif
                                    <textarea
                                        name="reason"
                                        rows="2"
                                        class="form-control {{ $formErrors->has('reason') ? 'is-invalid' : '' }}"
                                        placeholder="Ly do doi link">{{ old('reason') }}</textarea>
                                    @if($formErrors->has('reason'))
                                        <div class="invalid-feedback d-block">{{ $formErrors->first('reason') }}</div>
                                    @endif
                                    <button type="submit" class="btn btn-outline-success fw-bold">
                                        <i class="fas fa-link me-2"></i>Luu link Meet moi
                                    </button>
                                </form>

                                @if($linkHistories->isNotEmpty())
                                    <div class="mt-3 small">
                                        <div class="fw-bold text-muted mb-2">Lich su gan day</div>
                                        @foreach($linkHistories as $history)
                                            <div class="border rounded p-2 mb-2 bg-light">
                                                <div class="fw-bold text-truncate">{{ $history->new_url }}</div>
                                                <div class="text-muted">
                                                    {{ optional($history->created_at)->format('d/m/Y H:i') }}
                                                    @if($history->nguoiCapNhat)
                                                        - {{ $history->nguoiCapNhat->ho_ten }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="teacher-live-runbook mb-4">
                                <div class="teacher-live-runbook__item">
                                    <span>1</span>
                                    <div>
                                        <strong>Bắt đầu trên hệ thống</strong>
                                        <small>Ghi nhận trạng thái buổi dạy và điểm danh giảng viên.</small>
                                    </div>
                                </div>
                                <div class="teacher-live-runbook__item">
                                    <span>2</span>
                                    <div>
                                        <strong>Mở phòng họp thật</strong>
                                        <small>{{ $hasExternalLaunch ? 'Dùng nút ' . $externalLaunchText . ' để vào Google Meet/Zoom.' : 'Cần cập nhật link online cho lịch học.' }}</small>
                                    </div>
                                </div>
                                <div class="teacher-live-runbook__item">
                                    <span>3</span>
                                    <div>
                                        <strong>Kết thúc đúng luồng</strong>
                                        <small>Bấm kết thúc buổi học để đồng bộ check-out.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-3">
                                @php
                                    $lichHocId = $baiGiang->lichHoc?->id;
                                @endphp

                                @if($lichHocId)
                                    <div class="row g-2 teacher-live-quick-actions">
                                        <div class="col-4">
                                            <a href="{{ $attendanceUrl ?: $backUrl }}" class="btn btn-outline-info w-100 py-2 fw-bold">
                                                <i class="fas fa-user-check d-block mb-1"></i> Điểm danh
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <a href="{{ $resourceUrl ?: $backUrl }}" class="btn btn-outline-info w-100 py-2 fw-bold">
                                                <i class="fas fa-folder-open d-block mb-1"></i> Tài nguyên
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <a href="{{ $examUrl ?: $backUrl }}" class="btn btn-outline-danger w-100 py-2 fw-bold">
                                                <i class="fas fa-file-signature d-block mb-1"></i> Bài kiểm tra
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if($canTeacherStart)
                                    <form action="{{ $startRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                            <i class="fas fa-play-circle me-2"></i> Bắt đầu buổi học
                                        </button>
                                    </form>
                                @elseif($canTeacherReopen && $playerMode !== 'host')
                                    <a href="{{ $hostViewRoute }}" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                        <i class="fas fa-video me-2"></i> Mở phòng điều hành
                                    </a>
                                @endif

                                <a href="{{ $showRoute }}" class="btn btn-outline-secondary w-100 fw-bold">
                                    <i class="fas fa-sync-alt me-2"></i> Làm mới trang phòng
                                </a>

                                @if($playerMode === 'host')
                                    <form action="{{ $leaveRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary w-100 fw-bold">
                                            <i class="fas fa-sign-out-alt me-2"></i> Rời chế độ điều hành
                                        </button>
                                    </form>
                                @endif

                                @if($phongHocLive->isDangDienRa())
                                    <form action="{{ $endRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger w-100 fw-bold">
                                            <i class="fas fa-stop-circle me-2"></i> Kết thúc buổi học
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ $backUrl }}" class="btn btn-link text-decoration-none">
                                    Quay lại buổi học
                                </a>
                            </div>

                            @if($meetingIdentifier)
                                <div class="mt-4 p-3 bg-light rounded border teacher-live-login-info">
                                    <div class="small text-muted text-uppercase mb-2">Thông tin đăng nhập nhanh</div>
                                    <div class="d-flex justify-content-between mb-2 gap-3">
                                        <span>{{ $isGoogleMeetLaunch ? 'Mã Meet' : 'Meeting ID' }}</span>
                                        <strong class="text-end">{{ $meetingIdentifier }}</strong>
                                    </div>
                                    @if($meetingPasscode)
                                        <div class="d-flex justify-content-between gap-3">
                                            <span>Passcode</span>
                                            <strong class="text-end">{{ $meetingPasscode }}</strong>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .teacher-live-hero {
        border-radius: 1.5rem;
        background:
            radial-gradient(circle at top left, rgba(255, 255, 255, 0.2), transparent 30%),
            radial-gradient(circle at bottom right, rgba(34, 197, 94, 0.24), transparent 28%),
            linear-gradient(135deg, #0f766e, #0f172a 64%);
    }

    .teacher-live-eyebrow {
        color: #0f766e;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08rem;
        text-transform: uppercase;
    }

    .teacher-live-overview-card,
    .teacher-live-console,
    .teacher-live-action-card {
        border-radius: 1.25rem;
    }

    .teacher-live-status-pill,
    .teacher-live-action-card__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #bbf7d0;
        font-size: 0.78rem;
        font-weight: 800;
        padding: 0.45rem 0.75rem;
        white-space: nowrap;
    }

    .teacher-live-status-pill__dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 999px;
        background: #22c55e;
        box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.14);
    }

    .teacher-live-metric {
        height: 100%;
        padding: 1rem;
        border-radius: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .teacher-live-metric span {
        display: block;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.04rem;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
    }

    .teacher-live-metric strong {
        color: #0f172a;
        display: block;
        font-size: 0.98rem;
        line-height: 1.35;
    }

    .teacher-live-meeting-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-radius: 1.1rem;
        padding: 1rem;
        background: linear-gradient(135deg, #ecfdf5, #ffffff);
        border: 1px solid #bbf7d0;
    }

    .teacher-live-meeting-strip--warning {
        background: linear-gradient(135deg, #fff7ed, #ffffff);
        border-color: #fed7aa;
    }

    .teacher-live-meeting-strip__icon,
    .teacher-live-platform-orb {
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background:
            linear-gradient(135deg, #22c55e 0 25%, #fbbc04 25% 50%, #4285f4 50% 75%, #ea4335 75% 100%);
        color: #ffffff;
        box-shadow: 0 14px 28px rgba(15, 118, 110, 0.16);
        flex: 0 0 auto;
    }

    .teacher-live-player {
        background:
            radial-gradient(circle at top left, rgba(34, 197, 94, 0.16), transparent 28%),
            linear-gradient(145deg, #020617 0%, #0f172a 54%, #111827 100%);
        min-height: 520px;
    }

    .teacher-live-player__frame {
        width: 100%;
        min-height: 620px;
        border: 0;
        display: block;
    }

    .teacher-live-internal {
        min-height: 560px;
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.85fr);
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.22), transparent 32%),
            linear-gradient(145deg, #020617 0%, #0f172a 45%, #111827 100%);
    }

    .teacher-live-internal__stage {
        padding: 2rem;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .teacher-live-internal__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: rgba(248, 250, 252, 0.12);
        color: #f8fafc;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08rem;
        margin-bottom: 1rem;
    }

    .teacher-live-internal__stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .teacher-live-internal__stat {
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.55);
        border: 1px solid rgba(148, 163, 184, 0.14);
        color: #e2e8f0;
    }

    .teacher-live-internal__stat span {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 0.35rem;
    }

    .teacher-live-internal__stat strong {
        font-size: 1rem;
    }

    .teacher-live-internal__sidebar {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        background: rgba(255, 255, 255, 0.03);
    }

    .teacher-live-panel {
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.66);
        border: 1px solid rgba(148, 163, 184, 0.12);
        color: #e2e8f0;
    }

    .teacher-live-panel__title {
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .teacher-live-panel__list {
        margin: 0;
        padding-left: 1rem;
        color: #cbd5e1;
        font-size: 0.92rem;
    }

    .teacher-live-panel__placeholder {
        color: #cbd5e1;
        font-size: 0.92rem;
        line-height: 1.6;
    }

    .teacher-live-external-ready {
        min-height: 560px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .teacher-live-external-ready p {
        max-width: 680px;
    }

    .teacher-live-steps {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        width: min(680px, 100%);
    }

    .teacher-live-step {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(148, 163, 184, 0.18);
        color: #dbeafe;
        text-align: left;
    }

    .teacher-live-step strong {
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #22c55e;
        color: #ffffff;
        flex: 0 0 auto;
    }

    .teacher-live-step span {
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .teacher-live-meeting-code {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.45rem 1rem;
        max-width: 460px;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: #e2e8f0;
        text-align: left;
    }

    .teacher-live-meeting-code span {
        color: #94a3b8;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .teacher-live-launcher,
    .teacher-live-placeholder {
        min-height: 520px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .teacher-live-placeholder {
        background:
            radial-gradient(circle at top, rgba(15, 118, 110, 0.08), transparent 35%),
            #f8fafc;
    }

    .teacher-live-meet-cta {
        border-radius: 1.1rem;
        padding: 1rem;
        background:
            linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(255, 255, 255, 0.9)),
            #ffffff;
        border: 1px solid #bbf7d0;
    }

    .teacher-live-link-update {
        border-radius: 1.1rem;
        padding: 1rem;
        background: #ffffff;
        border: 1px solid #dbeafe;
    }

    .teacher-live-runbook {
        display: grid;
        gap: 0.75rem;
    }

    .teacher-live-runbook__item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem;
        border-radius: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .teacher-live-runbook__item > span {
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0f766e;
        color: #ffffff;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .teacher-live-runbook__item strong,
    .teacher-live-runbook__item small {
        display: block;
    }

    .teacher-live-runbook__item small {
        color: #64748b;
        line-height: 1.45;
        margin-top: 0.15rem;
    }

    .teacher-live-quick-actions .btn {
        font-size: 0.78rem;
        border-radius: 0.85rem;
    }

    .teacher-live-login-info {
        font-size: 0.92rem;
    }

    @media (max-width: 991.98px) {
        .teacher-live-internal {
            grid-template-columns: 1fr;
        }

        .teacher-live-internal__stage {
            border-right: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .teacher-live-internal__stats {
            grid-template-columns: 1fr;
        }

        .teacher-live-meeting-strip {
            align-items: stretch;
            flex-direction: column;
        }

        .teacher-live-meeting-strip .btn {
            width: 100%;
        }

        .teacher-live-steps {
            grid-template-columns: 1fr;
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const countdown = document.getElementById('teacher-live-countdown');

    if (!countdown) {
        return;
    }

    const openAt = new Date(countdown.dataset.openAt);
    const startAt = new Date(countdown.dataset.startAt);
    const timeline = countdown.dataset.timeline || '';
    const playerMode = countdown.dataset.playerMode || '';
    let hasReloaded = false;

    function formatDuration(totalSeconds) {
        const safeSeconds = Math.max(0, Math.floor(totalSeconds));
        const hours = Math.floor(safeSeconds / 3600);
        const minutes = Math.floor((safeSeconds % 3600) / 60);
        const seconds = safeSeconds % 60;

        if (hours > 0) {
            return `${hours}h ${minutes}p ${seconds}s`;
        }

        return `${minutes}p ${seconds}s`;
    }

    function updateCountdown() {
        const now = new Date();

        if (now < openAt) {
            countdown.textContent = `Mở phòng sau ${formatDuration((openAt - now) / 1000)}`;
        } else if (now < startAt) {
            countdown.textContent = `Đến giờ bắt đầu sau ${formatDuration((startAt - now) / 1000)}`;
        } else {
            countdown.textContent = 'Đã tới giờ học.';
        }

        if (playerMode || hasReloaded) {
            return;
        }

        const crossedOpenAt = timeline === 'chua_den_gio' && now >= openAt;
        const crossedStartAt = ['sap_bat_dau', 'cho_moderator'].includes(timeline) && now >= startAt;

        if (crossedOpenAt || crossedStartAt) {
            hasReloaded = true;
            window.location.reload();
        }
    }

    updateCountdown();
    window.setInterval(updateCountdown, 1000);
});
</script>
@endpush
@endsection
