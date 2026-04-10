<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportQuestionDocumentRequest;
use App\Http\Requests\UpsertNganHangCauHoiRequest;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use App\Services\QuestionBankImportService;
use App\Services\QuestionBankCourseCatalogService;
use App\Services\QuestionImport\ParsedQuestionExportService;
use App\Support\Imports\ImportTemplateRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class NganHangCauHoiController extends Controller
{
    public function __construct(
        private readonly ImportTemplateRegistry $templateRegistry,
        private readonly QuestionBankImportService $questionBankImportService,
        private readonly QuestionBankCourseCatalogService $questionBankCourseCatalogService,
        private readonly ParsedQuestionExportService $parsedQuestionExportService,
    ) {
    }

    private function getCourseCatalog(): array
    {
        return $this->questionBankCourseCatalogService->buildCatalogForUser(auth()->user());
    }

    private function getViewOptions(): array
    {
        return [
            'courseTypeOptions' => [
                'mau' => $this->questionBankCourseCatalogService->courseTypeLabel('mau'),
                'hoat_dong' => $this->questionBankCourseCatalogService->courseTypeLabel('hoat_dong'),
            ],
            'difficultyOptions' => [
                'de' => 'Dễ',
                'trung_binh' => 'Trung bình',
                'kho' => 'Khó',
            ],
            'statusOptions' => [
                NganHangCauHoi::TRANG_THAI_NHAP => 'Nháp',
                NganHangCauHoi::TRANG_THAI_SAN_SANG => 'Sẵn sàng',
                NganHangCauHoi::TRANG_THAI_TAM_AN => 'Tạm ẩn',
            ],
            'questionTypeOptions' => [
                NganHangCauHoi::LOAI_TRAC_NGHIEM => 'Trắc nghiệm',
                NganHangCauHoi::LOAI_TU_LUAN => 'Tự luận',
            ],
            'answerModeOptions' => [
                NganHangCauHoi::KIEU_MOT_DAP_AN => 'Một đáp án đúng',
                NganHangCauHoi::KIEU_NHIEU_DAP_AN => 'Nhiều đáp án đúng',
                NganHangCauHoi::KIEU_DUNG_SAI => 'Đúng / Sai',
            ],
        ];

        return [
            'courseTypeOptions' => [
                'mau' => 'Khóa học mẫu',
                'hoat_dong' => 'Khóa học hoạt động',
            ],
            'difficultyOptions' => [
                'de' => 'Dễ',
                'trung_binh' => 'Trung bình',
                'kho' => 'Khó',
            ],
            'statusOptions' => [
                NganHangCauHoi::TRANG_THAI_NHAP => 'Nháp',
                NganHangCauHoi::TRANG_THAI_SAN_SANG => 'Sẵn sàng',
                NganHangCauHoi::TRANG_THAI_TAM_AN => 'Tạm ẩn',
            ],
            'questionTypeOptions' => [
                NganHangCauHoi::LOAI_TRAC_NGHIEM => 'Trắc nghiệm',
                NganHangCauHoi::LOAI_TU_LUAN => 'Tự luận',
            ],
            'answerModeOptions' => [
                NganHangCauHoi::KIEU_MOT_DAP_AN => 'Một đáp án đúng',
                NganHangCauHoi::KIEU_NHIEU_DAP_AN => 'Nhiều đáp án đúng',
                NganHangCauHoi::KIEU_DUNG_SAI => 'Đúng / Sai',
            ],
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $catalog = $this->getCourseCatalog();
        $khoaHocs = $catalog['khoaHocs'];
        $sampleCourses = $catalog['sampleCourses'];
        $activeCourses = $catalog['activeCourses'];
        $modules = $catalog['modules'];
        $khoaHocIds = $khoaHocs->pluck('id')->all();

        $baseQuery = NganHangCauHoi::query();

        if ($user->isGiangVien()) {
            $baseQuery->whereIn('khoa_hoc_id', $khoaHocIds);
        }

        $this->applyIndexFilters($baseQuery, $request);

        $viewMode = in_array((string) $request->string('view_mode'), ['compact', 'detail'], true)
            ? (string) $request->string('view_mode')
            : 'compact';

        $questionBankSummaries = $this->buildQuestionBankSummaries(clone $baseQuery);
        $cauHois = null;

        if ($viewMode !== 'compact') {
            $cauHois = (clone $baseQuery)
                ->with(['khoaHoc', 'moduleHoc', 'nguoiTao', 'dapAns'])
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString();
        }

        return view('pages.admin.question-bank.index', array_merge(
            compact(
                'cauHois',
                'khoaHocs',
                'sampleCourses',
                'activeCourses',
                'modules',
                'viewMode',
                'questionBankSummaries'
            ),
            $this->getViewOptions()
        ));
    }

    private function applyIndexFilters(Builder $query, Request $request): void
    {
        if ($request->filled('course_type')) {
            $query->theoLoaiKhoaHoc((string) $request->string('course_type'));
        }

        if ($request->filled('khoa_hoc_id')) {
            $query->where('khoa_hoc_id', $request->integer('khoa_hoc_id'));
        }

        if ($request->filled('module_hoc_id')) {
            $query->where('module_hoc_id', $request->integer('module_hoc_id'));
        }

        if ($request->filled('loai_cau_hoi')) {
            $query->where('loai_cau_hoi', (string) $request->string('loai_cau_hoi'));
        }

        if ($request->filled('kieu_dap_an')) {
            $query->where('kieu_dap_an', (string) $request->string('kieu_dap_an'));
        }

        if ($request->filled('muc_do')) {
            $query->where('muc_do', (string) $request->string('muc_do'));
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', (string) $request->string('trang_thai'));
        }

        if ($request->filled('co_the_tai_su_dung')) {
            $query->where('co_the_tai_su_dung', $request->string('co_the_tai_su_dung') === '1');
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nestedQuery) use ($search) {
                $nestedQuery->where('noi_dung', 'like', "%{$search}%")
                    ->orWhere('ma_cau_hoi', 'like', "%{$search}%");
            });
        }
    }

    private function buildQuestionBankSummaries(Builder $query): Collection
    {
        return $query
            ->with([
                'khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc,loai',
                'moduleHoc:id,khoa_hoc_id,ten_module,ma_module',
            ])
            ->orderBy('khoa_hoc_id')
            ->orderBy('module_hoc_id')
            ->get()
            ->groupBy(function (NganHangCauHoi $item) {
                return $item->khoa_hoc_id . '::' . ($item->module_hoc_id ?? 'course');
            })
            ->map(function (Collection $items) {
                /** @var NganHangCauHoi $first */
                $first = $items->first();
                $previewQuestions = $items
                    ->sortByDesc('created_at')
                    ->take(4)
                    ->map(function (NganHangCauHoi $question) {
                        return [
                            'id' => $question->id,
                            'code' => $question->ma_cau_hoi,
                            'content' => $question->noi_dung,
                            'status_label' => $question->trang_thai_label,
                            'status_color' => $question->trang_thai_color,
                            'type_label' => $question->loai_cau_hoi_label,
                        ];
                    })
                    ->values();

                return [
                    'khoa_hoc_id' => $first->khoa_hoc_id,
                    'module_hoc_id' => $first->module_hoc_id,
                    'khoa_hoc_ten' => $first->khoaHoc?->ten_khoa_hoc ?? 'Không rõ khóa học',
                    'khoa_hoc_ma' => $first->khoaHoc?->ma_khoa_hoc,
                    'course_type' => $first->khoaHoc?->loai,
                    'course_type_label' => $first->loai_khoa_hoc_label,
                    'course_type_color' => $first->loai_khoa_hoc_color,
                    'module_hoc_ten' => $first->moduleHoc?->ten_module,
                    'module_hoc_ma' => $first->moduleHoc?->ma_module,
                    'group_label' => $first->moduleHoc?->ten_module
                        ? ($first->khoaHoc?->ten_khoa_hoc . ' / ' . $first->moduleHoc->ten_module)
                        : ($first->khoaHoc?->ten_khoa_hoc ?? 'Không rõ khóa học'),
                    'total_questions' => $items->count(),
                    'objective_questions' => $items->where('loai_cau_hoi', NganHangCauHoi::LOAI_TRAC_NGHIEM)->count(),
                    'essay_questions' => $items->where('loai_cau_hoi', NganHangCauHoi::LOAI_TU_LUAN)->count(),
                    'single_correct_questions' => $items->where('kieu_dap_an', NganHangCauHoi::KIEU_MOT_DAP_AN)->count(),
                    'multiple_correct_questions' => $items->where('kieu_dap_an', NganHangCauHoi::KIEU_NHIEU_DAP_AN)->count(),
                    'true_false_questions' => $items->where('kieu_dap_an', NganHangCauHoi::KIEU_DUNG_SAI)->count(),
                    'ready_questions' => $items->where('trang_thai', NganHangCauHoi::TRANG_THAI_SAN_SANG)->count(),
                    'draft_questions' => $items->where('trang_thai', NganHangCauHoi::TRANG_THAI_NHAP)->count(),
                    'hidden_questions' => $items->where('trang_thai', NganHangCauHoi::TRANG_THAI_TAM_AN)->count(),
                    'reusable_questions' => $items->where('co_the_tai_su_dung', true)->count(),
                    'latest_created_at' => $items->max('created_at'),
                    'preview_questions' => $previewQuestions,
                    'remaining_preview_count' => max(0, $items->count() - $previewQuestions->count()),
                ];
            })
            ->sortBy([
                fn (array $summary) => $summary['course_type'] === 'mau' ? 0 : 1,
                fn (array $summary) => mb_strtolower((string) $summary['group_label'], 'UTF-8'),
            ])
            ->values();
    }

    public function create()
    {
        $catalog = $this->getCourseCatalog();

        return view('pages.admin.question-bank.form', array_merge([
            'cauHoi' => new NganHangCauHoi([
                'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
                'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
                'muc_do' => 'trung_binh',
                'diem_mac_dinh' => 1,
                'trang_thai' => NganHangCauHoi::TRANG_THAI_NHAP,
                'co_the_tai_su_dung' => true,
            ]),
            'khoaHocs' => $catalog['khoaHocs'],
            'sampleCourses' => $catalog['sampleCourses'],
            'activeCourses' => $catalog['activeCourses'],
            'modules' => $catalog['modules'],
            'action' => route('admin.kiem-tra-online.cau-hoi.store'),
            'method' => 'POST',
            'title' => 'Thêm mới câu hỏi',
        ], $this->getViewOptions()));
    }

    public function store(UpsertNganHangCauHoiRequest $request)
    {
        $user = auth()->user();
        $payload = $this->buildQuestionPayload($request->validated());

        $this->authorizeCourseAccess($user, $payload['khoa_hoc_id']);

        if (NganHangCauHoi::isDuplicate($payload['khoa_hoc_id'], $payload['noi_dung'])) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học đã chọn.',
            ]);
        }

        DB::transaction(function () use ($payload, $user) {
            $cauHoi = NganHangCauHoi::create([
                'khoa_hoc_id' => $payload['khoa_hoc_id'],
                'module_hoc_id' => $payload['module_hoc_id'],
                'nguoi_tao_id' => $user->id,
                'ma_cau_hoi' => $payload['ma_cau_hoi'],
                'noi_dung' => $payload['noi_dung'],
                'loai_cau_hoi' => $payload['loai_cau_hoi'],
                'kieu_dap_an' => $payload['kieu_dap_an'],
                'muc_do' => $payload['muc_do'],
                'diem_mac_dinh' => $payload['diem_mac_dinh'],
                'goi_y_tra_loi' => $payload['goi_y_tra_loi'],
                'giai_thich_dap_an' => $payload['giai_thich_dap_an'],
                'trang_thai' => $payload['trang_thai'],
                'co_the_tai_su_dung' => $payload['co_the_tai_su_dung'],
            ]);

            $this->syncAnswers($cauHoi, $payload['answers']);
        });

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', 'Đã thêm câu hỏi thành công.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::with('dapAns')->findOrFail($id);
        $catalog = $this->getCourseCatalog();

        $this->authorizeCourseAccess($user, (int) $cauHoi->khoa_hoc_id);

        return view('pages.admin.question-bank.form', array_merge([
            'cauHoi' => $cauHoi,
            'khoaHocs' => $catalog['khoaHocs'],
            'sampleCourses' => $catalog['sampleCourses'],
            'activeCourses' => $catalog['activeCourses'],
            'modules' => $catalog['modules'],
            'action' => route('admin.kiem-tra-online.cau-hoi.update', $cauHoi->id),
            'method' => 'PUT',
            'title' => 'Chỉnh sửa câu hỏi',
        ], $this->getViewOptions()));
    }

    public function update(UpsertNganHangCauHoiRequest $request, $id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::with('dapAns')->findOrFail($id);
        $payload = $this->buildQuestionPayload($request->validated(), $cauHoi);

        $this->authorizeCourseAccess($user, $payload['khoa_hoc_id'], (int) $cauHoi->khoa_hoc_id);

        if (NganHangCauHoi::isDuplicate($payload['khoa_hoc_id'], $payload['noi_dung'], $cauHoi->id)) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học đã chọn.',
            ]);
        }

        DB::transaction(function () use ($cauHoi, $payload) {
            $cauHoi->update([
                'khoa_hoc_id' => $payload['khoa_hoc_id'],
                'module_hoc_id' => $payload['module_hoc_id'],
                'ma_cau_hoi' => $payload['ma_cau_hoi'] ?: $cauHoi->ma_cau_hoi ?: NganHangCauHoi::generateQuestionCode(),
                'noi_dung' => $payload['noi_dung'],
                'loai_cau_hoi' => $payload['loai_cau_hoi'],
                'kieu_dap_an' => $payload['kieu_dap_an'],
                'muc_do' => $payload['muc_do'],
                'diem_mac_dinh' => $payload['diem_mac_dinh'],
                'goi_y_tra_loi' => $payload['goi_y_tra_loi'],
                'giai_thich_dap_an' => $payload['giai_thich_dap_an'],
                'trang_thai' => $payload['trang_thai'],
                'co_the_tai_su_dung' => $payload['co_the_tai_su_dung'],
            ]);

            $this->syncAnswers($cauHoi, $payload['answers']);
        });

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', 'Đã cập nhật câu hỏi thành công.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::findOrFail($id);

        $this->authorizeCourseAccess($user, (int) $cauHoi->khoa_hoc_id);

        $cauHoi->delete();

        return back()->with('success', 'Đã xóa câu hỏi.');
    }

    public function toggleStatus($id)
    {
        $cauHoi = NganHangCauHoi::findOrFail($id);
        $this->authorizeCourseAccess(auth()->user(), (int) $cauHoi->khoa_hoc_id);

        $nextStatus = match ($cauHoi->trang_thai) {
            NganHangCauHoi::TRANG_THAI_SAN_SANG => NganHangCauHoi::TRANG_THAI_TAM_AN,
            NganHangCauHoi::TRANG_THAI_TAM_AN => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            default => NganHangCauHoi::TRANG_THAI_SAN_SANG,
        };

        $cauHoi->update(['trang_thai' => $nextStatus]);

        return back()->with('success', 'Đã cập nhật trạng thái hiển thị của câu hỏi.');
    }

    public function toggleReusable($id)
    {
        $cauHoi = NganHangCauHoi::findOrFail($id);
        $this->authorizeCourseAccess(auth()->user(), (int) $cauHoi->khoa_hoc_id);

        $cauHoi->update([
            'co_the_tai_su_dung' => !$cauHoi->co_the_tai_su_dung,
        ]);

        return back()->with('success', 'Đã cập nhật cờ tái sử dụng của câu hỏi.');
    }

    public function downloadTemplate()
    {
        $template = $this->templateRegistry->questionBankMcq();

        if (!$this->templateRegistry->exists(ImportTemplateRegistry::QUESTION_BANK_MCQ)) {
            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.index')
                ->with('error', 'Không tìm thấy file mẫu import câu hỏi trong storage.');
        }

        clearstatcache(true, $template['absolute_path']);

        return response()->download($template['absolute_path'], (string) $template['download_name'], [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function import(ImportQuestionDocumentRequest $request)
    {
        $user = auth()->user();
        $khoaHocId = $request->integer('khoa_hoc_id');
        $this->authorizeCourseAccess($user, $khoaHocId);
        $khoaHoc = $this->questionBankCourseCatalogService->ensureCourseMatchesType(
            $khoaHocId,
            $request->input('course_type')
        );
        $courseType = $this->questionBankCourseCatalogService->resolveCourseType(
            $request->input('course_type'),
            $khoaHocId
        );
        $moduleHocId = $this->resolveImportModuleId($request, $khoaHocId);
        $moduleHoc = $moduleHocId ? ModuleHoc::find($moduleHocId) : null;

        try {
            $preview = $this->questionBankImportService->buildPreview($request->file('file_import'), $khoaHocId);

            session(['import_preview' => [
                'khoa_hoc_id' => $khoaHocId,
                'khoa_hoc_ten' => $khoaHoc->ten_khoa_hoc,
                'course_type' => $courseType,
                'course_type_label' => $this->questionBankCourseCatalogService->courseTypeLabel($courseType),
                'course_type_color' => $this->questionBankCourseCatalogService->courseTypeBadgeColor($courseType),
                'module_hoc_id' => $moduleHocId,
                'module_hoc_ten' => $moduleHoc?->ten_module,
                'source_format' => $preview['source_format'],
                'profile' => $preview['profile'],
                'original_name' => $preview['original_name'],
                'data' => $preview['data'],
                'summary' => $preview['summary'],
                'user_id' => auth()->id(),
            ]]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('question_document_import.preview_failed', [
                'user_id' => auth()->id(),
                'khoa_hoc_id' => $khoaHocId,
                'module_hoc_id' => $moduleHocId,
                'file_name' => $request->file('file_import')?->getClientOriginalName(),
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'file_import' => 'Không thể phân tích file đã tải lên. Vui lòng kiểm tra định dạng file và thử lại.',
            ]);
        }

        return redirect()->route('admin.kiem-tra-online.cau-hoi.preview');
    }

    public function preview()
    {
        $preview = $this->getOwnedImportPreview();
        if ($preview === null) {
            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.index')
                ->with('error', 'Không tìm thấy dữ liệu xem trước hoặc phiên import không còn hợp lệ.');
        }

        return view('pages.admin.question-bank.preview', compact('preview'));
    }

    public function exportPreview(Request $request)
    {
        $preview = $this->getOwnedImportPreview();
        if ($preview === null) {
            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.index')
                ->with('error', 'Không tìm thấy dữ liệu xem trước hoặc phiên import không còn hợp lệ.');
        }

        $scope = strtolower(trim((string) $request->string('scope', 'all')));

        try {
            $export = $this->parsedQuestionExportService->export($preview, $scope);
        } catch (Throwable $exception) {
            Log::error('question_document_import.export_failed', [
                'user_id' => auth()->id(),
                'scope' => $scope,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.preview')
                ->with('error', 'Không thể xuất file Excel từ dữ liệu preview. Vui lòng thử lại.');
        }

        return response()->download($export['path'], $export['download_name'])->deleteFileAfterSend(true);
    }

    public function confirmImport(Request $request)
    {
        $preview = $this->getOwnedImportPreview();
        if ($preview === null) {
            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.index')
                ->with('error', 'Phiên import không còn hợp lệ. Vui lòng thực hiện lại.');
        }

        $khoaHocId = (int) $preview['khoa_hoc_id'];
        $userId = auth()->user()->id;
        $moduleHocId = isset($preview['module_hoc_id']) ? (int) $preview['module_hoc_id'] : null;

        try {
            $importResult = $this->questionBankImportService->confirmImport($preview, $khoaHocId, $userId, $moduleHocId);
        } catch (Throwable $exception) {
            Log::error('question_document_import.confirm_failed', [
                'user_id' => auth()->id(),
                'khoa_hoc_id' => $khoaHocId,
                'module_hoc_id' => $moduleHocId,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.index')
                ->with('error', 'Không thể lưu dữ liệu import vào hệ thống. Vui lòng thử lại.');
        }

        session()->forget('import_preview');

        $message = "Đã import thành công {$importResult['created']} câu hỏi.";

        if (($importResult['skipped_duplicate_db'] ?? 0) > 0) {
            $message .= " {$importResult['skipped_duplicate_db']} câu bị bỏ qua vì đã trùng trong hệ thống ở thời điểm xác nhận.";
        }

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', $message);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getOwnedImportPreview(): ?array
    {
        $preview = session('import_preview');
        if (!is_array($preview)) {
            return null;
        }

        if (($preview['user_id'] ?? null) != auth()->id()) {
            session()->forget('import_preview');

            return null;
        }

        return $preview;
    }

    private function authorizeCourseAccess($user, int $khoaHocId, ?int $secondKhoaHocId = null): void
    {
        if (!$user->isGiangVien()) {
            return;
        }

        $giangVien = $user->giangVien;
        $courseIds = $giangVien
            ? $giangVien->khoaHocDuocPhanCong()->pluck('khoa_hoc.id')->unique()->map(fn ($id) => (int) $id)->all()
            : [];

        foreach (array_filter([$khoaHocId, $secondKhoaHocId]) as $courseId) {
            abort_unless(in_array((int) $courseId, $courseIds, true), 403, 'Bạn không được phân công cho khóa học này.');
        }
    }

    private function resolveImportModuleId(ImportQuestionDocumentRequest $request, int $khoaHocId): ?int
    {
        $moduleHocId = $request->filled('module_hoc_id')
            ? $request->integer('module_hoc_id')
            : null;

        if ($moduleHocId === null) {
            return null;
        }

        $moduleBelongsToCourse = ModuleHoc::query()
            ->where('id', $moduleHocId)
            ->where('khoa_hoc_id', $khoaHocId)
            ->exists();

        if (!$moduleBelongsToCourse) {
            throw ValidationException::withMessages([
                'module_hoc_id' => 'Module không thuộc khóa học đã chọn.',
            ]);
        }

        return $moduleHocId;
    }

    private function buildQuestionPayload(array $validated, ?NganHangCauHoi $existing = null): array
    {
        $noiDung = trim((string) ($validated['noi_dung'] ?? $validated['noi_dung_cau_hoi'] ?? ''));
        if ($noiDung === '') {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi là bắt buộc.',
            ]);
        }

        $khoaHocId = (int) $validated['khoa_hoc_id'];
        $courseType = $this->questionBankCourseCatalogService->resolveCourseType(
            $validated['course_type'] ?? null,
            $khoaHocId
        );
        $this->questionBankCourseCatalogService->ensureCourseMatchesType($khoaHocId, $courseType);

        $moduleHocId = isset($validated['module_hoc_id']) && $validated['module_hoc_id'] !== null
            ? (int) $validated['module_hoc_id']
            : null;

        if ($moduleHocId !== null) {
            $moduleBelongsToCourse = ModuleHoc::query()
                ->where('id', $moduleHocId)
                ->where('khoa_hoc_id', $khoaHocId)
                ->exists();

            if (!$moduleBelongsToCourse) {
                throw ValidationException::withMessages([
                    'module_hoc_id' => 'Module không thuộc khóa học đã chọn.',
                ]);
            }
        }

        $loaiCauHoi = (string) $validated['loai_cau_hoi'];
        $kieuDapAn = $loaiCauHoi === NganHangCauHoi::LOAI_TU_LUAN
            ? null
            : (string) ($validated['kieu_dap_an'] ?? NganHangCauHoi::KIEU_MOT_DAP_AN);

        $answers = $this->buildAnswers($validated, $loaiCauHoi, $kieuDapAn);

        return [
            'course_type' => $courseType,
            'khoa_hoc_id' => $khoaHocId,
            'module_hoc_id' => $moduleHocId,
            'ma_cau_hoi' => trim((string) ($validated['ma_cau_hoi'] ?? '')) ?: ($existing?->ma_cau_hoi ?: NganHangCauHoi::generateQuestionCode()),
            'noi_dung' => $noiDung,
            'loai_cau_hoi' => $loaiCauHoi,
            'kieu_dap_an' => $kieuDapAn,
            'muc_do' => (string) ($validated['muc_do'] ?? 'trung_binh'),
            'diem_mac_dinh' => (float) ($validated['diem_mac_dinh'] ?? 1),
            'goi_y_tra_loi' => trim((string) ($validated['goi_y_tra_loi'] ?? '')) ?: null,
            'giai_thich_dap_an' => trim((string) ($validated['giai_thich_dap_an'] ?? '')) ?: null,
            'trang_thai' => (string) ($validated['trang_thai'] ?? NganHangCauHoi::TRANG_THAI_NHAP),
            'co_the_tai_su_dung' => array_key_exists('co_the_tai_su_dung', $validated)
                ? (bool) $validated['co_the_tai_su_dung']
                : true,
            'answers' => $answers,
        ];
    }

    private function buildAnswers(array $validated, string $loaiCauHoi, ?string $kieuDapAn): array
    {
        if ($loaiCauHoi === NganHangCauHoi::LOAI_TU_LUAN) {
            return [];
        }

        if ($kieuDapAn === NganHangCauHoi::KIEU_DUNG_SAI) {
            $answers = $this->buildTrueFalseAnswers($validated);
            $this->validateAnswers($answers, NganHangCauHoi::KIEU_DUNG_SAI);

            return $answers;
        }

        $answers = !empty($validated['dap_ans'])
            ? $this->normalizeStructuredAnswers($validated, $kieuDapAn ?? NganHangCauHoi::KIEU_MOT_DAP_AN)
            : $this->normalizeLegacyAnswers($validated);

        $this->validateAnswers($answers, $kieuDapAn ?? NganHangCauHoi::KIEU_MOT_DAP_AN);

        return $answers;
    }

    private function buildTrueFalseAnswers(array $validated): array
    {
        $dapAnDungSai = (string) ($validated['dap_an_dung_sai'] ?? '');

        if (!in_array($dapAnDungSai, ['dung', 'sai'], true)) {
            throw ValidationException::withMessages([
                'dap_an_dung_sai' => 'Vui lòng chọn đáp án đúng cho câu hỏi Đúng / Sai.',
            ]);
        }

        return [
            [
                'ky_hieu' => 'A',
                'noi_dung' => 'Đúng',
                'is_dap_an_dung' => $dapAnDungSai === 'dung',
            ],
            [
                'ky_hieu' => 'B',
                'noi_dung' => 'Sai',
                'is_dap_an_dung' => $dapAnDungSai === 'sai',
            ],
        ];
    }

    private function normalizeStructuredAnswers(array $validated, string $kieuDapAn): array
    {
        $correctAnswerKey = (string) ($validated['correct_answer_key'] ?? '');
        $correctAnswerKeys = collect($validated['correct_answer_keys'] ?? [])
            ->map(fn ($key) => (string) $key)
            ->values()
            ->all();
        $legacyCorrectToken = NganHangCauHoi::normalizeString($validated['dap_an_dung'] ?? '');

        $answers = [];
        $answerOrder = 0;

        foreach (($validated['dap_ans'] ?? []) as $rawKey => $answer) {
            $noiDung = trim((string) ($answer['noi_dung'] ?? ''));
            if ($noiDung === '') {
                continue;
            }

            $kyHieu = trim((string) ($answer['ky_hieu'] ?? ''));
            if ($kyHieu === '') {
                $kyHieu = $this->resolveAnswerKey($answerOrder);
            }

            $normalizedRawKey = (string) $rawKey;
            $normalizedKyHieu = NganHangCauHoi::normalizeString($kyHieu);
            $normalizedNoiDung = NganHangCauHoi::normalizeString($noiDung);

            $isCorrect = match ($kieuDapAn) {
                NganHangCauHoi::KIEU_MOT_DAP_AN => ($correctAnswerKey !== '' && $normalizedRawKey === $correctAnswerKey)
                    || ($legacyCorrectToken !== '' && ($normalizedKyHieu === $legacyCorrectToken || $normalizedNoiDung === $legacyCorrectToken)),
                NganHangCauHoi::KIEU_NHIEU_DAP_AN => in_array($normalizedRawKey, $correctAnswerKeys, true),
                default => false,
            };

            $answers[] = [
                'ky_hieu' => $kyHieu,
                'noi_dung' => $noiDung,
                'is_dap_an_dung' => $isCorrect,
            ];

            $answerOrder++;
        }

        return $answers;
    }

    private function normalizeLegacyAnswers(array $validated): array
    {
        $requiredFields = ['dap_an_dung', 'dap_an_sai_1', 'dap_an_sai_2', 'dap_an_sai_3'];
        foreach ($requiredFields as $field) {
            if (blank($validated[$field] ?? null)) {
                throw ValidationException::withMessages([
                    $field => 'Vui lòng nhập đầy đủ 4 đáp án cho câu hỏi trắc nghiệm.',
                ]);
            }
        }

        return [
            ['ky_hieu' => 'A', 'noi_dung' => trim((string) $validated['dap_an_dung']), 'is_dap_an_dung' => true],
            ['ky_hieu' => 'B', 'noi_dung' => trim((string) $validated['dap_an_sai_1']), 'is_dap_an_dung' => false],
            ['ky_hieu' => 'C', 'noi_dung' => trim((string) $validated['dap_an_sai_2']), 'is_dap_an_dung' => false],
            ['ky_hieu' => 'D', 'noi_dung' => trim((string) $validated['dap_an_sai_3']), 'is_dap_an_dung' => false],
        ];
    }

    private function validateAnswers(array $answers, string $kieuDapAn): void
    {
        if (count($answers) < 2) {
            throw ValidationException::withMessages([
                'dap_ans' => 'Câu hỏi trắc nghiệm cần ít nhất 2 đáp án.',
            ]);
        }

        $normalizedContents = collect($answers)
            ->map(fn ($answer) => NganHangCauHoi::normalizeString($answer['noi_dung'] ?? ''));

        if ($normalizedContents->contains('')) {
            throw ValidationException::withMessages([
                'dap_ans' => 'Nội dung đáp án không được để trống.',
            ]);
        }

        if ($normalizedContents->unique()->count() !== $normalizedContents->count()) {
            throw ValidationException::withMessages([
                'dap_ans' => 'Các đáp án không được trùng nhau.',
            ]);
        }

        $correctCount = collect($answers)->where('is_dap_an_dung', true)->count();

        if ($kieuDapAn === NganHangCauHoi::KIEU_MOT_DAP_AN && $correctCount !== 1) {
            throw ValidationException::withMessages([
                'correct_answer_key' => 'Câu hỏi một đáp án đúng phải có chính xác 1 đáp án đúng.',
            ]);
        }

        if ($kieuDapAn === NganHangCauHoi::KIEU_NHIEU_DAP_AN && $correctCount < 2) {
            throw ValidationException::withMessages([
                'correct_answer_keys' => 'Câu hỏi nhiều đáp án đúng phải có ít nhất 2 đáp án đúng.',
            ]);
        }

        if ($kieuDapAn === NganHangCauHoi::KIEU_DUNG_SAI && (count($answers) !== 2 || $correctCount !== 1)) {
            throw ValidationException::withMessages([
                'dap_an_dung_sai' => 'Câu hỏi Đúng / Sai phải có đúng 2 đáp án và 1 đáp án đúng.',
            ]);
        }
    }

    private function resolveAnswerKey(int $index): string
    {
        return chr(65 + $index);
    }

    private function syncAnswers(NganHangCauHoi $cauHoi, array $answers): void
    {
        $cauHoi->dapAns()->delete();

        if ($answers === []) {
            return;
        }

        $cauHoi->dapAns()->createMany(
            collect($answers)->values()->map(function (array $answer, int $index) {
                return [
                    'ky_hieu' => $answer['ky_hieu'] ?? $this->resolveAnswerKey($index),
                    'noi_dung' => $answer['noi_dung'],
                    'is_dap_an_dung' => (bool) ($answer['is_dap_an_dung'] ?? false),
                    'thu_tu' => $index + 1,
                ];
            })->all()
        );
    }
}
