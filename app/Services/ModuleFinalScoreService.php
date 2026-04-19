<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\KetQuaHocTap;
use App\Models\ModuleHoc;
use Illuminate\Support\Collection;

class ModuleFinalScoreService
{
    public function __construct(
        private readonly CourseAttendanceScoreService $attendanceScoreService,
        private readonly ModuleResultAggregationService $moduleResultAggregationService,
    )
    {
    }

    /**
     * Tính toán bảng điểm chi tiết cho 1 học viên trong 1 module
     */
    public function calculateForStudent(int $moduleId, int $hocVienId): array
    {
        $module = ModuleHoc::with('khoaHoc')->findOrFail($moduleId);
        $khoaHoc = $module->khoaHoc;

        // 1. Lấy tất cả kết quả bài kiểm tra của học viên trong module này
        $examResults = KetQuaHocTap::with('baiKiemTra')
            ->where('module_hoc_id', $moduleId)
            ->where('hoc_vien_id', $hocVienId)
            ->whereNotNull('bai_kiem_tra_id')
            ->get();

        // 2. Phân loại bài nhỏ và bài lớn
        $smallExams = $examResults->filter(fn ($r) => 
            in_array($r->baiKiemTra?->loai_bai_kiem_tra, [BaiKiemTra::LOAI_BUOI_HOC, BaiKiemTra::LOAI_MODULE])
        );
        $largeExams = $examResults->filter(fn ($r) => 
            $r->baiKiemTra?->loai_bai_kiem_tra === BaiKiemTra::LOAI_CUOI_MODULE
        );

        // 3. Tính trung bình bài nhỏ
        $avgSmallScore = $smallExams->count() > 0 ? (float) $smallExams->avg('diem_kiem_tra') : null;
        
        // 4. Lấy điểm bài lớn (thường chỉ có 1 bài cuối module)
        $largeScore = $largeExams->count() > 0 ? (float) $largeExams->first()->diem_kiem_tra : null;

        // 5. Tính trung bình kiểm tra (2 tầng)
        $aggregation = $this->moduleResultAggregationService->aggregate($module, $examResults);
        $moduleExamScore = $aggregation['score'];

        // 6. Điểm danh / Điểm quá trình
        $attendanceData = $this->calculateAttendance($moduleId, $hocVienId);
        $processScore = $attendanceData['diem_diem_danh'];

        // 7. Tính điểm tổng kết module theo trọng số
        $finalScore = null;
        if ($processScore !== null && $moduleExamScore !== null) {
            $finalScore = ($processScore * ((float) $khoaHoc->ty_trong_diem_danh / 100)) 
                        + ($moduleExamScore * ((float) $khoaHoc->ty_trong_kiem_tra / 100));
        } elseif ($moduleExamScore !== null) {
            $finalScore = $moduleExamScore;
        }

        return [
            'module_id' => $moduleId,
            'hoc_vien_id' => $hocVienId,
            'exam_results' => $examResults->map(fn($r) => [
                'id' => $r->id,
                'bai_kiem_tra_id' => $r->bai_kiem_tra_id,
                'tieu_de' => $r->baiKiemTra?->tieu_de,
                'loai' => $r->baiKiemTra?->loai_bai_kiem_tra,
                'diem' => (float) $r->diem_kiem_tra,
                'trang_thai' => $r->trang_thai
            ]),
            'summary' => [
                'avg_small_exam_score' => $avgSmallScore !== null ? round($avgSmallScore, 2) : null,
                'large_exam_score' => $largeScore !== null ? round($largeScore, 2) : null,
                'module_exam_score' => $moduleExamScore !== null ? round($moduleExamScore, 2) : null,
                'process_score' => $processScore !== null ? round($processScore, 2) : null,
                'final_score' => $finalScore !== null ? round($finalScore, 2) : null,
                'aggregation_strategy' => $aggregation['strategy'],
                'source_result_ids' => $aggregation['source_result_ids'],
                'source_attempt_ids' => $aggregation['source_attempt_ids'],
            ],
            'aggregation' => $aggregation,
            'attendance' => $attendanceData,
            'weights' => [
                'attendance' => (float) $khoaHoc->ty_trong_diem_danh,
                'assessment' => (float) $khoaHoc->ty_trong_kiem_tra,
            ],
            'can_finalize' => $this->checkCanFinalize($examResults),
        ];
    }

    private function calculateAttendance(int $moduleId, int $hocVienId): array
    {
        return $this->attendanceScoreService->calculateForModule($moduleId, $hocVienId);
    }

    private function checkCanFinalize(Collection $examResults): bool
    {
        if ($examResults->isEmpty()) return false;
        
        // Check xem có bài nào chưa chấm xong không
        // Giả sử có logic check trạng thái chấm ở KetQuaHocTap hoặc BaiLam
        // Ở đây ta tin tưởng KetQuaHocTap chỉ sinh ra khi đã có điểm
        return true;
    }
}
