<?php

namespace App\Services\QuestionImport;

use App\Models\NganHangCauHoi;

class ParsedQuestionValidator
{
    private const STATUS_VALID = 'hop_le';
    private const STATUS_DUPLICATE_FILE = 'trung_lap_trong_file';
    private const STATUS_DUPLICATE_DB = 'trung_lap_trong_he_thong';
    private const STATUS_INVALID = 'loi_du_lieu';

    /**
     * @param  array<int, array<string, mixed>>  $parsedQuestions
     * @return array{data:array<int, array<string, mixed>>, summary:array<string, int>}
     */
    public function buildPreviewData(array $parsedQuestions, int $khoaHocId): array
    {
        $summary = [
            'total' => 0,
            'valid' => 0,
            'duplicate_file' => 0,
            'duplicate_db' => 0,
            'error' => 0,
            'needs_review' => 0,
        ];
        $previewData = [];
        $contentSetInFile = [];

        foreach ($parsedQuestions as $parsedQuestion) {
            $summary['total']++;
            $previewRow = $this->buildPreviewRow($parsedQuestion);
            $normalizedContent = NganHangCauHoi::normalizeString($previewRow['noi_dung_cau_hoi']);

            if ($previewRow['status'] === self::STATUS_VALID && $normalizedContent !== '') {
                if (isset($contentSetInFile[$normalizedContent])) {
                    $previewRow['status'] = self::STATUS_DUPLICATE_FILE;
                    $previewRow['validation_status'] = 'trung_trong_file';
                    $previewRow['note'] = 'Trùng với câu hỏi tại dòng ' . $contentSetInFile[$normalizedContent] . ' trong file.';
                } else {
                    $contentSetInFile[$normalizedContent] = $previewRow['line'];

                    if (NganHangCauHoi::isDuplicate($khoaHocId, $previewRow['noi_dung_cau_hoi'])) {
                        $previewRow['status'] = self::STATUS_DUPLICATE_DB;
                        $previewRow['validation_status'] = 'trung_trong_he_thong';
                        $previewRow['note'] = 'Câu hỏi đã tồn tại trong ngân hàng của khóa học này.';
                    }
                }
            }

            if (in_array((string) ($previewRow['validation_status'] ?? ''), [
                'khong_xac_dinh_dap_an_dung',
                'dap_an_dung_khong_khop',
                'nhieu_hon_mot_dap_an_dung',
            ], true)) {
                $summary['needs_review']++;
            }

            match ($previewRow['status']) {
                self::STATUS_VALID => $summary['valid']++,
                self::STATUS_DUPLICATE_FILE => $summary['duplicate_file']++,
                self::STATUS_DUPLICATE_DB => $summary['duplicate_db']++,
                default => $summary['error']++,
            };

            $previewData[] = $previewRow;
        }

        return [
            'data' => $previewData,
            'summary' => $summary,
        ];
    }

    /**
     * @param  array<string, mixed>  $parsedQuestion
     * @return array<string, mixed>
     */
    private function buildPreviewRow(array $parsedQuestion): array
    {
        $question = trim((string) ($parsedQuestion['noi_dung'] ?? ''));
        $questionType = (string) ($parsedQuestion['loai'] ?? NganHangCauHoi::LOAI_TRAC_NGHIEM);
        if (!in_array($questionType, [NganHangCauHoi::LOAI_TRAC_NGHIEM, NganHangCauHoi::LOAI_TU_LUAN], true)) {
            $questionType = NganHangCauHoi::LOAI_TRAC_NGHIEM;
        }

        $answers = collect($parsedQuestion['dap_an'] ?? [])
            ->map(function (array $answer, int $index) {
                return [
                    'ky_hieu' => $answer['thu_tu_hien_thi'] ?? chr(65 + $index),
                    'noi_dung' => trim((string) ($answer['noi_dung'] ?? '')),
                    'is_dap_an_dung' => (bool) ($answer['is_correct'] ?? false),
                ];
            })
            ->values()
            ->all();

        $validationStatus = (string) ($parsedQuestion['trang_thai_parse'] ?? self::STATUS_VALID);
        $note = $parsedQuestion['ghi_chu_loi'] ?? null;
        $fallbackCorrectDisplay = $this->resolveFallbackCorrectDisplay($parsedQuestion);
        $goiYTraLoi = trim((string) ($parsedQuestion['goi_y_tra_loi'] ?? ''));

        if ($validationStatus === self::STATUS_VALID) {
            if ($questionType === NganHangCauHoi::LOAI_TU_LUAN) {
                [$validationStatus, $note] = $this->validateStructuredQuestion($question, $answers, $questionType);
            } else {
                [$answers, $validationStatus, $note] = $this->resolveCorrectAnswerSignals($answers, $parsedQuestion);

                if ($validationStatus === self::STATUS_VALID) {
                    [$validationStatus, $note] = $this->validateStructuredQuestion($question, $answers, $questionType);
                }
            }
        }

        $correctAnswers = collect($answers)
            ->where('is_dap_an_dung', true)
            ->pluck('noi_dung')
            ->values();

        return [
            'line' => (int) ($parsedQuestion['line'] ?? $parsedQuestion['so_thu_tu'] ?? 0),
            'noi_dung_cau_hoi' => $question,
            'loai_cau_hoi' => $questionType,
            'loai_cau_hoi_label' => $questionType === NganHangCauHoi::LOAI_TU_LUAN ? 'Tự luận' : 'Trắc nghiệm',
            'answers' => $answers,
            'answers_display' => array_map(fn (array $answer) => $answer['noi_dung'], $answers),
            'dap_an_dung' => $questionType === NganHangCauHoi::LOAI_TU_LUAN
                ? 'Giảng viên chấm tay'
                : ($correctAnswers->count() === 1 ? $correctAnswers->first() : $fallbackCorrectDisplay),
            'status' => $validationStatus === self::STATUS_VALID ? self::STATUS_VALID : self::STATUS_INVALID,
            'validation_status' => $validationStatus,
            'note' => $note,
            'import_answers' => $validationStatus === self::STATUS_VALID && $questionType === NganHangCauHoi::LOAI_TRAC_NGHIEM ? $answers : [],
            'goi_y_tra_loi' => $goiYTraLoi !== '' ? $goiYTraLoi : null,
            'nguon_file' => $parsedQuestion['nguon_file'] ?? null,
        ];
    }

