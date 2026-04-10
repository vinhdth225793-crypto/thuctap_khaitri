@extends('layouts.app', ['title' => 'Phòng học live'])

@section('content')
@php
    $timelineStatus = $phongHocLive->timeline_trang_thai;
    $showRoute = route('giang-vien.live-room.show', $lectureId);
    $hostViewRoute = route('giang-vien.live-room.show', ['id' => $lectureId, 'player' => 'host']);
    $startRoute = route('giang-vien.live-room.start', $lectureId);
    $leaveRoute = route('giang-vien.live-room.leave', $lectureId);
    $endRoute = route('giang-vien.live-room.end', $lectureId);
    $platformPayload = $phongHocLive->du_lieu_nen_tang_json ?? [];
    $meetingIdentifier = $platformPayload['meeting_id'] ?? $platformPayload['meeting_code'] ?? null;
    $meetingPasscode = $platformPayload['passcode'] ?? null;
    $platformLabel = $phongHocLive->nen_tang_live === 'google_meet' ? 'Google Meet' : $phongHocLive->platform_label;
    $isInternalRoom = $phongHocLive->nen_tang_live === \App\Models\PhongHocLive::PLATFORM_INTERNAL;
    $canTeacherStart = $canManageRoom
        && ($isInternalRoom || filled($phongHocLive->start_url) || filled($phongHocLive->join_url))
        && !in_array($timelineStatus, [\App\Models\PhongHocLive::ROOM_STATE_DANG_DIEN_RA, \App\Models\PhongHocLive::ROOM_STATE_DA_KET_THUC, \App\Models\PhongHocLive::ROOM_STATE_DA_HUY], true);
    $canTeacherReopen = $canManageRoom
        && $phongHocLive->isDangDienRa()
        && ($isInternalRoom || filled($phongHocLive->start_url) || filled($phongHocLive->join_url));
    $isExternalLaunch = filled($playerUrl) && !$playerSupportsEmbed;
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
                        <span class="badge bg-light text-dark">{{ $phongHocLive->thoi_luong_phut }} phut</span>
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
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            @include('components.alert')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Tổng quan phòng học</h5>
                            <p class="text-muted mb-4">{{ $phongHocLive->mo_ta ?: ($baiGiang->mo_ta ?: 'Chưa có mô tả chi tiết cho phòng học này.') }}</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Người điều phối</div>
                                    <div class="fw-bold">{{ $phongHocLive->moderator->ho_ten ?? 'Chưa cập nhật' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Trợ giảng</div>
                                    <div class="fw-bold">{{ $phongHocLive->troGiang->ho_ten ?? 'Không có' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Mở phòng trước</div>
                                    <div class="fw-bold">{{ $phongHocLive->mo_phong_truoc_phut }} phut</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Trạng thái</div>
                                    <div class="fw-bold">{{ $phongHocLive->status_hint }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center gap-2">
                            <h6 class="fw-bold mb-0">Khung live room</h6>
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
                                                Bạn đang điều hành buổi học ngay trong hệ thống. Khu vực này được thiết kế để demo luồng room nội bộ và có thể thay bằng WebRTC/Jitsi sau này.
                                            </p>
                                            <div class="teacher-live-internal__stats">
                                                <div class="teacher-live-internal__stat">
                                                    <span>Phong</span>
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
                                        <div class="badge bg-primary mb-3">{{ strtoupper($platformLabel) }}</div>
                                        <h4 class="fw-bold mb-2">Sẵn sàng mở {{ $platformLabel }}</h4>
                                        <p class="text-muted mb-4">Nền tảng này sẽ mở ở cửa sổ mới. Bấm nút bên phải để vào phòng điều hành.</p>
                                        <a href="{{ $playerUrl }}" target="_blank" class="btn btn-primary btn-lg px-5 fw-bold">
                                            Mở {{ $platformLabel }}
                                        </a>
                                    </div>
                                @else
                                    <div class="teacher-live-placeholder p-5 text-center bg-light">
                                        <i class="fas fa-video-slash fa-4x text-muted opacity-25 mb-3"></i>
                                        <h5 class="fw-bold text-dark">Phòng học chưa mở</h5>
                                        <p class="text-muted mb-0">
                                            @if($canTeacherStart)
                                                Đã tới giờ. Bấm "Bắt đầu buổi học" để mở phòng ngay.
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
                    <div class="card border-0 shadow-sm sticky-top" style="top: 1.5rem;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Hành động</h5>

                            <div class="d-grid gap-3">
                                @php
                                    $lichHocId = $baiGiang->lichHoc?->id;
                                @endphp
                                
                                @if($lichHocId)
                                    <div class="row g-2">
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
                                @elseif($playerMode === 'host' && $isExternalLaunch)
                                    <a href="{{ $playerUrl }}" target="_blank" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                        <i class="fas fa-external-link-alt me-2"></i> Mở {{ $platformLabel }}
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
                                <div class="mt-4 p-3 bg-light rounded border">
                                    <div class="small text-muted text-uppercase mb-2">Thông tin đăng nhập nhanh</div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Meeting ID</span>
                                        <strong>{{ $meetingIdentifier }}</strong>
                                    </div>
                                    @if($meetingPasscode)
                                        <div class="d-flex justify-content-between">
                                            <span>Passcode</span>
                                            <strong>{{ $meetingPasscode }}</strong>
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
            radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 30%),
            linear-gradient(135deg, #1d4ed8, #0f172a);
    }

    .teacher-live-player {
        background: #020617;
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

    .teacher-live-launcher,
    .teacher-live-placeholder {
        min-height: 520px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
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
            countdown.textContent = `Mở phong sau ${formatDuration((openAt - now) / 1000)}`;
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
