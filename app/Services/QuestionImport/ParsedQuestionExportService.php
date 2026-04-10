<?php

namespace App\Services\QuestionImport;

use App\Support\Imports\ImportTemplateRegistry;
use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class ParsedQuestionExportService
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_REL_OFFICE = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_REL_PACKAGE = 'http://schemas.openxmlformats.org/package/2006/relationships';
    private const EXPORT_COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F'];
    private const CORRECT_FILL_COLOR = 'ECFDF3';
    private const ERROR_FILL_COLOR = 'FDE2E1';

    public function __construct(
        private readonly ImportTemplateRegistry $templateRegistry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array{path:string, download_name:string}
     */
    public function export(array $preview, string $scope = 'all'): array
    {
        $scope = strtolower(trim($scope));
        if (!in_array($scope, ['all', 'valid', 'error'], true)) {
            throw new InvalidArgumentException('Pham vi export khong hop le.');
        }

        $rows = $this->filterRows((array) ($preview['data'] ?? []), $scope);
        $template = $this->templateRegistry->questionBankMcq();
        $templatePath = (string) ($template['absolute_path'] ?? '');

        if (!is_file($templatePath)) {
            throw new InvalidArgumentException('Không tìm thấy tệp mẫu Excel để xuất xem trước.');
        }

        $filePath = tempnam(sys_get_temp_dir(), 'question-preview-export-');
        if ($filePath === false) {
            throw new InvalidArgumentException('Không thể tạo tệp tạm để export dữ liệu.');
        }

        $xlsxPath = $filePath . '.xlsx';
        if (!@rename($filePath, $xlsxPath)) {
            @unlink($filePath);

            throw new InvalidArgumentException('Không thể tạo tệp xlsx tạm để xuất dữ liệu.');
        }

        @unlink($xlsxPath);

        if (!@copy($templatePath, $xlsxPath)) {
            throw new InvalidArgumentException('Không thể sao chép tệp mẫu Excel để xuất dữ liệu.');
        }

        $this->fillTemplateWorkbook($xlsxPath, (string) ($template['sheet'] ?? 'Mau_Import'), (int) ($template['data_starts_on_row'] ?? 7), array_map(
            fn (array $row) => [
                'values' => $this->buildImportRow($row),
                'highlight_error' => ($row['status'] ?? null) !== 'hop_le',
                'correct_answer_cell_index' => $this->resolveCorrectAnswerCellIndex($row),
            ],
            $rows,
        ));

        return [
            'path' => $xlsxPath,
            'download_name' => $this->buildDownloadName($preview, $scope),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function filterRows(array $rows, string $scope): array
    {
        return array_values(array_filter($rows, function (array $row) use ($scope) {
            return match ($scope) {
                'valid' => ($row['status'] ?? null) === 'hop_le',
                'error' => ($row['status'] ?? null) !== 'hop_le',
                default => true,
            };
        }));
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, string>
     */
    private function buildImportRow(array $row): array
    {
        $answerTexts = collect($row['answers'] ?? [])
            ->map(fn (array $answer) => trim((string) ($answer['noi_dung'] ?? '')))
            ->values()
            ->all();

        $answerTexts = array_pad(array_slice($answerTexts, 0, 4), 4, '');

        $correctCount = collect($row['answers'] ?? [])
            ->where('is_dap_an_dung', true)
            ->count();

        return [
            trim((string) ($row['noi_dung_cau_hoi'] ?? '')),
            (string) ($answerTexts[0] ?? ''),
            (string) ($answerTexts[1] ?? ''),
            (string) ($answerTexts[2] ?? ''),
            (string) ($answerTexts[3] ?? ''),
            $correctCount === 1 ? trim((string) ($row['dap_an_dung'] ?? '')) : '',
        ];
    }

    /**
     * Return the worksheet cell index for the correct answer within export columns A-F.
     * B=1, C=2, D=3, E=4. Returns null when the correct answer is not determined safely.
     *
     * @param  array<string, mixed>  $row
     */
    private function resolveCorrectAnswerCellIndex(array $row): ?int
    {
        $correctAnswerIndexes = collect($row['answers'] ?? [])
            ->filter(fn (array $answer) => !empty($answer['is_dap_an_dung']))
            ->keys()
            ->values()
            ->all();

        if (count($correctAnswerIndexes) !== 1) {
            return null;
        }

        $answerIndex = (int) $correctAnswerIndexes[0];
        if ($answerIndex < 0 || $answerIndex > 3) {
            return null;
        }

        return $answerIndex + 1;
    }

    /**
     * @param  array<string, mixed>  $preview
     */
    private function buildDownloadName(array $preview, string $scope): string
    {
        $baseName = pathinfo((string) ($preview['original_name'] ?? 'question-preview'), PATHINFO_FILENAME);
        $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName) ?? 'question-preview';
        $baseName = trim($baseName, '-');
        $baseName = $baseName !== '' ? $baseName : 'question-preview';

        return $baseName . '-preview-' . $scope . '-' . now()->format('YmdHis') . '.xlsx';
    }

    /**
     * @param  array<int, array{values:array<int, string>, highlight_error:bool, correct_answer_cell_index:?int}>  $rows
     */
    private function fillTemplateWorkbook(string $xlsxPath, string $sheetName, int $startRow, array $rows): void
    {
        $zip = new ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            throw new RuntimeException('Không thể mở tệp Excel mẫu để xuất dữ liệu.');
        }

        try {
            $worksheetPath = $this->resolveWorksheetPath($zip, $sheetName);
            $worksheetContent = $zip->getFromName($worksheetPath);
            $stylesContent = $zip->getFromName('xl/styles.xml');

            if ($worksheetContent === false || $stylesContent === false) {
                throw new RuntimeException('Không thể đọc sheet Mau_Import từ tệp mẫu Excel.');
            }

            $worksheetDocument = $this->loadDocument($worksheetContent, 'worksheet');
            $stylesDocument = $this->loadDocument($stylesContent, 'styles');
            $xpath = new DOMXPath($worksheetDocument);
            $xpath->registerNamespace('main', self::NS_MAIN);
            $errorStyleIds = [];
            $correctStyleIds = [];
            $fillIds = [];

            $sheetData = $xpath->query('//main:sheetData')->item(0);
            if (!$sheetData instanceof DOMElement) {
                throw new RuntimeException('File mau Excel khong hop le: thieu sheetData.');
            }

            $templateRow = null;
            $maxExistingRow = $startRow - 1;
            $rowsToReplace = [];

            foreach ($xpath->query('./main:row', $sheetData) as $rowNode) {
                if (!$rowNode instanceof DOMElement) {
                    continue;
                }

                $rowNumber = (int) $rowNode->getAttribute('r');
                $maxExistingRow = max($maxExistingRow, $rowNumber);

                if ($rowNumber === $startRow) {
                    $templateRow = $rowNode->cloneNode(true);
                }

                if ($rowNumber >= $startRow) {
                    $rowsToReplace[] = $rowNode;
                }
            }

            if (!$templateRow instanceof DOMElement) {
                throw new RuntimeException('File mau Excel khong co dong bat dau du lieu de tao export.');
            }

            foreach ($rowsToReplace as $rowNode) {
                $sheetData->removeChild($rowNode);
            }

            $lastRequiredRow = $startRow + count($rows) - 1;
            $lastRow = max($maxExistingRow, $lastRequiredRow);

            for ($rowNumber = $startRow; $rowNumber <= $lastRow; $rowNumber++) {
                $rowPayload = $rows[$rowNumber - $startRow] ?? [
                    'values' => array_fill(0, count(self::EXPORT_COLUMNS), ''),
                    'highlight_error' => false,
                    'correct_answer_cell_index' => null,
                ];
                $newRow = $templateRow->cloneNode(true);

                if (!$newRow instanceof DOMElement) {
                    throw new RuntimeException('Không thể sao chép dòng mẫu trong tệp Excel.');
                }

                $this->fillWorksheetRow(
                    $worksheetDocument,
                    $stylesDocument,
                    $newRow,
                    $rowNumber,
                    (array) ($rowPayload['values'] ?? []),
                    (bool) ($rowPayload['highlight_error'] ?? false),
                    isset($rowPayload['correct_answer_cell_index']) ? (is_numeric($rowPayload['correct_answer_cell_index']) ? (int) $rowPayload['correct_answer_cell_index'] : null) : null,
                    $errorStyleIds,
                    $correctStyleIds,
                    $fillIds,
                );
                $sheetData->appendChild($newRow);
            }

            $this->updateConditionalFormattingRange($xpath, max($lastRow, $maxExistingRow));

            $updatedWorksheetContent = $worksheetDocument->saveXML();
            $updatedStylesContent = $stylesDocument->saveXML();
            if ($updatedWorksheetContent === false) {
                throw new RuntimeException('Không thể lưu nội dung sheet export.');
            }
            if ($updatedStylesContent === false) {
                throw new RuntimeException('Không thể lưu style của tệp export.');
            }

            $zip->deleteName($worksheetPath);
            if (!$zip->addFromString($worksheetPath, $updatedWorksheetContent)) {
                throw new RuntimeException('Không thể cập nhật dữ liệu vào tệp export.');
            }

            $zip->deleteName('xl/styles.xml');
            if (!$zip->addFromString('xl/styles.xml', $updatedStylesContent)) {
                throw new RuntimeException('Không thể cập nhật style vào tệp export.');
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<int, int>  $errorStyleIds
     * @param  array<int, int>  $correctStyleIds
     * @param  array<string, int>  $fillIds
     */
    private function fillWorksheetRow(
        DOMDocument $document,
        DOMDocument $stylesDocument,
        DOMElement $rowNode,
        int $rowNumber,
        array $values,
        bool $highlightError,
        ?int $correctAnswerCellIndex,
        array &$errorStyleIds,
        array &$correctStyleIds,
        array &$fillIds,
    ): void {
        $values = array_pad(array_slice(array_values($values), 0, count(self::EXPORT_COLUMNS)), count(self::EXPORT_COLUMNS), '');
        $rowNode->setAttribute('r', (string) $rowNumber);

        $cellNodes = [];
        foreach ($rowNode->childNodes as $childNode) {
            if ($childNode instanceof DOMElement && $childNode->localName === 'c') {
                $cellNodes[] = $childNode;
            }
        }

        foreach (self::EXPORT_COLUMNS as $index => $columnName) {
            $cellNode = $cellNodes[$index] ?? null;

            if (!$cellNode instanceof DOMElement) {
                $cellNode = $document->createElementNS(self::NS_MAIN, 'x:c');
                $rowNode->appendChild($cellNode);
            }

            $cellNode->setAttribute('r', $columnName . $rowNumber);
            $this->fillWorksheetCell($document, $cellNode, (string) ($values[$index] ?? ''));

            $baseStyleId = (int) ($cellNode->getAttribute('s') !== '' ? $cellNode->getAttribute('s') : '0');

            if ($highlightError) {
                $cellNode->setAttribute('s', (string) $this->resolveHighlightStyleId(
                    $stylesDocument,
                    $baseStyleId,
                    self::ERROR_FILL_COLOR,
                    $errorStyleIds,
                    $fillIds,
                ));
            }

            if ($correctAnswerCellIndex !== null && $index === $correctAnswerCellIndex && trim((string) ($values[$index] ?? '')) !== '') {
                $cellNode->setAttribute('s', (string) $this->resolveHighlightStyleId(
                    $stylesDocument,
                    $baseStyleId,
                    self::CORRECT_FILL_COLOR,
                    $correctStyleIds,
                    $fillIds,
                ));
            }
        }
    }

    private function fillWorksheetCell(DOMDocument $document, DOMElement $cellNode, string $value): void
    {
        while ($cellNode->firstChild !== null) {
            $cellNode->removeChild($cellNode->firstChild);
        }

        if ($value === '') {
            $cellNode->removeAttribute('t');

            return;
        }

        $cellNode->setAttribute('t', 'inlineStr');

        $inlineString = $document->createElementNS(self::NS_MAIN, 'x:is');
        $textNode = $document->createElementNS(self::NS_MAIN, 'x:t');

        if ($this->shouldPreserveXmlWhitespace($value)) {
            $textNode->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }

        $textNode->appendChild($document->createTextNode($value));
        $inlineString->appendChild($textNode);
        $cellNode->appendChild($inlineString);
    }

    private function shouldPreserveXmlWhitespace(string $value): bool
    {
        return preg_match('/^\s|\s$|\R| {2,}|\t/', $value) === 1;
    }

    private function updateConditionalFormattingRange(DOMXPath $xpath, int $lastRow): void
    {
        foreach ($xpath->query('//main:conditionalFormatting') as $conditionalFormattingNode) {
            if (!$conditionalFormattingNode instanceof DOMElement) {
                continue;
            }

            $conditionalFormattingNode->setAttribute('sqref', 'F3:F' . max(3, $lastRow));
        }
    }

    /**
     * @param  array<int, int>  $errorStyleIds
     * @param  array<string, int>  $fillIds
     */
    private function resolveHighlightStyleId(
        DOMDocument $stylesDocument,
        int $baseStyleId,
        string $fillColor,
        array &$styleIds,
        array &$fillIds,
    ): int
    {
        $styleCacheKey = $fillColor . ':' . $baseStyleId;
        if (isset($styleIds[$styleCacheKey])) {
            return $styleIds[$styleCacheKey];
        }

        $xpath = new DOMXPath($stylesDocument);
        $xpath->registerNamespace('main', self::NS_MAIN);

        $fillsNode = $xpath->query('//main:fills')->item(0);
        $cellXfsNode = $xpath->query('//main:cellXfs')->item(0);

        if (!$fillsNode instanceof DOMElement || !$cellXfsNode instanceof DOMElement) {
            throw new RuntimeException('File style Excel mau khong hop le.');
        }

        $fillId = $this->resolveFillId($stylesDocument, $fillsNode, $fillColor, $fillIds);
        $baseXfNode = $xpath->query('./main:xf', $cellXfsNode)->item($baseStyleId);

        if (!$baseXfNode instanceof DOMElement) {
            throw new RuntimeException('Không tìm thấy style cơ sở để tạo dòng lỗi trong tệp export.');
        }

        $newXfNode = $baseXfNode->cloneNode(true);
        if (!$newXfNode instanceof DOMElement) {
            throw new RuntimeException('Không thể tạo style dòng lỗi cho tệp export.');
        }

        $newStyleId = $xpath->query('./main:xf', $cellXfsNode)->length;
        $newXfNode->setAttribute('fillId', (string) $fillId);
        $newXfNode->setAttribute('applyFill', '1');
        $cellXfsNode->appendChild($newXfNode);
        $cellXfsNode->setAttribute('count', (string) ($newStyleId + 1));

        return $styleIds[$styleCacheKey] = $newStyleId;
    }

    /**
     * @param  array<string, int>  $fillIds
     */
    private function resolveFillId(DOMDocument $stylesDocument, DOMElement $fillsNode, string $fillColor, array &$fillIds): int
    {
        $fillColor = strtoupper($fillColor);

        if (isset($fillIds[$fillColor])) {
            return $fillIds[$fillColor];
        }

        $currentIndex = 0;
        foreach ($fillsNode->childNodes as $fillNode) {
            if (!$fillNode instanceof DOMElement || $fillNode->localName !== 'fill') {
                continue;
            }

            foreach ($fillNode->childNodes as $childNode) {
                if (!$childNode instanceof DOMElement || $childNode->localName !== 'patternFill') {
                    continue;
                }

                foreach ($childNode->childNodes as $colorNode) {
                    if (!$colorNode instanceof DOMElement || $colorNode->localName !== 'fgColor') {
                        continue;
                    }

                    if (strtoupper((string) $colorNode->getAttribute('rgb')) === $fillColor) {
                        return $fillIds[$fillColor] = $currentIndex;
                    }
                }
            }

            $currentIndex++;
        }

        $fillCount = (int) $fillsNode->getAttribute('count');
        $fillId = $fillCount;

        $fillNode = $stylesDocument->createElementNS(self::NS_MAIN, 'x:fill');
        $patternFillNode = $stylesDocument->createElementNS(self::NS_MAIN, 'x:patternFill');
        $patternFillNode->setAttribute('patternType', 'solid');

        $foregroundNode = $stylesDocument->createElementNS(self::NS_MAIN, 'x:fgColor');
        $foregroundNode->setAttribute('rgb', $fillColor);

        $backgroundNode = $stylesDocument->createElementNS(self::NS_MAIN, 'x:bgColor');
        $backgroundNode->setAttribute('rgb', $fillColor);

        $patternFillNode->appendChild($foregroundNode);
        $patternFillNode->appendChild($backgroundNode);
        $fillNode->appendChild($patternFillNode);
        $fillsNode->appendChild($fillNode);
        $fillsNode->setAttribute('count', (string) ($fillCount + 1));

        return $fillIds[$fillColor] = $fillId;
    }

    private function resolveWorksheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbookContent = $zip->getFromName('xl/workbook.xml');
        $relationshipsContent = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookContent === false || $relationshipsContent === false) {
            throw new RuntimeException('File Excel mau khong hop le: thieu workbook metadata.');
        }

        $workbookDocument = $this->loadDocument($workbookContent, 'workbook');
        $workbookXPath = new DOMXPath($workbookDocument);
        $workbookXPath->registerNamespace('main', self::NS_MAIN);
        $workbookXPath->registerNamespace('r', self::NS_REL_OFFICE);

        $relationshipsDocument = $this->loadDocument($relationshipsContent, 'workbook relationships');
        $relationshipsXPath = new DOMXPath($relationshipsDocument);
        $relationshipsXPath->registerNamespace('rel', self::NS_REL_PACKAGE);

        $targets = [];
        foreach ($relationshipsXPath->query('//rel:Relationship') as $relationshipNode) {
            if (!$relationshipNode instanceof DOMElement) {
                continue;
            }

            $target = ltrim($relationshipNode->getAttribute('Target'), '/');
            if (!str_starts_with($target, 'xl/')) {
                $target = 'xl/' . $target;
            }

            $targets[$relationshipNode->getAttribute('Id')] = $target;
        }

        foreach ($workbookXPath->query('//main:sheets/main:sheet') as $sheetNode) {
            if (!$sheetNode instanceof DOMElement || $sheetNode->getAttribute('name') !== $sheetName) {
                continue;
            }

            $relationId = $sheetNode->getAttributeNS(self::NS_REL_OFFICE, 'id') ?: $sheetNode->getAttribute('r:id');
            $worksheetPath = $targets[$relationId] ?? null;

            if ($worksheetPath !== null) {
                return $worksheetPath;
            }
        }

        throw new RuntimeException("Không tìm thấy sheet {$sheetName} trong tệp Excel mẫu.");
    }

    private function loadDocument(string $xmlContent, string $label): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;

        if (!@$document->loadXML($xmlContent)) {
            throw new RuntimeException("Không thể phân tích {$label} trong tệp Excel mẫu.");
        }

        return $document;
    }
}
