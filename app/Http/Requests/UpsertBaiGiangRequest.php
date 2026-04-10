<?php

namespace App\Http\Requests;

use App\Models\BaiGiang;
use App\Models\PhongHocLive;
use Illuminate\Foundation\Http\FormRequest;
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
            'live.join_url' => ['required_if:loai_bai_giang,' . BaiGiang::TYPE_LIVE, 'nullable', 'url', 'max:500'],
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
            'live.thoi_luong_phut.required_if' => 'Vui lòng nhập thời lượng dự kiến.',
            'live.join_url.required_if' => 'Vui lòng nhập liên kết tham gia hợp lệ cho phòng học trực tuyến.',
            'live.tro_giang_id.different' => 'Trợ giảng không được trùng với người điều phối.',
        ];
    }
}
