@extends('layouts.app', ['title' => 'Chi tiết buổi học'])

@section('content')
@php
    $liveRoom = $liveLecture?->phongHocLive;
    $legacyOnlineUrl = \App\Support\OnlineMeetingUrl::normalize($lichHoc->link_online);
    $liveRoomJoinUrl = \App\Support\OnlineMeetingUrl::normalize($liveRoom?->join_url ?: $liveRoom?->start_url);
    $externalOnlineUrl = $legacyOnlineUrl ?: $liveRoomJoinUrl;
    $platformSignal = strtolower(trim(($lichHoc->nen_tang_label ?? '') . ' ' . ($lichHoc->nen_tang ?? '') . ' ' . ($externalOnlineUrl ?? '')));
    $isGoogleMeet = str_contains($platformSignal, 'google') || str_contains($platformSignal, 'meet.google.com') || str_contains($platformSignal, 'meet');
    $onlinePlatformLabel = $isGoogleMeet ? 'Google Meet' : ($lichHoc->nen_tang_label ?: 'Phòng học online');
    $canJoinLiveRoom = $liveRoom && $liveRoom->can_student_join;
    $liveRoomUrl = $liveLecture ? route('hoc-vien.live-room.show', $liveLecture->id) : null;
    $isSessionEnded = $lichHoc->is_ended;
    $canOpenExternalOnlineUrl = $externalOnlineUrl && ! $isSessionEnded;
    $meetingIdentifier = $lichHoc->meeting_id;
    $meetingPasscode = $lichHoc->mat_khau_cuoc_hop;

    if (!$meetingIdentifier && $isGoogleMeet && $externalOnlineUrl) {
        $meetingIdentifier = \App\Support\OnlineMeetingUrl::meetingCode($externalOnlineUrl);
    }

    $primaryLecture = $relatedLectures->first(fn ($lecture) => !$lecture->isLive()) ?: $relatedLectures->first();
    $sessionTopic = $primaryLecture?->tieu_de
        ?: ($liveLecture?->tieu_de ?: 'Nội dung buổi ' . ($lichHoc->buoi_so ?: '#'));
    $sessionSummary = $primaryLecture?->mo_ta
        ?: ($lichHoc->ghi_chu ?: 'Giảng viên sẽ cập nhật nội dung chi tiết, tài nguyên và bài tập liên quan cho buổi học này.');
    $joinOpenAt = $lichHoc->starts_at?->copy()->subMinutes(\App\Models\LichHoc::ONLINE_JOIN_EARLY_MINUTES);
    $scheduleTimeLabel = $lichHoc->schedule_range_label ?: (
        ($lichHoc->ngay_hoc?->format('d/m/Y') ?: 'Chưa có ngày học')
        . ' • '
        . (substr((string) $lichHoc->gio_bat_dau, 0, 5) ?: '--:--')
        . ($lichHoc->gio_ket_thuc ? ' - ' . substr((string) $lichHoc->gio_ket_thuc, 0, 5) : '')
    );
@endphp

