@extends('layouts.app')

@section('title', $phongHocLive->tieu_de)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <a href="{{ $backUrl }}" class="btn btn-link ps-0 text-decoration-none">&larr; Quay lai</a>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h2 class="fw-bold mb-2">{{ $phongHocLive->tieu_de }}</h2>
                    <div class="text-muted mb-2">{{ $baiGiang->khoaHoc->ten_khoa_hoc }} / {{ $baiGiang->moduleHoc->ten_module }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-{{ $phongHocLive->timeline_trang_thai_color }}">{{ $phongHocLive->timeline_trang_thai_label }}</span>
                        <span class="badge bg-light text-dark border">{{ $phongHocLive->platform_label }}</span>
                        <span class="badge bg-light text-dark border">{{ $phongHocLive->thoi_luong_phut }} phut</span>
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="small text-muted">Bat dau</div>
                    <div class="fw-semibold">{{ $phongHocLive->thoi_gian_bat_dau->format('d/m/Y H:i') }}</div>
                    <div id="live-room-countdown" class="small text-primary mt-1" data-start-at="{{ $phongHocLive->thoi_gian_bat_dau->toIso8601String() }}" data-open-at="{{ $phongHocLive->join_opens_at->toIso8601String() }}"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @php
                $isExternalLaunch = filled($playerUrl) && !$playerSupportsEmbed;
                $platformPayload = $phongHocLive->du_lieu_nen_tang_json ?? [];
                $meetingIdentifier = $platformPayload['meeting_id'] ?? $platformPayload['meeting_code'] ?? null;
                $meetingPasscode = $platformPayload['passcode'] ?? null;
                $platformLaunchLabel = $phongHocLive->nen_tang_live === 'google_meet' ? 'Google Meet' : $phongHocLive->platform_label;
                $startActionLabel = $playerSupportsEmbed ? 'Bat dau trong trang' : 'Bat dau va mo ' . $platformLaunchLabel;
                $joinManageActionLabel = $playerSupportsEmbed ? 'Mo phong hoc trong trang' : 'Mo ' . $platformLaunchLabel;
                $joinStudentActionLabel = $playerSupportsEmbed ? 'Tham gia trong trang' : 'Tham gia ' . $platformLaunchLabel;
                $platformThemeClass = $phongHocLive->nen_tang_live === 'google_meet' ? 'live-room-launcher--google-meet' : 'live-room-launcher--zoom';
            @endphp

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold">Mo ta</h5>
                            <p class="mb-4">{{ $phongHocLive->mo_ta ?: ($baiGiang->mo_ta ?: 'Chua co mo ta cho phong hoc live.') }}</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted">Moderator</div>
                                    <div class="fw-semibold">{{ $phongHocLive->moderator->ho_ten ?? 'Chua cap nhat' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Tro giang</div>
                                    <div class="fw-semibold">{{ $phongHocLive->troGiang->ho_ten ?? 'Khong co' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Mo phong truoc</div>
                                    <div class="fw-semibold">{{ $phongHocLive->mo_phong_truoc_phut }} phut</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Trang thai phe duyet</div>
                                    <div class="fw-semibold">{{ $phongHocLive->trang_thai_duyet }}</div>
                                </div>
                            </div>

                            <div class="alert alert-{{ $phongHocLive->timeline_trang_thai_color }} mt-4 mb-0">
                                {{ $phongHocLive->status_hint }}
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <strong>Khung phong hoc truc tiep</strong>
                            @if($playerMode === 'host')
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Che do dieu hanh</span>
                            @elseif($playerMode === 'participant')
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Che do tham gia</span>
                            @elseif($isExternalLaunch)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Mo ben ngoai trang</span>
                            @else
                                <span class="badge bg-light text-dark border">Chua mo phong</span>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            <div class="live-room-player-shell mb-3">
                                @if($playerUrl && $playerSupportsEmbed)
                                    <iframe
                                        class="live-room-player-frame"
                                        src="{{ $playerUrl }}"
                                        title="{{ $phongHocLive->tieu_de }}"
                                        allow="camera; microphone; fullscreen; display-capture; autoplay"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen></iframe>
                                @elseif($playerUrl)
                                    <div class="live-room-launcher {{ $platformThemeClass }}">
                                        <div class="live-room-launcher__copy">
                                            <div class="live-room-player-chip">{{ strtoupper($platformLaunchLabel) }}</div>
                                            <h4 class="fw-bold mb-2">{{ $platformLaunchLabel }} se duoc mo tu card nay</h4>
                                            <p class="mb-3 text-white-50">
                                                Nen tang nay khong cho phep nhung truc tiep vao website. Ban van co the bat dau va tham gia buoi hoc nhanh bang cua so nho hoac tab moi.
                                            </p>
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                <button
                                                    type="button"
                                                    class="btn btn-light fw-semibold"
                                                    data-live-room-popup-url="{{ $playerUrl }}"
                                                    data-live-room-popup-name="live-room-{{ $phongHocLive->id }}">
                                                    Mo {{ $platformLaunchLabel }} trong cua so nho
                                                </button>
                                                <a href="{{ $playerUrl }}" target="_blank" rel="noopener" class="btn btn-outline-light fw-semibold">
                                                    Mo trong tab moi
                                                </a>
                                            </div>
                                            <div class="small text-white-50">
                                                Neu trinh duyet chan popup, hay dung nut mo tab moi. Sau khi mo phong, ban van quay lai trang nay de xem mo ta, tai lieu va ban ghi.
                                            </div>
                                        </div>
                                        <div class="live-room-launcher__meta">
                                            <div class="live-room-meta-card">
                                                <div class="small text-uppercase text-white-50 mb-1">Nen tang</div>
                                                <div class="fw-semibold text-white">{{ $platformLaunchLabel }}</div>
                                            </div>
                                            <div class="live-room-meta-card">
                                                <div class="small text-uppercase text-white-50 mb-1">Trang thai</div>
                                                <div class="fw-semibold text-white">{{ $phongHocLive->timeline_trang_thai_label }}</div>
                                            </div>
                                            @if($meetingIdentifier)
                                                <div class="live-room-meta-card">
                                                    <div class="small text-uppercase text-white-50 mb-1">Meeting</div>
                                                    <div class="fw-semibold text-white">{{ $meetingIdentifier }}</div>
                                                </div>
                                            @endif
                                            @if($meetingPasscode)
                                                <div class="live-room-meta-card">
                                                    <div class="small text-uppercase text-white-50 mb-1">Passcode</div>
                                                    <div class="fw-semibold text-white">{{ $meetingPasscode }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="live-room-player-placeholder">
                                        <div class="live-room-player-placeholder__inner">
                                            <div class="live-room-player-chip">LIVE ROOM</div>
                                            <h5 class="fw-bold mb-2">Phong hoc se hien thi tai day</h5>
                                            <p class="mb-0 text-white-50">
                                                Bat dau hoac tham gia buoi hoc o cot ben phai de mo phong hoc. Neu nen tang khong ho tro nhung, he thong se mo bang popup hoac tab moi.
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($playerUrl && $playerSupportsEmbed)
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="small text-muted">
                                        {{ $playerMode === 'host' ? 'Dang hien thi lien ket dieu hanh phong ngay trong trang.' : 'Dang hien thi lien ket tham gia phong ngay trong trang.' }}
                                    </div>
                                    <a href="{{ $playerUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                                        Mo trong tab moi
                                    </a>
                                </div>
                            @elseif($playerUrl)
                                <div class="alert alert-warning mb-0">
                                    {{ $platformLaunchLabel }} da chan iframe tren website, nen he thong da chuyen sang che do preview va mo ngoai trang de giu trai nghiem on dinh.
                                </div>
                            @else
                                <div class="small text-muted">
                                    Khung nay duoc thiet ke nhu mot player trung tam. Khi moderator bat dau hoac hoc vien tham gia, noi dung se hien thi tai day hoac mo bang popup/tab moi tuy theo nen tang.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white"><strong>Tai lieu dinh kem</strong></div>
                        <div class="card-body p-4">
                            @if($baiGiang->taiNguyenChinh)
                                <div class="mb-3">
                                    <div class="fw-semibold">{{ $baiGiang->taiNguyenChinh->tieu_de }}</div>
                                    <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" rel="noopener" class="small">Mo tai nguyen chinh</a>
                                </div>
                            @endif
                            @if($baiGiang->taiNguyenPhu->isNotEmpty())
                                <div class="row g-2">
                                    @foreach($baiGiang->taiNguyenPhu as $taiNguyen)
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="fw-semibold">{{ $taiNguyen->tieu_de }}</div>
                                                <div class="small text-muted mb-2">{{ $taiNguyen->loai_label }}</div>
                                                <a href="{{ $taiNguyen->file_url }}" target="_blank" rel="noopener" class="small">Mo tai nguyen</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(!$baiGiang->taiNguyenChinh)
                                <div class="text-muted small">Chua co tai lieu dinh kem.</div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <strong>Ban ghi</strong>
                            <span class="badge bg-light text-dark border">{{ $phongHocLive->banGhis->count() }}</span>
                        </div>
                        <div class="card-body p-4">
                            @forelse($phongHocLive->banGhis as $recording)
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $recording->tieu_de }}</div>
                                            <div class="small text-muted">{{ $recording->nguon_ban_ghi }} @if($recording->thoi_luong) / {{ $recording->thoi_luong }} giay @endif</div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($recording->link_ngoai)
                                                <a href="{{ $recording->link_ngoai }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Xem</a>
                                            @elseif($recording->duong_dan_file)
                                                <a href="{{ asset('storage/' . $recording->duong_dan_file) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Xem</a>
                                            @endif
                                            @if($mode === 'teacher' && $canManageRoom)
                                                <form action="{{ route('giang-vien.live-room.recordings.destroy', [$baiGiang->id, $recording->id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xoa</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small">Chua co ban ghi cho buoi hoc nay.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold">Thao tac</h5>

                            @if($mode === 'teacher' && $canManageRoom)
                                <div class="d-grid gap-2">
                                    @if($phongHocLive->can_moderator_start)
                                        <form action="{{ route('giang-vien.live-room.start', $baiGiang->id) }}" method="POST">@csrf <button class="btn btn-success w-100">{{ $startActionLabel }}</button></form>
                                    @endif
                                    <form action="{{ route('giang-vien.live-room.join', $baiGiang->id) }}" method="POST">@csrf <button class="btn btn-primary w-100">{{ $joinManageActionLabel }}</button></form>
                                    <form action="{{ route('giang-vien.live-room.leave', $baiGiang->id) }}" method="POST">@csrf <button class="btn btn-outline-secondary w-100">Danh dau roi phong</button></form>
                                    <form action="{{ route('giang-vien.live-room.end', $baiGiang->id) }}" method="POST">@csrf <button class="btn btn-outline-danger w-100">Ket thuc buoi hoc</button></form>
                                </div>
                            @endif

                            @if($mode === 'student')
                                <div class="d-grid gap-2">
                                    @if($canJoinRoom)
                                        <form action="{{ route('hoc-vien.live-room.join', $baiGiang->id) }}" method="POST">@csrf <button class="btn btn-primary w-100">{{ $joinStudentActionLabel }}</button></form>
                                    @else
                                        <button class="btn btn-secondary w-100" disabled>Chua the tham gia</button>
                                    @endif
                                    <form action="{{ route('hoc-vien.live-room.leave', $baiGiang->id) }}" method="POST">@csrf <button class="btn btn-outline-secondary w-100">Danh dau roi phong</button></form>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($mode === 'teacher' && $canManageRoom)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold">Them ban ghi</h5>
                                <form action="{{ route('giang-vien.live-room.recordings.store', $baiGiang->id) }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                                    @csrf
                                    <select name="nguon_ban_ghi" class="form-select">
                                        <option value="zoom">Zoom</option>
                                        <option value="google_meet">Google Meet</option>
                                        <option value="upload">Upload</option>
                                    </select>
                                    <input type="text" name="tieu_de" class="form-control" placeholder="Tieu de ban ghi" required>
                                    <input type="url" name="link_ngoai" class="form-control" placeholder="Link ngoai (neu co)">
                                    <input type="file" name="file_ban_ghi" class="form-control">
                                    <input type="number" name="thoi_luong" class="form-control" placeholder="Thoi luong (giay)">
                                    <button type="submit" class="btn btn-outline-primary">Luu ban ghi</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($mode === 'teacher')
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="fw-bold">Nguoi tham gia</h5>
                                @forelse($phongHocLive->nguoiThamGia as $participant)
                                    <div class="border rounded p-2 mb-2">
                                        <div class="fw-semibold">{{ $participant->nguoiDung->ho_ten ?? ('User #' . $participant->nguoi_dung_id) }}</div>
                                        <div class="small text-muted">{{ $participant->vai_tro }} / {{ $participant->trang_thai }}</div>
                                    </div>
                                @empty
                                    <div class="text-muted small">Chua co log tham gia.</div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.live-room-player-shell {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    border-radius: 1rem;
    overflow: hidden;
    background:
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 35%),
        linear-gradient(135deg, #0f172a 0%, #111827 48%, #1d4ed8 100%);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
}

.live-room-player-frame {
    width: 100%;
    height: 100%;
    border: 0;
    background: #000;
}

.live-room-launcher {
    width: 100%;
    height: 100%;
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(240px, 0.9fr);
    gap: 1rem;
    padding: 1.5rem;
    color: #fff;
}

.live-room-launcher--google-meet {
    background:
        radial-gradient(circle at top right, rgba(52, 168, 83, 0.18), transparent 30%),
        linear-gradient(140deg, #0f172a 0%, #102542 38%, #1a73e8 100%);
}

.live-room-launcher--zoom {
    background:
        radial-gradient(circle at top right, rgba(96, 165, 250, 0.22), transparent 30%),
        linear-gradient(140deg, #0f172a 0%, #111827 42%, #2563eb 100%);
}

.live-room-launcher__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.live-room-launcher__meta {
    display: grid;
    gap: 0.75rem;
    align-content: center;
}

.live-room-meta-card {
    padding: 0.9rem 1rem;
    border-radius: 0.85rem;
    background: rgba(15, 23, 42, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
}

.live-room-player-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: #fff;
    text-align: center;
}

.live-room-player-placeholder__inner {
    max-width: 34rem;
}

.live-room-player-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.7rem;
    margin-bottom: 1rem;
    border-radius: 999px;
    background: rgba(239, 68, 68, 0.18);
    color: #fecaca;
    border: 1px solid rgba(248, 113, 113, 0.35);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
}

@media (max-width: 991.98px) {
    .live-room-launcher {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const countdownEl = document.getElementById('live-room-countdown');
    if (countdownEl) {
        const startAt = new Date(countdownEl.dataset.startAt);
        const openAt = new Date(countdownEl.dataset.openAt);

        function renderCountdown() {
            const now = new Date();
            const target = now < openAt ? openAt : startAt;
            const diff = target - now;

            if (diff <= 0) {
                countdownEl.textContent = 'Phong hoc da toi moc mo / bat dau.';
                return;
            }

            const totalMinutes = Math.floor(diff / 60000);
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            countdownEl.textContent = now < openAt
                ? `Mo phong sau ${hours}h ${minutes}m`
                : `Bat dau sau ${hours}h ${minutes}m`;
        }

        renderCountdown();
        setInterval(renderCountdown, 60000);
    }

    document.querySelectorAll('[data-live-room-popup-url]').forEach(function (button) {
        button.addEventListener('click', function () {
            const popupUrl = button.dataset.liveRoomPopupUrl;
            const popupName = button.dataset.liveRoomPopupName || 'live-room-window';
            const width = Math.min(window.screen.availWidth - 80, 1400);
            const height = Math.min(window.screen.availHeight - 80, 900);
            const left = Math.max(0, Math.floor((window.screen.availWidth - width) / 2));
            const top = Math.max(0, Math.floor((window.screen.availHeight - height) / 2));
            const features = `popup=yes,width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`;
            const popup = window.open(popupUrl, popupName, features);

            if (popup) {
                popup.focus();
                return;
            }

            window.alert('Trinh duyet da chan popup. Vui long dung nut mo trong tab moi.');
        });
    });
});
</script>
@endpush
