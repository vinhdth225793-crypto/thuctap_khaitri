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

    // ===== Jitsi Meet embed (nhúng vào trang) =====
    $isJitsi = $phongHocLive->nen_tang_live === \App\Models\PhongHocLive::PLATFORM_JITSI;
    $jitsiServer = config('live_room.platforms.jitsi.server', 'meet.jit.si');
    $jitsiRoomName = $isJitsi
        ? 'learntest-' . ($baiGiang->khoaHoc->ma_khoa_hoc ?? 'kh') . '-bg' . $baiGiang->id . '-pl' . $phongHocLive->id
        : null;
    $jitsiDisplayName = auth()->user()?->ho_ten ?? 'Giảng viên';
    $jitsiSubject = $phongHocLive->tieu_de;
    $jitsiPasscode = $platformPayload['passcode'] ?? null;
@endphp

<div class="container-fluid lr-page">
    {{-- Welcome banner xanh dương — Phòng điều hành GV --}}
    <div class="apx-welcome lr-welcome">
        @php
            $statusClassMap = [
                'sap_dien_ra'    => 'is-warning',
                'dang_dien_ra'   => 'is-success',
                'da_ket_thuc'    => 'is-secondary',
                'da_huy'         => 'is-danger',
                'da_hoan_thanh'  => 'is-info',
            ];
            $statusIconMap = [
                'sap_dien_ra'    => 'fa-clock',
                'dang_dien_ra'   => 'fa-circle-play',
                'da_ket_thuc'    => 'fa-flag-checkered',
                'da_huy'         => 'fa-circle-xmark',
                'da_hoan_thanh'  => 'fa-circle-check',
            ];
            $hStatusClass = $statusClassMap[$timelineStatus] ?? 'is-secondary';
            $hStatusIcon  = $statusIconMap[$timelineStatus] ?? 'fa-circle';
        @endphp
        <div class="lr-welcome-icon">
            <i class="fas fa-broadcast-tower"></i>
            @if($timelineStatus === \App\Models\PhongHocLive::ROOM_STATE_DANG_DIEN_RA)
                <span class="lr-live-dot"></span>
            @endif
        </div>
        <div class="apx-welcome-text">
            <div class="lr-tag-row">
                <span class="lr-loai-badge"><i class="fas fa-broadcast-tower"></i> PHÒNG LIVE GIẢNG VIÊN</span>
                <span class="lr-status-pill {{ $hStatusClass }}">
                    <i class="fas {{ $hStatusIcon }}"></i> {{ $phongHocLive->timeline_trang_thai_label }}
                </span>
                <span class="lr-status-badge"><i class="fas fa-cube"></i> {{ $platformLabel }}</span>
                @if($isInternalRoom && $hasExternalLaunch)
                    <span class="lr-status-pill is-success"><i class="fas fa-circle-check"></i> {{ $externalPlatformLabel }} sẵn sàng</span>
                @endif
                <span class="lr-status-badge"><i class="far fa-clock"></i> {{ $phongHocLive->thoi_luong_phut }} phút</span>
            </div>
            <h4>{{ $phongHocLive->tieu_de }}</h4>
            <p>
                <span><i class="fas fa-graduation-cap"></i> {{ $baiGiang->khoaHoc->ten_khoa_hoc }}</span>
                <span class="lr-sep">·</span>
                <span><i class="fas fa-cube"></i> {{ $baiGiang->moduleHoc->ten_module }}</span>
                <span class="lr-sep">·</span>
                <span><i class="fas fa-calendar-day"></i> Bắt đầu {{ $phongHocLive->thoi_gian_bat_dau->format('d/m/Y H:i') }}</span>
            </p>
            <div
                id="teacher-live-countdown"
                class="lr-countdown"
                data-open-at="{{ $phongHocLive->join_opens_at->toIso8601String() }}"
                data-start-at="{{ $phongHocLive->thoi_gian_bat_dau->toIso8601String() }}"
                data-timeline="{{ $timelineStatus }}"
                data-player-mode="{{ $playerMode }}"
            ></div>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ $backUrl }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về buổi học</span>
            </a>
            @if($hasExternalLaunch)
                <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener" class="btn btn-light text-primary fw-bold shadow-sm lr-launch-btn lr-launch-popup">
                    <i class="fas fa-external-link-alt me-1"></i> {{ $externalLaunchText }}
                </a>
            @endif
        </div>
    </div>

    {{-- ============ BODY: 2 cột — main player + side actions ============ --}}
    <div class="row g-4">
        <div class="col-lg-12 mb-2">
            @include('components.alert')
        </div>

        {{-- ===== MAIN COL: timer + player ===== --}}
        <div class="col-lg-8">
            {{-- Đồng hồ thực tế + control --}}
            @php
                // Ưu tiên `lich_hoc.actual_started_at` (buổi học bắt đầu) — đây là nguồn chuẩn nhất
                // Khi GV bấm "Bắt đầu buổi học" ở session card, field này được set
                // Fallback: `phong_hoc_live.bat_dau_thuc_te` (live room start riêng)
                $startedAt = $baiGiang->lichHoc?->actual_started_at ?? $phongHocLive->bat_dau_thuc_te;
                $endedAt = $baiGiang->lichHoc?->actual_finished_at ?? $phongHocLive->ket_thuc_thuc_te;
                $isRunning = $startedAt && !$endedAt;
                $isFinished = $startedAt && $endedAt;
                $totalSeconds = $isFinished ? $endedAt->diffInSeconds($startedAt) : 0;
            @endphp
            <div class="lr-timer-card {{ $isRunning ? 'is-running' : ($isFinished ? 'is-finished' : 'is-idle') }}">
                <div class="lr-timer-card__head">
                    <div class="lr-timer-card__label">
                        @if($isRunning)
                            <span class="lr-pulse"></span>
                            <span>Đang dạy</span>
                        @elseif($isFinished)
                            <i class="fas fa-flag-checkered"></i>
                            <span>Đã kết thúc</span>
                        @else
                            <i class="fas fa-pause-circle"></i>
                            <span>Chưa bắt đầu</span>
                        @endif
                    </div>
                    @if($startedAt)
                        <div class="lr-timer-card__meta">
                            <i class="fas fa-play"></i> Bắt đầu {{ $startedAt->format('H:i:s · d/m/Y') }}
                            @if($endedAt)
                                <span class="lr-sep">·</span>
                                <i class="fas fa-stop"></i> Kết thúc {{ $endedAt->format('H:i:s') }}
                            @endif
                        </div>
                    @else
                        <div class="lr-timer-card__meta">
                            <i class="far fa-calendar"></i> Dự kiến {{ $phongHocLive->thoi_gian_bat_dau->format('H:i · d/m/Y') }} ({{ $phongHocLive->thoi_luong_phut }} phút)
                        </div>
                    @endif
                </div>

                <div
                    class="lr-timer-card__display"
                    id="lr-timer-display"
                    data-started-at="{{ $startedAt?->toIso8601String() }}"
                    data-ended-at="{{ $endedAt?->toIso8601String() }}"
                    data-server-now="{{ now()->toIso8601String() }}"
                    data-total-seconds="{{ $totalSeconds }}">
                    @if($isFinished)
                        @php
                            $h = floor($totalSeconds / 3600);
                            $m = floor(($totalSeconds % 3600) / 60);
                            $s = $totalSeconds % 60;
                        @endphp
                        {{ sprintf('%02d:%02d:%02d', $h, $m, $s) }}
                    @else
                        00:00:00
                    @endif
                </div>

                <div class="lr-timer-card__actions">
                    @if($canTeacherStart)
                        <form action="{{ $startRoute }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn lr-btn-primary">
                                <i class="fas fa-play-circle"></i> Bắt đầu dạy
                            </button>
                        </form>
                    @endif

                    @if($canTeacherReopen && $playerMode !== 'host')
                        <a href="{{ $hostViewRoute }}" class="btn lr-btn-primary">
                            <i class="fas fa-video"></i> Mở phòng điều hành
                        </a>
                    @endif

                    @if($phongHocLive->isDangDienRa())
                        <form action="{{ $endRoute }}" method="POST" class="d-inline" onsubmit="return confirm('Kết thúc buổi dạy? Hệ thống sẽ check-out và đồng bộ điểm danh.')">
                            @csrf
                            <button type="submit" class="btn lr-btn-danger">
                                <i class="fas fa-stop-circle"></i> Kết thúc buổi học
                            </button>
                        </form>
                    @endif

                    @if($playerMode === 'host')
                        <form action="{{ $leaveRoute }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn lr-btn-ghost" title="Rời chế độ host (không kết thúc buổi)">
                                <i class="fas fa-sign-out-alt"></i> Rời điều hành
                            </button>
                        </form>
                    @endif

                    @if($hasExternalLaunch && !$isJitsi)
                        <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener" class="btn lr-btn-success lr-launch-popup">
                            <i class="fas fa-external-link-alt"></i> {{ $externalLaunchText }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- ===== Trạng thái điểm danh giảng viên ===== --}}
            @php
                $hasCheckedIn = $teacherAttendance && $teacherAttendance->thoi_gian_bat_dau_day;
                $hasCheckedOut = $teacherAttendance && $teacherAttendance->thoi_gian_ket_thuc_day;
                $lateMin = (int) ($teacherAttendance->late_minutes ?? 0);
                $earlyMin = (int) ($teacherAttendance->early_leave_minutes ?? 0);
                $teachMin = (int) ($teacherAttendance->tong_thoi_luong_day_phut ?? 0);
                $isFlagged = $teacherAttendance && (filled($teacherAttendance->flag_reason) || $teacherAttendance->flagged_at);

                if (!$hasCheckedIn) {
                    $attClass = 'is-pending';
                    $attIcon = 'fa-circle-exclamation';
                    $attTitle = 'Chưa check-in';
                    $attMsg = 'Bạn chưa được điểm danh cho buổi này. Bấm "Bắt đầu dạy" — hệ thống sẽ tự động check-in giảng viên.';
                } elseif (!$hasCheckedOut) {
                    $attClass = 'is-active';
                    $attIcon = 'fa-circle-check';
                    $attTitle = 'Đã check-in lúc ' . $teacherAttendance->thoi_gian_bat_dau_day->format('H:i:s · d/m/Y');
                    $attMsg = $lateMin > 0
                        ? 'Bạn đã vào trễ ' . $lateMin . ' phút so với lịch dự kiến. Khi kết thúc, hệ thống sẽ tự động check-out.'
                        : 'Hệ thống đã ghi nhận giờ vào lớp đúng giờ. Bấm "Kết thúc buổi học" khi xong để check-out.';
                } else {
                    $attClass = 'is-done';
                    $attIcon = 'fa-flag-checkered';
                    $attTitle = 'Đã hoàn tất điểm danh';
                    $attMsg = 'Check-in: ' . $teacherAttendance->thoi_gian_bat_dau_day->format('H:i') .
                              ' · Check-out: ' . $teacherAttendance->thoi_gian_ket_thuc_day->format('H:i') .
                              ' · Tổng thời lượng: ' . $teachMin . ' phút';
                }
            @endphp

            <div class="lr-attendance-card {{ $attClass }}">
                <div class="lr-attendance-card__icon">
                    <i class="fas {{ $attIcon }}"></i>
                </div>
                <div class="lr-attendance-card__body">
                    <div class="lr-attendance-card__title">{{ $attTitle }}</div>
                    <div class="lr-attendance-card__msg">{{ $attMsg }}</div>

                    @if($hasCheckedIn)
                        <div class="lr-attendance-card__stats">
                            <span class="lr-att-stat">
                                <i class="fas fa-play"></i>
                                Vào: <strong>{{ $teacherAttendance->thoi_gian_bat_dau_day->format('H:i:s') }}</strong>
                            </span>
                            @if($hasCheckedOut)
                                <span class="lr-att-stat">
                                    <i class="fas fa-stop"></i>
                                    Ra: <strong>{{ $teacherAttendance->thoi_gian_ket_thuc_day->format('H:i:s') }}</strong>
                                </span>
                                <span class="lr-att-stat">
                                    <i class="far fa-clock"></i>
                                    Thời lượng: <strong>{{ $teachMin }} phút</strong>
                                </span>
                            @endif
                            @if($lateMin > 0)
                                <span class="lr-att-stat lr-att-stat--warn">
                                    <i class="fas fa-triangle-exclamation"></i>
                                    Trễ: <strong>{{ $lateMin }} phút</strong>
                                </span>
                            @endif
                            @if($earlyMin > 0)
                                <span class="lr-att-stat lr-att-stat--warn">
                                    <i class="fas fa-arrow-right-from-bracket"></i>
                                    Về sớm: <strong>{{ $earlyMin }} phút</strong>
                                </span>
                            @endif
                            @if($isFlagged)
                                <span class="lr-att-stat lr-att-stat--danger" title="{{ $teacherAttendance->flag_reason }}">
                                    <i class="fas fa-flag"></i>
                                    Đã đánh dấu vi phạm
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
                @if($attendanceUrl ?? null)
                    <a href="{{ $attendanceUrl }}" class="lr-attendance-card__link">
                        Quản lý <i class="fas fa-arrow-right"></i>
                    </a>
                @endif
            </div>

            {{-- Player surface --}}
            <div class="lr-player-card">
                <div class="lr-player-card__head">
                    <div>
                        <div class="lr-player-card__eyebrow">{{ $platformLabel }}</div>
                        <h6 class="lr-player-card__title">{{ $isJitsi ? 'Phòng học trực tuyến' : ($isInternalRoom ? 'Khung điều hành nội bộ' : 'Trung tâm điều phối') }}</h6>
                    </div>
                    @if($playerMode === 'host')
                        <span class="lr-chip is-success">Đang điều hành</span>
                    @elseif($canTeacherStart)
                        <span class="lr-chip is-warning">Sẵn sàng</span>
                    @elseif($canTeacherReopen)
                        <span class="lr-chip is-info">Có thể mở lại</span>
                    @else
                        <span class="lr-chip is-muted">Chưa mở</span>
                    @endif
                </div>

                <div class="lr-player-card__body">
                    @if($playerMode === 'host' && $isJitsi)
                        <div id="jitsi-container" class="lr-jitsi-container"
                             data-jitsi-server="{{ $jitsiServer }}"
                             data-jitsi-room="{{ $jitsiRoomName }}"
                             data-jitsi-display="{{ $jitsiDisplayName }}"
                             data-jitsi-subject="{{ $jitsiSubject }}"
                             data-jitsi-passcode="{{ $jitsiPasscode }}"
                             data-jitsi-is-host="1"></div>
                    @elseif($playerMode === 'host' && $playerUrl && $playerSupportsEmbed)
                        <iframe
                            src="{{ $playerUrl }}"
                            title="{{ $phongHocLive->tieu_de }}"
                            allow="camera; microphone; fullscreen; display-capture; autoplay"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                            class="lr-player-iframe"></iframe>
                    @elseif($playerMode === 'host' && $isInternalRoom)
                        <div class="lr-player-internal">
                            <div class="lr-player-internal__badge">LIVE NỘI BỘ</div>
                            <h3>{{ $phongHocLive->tieu_de }}</h3>
                            <p>Bạn đang điều hành buổi học trong hệ thống. Bấm "Kết thúc buổi học" khi xong để đồng bộ check-out.</p>
                            <div class="lr-player-internal__stats">
                                <div><span>Phòng</span><strong>{{ data_get($platformPayload, 'room_code', 'NOI-BO') }}</strong></div>
                                <div><span>Người tham gia</span><strong>{{ $phongHocLive->participant_count }}</strong></div>
                                <div><span>Trạng thái</span><strong>{{ $phongHocLive->timeline_trang_thai_label }}</strong></div>
                            </div>
                        </div>
                    @elseif($hasExternalLaunch)
                        <div class="lr-player-launcher">
                            <div class="lr-player-launcher__orb"><i class="fas fa-video"></i></div>
                            <div class="lr-chip is-light mb-2">{{ $externalPlatformLabel }}</div>
                            <h4>Phòng {{ $externalPlatformLabel }} đã sẵn sàng</h4>
                            <p>{{ $externalPlatformLabel }} không nhúng được vào trang. Bấm nút bên dưới để mở phòng họp trong cửa sổ popup, vẫn ở trên trang này.</p>
                            <div class="lr-player-launcher__actions">
                                @if($canTeacherStart)
                                    <form action="{{ $startRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn lr-btn-primary"><i class="fas fa-play-circle"></i> Bắt đầu trên hệ thống</button>
                                    </form>
                                @endif
                                <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener" class="btn lr-btn-success lr-launch-popup">
                                    <i class="fas fa-external-link-alt"></i> {{ $externalLaunchText }}
                                </a>
                            </div>
                            @if($meetingIdentifier)
                                <div class="lr-meeting-info">
                                    <span>Mã phòng</span><strong>{{ $meetingIdentifier }}</strong>
                                    @if($meetingPasscode)
                                        <span>Mật khẩu</span><strong>{{ $meetingPasscode }}</strong>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="lr-player-empty">
                            <i class="fas fa-video-slash"></i>
                            <h5>Phòng học chưa mở</h5>
                            <p>
                                @if($canTeacherStart)
                                    Bấm "Bắt đầu dạy" phía trên để mở phòng và đồng bộ check-in giảng viên.
                                @elseif($canTeacherReopen)
                                    Buổi học đang diễn ra. Bấm "Mở phòng điều hành" để quay lại.
                                @else
                                    Hệ thống đang chờ đến mốc mở phòng hoặc chờ người điều phối.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== SIDE COL: thông tin + cập nhật link + lịch sử ===== --}}
        <div class="col-lg-4">
            {{-- Quick stats --}}
            <div class="lr-side-card">
                <div class="lr-side-card__head">
                    <i class="fas fa-circle-info"></i>
                    <span>Thông tin nhanh</span>
                </div>
                <div class="lr-side-card__body">
                    <div class="lr-stat-row">
                        <span>Nền tảng</span><strong>{{ $displayPlatformLabel }}</strong>
                    </div>
                    <div class="lr-stat-row">
                        <span>Điều phối</span><strong>{{ $phongHocLive->moderator->ho_ten ?? 'Chưa cập nhật' }}</strong>
                    </div>
                    <div class="lr-stat-row">
                        <span>Thời lượng dự kiến</span><strong>{{ $phongHocLive->thoi_luong_phut }} phút</strong>
                    </div>
                    <div class="lr-stat-row">
                        <span>Tham gia</span><strong>{{ $phongHocLive->participant_count }} người</strong>
                    </div>
                    @if($meetingIdentifier)
                        <hr class="my-2">
                        <div class="lr-stat-row">
                            <span>{{ $isGoogleMeetLaunch ? 'Mã Meet' : 'Meeting ID' }}</span>
                            <strong>{{ $meetingIdentifier }}</strong>
                        </div>
                        @if($meetingPasscode)
                            <div class="lr-stat-row">
                                <span>Passcode</span><strong>{{ $meetingPasscode }}</strong>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Quick actions --}}
            @if($baiGiang->lichHoc?->id)
                <div class="lr-side-card">
                    <div class="lr-side-card__head">
                        <i class="fas fa-bolt"></i>
                        <span>Lối tắt</span>
                    </div>
                    <div class="lr-side-card__body lr-quick-actions">
                        @if(!empty($attendanceUrl))
                            <a href="{{ $attendanceUrl }}" class="lr-quick-btn">
                                <i class="fas fa-user-check"></i><span>Điểm danh</span>
                            </a>
                        @endif
                        @if(!empty($resourceUrl))
                            <a href="{{ $resourceUrl }}" class="lr-quick-btn">
                                <i class="fas fa-folder-open"></i><span>Tài nguyên</span>
                            </a>
                        @endif
                        @if(!empty($examUrl))
                            <a href="{{ $examUrl }}" class="lr-quick-btn">
                                <i class="fas fa-file-signature"></i><span>Kiểm tra</span>
                            </a>
                        @endif
                        <a href="{{ $showRoute }}" class="lr-quick-btn">
                            <i class="fas fa-rotate-right"></i><span>Làm mới</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Update Meet link (collapsible) --}}
            <div class="lr-side-card">
                <button class="lr-side-card__head lr-side-card__head--toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#lr-update-link"
                        aria-expanded="{{ $formErrors->has('google_meet_url') || $formErrors->has('reason') ? 'true' : 'false' }}">
                    <i class="fas fa-link"></i>
                    <span>Cập nhật link Google Meet</span>
                    <i class="fas fa-chevron-down lr-side-card__chevron ms-auto"></i>
                </button>
                <div id="lr-update-link" class="collapse {{ $formErrors->has('google_meet_url') || $formErrors->has('reason') ? 'show' : '' }}">
                    <div class="lr-side-card__body">
                        <p class="small text-muted mb-2">Dùng khi link Meet hỏng hoặc đổi phòng. Học viên sẽ thấy link mới ngay.</p>
                        <form action="{{ $updateMeetLinkRoute }}" method="POST" class="d-grid gap-2">
                            @csrf
                            <input
                                type="url"
                                name="google_meet_url"
                                value="{{ old('google_meet_url', $externalLaunchUrl) }}"
                                class="form-control form-control-sm {{ $formErrors->has('google_meet_url') ? 'is-invalid' : '' }}"
                                placeholder="https://meet.google.com/abc-defg-hij"
                                required>
                            @if($formErrors->has('google_meet_url'))
                                <div class="invalid-feedback d-block">{{ $formErrors->first('google_meet_url') }}</div>
                            @endif
                            <textarea
                                name="reason"
                                rows="2"
                                class="form-control form-control-sm {{ $formErrors->has('reason') ? 'is-invalid' : '' }}"
                                placeholder="Lý do đổi link">{{ old('reason') }}</textarea>
                            @if($formErrors->has('reason'))
                                <div class="invalid-feedback d-block">{{ $formErrors->first('reason') }}</div>
                            @endif
                            <button type="submit" class="btn lr-btn-success-outline btn-sm">
                                <i class="fas fa-save"></i> Lưu link mới
                            </button>
                        </form>

                        @if($linkHistories->isNotEmpty())
                            <div class="lr-link-history mt-3">
                                <div class="lr-link-history__title">Lịch sử đổi link</div>
                                @foreach($linkHistories as $history)
                                    <div class="lr-link-history__item">
                                        <div class="lr-link-history__url">{{ $history->new_url }}</div>
                                        <div class="lr-link-history__meta">
                                            {{ optional($history->created_at)->format('d/m/Y H:i') }}
                                            @if($history->nguoiCapNhat)
                                                · {{ $history->nguoiCapNhat->ho_ten }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================================
       LIVE ROOM — clean redesign (timer + player + side info)
       ============================================================ */
    .lr-page { padding-bottom: 24px; }

    /* ===== Timer card ===== */
    .lr-timer-card {
        background: #fff;
        border-radius: 18px;
        padding: 20px 22px;
        margin-bottom: 18px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    .lr-timer-card.is-running {
        border-color: #fca5a5;
        background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
        box-shadow: 0 12px 28px rgba(220, 38, 38, 0.18);
    }
    .lr-timer-card.is-finished {
        border-color: #6ee7b7;
        background: linear-gradient(135deg, #fff 0%, #ecfdf5 100%);
    }
    .lr-timer-card.is-idle {
        background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    }

    .lr-timer-card__head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .lr-timer-card__label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .lr-timer-card.is-running .lr-timer-card__label { color: #b91c1c; }
    .lr-timer-card.is-finished .lr-timer-card__label { color: #047857; }
    .lr-timer-card.is-idle .lr-timer-card__label { color: #64748b; }

    .lr-pulse {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #ef4444;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6);
        animation: lrPulse 1.4s ease-out infinite;
    }
    @keyframes lrPulse {
        0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
        70%  { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .lr-timer-card__meta {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .lr-timer-card__meta i { color: #94a3b8; margin: 0 4px 0 0; }
    .lr-timer-card__meta .lr-sep { color: #cbd5e1; margin: 0 6px; }

    .lr-timer-card__display {
        font-family: 'Courier New', 'Consolas', monospace;
        font-size: 3.2rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: 0.04em;
        line-height: 1;
        margin: 14px 0 16px;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .lr-timer-card.is-running .lr-timer-card__display { color: #b91c1c; }
    .lr-timer-card.is-finished .lr-timer-card__display { color: #047857; }
    .lr-timer-card.is-idle .lr-timer-card__display { color: #94a3b8; }

    .lr-timer-card__actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
    }

    /* ===== Buttons ===== */
    .lr-btn-primary, .lr-btn-success, .lr-btn-danger, .lr-btn-ghost, .lr-btn-success-outline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 0.9rem;
        font-weight: 800;
        border-radius: 10px;
        border: 1px solid;
        transition: all 0.18s ease;
        text-decoration: none;
    }
    .lr-btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #fff;
        border-color: #1d4ed8;
        box-shadow: 0 6px 14px rgba(29, 78, 216, 0.28);
    }
    .lr-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(29, 78, 216, 0.36); color: #fff; }

    .lr-btn-success {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #fff; border-color: #047857;
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.26);
    }
    .lr-btn-success:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(16, 185, 129, 0.34); color: #fff; }

    .lr-btn-success-outline {
        background: #fff; color: #047857; border-color: #10b981;
    }
    .lr-btn-success-outline:hover { background: #ecfdf5; }

    .lr-btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: #fff; border-color: #b91c1c;
        box-shadow: 0 6px 14px rgba(220, 38, 38, 0.28);
    }
    .lr-btn-danger:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(220, 38, 38, 0.36); color: #fff; }

    .lr-btn-ghost {
        background: #fff; color: #475569; border-color: #cbd5e1;
    }
    .lr-btn-ghost:hover { background: #f8fafc; color: #1e293b; }

    /* ===== Attendance status card ===== */
    .lr-attendance-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        margin-bottom: 14px;
        border-radius: 14px;
        border: 1px solid;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .lr-attendance-card.is-pending {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fcd34d;
        color: #92400e;
    }
    .lr-attendance-card.is-active {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-color: #6ee7b7;
        color: #065f46;
    }
    .lr-attendance-card.is-done {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-color: #cbd5e1;
        color: #334155;
    }

    .lr-attendance-card__icon {
        flex-shrink: 0;
        width: 48px; height: 48px;
        border-radius: 12px;
        display: grid; place-items: center;
        font-size: 1.4rem;
        background: rgba(255, 255, 255, 0.7);
    }
    .lr-attendance-card.is-pending .lr-attendance-card__icon { color: #d97706; }
    .lr-attendance-card.is-active .lr-attendance-card__icon { color: #047857; }
    .lr-attendance-card.is-done .lr-attendance-card__icon { color: #475569; }

    .lr-attendance-card__body { flex: 1; min-width: 0; }
    .lr-attendance-card__title {
        font-size: 0.95rem;
        font-weight: 800;
        margin-bottom: 2px;
    }
    .lr-attendance-card__msg {
        font-size: 0.82rem;
        opacity: 0.88;
        line-height: 1.5;
    }
    .lr-attendance-card__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        margin-top: 8px;
    }
    .lr-att-stat {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 3px 10px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 999px;
    }
    .lr-att-stat i { font-size: 0.7rem; opacity: 0.85; }
    .lr-att-stat strong { font-weight: 800; }
    .lr-att-stat--warn { background: #fef3c7; color: #92400e; }
    .lr-att-stat--danger { background: #fee2e2; color: #991b1b; }

    .lr-attendance-card__link {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid currentColor;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 800;
        color: inherit;
        transition: all 0.18s ease;
    }
    .lr-attendance-card__link:hover {
        background: #fff;
        transform: translateX(2px);
        color: inherit;
    }

    @media (max-width: 768px) {
        .lr-attendance-card { flex-direction: column; align-items: flex-start; }
        .lr-attendance-card__link { align-self: stretch; justify-content: center; }
    }

    /* ===== Player card ===== */
    .lr-player-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }
    .lr-player-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    .lr-player-card__eyebrow {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
    }
    .lr-player-card__title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
        margin: 2px 0 0;
    }

    .lr-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        font-size: 0.74rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .lr-chip.is-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .lr-chip.is-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .lr-chip.is-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .lr-chip.is-info    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .lr-chip.is-light   { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .lr-chip.is-muted   { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }

    .lr-player-card__body { background: #0f172a; }

    .lr-player-iframe {
        width: 100%;
        min-height: 620px;
        border: 0;
        display: block;
    }

    /* Internal mode panel */
    .lr-player-internal {
        padding: 36px 32px;
        text-align: center;
        background: radial-gradient(circle at 30% 20%, rgba(56, 189, 248, 0.18), transparent 50%), #0f172a;
        color: #e2e8f0;
        min-height: 460px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .lr-player-internal__badge {
        display: inline-block;
        background: rgba(56, 189, 248, 0.18);
        color: #7dd3fc;
        font-weight: 800;
        font-size: 0.74rem;
        letter-spacing: 0.5px;
        padding: 6px 14px;
        border-radius: 999px;
        margin-bottom: 14px;
    }
    .lr-player-internal h3 { color: #fff; font-weight: 800; margin: 0 0 10px; }
    .lr-player-internal p { color: #94a3b8; margin: 0 auto 20px; max-width: 520px; }
    .lr-player-internal__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .lr-player-internal__stats > div {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 10px 18px;
        border-radius: 12px;
        min-width: 130px;
    }
    .lr-player-internal__stats span {
        display: block;
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .lr-player-internal__stats strong {
        display: block;
        font-size: 1rem;
        color: #fff;
        margin-top: 2px;
    }

    /* External launcher */
    .lr-player-launcher {
        padding: 40px 28px;
        text-align: center;
        background: radial-gradient(circle at 70% 20%, rgba(34, 197, 94, 0.18), transparent 50%), #0f172a;
        color: #e2e8f0;
        min-height: 460px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .lr-player-launcher__orb {
        width: 70px; height: 70px;
        background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        font-size: 1.6rem;
        color: #fff;
        margin-bottom: 14px;
        box-shadow: 0 12px 28px rgba(34, 197, 94, 0.4);
    }
    .lr-player-launcher h4 { color: #fff; font-weight: 800; margin: 0 0 8px; }
    .lr-player-launcher p { color: #94a3b8; max-width: 480px; margin: 0 auto 20px; }
    .lr-player-launcher__actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }

    .lr-meeting-info {
        margin-top: 22px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 10px 16px;
        border-radius: 10px;
        display: inline-flex;
        gap: 14px;
        align-items: center;
    }
    .lr-meeting-info span { color: #94a3b8; font-size: 0.78rem; }
    .lr-meeting-info strong { color: #fff; font-family: monospace; font-size: 0.92rem; }

    /* Empty placeholder */
    .lr-player-empty {
        padding: 60px 24px;
        text-align: center;
        color: #94a3b8;
        background: #0f172a;
        min-height: 360px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .lr-player-empty i { font-size: 3rem; opacity: 0.3; margin-bottom: 14px; }
    .lr-player-empty h5 { color: #cbd5e1; font-weight: 800; }
    .lr-player-empty p { max-width: 380px; margin: 0; }

    /* ===== Side cards ===== */
    .lr-side-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        margin-bottom: 14px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }
    .lr-side-card__head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
        border-bottom: 1px solid #fecaca;
        font-weight: 800;
        font-size: 0.86rem;
        color: #b91c1c;
    }
    .lr-side-card__head--toggle {
        all: unset;
        cursor: pointer;
        width: 100%;
        box-sizing: border-box;
    }
    .lr-side-card__head--toggle:hover { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); }
    .lr-side-card__head--toggle[aria-expanded="false"] { border-bottom-color: transparent; }

    .lr-side-card__chevron {
        transition: transform 0.25s ease;
        font-size: 0.78rem;
    }
    .lr-side-card__head--toggle[aria-expanded="true"] .lr-side-card__chevron {
        transform: rotate(180deg);
    }

    .lr-side-card__body {
        padding: 14px 16px;
    }

    .lr-stat-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 4px 0;
        font-size: 0.85rem;
    }
    .lr-stat-row span { color: #64748b; }
    .lr-stat-row strong { color: #0f172a; font-weight: 700; text-align: right; }

    /* Quick actions grid */
    .lr-quick-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .lr-quick-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 14px 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        text-align: center;
        transition: all 0.18s ease;
    }
    .lr-quick-btn i { font-size: 1.1rem; color: #94a3b8; transition: color 0.18s; }
    .lr-quick-btn:hover {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .lr-quick-btn:hover i { color: #dc2626; }

    /* Link history */
    .lr-link-history__title {
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #94a3b8;
        margin-bottom: 6px;
    }
    .lr-link-history__item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 6px;
    }
    .lr-link-history__url {
        font-family: monospace;
        font-size: 0.74rem;
        color: #1e293b;
        font-weight: 700;
        word-break: break-all;
    }
    .lr-link-history__meta {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    @media (max-width: 992px) {
        .lr-timer-card__display { font-size: 2.4rem; }
        .lr-player-internal, .lr-player-launcher { padding: 24px 18px; min-height: 360px; }
    }

    /* ===== Welcome banner xanh dương cho live-room ===== */
    .lr-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
        position: relative;
        overflow: hidden;
    }
    .lr-welcome-icon {
        flex-shrink: 0;
        width: 64px; height: 64px;
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.28);
        backdrop-filter: blur(8px);
        color: #fff;
        border-radius: 14px;
        display: grid; place-items: center;
        font-size: 1.6rem;
        position: relative;
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.18);
        z-index: 1;
    }
    .lr-live-dot {
        position: absolute;
        top: -3px; right: -3px;
        width: 16px; height: 16px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        animation: lrLiveBlink 1.4s ease-out infinite;
    }
    @keyframes lrLiveBlink {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .lr-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .lr-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem; font-weight: 800;
        letter-spacing: 1px;
        border-radius: 999px;
    }
    .lr-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .lr-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .lr-status-pill i { font-size: 0.62rem; }
    .lr-status-pill.is-success   { background: #dcfce7; color: #166534; animation: lrPulse 1.6s ease-out infinite; }
    .lr-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .lr-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .lr-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .lr-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    @keyframes lrPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.45); }
        50%      { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
    }
    .apx-welcome.lr-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.lr-welcome p i { color: #fef3c7; margin-right: 4px; }
    .lr-sep { opacity: 0.5; }

    .lr-countdown {
        display: inline-flex; align-items: center; gap: 6px;
        margin-top: 10px;
        padding: 6px 14px;
        background: rgba(0, 0, 0, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        font-size: 0.84rem;
        font-weight: 800;
        border-radius: 8px;
        letter-spacing: 0.4px;
        white-space: nowrap;
    }
    .lr-countdown:empty { display: none; }
    .lr-launch-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* Giữ class cũ để không vỡ JS countdown nếu code khác bám vào */
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

    /* ===== Jitsi Meet embed container ===== */
    .lr-jitsi-container {
        width: 100%;
        min-height: 660px;
        height: 78vh;
        background: #0f172a;
        position: relative;
    }
    .lr-jitsi-container iframe {
        width: 100% !important;
        height: 100% !important;
        border: 0 !important;
        display: block;
    }
    .lr-jitsi-end {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 10px;
        padding: 32px;
        text-align: center;
        background: #0f172a;
        color: #e2e8f0;
    }
    .lr-jitsi-end i { font-size: 2.4rem; color: #34d399; }
    .lr-jitsi-end h5 { color: #fff; font-weight: 800; margin: 0; }
    .lr-jitsi-end p { color: #94a3b8; margin: 0; max-width: 420px; }
    .lr-jitsi-end a { color: #60a5fa; word-break: break-all; }

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

// =============================================
// Real-time clock — đồng hồ thực tế dạy
// Anchor vào server timestamp `bat_dau_thuc_te` → đếm tới hiện tại
// Nếu đã có `ket_thuc_thuc_te` → freeze tại tổng thời gian
// Persistent: thoát rồi vào lại vẫn đúng vì đọc từ DB
// =============================================
(function () {
    const display = document.getElementById('lr-timer-display');
    if (!display) return;

    const startedAtStr = display.dataset.startedAt;
    const endedAtStr = display.dataset.endedAt;
    const serverNowStr = display.dataset.serverNow;
    const totalSeconds = parseInt(display.dataset.totalSeconds || '0', 10);

    // Đã kết thúc → giữ nguyên giá trị server-rendered, không cần JS update
    if (endedAtStr && totalSeconds > 0) return;

    // Chưa bắt đầu → giữ "00:00:00"
    if (!startedAtStr) return;

    const startedAt = new Date(startedAtStr);
    const serverNow = serverNowStr ? new Date(serverNowStr) : new Date();
    const clientNow = new Date();
    // Bù lệch giờ server vs client (nếu có)
    const skewMs = serverNow.getTime() - clientNow.getTime();

    function format(totalSec) {
        const safe = Math.max(0, Math.floor(totalSec));
        const h = Math.floor(safe / 3600);
        const m = Math.floor((safe % 3600) / 60);
        const s = safe % 60;
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(h)}:${pad(m)}:${pad(s)}`;
    }

    function tick() {
        const now = new Date(Date.now() + skewMs);
        const diff = (now.getTime() - startedAt.getTime()) / 1000;
        display.textContent = format(diff);
    }

    tick();
    window.setInterval(tick, 1000);
})();

// =============================================
// 1) Mở Google Meet / link ngoài bằng popup window
// =============================================
document.addEventListener('click', function (event) {
    const link = event.target.closest('a.lr-launch-popup');
    if (!link) return;

    const url = link.getAttribute('href');
    if (!url || url === '#') return;

    event.preventDefault();
    const w = Math.min(1280, window.screen.availWidth - 40);
    const h = Math.min(820, window.screen.availHeight - 80);
    const left = Math.round((window.screen.availWidth - w) / 2);
    const top = Math.round((window.screen.availHeight - h) / 2);
    const features = `popup=yes,width=${w},height=${h},left=${left},top=${top},menubar=no,toolbar=no,location=no,status=no`;
    const popup = window.open(url, 'lr-meet-' + Date.now(), features);
    if (!popup) {
        // popup bị browser chặn → fallback mở tab mới
        window.open(url, '_blank', 'noopener');
    } else {
        try { popup.focus(); } catch (e) {}
    }
});

// =============================================
// 2) Jitsi Meet — nhúng vào trang qua JitsiMeetExternalAPI
// =============================================
(function () {
    const container = document.getElementById('jitsi-container');
    if (!container) return;

    const server = container.dataset.jitsiServer || 'meet.jit.si';
    const room = container.dataset.jitsiRoom;
    const display = container.dataset.jitsiDisplay || 'Người dùng';
    const subject = container.dataset.jitsiSubject || '';
    const passcode = container.dataset.jitsiPasscode || '';
    const isHost = container.dataset.jitsiIsHost === '1';

    if (!room) return;

    function mountJitsi() {
        if (typeof JitsiMeetExternalAPI === 'undefined') return;

        const api = new JitsiMeetExternalAPI(server, {
            roomName: room,
            parentNode: container,
            width: '100%',
            height: '100%',
            userInfo: {
                displayName: display,
            },
            configOverwrite: {
                prejoinPageEnabled: false,
                disableDeepLinking: true,
                startWithAudioMuted: !isHost,
                startWithVideoMuted: !isHost,
                subject: subject,
            },
            interfaceConfigOverwrite: {
                MOBILE_APP_PROMO: false,
                SHOW_JITSI_WATERMARK: false,
                DISABLE_VIDEO_BACKGROUND: false,
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat',
                    'settings', 'raisehand', 'videoquality', 'tileview',
                    'select-background', 'mute-everyone', 'security',
                ],
            },
        });

        if (passcode) {
            api.addEventListener('participantRoleChanged', function (event) {
                if (event.role === 'moderator') {
                    api.executeCommand('password', passcode);
                }
            });
            api.addEventListener('passwordRequired', function () {
                api.executeCommand('password', passcode);
            });
        }

        api.addEventListener('readyToClose', function () {
            container.innerHTML = '<div class="lr-jitsi-end"><i class="fas fa-flag-checkered"></i><h5>Phiên Jitsi đã kết thúc</h5><p>Bấm nút "Kết thúc buổi học" để đồng bộ trạng thái.</p></div>';
        });
    }

    if (typeof JitsiMeetExternalAPI === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://' + server + '/external_api.js';
        script.async = true;
        script.onload = mountJitsi;
        script.onerror = function () {
            container.innerHTML = '<div class="lr-jitsi-end"><i class="fas fa-circle-exclamation text-danger"></i><h5>Không tải được Jitsi</h5><p>Kiểm tra kết nối mạng hoặc thử mở Jitsi ở tab mới: <a href="https://' + server + '/' + room + '" target="_blank" rel="noopener">' + server + '/' + room + '</a></p></div>';
        };
        document.head.appendChild(script);
    } else {
        mountJitsi();
    }
})();
</script>
@endpush

@include('pages.admin.partials._admin-page-styles')
@endsection
