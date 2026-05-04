<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GiangVien\Concerns\XacThucGiangVienBaiKiemTra;
use App\Models\BaiKiemTra;
use App\Models\NganHangCauHoi;
use App\Services\ExamConfigurationService;
use App\Services\ExamQuestionImportService;
use App\Services\ExamQuestionSelectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaiKiemTraNhapCauHoiController extends Controller
{
    use XacThucGiangVienBaiKiemTra;

    public function __construct(
        private readonly ExamQuestionImportService $importService,
        private readonly ExamQuestionSelectionService $questionSelectionService,
    ) {}

    public function downloadEssayImportTemplate()
    {
        $rows = [
            ['noi_dung', 'goi_y_tra_loi', 'dap_an_mau', 'rubric', 'diem', 'muc_do', 'status', 'note'],
            [
                'Phan tich tinh huong va neu ket luan cua ban.',
                'Goi y ngan cho hoc vien neu can hien thi.',
                'Dap an mau hoac cac y chinh giang vien dung de tham khao.',
                'Y chinh: 4 diem; Lap luan: 3 diem; Trinh bay: 3 diem.',
                '10',
                'trung_binh',
                'san_sang',
                'Dong mau co the xoa truoc khi import.',
            ],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'mau-import-cau-hoi-tu-luan.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeEssayQuestion(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::with('chiTietCauHois.cauHoi')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $validated = $request->validate([
            'noi_dung' => 'required|string|max:50000',
            'goi_y_tra_loi' => 'nullable|string|max:50000',
            'dap_an_mau' => 'nullable|string|max:50000',
            'rubric_cham' => 'nullable|string|max:50000',
            'diem_mac_dinh' => 'required|numeric|min:0.25|max:100',
            'muc_do' => 'required|in:de,trung_binh,kho',
            'thu_tu' => 'nullable|integer|min:1',
            'trang_thai' => 'nullable|in:nhap,san_sang,tam_an',
        ]);

        if (($validated['trang_thai'] ?? NganHangCauHoi::TRANG_THAI_SAN_SANG) !== NganHangCauHoi::TRANG_THAI_SAN_SANG) {
            throw ValidationException::withMessages([
                'trang_thai' => 'Cau hoi gan vao de can o trang thai san sang.',
            ]);
        }

        $cauHoi = DB::transaction(function () use ($baiKiemTra, $validated) {
            $normalizedContent = NganHangCauHoi::normalizeString($validated['noi_dung']);
            $existingQuestion = NganHangCauHoi::query()
                ->where('khoa_hoc_id', $baiKiemTra->khoa_hoc_id)
                ->get()
                ->first(fn (NganHangCauHoi $question) => NganHangCauHoi::normalizeString($question->noi_dung) === $normalizedContent);

            if ($existingQuestion && $existingQuestion->loai_cau_hoi !== NganHangCauHoi::LOAI_TU_LUAN) {
                throw ValidationException::withMessages([
                    'noi_dung' => 'Noi dung nay da ton tai trong kho voi loai cau hoi khac.',
                ]);
            }

            $cauHoi = $existingQuestion ?: NganHangCauHoi::create([
                'khoa_hoc_id' => $baiKiemTra->khoa_hoc_id,
                'module_hoc_id' => $baiKiemTra->module_hoc_id,
                'nguoi_tao_id' => auth()->id(),
                'noi_dung' => $validated['noi_dung'],
                'loai_cau_hoi' => NganHangCauHoi::LOAI_TU_LUAN,
                'kieu_dap_an' => null,
                'muc_do' => $validated['muc_do'],
                'diem_mac_dinh' => round((float) $validated['diem_mac_dinh'], 2),
                'goi_y_tra_loi' => $validated['goi_y_tra_loi'] ?? null,
                'dap_an_mau' => $validated['dap_an_mau'] ?? null,
                'rubric_cham' => $validated['rubric_cham'] ?? null,
                'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
                'co_the_tai_su_dung' => true,
            ]);

            if ($existingQuestion && (
                $existingQuestion->trang_thai !== NganHangCauHoi::TRANG_THAI_SAN_SANG
                || ! $existingQuestion->co_the_tai_su_dung
            )) {
                throw ValidationException::withMessages([
                    'noi_dung' => 'Cau hoi nay da ton tai nhung chua san sang de gan vao de.',
                ]);
            }

            $this->attachQuestionsToExam(
                $baiKiemTra,
                [(int) $cauHoi->id],
                [(int) $cauHoi->id => round((float) $validated['diem_mac_dinh'], 2)],
                isset($validated['thu_tu']) ? (int) $validated['thu_tu'] : null
            );

            return $cauHoi;
        });

        return redirect()
            ->route('giang-vien.bai-kiem-tra.edit', [
                'id' => $baiKiemTra->id,
                'tab' => 'questions',
                'preferred_mode' => $baiKiemTra->fresh()->content_mode_key,
            ])
            ->with('success', 'Da them cau tu luan vao de.')
            ->with('exam_imported_question_ids', [(int) $cauHoi->id]);
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

            $previewId = str()->uuid()->toString();
            session()->put('exam_import_preview_'.$previewId, $preview);

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
                            'question_type' => $row['loai_cau_hoi'] ?? null,
                            'question_type_label' => $row['loai_cau_hoi_label'] ?? null,
                            'correct_answer' => $row['dap_an_dung'] ?? null,
                            'goi_y_tra_loi' => $row['goi_y_tra_loi'] ?? null,
                            'dap_an_mau' => $row['dap_an_mau'] ?? null,
                            'rubric_cham' => $row['rubric_cham'] ?? null,
                            'diem_mac_dinh' => $row['diem_mac_dinh'] ?? null,
                            'muc_do' => $row['muc_do'] ?? null,
                            'status' => $row['status'] ?? null,
                            'note' => $row['note'] ?? null,
                        ];
                    })
                    ->values(),
                'remaining_preview_rows' => max(0, count($preview['data']) - 8),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Không thể phân tích file import lúc này. Vui lòng kiểm tra dữ liệu và thử lại.',
            ], 422);
        }
    }

    public function importConfirm(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $request->validate([
            'preview_id' => 'required|string',
            'preferred_mode' => 'nullable|in:trac_nghiem,tu_luan_tu_do,tu_luan_theo_cau,hon_hop',
        ]);

        $preview = session()->get('exam_import_preview_'.$request->preview_id);
        if (! $preview) {
            return back()->with('error', 'Phiên import đã hết hạn, vui lòng thử lại.');
        }

        try {
            $result = $this->importService->importToBank($preview, $baiKiemTra, auth()->id());

            session()->forget('exam_import_preview_'.$request->preview_id);
            $baiKiemTra->refresh();

            return redirect()
                ->route('giang-vien.bai-kiem-tra.edit', [
                    'id' => $baiKiemTra->id,
                    'tab' => 'questions',
                    'preferred_mode' => $baiKiemTra->content_mode_key,
                ])
                ->with('success', "Đã import thành công {$result['created']} câu hỏi vào ngân hàng. Bạn có thể chọn chúng cho đề thi ngay bây giờ.")
                ->with('exam_imported_question_ids', $result['ids'] ?? []);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Không thể import câu hỏi lúc này. Vui lòng thử lại.');
        }
    }

    /**
     * @param  array<int, int>  $questionIdsToAttach
     * @param  array<int, float>  $scoreOverrides
     * @return array{0: float, 1: string, 2: array<int, int>}
     */
    private function attachQuestionsToExam(
        BaiKiemTra $baiKiemTra,
        array $questionIdsToAttach,
        array $scoreOverrides = [],
        ?int $insertAt = null
    ): array {
        $baiKiemTra->load('chiTietCauHois.cauHoi');

        $existingDetails = $baiKiemTra->chiTietCauHois->sortBy('thu_tu')->values();
        $orderedQuestionIds = $existingDetails
            ->pluck('ngan_hang_cau_hoi_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $scoreMap = $existingDetails
            ->mapWithKeys(fn ($detail) => [(int) $detail->ngan_hang_cau_hoi_id => round((float) $detail->diem_so, 2)])
            ->all();

        $insertIndex = $insertAt !== null
            ? max(0, min(count($orderedQuestionIds), $insertAt - 1))
            : null;

        foreach (array_values(array_unique(array_map('intval', $questionIdsToAttach))) as $questionId) {
            if (in_array($questionId, $orderedQuestionIds, true)) {
                continue;
            }

            if ($insertIndex === null) {
                $orderedQuestionIds[] = $questionId;

                continue;
            }

            array_splice($orderedQuestionIds, $insertIndex, 0, [$questionId]);
            $insertIndex++;
        }

        $questionsById = NganHangCauHoi::query()
            ->whereIn('id', $orderedQuestionIds)
            ->get()
            ->keyBy('id');

        foreach ($orderedQuestionIds as $index => $questionId) {
            $scoreMap[$questionId] = round((float) (
                $scoreOverrides[$questionId]
                ?? $scoreMap[$questionId]
                ?? $questionsById->get($questionId)?->diem_mac_dinh
                ?? ExamConfigurationService::MIN_QUESTION_SCORE
            ), 2);

            if ($scoreMap[$questionId] < ExamConfigurationService::MIN_QUESTION_SCORE) {
                $scoreMap[$questionId] = ExamConfigurationService::MIN_QUESTION_SCORE;
            }
        }

        [$tongDiem, $loaiNoiDung] = $this->questionSelectionService->syncQuestions($baiKiemTra, $orderedQuestionIds, $scoreMap);
        $contentMode = $orderedQuestionIds === []
            ? BaiKiemTra::CHE_DO_TU_LUAN_TU_DO
            : BaiKiemTra::contentModeForLegacyContentType($loaiNoiDung);

        $baiKiemTra->update([
            'tong_diem' => $orderedQuestionIds === [] ? (float) ($baiKiemTra->tong_diem ?: 10) : $tongDiem,
            'loai_noi_dung' => BaiKiemTra::legacyContentTypeForMode($contentMode),
            'che_do_noi_dung' => $contentMode,
            'che_do_tinh_diem' => $orderedQuestionIds === [] ? 'thu_cong' : $baiKiemTra->che_do_tinh_diem,
            'so_cau_goi_diem' => $baiKiemTra->che_do_tinh_diem === 'goi_diem' ? count($orderedQuestionIds) : $baiKiemTra->so_cau_goi_diem,
        ]);

        return [$tongDiem, $contentMode, $orderedQuestionIds];
    }
}
