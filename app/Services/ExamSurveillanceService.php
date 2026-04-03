<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\BaiLamViPhamGiamSat;
use Illuminate\Http\Request;

class ExamSurveillanceService
{
    public const DEFAULT_MAX_VIOLATIONS = 3;
    public const DEFAULT_SNAPSHOT_INTERVAL = 30;
    public const MIN_SNAPSHOT_INTERVAL = 10;
    public const MAX_SNAPSHOT_INTERVAL = 300;

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function normalizeExamConfig(array $validated, Request $request): array
    {
        $coGiamSat = $request->boolean('co_giam_sat');

        if (!$coGiamSat) {
            return [
                'co_giam_sat' => false,
                'bat_buoc_fullscreen' => false,
                'bat_buoc_camera' => false,
                'so_lan_vi_pham_toi_da' => self::DEFAULT_MAX_VIOLATIONS,
                'chu_ky_snapshot_giay' => self::DEFAULT_SNAPSHOT_INTERVAL,
                'tu_dong_nop_khi_vi_pham' => false,
                'chan_copy_paste' => false,
                'chan_chuot_phai' => false,
            ];
        }

        $batBuocCamera = $request->boolean('bat_buoc_camera');

        return [
            'co_giam_sat' => true,
            'bat_buoc_fullscreen' => $request->boolean('bat_buoc_fullscreen'),
            'bat_buoc_camera' => $batBuocCamera,
            'so_lan_vi_pham_toi_da' => max(1, (int) ($validated['so_lan_vi_pham_toi_da'] ?? self::DEFAULT_MAX_VIOLATIONS)),
            'chu_ky_snapshot_giay' => $batBuocCamera
                ? max(
                    self::MIN_SNAPSHOT_INTERVAL,
                    min(
                        self::MAX_SNAPSHOT_INTERVAL,
                        (int) ($validated['chu_ky_snapshot_giay'] ?? self::DEFAULT_SNAPSHOT_INTERVAL)
                    )
                )
                : self::DEFAULT_SNAPSHOT_INTERVAL,
            'tu_dong_nop_khi_vi_pham' => $request->boolean('tu_dong_nop_khi_vi_pham'),
            'chan_copy_paste' => $request->boolean('chan_copy_paste'),
            'chan_chuot_phai' => $request->boolean('chan_chuot_phai'),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $precheckData
     * @return array<string, mixed>
     */
    public function resolveAttemptStartPayload(BaiKiemTra $baiKiemTra, Request $request, ?array $precheckData = null): array
    {
        return [
            'dia_chi_ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
            'precheck_data' => $baiKiemTra->co_giam_sat ? $precheckData : null,
            'precheck_completed_at' => $baiKiemTra->co_giam_sat ? now() : null,
            'tong_so_vi_pham' => 0,
            'trang_thai_giam_sat' => $baiKiemTra->co_giam_sat ? 'binh_thuong' : 'khong_ap_dung',
            'da_tu_dong_nop' => false,
        ];
    }

    public function finalizeAttempt(BaiLamBaiKiemTra $baiLam): BaiLamBaiKiemTra
    {
        $baiLam->loadMissing('baiKiemTra');

        if (!$baiLam->baiKiemTra || !$baiLam->baiKiemTra->co_giam_sat) {
            $baiLam->forceFill([
                'trang_thai_giam_sat' => 'khong_ap_dung',
            ])->save();

            return $baiLam->fresh(['baiKiemTra']);
        }

        if (in_array($baiLam->trang_thai_giam_sat, ['da_xac_nhan', 'nghi_ngo'], true)) {
            return $baiLam->fresh(['baiKiemTra']);
        }

        $nguong = max(1, (int) ($baiLam->baiKiemTra->so_lan_vi_pham_toi_da ?: self::DEFAULT_MAX_VIOLATIONS));
        $coNhieuLoiSnapshot = $baiLam->giamSatLogs()
            ->where('loai_su_kien', BaiLamViPhamGiamSat::SU_KIEN_SNAPSHOT_FAILED)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count() >= 3;

        $trangThai = ((int) $baiLam->tong_so_vi_pham >= $nguong || $coNhieuLoiSnapshot)
            ? 'can_xem_xet'
            : 'binh_thuong';

        $baiLam->forceFill([
            'trang_thai_giam_sat' => $trangThai,
        ])->save();

        return $baiLam->fresh(['baiKiemTra']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateReview(BaiLamBaiKiemTra $baiLam, array $payload, int $reviewerId): BaiLamBaiKiemTra
    {
        $baiLam->forceFill([
            'trang_thai_giam_sat' => $payload['trang_thai_giam_sat'],
            'ghi_chu_giam_sat' => $payload['ghi_chu_giam_sat'] ?? null,
            'nguoi_hau_kiem_id' => $reviewerId,
            'hau_kiem_luc' => now(),
        ])->save();

        return $baiLam->fresh(['nguoiHauKiem']);
    }

    /**
     * @return array<string, string>
     */
    public function reviewStatusOptions(): array
    {
        return [
            'binh_thuong' => 'Bình thường',
            'can_xem_xet' => 'Cần xem xét',
            'da_xac_nhan' => 'Đã xác nhận',
            'nghi_ngo' => 'Nghi ngờ',
        ];
    }

    /**
     * @return array<string, int>
     */
    public function summarizeLogs(BaiLamBaiKiemTra $baiLam): array
    {
        $counts = $baiLam->giamSatLogs()
            ->selectRaw('loai_su_kien, COUNT(*) as total')
            ->groupBy('loai_su_kien')
            ->pluck('total', 'loai_su_kien')
            ->map(fn ($total) => (int) $total)
            ->all();

        return [
            'tab_switch' => (int) ($counts[BaiLamViPhamGiamSat::SU_KIEN_TAB_SWITCH] ?? 0),
            'window_blur' => (int) ($counts[BaiLamViPhamGiamSat::SU_KIEN_WINDOW_BLUR] ?? 0),
            'fullscreen_exit' => (int) ($counts[BaiLamViPhamGiamSat::SU_KIEN_FULLSCREEN_EXIT] ?? 0),
            'camera_off' => (int) ($counts[BaiLamViPhamGiamSat::SU_KIEN_CAMERA_OFF] ?? 0),
            'snapshot_failed' => (int) ($counts[BaiLamViPhamGiamSat::SU_KIEN_SNAPSHOT_FAILED] ?? 0),
            'snapshot_captured' => (int) ($counts[BaiLamViPhamGiamSat::SU_KIEN_SNAPSHOT_CAPTURED] ?? 0),
        ];
    }
}
