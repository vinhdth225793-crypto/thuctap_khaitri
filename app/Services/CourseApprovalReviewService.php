<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\ChiTietPhieuXetDuyetKetQua;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KetQuaHocTapChotLog;
use App\Models\KhoaHoc;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;
use App\Models\PhieuXetDuyetKetQua;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseApprovalReviewService
{
    public const ASSESSMENT_WEIGHT = 80.0;
    public const ATTENDANCE_WEIGHT = 20.0;
    public const PASSING_SCORE = 5.0;

    public function __construct(
        private readonly CourseAttendanceScoreService $attendanceScoreService,
        private readonly KetQuaHocTapService $ketQuaHocTapService,
    ) {
    }

    /**
     * @return array{final_exams: Collection<int, BaiKiemTra>, selectable_exams: Collection<int, BaiKiemTra>, all: Collection<int, BaiKiemTra>}
     */
    public function examGroupsForCourse(KhoaHoc|int $course): array
    {
        $courseModel = $course instanceof KhoaHoc ? $course : KhoaHoc::findOrFail($course);

        $exams = BaiKiemTra::query()
            ->with(['moduleHoc', 'lichHoc'])
            ->where('khoa_hoc_id', $courseModel->id)
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai_phat_hanh', 'phat_hanh')
            ->orderByRaw("CASE WHEN loai_bai_kiem_tra = 'cuoi_khoa' THEN 0 WHEN loai_bai_kiem_tra IN ('module', 'cuoi_module') THEN 1 ELSE 2 END")
            ->orderBy('module_hoc_id')
            ->orderBy('lich_hoc_id')
            ->orderBy('id')
            ->get();

        return [
            'final_exams' => $exams
                ->filter(fn (BaiKiemTra $exam) => $this->isFinalExam($exam))
                ->values(),
            'selectable_exams' => $exams
                ->filter(fn (BaiKiemTra $exam) => $this->isSelectableExam($exam))
                ->values(),
            'all' => $exams,
        ];
    }

    /**
     * @return array<int, int>
     */
    public function defaultExamIdsFor(KhoaHoc $course, string $mode): array
    {
        $mode = $this->normalizeMode($mode);
        $groups = $this->examGroupsForCourse($course);

        if ($mode === PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE) {
            $firstFinalExam = $groups['final_exams']->first();

            return $firstFinalExam ? [(int) $firstFinalExam->id] : [];
        }

        return $groups['selectable_exams']
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPreview(KhoaHoc $course, string $mode, array $selectedExamIds): array
    {
        $mode = $this->normalizeMode($mode);
        $selectedExams = $this->selectedExams($course, $mode, $selectedExamIds);
        $enrollments = $this->activeEnrollments($course);
        $selectedExamCount = $selectedExams->count();

        $rows = $enrollments->map(function (HocVienKhoaHoc $enrollment) use ($course, $mode, $selectedExams, $selectedExamCount) {
            $hocVienId = (int) $enrollment->hoc_vien_id;
            $attendance = $this->attendanceScoreService->calculateForCourse((int) $course->id, $hocVienId);
            $examRows = $this->examRowsForStudent($selectedExams, $hocVienId);
            $completedExamRows = collect($examRows)->filter(fn (array $row) => $row['diem'] !== null)->values();

            $assessmentScore = null;
            if ($mode === PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE) {
                $assessmentScore = $completedExamRows->first()['diem'] ?? null;
            } elseif ($completedExamRows->isNotEmpty()) {
                $assessmentScore = round((float) $completedExamRows->avg('diem'), 2);
            }

            $attendanceScore = $attendance['diem_diem_danh'];
            $finalScore = null;
            if ($assessmentScore !== null && $attendanceScore !== null) {
                $finalScore = round(
                    ($assessmentScore * (self::ASSESSMENT_WEIGHT / 100))
                    + ($attendanceScore * (self::ATTENDANCE_WEIGHT / 100)),
                    2
                );
            }

            $ketQua = ChiTietPhieuXetDuyetKetQua::KET_QUA_CHUA_DU;
            if ($finalScore !== null) {
                $ketQua = $finalScore >= self::PASSING_SCORE
                    ? ChiTietPhieuXetDuyetKetQua::KET_QUA_DAT
                    : ChiTietPhieuXetDuyetKetQua::KET_QUA_KHONG_DAT;
            }

            $sourceAttemptIds = $completedExamRows
                ->flatMap(fn (array $row) => $row['source_attempt_ids'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            return [
                'enrollment' => $enrollment,
                'student' => $enrollment->hocVien?->nguoiDung,
                'hoc_vien_id' => $hocVienId,
                'attendance' => $attendance,
                'exam_rows' => $examRows,
                'diem_kiem_tra' => $assessmentScore,
                'diem_xet_duyet' => $finalScore,
                'ket_qua' => $ketQua,
                'missing_exam_count' => max(0, $selectedExamCount - $completedExamRows->count()),
                'source_attempt_ids' => $sourceAttemptIds,
            ];
        })->values();

        return [
            'course' => $course,
            'mode' => $mode,
            'selected_exams' => $selectedExams,
            'weights' => [
                'assessment' => self::ASSESSMENT_WEIGHT,
                'attendance' => self::ATTENDANCE_WEIGHT,
            ],
            'students' => $rows,
            'summary' => [
                'student_count' => $rows->count(),
                'selected_exam_count' => $selectedExamCount,
                'ready_count' => $rows->whereNotNull('diem_xet_duyet')->count(),
                'passed_count' => $rows->where('ket_qua', ChiTietPhieuXetDuyetKetQua::KET_QUA_DAT)->count(),
                'failed_count' => $rows->where('ket_qua', ChiTietPhieuXetDuyetKetQua::KET_QUA_KHONG_DAT)->count(),
                'missing_data_count' => $rows->where('ket_qua', ChiTietPhieuXetDuyetKetQua::KET_QUA_CHUA_DU)->count(),
            ],
        ];
    }

    public function saveDraft(
        KhoaHoc $course,
        NguoiDung $teacherUser,
        ?PhanCongModuleGiangVien $assignment,
        string $mode,
        array $selectedExamIds,
        ?string $note = null,
        bool $submit = false
    ): PhieuXetDuyetKetQua {
        $mode = $this->normalizeMode($mode);
        $selectedExams = $this->selectedExams($course, $mode, $selectedExamIds);

        if ($selectedExams->isEmpty()) {
            throw ValidationException::withMessages([
                'bai_kiem_tra_ids' => 'Can chon it nhat mot bai kiem tra hop le de lap phieu xet duyet.',
            ]);
        }

        $preview = $this->buildPreview(
            $course,
            $mode,
            $selectedExams->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        return DB::transaction(function () use ($course, $teacherUser, $assignment, $mode, $selectedExams, $note, $submit, $preview) {
            $ticket = $this->editableTicket($course, $teacherUser)
                ?? new PhieuXetDuyetKetQua([
                    'khoa_hoc_id' => (int) $course->id,
                    'nguoi_lap_id' => (int) $teacherUser->ma_nguoi_dung,
                ]);

            $ticket->fill([
                'phan_cong_id' => $assignment?->id,
                'giang_vien_id' => $teacherUser->giangVien?->id,
                'phuong_an' => $mode,
                'ty_trong_kiem_tra' => self::ASSESSMENT_WEIGHT,
                'ty_trong_diem_danh' => self::ATTENDANCE_WEIGHT,
                'diem_dat' => self::PASSING_SCORE,
                'bai_kiem_tra_ids' => $selectedExams->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'cong_thuc' => $this->formulaFor($mode),
                'trang_thai' => $submit
                    ? PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED
                    : PhieuXetDuyetKetQua::TRANG_THAI_DRAFT,
                'ghi_chu' => $note,
                'submitted_at' => $submit ? now() : $ticket->submitted_at,
                'reviewing_by_id' => null,
                'reviewing_at' => null,
                'approved_by_id' => null,
                'approved_at' => null,
                'rejected_by_id' => null,
                'rejected_at' => null,
                'reject_reason' => null,
                'finalized_by_id' => null,
                'finalized_at' => null,
            ]);
            $ticket->save();

            $ticket->chiTiets()->delete();
            foreach ($preview['students'] as $row) {
                $ticket->chiTiets()->create($this->detailPayload($row));
            }

            return $ticket->load(['khoaHoc', 'nguoiLap', 'chiTiets.hocVien']);
        });
    }

    public function startReview(PhieuXetDuyetKetQua $ticket, NguoiDung $admin): PhieuXetDuyetKetQua
    {
        $this->ensureInStatuses($ticket, [
            PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
        ]);

        $ticket->update([
            'trang_thai' => PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
            'reviewing_by_id' => (int) $admin->ma_nguoi_dung,
            'reviewing_at' => now(),
        ]);

        return $ticket->refresh();
    }

    public function approve(PhieuXetDuyetKetQua $ticket, NguoiDung $admin): PhieuXetDuyetKetQua
    {
        $this->ensureInStatuses($ticket, [
            PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
            PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
        ]);

        $ticket->update([
            'trang_thai' => PhieuXetDuyetKetQua::TRANG_THAI_APPROVED,
            'approved_by_id' => (int) $admin->ma_nguoi_dung,
            'approved_at' => now(),
            'rejected_by_id' => null,
            'rejected_at' => null,
            'reject_reason' => null,
        ]);

        return $ticket->refresh();
    }

    public function reject(PhieuXetDuyetKetQua $ticket, NguoiDung $admin, string $reason): PhieuXetDuyetKetQua
    {
        $this->ensureInStatuses($ticket, [
            PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
            PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
            PhieuXetDuyetKetQua::TRANG_THAI_APPROVED,
        ]);

        $ticket->update([
            'trang_thai' => PhieuXetDuyetKetQua::TRANG_THAI_REJECTED,
            'rejected_by_id' => (int) $admin->ma_nguoi_dung,
            'rejected_at' => now(),
            'reject_reason' => $reason,
            'approved_by_id' => null,
            'approved_at' => null,
        ]);

        return $ticket->refresh();
    }

    public function finalize(PhieuXetDuyetKetQua $ticket, NguoiDung $admin, ?string $note = null): PhieuXetDuyetKetQua
    {
        $this->ensureInStatuses($ticket, [
            PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
            PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
            PhieuXetDuyetKetQua::TRANG_THAI_APPROVED,
        ]);

        return DB::transaction(function () use ($ticket, $admin, $note) {
            $ticket->loadMissing(['chiTiets', 'khoaHoc']);

            if ($ticket->approved_by_id === null) {
                $ticket->approved_by_id = (int) $admin->ma_nguoi_dung;
                $ticket->approved_at = now();
            }

            $ticket->fill([
                'trang_thai' => PhieuXetDuyetKetQua::TRANG_THAI_FINALIZED,
                'finalized_by_id' => (int) $admin->ma_nguoi_dung,
                'finalized_at' => now(),
            ]);
            $ticket->save();

            foreach ($ticket->chiTiets as $detail) {
                $this->storeOfficialCourseResult($ticket, $detail, $admin, $note);
            }

            return $ticket->refresh()->load(['khoaHoc', 'nguoiLap', 'chiTiets.hocVien']);
        });
    }

    private function storeOfficialCourseResult(
        PhieuXetDuyetKetQua $ticket,
        ChiTietPhieuXetDuyetKetQua $detail,
        NguoiDung $admin,
        ?string $note
    ): KetQuaHocTap {
        $metadata = $detail->calculation_metadata ?: [];
        $sourceAttemptIds = collect($metadata['source_attempt_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
        $status = match ($detail->ket_qua) {
            ChiTietPhieuXetDuyetKetQua::KET_QUA_DAT => 'dat',
            ChiTietPhieuXetDuyetKetQua::KET_QUA_KHONG_DAT => 'khong_dat',
            default => 'dang_hoc',
        };

        $existing = KetQuaHocTap::query()
            ->where('khoa_hoc_id', $ticket->khoa_hoc_id)
            ->where('hoc_vien_id', $detail->hoc_vien_id)
            ->whereNull('module_hoc_id')
            ->whereNull('bai_kiem_tra_id')
            ->first();
        $previousScore = $existing?->diem_giang_vien_chot ?? $existing?->diem_tong_ket;

        $result = KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => (int) $ticket->khoa_hoc_id,
                'hoc_vien_id' => (int) $detail->hoc_vien_id,
                'module_hoc_id' => null,
                'bai_kiem_tra_id' => null,
            ],
            [
                'phuong_thuc_danh_gia' => $ticket->phuong_an,
                'aggregation_strategy_used' => $ticket->phuong_an,
                'source_attempt_id' => $sourceAttemptIds[0] ?? null,
                'source_attempt_ids' => $sourceAttemptIds,
                'diem_diem_danh' => $detail->diem_chuyen_can,
                'diem_kiem_tra' => $detail->diem_kiem_tra,
                'diem_tong_ket' => $detail->diem_xet_duyet,
                'diem_giang_vien_chot' => $detail->diem_xet_duyet,
                'tong_so_buoi' => $detail->tong_so_buoi,
                'so_buoi_tham_du' => $detail->so_buoi_tham_du,
                'ty_le_tham_du' => $detail->ty_le_tham_du,
                'so_bai_kiem_tra_hoan_thanh' => collect($detail->chi_tiet_bai_kiem_tra ?: [])
                    ->whereNotNull('diem')
                    ->count(),
                'trang_thai' => $status,
                'trang_thai_luu_ho_so' => 'da_luu',
                'da_chot' => true,
                'trang_thai_chot' => KetQuaHocTap::TRANG_THAI_CHOT_DA_CHOT,
                'chot_boi' => (int) $ticket->nguoi_lap_id,
                'chot_luc' => $ticket->submitted_at ?? $ticket->updated_at,
                'ghi_chu_chot' => $ticket->ghi_chu,
                'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET,
                'admin_duyet_id' => (int) $admin->ma_nguoi_dung,
                'duyet_luc' => now(),
                'ghi_chu_duyet' => $note,
                'luu_ho_so_luc' => now(),
                'chi_tiet' => [
                    'phieu_xet_duyet_id' => (int) $ticket->id,
                    'phuong_an' => $ticket->phuong_an,
                    'bai_kiem_tra' => $detail->chi_tiet_bai_kiem_tra,
                    'ket_qua' => $detail->ket_qua,
                ],
                'calculation_metadata' => [
                    'course_approval_ticket' => [
                        'id' => (int) $ticket->id,
                        'status' => $ticket->trang_thai,
                        'mode' => $ticket->phuong_an,
                        'formula' => $ticket->cong_thuc,
                        'finalized_at' => optional($ticket->finalized_at)->toDateTimeString(),
                    ],
                    'attendance' => [
                        'tong_so_buoi' => $detail->tong_so_buoi,
                        'so_buoi_tham_du' => $detail->so_buoi_tham_du,
                        'ty_le_tham_du' => $detail->ty_le_tham_du !== null ? (float) $detail->ty_le_tham_du : null,
                        'diem_diem_danh' => $detail->diem_chuyen_can !== null ? (float) $detail->diem_chuyen_can : null,
                    ],
                    'assessment' => $metadata,
                    'weights' => [
                        'ty_trong_kiem_tra' => (float) $ticket->ty_trong_kiem_tra,
                        'ty_trong_diem_danh' => (float) $ticket->ty_trong_diem_danh,
                    ],
                ],
                'cap_nhat_luc' => now(),
            ]
        );

        KetQuaHocTapChotLog::create([
            'ket_qua_hoc_tap_id' => $result->id,
            'hoc_vien_id' => (int) $detail->hoc_vien_id,
            'module_hoc_id' => null,
            'khoa_hoc_id' => (int) $ticket->khoa_hoc_id,
            'nguoi_thuc_hien_id' => (int) $admin->ma_nguoi_dung,
            'hanh_dong' => 'finalize_course_approval',
            'diem_truoc' => $previousScore,
            'diem_sau' => $detail->diem_xet_duyet,
            'ly_do' => $note,
            'metadata' => [
                'phieu_xet_duyet_id' => (int) $ticket->id,
                'detail_id' => (int) $detail->id,
                'mode' => $ticket->phuong_an,
            ],
        ]);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function detailPayload(array $row): array
    {
        return [
            'hoc_vien_id' => $row['hoc_vien_id'],
            'tong_so_buoi' => $row['attendance']['tong_so_buoi'],
            'so_buoi_tham_du' => $row['attendance']['so_buoi_tham_du'],
            'ty_le_tham_du' => $row['attendance']['ty_le_tham_du'],
            'diem_chuyen_can' => $row['attendance']['diem_diem_danh'],
            'diem_kiem_tra' => $row['diem_kiem_tra'],
            'diem_xet_duyet' => $row['diem_xet_duyet'],
            'ket_qua' => $row['ket_qua'],
            'chi_tiet_bai_kiem_tra' => $row['exam_rows'],
            'calculation_metadata' => [
                'formula' => 'assessment * 0.8 + attendance * 0.2',
                'mode' => $row['exam_rows'][0]['mode'] ?? null,
                'missing_exam_count' => $row['missing_exam_count'],
                'source_attempt_ids' => $row['source_attempt_ids'],
            ],
        ];
    }

    /**
     * @param  Collection<int, BaiKiemTra>  $selectedExams
     * @return array<int, array<string, mixed>>
     */
    private function examRowsForStudent(Collection $selectedExams, int $hocVienId): array
    {
        return $selectedExams
            ->map(function (BaiKiemTra $exam) use ($hocVienId) {
                $result = $this->ketQuaHocTapService->refreshForExamStudent((int) $exam->id, $hocVienId);

                return [
                    'bai_kiem_tra_id' => (int) $exam->id,
                    'tieu_de' => $exam->tieu_de,
                    'loai' => $this->examTypeKey($exam),
                    'mode' => $exam->loai_bai_kiem_tra,
                    'module' => $exam->moduleHoc?->ten_module,
                    'lich_hoc_id' => $exam->lich_hoc_id ? (int) $exam->lich_hoc_id : null,
                    'diem' => $result?->diem_kiem_tra !== null ? round((float) $result->diem_kiem_tra, 2) : null,
                    'ket_qua_hoc_tap_id' => $result?->id ? (int) $result->id : null,
                    'source_attempt_id' => $result?->source_attempt_id ? (int) $result->source_attempt_id : null,
                    'source_attempt_ids' => $result?->source_attempt_ids ?: [],
                ];
            })
            ->values()
            ->all();
    }

    private function editableTicket(KhoaHoc $course, NguoiDung $teacherUser): ?PhieuXetDuyetKetQua
    {
        return PhieuXetDuyetKetQua::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('nguoi_lap_id', $teacherUser->ma_nguoi_dung)
            ->whereIn('trang_thai', [
                PhieuXetDuyetKetQua::TRANG_THAI_DRAFT,
                PhieuXetDuyetKetQua::TRANG_THAI_REJECTED,
            ])
            ->latest('updated_at')
            ->first();
    }

    /**
     * @return Collection<int, HocVienKhoaHoc>
     */
    private function activeEnrollments(KhoaHoc $course): Collection
    {
        return HocVienKhoaHoc::with('hocVien.nguoiDung')
            ->where('khoa_hoc_id', $course->id)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->orderBy('hoc_vien_id')
            ->get();
    }

    /**
     * @param  array<int, mixed>  $selectedExamIds
     * @return Collection<int, BaiKiemTra>
     */
    private function selectedExams(KhoaHoc $course, string $mode, array $selectedExamIds): Collection
    {
        $mode = $this->normalizeMode($mode);
        $ids = collect($selectedExamIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $query = BaiKiemTra::query()
            ->with(['moduleHoc', 'lichHoc'])
            ->where('khoa_hoc_id', $course->id)
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai_phat_hanh', 'phat_hanh')
            ->whereIn('id', $ids->all());

        $exams = $query->get()
            ->filter(fn (BaiKiemTra $exam) => $mode === PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE
                ? $this->isFinalExam($exam)
                : $this->isSelectableExam($exam))
            ->sortBy(fn (BaiKiemTra $exam) => $ids->search((int) $exam->id))
            ->values();

        if ($mode === PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE) {
            return $exams->take(1)->values();
        }

        return $exams;
    }

    private function normalizeMode(?string $mode): string
    {
        return in_array($mode, [
            PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE,
            PhieuXetDuyetKetQua::PHUONG_AN_SELECTED_EXAMS_ATTENDANCE,
        ], true)
            ? $mode
            : PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE;
    }

    /**
     * @return array<string, mixed>
     */
    private function formulaFor(string $mode): array
    {
        return [
            'mode' => $this->normalizeMode($mode),
            'assessment_weight' => self::ASSESSMENT_WEIGHT,
            'attendance_weight' => self::ATTENDANCE_WEIGHT,
            'passing_score' => self::PASSING_SCORE,
            'expression' => 'diem_xet_duyet = diem_kiem_tra * 0.8 + diem_chuyen_can * 0.2',
        ];
    }

    private function isFinalExam(BaiKiemTra $exam): bool
    {
        return $exam->loai_bai_kiem_tra === BaiKiemTra::LOAI_CUOI_KHOA
            || $exam->pham_vi === 'cuoi_khoa';
    }

    private function isSelectableExam(BaiKiemTra $exam): bool
    {
        return in_array($exam->loai_bai_kiem_tra, [
            BaiKiemTra::LOAI_MODULE,
            BaiKiemTra::LOAI_CUOI_MODULE,
            BaiKiemTra::LOAI_BUOI_HOC,
        ], true)
            || in_array($exam->pham_vi, ['module', 'buoi_hoc'], true);
    }

    private function examTypeKey(BaiKiemTra $exam): string
    {
        if ($this->isFinalExam($exam)) {
            return 'cuoi_khoa';
        }

        if ($exam->loai_bai_kiem_tra === BaiKiemTra::LOAI_BUOI_HOC || $exam->pham_vi === 'buoi_hoc') {
            return 'buoi_hoc';
        }

        return 'module';
    }

    /**
     * @param  array<int, string>  $statuses
     */
    private function ensureInStatuses(PhieuXetDuyetKetQua $ticket, array $statuses): void
    {
        if (! in_array($ticket->trang_thai, $statuses, true)) {
            throw ValidationException::withMessages([
                'phieu_id' => 'Trang thai phieu hien tai khong cho phep thao tac nay.',
            ]);
        }
    }
}