    /**
     * @param  array<int, array{ky_hieu:string, noi_dung:string, is_dap_an_dung:bool}>  $answers
     * @param  array<string, mixed>  $parsedQuestion
     * @return array{array<int, array{ky_hieu:string, noi_dung:string, is_dap_an_dung:bool}>, string, string|null}
     */
    private function resolveCorrectAnswerSignals(array $answers, array $parsedQuestion): array
    {
        $styleIndexes = collect($answers)
            ->filter(fn (array $answer) => (bool) ($answer['is_dap_an_dung'] ?? false))
            ->keys()
            ->map(fn ($index) => (int) $index)
            ->values()
            ->all();

        $explicitReferences = collect($parsedQuestion['dap_an_dung_refs'] ?? [])
            ->map(fn ($reference) => trim((string) $reference))
            ->filter(fn (string $reference) => $reference !== '')
            ->values()
            ->all();

        if ($explicitReferences === [] && filled($parsedQuestion['dap_an_dung_text'] ?? null)) {
            $explicitReferences = [trim((string) $parsedQuestion['dap_an_dung_text'])];
        }

        $explicitIndexes = [];
        $unmatchedReferences = [];

        foreach ($explicitReferences as $reference) {
            $matchedIndex = $this->matchCorrectAnswerReference($answers, $reference);

            if ($matchedIndex === null) {
                $unmatchedReferences[] = $reference;
                continue;
            }

            $explicitIndexes[] = $matchedIndex;
        }

        $explicitIndexes = array_values(array_unique($explicitIndexes));

        if ($unmatchedReferences !== []) {
            return [
                $answers,
                'dap_an_dung_khong_khop',
                'Dòng đáp án đúng không khớp với bất kỳ đáp án nào: ' . implode(' | ', $unmatchedReferences) . '.',
            ];
        }

        $resolvedIndexes = array_values(array_unique(array_merge($styleIndexes, $explicitIndexes)));

        if ($resolvedIndexes === []) {
            return [
                $answers,
                'khong_xac_dinh_dap_an_dung',
                'Chưa xác định được đáp án đúng. Cần kiểm tra thủ công trước khi import.',
            ];
        }

        if (count($resolvedIndexes) > 1) {
            return [
                $answers,
                'nhieu_hon_mot_dap_an_dung',
                $this->buildConflictingCorrectAnswerNote($answers, $resolvedIndexes, $styleIndexes, $explicitIndexes),
            ];
        }

        $resolvedIndex = $resolvedIndexes[0];
        foreach ($answers as $index => $answer) {
            $answers[$index]['is_dap_an_dung'] = $index === $resolvedIndex;
        }

        return [$answers, self::STATUS_VALID, null];
    }

