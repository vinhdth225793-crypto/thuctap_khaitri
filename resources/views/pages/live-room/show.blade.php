@extends('layouts.app', ['title' => 'Phòng học trực tuyến'])

@section('content')
@php
    $mode = $mode ?? 'student';
    $platformPayload = $phongHocLive->du_lieu_nen_tang_json ?? [];
    $scheduleOnlineUrl = \App\Support\OnlineMeetingUrl::normalize($baiGiang->lichHoc?->link_online);
    $schedulePlatform = strtolower((string) $baiGiang->lichHoc?->nen_tang);
    $externalLaunchUrl = \App\Support\OnlineMeetingUrl::normalize($playerUrl ?: ($phongHocLive->effective_external_meeting_url ?: ($phongHocLive->join_url ?: ($phongHocLive->start_url ?: $scheduleOnlineUrl))));
    $roomTimelineStatus = $phongHocLive->timeline_trang_thai;
    $isRoomClosed = in_array($roomTimelineStatus, ['da_ket_thuc', 'da_huy'], true);
    $isGoogleMeetLaunch = $phongHocLive->nen_tang_live === \App\Models\PhongHocLive::PLATFORM_GOOGLE_MEET
        || str_contains(strtolower((string) $externalLaunchUrl), 'meet.google.com')
        || str_contains($schedulePlatform, 'google')
        || str_contains($schedulePlatform, 'meet');
    $canOpenExternalLaunch = filled($externalLaunchUrl) && ! $isRoomClosed && ($canJoinRoom || $isGoogleMeetLaunch);
    $isExternalLaunch = $canOpenExternalLaunch && !$playerSupportsEmbed;
    $meetingIdentifier = $platformPayload['meeting_id'] ?? $platformPayload['meeting_code'] ?? $baiGiang->lichHoc?->meeting_id ?? null;
    $meetingPasscode = $platformPayload['passcode'] ?? $baiGiang->lichHoc?->mat_khau_cuoc_hop ?? null;
    if (!$meetingIdentifier && $isGoogleMeetLaunch && $externalLaunchUrl) {
        $meetingIdentifier = \App\Support\OnlineMeetingUrl::meetingCode($externalLaunchUrl);
    }
    $platformLaunchLabel = $isGoogleMeetLaunch ? 'Google Meet' : ($phongHocLive->nen_tang_live === 'google_meet' ? 'Google Meet' : $phongHocLive->platform_label);
    $joinManageActionLabel = $playerSupportsEmbed ? 'Mở phòng học trực tiếp' : 'Mở ' . $platformLaunchLabel;

    // Jitsi embed (nhúng vào trang)
    $isJitsi = $phongHocLive->nen_tang_live === \App\Models\PhongHocLive::PLATFORM_JITSI;
    $isInternalRoom = $phongHocLive->nen_tang_live === \App\Models\PhongHocLive::PLATFORM_INTERNAL;
    $jitsiServer = config('live_room.platforms.jitsi.server', 'meet.jit.si');
    $jitsiRoomName = $isJitsi
        ? 'learntest-' . ($baiGiang->khoaHoc->ma_khoa_hoc ?? 'kh') . '-bg' . $baiGiang->id . '-pl' . $phongHocLive->id
        : null;
    $jitsiDisplayName = auth()->user()?->ho_ten ?? ($mode === 'admin' ? 'Giám sát' : 'Học viên');
    $jitsiSubject = $phongHocLive->tieu_de;
    $jitsiPasscode = $platformPayload['passcode'] ?? null;

    $joinStudentActionLabel = $playerSupportsEmbed ? 'Tham gia trực tiếp' : 'Tham gia ' . $platformLaunchLabel;
    $showRoute = $showRoute ?? route('hoc-vien.live-room.show', $baiGiang->id);
    $joinRoute = $joinRoute ?? route('hoc-vien.live-room.join', $baiGiang->id);
    $leaveRoute = $leaveRoute ?? route('hoc-vien.live-room.leave', $baiGiang->id);
    $joinButtonLabel = $mode === 'admin' ? 'Vào phòng với vai trò giám sát' : $joinStudentActionLabel;

    // Trạng thái đồng hồ thực tế — ưu tiên lich_hoc.actual_started_at (GV bấm "Bắt đầu buổi học")
    // Fallback: phong_hoc_live.bat_dau_thuc_te (live room start)
    $startedAt = $baiGiang->lichHoc?->actual_started_at ?? $phongHocLive->bat_dau_thuc_te;
    $endedAt = $baiGiang->lichHoc?->actual_finished_at ?? $phongHocLive->ket_thuc_thuc_te;
    $isLive = $startedAt && !$endedAt;
    $isFinished = $startedAt && $endedAt;
    $totalSeconds = $isFinished ? $endedAt->diffInSeconds($startedAt) : 0;

    $statusClassMap = [
        'sap_dien_ra'    => 'is-warning',
        'dang_dien_ra'   => 'is-success',
        'da_ket_thuc'    => 'is-secondary',
        'da_huy'         => 'is-danger',
        'da_hoan_thanh'  => 'is-info',
    ];
    $hStatusClass = $statusClassMap[$roomTimelineStatus] ?? 'is-secondary';
