<?php

namespace App\Services;

use App\Models\BaiGiang;
use App\Models\LopHoc;
use App\Models\NguoiDung;
use App\Models\PhongHocLive;
use App\Models\PhongHocLiveBanGhi;
use App\Support\OnlineMeetingUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
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
        $platform = $liveInput['nen_tang_live'] ?? PhongHocLive::PLATFORM_ZOOM;
        $platformPayload = $this->platformService->buildPlatformPayload($platform, $liveInput);
        $externalMeetingUrl = $platform === PhongHocLive::PLATFORM_INTERNAL
            ? null
            : OnlineMeetingUrl::normalize($platformPayload['join_url'] ?? ($platformPayload['start_url'] ?? null));
        $externalMeetingCode = $platformPayload['meeting_code']
            ?? $platformPayload['meeting_id']
            ?? OnlineMeetingUrl::meetingCode($externalMeetingUrl);

        $approvalStatus = $this->resolveApprovalStatus($action, $isAdmin);
        $publishStatus = $approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET
            ? ($baiGiang->trang_thai_cong_bo ?: BaiGiang::CONG_BO_AN)
            : BaiGiang::CONG_BO_AN;

        $roomData = [
            'lop_hoc_id' => $this->resolveClassId($baiGiang),
            'giang_vien_id' => $actor->giangVien?->id ?? 0,
            'moderator_id' => $liveInput['moderator_id'] ?? null,
            'tro_giang_id' => $liveInput['tro_giang_id'] ?? null,
            'tieu_de' => $liveInput['tieu_de'] ?? $baiGiang->tieu_de,
            'nen_tang' => $platform,
            'platform_type' => $platform,
            'external_meeting_url' => $externalMeetingUrl,
            'external_meeting_code' => $externalMeetingCode,
            'bat_dau_du_kien' => $liveInput['thoi_gian_bat_dau'] ?? ($baiGiang->thoi_diem_mo ?? now()),
            'thoi_luong_phut' => $liveInput['thoi_luong_phut'] ?? 90,
            'ket_thuc_du_kien' => \Carbon\Carbon::parse($liveInput['thoi_gian_bat_dau'] ?? ($baiGiang->thoi_diem_mo ?? now()))->addMinutes($liveInput['thoi_luong_phut'] ?? 90),
            'trang_thai' => $this->normalizeRoomState($baiGiang->phongHocLive?->trang_thai),
            'trang_thai_duyet' => $approvalStatus,
            'trang_thai_cong_bo' => $publishStatus,
            'du_lieu_nen_tang' => $platformPayload,
        ];

        if ($approvalStatus === BaiGiang::STATUS_DUYET_DA_DUYET) {
            $roomData['approved_by'] = $actor->ma_nguoi_dung;
            $roomData['approved_at'] = now();
        }

        if (!$baiGiang->phongHocLive) {
            $roomData['created_by'] = $actor->ma_nguoi_dung;
        }

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

    private function normalizeRoomState(?string $state): string
    {
        return in_array($state, ['cho', 'dang_dien_ra', 'da_ket_thuc', 'huy'], true)
            ? $state
            : 'cho';
    }

    private function resolveClassId(BaiGiang $baiGiang): int
    {
        $lichHoc = $baiGiang->relationLoaded('lichHoc')
            ? $baiGiang->lichHoc
            : $baiGiang->lichHoc()->first();

        if ($lichHoc?->lop_hoc_id) {
            return (int) $lichHoc->lop_hoc_id;
        }

        if (! $baiGiang->khoa_hoc_id || ! Schema::hasTable('lop_hoc')) {
            return 0;
        }

        $lopHoc = LopHoc::query()
            ->where('khoa_hoc_id', $baiGiang->khoa_hoc_id)
            ->orderBy('id')
            ->first();

        if (! $lopHoc) {
            $lopHoc = LopHoc::create([
                'khoa_hoc_id' => $baiGiang->khoa_hoc_id,
                'ma_lop_hoc' => 'AUTO-KH-' . $baiGiang->khoa_hoc_id,
                'ngay_khai_giang' => $lichHoc?->ngay_hoc,
                'trang_thai_van_hanh' => 'dang_day',
                'ghi_chu' => 'Tu dong tao khi tao phong hoc live cho khoa hoc chua co lop.',
                'created_by' => $baiGiang->nguoi_tao_id,
            ]);
        }

        if ($lichHoc && ! $lichHoc->lop_hoc_id) {
            $lichHoc->forceFill(['lop_hoc_id' => $lopHoc->id])->save();
        }

        return (int) $lopHoc->id;
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
