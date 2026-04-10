<?php

namespace App\Services\QuestionImport\Support;

use App\Models\NganHangCauHoi;

class QuestionTextPatternParser
{
    /**
     * @param  array<int, array{text:string, line:int, has_bold?:bool, has_highlight?:bool}>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function parseBlocks(array $blocks, string $sourceFormat): array
    {
        $questions = [];
        $currentQuestion = null;
        $currentAnswerIndex = null;

        foreach ($blocks as $block) {
            $text = $this->normalizeText((string) ($block['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            if ($this->isDecorativeCounterLine($text)) {
                continue;
            }

            if (preg_match('/^\s*(\d+)[\.\)]\s*(.+)$/u', $text, $matches) === 1) {
                $this->pushCurrentQuestion($questions, $currentQuestion);
                [$questionType, $questionContent] = $this->extractQuestionType(trim($matches[2]));

                $currentQuestion = [
                    'line' => (int) ($block['line'] ?? (count($questions) + 1)),
                    'so_thu_tu' => (int) $matches[1],
                    'noi_dung' => $questionContent,
                    'loai' => $questionType,
                    'dap_an' => [],
                    'dap_an_dung_refs' => [],
                    'goi_y_tra_loi' => null,
                    'trang_thai_parse' => 'hop_le',
                    'ghi_chu_loi' => null,
                    'nguon_file' => $sourceFormat,
                ];
                $currentAnswerIndex = null;

                continue;
            }

            $essaySuggestion = null;
            if ($currentQuestion !== null
                && ($currentQuestion['loai'] ?? null) === NganHangCauHoi::LOAI_TU_LUAN
                && $this->matchEssaySuggestionLine($text, $essaySuggestion)
            ) {
                $currentQuestion['goi_y_tra_loi'] = $this->appendText(
                    (string) ($currentQuestion['goi_y_tra_loi'] ?? ''),
                    $essaySuggestion
                );
                $currentAnswerIndex = null;

                continue;
            }

            $reference = null;
            if ($currentQuestion !== null
                && ($currentQuestion['loai'] ?? null) !== NganHangCauHoi::LOAI_TU_LUAN
                && $this->matchExplicitCorrectAnswerReference($text, $reference)
            ) {
                $currentQuestion['dap_an_dung_refs'][] = $reference;
                $currentQuestion['dap_an_dung_text'] = $reference;
                $currentAnswerIndex = null;

                continue;
            }

            if (preg_match('/^\s*([A-Za-z])[\.\)]\s*(.+)$/u', $text, $matches) === 1) {
                if ($currentQuestion === null) {
                    continue;
                }

                if (($currentQuestion['loai'] ?? null) === NganHangCauHoi::LOAI_TU_LUAN) {
                    $currentQuestion['noi_dung'] = $this->appendText((string) $currentQuestion['noi_dung'], $text);
                    $currentAnswerIndex = null;

                    continue;
                }

                [$answerText, $isMarkedCorrect] = $this->stripCorrectMarker(trim($matches[2]));
                $currentQuestion['dap_an'][] = [
                    'thu_tu_hien_thi' => strtoupper($matches[1]),
                    'noi_dung' => $answerText,
                    'is_correct' => $isMarkedCorrect
                        || (bool) ($block['has_bold'] ?? false)
                        || (bool) ($block['has_highlight'] ?? false),
                ];
                $currentAnswerIndex = array_key_last($currentQuestion['dap_an']);

                continue;
            }

            if ($currentQuestion === null) {
                continue;
            }

            if ($currentAnswerIndex !== null) {
                $currentQuestion['dap_an'][$currentAnswerIndex]['noi_dung'] = $this->appendText(
                    $currentQuestion['dap_an'][$currentAnswerIndex]['noi_dung'],
                    $text
                );
                $currentQuestion['dap_an'][$currentAnswerIndex]['is_correct'] = $currentQuestion['dap_an'][$currentAnswerIndex]['is_correct']
                    || (bool) ($block['has_bold'] ?? false)
                    || (bool) ($block['has_highlight'] ?? false);

                continue;
            }

            $currentQuestion['noi_dung'] = $this->appendText((string) $currentQuestion['noi_dung'], $text);
        }

        $this->pushCurrentQuestion($questions, $currentQuestion);

        return $questions;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace("/\n{2,}/u", "\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * @return array{string, bool}
     */
    private function stripCorrectMarker(string $text): array
    {
        if ($text === '') {
            return ['', false];
        }

        if (preg_match('/^\*\s*(.+)$/u', $text, $matches) === 1) {
            return [trim($matches[1]), true];
        }

        if (preg_match('/^(.+?)\s*\*$/u', $text, $matches) === 1) {
            return [trim($matches[1]), true];
        }

        return [$text, false];
    }

    private function matchExplicitCorrectAnswerReference(string $text, ?string &$reference): bool
    {
        $reference = null;

        if (preg_match('/^\s*(?:dap\s*an(?:\s*dung)?|answer(?:\s*key)?|correct\s*answer|đáp\s*án(?:\s*đúng)?)\s*[:\-]\s*(.+)$/iu', $text, $matches) !== 1) {
            return false;
        }

        $reference = $this->normalizeExplicitCorrectReference((string) $matches[1]);

        return $reference !== '';
    }

    private function normalizeExplicitCorrectReference(string $reference): string
    {
        $reference = trim($reference);
        $reference = preg_replace('/^[\*\-\s]+|[\*\-\s]+$/u', '', $reference) ?? $reference;

        return trim($reference);
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     * @param  array<string, mixed>|null  $currentQuestion
     */
    private function pushCurrentQuestion(array &$questions, ?array &$currentQuestion): void
    {
        if ($currentQuestion === null) {
            return;
        }

        $questions[] = $currentQuestion;
        $currentQuestion = null;
    }

    private function appendText(string $base, string $append): string
    {
        if ($base === '') {
            return $append;
        }

        return trim($base . "\n" . $append);
    }

    private function isDecorativeCounterLine(string $text): bool
    {
        return preg_match('/^\s*\d+\s*\/\s*\d+\s*$/u', $text) === 1;
    }

    /**
     * @return array{string, string}
     */
    private function extractQuestionType(string $text): array
    {
        if ($text === '') {
            return [NganHangCauHoi::LOAI_TRAC_NGHIEM, ''];
        }

        if (preg_match('/^\[(?:tự\s*luận|tu\s*luan)\]\s*(.*)$/iu', $text, $matches) === 1) {
            return [
                NganHangCauHoi::LOAI_TU_LUAN,
                trim((string) ($matches[1] ?? '')),
            ];
        }

        if (preg_match('/^(?:tự\s*luận|tu\s*luan)\s*[:\-]\s*(.*)$/iu', $text, $matches) === 1) {
            return [
                NganHangCauHoi::LOAI_TU_LUAN,
                trim((string) ($matches[1] ?? '')),
            ];
        }

        return [NganHangCauHoi::LOAI_TRAC_NGHIEM, $text];
    }

    private function matchEssaySuggestionLine(string $text, ?string &$suggestion): bool
    {
        $suggestion = null;

        if (preg_match('/^\s*(?:gợi\s*ý|goi\s*y|rubric|hướng\s*dẫn\s*chấm|huong\s*dan\s*cham)\s*[:\-]\s*(.+)$/iu', $text, $matches) !== 1) {
            return false;
        }

        $suggestion = trim((string) ($matches[1] ?? ''));

        return $suggestion !== '';
    }
}
