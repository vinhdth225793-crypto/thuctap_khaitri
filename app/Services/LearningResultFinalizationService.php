<?php

namespace App\Services;

use App\Models\KetQuaHocTap;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Validation\ValidationException;

class LearningResultFinalizationService
{
    public function __construct(
        private readonly KetQuaHocTapService $ketQuaHocTapService,
    ) {
    }

    public function finalizeModuleResult(
        PhanCongModuleGiangVien $assignment,
        int $hocVienId,
        int $teacherUserId,
        ?string $note = null
    ): KetQuaHocTap {
        $moduleResult = $this->ketQuaHocTapService->refreshForModuleStudent((int) $assignment->module_hoc_id, $hocVienId);
        $this->ketQuaHocTapService->refreshForCourseStudent((int) $assignment->khoa_hoc_id, $hocVienId);

        if ($moduleResult->diem_tong_ket === null) {
            throw ValidationException::withMessages([
                'hoc_vien_id' => 'Chua du du lieu diem danh va diem kiem tra de chot diem module.',
            ]);
        }

        $metadata = $moduleResult->calculation_metadata ?: [];
        $metadata['teacher_finalization'] = [
            'score' => round((float) $moduleResult->diem_tong_ket, 2),
            'teacher_user_id' => $teacherUserId,
            'finalized_at' => now()->toDateTimeString(),
            'note' => $note,
            'approval_status' => KetQuaHocTap::TRANG_THAI_DUYET_CHO_DUYET,
        ];

        $moduleResult->update([
            'diem_giang_vien_chot' => $moduleResult->diem_tong_ket,
            'trang_thai_chot' => KetQuaHocTap::TRANG_THAI_CHOT_DA_CHOT,
            'chot_boi' => $teacherUserId,
            'chot_luc' => now(),
            'ghi_chu_chot' => $note,
            'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_CHO_DUYET,
            'admin_duyet_id' => null,
            'duyet_luc' => null,
            'ghi_chu_duyet' => null,
            'luu_ho_so_luc' => null,
            'calculation_metadata' => $metadata,
        ]);

        $this->ketQuaHocTapService->refreshForCourseStudent((int) $assignment->khoa_hoc_id, $hocVienId);

        return $moduleResult->refresh();
    }

    public function approveResult(KetQuaHocTap $result, NguoiDung $admin, ?string $note = null): KetQuaHocTap
    {
        $this->ensureResultCanBeReviewed($result);

        $metadata = $result->calculation_metadata ?: [];
        $metadata['admin_approval'] = [
            'status' => KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET,
            'admin_user_id' => (int) $admin->ma_nguoi_dung,
            'reviewed_at' => now()->toDateTimeString(),
            'note' => $note,
        ];

        $result->update([
            'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET,
            'admin_duyet_id' => (int) $admin->ma_nguoi_dung,
            'duyet_luc' => now(),
            'ghi_chu_duyet' => $note,
            'luu_ho_so_luc' => now(),
            'calculation_metadata' => $metadata,
        ]);

        return $result->refresh();
    }

    public function rejectResult(KetQuaHocTap $result, NguoiDung $admin, ?string $note = null): KetQuaHocTap
    {
        $this->ensureResultCanBeReviewed($result);

        $metadata = $result->calculation_metadata ?: [];
        $metadata['admin_approval'] = [
            'status' => KetQuaHocTap::TRANG_THAI_DUYET_TU_CHOI,
            'admin_user_id' => (int) $admin->ma_nguoi_dung,
            'reviewed_at' => now()->toDateTimeString(),
            'note' => $note,
        ];

        $result->update([
            'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_TU_CHOI,
            'admin_duyet_id' => (int) $admin->ma_nguoi_dung,
            'duyet_luc' => now(),
            'ghi_chu_duyet' => $note,
            'luu_ho_so_luc' => null,
            'calculation_metadata' => $metadata,
        ]);

        return $result->refresh();
    }

    private function ensureResultCanBeReviewed(KetQuaHocTap $result): void
    {
        if (
            $result->trang_thai_chot !== KetQuaHocTap::TRANG_THAI_CHOT_DA_CHOT
            || $result->diem_giang_vien_chot === null
        ) {
            throw ValidationException::withMessages([
                'result_id' => 'Ket qua nay chua duoc giang vien chot diem.',
            ]);
        }
    }
}
