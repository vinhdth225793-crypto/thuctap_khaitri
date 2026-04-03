@extends('layouts.app', ['title' => 'Phong hoc live'])

@section('content')
@php
    $timelineStatus = $phongHocLive->timeline_trang_thai;
    $showRoute = route('giang-vien.live-room.show', $phongHocLive->id);
    $hostViewRoute = route('giang-vien.live-room.show', ['id' => $phongHocLive->id, 'player' => 'host']);
    $startRoute = route('giang-vien.live-room.start', $phongHocLive->id);
    $leaveRoute = route('giang-vien.live-room.leave', $phongHocLive->id);
    $endRoute = route('giang-vien.live-room.end', $phongHocLive->id);
    $platformPayload = $phongHocLive->du_lieu_nen_tang_json ?? [];
    $meetingIdentifier = $platformPayload['meeting_id'] ?? $platformPayload['meeting_code'] ?? null;
    $meetingPasscode = $platformPayload['passcode'] ?? null;
    $platformLabel = $phongHocLive->nen_tang_live === 'google_meet' ? 'Google Meet' : $phongHocLive->platform_label;
    $canTeacherStart = $canManageRoom
        && filled($phongHocLive->start_url)
        && !in_array($timelineStatus, [\App\Models\PhongHocLive::ROOM_STATE_DANG_DIEN_RA, \App\Models\PhongHocLive::ROOM_STATE_DA_KET_THUC, \App\Models\PhongHocLive::ROOM_STATE_DA_HUY], true);
    $canTeacherReopen = $canManageRoom
        && $phongHocLive->isDangDienRa()
        && filled($phongHocLive->start_url);
    $isExternalLaunch = filled($playerUrl) && !$playerSupportsEmbed;
@endphp

