<?php

namespace App\Services;

use App\Models\BaiLamBaiKiemTra;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use Illuminate\Support\Collection;

class ExamResultReportDataService
{
    public function __construct(
        private readonly ModuleFinalScoreService $moduleFinalScoreService
    ) {
    }

    /**
     * @return array{khoa_hoc: KhoaHoc, student_results: array<int, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function buildCourseReport(int $khoaHocId): array
    {
        $khoaHoc = KhoaHoc::with(['moduleHocs', 'nhomNganh'])->findOrFail($khoaHocId);
        $enrollments = HocVienKhoaHoc::with('hocVien.nguoiDung')
            ->where('khoa_hoc_id', $khoaHocId)
            ->orderBy('hoc_vien_id')
            ->get();
        $studentIds = $enrollments->pluck('hoc_vien_id')->filter()->values();

        $resultsByStudent = KetQuaHocTap::with(['moduleHoc', 'baiKiemTra'])
            ->where('khoa_hoc_id', $khoaHocId)
            ->whereIn('hoc_vien_id', $studentIds->all())
            ->get()
            ->groupBy('hoc_vien_id');
        $attemptsByStudentExam = BaiLamBaiKiemTra::query()
            ->with([
                'baiKiemTra:id,khoa_hoc_id,module_hoc_id,tieu_de,loai_bai_kiem_tra',
                'nguoiCham:ma_nguoi_dung,ho_ten,email',
            ])
            ->whereIn('hoc_vien_id', $studentIds->all())
            ->whereHas('baiKiemTra', fn ($query) => $query->where('khoa_hoc_id', $khoaHocId))
            ->orderBy('hoc_vien_id')
            ->orderBy('bai_kiem_tra_id')
            ->orderBy('lan_lam_thu')
            ->get()
            ->groupBy(fn (BaiLamBaiKiemTra $attempt) => $attempt->hoc_vien_id . ':' . $attempt->bai_kiem_tra_id);

        $studentResults = $enrollments->map(function (HocVienKhoaHoc $enrollment) use ($resultsByStudent, $attemptsByStudentExam) {
            $allResults = $resultsByStudent->get($enrollment->hoc_vien_id, collect());
            $courseResult = $allResults->whereNull('module_hoc_id')->whereNull('bai_kiem_tra_id')->first();
            $moduleResults = $allResults->whereNotNull('module_hoc_id')->whereNull('bai_kiem_tra_id')->values();
            $examResults = $allResults->whereNotNull('bai_kiem_tra_id')->values();

            $moduleBreakdowns = $moduleResults->map(function ($kq) use ($enrollment) {
                return [
                    'result' => $kq,
                    'breakdown' => $this->moduleFinalScoreService->calculateForStudent((int) $kq->module_hoc_id, (int) $enrollment->hoc_vien_id),
                ];
            })->values();

            return [
                'enrollment' => $enrollment,
                'student' => $enrollment->hocVien?->nguoiDung,
                'course_result' => $courseResult,
                'module_results' => $moduleResults,
                'module_breakdowns' => $moduleBreakdowns,
                'exam_results' => $examResults,
                'attempts_by_exam' => $examResults->mapWithKeys(function (KetQuaHocTap $result) use ($attemptsByStudentExam, $enrollment) {
                    return [
                        $result->bai_kiem_tra_id => $attemptsByStudentExam->get($enrollment->hoc_vien_id . ':' . $result->bai_kiem_tra_id, collect()),
                    ];
                }),
            ];
        })->values()->all();

        $courseResults = collect($studentResults)->pluck('course_result')->filter();
        $reviewableResults = collect($studentResults)
            ->flatMap(fn (array $row) => collect([$row['course_result']])->merge($row['module_results']))
            ->filter();

        return [
            'khoa_hoc' => $khoaHoc,
            'student_results' => $studentResults,
            'summary' => [
                'student_count' => $enrollments->count(),
                'course_result_count' => $courseResults->count(),
                'passed_count' => $courseResults->where('trang_thai', 'dat')->count(),
                'failed_count' => $courseResults->where('trang_thai', 'khong_dat')->count(),
                'pending_approval_count' => $reviewableResults->where('trang_thai_duyet', KetQuaHocTap::TRANG_THAI_DUYET_CHO_DUYET)->count(),
                'archived_count' => $reviewableResults->where('trang_thai_duyet', KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET)->count(),
                'average_score' => $courseResults->isNotEmpty()
                    ? round((float) $courseResults->avg('diem_tong_ket'), 2)
                    : null,
            ],
        ];
    }

    /**
     * @return Collection<int, KhoaHoc>
     */
    public function courseOptions(): Collection
    {
        return KhoaHoc::query()
            ->withCount('hocVienKhoaHocs')
            ->orderByDesc('created_at')
            ->get();
    }
}