    /**
     * @param  array<int, array{ky_hieu:string, noi_dung:string, is_dap_an_dung:bool}>  $answers
     */
    private function matchCorrectAnswerReference(array $answers, string $reference): ?int
    {
        $normalizedReference = $this->normalizeReferenceToken($reference);
        if ($normalizedReference === '') {
            return null;
        }

        $normalizedTextReference = NganHangCauHoi::normalizeString($reference);

        foreach ($answers as $index => $answer) {
            $normalizedKey = $this->normalizeReferenceToken((string) ($answer['ky_hieu'] ?? ''));
            $normalizedAnswerText = NganHangCauHoi::normalizeString((string) ($answer['noi_dung'] ?? ''));

            if ($normalizedKey !== '' && $normalizedKey === $normalizedReference) {
                return $index;
            }

            if ($normalizedAnswerText !== '' && $normalizedAnswerText === $normalizedTextReference) {
                return $index;
            }
        }

        return null;
    }

    private function normalizeReferenceToken(string $reference): string
    {
        $reference = trim($reference);
        $reference = preg_replace('/^[\*\-\s]+|[\*\-\s]+$/u', '', $reference) ?? $reference;

        if (preg_match('/^([A-Za-z])[\.\)]?$/u', $reference, $matches) === 1) {
            return mb_strtolower($matches[1], 'UTF-8');
        }

        return NganHangCauHoi::normalizeString($reference);
    }

    /**
     * @param  array<int, array{ky_hieu:string, noi_dung:string, is_dap_an_dung:bool}>  $answers
     * @param  array<int, int>  $resolvedIndexes
     * @param  array<int, int>  $styleIndexes
     * @param  array<int, int>  $explicitIndexes
     */
    private function buildConflictingCorrectAnswerNote(array $answers, array $resolvedIndexes, array $styleIndexes, array $explicitIndexes): string
    {
        $labels = collect($resolvedIndexes)
            ->map(function (int $index) use ($answers) {
                $answer = $answers[$index] ?? [];
                $label = (string) ($answer['ky_hieu'] ?? chr(65 + $index));
                $content = trim((string) ($answer['noi_dung'] ?? ''));

                return $label . ($content !== '' ? ' - ' . $content : '');
            })
            ->implode(' | ');

        if ($styleIndexes !== [] && $explicitIndexes !== []) {
            return 'Phát hiện mâu thuẫn giữa style đánh dấu và dòng đáp án đúng: ' . $labels . '.';
        }

        return 'Phát hiện nhiều hơn một đáp án đúng trong cùng một câu hỏi: ' . $labels . '.';
    }

    /**
     * @param  array<string, mixed>  $parsedQuestion
     */
    private function resolveFallbackCorrectDisplay(array $parsedQuestion): ?string
    {
        if (filled($parsedQuestion['dap_an_dung_text'] ?? null)) {
            return trim((string) $parsedQuestion['dap_an_dung_text']);
        }

        $references = collect($parsedQuestion['dap_an_dung_refs'] ?? [])
            ->map(fn ($reference) => trim((string) $reference))
            ->filter(fn (string $reference) => $reference !== '')
            ->values();

        return $references->count() === 1 ? $references->first() : null;
    }

    /**
     * @param  array<int, array{ky_hieu:string, noi_dung:string, is_dap_an_dung:bool}>  $answers
     * @return array{string, string|null}
     */
    private function validateStructuredQuestion(string $question, array $answers, string $questionType): array
    {
        if ($question === '') {
            return ['thieu_cau_hoi', 'Thiếu nội dung câu hỏi.'];
        }

        if ($questionType === NganHangCauHoi::LOAI_TU_LUAN) {
            return [self::STATUS_VALID, null];
        }

        if ($answers === []) {
            return ['thieu_dap_an', 'Không tìm thấy đáp án cho câu hỏi này.'];
        }

        if (count($answers) !== 4) {
            return ['khong_du_4_dap_an', 'Flow import hiện tại chỉ hỗ trợ câu hỏi có đúng 4 đáp án. Phát hiện ' . count($answers) . ' đáp án.'];
        }

        $normalizedContents = collect($answers)
            ->map(fn (array $answer) => NganHangCauHoi::normalizeString($answer['noi_dung']))
            ->values();

        if ($normalizedContents->contains('')) {
            return ['thieu_dap_an', 'Một hoặc nhiều đáp án đang để trống.'];
        }

        if ($normalizedContents->unique()->count() !== $normalizedContents->count()) {
            return ['trung_dap_an', 'Các đáp án trong cùng một câu không được trùng nhau.'];
        }

        $correctCount = collect($answers)->where('is_dap_an_dung', true)->count();
        if ($correctCount === 0) {
            return ['khong_xac_dinh_dap_an_dung', 'Chưa xác định được đáp án đúng. Cần kiểm tra thủ công trước khi import.'];
        }

        if ($correctCount > 1) {
            return ['nhieu_hon_mot_dap_an_dung', 'Phát hiện nhiều hơn một đáp án đúng trong cùng một câu hỏi.'];
        }

        return [self::STATUS_VALID, null];
    }
}