@endphp

<div class="container-fluid lrs-page">
    {{-- ======= Welcome banner xanh dương ======= --}}
    <div class="apx-welcome lrs-welcome">
        <div class="lrs-welcome-icon">
            <i class="fas fa-graduation-cap"></i>
            @if($isLive)
                <span class="lrs-live-dot"></span>
            @endif
        </div>
        <div class="apx-welcome-text">
            <div class="lrs-tag-row">
                <span class="lrs-loai-badge">
                    <i class="fas fa-{{ $mode === 'admin' ? 'user-shield' : 'user-graduate' }}"></i>
                    {{ $mode === 'admin' ? 'GIÁM SÁT PHÒNG HỌC' : 'PHÒNG HỌC TRỰC TUYẾN' }}
                </span>
                <span class="lrs-status-pill {{ $hStatusClass }}">
                    @if($isLive)
                        <i class="fas fa-circle-play"></i> Đang diễn ra
                    @else
                        <i class="fas fa-circle"></i> {{ $phongHocLive->timeline_trang_thai_label }}
                    @endif
                </span>
                <span class="lrs-status-badge"><i class="fas fa-cube"></i> {{ $phongHocLive->platform_label }}</span>
                <span class="lrs-status-badge"><i class="far fa-clock"></i> {{ $phongHocLive->thoi_luong_phut }} phút</span>
            </div>
            <h4>{{ $phongHocLive->tieu_de }}</h4>
            <p>
                <span><i class="fas fa-graduation-cap"></i> {{ $baiGiang->khoaHoc->ten_khoa_hoc }}</span>
                <span class="lrs-sep">·</span>
                <span><i class="fas fa-cube"></i> {{ $baiGiang->moduleHoc->ten_module }}</span>
                <span class="lrs-sep">·</span>
                <span><i class="fas fa-calendar-day"></i> {{ $phongHocLive->thoi_gian_bat_dau->format('d/m/Y H:i') }}</span>
            </p>
            <div
                id="lrs-countdown"
                class="lrs-countdown"
                data-open-at="{{ $phongHocLive->join_opens_at->toIso8601String() }}"
                data-start-at="{{ $phongHocLive->thoi_gian_bat_dau->toIso8601String() }}"
                data-timeline="{{ $roomTimelineStatus }}"
            ></div>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ $backUrl }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Quay lại</span>
            </a>
        </div>
    </div>

    {{-- ======= Banner "Buổi học đã kết thúc" ======= --}}
    @if($isFinished || $isRoomClosed)
        @php
            $myAttHV = auth()->user()?->ma_nguoi_dung && $baiGiang->lich_hoc_id
                ? \App\Models\DiemDanh::where('lich_hoc_id', $baiGiang->lich_hoc_id)
                    ->where('hoc_vien_id', auth()->user()->ma_nguoi_dung)
                    ->first()
                : null;
            $attMapHV = [
                'co_mat'   => ['label' => 'Có mặt',   'icon' => 'fa-circle-check', 'color' => '#10b981'],
                'vao_tre'  => ['label' => 'Vào trễ',  'icon' => 'fa-clock',         'color' => '#f59e0b'],
                'vang_mat' => ['label' => 'Vắng mặt', 'icon' => 'fa-circle-xmark',  'color' => '#ef4444'],
                'co_phep'  => ['label' => 'Có phép',  'icon' => 'fa-circle-info',   'color' => '#0ea5e9'],
            ];
            $myAttPillHV = $myAttHV ? ($attMapHV[$myAttHV->trang_thai] ?? null) : null;
        @endphp
        <div class="session-ended-banner">
            <div class="session-ended-banner__icon">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div class="session-ended-banner__main">
                <div class="session-ended-banner__title">Buổi học đã kết thúc</div>
                <div class="session-ended-banner__msg">
                    @if($endedAt)
                        Phòng học đã đóng lúc <strong>{{ $endedAt->format('H:i:s · d/m/Y') }}</strong>
                        @if($startedAt)
                            · Tổng thời lượng <strong>{{ $totalSeconds > 0 ? floor($totalSeconds / 60) : 0 }} phút</strong>.
                        @else
                            .
                        @endif
                    @else
                        Phòng học đã đóng. Bạn có thể quay lại buổi học để xem bản ghi (nếu có), tài liệu và bài tập liên quan.
                    @endif
                </div>
                @if($myAttPillHV)
                    <div class="session-ended-banner__att">
                        Trạng thái điểm danh của bạn:
                        <span class="session-ended-banner__pill" style="background: {{ $myAttPillHV['color'] }};">
                            <i class="fas {{ $myAttPillHV['icon'] }}"></i> {{ $myAttPillHV['label'] }}
                        </span>
                    </div>
                @elseif(auth()->user()?->ma_nguoi_dung)
                    <div class="session-ended-banner__att">
                        <i class="fas fa-circle-question"></i> Giảng viên chưa cập nhật điểm danh cho bạn ở buổi này.
                    </div>
                @endif
            </div>
            <a href="{{ $backUrl }}" class="session-ended-banner__cta">
                <i class="fas fa-arrow-right"></i> Về buổi học
            </a>
        </div>
    @endif

    {{-- ============ BODY: timer + player + side ============ --}}
    <div class="row g-4">
        <div class="col-lg-12 mb-2">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-0">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-0">{{ session('error') }}</div>
            @endif
        </div>

        {{-- ===== MAIN COL ===== --}}
        <div class="col-lg-8">
            {{-- Đồng hồ thực tế buổi học --}}
            <div class="lrs-timer-card {{ $isLive ? 'is-running' : ($isFinished ? 'is-finished' : 'is-idle') }}">
                <div class="lrs-timer-card__head">
                    <div class="lrs-timer-card__label">
                        @if($isLive)
                            <span class="lrs-pulse"></span>
                            <span>Đang diễn ra</span>
                        @elseif($isFinished)
                            <i class="fas fa-flag-checkered"></i>
                            <span>Buổi học đã kết thúc</span>
                        @else
                            <i class="fas fa-pause-circle"></i>
                            <span>Buổi học chưa bắt đầu</span>
                        @endif
                    </div>
                    @if($startedAt)
                        <div class="lrs-timer-card__meta">
                            <i class="fas fa-play"></i> Bắt đầu {{ $startedAt->format('H:i:s · d/m/Y') }}
                            @if($endedAt)
                                <span class="lrs-sep">·</span>
                                <i class="fas fa-stop"></i> Kết thúc {{ $endedAt->format('H:i:s') }}
                            @endif
                        </div>
                    @else
                        <div class="lrs-timer-card__meta">
                            <i class="far fa-calendar"></i> Dự kiến {{ $phongHocLive->thoi_gian_bat_dau->format('H:i · d/m/Y') }}
                        </div>
                    @endif
                </div>

                <div
                    class="lrs-timer-card__display"
                    id="lrs-timer-display"
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

                <div class="lrs-timer-card__hint">
                    @if($isLive)
                        Giảng viên đang dạy. Bạn có thể tham gia phòng để nghe giảng và tương tác.
                    @elseif($isFinished)
                        Buổi học đã kết thúc. Bạn có thể xem lại bản ghi (nếu có) trong trang buổi học.
                    @elseif($canJoinRoom)
                        Phòng đã mở để vào sớm. Bấm "Tham gia phòng học" bên phải khi sẵn sàng.
                    @else
                        Hãy quay lại đúng giờ học. Hệ thống sẽ tự động mở phòng khi đến mốc thời gian.
                    @endif
                </div>
            </div>

            {{-- Player surface --}}
            <div class="lrs-player-card">
                <div class="lrs-player-card__head">
                    <div>
                        <div class="lrs-player-card__eyebrow">{{ $platformLaunchLabel }}</div>
                        <h6 class="lrs-player-card__title">{{ $isJitsi ? 'Phòng học trực tuyến' : 'Khung học' }}</h6>
                    </div>
                    @if($playerMode === 'host')
                        <span class="lrs-chip is-success">Chế độ điều hành</span>
                    @elseif($playerMode === 'participant')
                        <span class="lrs-chip is-info">Đang tham gia</span>
                    @elseif($isExternalLaunch)
                        <span class="lrs-chip is-warning">Mở bên ngoài trang</span>
                    @elseif($isRoomClosed)
                        <span class="lrs-chip is-muted">Đã đóng</span>
                    @else
                        <span class="lrs-chip is-light">Chưa mở</span>
                    @endif
                </div>

                <div class="lrs-player-card__body">
                    @if($playerMode && $isJitsi)
                        <div id="jitsi-container" class="lrs-jitsi-container"
                             data-jitsi-server="{{ $jitsiServer }}"
                             data-jitsi-room="{{ $jitsiRoomName }}"
                             data-jitsi-display="{{ $jitsiDisplayName }}"
                             data-jitsi-subject="{{ $jitsiSubject }}"
                             data-jitsi-passcode="{{ $jitsiPasscode }}"
                             data-jitsi-is-host="0"></div>
                    @elseif($playerUrl && $playerSupportsEmbed)
                        <iframe
                            src="{{ $playerUrl }}"
                            title="{{ $phongHocLive->tieu_de }}"
                            allow="camera; microphone; fullscreen; display-capture; autoplay"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                            class="lrs-player-iframe"></iframe>
                    @elseif($playerMode === 'participant' && $isInternalRoom)
                        <div class="lrs-player-internal">
                            <div class="lrs-player-internal__badge">LIVE NỘI BỘ</div>
                            <h3>{{ $mode === 'admin' ? 'Đang giám sát phòng nội bộ' : 'Đang tham gia phòng nội bộ' }}</h3>
                            <p>Hệ thống đã ghi nhận phiên tham gia của bạn. Phòng nội bộ dùng khung điều hành local của hệ thống.</p>
                        </div>
                    @elseif($canOpenExternalLaunch)
                        <div class="lrs-player-launcher">
                            <div class="lrs-player-launcher__orb"><i class="fas fa-video"></i></div>
                            <div class="lrs-chip is-light mb-2">{{ strtoupper($platformLaunchLabel) }}</div>
                            <h4>{{ $platformLaunchLabel }} sẽ mở trong cửa sổ popup</h4>
                            <p>Nền tảng này không nhúng được vào website. Bấm nút bên dưới để vào lớp học — popup vẫn hiển thị bên cạnh trang này.</p>
                            <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener noreferrer" class="btn lrs-btn-primary lr-launch-popup">
                                <i class="fas fa-external-link-alt"></i> Mở {{ $platformLaunchLabel }} ngay
                            </a>
                            @if($meetingIdentifier)
                                <div class="lrs-meeting-info">
                                    <span>Mã phòng</span><strong>{{ $meetingIdentifier }}</strong>
                                    @if($meetingPasscode)
                                        <span>Mật khẩu</span><strong>{{ $meetingPasscode }}</strong>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @elseif($isRoomClosed)
                        <div class="lrs-player-empty">
                            <i class="fas fa-lock"></i>
                            <h5>Phòng học đã đóng</h5>
                            <p>{{ $platformLaunchLabel }} đã đóng vì buổi học đã kết thúc. Hãy xem lại bản ghi nếu có.</p>
                        </div>
                    @else
                        <div class="lrs-player-empty">
                            <i class="fas fa-video-slash"></i>
                            <h5>Phòng học chưa bắt đầu</h5>
                            <p>Vui lòng quay lại khi đến giờ học hoặc khi giảng viên mở phòng.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mô tả phòng học --}}
            @if($phongHocLive->mo_ta || $baiGiang->mo_ta)
                <div class="lrs-side-card mt-3">
                    <div class="lrs-side-card__head">
                        <i class="fas fa-info-circle"></i>
                        <span>Mô tả phòng học</span>
                    </div>
                    <div class="lrs-side-card__body">
                        <p class="mb-0 text-muted small">{{ $phongHocLive->mo_ta ?: $baiGiang->mo_ta }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ===== SIDE COL ===== --}}
        <div class="col-lg-4">
            {{-- Action card (sticky) --}}
            <div class="lrs-side-card lrs-action-card">
                <div class="lrs-side-card__head">
                    <i class="fas fa-bolt"></i>
                    <span>{{ $mode === 'admin' ? 'Hành động giám sát' : 'Hành động' }}</span>
                </div>
                <div class="lrs-side-card__body">
                    <div class="d-grid gap-2">
                        @if(!$playerMode && $canJoinRoom)
                            <form action="{{ $joinRoute }}" method="POST">
                                @csrf
                                <button type="submit" class="btn lrs-btn-primary w-100">
                                    <i class="fas fa-door-open"></i> {{ $joinButtonLabel }}
                                </button>
                            </form>
                        @endif

                        @if($isExternalLaunch)
                            <a href="{{ $externalLaunchUrl }}" target="_blank" rel="noopener noreferrer" class="btn lrs-btn-success lr-launch-popup">
                                <i class="fas fa-external-link-alt"></i> {{ $joinManageActionLabel }}
                            </a>
                        @endif

                        @if($playerMode === 'participant')
                            <form action="{{ $leaveRoute }}" method="POST">
                                @csrf
                                <button type="submit" class="btn lrs-btn-ghost w-100">
                                    <i class="fas fa-sign-out-alt"></i> Rời phòng
                                </button>
                            </form>
                        @endif

                        @if($canOpenExternalLaunch || $playerUrl)
                            <a href="{{ $showRoute }}" class="btn lrs-btn-ghost w-100">
                                <i class="fas fa-rotate-right"></i> Làm mới phòng
                            </a>
                        @endif

                        <a href="{{ $backUrl }}" class="btn btn-link text-decoration-none small text-center">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại buổi học
                        </a>
                    </div>

                    @if(Auth::user()?->isAdmin())
                        <hr class="my-3">
                        <a href="{{ route('admin.bai-giang.edit', $baiGiang->id) }}" class="btn lrs-btn-outline-dark w-100">
                            <i class="fas fa-cog"></i> Cấu hình phòng (Admin)
                        </a>
                    @endif
                </div>
            </div>

            {{-- Thông tin nhanh --}}
            <div class="lrs-side-card">
                <div class="lrs-side-card__head">
                    <i class="fas fa-circle-info"></i>
                    <span>Thông tin phòng</span>
                </div>
                <div class="lrs-side-card__body">
                    <div class="lrs-stat-row">
                        <span>Người chủ trì</span><strong>{{ $phongHocLive->moderator->ho_ten ?? 'Chưa cập nhật' }}</strong>
                    </div>
                    <div class="lrs-stat-row">
                        <span>Trợ giảng</span><strong>{{ $phongHocLive->troGiang->ho_ten ?? 'Không có' }}</strong>
                    </div>
                    <div class="lrs-stat-row">
                        <span>Mở phòng trước</span><strong>{{ $phongHocLive->mo_phong_truoc_phut }} phút</strong>
                    </div>
                    <div class="lrs-stat-row">
                        <span>Số người tham gia</span><strong>{{ $phongHocLive->participant_count }}</strong>
                    </div>
                    @if($meetingIdentifier && $canOpenExternalLaunch)
                        <hr class="my-2">
                        <div class="lrs-stat-row">
                            <span>Mã phòng</span><strong>{{ $meetingIdentifier }}</strong>
                        </div>
                        @if($meetingPasscode)
                            <div class="lrs-stat-row">
                                <span>Mật khẩu</span><strong>{{ $meetingPasscode }}</strong>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Hint trạng thái --}}
            <div class="lrs-status-hint lrs-status-hint--{{ $phongHocLive->timeline_trang_thai_color }}">
                <i class="fas fa-lightbulb"></i>
                <p>{{ $phongHocLive->status_hint }}</p>
            </div>
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ============================================================
       LIVE ROOM HỌC VIÊN — clean redesign
       ============================================================ */
    .lrs-page { padding-bottom: 24px; }

    /* ===== Welcome banner ===== */
    .lrs-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
        position: relative;
        overflow: hidden;
    }
    .lrs-welcome-icon {
        flex-shrink: 0;
        width: 64px; height: 64px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        backdrop-filter: blur(8px);
        color: #fff;
        border-radius: 14px;
        display: grid; place-items: center;
        font-size: 1.6rem;
        position: relative;
        z-index: 1;
    }
    .lrs-live-dot {
        position: absolute;
        top: 6px; right: 6px;
        width: 12px; height: 12px;
        background: #ef4444;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        animation: lrsPulseDot 1.4s ease-out infinite;
    }
    @keyframes lrsPulseDot {
        0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70%  { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .lrs-tag-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
    .lrs-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px;
        background: #fff;
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        border-radius: 999px;
        text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }
    .lrs-status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        font-size: 0.74rem;
        font-weight: 700;
        border-radius: 999px;
        backdrop-filter: blur(6px);
    }
    .lrs-status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .lrs-status-pill.is-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .lrs-status-pill.is-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .lrs-status-pill.is-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .lrs-status-pill.is-info    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .lrs-status-pill.is-secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .lrs-status-pill i { font-size: 0.6rem; }

    .lrs-sep { color: rgba(255, 255, 255, 0.5); }
    .lrs-countdown {
        margin-top: 10px;
        font-size: 0.84rem;
        color: rgba(255, 255, 255, 0.92);
        font-weight: 700;
    }

    /* ===== Timer card ===== */
    .lrs-timer-card {
        background: #fff;
        border-radius: 18px;
        padding: 20px 22px;
        margin-bottom: 14px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
        border: 1px solid #e2e8f0;
    }
    .lrs-timer-card.is-running {
        border-color: #6ee7b7;
        background: linear-gradient(135deg, #fff 0%, #ecfdf5 100%);
        box-shadow: 0 12px 28px rgba(16, 185, 129, 0.15);
    }
    .lrs-timer-card.is-finished {
        border-color: #cbd5e1;
        background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    }

    .lrs-timer-card__head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    .lrs-timer-card__label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .lrs-timer-card.is-running .lrs-timer-card__label { color: #047857; }
    .lrs-timer-card.is-finished .lrs-timer-card__label { color: #475569; }
    .lrs-timer-card.is-idle .lrs-timer-card__label { color: #64748b; }

    .lrs-pulse {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #10b981;
        animation: lrsPulseGreen 1.4s ease-out infinite;
    }
    @keyframes lrsPulseGreen {
        0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
        70%  { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .lrs-timer-card__meta {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .lrs-timer-card__meta i { color: #94a3b8; margin: 0 4px 0 0; }
    .lrs-timer-card__meta .lrs-sep { color: #cbd5e1; margin: 0 6px; }

    .lrs-timer-card__display {
        font-family: 'Courier New', 'Consolas', monospace;
        font-size: 3rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: 0.04em;
        line-height: 1;
        margin: 14px 0 12px;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .lrs-timer-card.is-running .lrs-timer-card__display { color: #047857; }
    .lrs-timer-card.is-finished .lrs-timer-card__display { color: #475569; }
    .lrs-timer-card.is-idle .lrs-timer-card__display { color: #94a3b8; }

    .lrs-timer-card__hint {
        font-size: 0.82rem;
        color: #475569;
        text-align: center;
        padding-top: 8px;
        border-top: 1px dashed #e2e8f0;
        line-height: 1.5;
    }

    /* ===== Buttons ===== */
    .lrs-btn-primary, .lrs-btn-success, .lrs-btn-ghost, .lrs-btn-outline-dark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 0.88rem;
        font-weight: 800;
        border-radius: 10px;
        border: 1px solid;
        transition: all 0.18s ease;
        text-decoration: none;
        cursor: pointer;
    }
    .lrs-btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #fff; border-color: #1d4ed8;
        box-shadow: 0 6px 14px rgba(29, 78, 216, 0.28);
    }
    .lrs-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(29, 78, 216, 0.36); color: #fff; }
    .lrs-btn-success {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #fff; border-color: #047857;
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.26);
    }
    .lrs-btn-success:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(16, 185, 129, 0.34); color: #fff; }
    .lrs-btn-ghost {
        background: #fff; color: #475569; border-color: #cbd5e1;
    }
    .lrs-btn-ghost:hover { background: #f8fafc; color: #1e293b; border-color: #94a3b8; }
    .lrs-btn-outline-dark {
        background: #fff; color: #1e293b; border-color: #1e293b;
    }
    .lrs-btn-outline-dark:hover { background: #1e293b; color: #fff; }

    /* ===== Player card ===== */
    .lrs-player-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }
    .lrs-player-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    .lrs-player-card__eyebrow {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
    }
    .lrs-player-card__title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
        margin: 2px 0 0;
    }
    .lrs-chip {
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
    .lrs-chip.is-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .lrs-chip.is-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .lrs-chip.is-info    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .lrs-chip.is-light   { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .lrs-chip.is-muted   { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }

    .lrs-player-card__body { background: #0f172a; min-height: 460px; }
    .lrs-player-iframe {
        width: 100%;
        min-height: 620px;
        border: 0;
        display: block;
    }
    .lrs-jitsi-container {
        width: 100%;
        min-height: 660px;
        height: 78vh;
        background: #0f172a;
        position: relative;
    }
    .lrs-jitsi-container iframe {
        width: 100% !important;
        height: 100% !important;
        border: 0 !important;
        display: block;
    }

    .lrs-player-internal,
    .lrs-player-launcher,
    .lrs-player-empty {
        padding: 36px 28px;
        text-align: center;
        color: #e2e8f0;
        min-height: 460px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .lrs-player-internal {
        background: radial-gradient(circle at 30% 20%, rgba(56, 189, 248, 0.18), transparent 50%), #0f172a;
    }
    .lrs-player-launcher {
        background: radial-gradient(circle at 70% 20%, rgba(34, 197, 94, 0.18), transparent 50%), #0f172a;
    }
    .lrs-player-empty {
        background: #0f172a;
        color: #94a3b8;
    }

    .lrs-player-internal__badge {
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
    .lrs-player-internal h3,
    .lrs-player-launcher h4 { color: #fff; font-weight: 800; margin: 0 0 10px; }
    .lrs-player-internal p,
    .lrs-player-launcher p { color: #94a3b8; max-width: 480px; margin: 0 auto 18px; }

    .lrs-player-launcher__orb {
        width: 70px; height: 70px;
        background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        font-size: 1.6rem;
        color: #fff;
        margin-bottom: 14px;
        box-shadow: 0 12px 28px rgba(34, 197, 94, 0.4);
    }
    .lrs-meeting-info {
        margin-top: 22px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 10px 16px;
        border-radius: 10px;
        display: inline-flex;
        gap: 14px;
        align-items: center;
    }
    .lrs-meeting-info span { color: #94a3b8; font-size: 0.78rem; }
    .lrs-meeting-info strong { color: #fff; font-family: monospace; font-size: 0.92rem; }

    .lrs-player-empty i { font-size: 3rem; opacity: 0.3; margin-bottom: 14px; }
    .lrs-player-empty h5 { color: #cbd5e1; font-weight: 800; }
    .lrs-player-empty p { max-width: 380px; margin: 0; color: #94a3b8; }

    /* ===== Side cards ===== */
    .lrs-side-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        margin-bottom: 14px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }
    .lrs-action-card {
        position: sticky;
        top: 1.5rem;
    }
    .lrs-side-card__head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: linear-gradient(135deg, #fff 0%, #eff6ff 100%);
        border-bottom: 1px solid #bfdbfe;
        font-weight: 800;
        font-size: 0.86rem;
        color: #1d4ed8;
    }
    .lrs-side-card__head i { color: #2563eb; }
    .lrs-side-card__body { padding: 14px 16px; }

    .lrs-stat-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 4px 0;
        font-size: 0.85rem;
    }
    .lrs-stat-row span { color: #64748b; }
    .lrs-stat-row strong { color: #0f172a; font-weight: 700; text-align: right; }

    .lrs-status-hint {
        padding: 14px 16px;
        border-radius: 14px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .lrs-status-hint i { font-size: 1.05rem; flex-shrink: 0; margin-top: 2px; }
    .lrs-status-hint p { margin: 0; line-height: 1.5; }
    .lrs-status-hint--success { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
    .lrs-status-hint--success i { color: #10b981; }
    .lrs-status-hint--warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .lrs-status-hint--warning i { color: #f59e0b; }
    .lrs-status-hint--primary { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .lrs-status-hint--primary i { color: #2563eb; }
    .lrs-status-hint--info { background: #cffafe; color: #155e75; border: 1px solid #67e8f9; }
    .lrs-status-hint--info i { color: #0891b2; }
    .lrs-status-hint--danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .lrs-status-hint--danger i { color: #ef4444; }
    .lrs-status-hint--secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .lrs-status-hint--secondary i { color: #64748b; }

    @media (max-width: 992px) {
        .lrs-timer-card__display { font-size: 2.4rem; }
        .lrs-action-card { position: static; }
        .lrs-player-internal, .lrs-player-launcher, .lrs-player-empty { padding: 24px 18px; min-height: 320px; }
    }

    /* ===== Banner "Buổi học đã kết thúc" ===== */
    .session-ended-banner {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        margin: 0 0 16px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border: 1px solid #cbd5e1;
        border-left: 5px solid #475569;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .session-ended-banner__icon {
        flex-shrink: 0;
        width: 56px; height: 56px;
        background: #fff;
        border: 2px solid #cbd5e1;
        border-radius: 14px;
        display: grid; place-items: center;
        font-size: 1.7rem;
        color: #475569;
    }
    .session-ended-banner__main { flex: 1; min-width: 0; }
    .session-ended-banner__title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .session-ended-banner__msg {
        font-size: 0.86rem;
        color: #475569;
        line-height: 1.5;
    }
    .session-ended-banner__msg strong { color: #1e293b; font-weight: 800; }
    .session-ended-banner__att {
        margin-top: 10px;
        font-size: 0.84rem;
        color: #475569;
        font-weight: 600;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .session-ended-banner__pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        font-size: 0.78rem;
        font-weight: 800;
        color: #fff;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .session-ended-banner__pill i { font-size: 0.7rem; }
    .session-ended-banner__cta {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #1e293b;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 800;
        font-size: 0.84rem;
        transition: all 0.18s ease;
    }
    .session-ended-banner__cta:hover {
        background: #1e293b;
        color: #fff;
        border-color: #1e293b;
        transform: translateX(2px);
    }

    @media (max-width: 720px) {
        .session-ended-banner { flex-direction: column; align-items: flex-start; }
        .session-ended-banner__cta { align-self: stretch; justify-content: center; }
    }
</style>

@push('scripts')
<script>
// ====================================================
// Countdown banner — chỉ hiển thị, không reload tự động
// ====================================================
document.addEventListener('DOMContentLoaded', function () {
    const cd = document.getElementById('lrs-countdown');
    if (!cd) return;

    const openAt = new Date(cd.dataset.openAt);
    const startAt = new Date(cd.dataset.startAt);
    const timeline = cd.dataset.timeline || '';

    function fmt(sec) {
        const safe = Math.max(0, Math.floor(sec));
        const h = Math.floor(safe / 3600);
        const m = Math.floor((safe % 3600) / 60);
        const s = safe % 60;
        if (h > 0) return `${h}h ${m}p ${s}s`;
        return `${m}p ${s}s`;
    }

    function tick() {
        const now = new Date();
        if (timeline === 'dang_dien_ra' || timeline === 'da_ket_thuc') {
            cd.textContent = '';
            return;
        }
        if (now < openAt) {
            cd.innerHTML = `<i class="fas fa-hourglass-half me-1"></i> Còn ${fmt((openAt - now) / 1000)} đến giờ mở phòng`;
        } else if (now < startAt) {
            cd.innerHTML = `<i class="fas fa-clock me-1"></i> Còn ${fmt((startAt - now) / 1000)} đến giờ bắt đầu`;
        } else {
            cd.innerHTML = `<i class="fas fa-circle-play me-1"></i> Đã đến giờ học`;
        }
    }
    tick();
    setInterval(tick, 1000);
});

// ====================================================
// Real-time clock — đồng hồ buổi học (anchor server)
// ====================================================
(function () {
    const display = document.getElementById('lrs-timer-display');
    if (!display) return;

    const startedAtStr = display.dataset.startedAt;
    const endedAtStr = display.dataset.endedAt;
    const serverNowStr = display.dataset.serverNow;
    const totalSeconds = parseInt(display.dataset.totalSeconds || '0', 10);

    if (endedAtStr && totalSeconds > 0) return; // đã freeze server-side
    if (!startedAtStr) return; // chưa bắt đầu

    const startedAt = new Date(startedAtStr);
    const serverNow = serverNowStr ? new Date(serverNowStr) : new Date();
    const skewMs = serverNow.getTime() - new Date().getTime();

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
        display.textContent = format((now.getTime() - startedAt.getTime()) / 1000);
    }
    tick();
    setInterval(tick, 1000);
})();

// ====================================================
// Mở Google Meet bằng popup window
// ====================================================
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
    const popup = window.open(url, 'lrs-meet-' + Date.now(), features);
    if (!popup) { window.open(url, '_blank', 'noopener'); }
    else { try { popup.focus(); } catch (e) {} }
});

// ====================================================
// Jitsi Meet embed
// ====================================================
(function () {
    const container = document.getElementById('jitsi-container');
    if (!container) return;

    const server = container.dataset.jitsiServer || 'meet.jit.si';
    const room = container.dataset.jitsiRoom;
    const display = container.dataset.jitsiDisplay || 'Học viên';
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
            userInfo: { displayName: display },
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
            },
        });
        if (passcode) {
            api.addEventListener('passwordRequired', function () {
                api.executeCommand('password', passcode);
            });
        }
        api.addEventListener('readyToClose', function () {
            container.innerHTML = '<div class="lrs-player-empty"><i class="fas fa-flag-checkered" style="color:#34d399;opacity:1"></i><h5 style="color:#fff">Bạn đã rời phòng</h5><p>Bấm "Rời phòng" bên phải để xác nhận và quay lại buổi học.</p></div>';
        });
    }

    if (typeof JitsiMeetExternalAPI === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://' + server + '/external_api.js';
        script.async = true;
        script.onload = mountJitsi;
        script.onerror = function () {
            container.innerHTML = '<div class="lrs-player-empty"><i class="fas fa-circle-exclamation" style="color:#ef4444;opacity:1"></i><h5 style="color:#fff">Không tải được Jitsi</h5><p>Mở trực tiếp: <a href="https://' + server + '/' + room + '" target="_blank" style="color:#60a5fa">' + server + '/' + room + '</a></p></div>';
        };
        document.head.appendChild(script);
    } else {
        mountJitsi();
    }
})();
</script>
@endpush
@endsection
