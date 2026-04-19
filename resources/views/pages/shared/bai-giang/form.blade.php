@php
    $liveRoom = $baiGiang?->phongHocLive;
    $selectedPhanCongId = old('phan_cong_id', request('phan_cong_id'));
    if (!$selectedPhanCongId && $baiGiang) {
        $selectedPhanCongId = optional($phanCongs->first(function ($phanCong) use ($baiGiang) {
            return (int) $phanCong->khoa_hoc_id === (int) $baiGiang->khoa_hoc_id
                && (int) $phanCong->module_hoc_id === (int) $baiGiang->module_hoc_id;
        }))->id;
    }
    $selectedLichHocId = old('lich_hoc_id', $baiGiang->lich_hoc_id ?? request('lich_hoc_id'));
    $selectedLichHoc = $selectedLichHocId ? \App\Models\LichHoc::query()->find((int) $selectedLichHocId) : null;
    $selectedLoai = old('loai_bai_giang', $baiGiang->loai_bai_giang ?? 'hon_hop');
    $selectedMainResource = old('tai_nguyen_chinh_id', $baiGiang->tai_nguyen_chinh_id ?? null);
    $selectedExtraResources = collect(old('tai_nguyen_phu_ids', $baiGiang?->taiNguyenPhu?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    $selectedModeratorId = old('live.moderator_id', $liveRoom?->moderator_id ?? $defaultModeratorId ?? null);
    $selectedAssistantId = old('live.tro_giang_id', $liveRoom?->tro_giang_id ?? null);
    $selectedScheduleSignal = strtolower((string) $selectedLichHoc?->nen_tang . ' ' . (string) $selectedLichHoc?->link_online);
    $defaultLivePlatform = str_contains($selectedScheduleSignal, 'google') || str_contains($selectedScheduleSignal, 'meet.google.com')
        ? \App\Models\PhongHocLive::PLATFORM_GOOGLE_MEET
        : (str_contains($selectedScheduleSignal, 'zoom') || filled($selectedLichHoc?->link_online) ? \App\Models\PhongHocLive::PLATFORM_ZOOM : \App\Models\PhongHocLive::PLATFORM_INTERNAL);
    $defaultLiveStart = optional($liveRoom?->thoi_gian_bat_dau ?: $selectedLichHoc?->starts_at)->format('Y-m-d\TH:i');
    $defaultLiveDuration = $liveRoom->thoi_luong_phut
        ?? ($selectedLichHoc?->starts_at && $selectedLichHoc?->ends_at ? max(15, $selectedLichHoc->starts_at->diffInMinutes($selectedLichHoc->ends_at)) : 90);
    $defaultLiveJoinUrl = $liveRoom->du_lieu_nen_tang_json['join_url'] ?? \App\Support\OnlineMeetingUrl::normalize($selectedLichHoc?->link_online);
    $defaultLiveStartUrl = $liveRoom->du_lieu_nen_tang_json['start_url'] ?? $defaultLiveJoinUrl;
    $defaultMeetingCode = $liveRoom->du_lieu_nen_tang_json['meeting_code'] ?? \App\Support\OnlineMeetingUrl::meetingCode($defaultLiveJoinUrl);
    $defaultMeetingId = $liveRoom->du_lieu_nen_tang_json['meeting_id'] ?? $selectedLichHoc?->meeting_id;
    $defaultPasscode = $liveRoom->du_lieu_nen_tang_json['passcode'] ?? $selectedLichHoc?->mat_khau_cuoc_hop;
    $submitPrimaryValue = $isAdmin ? 'duyet_ngay' : 'gui_duyet';
    $submitPrimaryLabel = $isAdmin ? 'Lưu và duyệt ngay' : 'Lưu và gửi duyệt';
@endphp

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <h2 class="fw-bold mb-1">{{ $pageTitle }}</h2>
            <p class="text-muted mb-0">{{ $pageSubtitle }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <form action="{{ $formAction }}" method="POST">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tiêu đề bài giảng</label>
                                    <input type="text" name="tieu_de" class="form-control @error('tieu_de') is-invalid @enderror" value="{{ old('tieu_de', $baiGiang->tieu_de ?? '') }}" required>
                                    @error('tieu_de') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mô tả</label>
                                    <textarea name="mo_ta" rows="4" class="form-control @error('mo_ta') is-invalid @enderror">{{ old('mo_ta', $baiGiang->mo_ta ?? '') }}</textarea>
                                    @error('mo_ta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Loại bài giảng</label>
                                        <select name="loai_bai_giang" id="loai_bai_giang" class="form-select @error('loai_bai_giang') is-invalid @enderror" required>
                                            @foreach(['video' => 'Video', 'tai_lieu' => 'Tài liệu', 'bai_doc' => 'Bài đọc', 'bai_tap' => 'Bài tập', 'hon_hop' => 'Hỗn hợp', 'live' => 'Phòng học live'] as $value => $label)
                                                <option value="{{ $value }}" @selected($selectedLoai === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('loai_bai_giang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Thứ tự hiển thị</label>
                                        <input type="number" name="thu_tu_hien_thi" min="0" class="form-control @error('thu_tu_hien_thi') is-invalid @enderror" value="{{ old('thu_tu_hien_thi', $baiGiang->thu_tu_hien_thi ?? 0) }}">
                                        @error('thu_tu_hien_thi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-white"><strong>Tài nguyên</strong></div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tài nguyên chính</label>
                                    <div class="input-group">
                                        <select name="tai_nguyen_chinh_id" id="tai_nguyen_chinh_id" class="form-select @error('tai_nguyen_chinh_id') is-invalid @enderror">
                                            <option value="" data-url="">-- Chọn --</option>
                                            @foreach($thuVien as $tn)
                                                <option value="{{ $tn->id }}" data-url="{{ $tn->file_url }}" @selected((int) $selectedMainResource === (int) $tn->id)>{{ $tn->tieu_de }} ({{ $tn->loai_label }})</option>
                                            @endforeach
                                        </select>
                                        <a id="preview-main-resource" href="{{ $baiGiang?->taiNguyenChinh?->file_url ?? '#' }}" target="_blank" class="btn btn-outline-secondary {{ $selectedMainResource ? '' : 'disabled' }}">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </div>
                                    @error('tai_nguyen_chinh_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <label class="form-label fw-bold">Tài nguyên phụ</label>
                                <div class="row g-2">
                                    @foreach($thuVien as $tn)
                                        <div class="col-md-6">
                                            <label class="card border-0 bg-light p-3 h-100 position-relative">
                                                <span class="d-flex gap-2">
                                                    <input type="checkbox" class="form-check-input mt-0" name="tai_nguyen_phu_ids[]" value="{{ $tn->id }}" @checked(in_array((int) $tn->id, $selectedExtraResources, true))>
                                                    <span>{{ $tn->tieu_de }}</span>
                                                </span>
                                                <small class="text-muted mt-2">{{ $tn->loai_label }}</small>
                                                <a href="{{ $tn->file_url }}" target="_blank" class="position-absolute top-0 end-0 p-2 text-decoration-none" title="Xem tài nguyên">
                                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                                </a>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('tai_nguyen_phu_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if($isAdmin && $baiGiang)
                        <div class="card border-0 shadow-sm mt-4 border-start border-primary border-4">
                            <div class="card-header bg-white"><strong class="text-primary">Thông tin tài liệu phục vụ phê duyệt</strong></div>
                            <div class="card-body p-4">
                                @if($baiGiang->taiNguyenChinh)
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-2">Tài nguyên chính:</h6>
                                        <div class="d-flex align-items-start p-3 bg-light rounded shadow-sm">
                                            <div class="bg-{{ $baiGiang->taiNguyenChinh->loai_color }} text-white rounded p-3 me-3">
                                                <i class="fas {{ $baiGiang->taiNguyenChinh->loai_icon }} fa-2x"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-bold text-dark">{{ $baiGiang->taiNguyenChinh->tieu_de }}</h5>
                                                <p class="text-muted small mb-2">{{ $baiGiang->taiNguyenChinh->mo_ta ?: 'Không có mô tả' }}</p>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-{{ $baiGiang->taiNguyenChinh->loai_color }}">{{ $baiGiang->taiNguyenChinh->loai_label }}</span>
                                                    @if($baiGiang->taiNguyenChinh->file_size)
                                                        <span class="badge bg-secondary text-white">{{ number_format($baiGiang->taiNguyenChinh->file_size / 1024 / 1024, 2) }} MB</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-external-link-alt me-1"></i> Xem tài liệu
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($baiGiang->taiNguyenPhu->isNotEmpty())
                                    <div>
                                        <h6 class="fw-bold mb-2">Tài nguyên phụ ({{ $baiGiang->taiNguyenPhu->count() }}):</h6>
                                        <div class="list-group list-group-flush border rounded overflow-hidden">
                                            @foreach($baiGiang->taiNguyenPhu as $tnPhu)
                                                <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="text-{{ $tnPhu->loai_color }} me-3">
                                                            <i class="fas {{ $tnPhu->loai_icon }} fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-0">{{ $tnPhu->tieu_de }}</div>
                                                            <small class="text-muted">{{ $tnPhu->loai_label }}</small>
                                                        </div>
                                                    </div>
                                                    <a href="{{ $tnPhu->file_url }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                        <i class="fas fa-eye me-1"></i> Xem
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!$baiGiang->taiNguyenChinh && $baiGiang->taiNguyenPhu->isEmpty())
                                    <div class="text-center py-4 bg-light rounded border border-dashed">
                                        <i class="fas fa-file-excel fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Bài giảng này không đính kèm tài nguyên nào.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div id="live-config-card" class="card border-0 shadow-sm mt-4 {{ $selectedLoai === 'live' ? '' : 'd-none' }}">
                            <div class="card-header bg-white"><strong>Phòng học live</strong></div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nền tảng</label>
                                        <select name="live[nen_tang_live]" id="live_nen_tang_live" class="form-select @error('live.nen_tang_live') is-invalid @enderror">
                                            @foreach(config('live_room.platforms', []) as $platformKey => $platformConfig)
                                                <option value="{{ $platformKey }}" @selected(old('live.nen_tang_live', $liveRoom->nen_tang_live ?? $defaultLivePlatform) === $platformKey)>{{ $platformConfig['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('live.nen_tang_live') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Loại live</label>
                                        <select name="live[loai_live]" class="form-select @error('live.loai_live') is-invalid @enderror">
                                            @foreach(['class' => 'Class', 'meeting' => 'Meeting', 'webinar' => 'Webinar'] as $value => $label)
                                                <option value="{{ $value }}" @selected(old('live.loai_live', $liveRoom->loai_live ?? 'class') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('live.loai_live') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Moderator</label>
                                        <select name="live[moderator_id]" id="live_moderator_id" class="form-select @error('live.moderator_id') is-invalid @enderror">
                                            <option value="">-- Chọn --</option>
                                            @foreach($moderatorOptions as $user)
                                                @php $moderatorUserId = $user->ma_nguoi_dung ?? $user->id; @endphp
                                                <option value="{{ $moderatorUserId }}" @selected((int) $selectedModeratorId === (int) $moderatorUserId)>{{ $user->ho_ten }} ({{ $user->vai_tro }})</option>
                                            @endforeach
                                        </select>
                                        @error('live.moderator_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Trợ giảng</label>
                                        <select name="live[tro_giang_id]" class="form-select @error('live.tro_giang_id') is-invalid @enderror">
                                            <option value="">-- Không có --</option>
                                            @foreach($assistantOptions as $user)
                                                @php $assistantUserId = $user->ma_nguoi_dung ?? $user->id; @endphp
                                                <option value="{{ $assistantUserId }}" @selected((int) $selectedAssistantId === (int) $assistantUserId)>{{ $user->ho_ten }} ({{ $user->vai_tro }})</option>
                                            @endforeach
                                        </select>
                                        @error('live.tro_giang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Bắt đầu</label>
                                        <input type="datetime-local" name="live[thoi_gian_bat_dau]" id="live_thoi_gian_bat_dau" class="form-control @error('live.thoi_gian_bat_dau') is-invalid @enderror" value="{{ old('live.thoi_gian_bat_dau', $defaultLiveStart) }}">
                                        @error('live.thoi_gian_bat_dau') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Thời lượng</label>
                                        <input type="number" min="15" max="480" name="live[thoi_luong_phut]" id="live_thoi_luong_phut" class="form-control @error('live.thoi_luong_phut') is-invalid @enderror" value="{{ old('live.thoi_luong_phut', $defaultLiveDuration) }}">
                                        @error('live.thoi_luong_phut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Sức chứa</label>
                                        <input type="number" min="1" max="1000" name="live[suc_chua_toi_da]" class="form-control @error('live.suc_chua_toi_da') is-invalid @enderror" value="{{ old('live.suc_chua_toi_da', $liveRoom->suc_chua_toi_da ?? '') }}">
                                        @error('live.suc_chua_toi_da') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Join URL</label>
                                        <input type="url" name="live[join_url]" id="live_join_url" class="form-control @error('live.join_url') is-invalid @enderror" value="{{ old('live.join_url', $defaultLiveJoinUrl) }}">
                                        @error('live.join_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Start URL</label>
                                        <input type="url" name="live[start_url]" id="live_start_url" class="form-control @error('live.start_url') is-invalid @enderror" value="{{ old('live.start_url', $defaultLiveStartUrl) }}">
                                        @error('live.start_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Meeting ID</label>
                                        <input type="text" name="live[meeting_id]" id="live_meeting_id" class="form-control" value="{{ old('live.meeting_id', $defaultMeetingId) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Meeting code</label>
                                        <input type="text" name="live[meeting_code]" id="live_meeting_code" class="form-control" value="{{ old('live.meeting_code', $defaultMeetingCode) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Passcode</label>
                                        <input type="text" name="live[passcode]" id="live_passcode" class="form-control" value="{{ old('live.passcode', $defaultPasscode) }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Mở phòng trước (phút)</label>
                                        <input type="number" min="0" max="180" name="live[mo_phong_truoc_phut]" class="form-control" value="{{ old('live.mo_phong_truoc_phut', $liveRoom->mo_phong_truoc_phut ?? config('live_room.defaults.open_before_minutes', 15)) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Nhắc trước (phút)</label>
                                        <input type="number" min="0" max="180" name="live[nhac_truoc_phut]" class="form-control" value="{{ old('live.nhac_truoc_phut', $liveRoom->nhac_truoc_phut ?? config('live_room.defaults.reminder_minutes', 10)) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Embed URL</label>
                                        <input type="url" name="live[embed_url]" class="form-control" value="{{ old('live.embed_url', $liveRoom->du_lieu_nen_tang_json['embed_url'] ?? '') }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold">Security note</label>
                                        <textarea name="live[security_note]" rows="2" class="form-control">{{ old('live.security_note', $liveRoom->du_lieu_nen_tang_json['security_note'] ?? '') }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="row g-2">
                                            @foreach([
                                                'cho_phep_chat' => 'Cho phép Chat',
                                                'cho_phep_thao_luan' => 'Cho phép thảo luận',
                                                'cho_phep_chia_se_man_hinh' => 'Chia sẻ màn hình',
                                                'tat_mic_khi_vao' => 'Tắt mic khi vào',
                                                'tat_camera_khi_vao' => 'Tắt camera khi vào',
                                                'cho_phep_ghi_hinh' => 'Cho phép ghi hình',
                                                'chi_admin_duoc_ghi_hinh' => 'Chỉ admin mới được ghi hình',
                                                'tu_dong_gan_ban_ghi' => 'Tự động gắn bản ghi',
                                                'khoa_copy_noi_dung_mo_ta' => 'Khóa sao chép mô tả',
                                                'waiting_room' => 'Phòng chờ (Waiting room)',
                                            ] as $field => $label)
                                                <div class="col-md-6">
                                                    <label class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="live[{{ $field }}]" value="1" @checked(old('live.' . $field, data_get($liveRoom, $field, data_get($liveRoom?->du_lieu_nen_tang_json, $field, in_array($field, ['cho_phep_chat', 'cho_phep_thao_luan', 'tat_mic_khi_vao', 'tat_camera_khi_vao']) ? true : false))))>
                                                        <span class="form-check-label">{{ $label }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phân công module</label>
                                    <select name="phan_cong_id" id="phan_cong_id" class="form-select @error('phan_cong_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn phân công --</option>
                                        @foreach($phanCongs as $pc)
                                            @php $optionModeratorId = $pc->giangVien->nguoiDung->ma_nguoi_dung ?? null; @endphp
                                            <option value="{{ $pc->id }}" data-moderator-id="{{ $optionModeratorId }}" @selected((int) $selectedPhanCongId === (int) $pc->id)>{{ $pc->moduleHoc->ten_module }} ({{ $pc->khoaHoc->ten_khoa_hoc }})</option>
                                        @endforeach
                                    </select>
                                    @error('phan_cong_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Buổi học</label>
                                    <select name="lich_hoc_id" id="lich_hoc_id" class="form-select @error('lich_hoc_id') is-invalid @enderror">
                                        <option value="">-- Chọn buổi học --</option>
                                    </select>
                                    @error('lich_hoc_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Thời điểm mở</label>
                                    <input type="datetime-local" name="thoi_diem_mo" class="form-control @error('thoi_diem_mo') is-invalid @enderror" value="{{ old('thoi_diem_mo', optional($baiGiang?->thoi_diem_mo)->format('Y-m-d\TH:i')) }}">
                                    @error('thoi_diem_mo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm bg-primary text-white mt-4">
                            <div class="card-body p-3 d-grid gap-2">
                                <button type="submit" name="hanh_dong" value="luu_nhap" class="btn btn-light fw-bold text-primary">Lưu nháp</button>
                                <button type="submit" name="hanh_dong" value="{{ $submitPrimaryValue }}" class="btn btn-outline-light fw-bold">{{ $submitPrimaryLabel }}</button>
                                <a href="{{ $indexRoute }}" class="btn btn-link text-white text-decoration-none">Quay lại</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const phanCongSelect = document.getElementById('phan_cong_id');
    const lichHocSelect = document.getElementById('lich_hoc_id');
    const loaiBaiGiangSelect = document.getElementById('loai_bai_giang');
    const liveConfigCard = document.getElementById('live-config-card');
    const moderatorSelect = document.getElementById('live_moderator_id');
    const livePlatformSelect = document.getElementById('live_nen_tang_live');
    const liveStartInput = document.getElementById('live_thoi_gian_bat_dau');
    const liveDurationInput = document.getElementById('live_thoi_luong_phut');
    const liveJoinUrlInput = document.getElementById('live_join_url');
    const liveStartUrlInput = document.getElementById('live_start_url');
    const liveMeetingIdInput = document.getElementById('live_meeting_id');
    const liveMeetingCodeInput = document.getElementById('live_meeting_code');
    const livePasscodeInput = document.getElementById('live_passcode');
    const initialLichHocId = @json($selectedLichHocId);
    const mainResourceSelect = document.getElementById('tai_nguyen_chinh_id');
    const previewMainBtn = document.getElementById('preview-main-resource');
    let moderatorTouched = false;

    if (mainResourceSelect && previewMainBtn) {
        mainResourceSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const url = selectedOption.dataset.url;
            if (url) {
                previewMainBtn.href = url;
                previewMainBtn.classList.remove('disabled');
            } else {
                previewMainBtn.href = '#';
                previewMainBtn.classList.add('disabled');
            }
        });
    }

    if (moderatorSelect) {
        moderatorSelect.addEventListener('change', function () {
            moderatorTouched = true;
        });
    }

    function toggleLiveConfig() {
        liveConfigCard.classList.toggle('d-none', loaiBaiGiangSelect.value !== 'live');
    }

    function syncModerator() {
        if (moderatorTouched || !moderatorSelect || !phanCongSelect.value) return;
        const selectedOption = phanCongSelect.options[phanCongSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.moderatorId) {
            moderatorSelect.value = selectedOption.dataset.moderatorId;
        }
    }

    function setValue(input, value, force = false) {
        if (!input || value === undefined || value === null || value === '') {
            return;
        }

        if (force || !input.value) {
            input.value = value;
        }
    }

    function applyScheduleDefaults(option, force = false) {
        if (!option) {
            return;
        }

        setValue(liveStartInput, option.dataset.startsAt, force);
        setValue(liveDurationInput, option.dataset.durationMinutes, force);
        setValue(liveJoinUrlInput, option.dataset.linkOnline, force);
        setValue(liveStartUrlInput, option.dataset.linkOnline, force);
        setValue(liveMeetingIdInput, option.dataset.meetingId, force);
        setValue(liveMeetingCodeInput, option.dataset.meetingCode, force);
        setValue(livePasscodeInput, option.dataset.passcode, force);

        if (livePlatformSelect && option.dataset.platform && (force || !livePlatformSelect.value)) {
            livePlatformSelect.value = option.dataset.platform;
        }
    }

    function loadLichHoc(selectedId = null) {
        lichHocSelect.innerHTML = '<option value="">-- Chọn buổi học --</option>';
        if (!phanCongSelect.value) return;
        fetch(`{{ $getLichHocRoute }}?phan_cong_id=${phanCongSelect.value}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `Buổi ${item.buoi_so} (${item.ngay_hoc || 'Chưa xếp lịch'})`;
                    option.dataset.startsAt = item.starts_at || '';
                    option.dataset.durationMinutes = item.duration_minutes || '';
                    option.dataset.platform = item.platform || '';
                    option.dataset.linkOnline = item.link_online || '';
                    option.dataset.meetingId = item.meeting_id || '';
                    option.dataset.meetingCode = item.meeting_code || '';
                    option.dataset.passcode = item.passcode || '';
                    if (selectedId && String(item.id) === String(selectedId)) {
                        option.selected = true;
                    }
                    lichHocSelect.appendChild(option);
                });

                applyScheduleDefaults(lichHocSelect.options[lichHocSelect.selectedIndex], false);
            });
    }

    toggleLiveConfig();
    syncModerator();
    loadLichHoc(initialLichHocId);

    loaiBaiGiangSelect.addEventListener('change', toggleLiveConfig);
    phanCongSelect.addEventListener('change', function () {
        syncModerator();
        loadLichHoc();
    });
    lichHocSelect.addEventListener('change', function () {
        applyScheduleDefaults(lichHocSelect.options[lichHocSelect.selectedIndex], true);
    });
});
</script>
@endpush
