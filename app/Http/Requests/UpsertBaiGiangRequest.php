<?php

namespace App\Http\Requests;

use App\Models\BaiGiang;
use App\Models\LichHoc;
use App\Models\PhongHocLive;
use App\Support\OnlineMeetingUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpsertBaiGiangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isGiangVien() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        $platforms = array_keys(config('live_room.platforms', []));
        $requiresExternalJoinUrl = fn (): bool => $this->input('loai_bai_giang') === BaiGiang::TYPE_LIVE
            && $this->input('live.nen_tang_live') !== PhongHocLive::PLATFORM_INTERNAL;

        return [
            'tieu_de' => ['required', 'string', 'max:255'],
            'mo_ta' => ['nullable', 'string'],
            'phan_cong_id' => ['required', 'exists:phan_cong_module_giang_vien,id'],
            'lich_hoc_id' => ['nullable', 'exists:lich_hoc,id'],
            'loai_bai_giang' => ['required', Rule::in(['video', 'tai_lieu', 'bai_doc', 'bai_tap', 'hon_hop', BaiGiang::TYPE_LIVE])],
            'tai_nguyen_chinh_id' => ['nullable', 'exists:tai_nguyen_buoi_hoc,id'],
            'tai_nguyen_phu_ids' => ['nullable', 'array'],
            'tai_nguyen_phu_ids.*' => ['integer', 'exists:tai_nguyen_buoi_hoc,id'],
            'thoi_diem_mo' => ['nullable', 'date'],
            'thu_tu_hien_thi' => ['nullable', 'integer', 'min:0'],
            'hanh_dong' => ['nullable', Rule::in(['luu_nhap', 'gui_duyet', 'duyet_ngay'])],

            'live' => ['nullable', 'array'],
            'live.nen_tang_live' => ['required_if:loai_bai_giang,' . BaiGiang::TYPE_LIVE, Rule::in($platforms)],
            'live.loai_live' => ['nullable', Rule::in([PhongHocLive::TYPE_MEETING, PhongHocLive::TYPE_CLASS, PhongHocLive::TYPE_WEBINAR])],
            'live.tieu_de' => ['nullable', 'string', 'max:255'],
            'live.mo_ta' => ['nullable', 'string'],
            'live.moderator_id' => ['required_if:loai_bai_giang,' . BaiGiang::TYPE_LIVE, 'exists:nguoi_dung,ma_nguoi_dung'],
            'live.tro_giang_id' => ['nullable', 'different:live.moderator_id', 'exists:nguoi_dung,ma_nguoi_dung'],
            'live.thoi_gian_bat_dau' => ['required_if:loai_bai_giang,' . BaiGiang::TYPE_LIVE, 'date'],
            'live.thoi_luong_phut' => ['required_if:loai_bai_giang,' . BaiGiang::TYPE_LIVE, 'integer', 'min:15', 'max:480'],
            'live.mo_phong_truoc_phut' => ['nullable', 'integer', 'min:0', 'max:180'],
            'live.nhac_truoc_phut' => ['nullable', 'integer', 'min:0', 'max:180'],
            'live.suc_chua_toi_da' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'live.cho_phep_chat' => ['nullable', 'boolean'],
            'live.cho_phep_thao_luan' => ['nullable', 'boolean'],
            'live.cho_phep_chia_se_man_hinh' => ['nullable', 'boolean'],
            'live.tat_mic_khi_vao' => ['nullable', 'boolean'],
            'live.tat_camera_khi_vao' => ['nullable', 'boolean'],
            'live.cho_phep_ghi_hinh' => ['nullable', 'boolean'],
            'live.chi_admin_duoc_ghi_hinh' => ['nullable', 'boolean'],
            'live.tu_dong_gan_ban_ghi' => ['nullable', 'boolean'],
            'live.khoa_copy_noi_dung_mo_ta' => ['nullable', 'boolean'],
            'live.join_url' => [Rule::requiredIf($requiresExternalJoinUrl), 'nullable', 'url', 'max:500'],
            'live.start_url' => ['nullable', 'url', 'max:500'],
            'live.embed_url' => ['nullable', 'url', 'max:500'],
            'live.meeting_id' => ['nullable', 'string', 'max:120'],
            'live.meeting_code' => ['nullable', 'string', 'max:120'],
            'live.passcode' => ['nullable', 'string', 'max:120'],
            'live.host_email' => ['nullable', 'email', 'max:255'],
            'live.host_name' => ['nullable', 'string', 'max:255'],
            'live.waiting_room' => ['nullable', 'boolean'],
            'live.security_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'live.nen_tang_live.required_if' => 'Vui lòng chọn nền tảng cho phòng học trực tuyến.',
            'live.moderator_id.required_if' => 'Vui lòng chọn người điều phối cho phòng học trực tuyến.',
            'live.thoi_gian_bat_dau.required_if' => 'Vui lòng chọn thời gian bắt đầu.',
            'live.thoi_gian_bat_dau.date' => 'Thời gian bắt đầu không hợp lệ. Vui lòng chọn lại từ ô ngày giờ.',
            'live.thoi_luong_phut.required_if' => 'Vui lòng nhập thời lượng dự kiến.',
            'live.join_url.required' => 'Vui lòng nhập liên kết tham gia cho nền tảng bên ngoài.',
            'live.join_url.required_if' => 'Vui lòng nhập liên kết tham gia hợp lệ cho phòng học trực tuyến.',
            'live.join_url.url' => 'Liên kết tham gia không hợp lệ.',
            'live.tro_giang_id.different' => 'Trợ giảng không được trùng với người điều phối.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        if (filled($data['thoi_diem_mo'] ?? null)) {
            $data['thoi_diem_mo'] = $this->normalizeDateTime((string) $data['thoi_diem_mo']);
        }

        if (($data['loai_bai_giang'] ?? null) !== BaiGiang::TYPE_LIVE) {
            $this->merge($data);

            return;
        }

        $live = $data['live'] ?? [];
        $lichHoc = filled($data['lich_hoc_id'] ?? null)
            ? LichHoc::query()->find((int) $data['lich_hoc_id'])
            : null;

        if (filled($live['thoi_gian_bat_dau'] ?? null)) {
            $live['thoi_gian_bat_dau'] = $this->normalizeDateTime((string) $live['thoi_gian_bat_dau']);
        } elseif ($lichHoc?->starts_at) {
            $live['thoi_gian_bat_dau'] = $lichHoc->starts_at->format('Y-m-d H:i:s');
        }

        if (blank($live['thoi_luong_phut'] ?? null) && $lichHoc?->starts_at && $lichHoc?->ends_at) {
            $live['thoi_luong_phut'] = max(15, $lichHoc->starts_at->diffInMinutes($lichHoc->ends_at));
        }

        if (blank($live['join_url'] ?? null) && filled($lichHoc?->link_online)) {
            $live['join_url'] = OnlineMeetingUrl::normalize($lichHoc->link_online);
        }

        if (blank($live['start_url'] ?? null) && filled($live['join_url'] ?? null)) {
            $live['start_url'] = $live['join_url'];
        }

        if (blank($live['nen_tang_live'] ?? null) && $lichHoc) {
            $live['nen_tang_live'] = $this->inferPlatformFromSchedule($lichHoc);
        }

        if (($live['nen_tang_live'] ?? null) === PhongHocLive::PLATFORM_GOOGLE_MEET
            && filled($live['join_url'] ?? null)
            && blank($live['meeting_code'] ?? null)) {
            $live['meeting_code'] = OnlineMeetingUrl::meetingCode($live['join_url']);
        }

        $data['live'] = $live;

        $this->merge($data);
    }

    private function normalizeDateTime(string $value): string
    {
        $value = trim($value);

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function inferPlatformFromSchedule(LichHoc $lichHoc): string
    {
        $signal = strtolower((string) $lichHoc->nen_tang . ' ' . (string) $lichHoc->link_online);

        if (str_contains($signal, 'google') || str_contains($signal, 'meet.google.com')) {
            return PhongHocLive::PLATFORM_GOOGLE_MEET;
        }

        if (str_contains($signal, 'zoom')) {
            return PhongHocLive::PLATFORM_ZOOM;
        }

        return filled($lichHoc->link_online)
            ? PhongHocLive::PLATFORM_ZOOM
            : PhongHocLive::PLATFORM_INTERNAL;
    }
}