<div class="container-fluid admin-page-x sshv-page student-session-page">
    {{-- Welcome banner xanh dương --}}
    <div class="apx-welcome sshv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="apx-welcome-text">
            <div class="sshv-tag-row">
                <span class="sshv-loai-badge"><i class="fas fa-list-check"></i> BUỔI {{ $lichHoc->buoi_so ?: '#' }}</span>
                <span class="sshv-status-pill bg-{{ $lichHoc->trang_thai_color }}">
                    <i class="fas fa-circle"></i> {{ $lichHoc->trang_thai_label }}
                </span>
                <span class="sshv-status-pill bg-{{ $lichHoc->hinh_thuc_color }}">
                    <i class="fas {{ $lichHoc->hinh_thuc === 'online' ? 'fa-video' : 'fa-chalkboard' }}"></i>
                    {{ $lichHoc->hinh_thuc_label }}
                </span>
                <span class="sshv-status-badge"><i class="fas fa-cube"></i> {{ $lichHoc->moduleHoc->ten_module ?? 'Chưa gán module' }}</span>
            </div>
            <h4>{{ $sessionTopic }}</h4>
            <p>
                <span><i class="fas fa-calendar-alt"></i> {{ $scheduleTimeLabel }}</span>
                <span class="sshv-sep">·</span>
                <span><i class="fas fa-chalkboard-teacher"></i> {{ $lichHoc->giangVien?->nguoiDung?->ho_ten ?? 'Chưa phân công GV' }}</span>
                <span class="sshv-sep">·</span>
                <span><i class="fas fa-graduation-cap"></i> {{ $lichHoc->khoaHoc->ten_khoa_hoc }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $lichHoc->khoa_hoc_id) }}#lich-hoc" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về lịch học</span>
            </a>
            @if($liveRoomUrl)
                <a href="{{ $liveRoomUrl }}" class="apx-view-toggle">
                    <i class="fas fa-video"></i> <span>Xem live room</span>
                </a>
            @endif
            @if($canOpenExternalOnlineUrl)
                <a href="{{ $externalOnlineUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-light text-primary fw-bold shadow-sm sshv-launch-btn">
                    <i class="fas fa-external-link-alt me-1"></i> Mở {{ $onlinePlatformLabel }}
                </a>
            @endif
        </div>
    </div>

    {{-- Breadcrumb đỏ --}}
    <nav aria-label="breadcrumb" class="sshv-breadcrumb mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}">Khóa học của tôi</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $lichHoc->khoa_hoc_id) }}">{{ $lichHoc->khoaHoc->ten_khoa_hoc }}</a></li>
            <li class="breadcrumb-item active">Buổi {{ $lichHoc->buoi_so ?: '#' }}</li>
        </ol>
    </nav>

    @include('components.alert')

    {{-- ======= Banner "Buổi học đã kết thúc" ======= --}}
    @if($isSessionEnded)
        @php
            $myAttendance = auth()->user()?->ma_nguoi_dung
                ? \App\Models\DiemDanh::where('lich_hoc_id', $lichHoc->id)
                    ->where('hoc_vien_id', auth()->user()->ma_nguoi_dung)
                    ->first()
                : null;
            $attMap = [
                'co_mat'   => ['label' => 'Có mặt',   'icon' => 'fa-circle-check', 'color' => '#10b981'],
                'vao_tre'  => ['label' => 'Vào trễ',  'icon' => 'fa-clock',         'color' => '#f59e0b'],
                'vang_mat' => ['label' => 'Vắng mặt', 'icon' => 'fa-circle-xmark',  'color' => '#ef4444'],
                'co_phep'  => ['label' => 'Có phép',  'icon' => 'fa-circle-info',   'color' => '#0ea5e9'],
            ];
            $myAtt = $myAttendance ? ($attMap[$myAttendance->trang_thai] ?? null) : null;
            $endedAtSession = $lichHoc->ends_at;
        @endphp
        <div class="session-ended-banner">
            <div class="session-ended-banner__icon">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div class="session-ended-banner__main">
                <div class="session-ended-banner__title">Buổi học đã kết thúc</div>
                <div class="session-ended-banner__msg">
                    Buổi {{ $lichHoc->buoi_so ?: '#' }}
                    @if($endedAtSession)
                        đã kết thúc lúc <strong>{{ $endedAtSession->format('H:i · d/m/Y') }}</strong>.
                    @else
                        đã hoàn tất.
                    @endif
                    Bạn có thể xem lại bản ghi (nếu có), tài liệu và bài tập của buổi này.
                </div>
                @if($myAtt)
                    <div class="session-ended-banner__att">
                        Trạng thái điểm danh của bạn:
                        <span class="session-ended-banner__pill" style="background: {{ $myAtt['color'] }};">
                            <i class="fas {{ $myAtt['icon'] }}"></i> {{ $myAtt['label'] }}
                        </span>
                    </div>
                @elseif(auth()->user()?->ma_nguoi_dung)
                    <div class="session-ended-banner__att">
                        <i class="fas fa-circle-question"></i> Giảng viên chưa cập nhật điểm danh cho bạn ở buổi này.
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Quick stats --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-primary">
                <div class="aps-icon"><i class="fas fa-chalkboard"></i></div>
                <div class="aps-text"><strong>{{ $relatedLectures->count() }}</strong><small>Bài giảng</small></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-success">
                <div class="aps-icon"><i class="fas fa-folder-open"></i></div>
                <div class="aps-text"><strong>{{ $lichHoc->taiNguyen->count() }}</strong><small>Tài nguyên</small></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-warning">
                <div class="aps-icon"><i class="fas fa-clipboard-check"></i></div>
                <div class="aps-text"><strong>{{ $relatedExams->count() }}</strong><small>Bài kiểm tra</small></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-info">
                <div class="aps-icon"><i class="fas fa-clock"></i></div>
                <div class="aps-text">
                    <strong>{{ substr((string) $lichHoc->gio_bat_dau, 0, 5) ?: '--:--' }}</strong>
                    <small>{{ $lichHoc->ngay_hoc?->format('d/m') ?? '—' }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Session shell — đồng bộ style với trang giảng viên --}}
    <div class="session-block sshv-session">
        <div class="session-shell shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="session-shell__header bg-white py-3 px-4 border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="session-shell__number" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color:#fff; border:0;">
                            {{ $lichHoc->buoi_so ?: '#' }}
                        </div>
                        <div>
                            <h6 class="session-shell__title fw-bold mb-0 text-dark">
                                Buổi {{ $lichHoc->buoi_so ?: '#' }}: {{ $lichHoc->ngay_hoc?->format('d/m/Y') ?? '—' }}
                                @if($lichHoc->ngay_hoc)
                                    <span class="text-muted fw-normal">({{ $lichHoc->ngay_hoc->isoFormat('dddd') }})</span>
                                @endif
                            </h6>
                            <div class="smaller text-muted d-flex align-items-center gap-2 mt-1 flex-wrap">
                                <span><i class="far fa-clock me-1 text-danger"></i>{{ substr((string) $lichHoc->gio_bat_dau, 0, 5) ?: '--:--' }}{{ $lichHoc->gio_ket_thuc ? ' - ' . substr((string) $lichHoc->gio_ket_thuc, 0, 5) : '' }}</span>
                                <span class="text-silver">|</span>
                                <span class="badge bg-{{ $lichHoc->hinh_thuc_color }}-soft text-{{ $lichHoc->hinh_thuc_color }} border-0">
                                    <i class="fas {{ $lichHoc->hinh_thuc === 'online' ? 'fa-video' : 'fa-chalkboard' }} me-1"></i>{{ $lichHoc->hinh_thuc_label }}
                                </span>
                                <span class="text-silver">|</span>
                                <span class="badge bg-{{ $lichHoc->trang_thai_color }}-soft text-{{ $lichHoc->trang_thai_color }} border-0">{{ $lichHoc->trang_thai_label }}</span>
                                <span class="text-silver">|</span>
                                <span><i class="fas fa-cube me-1 text-info"></i>{{ $lichHoc->moduleHoc->ten_module ?? 'Chưa gán module' }}</span>
                                <span class="text-silver">|</span>
                                <span><i class="fas fa-chalkboard-teacher me-1 text-primary"></i>{{ $lichHoc->giangVien?->nguoiDung?->ho_ten ?? 'Chưa phân công' }}</span>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#sshv-body">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div id="sshv-body" class="collapse show">
                <div class="session-shell__body p-3 p-lg-4 bg-white">

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm student-online-card mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <div class="student-session-eyebrow text-primary">Lớp học trực tuyến</div>
                            <h3 class="fw-bold mb-2">Tham gia lớp học online</h3>
                            <p class="text-muted mb-0">{{ $lichHoc->hinh_thuc === 'online' ? $lichHoc->online_join_message : 'Buổi học này diễn ra trực tiếp tại lớp.' }}</p>
                        </div>
                        <span class="badge rounded-pill bg-{{ $lichHoc->online_join_state_color }} px-3 py-2">{{ $lichHoc->online_join_state_label }}</span>
                    </div>

                    @if($lichHoc->hinh_thuc === 'online')
                        <div class="online-join-panel">
                            <div class="online-join-panel__main">
                                <div class="online-platform-orb">
                                    <i class="fas fa-video"></i>
                                </div>
                                <div>
                                    <div class="small text-uppercase fw-bold text-muted mb-1">Nền tảng</div>
                                    <h4 class="fw-bold mb-1">{{ $onlinePlatformLabel }}</h4>
                                    <div class="text-muted small">
                                        @if($joinOpenAt)
                                            Phòng học mở cho học viên từ {{ $joinOpenAt->format('H:i d/m/Y') }}.
                                        @else
                                            Hệ thống sẽ hiển thị trạng thái vào lớp theo lịch của giảng viên.
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="online-join-panel__actions">
                                @if($canJoinLiveRoom)
                                    <form action="{{ route('hoc-vien.live-room.join', $liveLecture->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                                            <i class="fas fa-door-open me-2"></i>Tham gia live room
                                        </button>
                                    </form>
                                @elseif($liveRoomUrl)
                                    <a href="{{ $liveRoomUrl }}" class="btn btn-outline-primary btn-lg w-100 fw-bold">
                                        <i class="fas fa-video me-2"></i>Xem phòng live
                                    </a>
                                @endif

                                @if($canOpenExternalOnlineUrl)
                                    <a href="{{ $externalOnlineUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg w-100 fw-bold">
                                        <i class="fas fa-external-link-alt me-2"></i>Mở {{ $onlinePlatformLabel }}
                                    </a>
                                @elseif($externalOnlineUrl && $isSessionEnded)
                                    <div class="alert alert-danger border-0 mb-0 fw-semibold text-center">
                                        <i class="fas fa-lock me-2"></i>{{ $onlinePlatformLabel }} đã đóng vì buổi học đã kết thúc.
                                    </div>
                                @elseif(!$liveRoomUrl)
                                    <div class="alert alert-warning border-0 mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Giảng viên chưa cập nhật link vào lớp trực tuyến.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <div class="session-info-tile">
                                    <span>Trạng thái</span>
                                    <strong>{{ $lichHoc->online_join_state_label }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="session-info-tile">
                                    <span>Mã phòng</span>
                                    <strong>{{ $isSessionEnded ? 'Đã đóng' : ($meetingIdentifier ?: 'Chưa cập nhật') }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="session-info-tile">
                                    <span>Mật khẩu</span>
                                    <strong>{{ $isSessionEnded ? 'Đã đóng' : ($meetingPasscode ?: 'Không có') }}</strong>
                                </div>
                            </div>
                        </div>

                        @if($canOpenExternalOnlineUrl)
                            <div class="online-link-copy mt-3">
                                <i class="fas fa-link text-primary"></i>
                                <span>{{ $externalOnlineUrl }}</span>
                            </div>
                        @endif
                    @else
                        <div class="empty-state-box text-start">
                            <strong>Buổi học trực tiếp</strong>
                            <div class="text-muted small mt-1">Vui lòng theo dõi phòng học hoặc địa điểm được giảng viên cập nhật trong lịch.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="student-session-eyebrow">Sẽ học gì?</div>
                    <h4 class="fw-bold mb-3">Nội dung trọng tâm của buổi học</h4>
                    <div class="learning-agenda">
                        <div class="learning-agenda__item is-primary">
                            <div class="learning-agenda__icon"><i class="fas fa-bullseye"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ $sessionTopic }}</h6>
                                <p class="text-muted mb-0">{{ \Illuminate\Support\Str::limit($sessionSummary, 220) }}</p>
                            </div>
                        </div>
                        <div class="learning-agenda__item">
                            <div class="learning-agenda__icon"><i class="fas fa-book-open"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1">Tài liệu cần xem</h6>
                                <p class="text-muted mb-0">{{ $lichHoc->taiNguyen->count() > 0 ? 'Có ' . $lichHoc->taiNguyen->count() . ' tài nguyên đã được mở cho buổi này.' : 'Chưa có tài nguyên công bố cho buổi này.' }}</p>
                            </div>
                        </div>
                        <div class="learning-agenda__item">
                            <div class="learning-agenda__icon"><i class="fas fa-clipboard-check"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1">Hoạt động sau buổi học</h6>
                                <p class="text-muted mb-0">{{ $relatedExams->count() > 0 ? 'Có ' . $relatedExams->count() . ' bài kiểm tra/bài tập liên quan.' : 'Chưa có bài kiểm tra phát hành cho buổi này.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Tài nguyên buổi học</h5>
                        <p class="text-muted small mb-0">Tài nguyên gắn trực tiếp với buổi học và đã mở cho học viên.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $lichHoc->taiNguyen->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($lichHoc->taiNguyen as $taiNguyen)
                        @php
                            $previewId = 'resource-preview-' . $taiNguyen->id;
                        @endphp
                        <div class="resource-preview-item">
                            <div class="resource-preview-item__header">
                                <div class="resource-preview-item__icon">
                                    <i class="fas {{ $taiNguyen->loai_icon }}"></i>
                                </div>
                                <div class="resource-preview-item__main">
                                    <div class="fw-semibold text-dark">{{ $taiNguyen->tieu_de }}</div>
                                    <div class="small text-muted">{{ $taiNguyen->loai_label }} • {{ $taiNguyen->file_status_message }}</div>
                                </div>
                                <div class="resource-preview-item__actions">
                                    <button
                                        class="btn btn-sm btn-primary fw-bold"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $previewId }}"
                                        aria-expanded="false"
                                        aria-controls="{{ $previewId }}"
                                    >
                                        <i class="fas fa-chevron-down me-1"></i>Xem trong trang
                                    </button>
                                    @if($taiNguyen->file_url)
                                        <a href="{{ $taiNguyen->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary fw-bold">
                                            Mở tab mới
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="collapse" id="{{ $previewId }}">
                                <div class="resource-preview-panel">
                                    <iframe
                                        class="resource-preview-frame"
                                        data-resource-preview-frame
                                        data-preview-src="{{ route('hoc-vien.tai-nguyen.preview', $taiNguyen->id) }}"
                                        title="Xem trước {{ $taiNguyen->tieu_de }}"
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-box">Buổi học này chưa có tài nguyên trực tiếp nào được công bố.</div>
                    @endforelse
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Bài giảng liên quan</h5>
                        <p class="text-muted small mb-0">Bao gồm bài giảng tài liệu và live room của buổi học này.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $relatedLectures->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($relatedLectures as $baiGiang)
                            <div class="col-md-6">
                                <div class="lecture-card h-100">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-light text-dark border">{{ $baiGiang->moduleHoc->ten_module ?? 'Chưa gán module' }}</span>
                                        @if($baiGiang->isLive() && $baiGiang->phongHocLive)
                                            <span class="badge bg-info text-white">{{ $baiGiang->phongHocLive->platform_label }}</span>
                                        @endif
                                    </div>
                                    <h6 class="fw-semibold mb-2">{{ $baiGiang->tieu_de }}</h6>
                                    <p class="small text-muted mb-3">{{ \Illuminate\Support\Str::limit($baiGiang->mo_ta ?: 'Nội dung đã được mở cho học viên.', 110) }}</p>
                                    <a href="{{ $baiGiang->isLive() && $baiGiang->phongHocLive ? route('hoc-vien.live-room.show', $baiGiang->id) : route('hoc-vien.bai-giang.show', $baiGiang->id) }}" class="btn btn-sm btn-outline-primary">
                                        {{ $baiGiang->isLive() ? 'Vào live room' : 'Xem bài giảng' }}
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state-box">Buổi học này chưa có bài giảng nào được công bố.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Bài kiểm tra liên quan</h5>
                        <p class="text-muted small mb-0">Chỉ hiển thị bài kiểm tra đã phát hành cho chính buổi học này.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $relatedExams->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($relatedExams as $baiKiemTra)
                        <div class="content-row">
                            <div>
                                <div class="fw-semibold text-dark">{{ $baiKiemTra->tieu_de }}</div>
                                <div class="small text-muted">{{ $baiKiemTra->thoi_gian_lam_bai }} phút • {{ $baiKiemTra->question_count }} câu hỏi</div>
                            </div>
                            <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-sm btn-outline-primary">
                                Xem bài kiểm tra
                            </a>
                        </div>
                    @empty
                        <div class="empty-state-box">Buổi học này chưa có bài kiểm tra nào được phát hành.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card vip-card border-0 shadow-sm mb-4 student-side-card">
                <div class="card-header border-0 bg-white py-3">
                    <h6 class="fw-semibold mb-0">Điều hướng buổi học</h6>
                </div>
                <div class="card-body">
                    @foreach($courseSchedules as $session)
                        <a href="{{ route('hoc-vien.buoi-hoc.show', $session->id) }}" class="timeline-link {{ (int) $session->id === (int) $lichHoc->id ? 'is-active' : '' }} {{ $session->is_ended ? 'is-complete' : '' }}">
                            <span class="timeline-link__title">Buổi {{ $session->buoi_so ?: '#' }}</span>
                            <span class="timeline-link__meta">{{ $session->moduleHoc->ten_module ?? 'Chưa gán module' }} • {{ $session->ngay_hoc?->format('d/m/Y') ?: 'Chưa rõ ngày' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="card vip-card border-0 shadow-sm">
                <div class="card-header border-0 bg-white py-3">
                    <h6 class="fw-semibold mb-0">Bản ghi buổi học</h6>
                </div>
                <div class="card-body">
                    @forelse($recordings as $recording)
                        <div class="content-row">
                            <div>
                                <div class="fw-semibold text-dark">{{ $recording->tieu_de }}</div>
                                <div class="small text-muted">{{ $recording->duration_label }}</div>
                            </div>
                            @if($recording->playback_url)
                                <a href="{{ $recording->playback_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    Xem bản ghi
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state-box">Chưa có bản ghi nào được công bố cho buổi học này.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
                </div>{{-- /.session-shell__body --}}
            </div>{{-- /#sshv-body --}}
        </div>{{-- /.session-shell --}}
    </div>{{-- /.session-block --}}
</div>{{-- /.container-fluid --}}

@push('styles')
<style>
    .student-session-page {
        --session-ink: #0f172a;
        --session-muted: #64748b;
        --session-line: #e2e8f0;
        --session-blue: #2563eb;
        --session-blue-dark: #1d4ed8;
        --session-blue-soft: #eff6ff;
        --session-blue-line: #bfdbfe;
    }

    .student-session-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.75rem;
        color: #ffffff;
        background:
            radial-gradient(circle at 8% 10%, rgba(255, 255, 255, 0.22), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(96, 165, 250, 0.34), transparent 24%),
            linear-gradient(135deg, var(--session-blue) 0%, var(--session-blue-dark) 48%, #0f172a 100%);
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.16);
    }

    .student-session-hero__content {
        position: relative;
        padding: 2rem;
        z-index: 1;
    }

    .student-session-breadcrumb,
    .student-session-breadcrumb a,
    .student-session-breadcrumb .active {
        color: rgba(255, 255, 255, 0.78);
    }

    .student-session-eyebrow {
        color: var(--session-blue-line);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08rem;
        text-transform: uppercase;
    }

    .student-session-title {
        color: #ffffff;
        font-size: clamp(1.9rem, 4vw, 3.3rem);
        font-weight: 850;
        line-height: 1.08;
        margin: 0.45rem 0 1rem;
    }

    .student-session-summary {
        color: rgba(255, 255, 255, 0.78);
        font-size: 1rem;
        line-height: 1.75;
        max-width: 760px;
    }

    .student-session-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .student-session-meta span {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.55rem 0.8rem;
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
        font-weight: 700;
    }

    .session-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        color: #ffffff;
        font-size: 0.78rem;
        font-weight: 800;
        padding: 0.45rem 0.72rem;
    }

    .session-chip--light {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .student-session-focus-card {
        border-radius: 1.25rem;
        padding: 1.15rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(12px);
    }

    .student-session-focus-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .student-session-focus-grid div {
        border-radius: 1rem;
        padding: 0.85rem;
        background: rgba(255, 255, 255, 0.12);
        text-align: center;
    }

    .student-session-focus-grid strong,
    .student-session-focus-grid span {
        display: block;
    }

    .student-session-focus-grid strong {
        font-size: 1.45rem;
        line-height: 1;
    }

    .student-session-focus-grid span {
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.78rem;
        margin-top: 0.35rem;
    }

    .student-online-card {
        border-radius: 1.35rem;
        overflow: hidden;
    }

    .online-join-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(240px, 0.42fr);
        gap: 1rem;
        border-radius: 1.25rem;
        padding: 1.1rem;
        background:
            linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(255, 255, 255, 0.94)),
            #ffffff;
        border: 1px solid var(--session-blue-line);
    }

    .online-join-panel__main {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .online-join-panel__actions {
        display: grid;
        align-content: center;
        gap: 0.75rem;
    }

    .online-platform-orb {
        width: 4.25rem;
        height: 4.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.2rem;
        color: #ffffff;
        font-size: 1.35rem;
        background:
            linear-gradient(135deg, var(--session-blue) 0%, #38bdf8 100%);
        box-shadow: 0 18px 32px rgba(37, 99, 235, 0.16);
        flex: 0 0 auto;
    }

    .session-info-tile {
        height: 100%;
        border-radius: 1rem;
        padding: 0.95rem;
        background: #f8fafc;
        border: 1px solid var(--session-line);
    }

    .session-info-tile span,
    .session-info-tile strong {
        display: block;
    }

    .session-info-tile span {
        color: var(--session-muted);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.04rem;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
    }

    .session-info-tile strong {
        color: var(--session-ink);
        word-break: break-word;
    }

    .online-link-copy {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        border-radius: 1rem;
        padding: 0.8rem 1rem;
        background: var(--session-blue-soft);
        border: 1px dashed #93c5fd;
        color: var(--session-blue-dark);
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .learning-agenda {
        display: grid;
        gap: 0.9rem;
    }

    .learning-agenda__item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--session-line);
        border-radius: 1.1rem;
        background: #ffffff;
    }

    .learning-agenda__item.is-primary {
        background: var(--session-blue-soft);
        border-color: var(--session-blue-line);
    }

    .learning-agenda__icon {
        width: 2.65rem;
        height: 2.65rem;
        border-radius: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--session-blue);
        color: #ffffff;
        flex: 0 0 auto;
    }

    .info-box,
    .lecture-card {
        border: 1px solid var(--session-line);
        border-radius: 18px;
        background: #fff;
        padding: 1rem 1.1rem;
    }

    .content-row {
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
    }

    .content-row:first-child {
        padding-top: 0;
    }

    .content-row:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .resource-preview-item {
        border: 1px solid var(--session-line);
        border-radius: 1rem;
        background: #ffffff;
        margin-bottom: 0.85rem;
        overflow: hidden;
    }

    .resource-preview-item:last-child {
        margin-bottom: 0;
    }

    .resource-preview-item__header {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 0.9rem;
        align-items: center;
        padding: 1rem;
    }

    .resource-preview-item__icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--session-blue-soft);
        color: var(--session-blue);
        font-size: 1.05rem;
    }

    .resource-preview-item__main {
        min-width: 0;
    }

    .resource-preview-item__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .resource-preview-item__actions [aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
    }

    .resource-preview-item__actions .fa-chevron-down {
        transition: transform 160ms ease;
    }

    .resource-preview-panel {
        border-top: 1px solid var(--session-line);
        background: #f8fafc;
        padding: 0.85rem;
    }

    .resource-preview-frame {
        display: block;
        width: 100%;
        min-height: 720px;
        border: 1px solid #bfdbfe;
        border-radius: 0.85rem;
        background: #ffffff;
    }

    .student-side-card {
        position: relative;
        z-index: 0;
    }

    .student-side-card:hover {
        transform: none;
    }

    .timeline-link {
        display: block;
        padding: 0.9rem 1rem;
        border-radius: 16px;
        border: 1px solid var(--session-line);
        color: var(--session-ink);
        text-decoration: none;
        margin-bottom: 0.75rem;
        background: #fff;
        transition: 160ms ease;
    }

    .timeline-link:hover {
        transform: translateY(-1px);
        border-color: #60a5fa;
        box-shadow: 0 12px 28px rgba(37, 99, 235, 0.08);
    }

    .timeline-link:last-child {
        margin-bottom: 0;
    }

    .timeline-link.is-active {
        border-color: var(--session-blue);
        background: var(--session-blue-soft);
    }

    .timeline-link.is-complete {
        border-color: #22c55e;
        background: #f0fdf4;
        color: #14532d;
    }

    .timeline-link.is-complete:hover {
        border-color: #16a34a;
        box-shadow: 0 12px 28px rgba(22, 163, 74, 0.1);
    }

    .timeline-link.is-complete .timeline-link__meta {
        color: #166534;
    }

    .timeline-link__title {
        display: block;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .timeline-link__meta {
        display: block;
        color: var(--session-muted);
        font-size: 0.88rem;
    }

    .empty-state-box {
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        padding: 1.25rem;
        background: #f8fafc;
        color: var(--session-muted);
        text-align: center;
    }

    @media (max-width: 991.98px) {
        .student-session-hero__content {
            padding: 1.35rem;
        }

        .online-join-panel {
            grid-template-columns: 1fr;
        }

        .online-join-panel__main {
            align-items: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .student-session-focus-grid {
            grid-template-columns: 1fr;
        }

        .content-row,
        .learning-agenda__item {
            flex-direction: column;
        }

        .resource-preview-item__header {
            grid-template-columns: 1fr;
        }

        .resource-preview-item__actions {
            justify-content: stretch;
        }

        .resource-preview-item__actions .btn {
            width: 100%;
        }

        .resource-preview-frame {
            min-height: 560px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('shown.bs.collapse', function (event) {
        const frame = event.target.querySelector('[data-resource-preview-frame]');

        if (!frame || frame.getAttribute('src')) {
            return;
        }

        frame.setAttribute('src', frame.dataset.previewSrc);
    });
</script>
@endpush

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Welcome banner xanh dương ===== */
    .sshv-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .sshv-tag-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
    .sshv-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem; font-weight: 800;
        letter-spacing: 1px; border-radius: 999px;
    }
    .sshv-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .sshv-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
        color: #fff;
    }
    .sshv-status-pill i { font-size: 0.6rem; }
    .apx-welcome.sshv-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.sshv-welcome p i { color: #fef3c7; margin-right: 4px; }
    .sshv-sep { opacity: 0.5; }
    .sshv-launch-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    /* Breadcrumb đỏ */
    .sshv-breadcrumb {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 3px solid #dc2626;
        border-radius: 8px;
        padding: 8px 16px;
    }
    .sshv-breadcrumb .breadcrumb { font-size: 0.82rem; }
    .sshv-breadcrumb a { color: #dc2626; text-decoration: none; font-weight: 600; }
    .sshv-breadcrumb a:hover { color: #b91c1c; text-decoration: underline; }
    .sshv-breadcrumb .breadcrumb-item.active { color: #0f172a; font-weight: 700; }

    /* Card section title polish — viền đỏ trái */
    .sshv-page .card-body h3,
    .sshv-page .card-body h4,
    .sshv-page .student-session-eyebrow {
        position: relative;
    }
    .sshv-page .student-session-eyebrow {
        color: #dc2626 !important;
        font-weight: 800;
        letter-spacing: 0.5px;
    }
    .sshv-page .card .card-header h5::before {
        content: '';
        display: inline-block;
        width: 4px; height: 16px;
        background: #dc2626;
        margin-right: 10px;
        vertical-align: middle;
        border-radius: 2px;
    }

    /* Learning agenda — đổi accent sang đỏ */
    .sshv-page .learning-agenda__item.is-primary .learning-agenda__icon {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
        color: #fff !important;
    }

    /* ===== Session shell — copy style từ trang giảng viên ===== */
    .sshv-session { margin-bottom: 0; }
    .sshv-session .session-shell {
        border-radius: 1.25rem;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e2e8f0;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .sshv-session .session-shell:hover {
        box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, 0.08) !important;
    }
    .sshv-session .session-shell__header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #fecaca;
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
    }
    .sshv-session .session-shell__number {
        width: 54px; height: 54px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.25);
    }
    .sshv-session .session-shell__title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.02em;
    }
    .sshv-session .session-shell__body {
        background-color: #fff;
    }
    .sshv-session .text-silver { color: #cbd5e1; }

    /* Soft badges (giống teacher) */
    .sshv-session .bg-primary-soft   { background-color: rgba(13, 110, 253, 0.1); }
    .sshv-session .bg-success-soft   { background-color: rgba(25, 135, 84, 0.1); }
    .sshv-session .bg-info-soft      { background-color: rgba(13, 202, 240, 0.1); }
    .sshv-session .bg-warning-soft   { background-color: rgba(255, 193, 7, 0.12); }
    .sshv-session .bg-danger-soft    { background-color: rgba(220, 53, 69, 0.1); }
    .sshv-session .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }

    /* Internal cards inside body — slim corners + light tone match */
    .sshv-session .session-shell__body .card {
        border-radius: 1rem;
        border: 1px solid #f1f5f9;
        background: #fbfcfd;
        transition: all 0.25s ease;
    }
    .sshv-session .session-shell__body .card:hover {
        background: #fff;
        border-color: #e2e8f0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
    }
    .sshv-session .session-shell__body .card-header {
        background: transparent !important;
        border-bottom: 1px dashed #fecaca !important;
    }

    @media (max-width: 720px) {
        .sshv-session .session-shell__header { padding: 1rem 1.25rem; }
        .sshv-session .session-shell__body { padding: 1rem !important; }
        .sshv-session .session-shell__number { width: 44px; height: 44px; font-size: 1.05rem; }
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

    @media (max-width: 720px) {
        .session-ended-banner { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection
