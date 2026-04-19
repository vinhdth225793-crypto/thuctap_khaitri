<?php

namespace App\Services;

use App\Models\KetQuaHocTap;
use App\Models\KetQuaHocTapChotLog;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LearningResultFinalizationService
{
    public function __construct(
        private readonly KetQuaHocTapService $ketQuaHocTapService,
        private readonly ModuleFinalScoreService $moduleFinalScoreService,
    ) {
    }

    public function finalizeModuleResult(
        PhanCongModuleGiangVien $assignment,
        int $hocVienId,
        int $teacherUserId,
        ?string $note = null
    ): KetQuaHocTap {
        $moduleId = (int) $assignment->module_hoc_id;
        
        return DB::transaction(function () use ($assignment, $hocVienId, $teacherUserId, $note, $moduleId) {
            // 1. Refresh dữ liệu tạm tính mới nhất
            $data = $this->moduleFinalScoreService->calculateForStudent($moduleId, $hocVienId);

            if ($data['summary']['final_score'] === null) {
                throw ValidationException::withMessages([
                    'hoc_vien_id' => 'Chưa đủ dữ liệu điểm danh và điểm kiểm tra để chốt điểm module.',
                ]);
            }

            // 2. Tìm bản ghi kết quả để cập nhật
            $moduleResult = KetQuaHocTap::where('module_hoc_id', $moduleId)
                ->where('hoc_vien_id', $hocVienId)
                ->whereNull('bai_kiem_tra_id')
                ->firstOrFail();

            $diemTruoc = $moduleResult->diem_giang_vien_chot;
            $hanhDong = $moduleResult->da_chot ? 'cap_nhat' : 'chot';

            $metadata = $moduleResult->calculation_metadata ?: [];
            $metadata['teacher_finalization'] = [
                'score' => (float) $data['summary']['final_score'],
                'teacher_user_id' => $teacherUserId,
                'finalized_at' => now()->toDateTimeString(),
                'note' => $note,
                'approval_status' => KetQuaHocTap::TRANG_THAI_DUYET_CHO_DUYET,
                'breakdown' => $data,
            ];

            // 3. Lưu chính thức dữ liệu chốt
            $moduleResult->update([
                'diem_trung_binh_bai_kiem_tra' => $data['summary']['module_exam_score'],
                'diem_qua_trinh' => $data['summary']['process_score'],
                'diem_giang_vien_chot' => $data['summary']['final_score'],
                'da_chot' => true,
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
                'chi_tiet' => array_merge($moduleResult->chi_tiet ?: [], ['finalized_data' => $data]),
            ]);

            // 4. Audit Log
            KetQuaHocTapChotLog::create([
                'ket_qua_hoc_tap_id' => $moduleResult->id,
                'hoc_vien_id' => $hocVienId,
                'module_hoc_id' => $moduleId,
                'khoa_hoc_id' => (int) $assignment->khoa_hoc_id,
                'nguoi_thuc_hien_id' => $teacherUserId,
                'hanh_dong' => $hanhDong,
                'diem_truoc' => $diemTruoc,
                'diem_sau' => $data['summary']['final_score'],
                'ly_do' => $note,
                'metadata' => $data,
            ]);

            // 5. Đồng bộ lại kết quả cấp khóa học
            $this->ketQuaHocTapService->refreshForCourseStudent((int) $assignment->khoa_hoc_id, $hocVienId);

            return $moduleResult->refresh();
        });
    }

    public function unlockModuleResult(
        KetQuaHocTap $result,
        int $userId,
        string $reason
    ): KetQuaHocTap {
        if (!$result->da_chot) {
            throw ValidationException::withMessages(['result_id' => 'Kết quả này chưa được chốt.']);
        }

        return DB::transaction(function () use ($result, $userId, $reason) {
            $diemTruoc = $result->diem_giang_vien_chot;

            $result->update([
                'da_chot' => false,
                'trang_thai_chot' => KetQuaHocTap::TRANG_THAI_CHOT_CHUA_CHOT,
                'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_CHUA_GUI,
                // Giữ lại điểm chốt cũ để tham khảo nhưng cho phép refresh lại
            ]);

            KetQuaHocTapChotLog::create([
                'ket_qua_hoc_tap_id' => $result->id,
                'hoc_vien_id' => $result->hoc_vien_id,
                'module_hoc_id' => $result->module_hoc_id,
                'khoa_hoc_id' => $result->khoa_hoc_id,
                'nguoi_thuc_hien_id' => $userId,
                'hanh_dong' => 'mo_chot',
                'diem_truoc' => $diemTruoc,
                'diem_sau' => null,
                'ly_do' => $reason,
                'metadata' => [
                    'unlocked_at' => now()->toDateTimeString(),
                    'reason' => $reason,
                ],
            ]);

            return $result->refresh();
        });
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

        return DB::transaction(function () use ($result, $admin, $note, $metadata) {
            $result->update([
                'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET,
                'admin_duyet_id' => (int) $admin->ma_nguoi_dung,
                'duyet_luc' => now(),
                'ghi_chu_duyet' => $note,
                'luu_ho_so_luc' => now(),
                'calculation_metadata' => $metadata,
            ]);

            KetQuaHocTapChotLog::create([
                'ket_qua_hoc_tap_id' => $result->id,
                'hoc_vien_id' => $result->hoc_vien_id,
                'module_hoc_id' => $result->module_hoc_id,
                'khoa_hoc_id' => $result->khoa_hoc_id,
                'nguoi_thuc_hien_id' => (int) $admin->ma_nguoi_dung,
                'hanh_dong' => 'duyet_luu_ho_so',
                'diem_truoc' => $result->diem_giang_vien_chot,
                'diem_sau' => $result->diem_giang_vien_chot,
                'ly_do' => $note,
            ]);

            return $result->refresh();
        });
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

        return DB::transaction(function () use ($result, $admin, $note, $metadata) {
            $result->update([
                'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_TU_CHOI,
                'admin_duyet_id' => (int) $admin->ma_nguoi_dung,
                'duyet_luc' => now(),
                'ghi_chu_duyet' => $note,
                'luu_ho_so_luc' => null,
                'calculation_metadata' => $metadata,
            ]);

            KetQuaHocTapChotLog::create([
                'ket_qua_hoc_tap_id' => $result->id,
                'hoc_vien_id' => $result->hoc_vien_id,
                'module_hoc_id' => $result->module_hoc_id,
                'khoa_hoc_id' => $result->khoa_hoc_id,
                'nguoi_thuc_hien_id' => (int) $admin->ma_nguoi_dung,
                'hanh_dong' => 'tu_choi',
                'diem_truoc' => $result->diem_giang_vien_chot,
                'diem_sau' => $result->diem_giang_vien_chot,
                'ly_do' => $note,
            ]);

            return $result->refresh();
        });
    }

    private function ensureResultCanBeReviewed(KetQuaHocTap $result): void
    {
        if (
            $result->trang_thai_chot !== KetQuaHocTap::TRANG_THAI_CHOT_DA_CHOT
            || $result->diem_giang_vien_chot === null
        ) {
            throw ValidationException::withMessages([
                'result_id' => 'Kết quả này chưa được giảng viên chốt điểm.',
            ]);
        }
    }
}
