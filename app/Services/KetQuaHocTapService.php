<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\DiemDanh;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;

class KetQuaHocTapService
{
    public function __construct(
        private readonly ExamResultAggregationService $examAggregationService,
        private readonly ModuleResultAggregationService $moduleAggregationService,
        private readonly CourseResultAggregationService $courseAggregationService,
        private readonly ModuleFinalScoreService $moduleFinalScoreService,
    ) {
    }

    public function refreshAllForCourseStudent(int $khoaHocId, int $hocVienId): void
    {
        $khoaHoc = KhoaHoc::with('moduleHocs')->findOrFail($khoaHocId);

        foreach ($khoaHoc->moduleHocs as $module) {
            $this->refreshForModuleStudent($module->id, $hocVienId);
        }

        BaiKiemTra::query()
            ->where('khoa_hoc_id', $khoaHocId)
            ->where(function ($query) {
                $query->where('loai_bai_kiem_tra', 'cuoi_khoa')
                    ->orWhereNull('module_hoc_id');
            })
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai_phat_hanh', 'phat_hanh')
            ->get(['id'])
            ->each(fn (BaiKiemTra $baiKiemTra) => $this->refreshForExamStudent($baiKiemTra->id, $hocVienId));

        $this->refreshForCourseStudent($khoaHocId, $hocVienId);
    }

    public function refreshForCourseStudent(int $khoaHocId, int $hocVienId): KetQuaHocTap
    {
        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);
        $attendance = $this->calculateAttendanceForCourse($khoaHocId, $hocVienId);
        $tongSoBuoi = $attendance['tong_so_buoi'];
        $soBuoiThamDu = $attendance['so_buoi_tham_du'];
        $tyLeThamDu = $attendance['ty_le_tham_du'];
        $diemDiemDanh = $attendance['diem_diem_danh'];

        $allResults = KetQuaHocTap::query()
            ->with(['moduleHoc', 'baiKiemTra'])
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('hoc_vien_id', $hocVienId)
            ->where(function ($query) {
                $query->whereNotNull('module_hoc_id')
                    ->orWhereNotNull('bai_kiem_tra_id');
            })
            ->get();

        $moduleResults = $allResults
            ->whereNotNull('module_hoc_id')
            ->whereNull('bai_kiem_tra_id')
            ->values();
        $examResults = $allResults
            ->whereNotNull('bai_kiem_tra_id')
            ->values();
        $finalExamResults = $examResults
            ->filter(fn (KetQuaHocTap $result) => $result->baiKiemTra?->loai_bai_kiem_tra === 'cuoi_khoa')
            ->values();

        $aggregation = $this->courseAggregationService->aggregate(
            $khoaHoc,
            $moduleResults,
            $finalExamResults,
            $examResults
        );

        $diemKiemTra = $aggregation['score'];
        $diemTongKet = null;
        if ($diemDiemDanh !== null && $diemKiemTra !== null) {
            $diemTongKet = round(
                ($diemDiemDanh * ((float) $khoaHoc->ty_trong_diem_danh / 100))
                + ($diemKiemTra * ((float) $khoaHoc->ty_trong_kiem_tra / 100)),
                2
            );
        }

        $trangThai = 'dang_hoc';
        if ($diemTongKet !== null) {
            $trangThai = $diemTongKet >= 5.0 ? 'dat' : 'khong_dat';
        }

