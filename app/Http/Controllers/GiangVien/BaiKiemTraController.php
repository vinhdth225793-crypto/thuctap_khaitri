<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GiangVien\Concerns\XacThucGiangVienBaiKiemTra;
use App\Models\BaiKiemTra;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use App\Services\ExamConfigurationService;
use App\Services\ExamQuestionSelectionService;
use App\Services\ExamSurveillanceService;
use App\Services\TeacherAssignmentResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaiKiemTraController extends Controller
{
    use XacThucGiangVienBaiKiemTra;

    public function __construct(
        private readonly TeacherAssignmentResolver $assignmentResolver,
        private readonly ExamQuestionSelectionService $questionSelectionService,
        private readonly ExamConfigurationService $examConfigurationService,
        private readonly ExamSurveillanceService $surveillanceService,
    ) {}

    public function index(Request $request)
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(! $giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        $moduleIds = $this->getAcceptedModuleIds($giangVien);
        $courseIds = $this->getAcceptedCourseIds($giangVien);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'pham_vi' => $request->filled('pham_vi') ? (string) $request->query('pham_vi') : null,
            'trang_thai_duyet' => $request->filled('trang_thai_duyet') ? (string) $request->query('trang_thai_duyet') : null,
            'trang_thai_phat_hanh' => $request->filled('trang_thai_phat_hanh') ? (string) $request->query('trang_thai_phat_hanh') : null,
        ];

        $accessibleExamQuery = $this->buildAccessibleExamQuery($moduleIds, $courseIds);

        $stats = [
            'tong' => (clone $accessibleExamQuery)->count(),
            'nhap' => (clone $accessibleExamQuery)->where('trang_thai_duyet', 'nhap')->count(),
            'cho_duyet' => (clone $accessibleExamQuery)->where('trang_thai_duyet', 'cho_duyet')->count(),
            'phat_hanh' => (clone $accessibleExamQuery)->where('trang_thai_phat_hanh', 'phat_hanh')->count(),
        ];

        $baiKiemTras = $this->buildAccessibleExamQuery($moduleIds, $courseIds)
            ->with([
                'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'moduleHoc:id,ma_module,ten_module',
                'lichHoc:id,buoi_so,ngay_hoc',
            ])
            ->withCount(['chiTietCauHois', 'baiLams'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('tieu_de', 'like', '%'.$search.'%')
                        ->orWhereHas('khoaHoc', function ($courseQuery) use ($search) {
                            $courseQuery->where('ten_khoa_hoc', 'like', '%'.$search.'%')
                                ->orWhere('ma_khoa_hoc', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('moduleHoc', function ($moduleQuery) use ($search) {
                            $moduleQuery->where('ten_module', 'like', '%'.$search.'%')
                                ->orWhere('ma_module', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($filters['pham_vi'], fn ($query, $phamVi) => $query->where('pham_vi', $phamVi))
            ->when($filters['trang_thai_duyet'], fn ($query, $status) => $query->where('trang_thai_duyet', $status))
            ->when($filters['trang_thai_phat_hanh'], fn ($query, $status) => $query->where('trang_thai_phat_hanh', $status))
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('pages.giang-vien.bai-kiem-tra.index', compact(
            'baiKiemTras',
            'stats',
            'filters',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'tieu_de' => 'required|string|max:255',
            'pham_vi' => 'required|in:module,buoi_hoc,cuoi_khoa',
            'thoi_gian_lam_bai' => 'required|integer|min:1|max:300',
            'module_hoc_id' => 'nullable|exists:module_hoc,id',
            'lich_hoc_id' => 'nullable|exists:lich_hoc,id',
            'mo_ta' => 'nullable|string',
            'co_giam_sat' => 'nullable|boolean',
            'che_do_noi_dung' => 'nullable|in:trac_nghiem,tu_luan_tu_do,tu_luan_theo_cau,hon_hop',
        ]);

        $giangVien = auth()->user()?->giangVien;
        abort_if(! $giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        [$moduleId, $lichHoc] = $this->resolveScope($validated);
        $loaiBaiKiemTra = $this->resolveExamType($validated['pham_vi']);
        $preferredContentMode = (string) ($validated['che_do_noi_dung'] ?? 'tu_luan_tu_do');
        if (! in_array($preferredContentMode, BaiKiemTra::contentModeKeys(), true)) {
            $preferredContentMode = BaiKiemTra::CHE_DO_TU_LUAN_TU_DO;
        }
        $surveillanceConfig = $this->surveillanceService->normalizeExamConfig($validated, $request);

        $this->authorizeTeacherForScope($giangVien, (int) $validated['khoa_hoc_id'], $moduleId, $loaiBaiKiemTra);

        $baiKiemTra = BaiKiemTra::create([
            'khoa_hoc_id' => $validated['khoa_hoc_id'],
            'module_hoc_id' => $moduleId,
            'lich_hoc_id' => $lichHoc?->id,
            'tieu_de' => $validated['tieu_de'],
            'mo_ta' => $validated['mo_ta'] ?? null,
            'thoi_gian_lam_bai' => $validated['thoi_gian_lam_bai'],
            'pham_vi' => $validated['pham_vi'],
            'loai_bai_kiem_tra' => $loaiBaiKiemTra,
            'loai_noi_dung' => BaiKiemTra::legacyContentTypeForMode($preferredContentMode),
            'che_do_noi_dung' => $preferredContentMode,
            'trang_thai_duyet' => 'nhap',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => auth()->id(),
            'trang_thai' => true,
            ...$surveillanceConfig,
        ]);

        return redirect()
            ->route('giang-vien.bai-kiem-tra.edit', [
                'id' => $baiKiemTra->id,
                'preferred_mode' => $preferredContentMode,
                'tab' => 'info',
            ])
            ->with('success', 'Đã tạo khung bài kiểm tra. Hãy cấu hình thông tin chi tiết, câu hỏi và gửi duyệt.');
    }

    public function edit(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::with([
            'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc,phuong_thuc_danh_gia',
            'moduleHoc:id,ma_module,ten_module',
            'lichHoc:id,buoi_so,ngay_hoc',
            'chiTietCauHois.cauHoi.dapAns',
            'baiLams' => fn ($query) => $query->with('hocVien:ma_nguoi_dung,ho_ten,email')->orderByDesc('created_at')->limit(10),
        ])->findOrFail($id);

        $giangVien = auth()->user()?->giangVien;
        $this->authorizeTeacherForExam($giangVien, $baiKiemTra);

        $questionFilters = [
            'search' => trim((string) $request->string('question_search')),
            'module_hoc_id' => $request->filled('question_module_hoc_id') ? $request->integer('question_module_hoc_id') : null,
            'loai_cau_hoi' => $request->filled('question_loai_cau_hoi') ? (string) $request->string('question_loai_cau_hoi') : null,
            'muc_do' => $request->filled('question_muc_do') ? (string) $request->string('question_muc_do') : null,
            'trang_thai' => $request->filled('question_trang_thai') ? (string) $request->string('question_trang_thai') : null,
        ];
        $activeTab = in_array($request->query('tab'), ['info', 'scoring', 'import', 'questions'], true)
            ? (string) $request->query('tab')
            : 'info';
        $preferredContentMode = $request->filled('preferred_mode')
            ? (string) $request->query('preferred_mode')
            : $baiKiemTra->content_mode_key;

        if (
            ! $request->filled('preferred_mode')
            && $preferredContentMode === BaiKiemTra::CHE_DO_TU_LUAN_TU_DO
            && in_array($activeTab, ['import', 'questions'], true)
        ) {
            $preferredContentMode = BaiKiemTra::CHE_DO_TU_LUAN_THEO_CAU;
        }

        if (! in_array($preferredContentMode, BaiKiemTra::contentModeKeys(), true)) {
            $preferredContentMode = $baiKiemTra->content_mode_key;
        }

        $availableQuestions = $this->questionSelectionService
            ->buildDisplayQuery($baiKiemTra, $questionFilters)
            ->with('dapAns')
            ->orderByDesc('created_at')
            ->get();

        $selectableQuestionIds = $this->questionSelectionService->selectableQuestionIds($baiKiemTra);
        $courseModules = ModuleHoc::query()
            ->where('khoa_hoc_id', $baiKiemTra->khoa_hoc_id)
            ->orderBy('thu_tu_module')
            ->get(['id', 'khoa_hoc_id', 'ma_module', 'ten_module']);

        $assignmentId = $this->assignmentResolver->resolveForExam($giangVien->id, $baiKiemTra);
        if ($assignmentId !== null) {
            if ($baiKiemTra->relationLoaded('lichHoc') && $baiKiemTra->lichHoc) {
                $baiKiemTra->lichHoc->setAttribute('module_hoc_id', $assignmentId);
            }

            $baiKiemTra->setAttribute('module_hoc_id', $assignmentId);
        }

        $questionTypeOptions = [
            NganHangCauHoi::LOAI_TRAC_NGHIEM => 'Trắc nghiệm',
            NganHangCauHoi::LOAI_TU_LUAN => 'Tự luận',
        ];
        $difficultyOptions = [
            'de' => 'Dễ',
            'trung_binh' => 'Trung bình',
            'kho' => 'Khó',
        ];
        $statusOptions = [
            NganHangCauHoi::TRANG_THAI_SAN_SANG => 'Sẵn sàng',
            NganHangCauHoi::TRANG_THAI_NHAP => 'Nháp',
            NganHangCauHoi::TRANG_THAI_TAM_AN => 'Tạm ẩn',
        ];

        return view('pages.giang-vien.bai-kiem-tra.edit', compact(
            'baiKiemTra',
            'availableQuestions',
            'questionFilters',
            'activeTab',
            'preferredContentMode',
            'questionTypeOptions',
            'difficultyOptions',
            'statusOptions',
            'courseModules',
            'selectableQuestionIds',
        ));
    }

    public function update(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::with('chiTietCauHois')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'mo_ta_tu_luan_tu_do' => 'nullable|string',
            'thoi_gian_lam_bai' => 'required|integer|min:1|max:300',
            'ngay_mo' => 'nullable|date',
            'ngay_dong' => 'nullable|date|after:ngay_mo',
            'so_lan_duoc_lam' => 'required|integer|min:1|max:10',
            'randomize_questions' => 'nullable|boolean',
            'randomize_answers' => 'nullable|boolean',
            'co_giam_sat' => 'nullable|boolean',
            'bat_buoc_fullscreen' => 'nullable|boolean',
            'bat_buoc_camera' => 'nullable|boolean',
            'so_lan_vi_pham_toi_da' => 'nullable|integer|min:1|max:20',
            'chu_ky_snapshot_giay' => 'nullable|integer|min:10|max:300',
            'tu_dong_nop_khi_vi_pham' => 'nullable|boolean',
            'chan_copy_paste' => 'nullable|boolean',
            'chan_chuot_phai' => 'nullable|boolean',
            'che_do_noi_dung' => 'nullable|in:trac_nghiem,tu_luan_tu_do,tu_luan_theo_cau,hon_hop',
            'che_do_tinh_diem' => 'required|in:goi_diem,thu_cong',
            'so_cau_goi_diem' => 'nullable|integer|min:1',
            'tong_diem_goi_diem' => 'nullable|numeric|min:0.25',
            'tong_diem_tu_luan_tu_do' => 'nullable|numeric|min:0.25|max:1000',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'integer|exists:ngan_hang_cau_hoi,id',
            'question_scores' => 'nullable|array',
            'question_scores.*' => 'nullable|numeric|min:0.25|max:100',
            'action_after_save' => 'nullable|in:submit_for_approval',
        ]);

        $explicitContentMode = $request->filled('che_do_noi_dung');
        $submittedQuestionIds = array_values(array_unique(array_map('intval', $validated['question_ids'] ?? [])));
        $requestedContentMode = $explicitContentMode
            ? (string) $validated['che_do_noi_dung']
            : $baiKiemTra->content_mode_key;

        if (! $explicitContentMode) {
            $requestedContentMode = $submittedQuestionIds === []
                ? BaiKiemTra::CHE_DO_TU_LUAN_TU_DO
                : $this->inferContentModeFromQuestionIds($baiKiemTra, $submittedQuestionIds);
        }

        $defaultFreeEssayScore = $baiKiemTra->is_free_essay ? ($baiKiemTra->tong_diem ?: 10) : 10;
        $freeEssayTotalScore = round((float) ($validated['tong_diem_tu_luan_tu_do'] ?? $defaultFreeEssayScore), 2);

        $essayPromptForSave = filled($validated['mo_ta_tu_luan_tu_do'] ?? null)
            ? $validated['mo_ta_tu_luan_tu_do']
            : ($validated['mo_ta'] ?? null);

        if (($requestedContentMode === BaiKiemTra::CHE_DO_TU_LUAN_TU_DO || $submittedQuestionIds === []) && blank($essayPromptForSave)) {
            throw ValidationException::withMessages([
                'mo_ta' => 'Vui lòng nhập đề kiểm tra tự luận trước khi lưu.',
            ]);
        }

        DB::transaction(function () use ($baiKiemTra, $validated, $request, $explicitContentMode, $requestedContentMode, $freeEssayTotalScore, $submittedQuestionIds, $essayPromptForSave) {
            $questionIds = $submittedQuestionIds;

            if ($requestedContentMode === 'tu_luan_tu_do') {
                $questionIds = [];
            }

            $this->ensureQuestionSelectionMatchesContentMode(
                $baiKiemTra,
                $questionIds,
                $requestedContentMode ?? '',
                $explicitContentMode
            );

            $scoringMode = $requestedContentMode === 'tu_luan_tu_do'
                ? 'thu_cong'
                : $validated['che_do_tinh_diem'];

            $surveillanceConfig = $request->hasAny([
                'co_giam_sat',
                'bat_buoc_fullscreen',
                'bat_buoc_camera',
                'so_lan_vi_pham_toi_da',
                'chu_ky_snapshot_giay',
                'tu_dong_nop_khi_vi_pham',
                'chan_copy_paste',
                'chan_chuot_phai',
            ])
                ? $this->surveillanceService->normalizeExamConfig($validated, $request)
                : [
                    'co_giam_sat' => $baiKiemTra->co_giam_sat,
                    'bat_buoc_fullscreen' => $baiKiemTra->bat_buoc_fullscreen,
                    'bat_buoc_camera' => $baiKiemTra->bat_buoc_camera,
                    'so_lan_vi_pham_toi_da' => $baiKiemTra->so_lan_vi_pham_toi_da,
                    'chu_ky_snapshot_giay' => $baiKiemTra->chu_ky_snapshot_giay,
                    'tu_dong_nop_khi_vi_pham' => $baiKiemTra->tu_dong_nop_khi_vi_pham,
                    'chan_copy_paste' => $baiKiemTra->chan_copy_paste,
                    'chan_chuot_phai' => $baiKiemTra->chan_chuot_phai,
                ];

            $baiKiemTra->update([
                'tieu_de' => $validated['tieu_de'],
                'mo_ta' => $essayPromptForSave,
                'thoi_gian_lam_bai' => $validated['thoi_gian_lam_bai'],
                'ngay_mo' => $validated['ngay_mo'] ?? null,
                'ngay_dong' => $validated['ngay_dong'] ?? null,
                'so_lan_duoc_lam' => $validated['so_lan_duoc_lam'],
                'randomize_questions' => $request->boolean('randomize_questions'),
                'randomize_answers' => $request->boolean('randomize_answers'),
                'che_do_tinh_diem' => $scoringMode,
                'so_cau_goi_diem' => $scoringMode === 'goi_diem' ? ($validated['so_cau_goi_diem'] ?? null) : null,
                ...$surveillanceConfig,
            ]);

            $questionScores = $questionIds === []
                ? []
                : $this->examConfigurationService->resolveQuestionScores(
                    array_merge($validated, ['che_do_tinh_diem' => $scoringMode]),
                    $questionIds
                );
            [$tongDiem, $loaiNoiDung] = $this->questionSelectionService->syncQuestions($baiKiemTra, $questionIds, $questionScores);
            $contentModeToPersist = $questionIds === []
                ? BaiKiemTra::CHE_DO_TU_LUAN_TU_DO
                : match ($loaiNoiDung) {
                    'trac_nghiem' => BaiKiemTra::CHE_DO_TRAC_NGHIEM,
                    'hon_hop' => BaiKiemTra::CHE_DO_HON_HOP,
                    default => BaiKiemTra::CHE_DO_TU_LUAN_THEO_CAU,
                };

            $baiKiemTra->update([
                'tong_diem' => $questionIds === [] ? $freeEssayTotalScore : $tongDiem,
                'loai_noi_dung' => BaiKiemTra::legacyContentTypeForMode($contentModeToPersist),
                'che_do_noi_dung' => $contentModeToPersist,
            ]);
        });

        if (($validated['action_after_save'] ?? null) === 'submit_for_approval') {
            $baiKiemTra->refresh()->load(['chiTietCauHois.cauHoi']);
            $this->examConfigurationService->ensureReadyForApproval($baiKiemTra);

            $baiKiemTra->update([
                'trang_thai_duyet' => 'cho_duyet',
                'trang_thai_phat_hanh' => 'nhap',
                'de_xuat_duyet_luc' => now(),
            ]);

            return back()->with('success', 'Đã lưu cấu hình và gửi bài kiểm tra cho admin duyệt.');
        }

        return back()->with('success', 'Đã cập nhật bài kiểm tra.');
    }

    public function submitForApproval(int $id)
    {
        $baiKiemTra = BaiKiemTra::with(['chiTietCauHois.cauHoi'])->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);
        $this->examConfigurationService->ensureReadyForApproval($baiKiemTra);

        $baiKiemTra->update([
            'trang_thai_duyet' => 'cho_duyet',
            'trang_thai_phat_hanh' => 'nhap',
            'de_xuat_duyet_luc' => now(),
        ]);

        try {
            app(\App\Services\NotificationService::class)->notifyExamSubmitted(
                $baiKiemTra->tieu_de,
                $baiKiemTra->id
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Đã gửi bài kiểm tra cho admin duyệt.');
    }

    public function publish(int $id)
    {
        $baiKiemTra = BaiKiemTra::with(['chiTietCauHois.cauHoi'])->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        if ($baiKiemTra->trang_thai_duyet !== 'da_duyet') {
            return back()->with('error', 'Chỉ bài đã được admin duyệt mới được phát hành cho học viên.');
        }

        if ($baiKiemTra->trang_thai_phat_hanh === 'phat_hanh') {
            return back()->with('success', 'Bài kiểm tra này đang được phát hành cho học viên.');
        }

        $this->examConfigurationService->ensureReadyForApproval($baiKiemTra);

        $baiKiemTra->update([
            'trang_thai_phat_hanh' => 'phat_hanh',
            'phat_hanh_luc' => now(),
            'trang_thai' => true,
        ]);

        return back()->with('success', 'Đã phát hành bài kiểm tra cho học viên.');
    }

    public function destroy(int $id)
    {
        $baiKiemTra = BaiKiemTra::withCount('baiLams')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        if ($baiKiemTra->bai_lams_count > 0) {
            return back()->with('error', 'Không thể xóa bài kiểm tra đã có học viên làm bài.');
        }

        if (! in_array($baiKiemTra->trang_thai_duyet, ['nhap', 'cho_duyet', 'tu_choi'])) {
            return back()->with('error', 'Chỉ có thể xóa bài kiểm tra ở trạng thái nháp, chờ duyệt hoặc bị từ chối.');
        }

        $baiKiemTra->delete();

        return back()->with('success', 'Đã xóa bài kiểm tra.');
    }

    /**
     * @return array{0: int|null, 1: LichHoc|null}
     */
    private function resolveScope(array $validated): array
    {
        $moduleId = isset($validated['module_hoc_id']) ? (int) $validated['module_hoc_id'] : null;
        $lichHoc = null;

        if ($validated['pham_vi'] === 'buoi_hoc') {
            $lichHoc = LichHoc::query()
                ->where('khoa_hoc_id', $validated['khoa_hoc_id'])
                ->findOrFail($validated['lich_hoc_id']);

            $moduleId = (int) $lichHoc->module_hoc_id;
        }

        if ($validated['pham_vi'] === 'module' && ! $moduleId) {
            throw ValidationException::withMessages([
                'module_hoc_id' => 'Vui lòng chọn module cho bài kiểm tra này.',
            ]);
        }

        return [$moduleId, $lichHoc];
    }

    private function resolveExamType(string $phamVi): string
    {
        return match ($phamVi) {
            'cuoi_khoa' => 'cuoi_khoa',
            'buoi_hoc' => 'buoi_hoc',
            default => 'module',
        };
    }

    /**
     * @param  array<int, int>  $questionIds
     */
    private function inferContentModeFromQuestionIds(BaiKiemTra $baiKiemTra, array $questionIds): string
    {
        $questions = $this->questionSelectionService
            ->buildSelectableQuery($baiKiemTra)
            ->whereIn('id', array_values(array_unique(array_map('intval', $questionIds))))
            ->get(['id', 'loai_cau_hoi']);

        return BaiKiemTra::contentModeForQuestionTypes(
            $questions->contains(fn (NganHangCauHoi $question) => $question->loai_cau_hoi === NganHangCauHoi::LOAI_TRAC_NGHIEM),
            $questions->contains(fn (NganHangCauHoi $question) => $question->loai_cau_hoi === NganHangCauHoi::LOAI_TU_LUAN),
        );
    }

    private function ensureQuestionSelectionMatchesContentMode(
        BaiKiemTra $baiKiemTra,
        array $questionIds,
        string $requestedContentMode,
        bool $explicitContentMode
    ): void {
        if ($requestedContentMode === BaiKiemTra::CHE_DO_TU_LUAN_TU_DO) {
            return;
        }

        if ($questionIds === []) {
            throw ValidationException::withMessages([
                'question_ids' => 'Vui lòng chọn ít nhất một câu hỏi để lưu loại nội dung bài kiểm tra này.',
            ]);
        }

        $selectedQuestions = $this->questionSelectionService
            ->buildSelectableQuery($baiKiemTra)
            ->whereIn('id', $questionIds)
            ->get();

        if ($selectedQuestions->count() !== count($questionIds)) {
            throw ValidationException::withMessages([
                'question_ids' => 'Danh sách câu hỏi có mục không hợp lệ, ngoài phạm vi đề hoặc không còn ở trạng thái sẵn sàng.',
            ]);
        }

        $objectiveCount = $selectedQuestions->where('loai_cau_hoi', NganHangCauHoi::LOAI_TRAC_NGHIEM)->count();
        $essayCount = $selectedQuestions->where('loai_cau_hoi', NganHangCauHoi::LOAI_TU_LUAN)->count();

        match ($requestedContentMode) {
            BaiKiemTra::CHE_DO_TRAC_NGHIEM => $essayCount > 0
                ? throw ValidationException::withMessages([
                    'question_ids' => 'Chế độ trắc nghiệm chỉ cho phép chọn câu hỏi trắc nghiệm.',
                ])
                : null,
            BaiKiemTra::CHE_DO_TU_LUAN_THEO_CAU => $objectiveCount > 0
                ? throw ValidationException::withMessages([
                    'question_ids' => 'Chế độ tự luận theo câu chỉ cho phép chọn câu hỏi tự luận.',
                ])
                : null,
            BaiKiemTra::CHE_DO_HON_HOP => ($objectiveCount === 0 || $essayCount === 0)
                ? throw ValidationException::withMessages([
                    'question_ids' => 'Chế độ hỗn hợp cần ít nhất một câu trắc nghiệm và một câu tự luận.',
                ])
                : null,
            default => null,
        };
    }
}
