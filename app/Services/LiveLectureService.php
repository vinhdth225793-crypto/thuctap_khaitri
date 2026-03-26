<?php

namespace App\Services;

use App\Models\BaiGiang;
use App\Models\NguoiDung;
use App\Models\PhongHocLive;
use App\Models\PhongHocLiveBanGhi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LiveLectureService
{
    public function __construct(
        private readonly LiveRoomPlatformService $platformService
    ) {
    }

    public function syncLiveRoom(BaiGiang $baiGiang, array $validated, NguoiDung $actor, bool $isAdmin = false): ?PhongHocLive
    {
        if (($validated['loai_bai_giang'] ?? null) !== BaiGiang::TYPE_LIVE) {
            $baiGiang->phongHocLive()?->delete();

            return null;
        }

        $liveInput = $validated['live'] ?? [];
        $action = $validated['hanh_dong'] ?? 'luu_nhap';

        $approvalStatus = $this->resolveApprovalStatus($action, $isAdmin);
        $publishStatus = $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET
            ? ($baiGiang->trang_thai_cong_bo ?: BaiGiang::CONG_BO_AN)
            : BaiGiang::CONG_BO_AN;

        $roomData = [
            'nen_tang_live' => $liveInput['nen_tang_live'] ?? PhongHocLive::PLATFORM_ZOOM,
            'loai_live' => $liveInput['loai_live'] ?? PhongHocLive::TYPE_CLASS,
            'tieu_de' => $liveInput['tieu_de'] ?? $baiGiang->tieu_de,
            'mo_ta' => $liveInput['mo_ta'] ?? $baiGiang->mo_ta,
            'moderator_id' => $liveInput['moderator_id'] ?? $baiGiang->nguoi_tao_id,
            'tro_giang_id' => $liveInput['tro_giang_id'] ?? null,
            'thoi_gian_bat_dau' => $liveInput['thoi_gian_bat_dau'] ?? ($baiGiang->thoi_diem_mo ?? now()),
            'thoi_luong_phut' => $liveInput['thoi_luong_phut'] ?? 90,
            'mo_phong_truoc_phut' => $liveInput['mo_phong_truoc_phut'] ?? config('live_room.defaults.open_before_minutes', 15),
            'nhac_truoc_phut' => $liveInput['nhac_truoc_phut'] ?? config('live_room.defaults.reminder_minutes', 10),
            'suc_chua_toi_da' => $liveInput['suc_chua_toi_da'] ?? null,
            'cho_phep_chat' => (bool) ($liveInput['cho_phep_chat'] ?? true),
            'cho_phep_thao_luan' => (bool) ($liveInput['cho_phep_thao_luan'] ?? true),
            'cho_phep_chia_se_man_hinh' => (bool) ($liveInput['cho_phep_chia_se_man_hinh'] ?? false),
            'tat_mic_khi_vao' => (bool) ($liveInput['tat_mic_khi_vao'] ?? true),
            'tat_camera_khi_vao' => (bool) ($liveInput['tat_camera_khi_vao'] ?? true),
            'cho_phep_ghi_hinh' => (bool) ($liveInput['cho_phep_ghi_hinh'] ?? false),
            'chi_admin_duoc_ghi_hinh' => (bool) ($liveInput['chi_admin_duoc_ghi_hinh'] ?? false),
            'tu_dong_gan_ban_ghi' => (bool) ($liveInput['tu_dong_gan_ban_ghi'] ?? false),
            'khoa_copy_noi_dung_mo_ta' => (bool) ($liveInput['khoa_copy_noi_dung_mo_ta'] ?? false),
            'trang_thai_duyet' => $approvalStatus,
            'trang_thai_cong_bo' => $publishStatus,
            'trang_thai_phong' => $baiGiang->phongHocLive?->trang_thai_phong ?? PhongHocLive::ROOM_STATE_CHUA_MO,
            'du_lieu_nen_tang_json' => $this->platformService->buildPlatformPayload(
                $liveInput['nen_tang_live'] ?? PhongHocLive::PLATFORM_ZOOM,
                $liveInput
            ),
            'created_by' => $baiGiang->phongHocLive?->created_by ?? $actor->ma_nguoi_dung,
            'approved_by' => $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? $actor->ma_nguoi_dung : null,
            'approved_at' => $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? now() : null,
        ];

        return $baiGiang->phongHocLive()->updateOrCreate(
            ['bai_giang_id' => $baiGiang->id],
            $roomData
        );
    }

    public function syncApprovalState(BaiGiang $baiGiang, string $approvalStatus, ?string $ghiChuAdmin, NguoiDung $admin): void
    {
        $baiGiang->update([
            'trang_thai_duyet' => $approvalStatus,
            'ghi_chu_admin' => $ghiChuAdmin,
            'ngay_duyet' => now(),
            'nguoi_duyet_id' => $admin->ma_nguoi_dung,
            'trang_thai_cong_bo' => $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET
                ? $baiGiang->trang_thai_cong_bo
                : BaiGiang::CONG_BO_AN,
        ]);

        if ($baiGiang->phongHocLive) {
            $baiGiang->phongHocLive->update([
                'trang_thai_duyet' => $approvalStatus,
                'trang_thai_cong_bo' => $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET
                    ? $baiGiang->trang_thai_cong_bo
                    : PhongHocLive::PUBLISH_AN,
                'approved_by' => $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? $admin->ma_nguoi_dung : null,
                'approved_at' => $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? now() : null,
            ]);
        }
    }

    public function togglePublication(BaiGiang $baiGiang): string
    {
        $trangThaiMoi = $baiGiang->trang_thai_cong_bo === BaiGiang::CONG_BO_DA_CONG_BO
            ? BaiGiang::CONG_BO_AN
            : BaiGiang::CONG_BO_DA_CONG_BO;

        $baiGiang->update(['trang_thai_cong_bo' => $trangThaiMoi]);

        if ($baiGiang->phongHocLive) {
            $baiGiang->phongHocLive->update([
                'trang_thai_cong_bo' => $trangThaiMoi,
            ]);
        }

        return $trangThaiMoi;
    }

    public function addRecording(PhongHocLive $phongHocLive, array $validated): PhongHocLiveBanGhi
    {
        $filePath = null;

        if (($validated['file_ban_ghi'] ?? null) instanceof UploadedFile) {
            $filePath = $validated['file_ban_ghi']->store('live-recordings', 'public');
        }

        return $phongHocLive->banGhis()->create([
            'nguon_ban_ghi' => $validated['nguon_ban_ghi'],
            'tieu_de' => $validated['tieu_de'],
            'duong_dan_file' => $filePath,
            'link_ngoai' => $validated['link_ngoai'] ?? null,
            'thoi_luong' => $validated['thoi_luong'] ?? null,
            'trang_thai' => 'san_sang',
        ]);
    }

    public function deleteRecording(PhongHocLiveBanGhi $banGhi): void
    {
        if ($banGhi->duong_dan_file && Storage::disk('public')->exists($banGhi->duong_dan_file)) {
            Storage::disk('public')->delete($banGhi->duong_dan_file);
        }

        $banGhi->delete();
    }

    private function resolveApprovalStatus(string $action, bool $isAdmin): string
    {
        if ($isAdmin && $action === 'duyet_ngay') {
            return BaiGiang::STATUS_DUYET_DA_DUYET;
        }

        if ($action === 'gui_duyet') {
            return BaiGiang::STATUS_DUYET_CHO;
        }

        return BaiGiang::STATUS_DUYET_NHAP;
    }
}
