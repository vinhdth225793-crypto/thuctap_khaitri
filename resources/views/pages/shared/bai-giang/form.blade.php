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
    $selectedLoai = old('loai_bai_giang', $baiGiang->loai_bai_giang ?? 'hon_hop');
    $selectedMainResource = old('tai_nguyen_chinh_id', $baiGiang->tai_nguyen_chinh_id ?? null);
    $selectedExtraResources = collect(old('tai_nguyen_phu_ids', $baiGiang?->taiNguyenPhu?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    $selectedModeratorId = old('live.moderator_id', $liveRoom?->moderator_id ?? $defaultModeratorId ?? null);
    $selectedAssistantId = old('live.tro_giang_id', $liveRoom?->tro_giang_id ?? null);
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
                                    <select name="tai_nguyen_chinh_id" class="form-select @error('tai_nguyen_chinh_id') is-invalid @enderror">
                                        <option value="">-- Chọn --</option>
                                        @foreach($thuVien as $tn)
                                            <option value="{{ $tn->id }}" @selected((int) $selectedMainResource === (int) $tn->id)>{{ $tn->tieu_de }} ({{ $tn->loai_label }})</option>
                                        @endforeach
                                    </select>
                                    @error('tai_nguyen_chinh_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <label class="form-label fw-bold">Tài nguyên phụ</label>
                                <div class="row g-2">
                                    @foreach($thuVien as $tn)
                                        <div class="col-md-6">
                                            <label class="card border-0 bg-light p-3 h-100">
                                                <span class="d-flex gap-2">
                                                    <input type="checkbox" class="form-check-input mt-0" name="tai_nguyen_phu_ids[]" value="{{ $tn->id }}" @checked(in_array((int) $tn->id, $selectedExtraResources, true))>
                                                    <span>{{ $tn->tieu_de }}</span>
                                                </span>
                                                <small class="text-muted mt-2">{{ $tn->loai_label }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('tai_nguyen_phu_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div id="live-config-card" class="card border-0 shadow-sm mt-4 {{ $selectedLoai === 'live' ? '' : 'd-none' }}">
                            <div class="card-header bg-white"><strong>Phòng học live</strong></div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nền tảng</label>
                                        <select name="live[nen_tang_live]" class="form-select @error('live.nen_tang_live') is-invalid @enderror">
                                            @foreach(config('live_room.platforms', []) as $platformKey => $platformConfig)
                                                <option value="{{ $platformKey }}" @selected(old('live.nen_tang_live', $liveRoom->nen_tang_live ?? 'zoom') === $platformKey)>{{ $platformConfig['label'] }}</option>
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
                                                <option value="{{ $user->ma_nguoi_dung }}" @selected((int) $selectedModeratorId === (int) $user->ma_nguoi_dung)>{{ $user->ho_ten }} ({{ $user->vai_tro }})</option>
                                            @endforeach
                                        </select>
                                        @error('live.moderator_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Trợ giảng</label>
                                        <select name="live[tro_giang_id]" class="form-select @error('live.tro_giang_id') is-invalid @enderror">
                                            <option value="">-- Không có --</option>
                                            @foreach($assistantOptions as $user)
                                                <option value="{{ $user->ma_nguoi_dung }}" @selected((int) $selectedAssistantId === (int) $user->ma_nguoi_dung)>{{ $user->ho_ten }} ({{ $user->vai_tro }})</option>
                                            @endforeach
                                        </select>
                                        @error('live.tro_giang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Bắt đầu</label>
                                        <input type="datetime-local" name="live[thoi_gian_bat_dau]" class="form-control @error('live.thoi_gian_bat_dau') is-invalid @enderror" value="{{ old('live.thoi_gian_bat_dau', optional($liveRoom?->thoi_gian_bat_dau)->format('Y-m-d\TH:i')) }}">
                                        @error('live.thoi_gian_bat_dau') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Thời lượng</label>
                                        <input type="number" min="15" max="480" name="live[thoi_luong_phut]" class="form-control @error('live.thoi_luong_phut') is-invalid @enderror" value="{{ old('live.thoi_luong_phut', $liveRoom->thoi_luong_phut ?? 90) }}">
                                        @error('live.thoi_luong_phut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Sức chứa</label>
                                        <input type="number" min="1" max="1000" name="live[suc_chua_toi_da]" class="form-control @error('live.suc_chua_toi_da') is-invalid @enderror" value="{{ old('live.suc_chua_toi_da', $liveRoom->suc_chua_toi_da ?? '') }}">
                                        @error('live.suc_chua_toi_da') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Join URL</label>
                                        <input type="url" name="live[join_url]" class="form-control @error('live.join_url') is-invalid @enderror" value="{{ old('live.join_url', $liveRoom->du_lieu_nen_tang_json['join_url'] ?? '') }}">
                                        @error('live.join_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Start URL</label>
                                        <input type="url" name="live[start_url]" class="form-control @error('live.start_url') is-invalid @enderror" value="{{ old('live.start_url', $liveRoom->du_lieu_nen_tang_json['start_url'] ?? '') }}">
                                        @error('live.start_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Meeting ID</label>
                                        <input type="text" name="live[meeting_id]" class="form-control" value="{{ old('live.meeting_id', $liveRoom->du_lieu_nen_tang_json['meeting_id'] ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Meeting code</label>
                                        <input type="text" name="live[meeting_code]" class="form-control" value="{{ old('live.meeting_code', $liveRoom->du_lieu_nen_tang_json['meeting_code'] ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Passcode</label>
                                        <input type="text" name="live[passcode]" class="form-control" value="{{ old('live.passcode', $liveRoom->du_lieu_nen_tang_json['passcode'] ?? '') }}">
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
    const initialLichHocId = @json($selectedLichHocId);
    let moderatorTouched = false;

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
                    if (selectedId && String(item.id) === String(selectedId)) {
                        option.selected = true;
                    }
                    lichHocSelect.appendChild(option);
                });
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
});
</script>
@endpush
