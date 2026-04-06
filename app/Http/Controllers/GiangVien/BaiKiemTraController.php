<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\GiangVien;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use App\Models\PhanCongModuleGiangVien;
use App\Services\BaiKiemTraScoringService;
use App\Services\ExamConfigurationService;
use App\Services\ExamQuestionSelectionService;
use App\Services\ExamSurveillanceService;
use App\Services\KetQuaHocTapService;
use App\Services\TeacherAssignmentResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaiKiemTraController extends Controller
{
    public function __construct(
        private readonly BaiKiemTraScoringService $scoringService,
        private readonly KetQuaHocTapService $ketQuaHocTapService,
        private readonly TeacherAssignmentResolver $assignmentResolver,
        private readonly \App\Services\ExamQuestionImportService $importService,
        private readonly ExamQuestionSelectionService $questionSelectionService,
        private readonly ExamConfigurationService $examConfigurationService,
        private readonly ExamSurveillanceService $surveillanceService,
    ) {
    }

    public function index(Request $request)
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(!$giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

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
                    $searchQuery->where('tieu_de', 'like', '%' . $search . '%')
                        ->orWhereHas('khoaHoc', function ($courseQuery) use ($search) {
                            $courseQuery->where('ten_khoa_hoc', 'like', '%' . $search . '%')
                                ->orWhere('ma_khoa_hoc', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('moduleHoc', function ($moduleQuery) use ($search) {
                            $moduleQuery->where('ten_module', 'like', '%' . $search . '%')
                                ->orWhere('ma_module', 'like', '%' . $search . '%');
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
        ]);

        $giangVien = auth()->user()?->giangVien;
        abort_if(!$giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        [$moduleId, $lichHoc] = $this->resolveScope($validated);
        $loaiBaiKiemTra = $this->resolveExamType($validated['pham_vi']);
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
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'nhap',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => auth()->id(),
            'trang_thai' => true,
            ...$surveillanceConfig,
        ]);

        return redirect()
            ->route('giang-vien.bai-kiem-tra.edit', $baiKiemTra->id)
            ->with('success', 'Đã tạo khung bài kiểm tra. Hãy cấu hình câu hỏi và gửi duyệt.');
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
            'questionTypeOptions',
            'difficultyOptions',
            'statusOptions',
            'courseModules',
            'selectableQuestionIds',
        ));
    }

    public function editSurveillance(int $id)
    {
        $baiKiemTra = BaiKiemTra::with([
            'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
            'moduleHoc:id,ma_module,ten_module',
            'lichHoc:id,buoi_so,ngay_hoc',
        ])->withCount([
            'chiTietCauHois',
            'baiLams',
            'baiLams as bai_lams_dang_lam_count' => fn ($query) => $query->where('trang_thai', 'dang_lam'),
        ])->findOrFail($id);

        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        return view('pages.hoc-vien.bai-kiem-tra.teacher-surveillance-settings', compact('baiKiemTra'));
    }

    public function update(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::with('chiTietCauHois')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
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
            'che_do_tinh_diem' => 'required|in:goi_diem,thu_cong',
            'so_cau_goi_diem' => 'nullable|required_if:che_do_tinh_diem,goi_diem|integer|min:1',
            'tong_diem_goi_diem' => 'nullable|required_if:che_do_tinh_diem,goi_diem|numeric|min:0.25',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'integer|exists:ngan_hang_cau_hoi,id',
            'question_scores' => 'nullable|array',
            'question_scores.*' => 'nullable|numeric|min:0.25|max:100',
        ]);

        DB::transaction(function () use ($baiKiemTra, $validated, $request) {
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
                'mo_ta' => $validated['mo_ta'] ?? null,
                'thoi_gian_lam_bai' => $validated['thoi_gian_lam_bai'],
                'ngay_mo' => $validated['ngay_mo'] ?? null,
                'ngay_dong' => $validated['ngay_dong'] ?? null,
                'so_lan_duoc_lam' => $validated['so_lan_duoc_lam'],
                'randomize_questions' => $request->boolean('randomize_questions'),
                'randomize_answers' => $request->boolean('randomize_answers'),
                'che_do_tinh_diem' => $validated['che_do_tinh_diem'],
                'so_cau_goi_diem' => $validated['che_do_tinh_diem'] === 'goi_diem' ? $validated['so_cau_goi_diem'] : null,
                ...$surveillanceConfig,
            ]);

            $questionIds = array_values(array_unique(array_map('intval', $validated['question_ids'] ?? [])));
            $questionScores = $this->examConfigurationService->resolveQuestionScores($validated, $questionIds);
            [$tongDiem, $loaiNoiDung] = $this->questionSelectionService->syncQuestions($baiKiemTra, $questionIds, $questionScores);

            $baiKiemTra->update([
                'tong_diem' => $questionIds === [] ? 10 : $tongDiem,
                'loai_noi_dung' => $questionIds === [] ? 'tu_luan' : $loaiNoiDung,
            ]);
        });

        return back()->with('success', 'Đã cập nhật bài kiểm tra.');
    }

    public function updateSurveillanceSettings(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::withCount([
            'baiLams as bai_lams_dang_lam_count' => fn ($query) => $query->where('trang_thai', 'dang_lam'),
        ])->findOrFail($id);

        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        if ((int) $baiKiemTra->bai_lams_dang_lam_count > 0) {
            return back()->with('error', 'KhÃ´ng thá»ƒ thay Ä‘á»•i cáº¥u hÃ¬nh giÃ¡m sÃ¡t khi Ä‘ang cÃ³ há»c viÃªn lÃ m bÃ i.');
        }

        $validated = $request->validate([
            'co_giam_sat' => 'nullable|boolean',
            'bat_buoc_fullscreen' => 'nullable|boolean',
            'bat_buoc_camera' => 'nullable|boolean',
            'so_lan_vi_pham_toi_da' => 'nullable|integer|min:1|max:20',
            'chu_ky_snapshot_giay' => 'nullable|integer|min:10|max:300',
            'tu_dong_nop_khi_vi_pham' => 'nullable|boolean',
            'chan_copy_paste' => 'nullable|boolean',
            'chan_chuot_phai' => 'nullable|boolean',
        ]);

        $baiKiemTra->update($this->surveillanceService->normalizeExamConfig($validated, $request));

        return redirect()
            ->route('giang-vien.bai-kiem-tra.surveillance.edit', $baiKiemTra->id)
            ->with('success', 'ÄÃ£ cáº­p nháº­t cáº¥u hÃ¬nh giÃ¡m sÃ¡t cho bÃ i kiá»ƒm tra.');
    }

    public function importPreview(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,docx,pdf,csv,txt',
        ]);

        try {
            $preview = $this->importService->previewForExam($request->file('file'), $baiKiemTra);
            
            // Store preview in session for confirm step
            $previewId = str()->uuid()->toString();
            session()->put('exam_import_preview_' . $previewId, $preview);

            return response()->json([
                'success' => true,
                'preview_id' => $previewId,
                'summary' => $preview['summary'],
                'original_name' => $preview['original_name'],
                'source_format' => $preview['source_format'],
                'preview_rows' => collect($preview['data'])
                    ->take(8)
                    ->map(function (array $row) {
                        return [
                            'line' => $row['line'] ?? null,
                            'question' => $row['noi_dung_cau_hoi'] ?? null,
                            'correct_answer' => $row['dap_an_dung'] ?? null,
                            'status' => $row['status'] ?? null,
                            'note' => $row['note'] ?? null,
                        ];
                    })
                    ->values(),
                'remaining_preview_rows' => max(0, count($preview['data']) - 8),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function importConfirm(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $request->validate([
            'preview_id' => 'required|string',
        ]);

        $preview = session()->get('exam_import_preview_' . $request->preview_id);
        if (!$preview) {
            return back()->with('error', 'Phiên import đã hết hạn, vui lòng thử lại.');
        }

        try {
            $result = $this->importService->importToBank($preview, $baiKiemTra, auth()->id());
            session()->forget('exam_import_preview_' . $request->preview_id);

            return redirect()
                ->route('giang-vien.bai-kiem-tra.edit', [
                    'id' => $baiKiemTra->id,
                    'tab' => 'questions',
                ])
                ->with('success', "Đã import thành công {$result['created']} câu hỏi vào ngân hàng. Bạn có thể chọn chúng cho đề thi ngay bây giờ.")
                ->with('exam_imported_question_ids', $result['ids'] ?? []);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi khi import: ' . $e->getMessage());
        }
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

        return back()->with('success', 'Đã gửi bài kiểm tra cho admin duyệt.');
    }

    public function destroy(int $id)
    {
        $baiKiemTra = BaiKiemTra::withCount('baiLams')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        if ($baiKiemTra->bai_lams_count > 0) {
            return back()->with('error', 'Không thể xóa bài kiểm tra đã có học viên làm bài.');
        }

        $baiKiemTra->delete();

        return back()->with('success', 'Đã xóa bài kiểm tra.');
    }

    public function chamDiemIndex()
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(!$giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

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

        foreach ($baiLam->chiTietTraLois as $chiTietTraLoi) {
            if ($chiTietTraLoi->cauHoi?->loai_cau_hoi !== 'tu_luan') {
                continue;
            }

            $grade = $grades[$chiTietTraLoi->id] ?? null;
            $diemToiDa = (float) ($chiTietTraLoi->chiTietBaiKiemTra?->diem_so ?? 0);
            $diemTuLuan = $grade['diem_tu_luan'] ?? null;

            if ($diemTuLuan === null || $diemTuLuan === '') {
                throw ValidationException::withMessages([
                    'grades.' . $chiTietTraLoi->id . '.diem_tu_luan' => 'Vui lòng nhập điểm cho mỗi câu tự luận.',
                ]);
            }

            if (!is_numeric($diemTuLuan) || (float) $diemTuLuan < 0 || (float) $diemTuLuan > $diemToiDa) {
                throw ValidationException::withMessages([
                    'grades.' . $chiTietTraLoi->id . '.diem_tu_luan' => 'Điểm phải nằm trong khoảng 0 - ' . $diemToiDa . '.',
                ]);
            }

            $normalizedGrades[$chiTietTraLoi->id] = [
                'diem_tu_luan' => $diemTuLuan,
                'nhan_xet' => $grade['nhan_xet'] ?? null,
            ];
        }

        DB::transaction(function () use ($baiLam, $normalizedGrades, $giangVien) {
            $this->scoringService->applyManualGrades($baiLam, $normalizedGrades, $giangVien);
            $this->ketQuaHocTapService->refreshAllForCourseStudent($baiLam->baiKiemTra->khoa_hoc_id, $baiLam->hoc_vien_id);
        });

        return redirect()
            ->route('giang-vien.cham-diem.show', $baiLam->id)
            ->with('success', 'Đã chấm bài và cập nhật kết quả học tập.');
    }

    public function updateSurveillanceReview(Request $request, int $id)
    {
        $baiLam = BaiLamBaiKiemTra::with(['baiKiemTra', 'nguoiHauKiem'])->findOrFail($id);
        $giangVien = auth()->user()?->giangVien;
        $this->authorizeTeacherForExam($giangVien, $baiLam->baiKiemTra);

        if (!$baiLam->baiKiemTra->co_giam_sat) {
            return back()->with('error', 'Bài làm này không áp dụng giám sát.');
        }

        $reviewStatusOptions = array_keys($this->surveillanceService->reviewStatusOptions());

        $validated = $request->validate([
            'trang_thai_giam_sat' => 'required|string|in:' . implode(',', $reviewStatusOptions),
            'ghi_chu_giam_sat' => 'nullable|string|max:2000',
        ]);

        $this->surveillanceService->updateReview($baiLam, $validated, auth()->id());

        return back()->with('success', 'Đã cập nhật trạng thái hậu kiểm cho bài làm.');
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

        if ($validated['pham_vi'] === 'module' && !$moduleId) {
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

    private function authorizeTeacherForScope(GiangVien $giangVien, int $khoaHocId, ?int $moduleId, string $loaiBaiKiemTra): void
    {
        $query = PhanCongModuleGiangVien::query()
            ->where('giang_vien_id', $giangVien->id)
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('trang_thai', 'da_nhan');

        if ($loaiBaiKiemTra !== 'cuoi_khoa') {
            $query->where('module_hoc_id', $moduleId);
        }

        abort_unless($query->exists(), 403, 'Bạn không được phân công cho bài kiểm tra này.');
    }

    private function authorizeTeacherForExam(?GiangVien $giangVien, BaiKiemTra $baiKiemTra): void
    {
        abort_if(!$giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        $this->authorizeTeacherForScope(
            $giangVien,
            (int) $baiKiemTra->khoa_hoc_id,
            $baiKiemTra->module_hoc_id ? (int) $baiKiemTra->module_hoc_id : null,
            $baiKiemTra->loai_bai_kiem_tra
        );
    }

    /**
     * @return array<int, int>
     */
    private function getAcceptedModuleIds(GiangVien $giangVien): array
    {
        return PhanCongModuleGiangVien::query()
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->pluck('module_hoc_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function getAcceptedCourseIds(GiangVien $giangVien): array
    {
        return PhanCongModuleGiangVien::query()
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->pluck('khoa_hoc_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function buildAccessibleExamQuery(array $moduleIds, array $courseIds)
    {
        return BaiKiemTra::query()
            ->where(function ($query) use ($moduleIds, $courseIds) {
                $hasCondition = false;

                if ($moduleIds !== []) {
                    $query->whereIn('module_hoc_id', $moduleIds);
                    $hasCondition = true;
                }

                if ($courseIds !== []) {
                    $method = $hasCondition ? 'orWhere' : 'where';

                    $query->{$method}(function ($courseQuery) use ($courseIds) {
                        $courseQuery->whereIn('khoa_hoc_id', $courseIds)
                            ->where(function ($examQuery) {
                                $examQuery->whereNull('module_hoc_id')
                                    ->orWhere('loai_bai_kiem_tra', 'cuoi_khoa');
                            });
                    });

                    $hasCondition = true;
                }

                if (!$hasCondition) {
                    $query->whereRaw('1 = 0');
                }
            });
    }
}