        if ($trangThai === 'dat') {
            HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)
                ->where('hoc_vien_id', $hocVienId)
                ->where('trang_thai', 'dang_hoc')
                ->update(['trang_thai' => 'hoan_thanh']);
        }

        return KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => $khoaHocId,
                'hoc_vien_id' => $hocVienId,
                'module_hoc_id' => null,
                'bai_kiem_tra_id' => null,
            ],
            [
                'phuong_thuc_danh_gia' => $khoaHoc->phuong_thuc_danh_gia,
                'aggregation_strategy_used' => $aggregation['strategy'],
                'source_attempt_ids' => $aggregation['source_attempt_ids'],
                'diem_diem_danh' => $diemDiemDanh,
                'diem_kiem_tra' => $diemKiemTra,
                'diem_tong_ket' => $diemTongKet,
                'tong_so_buoi' => $tongSoBuoi,
                'so_buoi_tham_du' => $soBuoiThamDu,
                'ty_le_tham_du' => $tyLeThamDu,
                'so_bai_kiem_tra_hoan_thanh' => $aggregation['completed_count'],
                'trang_thai' => $trangThai,
                'chi_tiet' => [
                    'aggregation_strategy' => $aggregation['strategy'],
                    'source_result_ids' => $aggregation['source_result_ids'],
                    'sources' => $aggregation['metadata']['sources'] ?? [],
                ],
                'calculation_metadata' => [
                    'attendance' => [
                        'tong_so_buoi' => $tongSoBuoi,
                        'so_buoi_tham_du' => $soBuoiThamDu,
                        'ty_le_tham_du' => $tyLeThamDu,
                        'diem_diem_danh' => $diemDiemDanh,
                    ],
                    'assessment' => $aggregation['metadata'],
                    'weights' => [
                        'ty_trong_diem_danh' => (float) $khoaHoc->ty_trong_diem_danh,
                        'ty_trong_kiem_tra' => (float) $khoaHoc->ty_trong_kiem_tra,
                    ],
                ],
                'cap_nhat_luc' => now(),
            ]
        );
    }

    public function refreshForModuleStudent(int $moduleHocId, int $hocVienId): KetQuaHocTap
    {
        $existing = KetQuaHocTap::where('module_hoc_id', $moduleHocId)
            ->where('hoc_vien_id', $hocVienId)
            ->whereNull('bai_kiem_tra_id')
            ->first();

        // CƠ CHẾ KHÓA: Nếu giảng viên đã chốt hoặc Admin đã duyệt thì không tự động tính toán đè lên dữ liệu chính thức
        if ($existing && ($existing->da_chot || $existing->trang_thai_duyet === KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET)) {
            return $existing;
        }

        $module = ModuleHoc::with('khoaHoc')->findOrFail($moduleHocId);

        // 1. Refresh từng bài thi trước
        BaiKiemTra::where('module_hoc_id', $moduleHocId)
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai_phat_hanh', 'phat_hanh')
            ->get(['id'])
            ->each(fn (BaiKiemTra $baiKiemTra) => $this->refreshForExamStudent($baiKiemTra->id, $hocVienId));

        // 2. Sử dụng Service chuyên biệt để tính bảng điểm 4 lớp chuẩn nghiệp vụ
        $breakdown = $this->moduleFinalScoreService->calculateForStudent($moduleHocId, $hocVienId);
        $summary = $breakdown['summary'];

        $diemTongKet = $summary['final_score'];
        $trangThai = 'dang_hoc';
        if ($diemTongKet !== null) {
            $trangThai = $diemTongKet >= 5.0 ? 'dat' : 'khong_dat';
        }

        return KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => (int) $module->khoa_hoc_id,
                'hoc_vien_id' => $hocVienId,
                'module_hoc_id' => $moduleHocId,
                'bai_kiem_tra_id' => null,
            ],
            [
                'diem_trung_binh_bai_kiem_tra' => $summary['module_exam_score'],
                'diem_qua_trinh' => $summary['process_score'],
                'diem_diem_danh' => $breakdown['attendance']['diem_diem_danh'],
                'diem_kiem_tra' => $summary['module_exam_score'],
                'diem_tong_ket' => $diemTongKet,
                'tong_so_buoi' => $breakdown['attendance']['tong_so_buoi'],
                'so_buoi_tham_du' => $breakdown['attendance']['so_buoi_tham_du'],
                'ty_le_tham_du' => $breakdown['attendance']['ty_le_tham_du'],
                'so_bai_kiem_tra_hoan_thanh' => collect($breakdown['exam_results'])->where('diem', '!==', null)->count(),
                'trang_thai' => $trangThai,
                'chi_tiet' => [
                    'aggregation_strategy' => 'two_tier_average',
                    'breakdown' => $breakdown,
                ],
                'calculation_metadata' => $breakdown,
                'cap_nhat_luc' => now(),
            ]
        );
    }

    public function refreshForExamStudent(int $baiKiemTraId, int $hocVienId): ?KetQuaHocTap
    {
        $baiKiemTra = BaiKiemTra::findOrFail($baiKiemTraId);
        $attempts = BaiLamBaiKiemTra::where('bai_kiem_tra_id', $baiKiemTraId)
            ->where('hoc_vien_id', $hocVienId)
            ->orderBy('lan_lam_thu')
            ->get();

        $strategy = $baiKiemTra->ket_qua_config['attempt_strategy']
            ?? $baiKiemTra->attempt_strategy
            ?? ExamResultAggregationService::HIGHEST_SCORE;
        $aggregation = $this->examAggregationService->aggregate($attempts, $strategy);

        if ($aggregation['score'] === null) {
            return null;
        }

        $sourceAttempt = $aggregation['source_attempt_id']
            ? $attempts->firstWhere('id', $aggregation['source_attempt_id'])
            : null;
        $trangThai = $aggregation['score'] >= ((float) $baiKiemTra->tong_diem * 0.5) ? 'dat' : 'khong_dat';

        return KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => $baiKiemTra->khoa_hoc_id,
                'hoc_vien_id' => $hocVienId,
                'module_hoc_id' => $baiKiemTra->module_hoc_id,
                'bai_kiem_tra_id' => $baiKiemTraId,
            ],
            [
                'attempt_strategy_used' => $aggregation['strategy'],
                'source_attempt_id' => $aggregation['source_attempt_id'],
                'source_attempt_ids' => $aggregation['source_attempt_ids'],
                'diem_kiem_tra' => $aggregation['score'],
                'diem_tong_ket' => $aggregation['score'],
                'trang_thai' => $trangThai,
                'chi_tiet' => [
                    'bai_lam_id' => $aggregation['source_attempt_id'],
                    'lan_lam_thu' => $sourceAttempt?->lan_lam_thu,
                    'nop_luc' => $sourceAttempt?->nop_luc,
                    'source_attempt_ids' => $aggregation['source_attempt_ids'],
                    'attempt_strategy' => $aggregation['strategy'],
                ],
                'calculation_metadata' => $aggregation['metadata'],
                'cap_nhat_luc' => now(),
            ]
        );
    }

    /**
     * @return array{tong_so_buoi: int, so_buoi_tham_du: int, ty_le_tham_du: float|null, diem_diem_danh: float|null}
     */
    private function calculateAttendanceForModule(int $moduleHocId, int $hocVienId): array
    {
        $scheduleIds = LichHoc::query()
            ->where('module_hoc_id', $moduleHocId)
            ->where('trang_thai', '!=', 'huy')
            ->pluck('id');

        return $this->calculateAttendanceScore($scheduleIds, $hocVienId);
    }

    /**
     * @return array{tong_so_buoi: int, so_buoi_tham_du: int, ty_le_tham_du: float|null, diem_diem_danh: float|null}
     */
    private function calculateAttendanceForCourse(int $khoaHocId, int $hocVienId): array
    {
        $scheduleIds = LichHoc::query()
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('trang_thai', '!=', 'huy')
            ->pluck('id');

        return $this->calculateAttendanceScore($scheduleIds, $hocVienId);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $scheduleIds
     * @return array{tong_so_buoi: int, so_buoi_tham_du: int, ty_le_tham_du: float|null, diem_diem_danh: float|null}
     */
    private function calculateAttendanceScore($scheduleIds, int $hocVienId): array
    {
        $tongSoBuoi = $scheduleIds->count();

        if ($tongSoBuoi === 0) {
            return [
                'tong_so_buoi' => 0,
                'so_buoi_tham_du' => 0,
                'ty_le_tham_du' => null,
                'diem_diem_danh' => null,
            ];
        }

        $soBuoiThamDu = DiemDanh::query()
            ->where('hoc_vien_id', $hocVienId)
            ->whereIn('lich_hoc_id', $scheduleIds->all())
            ->whereIn('trang_thai', ['co_mat', 'vao_tre'])
            ->distinct('lich_hoc_id')
            ->count('lich_hoc_id');

        $tyLeThamDu = round(($soBuoiThamDu / $tongSoBuoi) * 100, 2);

        return [
            'tong_so_buoi' => $tongSoBuoi,
            'so_buoi_tham_du' => $soBuoiThamDu,
            'ty_le_tham_du' => $tyLeThamDu,
            'diem_diem_danh' => round(($tyLeThamDu / 100) * 10, 2),
        ];
    }
}
