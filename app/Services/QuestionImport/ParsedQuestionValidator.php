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
                    $previewRow['note'] = 'Trung voi cau hoi tai dong ' . $contentSetInFile[$normalizedContent] . ' trong file.';
                } else {
                    $contentSetInFile[$normalizedContent] = $previewRow['line'];

                    if (NganHangCauHoi::isDuplicate($khoaHocId, $previewRow['noi_dung_cau_hoi'])) {
                        $previewRow['status'] = self::STATUS_DUPLICATE_DB;
                        $previewRow['validation_status'] = 'trung_trong_he_thong';
                        $previewRow['note'] = 'Cau hoi da ton tai trong ngan hang cua khoa hoc nay.';
                    }
                }
            }

            if (($previewRow['validation_status'] ?? null) === 'khong_xac_dinh_dap_an_dung') {
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

        $validationStatus = (string) ($parsedQuestion['trang_thai_parse'] ?? 'hop_le');
        $note = $parsedQuestion['ghi_chu_loi'] ?? null;

        if ($validationStatus === 'hop_le') {
            [$validationStatus, $note] = $this->validateStructuredQuestion(
                $question,
                $answers,
                (string) ($parsedQuestion['loai'] ?? NganHangCauHoi::LOAI_TRAC_NGHIEM)
            );
        }

        $correctAnswers = collect($answers)->where('is_dap_an_dung', true)->pluck('noi_dung')->values();

        return [
            'line' => (int) ($parsedQuestion['line'] ?? $parsedQuestion['so_thu_tu'] ?? 0),
            'noi_dung_cau_hoi' => $question,
            'answers' => $answers,
            'answers_display' => array_map(fn (array $answer) => $answer['noi_dung'], $answers),
            'dap_an_dung' => $correctAnswers->count() === 1 ? $correctAnswers->first() : ($parsedQuestion['dap_an_dung_text'] ?? null),
            'status' => $validationStatus === 'hop_le' ? self::STATUS_VALID : self::STATUS_INVALID,
            'validation_status' => $validationStatus,
            'note' => $note,
            'import_answers' => $validationStatus === 'hop_le' ? $answers : [],
            'nguon_file' => $parsedQuestion['nguon_file'] ?? null,
        ];
    }

    /**
     * @param  array<int, array{ky_hieu:string, noi_dung:string, is_dap_an_dung:bool}>  $answers
     * @return array{string, string|null}
     */
    private function validateStructuredQuestion(string $question, array $answers, string $questionType): array
    {
        if ($questionType !== NganHangCauHoi::LOAI_TRAC_NGHIEM) {
            return ['khong_ho_tro_loai_cau_hoi', 'He thong hien tai chi ho tro import cau hoi trac nghiem tu tai lieu.'];
        }

        if ($question === '') {
            return ['thieu_noi_dung', 'Thieu noi dung cau hoi.'];
        }

        if ($answers === []) {
            return ['thieu_dap_an', 'Khong tim thay dap an cho cau hoi nay.'];
        }

        if (count($answers) < 2) {
            return ['it_hon_so_dap_an_toi_thieu', 'Cau hoi trac nghiem can it nhat 2 dap an.'];
        }

        $normalizedContents = collect($answers)
            ->map(fn (array $answer) => NganHangCauHoi::normalizeString($answer['noi_dung']))
            ->values();

        if ($normalizedContents->contains('')) {
            return ['thieu_dap_an', 'Mot hoac nhieu dap an dang de trong.'];
        }

        if ($normalizedContents->unique()->count() !== $normalizedContents->count()) {
            return ['sai_dinh_dang', 'Cac dap an trong cung mot cau khong duoc trung nhau.'];
        }

        $correctCount = collect($answers)->where('is_dap_an_dung', true)->count();
        if ($correctCount === 0) {
            return ['khong_xac_dinh_dap_an_dung', 'Chua xac dinh duoc dap an dung. Can kiem tra thu cong truoc khi import.'];
        }

        if ($correctCount > 1) {
            return ['nhieu_hon_mot_dap_an_dung', 'Phat hien nhieu hon mot dap an dung trong cung mot cau hoi.'];
        }

        return ['hop_le', null];
    }
}
