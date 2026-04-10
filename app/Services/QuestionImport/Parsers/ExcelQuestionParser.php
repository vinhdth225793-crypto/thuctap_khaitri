<?php

namespace App\Services\QuestionImport\Parsers;

use App\Models\NganHangCauHoi;
use App\Support\Imports\ImportTemplateRegistry;
use App\Support\Imports\SimpleXlsxReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ExcelQuestionParser implements QuestionFileParser
{
    public const PROFILE_NEW_TEMPLATE = ImportTemplateRegistry::QUESTION_BANK_MCQ;
    public const PROFILE_LEGACY_CSV = ImportTemplateRegistry::QUESTION_BANK_MCQ_LEGACY_CSV;

    public function __construct(
        private readonly ImportTemplateRegistry $templateRegistry,
        private readonly SimpleXlsxReader $xlsxReader,
    ) {
    }

    public function supports(string $extension): bool
    {
        return in_array(strtolower($extension), ['xlsx', 'csv', 'txt'], true);
    }

    public function parse(UploadedFile $file): array
    {
        $rows = $this->extractRows($file);
        [$profile, $headerRowIndex, $headerMap] = $this->detectHeaderProfile($rows);
        $headerRowNumber = (int) ($rows[$headerRowIndex]['row'] ?? ($headerRowIndex + 1));
        $dataStartRow = $this->resolveDataStartRow($profile, $headerRowNumber);
        $questions = [];

        foreach (array_slice($rows, $headerRowIndex + 1) as $rowData) {
            $rowNumber = (int) $rowData['row'];
            if ($rowNumber < $dataStartRow) {
                continue;
            }

            $mappedRow = $this->mapRowValues($rowData['values'], $headerMap);
            if ($this->isCompletelyBlank($mappedRow)) {
                continue;
            }

            $questions[] = $this->buildQuestion(
                $profile,
                $mappedRow,
                $rowNumber,
                strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'unknown')
            );
        }

        return [
            'profile' => $profile,
            'source_format' => strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'unknown'),
            'original_name' => $file->getClientOriginalName(),
            'questions' => $questions,
        ];
    }

    /**
     * @return array<int, array{row:int, values:array<int, string>}>
     */
    private function extractRows(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if ($extension === 'xlsx') {
            $template = $this->templateRegistry->questionBankMcq();

            return $this->xlsxReader->readSheetRows($file->getRealPath(), (string) $template['sheet']);
        }

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsvRows($file->getRealPath());
        }

        throw ValidationException::withMessages([
            'file_import' => 'Định dạng tệp không được hỗ trợ. Vui lòng dùng tệp .xlsx, .csv, .txt, .docx hoặc .pdf.',
        ]);
    }

    /**
     * @param  array<int, array{row:int, values:array<int, string>}>  $rows
     * @return array{string, int, array<string, int>}
     */
    private function detectHeaderProfile(array $rows): array
    {
        $newHeaders = (array) ($this->templateRegistry->questionBankMcq()['headers'] ?? []);
        $legacyHeaders = (array) ($this->templateRegistry->questionBankMcqLegacyCsv()['headers'] ?? []);
        $normalizedNewHeaders = $this->normalizeHeaders($newHeaders);
        $normalizedLegacyHeaders = $this->normalizeHeaders($legacyHeaders);

        foreach ($rows as $index => $row) {
            $headerMap = $this->extractHeaderMap($row['values']);
            $normalizedHeaders = array_keys($headerMap);

            if ($normalizedHeaders === $normalizedNewHeaders) {
                return [self::PROFILE_NEW_TEMPLATE, $index, $headerMap];
            }

            if ($normalizedHeaders === $normalizedLegacyHeaders) {
                return [self::PROFILE_LEGACY_CSV, $index, $headerMap];
            }
        }

        throw ValidationException::withMessages([
            'file_import' => 'Không tìm thấy dòng header hợp lệ. Tệp mới phải có 6 cột: cau_hoi, dap_an_1, dap_an_2, dap_an_3, dap_an_4, dap_an_dung. Tệp cũ có thể dùng mẫu CSV legacy.',
        ]);
    }

    /**
     * @param  array<int, string>  $rowValues
     * @return array<string, int>
     */
    private function extractHeaderMap(array $rowValues): array
    {
        $headerMap = [];

        foreach ($rowValues as $index => $value) {
            $normalized = NganHangCauHoi::normalizeString($value);
            if ($normalized === '') {
                continue;
            }

            $headerMap[$normalized] = $index;
        }

        return $headerMap;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($header) => NganHangCauHoi::normalizeString($header), $headers);
    }

    /**
     * @param  array<int, string>  $rowValues
     * @param  array<string, int>  $headerMap
     * @return array<string, string>
     */
    private function mapRowValues(array $rowValues, array $headerMap): array
    {
        $mapped = [];

        foreach ($headerMap as $header => $index) {
            $mapped[$header] = trim((string) ($rowValues[$index] ?? ''));
        }

        return $mapped;
    }

    /**
     * @param  array<string, string>  $mappedRow
     */
    private function isCompletelyBlank(array $mappedRow): bool
    {
        foreach ($mappedRow as $value) {
            if (trim($value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string>  $mappedRow
     * @return array<string, mixed>
     */
    private function buildQuestion(string $profile, array $mappedRow, int $rowNumber, string $sourceFormat): array
    {
        return $profile === self::PROFILE_NEW_TEMPLATE
            ? $this->buildNewTemplateQuestion($mappedRow, $rowNumber, $sourceFormat)
            : $this->buildLegacyQuestion($mappedRow, $rowNumber, $sourceFormat);
    }

    /**
     * @param  array<string, string>  $mappedRow
     * @return array<string, mixed>
     */
    private function buildNewTemplateQuestion(array $mappedRow, int $rowNumber, string $sourceFormat): array
    {
        $question = trim($mappedRow['cau_hoi'] ?? '');
        $answerTexts = [
            trim($mappedRow['dap_an_1'] ?? ''),
            trim($mappedRow['dap_an_2'] ?? ''),
            trim($mappedRow['dap_an_3'] ?? ''),
            trim($mappedRow['dap_an_4'] ?? ''),
        ];
        $correctAnswerText = trim($mappedRow['dap_an_dung'] ?? '');

        $status = 'hop_le';
        $note = null;
        $answers = [];

        if ($question === '') {
            $status = 'thieu_noi_dung';
            $note = 'Thiếu nội dung câu hỏi.';
        } elseif (collect($answerTexts)->contains(fn ($value) => $value === '')) {
            $status = 'thieu_dap_an';
            $note = 'Thiếu một trong 4 đáp án.';
        } elseif ($correctAnswerText === '') {
            $status = 'khong_xac_dinh_dap_an_dung';
            $note = 'Không xác định được đáp án đúng từ cột dap_an_dung.';
        } else {
            $normalizedCorrectText = NganHangCauHoi::normalizeString($correctAnswerText);

            foreach ($answerTexts as $index => $answerText) {
                $answers[] = [
                    'thu_tu_hien_thi' => $this->resolveAnswerKey($index),
                    'noi_dung' => $answerText,
                    'is_correct' => NganHangCauHoi::normalizeString($answerText) === $normalizedCorrectText,
                ];
            }

            if (collect($answers)->where('is_correct', true)->count() !== 1) {
                $status = 'dap_an_dung_khong_khop';
                $note = 'dap_an_dung không khớp với bất kỳ đáp án nào.';
            }
        }

        return [
            'line' => $rowNumber,
            'so_thu_tu' => $rowNumber,
            'noi_dung' => $question,
            'loai' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'dap_an' => $answers,
            'dap_an_dung_text' => $correctAnswerText !== '' ? $correctAnswerText : null,
            'trang_thai_parse' => $status,
            'ghi_chu_loi' => $note,
            'nguon_file' => $sourceFormat,
        ];
    }

    /**
     * @param  array<string, string>  $mappedRow
     * @return array<string, mixed>
     */
    private function buildLegacyQuestion(array $mappedRow, int $rowNumber, string $sourceFormat): array
    {
        $question = trim($mappedRow['cau_hoi'] ?? '');
        $correctAnswerText = trim($mappedRow['dap_an_dung'] ?? '');
        $answerTexts = [
            $correctAnswerText,
            trim($mappedRow['dap_an_sai_1'] ?? ''),
            trim($mappedRow['dap_an_sai_2'] ?? ''),
            trim($mappedRow['dap_an_sai_3'] ?? ''),
        ];

        $status = 'hop_le';
        $note = null;
        $answers = [];

        if ($question === '') {
            $status = 'thieu_noi_dung';
            $note = 'Thiếu nội dung câu hỏi.';
        } elseif (collect($answerTexts)->contains(fn ($value) => $value === '')) {
            $status = 'thieu_dap_an';
            $note = 'Thiếu một trong 4 đáp án của mẫu CSV cũ.';
        } else {
            foreach ($answerTexts as $index => $answerText) {
                $answers[] = [
                    'thu_tu_hien_thi' => $this->resolveAnswerKey($index),
                    'noi_dung' => $answerText,
                    'is_correct' => $index === 0,
                ];
            }
        }

        return [
            'line' => $rowNumber,
            'so_thu_tu' => $rowNumber,
            'noi_dung' => $question,
            'loai' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'dap_an' => $answers,
            'dap_an_dung_text' => $correctAnswerText !== '' ? $correctAnswerText : null,
            'trang_thai_parse' => $status,
            'ghi_chu_loi' => $note,
            'nguon_file' => $sourceFormat,
        ];
    }

    /**
     * @return array<int, array{row:int, values:array<int, string>}>
     */
    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'file_import' => 'Không thể mở tệp CSV để import.',
            ]);
        }

        try {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $rows = [];
            $rowNumber = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $rows[] = [
                    'row' => $rowNumber,
                    'values' => array_map(fn ($value) => trim((string) $value), $row),
                ];
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function resolveDataStartRow(string $profile, int $headerRowNumber): int
    {
        if ($profile === self::PROFILE_NEW_TEMPLATE) {
            $template = $this->templateRegistry->questionBankMcq();
            $configuredRow = (int) ($template['data_starts_on_row'] ?? 0);

            return max($headerRowNumber + 1, $configuredRow > 0 ? $configuredRow : ($headerRowNumber + 1));
        }

        return $headerRowNumber + 1;
    }

    private function resolveAnswerKey(int $index): string
    {
        return chr(65 + $index);
    }
}
