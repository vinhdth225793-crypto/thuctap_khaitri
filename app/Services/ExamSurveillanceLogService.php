<?php

namespace App\Services;

use App\Models\BaiLamBaiKiemTra;
use App\Models\BaiLamViPhamGiamSat;

class ExamSurveillanceLogService
{
    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public function recordEvent(BaiLamBaiKiemTra $baiLam, string $eventType, ?string $description = null, array $meta = []): array
    {
        $baiLam->loadMissing('baiKiemTra');

        $laViPham = $this->isViolationEvent($eventType);
        $soLanViPham = (int) $baiLam->tong_so_vi_pham;

        if ($laViPham) {
            $soLanViPham++;
            $baiLam->forceFill([
                'tong_so_vi_pham' => $soLanViPham,
            ])->save();
        }

        $this->createLogRecord(
            $baiLam,
            $eventType,
            $description,
            $laViPham,
            $laViPham ? $soLanViPham : null,
            $meta
        );

        $nguong = max(
            1,
            (int) ($baiLam->baiKiemTra->so_lan_vi_pham_toi_da ?: ExamSurveillanceService::DEFAULT_MAX_VIOLATIONS)
        );

        $coNhieuLoiSnapshot = $baiLam->giamSatLogs()
            ->where('loai_su_kien', BaiLamViPhamGiamSat::SU_KIEN_SNAPSHOT_FAILED)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count() >= 3;

        $canXemXet = $soLanViPham >= $nguong || $coNhieuLoiSnapshot;
        if ($canXemXet && !in_array($baiLam->trang_thai_giam_sat, ['da_xac_nhan', 'nghi_ngo'], true)) {
            $baiLam->forceFill([
                'trang_thai_giam_sat' => 'can_xem_xet',
            ])->save();
        }

        $warningMessage = $this->resolveWarningMessage($eventType, $soLanViPham, $nguong);
        if ($warningMessage !== null) {
            $this->createLogRecord(
                $baiLam,
                BaiLamViPhamGiamSat::SU_KIEN_WARNING_ISSUED,
                $warningMessage,
                false,
                null,
                ['trigger' => $eventType]
            );
        }

        return [
            'violation_count' => $soLanViPham,
            'max_violations' => $nguong,
            'should_review' => $canXemXet,
            'auto_submit_required' => $laViPham
                && $baiLam->can_resume
                && $baiLam->baiKiemTra->tu_dong_nop_khi_vi_pham
                && $soLanViPham >= $nguong,
            'warning_message' => $warningMessage,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function recordAutoSubmit(BaiLamBaiKiemTra $baiLam, array $meta = []): void
    {
        $this->createLogRecord(
            $baiLam,
            BaiLamViPhamGiamSat::SU_KIEN_AUTO_SUBMIT,
            'Hệ thống kích hoạt tự động nộp bài do vượt ngưỡng vi phạm.',
            false,
            null,
            $meta
        );
    }

    private function isViolationEvent(string $eventType): bool
    {
        return in_array($eventType, [
            BaiLamViPhamGiamSat::SU_KIEN_TAB_SWITCH,
            BaiLamViPhamGiamSat::SU_KIEN_FULLSCREEN_EXIT,
            BaiLamViPhamGiamSat::SU_KIEN_CAMERA_OFF,
        ], true);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function createLogRecord(
        BaiLamBaiKiemTra $baiLam,
        string $eventType,
        ?string $description,
        bool $laViPham,
        ?int $soLanViPham,
        array $meta = []
    ): BaiLamViPhamGiamSat {
        return $baiLam->giamSatLogs()->create([
            'loai_su_kien' => $eventType,
            'mo_ta' => $description,
            'la_vi_pham' => $laViPham,
            'so_lan_vi_pham_hien_tai' => $soLanViPham,
            'meta' => $meta === [] ? null : $meta,
        ]);
    }

    private function resolveWarningMessage(string $eventType, int $soLanViPham, int $nguong): ?string
    {
        if ($eventType === BaiLamViPhamGiamSat::SU_KIEN_COPY_PASTE_BLOCKED) {
            return 'Thao tác copy/paste đã bị chặn trong bài thi giám sát.';
        }

        if ($eventType === BaiLamViPhamGiamSat::SU_KIEN_RIGHT_CLICK_BLOCKED) {
            return 'Thao tác chuột phải đã bị chặn trong bài thi giám sát.';
        }

        if (!$this->isViolationEvent($eventType)) {
            return null;
        }

        if ($soLanViPham >= $nguong) {
            return 'Bạn đã vượt ngưỡng vi phạm cho phép. Hệ thống sẽ đánh dấu bài làm để hậu kiểm.';
        }

        if ($soLanViPham >= 2) {
            return 'Bạn đang vi phạm quy chế thi. Nếu tiếp tục, bài làm sẽ bị đánh dấu cần xem xét.';
        }

        return 'Hệ thống đã ghi nhận một vi phạm. Vui lòng quay lại đúng chế độ làm bài.';
    }
}