<div class="container-fluid">
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden teacher-live-hero">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="small text-white-50 text-uppercase mb-2">Phong dieu hanh giang vien</div>
                    <h2 class="fw-bold mb-2 text-white">{{ $phongHocLive->tieu_de }}</h2>
                    <div class="text-white-50 mb-3">{{ $baiGiang->khoaHoc->ten_khoa_hoc }} / {{ $baiGiang->moduleHoc->ten_module }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-{{ $phongHocLive->timeline_trang_thai_color }}">{{ $phongHocLive->timeline_trang_thai_label }}</span>
                        <span class="badge bg-light text-dark">{{ $platformLabel }}</span>
                        <span class="badge bg-light text-dark">{{ $phongHocLive->thoi_luong_phut }} phut</span>
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="small text-white-50">Bat dau luc</div>
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
                            <h5 class="fw-bold mb-3">Tong quan phong hoc</h5>
                            <p class="text-muted mb-4">{{ $phongHocLive->mo_ta ?: ($baiGiang->mo_ta ?: 'Chua co mo ta chi tiet cho phong hoc nay.') }}</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Moderator</div>
                                    <div class="fw-bold">{{ $phongHocLive->moderator->ho_ten ?? 'Chua cap nhat' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Tro giang</div>
                                    <div class="fw-bold">{{ $phongHocLive->troGiang->ho_ten ?? 'Khong co' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Mo phong truoc</div>
                                    <div class="fw-bold">{{ $phongHocLive->mo_phong_truoc_phut }} phut</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase">Trang thai</div>
                                    <div class="fw-bold">{{ $phongHocLive->status_hint }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center gap-2">
                            <h6 class="fw-bold mb-0">Khung live room</h6>
                            @if($playerMode === 'host')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Dang dieu hanh</span>
                            @elseif($canTeacherStart)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">San sang bat dau</span>
                            @elseif($canTeacherReopen)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Co the mo lai</span>
                            @else
                                <span class="badge bg-light text-dark border">Chua mo</span>
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
                                @elseif($playerMode === 'host' && $playerUrl)
                                    <div class="teacher-live-launcher text-center p-5">
                                        <div class="badge bg-primary mb-3">{{ strtoupper($platformLabel) }}</div>
                                        <h4 class="fw-bold mb-2">San sang mo {{ $platformLabel }}</h4>
                                        <p class="text-muted mb-4">Nen tang nay se mo o cua so moi. Bam nut ben phai de vao phong dieu hanh.</p>
                                        <a href="{{ $playerUrl }}" target="_blank" class="btn btn-primary btn-lg px-5 fw-bold">
                                            Mo {{ $platformLabel }}
                                        </a>
                                    </div>
                                @else
                                    <div class="teacher-live-placeholder p-5 text-center bg-light">
                                        <i class="fas fa-video-slash fa-4x text-muted opacity-25 mb-3"></i>
                                        <h5 class="fw-bold text-dark">Phong hoc chua mo</h5>
                                        <p class="text-muted mb-0">
                                            @if($canTeacherStart)
                                                Da toi gio. Bam "Bat dau buoi hoc" de mo phong ngay.
                                            @elseif($canTeacherReopen)
                                                Buoi hoc dang dien ra. Bam "Mo phong dieu hanh" de quay lai phong.
                                            @else
                                                He thong dang cho den moc mo phong hoac cho moderator bat dau.
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
                            <h5 class="fw-bold mb-4">Hanh dong</h5>

                            <div class="d-grid gap-3">
                                @php
                                    $lichHocId = $baiGiang->lichHoc?->id;
                                @endphp
                                
                                @if($lichHocId)
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <a href="{{ route('giang-vien.buoi-hoc.diem-danh.show', $lichHocId) }}" class="btn btn-outline-info w-100 py-2 fw-bold">
                                                <i class="fas fa-user-check d-block mb-1"></i> Điểm danh
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="{{ route('giang-vien.khoa-hoc.show', ['id' => $baiGiang->khoaHoc->id, 'focus_lich_hoc_id' => $lichHocId]) }}#session-{{ $lichHocId }}" class="btn btn-outline-info w-100 py-2 fw-bold">
                                                <i class="fas fa-folder-open d-block mb-1"></i> Tài nguyên
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if($canTeacherStart)
                                    <form action="{{ $startRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                            <i class="fas fa-play-circle me-2"></i> Bat dau buoi hoc
                                        </button>
                                    </form>
                                @elseif($canTeacherReopen && $playerMode !== 'host')
                                    <a href="{{ $hostViewRoute }}" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                        <i class="fas fa-video me-2"></i> Mo phong dieu hanh
                                    </a>
                                @elseif($playerMode === 'host' && $isExternalLaunch)
                                    <a href="{{ $playerUrl }}" target="_blank" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                        <i class="fas fa-external-link-alt me-2"></i> Mo {{ $platformLabel }}
                                    </a>
                                @endif

                                <a href="{{ $showRoute }}" class="btn btn-outline-secondary w-100 fw-bold">
                                    <i class="fas fa-sync-alt me-2"></i> Lam moi trang phong
                                </a>

                                @if($playerMode === 'host')
                                    <form action="{{ $leaveRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary w-100 fw-bold">
                                            <i class="fas fa-sign-out-alt me-2"></i> Roi che do dieu hanh
                                        </button>
                                    </form>
                                @endif

                                @if($phongHocLive->isDangDienRa())
                                    <form action="{{ $endRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger w-100 fw-bold">
                                            <i class="fas fa-stop-circle me-2"></i> Ket thuc buoi hoc
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('giang-vien.bai-giang.index') }}" class="btn btn-link text-decoration-none">
                                    Quay lai danh sach bai giang
                                </a>
                            </div>

                            @if($meetingIdentifier)
                                <div class="mt-4 p-3 bg-light rounded border">
                                    <div class="small text-muted text-uppercase mb-2">Thong tin dang nhap nhanh</div>
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

    .teacher-live-launcher,
    .teacher-live-placeholder {
        min-height: 520px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
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
            countdown.textContent = `Mo phong sau ${formatDuration((openAt - now) / 1000)}`;
        } else if (now < startAt) {
            countdown.textContent = `Den gio bat dau sau ${formatDuration((startAt - now) / 1000)}`;
        } else {
            countdown.textContent = 'Da toi gio hoc.';
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
