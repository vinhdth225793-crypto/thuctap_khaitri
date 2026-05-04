<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GiangVien\Concerns\XacThucGiangVienBaiKiemTra;
use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\KetQuaHocTap;
use App\Services\BaiKiemTraScoringService;
use App\Services\ExamAttemptReportExportService;
use App\Services\ExamSurveillanceService;
use App\Services\KetQuaHocTapService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChamDiemController extends Controller
{
    use XacThucGiangVienBaiKiemTra;

    public function __construct(
        private readonly BaiKiemTraScoringService $scoringService,
        private readonly KetQuaHocTapService $ketQuaHocTapService,
        private readonly ExamSurveillanceService $surveillanceService,
        private readonly ExamAttemptReportExportService $attemptReportExportService,
    ) {}

    public function diemKiemTraIndex(Request $request)
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(! $giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        $moduleIds = $this->getAcceptedModuleIds($giangVien);
        $courseIds = $this->getAcceptedCourseIds($giangVien);
        $submittedStatuses = ['da_nop', 'cho_cham', 'da_cham'];

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'bai_kiem_tra_id' => $request->filled('bai_kiem_tra_id') ? $request->integer('bai_kiem_tra_id') : null,
            'loai_bai_kiem_tra' => $request->filled('loai_bai_kiem_tra') ? (string) $request->query('loai_bai_kiem_tra') : null,
            'trang_thai_cham' => $request->filled('trang_thai_cham') ? (string) $request->query('trang_thai_cham') : null,
        ];

        $examOptions = $this->buildAccessibleExamQuery($moduleIds, $courseIds)
            ->with([
                'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'moduleHoc:id,ma_module,ten_module',
            ])
            ->whereHas('baiLams', function ($query) use ($submittedStatuses) {
                $query->whereIn('trang_thai', $submittedStatuses)
                    ->whereNotNull('nop_luc');
            })
            ->orderBy('tieu_de')
            ->get(['id', 'khoa_hoc_id', 'module_hoc_id', 'tieu_de', 'loai_bai_kiem_tra', 'pham_vi']);

        $attemptQuery = BaiLamBaiKiemTra::query()
            ->with([
                'hocVien:ma_nguoi_dung,ho_ten,email',
                'baiKiemTra' => function ($query) {
                    $query->select([
                        'id',
                        'khoa_hoc_id',
                        'module_hoc_id',
                        'lich_hoc_id',
                        'tieu_de',
                        'pham_vi',
                        'loai_bai_kiem_tra',
                        'loai_noi_dung',
                        'tong_diem',
                        'co_giam_sat',
                    ])->withCount('chiTietCauHois');
                },
                'baiKiemTra.khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'baiKiemTra.moduleHoc:id,ma_module,ten_module,thu_tu_module',
                'baiKiemTra.lichHoc:id,buoi_so,ngay_hoc',
                'nguoiCham:ma_nguoi_dung,ho_ten,email',
            ])
            ->whereIn('bai_kiem_tra_id', $this->buildAccessibleExamQuery($moduleIds, $courseIds)->select('id'))
            ->whereIn('trang_thai', $submittedStatuses)
            ->whereNotNull('nop_luc')
            ->when($filters['bai_kiem_tra_id'], fn ($query, $examId) => $query->where('bai_kiem_tra_id', $examId))
            ->when($filters['trang_thai_cham'], fn ($query, $status) => $query->where('trang_thai_cham', $status))
            ->when($filters['loai_bai_kiem_tra'], function ($query, $type) {
                $query->whereHas('baiKiemTra', fn ($examQuery) => $examQuery->where('loai_bai_kiem_tra', $type));
            })
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->whereHas('hocVien', function ($studentQuery) use ($search) {
                        $studentQuery->where('ho_ten', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })->orWhereHas('baiKiemTra', function ($examQuery) use ($search) {
                        $examQuery->where('tieu_de', 'like', '%'.$search.'%');
                    });
                });
            });

        $stats = [
            'tong_luot_nop' => (clone $attemptQuery)->count(),
            'da_cham' => (clone $attemptQuery)->where('trang_thai_cham', 'da_cham')->count(),
            'cho_cham' => (clone $attemptQuery)->where('trang_thai_cham', 'cho_cham')->count(),
            'diem_trung_binh' => round((float) ((clone $attemptQuery)->whereNotNull('diem_so')->avg('diem_so') ?? 0), 2),
        ];

        $baiLams = $attemptQuery
            ->orderByDesc('nop_luc')
            ->orderByDesc('updated_at')
            ->get();
        $this->attachOfficialResultContext($baiLams);

        $scoreboardCourses = $this->buildScoreboardCourses($baiLams);
        $totalExamCards = $scoreboardCourses->sum(
            fn (array $course) => $course['modules']->sum(fn (array $module) => $module['exams']->count())
        );

        return view('pages.giang-vien.bai-kiem-tra.diem-index', compact(
            'examOptions',
            'filters',
            'scoreboardCourses',
            'stats',
            'totalExamCards',
        ));
    }

    public function diemKiemTraHocVien(Request $request, int $id)
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(! $giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        $baiKiemTra = BaiKiemTra::query()
            ->with([
                'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'moduleHoc:id,ma_module,ten_module',
                'lichHoc:id,buoi_so,ngay_hoc',
            ])
            ->withCount('chiTietCauHois')
            ->findOrFail($id);

        $this->authorizeTeacherForExam($giangVien, $baiKiemTra);

        $submittedStatuses = ['da_nop', 'cho_cham', 'da_cham'];
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'trang_thai_cham' => $request->filled('trang_thai_cham') ? (string) $request->query('trang_thai_cham') : null,
        ];

        $attemptQuery = BaiLamBaiKiemTra::query()
            ->with([
                'hocVien:ma_nguoi_dung,ho_ten,email',
                'nguoiCham:ma_nguoi_dung,ho_ten,email',
            ])
            ->where('bai_kiem_tra_id', $baiKiemTra->id)
            ->whereIn('trang_thai', $submittedStatuses)
            ->whereNotNull('nop_luc')
            ->when($filters['trang_thai_cham'], fn ($query, $status) => $query->where('trang_thai_cham', $status))
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

                $query->whereHas('hocVien', function ($studentQuery) use ($search) {
                    $studentQuery->where('ho_ten', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            });

        $stats = [
            'tong_luot_nop' => (clone $attemptQuery)->count(),
            'hoc_vien' => (clone $attemptQuery)->distinct('hoc_vien_id')->count('hoc_vien_id'),
            'da_cham' => (clone $attemptQuery)->where('trang_thai_cham', 'da_cham')->count(),
            'cho_cham' => (clone $attemptQuery)->where('trang_thai_cham', 'cho_cham')->count(),
            'diem_trung_binh' => round((float) ((clone $attemptQuery)->whereNotNull('diem_so')->avg('diem_so') ?? 0), 2),
        ];

        $baiLams = $attemptQuery
            ->orderByDesc('nop_luc')
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();
        $this->attachOfficialResultContext($baiLams->getCollection());

        return view('pages.giang-vien.bai-kiem-tra.diem-hoc-vien', compact(
            'baiKiemTra',
            'baiLams',
            'filters',
            'stats',
        ));
    }

    public function xuatBaoCaoDiemKiemTra(int $id)
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(! $giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        $baiKiemTra = BaiKiemTra::query()
            ->with([
                'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'moduleHoc:id,ma_module,ten_module,so_buoi',
                'lichHoc:id,khoa_hoc_id,module_hoc_id,buoi_so,buoi_hoc,ngay_hoc,gio_bat_dau,gio_ket_thuc,hinh_thuc,link_online',
                'lichHoc.khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'lichHoc.moduleHoc:id,ma_module,ten_module,so_buoi',
            ])
            ->findOrFail($id);

        $this->authorizeTeacherForExam($giangVien, $baiKiemTra);

        try {
            $export = $this->attemptReportExportService->export($baiKiemTra);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Không thể xuất báo cáo bài kiểm tra. Vui lòng kiểm tra lại file mẫu hoặc dữ liệu bài làm.');
        }

        return response()->download($export['path'], $export['download_name'], [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function chamDiemIndex()
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(! $giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        $moduleIds = $this->getAcceptedModuleIds($giangVien);
        $courseIds = $this->getAcceptedCourseIds($giangVien);

        $baiLams = BaiLamBaiKiemTra::query()
            ->with([
                'baiKiemTra.khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'baiKiemTra.moduleHoc:id,ma_module,ten_module',
                'hocVien:ma_nguoi_dung,ho_ten,email',
            ])
            ->where('trang_thai_cham', 'cho_cham')
            ->whereHas('baiKiemTra', function ($query) use ($moduleIds, $courseIds) {
                $query->where(function ($nestedQuery) use ($moduleIds, $courseIds) {
                    $nestedQuery->when($moduleIds !== [], fn ($q) => $q->orWhereIn('module_hoc_id', $moduleIds))
                        ->when($courseIds !== [], fn ($q) => $q->orWhere(function ($innerQuery) use ($courseIds) {
                            $innerQuery->where('loai_bai_kiem_tra', 'cuoi_khoa')
                                ->whereIn('khoa_hoc_id', $courseIds);
                        }));
                });
            })
            ->orderByDesc('nop_luc')
            ->paginate(15);

        return view('pages.giang-vien.bai-kiem-tra.cham-diem-index', compact('baiLams'));
    }

    public function chamDiemShow(int $id)
    {
        $baiLam = BaiLamBaiKiemTra::with([
            'hocVien:ma_nguoi_dung,ho_ten,email',
            'baiKiemTra.khoaHoc',
            'baiKiemTra.moduleHoc',
            'chiTietTraLois.chiTietBaiKiemTra',
            'chiTietTraLois.cauHoi.dapAns',
            'chiTietTraLois.dapAn',
            'giamSatLogs',
            'giamSatSnapshots',
            'nguoiHauKiem:ma_nguoi_dung,ho_ten,email',
        ])->findOrFail($id);

        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiLam->baiKiemTra);

        $surveillanceSummary = $baiLam->baiKiemTra->co_giam_sat
            ? $this->surveillanceService->summarizeLogs($baiLam)
            : [];
        $reviewStatusOptions = $this->surveillanceService->reviewStatusOptions();

        return view('pages.hoc-vien.bai-kiem-tra.teacher-review', compact(
            'baiLam',
            'surveillanceSummary',
            'reviewStatusOptions'
        ));
    }

    public function chamDiemStore(Request $request, int $id)
    {
        $baiLam = BaiLamBaiKiemTra::with([
            'baiKiemTra',
            'chiTietTraLois.chiTietBaiKiemTra',
            'chiTietTraLois.cauHoi',
        ])->findOrFail($id);

        $giangVien = auth()->user()?->giangVien;
        $this->authorizeTeacherForExam($giangVien, $baiLam->baiKiemTra);

        $grades = $request->input('grades', []);
        $normalizedGrades = [];

        if ($baiLam->chiTietTraLois->isEmpty()) {
            $validated = $request->validate([
                'overall_grade.diem_tu_luan' => 'required|numeric|min:0|max:'.(float) $baiLam->baiKiemTra->tong_diem,
                'overall_grade.nhan_xet' => 'nullable|string',
            ]);

            try {
                DB::transaction(function () use ($baiLam, $validated, $giangVien) {
                    $overallGrade = $validated['overall_grade'] ?? [];

                    $this->scoringService->applyManualOverallGrade(
                        $baiLam,
                        (float) ($overallGrade['diem_tu_luan'] ?? 0),
                        $overallGrade['nhan_xet'] ?? null,
                        $giangVien
                    );
                    $this->ketQuaHocTapService->refreshAllForCourseStudent($baiLam->baiKiemTra->khoa_hoc_id, $baiLam->hoc_vien_id);
                });
            } catch (\Throwable $exception) {
                report($exception);

                return back()->withInput()->with('error', 'Không thể lưu kết quả chấm bài lúc này. Vui lòng thử lại.');
            }

            return redirect()
                ->route('giang-vien.cham-diem.show', $baiLam->id)
                ->with('success', 'Đã chấm bài và cập nhật kết quả học tập.');
        }

        foreach ($baiLam->chiTietTraLois as $chiTietTraLoi) {
            if ($chiTietTraLoi->cauHoi?->loai_cau_hoi !== 'tu_luan') {
                continue;
            }

            $grade = $grades[$chiTietTraLoi->id] ?? null;
            $diemToiDa = (float) ($chiTietTraLoi->chiTietBaiKiemTra?->diem_so ?? 0);
            $diemTuLuan = $grade['diem_tu_luan'] ?? null;

            if ($diemTuLuan === null || $diemTuLuan === '') {
                throw ValidationException::withMessages([
                    'grades.'.$chiTietTraLoi->id.'.diem_tu_luan' => 'Vui lòng nhập điểm cho mỗi câu tự luận.',
                ]);
            }

            if (! is_numeric($diemTuLuan) || (float) $diemTuLuan < 0 || (float) $diemTuLuan > $diemToiDa) {
                throw ValidationException::withMessages([
                    'grades.'.$chiTietTraLoi->id.'.diem_tu_luan' => 'Điểm phải nằm trong khoảng 0 - '.$diemToiDa.'.',
                ]);
            }

            $normalizedGrades[$chiTietTraLoi->id] = [
                'diem_tu_luan' => $diemTuLuan,
                'nhan_xet' => $grade['nhan_xet'] ?? null,
            ];
        }

        try {
            DB::transaction(function () use ($baiLam, $normalizedGrades, $giangVien) {
                $this->scoringService->applyManualGrades($baiLam, $normalizedGrades, $giangVien);
                $this->ketQuaHocTapService->refreshAllForCourseStudent($baiLam->baiKiemTra->khoa_hoc_id, $baiLam->hoc_vien_id);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Không thể lưu kết quả chấm bài lúc này. Vui lòng thử lại.');
        }

        return redirect()
            ->route('giang-vien.cham-diem.show', $baiLam->id)
            ->with('success', 'Đã chấm bài và cập nhật kết quả học tập.');
    }

    public function updateSurveillanceReview(Request $request, int $id)
    {
        $baiLam = BaiLamBaiKiemTra::with(['baiKiemTra', 'nguoiHauKiem'])->findOrFail($id);
        $giangVien = auth()->user()?->giangVien;
        $this->authorizeTeacherForExam($giangVien, $baiLam->baiKiemTra);

        if (! $baiLam->baiKiemTra->co_giam_sat) {
            return back()->with('error', 'Bài làm này không áp dụng giám sát.');
        }

        $reviewStatusOptions = array_keys($this->surveillanceService->reviewStatusOptions());

        $validated = $request->validate([
            'trang_thai_giam_sat' => 'required|string|in:'.implode(',', $reviewStatusOptions),
            'ghi_chu_giam_sat' => 'nullable|string|max:2000',
        ]);

        $this->surveillanceService->updateReview($baiLam, $validated, auth()->id());

        return back()->with('success', 'Đã cập nhật trạng thái hậu kiểm cho bài làm.');
    }

    /**
     * @param  Collection<int, BaiLamBaiKiemTra>  $baiLams
     * @return Collection<int, array<string, mixed>>
     */
    private function buildScoreboardCourses(Collection $baiLams): Collection
    {
        return $baiLams
            ->filter(fn (BaiLamBaiKiemTra $baiLam) => $baiLam->baiKiemTra !== null)
            ->groupBy(fn (BaiLamBaiKiemTra $baiLam) => $baiLam->baiKiemTra->khoa_hoc_id ?: 'course-unknown')
            ->map(function (Collection $courseAttempts) {
                $firstExam = $courseAttempts->first()?->baiKiemTra;
                $course = $firstExam?->khoaHoc;
                $modules = $courseAttempts
                    ->groupBy(function (BaiLamBaiKiemTra $baiLam) {
                        $exam = $baiLam->baiKiemTra;

                        if ($exam?->loai_bai_kiem_tra === 'cuoi_khoa' || empty($exam?->module_hoc_id)) {
                            return 'final';
                        }

                        return 'module-'.$exam->module_hoc_id;
                    })
                    ->map(function (Collection $moduleAttempts, string $moduleKey) {
                        $firstExam = $moduleAttempts->first()?->baiKiemTra;
                        $module = $firstExam?->moduleHoc;
                        $isFinalGroup = $moduleKey === 'final';
                        $exams = $moduleAttempts
                            ->groupBy('bai_kiem_tra_id')
                            ->map(fn (Collection $examAttempts) => $this->buildScoreboardExamCard($examAttempts))
                            ->sortBy('sort')
                            ->values();

                        return [
                            'key' => $moduleKey,
                            'title' => $isFinalGroup
                                ? 'Bài kiểm tra cuối khóa'
                                : ($module?->ten_module ?? 'Module chưa xác định'),
                            'subtitle' => $isFinalGroup
                                ? 'Tổng kết toàn khóa'
                                : ($module?->ma_module ?? 'Chưa có mã module'),
                            'sort' => $isFinalGroup ? 999999 : (int) ($module?->thu_tu_module ?? 999998),
                            'attempt_count' => $moduleAttempts->count(),
                            'exam_count' => $exams->count(),
                            'exams' => $exams,
                        ];
                    })
                    ->sortBy('sort')
                    ->values();

                return [
                    'id' => $firstExam?->khoa_hoc_id,
                    'code' => $course?->ma_khoa_hoc ?? 'KH',
                    'title' => $course?->ten_khoa_hoc ?? 'Khóa học chưa xác định',
                    'sort' => $course?->ma_khoa_hoc ?? ('course-'.($firstExam?->khoa_hoc_id ?? 'unknown')),
                    'attempt_count' => $courseAttempts->count(),
                    'student_count' => $courseAttempts->pluck('hoc_vien_id')->unique()->count(),
                    'exam_count' => $modules->sum(fn (array $module) => $module['exam_count']),
                    'modules' => $modules,
                ];
            })
            ->sortBy('sort')
            ->values();
    }

    /**
     * @param  Collection<int, BaiLamBaiKiemTra>  $examAttempts
     * @return array<string, mixed>
     */
    private function buildScoreboardExamCard(Collection $examAttempts): array
    {
        $exam = $examAttempts->first()?->baiKiemTra;
        $sortedAttempts = $examAttempts
            ->sortByDesc(fn (BaiLamBaiKiemTra $baiLam) => $baiLam->nop_luc?->timestamp ?? 0)
            ->values();
        $scoredAttempts = $examAttempts->filter(fn (BaiLamBaiKiemTra $baiLam) => $baiLam->diem_so !== null);

        return [
            'id' => $exam?->id,
            'exam' => $exam,
            'attempts' => $sortedAttempts,
            'attempt_count' => $examAttempts->count(),
            'student_count' => $examAttempts->pluck('hoc_vien_id')->unique()->count(),
            'graded_count' => $examAttempts->where('trang_thai_cham', 'da_cham')->count(),
            'pending_count' => $examAttempts->where('trang_thai_cham', 'cho_cham')->count(),
            'official_count' => $examAttempts->filter(fn (BaiLamBaiKiemTra $baiLam) => (bool) ($baiLam->is_official_attempt ?? false))->count(),
            'average_score' => $scoredAttempts->isNotEmpty() ? round((float) $scoredAttempts->avg('diem_so'), 2) : null,
            'last_submitted_at' => $sortedAttempts->first()?->nop_luc,
            'sort' => sprintf('%05d-%s', (int) ($exam?->lichHoc?->buoi_so ?? 99999), $exam?->tieu_de ?? 'unknown'),
        ];
    }

    /**
     * @param  Collection<int, BaiLamBaiKiemTra>  $baiLams
     */
    private function attachOfficialResultContext(Collection $baiLams): void
    {
        if ($baiLams->isEmpty()) {
            return;
        }

        $results = KetQuaHocTap::query()
            ->whereIn('bai_kiem_tra_id', $baiLams->pluck('bai_kiem_tra_id')->filter()->unique()->values()->all())
            ->whereIn('hoc_vien_id', $baiLams->pluck('hoc_vien_id')->filter()->unique()->values()->all())
            ->get()
            ->keyBy(fn (KetQuaHocTap $result) => $result->bai_kiem_tra_id.':'.$result->hoc_vien_id);

        $baiLams->each(function (BaiLamBaiKiemTra $baiLam) use ($results) {
            $result = $results->get($baiLam->bai_kiem_tra_id.':'.$baiLam->hoc_vien_id);
            $sourceAttemptIds = collect($result?->source_attempt_ids ?: []);

            if ($result?->source_attempt_id) {
                $sourceAttemptIds->push((int) $result->source_attempt_id);
            }

            if (isset($result?->chi_tiet['bai_lam_id'])) {
                $sourceAttemptIds->push((int) $result->chi_tiet['bai_lam_id']);
            }

            $sourceAttemptIds = $sourceAttemptIds->map(fn ($id) => (int) $id)->filter()->unique()->values();

            $baiLam->setAttribute('is_official_attempt', $sourceAttemptIds->contains((int) $baiLam->id));
            $baiLam->setAttribute('official_score', $result?->diem_kiem_tra);
            $baiLam->setAttribute('official_strategy', $result?->attempt_strategy_used);
            $baiLam->setAttribute('official_result_id', $result?->id);
        });
    }
}
