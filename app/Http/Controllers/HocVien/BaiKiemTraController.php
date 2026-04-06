<?php

namespace App\Http\Controllers\HocVien;

use App\Http\Controllers\Controller;
use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\BaiLamViPhamGiamSat;
use App\Models\HocVienKhoaHoc;
use App\Services\BaiKiemTraScoringService;
use App\Services\ExamPrecheckService;
use App\Services\ExamSnapshotService;
use App\Services\ExamSurveillanceLogService;
use App\Services\ExamSurveillanceService;
use App\Services\KetQuaHocTapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaiKiemTraController extends Controller
{
    public function __construct(
        private readonly BaiKiemTraScoringService $scoringService,
        private readonly KetQuaHocTapService $ketQuaHocTapService,
        private readonly ExamPrecheckService $precheckService,
        private readonly ExamSurveillanceService $surveillanceService,
        private readonly ExamSurveillanceLogService $surveillanceLogService,
        private readonly ExamSnapshotService $snapshotService,
    ) {
    }

    public function index()
    {
        $user = auth()->user();

        $baiKiemTras = $this->queryBaiKiemTraHocVien($user->ma_nguoi_dung)
            ->get()
            ->sortBy(function (BaiKiemTra $baiKiemTra) {
                $priority = match ($baiKiemTra->access_status_key) {
                    'dang_mo' => 1,
                    'sap_mo' => 2,
                    'da_dong' => 3,
                    default => 4,
                };

                $timestamp = $baiKiemTra->ngay_mo?->timestamp ?? 0;

                return sprintf('%s-%s', $priority, str_pad((string) $timestamp, 12, '0', STR_PAD_LEFT));
            })
            ->values();

        $stats = [
            'tong' => $baiKiemTras->count(),
            'dang_mo' => $baiKiemTras->where('access_status_key', 'dang_mo')->count(),
            'sap_mo' => $baiKiemTras->where('access_status_key', 'sap_mo')->count(),
            'da_nop' => $baiKiemTras->filter(fn (BaiKiemTra $item) => optional($item->baiLams->first())->is_submitted)->count(),
            'cho_cham' => $baiKiemTras->filter(fn (BaiKiemTra $item) => optional($item->baiLams->first())->need_manual_grading)->count(),
        ];

        return view('pages.hoc-vien.bai-kiem-tra.index', compact('baiKiemTras', 'stats'));
    }

    public function show(int $id)
    {
        $hocVienId = auth()->user()->ma_nguoi_dung;
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, $hocVienId);
        $baiLam = $baiKiemTra->baiLams->sortByDesc('lan_lam_thu')->first();

        if ($baiLam) {
            $relations = [
                'chiTietTraLois.chiTietBaiKiemTra',
                'chiTietTraLois.cauHoi.dapAns',
                'chiTietTraLois.dapAn',
            ];

            if ($baiKiemTra->co_giam_sat) {
                $relations[] = 'giamSatLogs';
                $relations[] = 'giamSatSnapshots';
            }

            $baiLam->loadMissing($relations);
        }

        $cauHoiHienThi = $baiKiemTra->chiTietCauHois;
        if ($baiKiemTra->randomize_questions && $baiLam && $baiLam->can_resume) {
            $cauHoiHienThi = $cauHoiHienThi->shuffle($baiLam->id)->values();
        }

        if ($baiKiemTra->randomize_answers && $baiLam && $baiLam->can_resume) {
            foreach ($cauHoiHienThi as $chiTiet) {
                if ($chiTiet->cauHoi && $chiTiet->cauHoi->relationLoaded('dapAns')) {
                    $chiTiet->cauHoi->setRelation('dapAns', $chiTiet->cauHoi->dapAns->shuffle($baiLam->id + $chiTiet->id));
                }
            }
        }

        $attemptsUsed = $baiKiemTra->baiLams->count();
        $remainingAttempts = max(0, (int) $baiKiemTra->so_lan_duoc_lam - $attemptsUsed);
        $precheckState = $baiKiemTra->co_giam_sat
            ? $this->precheckService->getPassedPrecheck($baiKiemTra, $hocVienId)
            : null;
        $surveillanceSummary = ($baiLam && $baiKiemTra->co_giam_sat)
            ? $this->surveillanceService->summarizeLogs($baiLam)
            : [];

        return view('pages.hoc-vien.bai-kiem-tra.show', compact(
            'baiKiemTra',
            'baiLam',
            'cauHoiHienThi',
            'attemptsUsed',
            'remainingAttempts',
            'precheckState',
            'surveillanceSummary'
        ));
    }

    public function precheck(int $id)
    {
        $hocVienId = auth()->user()->ma_nguoi_dung;
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, $hocVienId);

        if (!$baiKiemTra->co_giam_sat) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('info', 'Bài kiểm tra này không yêu cầu bước pre-check.');
        }

        if (!$baiKiemTra->can_student_start) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Bài kiểm tra này chưa mở hoặc đã đóng.');
        }

        if ($baiKiemTra->baiLams->firstWhere('trang_thai', 'dang_lam')) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('info', 'Bạn đang có một lần làm bài chưa nộp. Hãy tiếp tục bài làm hiện tại.');
        }

        if ($baiKiemTra->baiLams->count() >= $baiKiemTra->so_lan_duoc_lam) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Bạn đã dùng hết số lần làm bài được phép.');
        }

        $precheckState = $this->precheckService->getPassedPrecheck($baiKiemTra, $hocVienId);

        return view('pages.hoc-vien.bai-kiem-tra.precheck', compact('baiKiemTra', 'precheckState'));
    }

    public function submitPrecheck(Request $request, int $id)
    {
        $hocVienId = auth()->user()->ma_nguoi_dung;
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, $hocVienId);

        if (!$baiKiemTra->co_giam_sat) {
            return redirect()->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id);
        }

        $validated = $request->validate([
            'precheck_payload' => 'required|string',
        ]);

        $payload = json_decode($validated['precheck_payload'], true);
        if (!is_array($payload)) {
            throw ValidationException::withMessages([
                'precheck_payload' => 'Không đọc được dữ liệu pre-check.',
            ]);
        }

        $normalized = $this->precheckService->validatePayload($baiKiemTra, $payload);
        $this->precheckService->storePassedPrecheck($baiKiemTra, $hocVienId, $normalized);

        return redirect()
            ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
            ->with('success', 'Pre-check đã đạt. Bạn có thể bắt đầu bài thi giám sát.');
    }

    public function batDau(Request $request, int $id)
    {
        $user = auth()->user();
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, $user->ma_nguoi_dung);

        if (!$baiKiemTra->can_student_start) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Bài kiểm tra này chưa mở hoặc đã đóng.');
        }

        $baiLam = $baiKiemTra->baiLams->firstWhere('trang_thai', 'dang_lam');

        if ($baiLam) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('info', 'Bạn đang có một lần làm bài chưa nộp. Hãy tiếp tục bài làm hiện tại.');
        }

        if ($baiKiemTra->baiLams->count() >= $baiKiemTra->so_lan_duoc_lam) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Bạn đã dùng hết số lần làm bài được phép.');
        }

        $precheckState = null;
        if ($baiKiemTra->co_giam_sat) {
            $precheckState = $this->precheckService->consumePassedPrecheck($baiKiemTra, $user->ma_nguoi_dung);

            if (!$precheckState) {
                return redirect()
                    ->route('hoc-vien.bai-kiem-tra.precheck', $baiKiemTra->id)
                    ->with('error', 'Bạn cần hoàn tất pre-check hợp lệ trước khi bắt đầu bài thi giám sát.');
            }
        }

        DB::transaction(function () use ($baiKiemTra, $user, $request, $precheckState, &$baiLam) {
            $attemptPayload = $this->surveillanceService->resolveAttemptStartPayload(
                $baiKiemTra,
                $request,
                $precheckState['payload'] ?? null
            );

            $baiLam = BaiLamBaiKiemTra::create([
                'bai_kiem_tra_id' => $baiKiemTra->id,
                'hoc_vien_id' => $user->ma_nguoi_dung,
                'lan_lam_thu' => (int) $baiKiemTra->baiLams->max('lan_lam_thu') + 1,
                'trang_thai' => 'dang_lam',
                'trang_thai_cham' => 'chua_cham',
                'bat_dau_luc' => now(),
                ...$attemptPayload,
            ]);

            foreach ($baiKiemTra->chiTietCauHois as $chiTietBaiKiemTra) {
                $baiLam->chiTietTraLois()->create([
                    'chi_tiet_bai_kiem_tra_id' => $chiTietBaiKiemTra->id,
                    'ngan_hang_cau_hoi_id' => $chiTietBaiKiemTra->ngan_hang_cau_hoi_id,
                ]);
            }
        });

        return redirect()
            ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
            ->with('success', 'Đã bắt đầu làm bài. Hãy hoàn thành và nộp bài trước khi hết hạn.');
    }

    public function nopBai(Request $request, int $id)
    {
        $user = auth()->user();
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, $user->ma_nguoi_dung);
        $baiLam = $baiKiemTra->baiLams->firstWhere('trang_thai', 'dang_lam');
        $tuDongNop = $request->boolean('tu_dong_nop');

        if (!$baiLam) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Bạn cần bắt đầu bài kiểm tra trước khi nộp bài.');
        }

        if (!$baiKiemTra->can_student_start) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Không thể nộp bài vì bài kiểm tra này đã đóng hoặc chưa đến giờ mở.');
        }

        if ($baiKiemTra->chiTietCauHois->isEmpty()) {
            $validated = $request->validate([
                'noi_dung_bai_lam' => ($tuDongNop ? 'nullable' : 'required') . '|string|max:50000',
            ]);

            DB::transaction(function () use ($baiLam, $baiKiemTra, $validated, $tuDongNop) {
                if ($tuDongNop && $baiKiemTra->co_giam_sat) {
                    $this->surveillanceLogService->recordAutoSubmit($baiLam, [
                        'trigger' => 'client_auto_submit',
                    ]);
                }

                $baiLam->update([
                    'noi_dung_bai_lam' => $validated['noi_dung_bai_lam'] ?? null,
                    'trang_thai' => 'cho_cham',
                    'trang_thai_cham' => 'cho_cham',
                    'nop_luc' => now(),
                    'da_tu_dong_nop' => $tuDongNop,
                ]);

                $this->surveillanceService->finalizeAttempt($baiLam->fresh());
                $this->ketQuaHocTapService->refreshAllForCourseStudent($baiKiemTra->khoa_hoc_id, $baiLam->hoc_vien_id);
            });

            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('success', 'Đã nộp bài kiểm tra thành công.');
        }

        $answerPayloads = $request->input('answers', []);
        $essaySummary = [];

        foreach ($baiKiemTra->chiTietCauHois as $chiTietBaiKiemTra) {
            $question = $chiTietBaiKiemTra->cauHoi;
            $payload = $answerPayloads[$chiTietBaiKiemTra->id] ?? [];

            if ($question?->loai_cau_hoi === 'trac_nghiem') {
                $dapAnId = $payload['dap_an_cau_hoi_id'] ?? null;

                if (!$tuDongNop && $chiTietBaiKiemTra->bat_buoc && !$dapAnId) {
                    throw ValidationException::withMessages([
                        'answers.' . $chiTietBaiKiemTra->id . '.dap_an_cau_hoi_id' => 'Vui lòng chọn đáp án cho câu hỏi trắc nghiệm.',
                    ]);
                }
            } else {
                $cauTraLoi = trim((string) ($payload['cau_tra_loi_text'] ?? ''));

                if (!$tuDongNop && $chiTietBaiKiemTra->bat_buoc && $cauTraLoi === '') {
                    throw ValidationException::withMessages([
                        'answers.' . $chiTietBaiKiemTra->id . '.cau_tra_loi_text' => 'Vui lòng nhập câu trả lời cho câu hỏi tự luận.',
                    ]);
                }

                if ($cauTraLoi !== '') {
                    $essaySummary[] = $cauTraLoi;
                }
            }
        }

        DB::transaction(function () use ($baiLam, $baiKiemTra, $answerPayloads, $essaySummary, $tuDongNop) {
            if ($tuDongNop && $baiKiemTra->co_giam_sat) {
                $this->surveillanceLogService->recordAutoSubmit($baiLam, [
                    'trigger' => 'client_auto_submit',
                ]);
            }

            foreach ($baiKiemTra->chiTietCauHois as $chiTietBaiKiemTra) {
                $payload = $answerPayloads[$chiTietBaiKiemTra->id] ?? [];

                $baiLam->chiTietTraLois()->updateOrCreate(
                    [
                        'chi_tiet_bai_kiem_tra_id' => $chiTietBaiKiemTra->id,
                    ],
                    [
                        'ngan_hang_cau_hoi_id' => $chiTietBaiKiemTra->ngan_hang_cau_hoi_id,
                        'dap_an_cau_hoi_id' => $payload['dap_an_cau_hoi_id'] ?? null,
                        'cau_tra_loi_text' => $payload['cau_tra_loi_text'] ?? null,
                    ]
                );
            }

            $baiLam->update([
                'noi_dung_bai_lam' => $essaySummary !== [] ? implode("\n\n", $essaySummary) : null,
                'trang_thai' => 'da_nop',
                'nop_luc' => now(),
                'da_tu_dong_nop' => $tuDongNop,
            ]);

            $baiLam = $this->scoringService->autoGrade($baiLam->fresh());

            $baiLam->update([
                'trang_thai' => $baiLam->trang_thai_cham === 'cho_cham' ? 'cho_cham' : 'da_cham',
            ]);

            $this->surveillanceService->finalizeAttempt($baiLam->fresh());
            $this->ketQuaHocTapService->refreshAllForCourseStudent($baiKiemTra->khoa_hoc_id, $baiLam->hoc_vien_id);
        });

        return redirect()
            ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
            ->with('success', 'Đã nộp bài kiểm tra thành công.');
    }

    public function logSurveillance(Request $request, int $baiLamId)
    {
        $baiLam = $this->findAttemptHocVien($baiLamId, auth()->user()->ma_nguoi_dung);

        if (!$baiLam->can_resume || !$baiLam->baiKiemTra?->co_giam_sat) {
            return response()->json([
                'message' => 'Bài làm hiện không còn ghi nhận giám sát.',
            ], 422);
        }

        $validated = $request->validate([
            'event_type' => 'required|string|max:80',
            'description' => 'nullable|string|max:1000',
            'meta' => 'nullable|array',
        ]);

        $allowedEvents = [
            BaiLamViPhamGiamSat::SU_KIEN_TAB_SWITCH,
            BaiLamViPhamGiamSat::SU_KIEN_WINDOW_BLUR,
            BaiLamViPhamGiamSat::SU_KIEN_WINDOW_FOCUS,
            BaiLamViPhamGiamSat::SU_KIEN_FULLSCREEN_EXIT,
            BaiLamViPhamGiamSat::SU_KIEN_CAMERA_OFF,
            BaiLamViPhamGiamSat::SU_KIEN_COPY_PASTE_BLOCKED,
            BaiLamViPhamGiamSat::SU_KIEN_RIGHT_CLICK_BLOCKED,
        ];

        if (!in_array($validated['event_type'], $allowedEvents, true)) {
            return response()->json([
                'message' => 'Sự kiện giám sát không hợp lệ.',
            ], 422);
        }

        $result = $this->surveillanceLogService->recordEvent(
            $baiLam,
            $validated['event_type'],
            $validated['description'] ?? null,
            $validated['meta'] ?? []
        );

        return response()->json($result);
    }

    public function captureSnapshot(Request $request, int $baiLamId)
    {
        $baiLam = $this->findAttemptHocVien($baiLamId, auth()->user()->ma_nguoi_dung);

        if (!$baiLam->can_resume || !$baiLam->baiKiemTra?->co_giam_sat) {
            return response()->json([
                'message' => 'Bài làm hiện không còn ghi nhận snapshot.',
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:captured,failed',
            'image_data' => 'nullable|string',
            'message' => 'nullable|string|max:1000',
            'meta' => 'nullable|array',
        ]);

        try {
            $snapshot = $this->snapshotService->handleSnapshot(
                $baiLam,
                $validated['status'],
                $validated['image_data'] ?? null,
                $validated['message'] ?? null,
                $validated['meta'] ?? []
            );

            return response()->json([
                'snapshot_id' => $snapshot->id,
                'status' => $snapshot->status,
                'file_url' => $snapshot->file_url,
            ]);
        } catch (\Throwable $exception) {
            $snapshot = $this->snapshotService->recordFailure(
                $baiLam,
                $exception->getMessage(),
                $validated['meta'] ?? []
            );

            return response()->json([
                'snapshot_id' => $snapshot->id,
                'status' => $snapshot->status,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    private function queryBaiKiemTraHocVien(int $hocVienId)
    {
        $khoaHocIds = HocVienKhoaHoc::query()
            ->where('hoc_vien_id', $hocVienId)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->pluck('khoa_hoc_id');

        return BaiKiemTra::query()
            ->where('trang_thai', true)
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai_phat_hanh', 'phat_hanh')
            ->whereIn('khoa_hoc_id', $khoaHocIds)
            ->with([
                'khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                'moduleHoc:id,ten_module,ma_module',
                'lichHoc:id,khoa_hoc_id,module_hoc_id,buoi_so,ngay_hoc',
                'chiTietCauHois.cauHoi.dapAns',
                'baiLams' => fn ($query) => $query
                    ->where('hoc_vien_id', $hocVienId)
                    ->with(['chiTietTraLois.cauHoi', 'chiTietTraLois.dapAn'])
                    ->orderByDesc('lan_lam_thu'),
            ])
            ->orderByDesc('created_at');
    }

    private function findBaiKiemTraHocVien(int $id, int $hocVienId): BaiKiemTra
    {
        return $this->queryBaiKiemTraHocVien($hocVienId)
            ->where('id', $id)
            ->firstOrFail();
    }

    private function findAttemptHocVien(int $id, int $hocVienId): BaiLamBaiKiemTra
    {
        return BaiLamBaiKiemTra::query()
            ->with('baiKiemTra')
            ->where('hoc_vien_id', $hocVienId)
            ->whereKey($id)
            ->firstOrFail();
    }
}
