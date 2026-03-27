@extends('layouts.app', ['title' => 'Phòng học trực tuyến'])

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white overflow-hidden" style="border-radius: 1.5rem;">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h2 class="fw-bold mb-2 text-white">{{ $phongHocLive->tieu_de }}</h2>
                    <div class="text-white-50 mb-2">{{ $baiGiang->khoaHoc->ten_khoa_hoc }} / {{ $baiGiang->moduleHoc->ten_module }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-{{ $phongHocLive->timeline_trang_thai_color }} shadow-sm">{{ $phongHocLive->timeline_trang_thai_label }}</span>
                        <span class="badge bg-white text-dark border-0 shadow-sm">{{ $phongHocLive->platform_label }}</span>
                        <span class="badge bg-white text-dark border-0 shadow-sm">{{ $phongHocLive->thoi_luong_phut }} phút</span>
                    </div>
                </div>
                <div class="text-md-end text-white">
                    <div class="small text-white-50">Bắt đầu lúc</div>
                    <div class="fw-bold fs-5">{{ $phongHocLive->thoi_gian_bat_dau->format('d/m/Y H:i') }}</div>
                    <div id="live-room-countdown" class="small text-white mt-1 fw-bold" data-start-at="{{ $phongHocLive->thoi_gian_bat_dau->toIso8601String() }}" data-open-at="{{ $phongHocLive->join_opens_at->toIso8601String() }}"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
            @endif

            @php
                $isExternalLaunch = filled($playerUrl) && !$playerSupportsEmbed;
                $platformPayload = $phongHocLive->du_lieu_nen_tang_json ?? [];
                $meetingIdentifier = $platformPayload['meeting_id'] ?? $platformPayload['meeting_code'] ?? null;
                $meetingPasscode = $platformPayload['passcode'] ?? null;
                $platformLaunchLabel = $phongHocLive->nen_tang_live === 'google_meet' ? 'Google Meet' : $phongHocLive->platform_label;
                $startActionLabel = $playerSupportsEmbed ? 'Bắt đầu trong trình duyệt' : 'Bắt đầu và mở ' . $platformLaunchLabel;
                $joinManageActionLabel = $playerSupportsEmbed ? 'Mở phòng học trực tiếp' : 'Mở ' . $platformLaunchLabel;
                $joinStudentActionLabel = $playerSupportsEmbed ? 'Tham gia trực tiếp' : 'Tham gia ' . $platformLaunchLabel;
                $platformThemeClass = $phongHocLive->nen_tang_live === 'google_meet' ? 'live-room-launcher--google-meet' : 'live-room-launcher--zoom';
            @endphp

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4 text-dark">
                            <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Mô tả phòng học</h5>
                            <p class="mb-4">{{ $phongHocLive->mo_ta ?: ($baiGiang->mo_ta ?: 'Chưa có mô tả chi tiết cho phòng học này.') }}</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted fw-bold text-uppercase smaller">Người chủ trì (Moderator)</div>
                                    <div class="fw-bold text-dark">{{ $phongHocLive->moderator->ho_ten ?? 'Chưa cập nhật' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted fw-bold text-uppercase smaller">Trợ giảng</div>
                                    <div class="fw-bold text-dark">{{ $phongHocLive->troGiang->ho_ten ?? 'Không có trợ giảng' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted fw-bold text-uppercase smaller">Mở phòng trước</div>
                                    <div class="fw-bold text-dark">{{ $phongHocLive->mo_phong_truoc_phut }} phút</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted fw-bold text-uppercase smaller">Trạng thái phê duyệt</div>
                                    <div class="fw-bold">
                                        @if($phongHocLive->trang_thai_duyet === 'da_duyet')
                                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Đã duyệt</span>
                                        @else
                                            <span class="text-warning"><i class="fas fa-clock me-1"></i>Chờ duyệt</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-{{ $phongHocLive->timeline_trang_thai_color }} mt-4 mb-0 border-0 shadow-xs">
                                <i class="fas fa-lightbulb me-2"></i> {{ $phongHocLive->status_hint }}
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h6 class="fw-bold mb-0">Khung học trực tuyến</h6>
                            @if($playerMode === 'host')
                                <span class="badge bg-success-soft text-success border border-success px-3">Chế độ điều hành</span>
                            @elseif($playerMode === 'participant')
                                <span class="badge bg-primary-soft text-primary border border-primary px-3">Chế độ tham gia</span>
                            @elseif($isExternalLaunch)
                                <span class="badge bg-warning-soft text-warning border border-warning px-3">Mở bên ngoài trang</span>
                            @else
                                <span class="badge bg-light text-dark border px-3">Phòng chưa mở</span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="live-room-player-shell">
                                @if($playerUrl && $playerSupportsEmbed)
                                    <iframe
                                        class="live-room-player-frame"
                                        src="{{ $playerUrl }}"
                                        title="{{ $phongHocLive->tieu_de }}"
                                        allow="camera; microphone; fullscreen; display-capture; autoplay"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen
                                        style="width: 100%; height: 600px; border: 0;"></iframe>
                                @elseif($playerUrl)
                                    <div class="live-room-launcher {{ $platformThemeClass }} p-5 text-center bg-dark text-white">
                                        <div class="live-room-launcher__copy mb-4">
                                            <div class="live-room-player-chip badge bg-primary mb-3">{{ strtoupper($platformLaunchLabel) }}</div>
                                            <h4 class="fw-bold mb-2">{{ $platformLaunchLabel }} sẽ được mở trong cửa sổ mới</h4>
                                            <p class="mb-3 text-white-50">
                                                Nền tảng này không hỗ trợ học trực tiếp trong website. Vui lòng bấm nút bên dưới để bắt đầu.
                                            </p>
                                        </div>
                                        <a href="{{ $playerUrl }}" target="_blank" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                                            <i class="fas fa-external-link-alt me-2"></i> MỞ {{ strtoupper($platformLaunchLabel) }} NGAY
                                        </a>
                                    </div>
                                @else
                                    <div class="p-5 text-center bg-light">
                                        <i class="fas fa-video-slash fa-4x text-muted mb-4 opacity-25"></i>
                                        <h5 class="fw-bold text-muted">Phòng học chưa bắt đầu</h5>
                                        <p class="text-muted mb-0">Vui lòng quay lại khi đến giờ học hoặc được người quản trị cho phép.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 1.5rem;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Hành động của bạn</h5>

                            <div class="d-grid gap-3">
                                @if($playerMode === 'host')
                                    <form action="{{ route('live-room.launch', $phongHocLive->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                            <i class="fas fa-play-circle me-2"></i> {{ $startActionLabel }}
                                        </button>
                                    </form>
                                @elseif($playerMode === 'participant')
                                    <form action="{{ route('live-room.launch', $phongHocLive->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                            <i class="fas fa-door-open me-2"></i> {{ $joinStudentActionLabel }}
                                        </button>
                                    </form>
                                @elseif($isExternalLaunch)
                                    <a href="{{ $playerUrl }}" target="_blank" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                        <i class="fas fa-external-link-alt me-2"></i> {{ $joinManageActionLabel }}
                                    </a>
                                @endif

                                @if($playerUrl)
                                    <a href="{{ route('live-room.show', $phongHocLive->id) }}" class="btn btn-outline-secondary w-100 fw-bold">
                                        <i class="fas fa-sync-alt me-2"></i> LÀM MỚI PHÒNG
                                    </a>
                                @endif

                                @if(Auth::user()->is_admin)
                                    <hr>
                                    <a href="{{ route('admin.bai-giang.edit', $baiGiang->id) }}" class="btn btn-outline-dark w-100 fw-bold">
                                        <i class="fas fa-edit me-2"></i> CẤU HÌNH PHÒNG (ADMIN)
                                    </a>
                                @endif
                            </div>

                            @if($meetingIdentifier)
                                <div class="mt-4 p-3 bg-light rounded shadow-xs border text-dark">
                                    <div class="small text-muted fw-bold text-uppercase smaller mb-2">Thông tin đăng nhập trực tiếp</div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small">Meeting ID:</span>
                                        <span class="fw-bold text-primary">{{ $meetingIdentifier }}</span>
                                    </div>
                                    @if($meetingPasscode)
                                        <div class="d-flex justify-content-between">
                                            <span class="small">Passcode:</span>
                                            <span class="fw-bold text-primary">{{ $meetingPasscode }}</span>
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
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .smaller { font-size: 0.75rem; letter-spacing: 0.05rem; }
    .live-room-player-shell { position: relative; background: #000; border-radius: 0.5rem; overflow: hidden; }
</style>
@endsection
