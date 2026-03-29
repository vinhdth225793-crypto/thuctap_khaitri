<?php

namespace App\Support\Imports;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class SimpleXlsxReader
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_REL_OFFICE = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_REL_PACKAGE = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /**
     * @return array<int, array{row:int, values:array<int, string>}>
     */
    public function readSheetRows(string $filePath, string $sheetName): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException('Khong tim thay file import Excel.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('Khong the mo file Excel de doc du lieu.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $worksheetPath = $this->resolveWorksheetPath($zip, $sheetName);

            return $this->readWorksheetRows($zip, $worksheetPath, $sharedStrings);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $content = $zip->getFromName('xl/sharedStrings.xml');
        if ($content === false) {
            return [];
        }

        $document = $this->loadDocument($content, 'shared strings');
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('main', self::NS_MAIN);

        $strings = [];
        foreach ($xpath->query('//main:sst/main:si') as $item) {
            if (!$item instanceof DOMElement) {
                continue;
            }

            $strings[] = $this->flattenTextNodes($xpath, $item);
        }

        return $strings;
    }

    private function resolveWorksheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbookContent = $zip->getFromName('xl/workbook.xml');
        $relsContent = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookContent === false || $relsContent === false) {
            throw new RuntimeException('File Excel khong hop le: thieu workbook metadata.');
        }

        $workbookDocument = $this->loadDocument($workbookContent, 'workbook');
        $workbookXPath = new DOMXPath($workbookDocument);
        $workbookXPath->registerNamespace('main', self::NS_MAIN);
        $workbookXPath->registerNamespace('r', self::NS_REL_OFFICE);

        $relationshipsDocument = $this->loadDocument($relsContent, 'workbook relationships');
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
            if (!$sheetNode instanceof DOMElement) {
                continue;
            }

            if ($sheetNode->getAttribute('name') !== $sheetName) {
                continue;
            }

            $relationId = $sheetNode->getAttributeNS(self::NS_REL_OFFICE, 'id') ?: $sheetNode->getAttribute('r:id');
            $worksheetPath = $targets[$relationId] ?? null;

            if (!$worksheetPath) {
                break;
            }

            return $worksheetPath;
        }

        throw new RuntimeException("Khong tim thay sheet {$sheetName} trong file Excel.");
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array{row:int, values:array<int, string>}>
     */
    private function readWorksheetRows(ZipArchive $zip, string $worksheetPath, array $sharedStrings): array
    {
        $content = $zip->getFromName($worksheetPath);
        if ($content === false) {
            throw new RuntimeException('Khong the doc du lieu sheet trong file Excel.');
        }

        $document = $this->loadDocument($content, 'worksheet');
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('main', self::NS_MAIN);

        $rows = [];
        foreach ($xpath->query('//main:sheetData/main:row') as $rowNode) {
            if (!$rowNode instanceof DOMElement) {
                continue;
            }

            $rowNumber = (int) $rowNode->getAttribute('r');
            $cells = [];

            foreach ($xpath->query('./main:c', $rowNode) as $cellNode) {
                if (!$cellNode instanceof DOMElement) {
                    continue;
                }

                $reference = $cellNode->getAttribute('r');
                $column = preg_replace('/\d+/', '', $reference) ?: 'A';
                $columnIndex = $this->columnToIndex($column);

                $cells[$columnIndex] = $this->resolveCellValue($xpath, $cellNode, $sharedStrings);
            }

            if ($cells === []) {
                $rows[] = ['row' => $rowNumber, 'values' => []];
                continue;
            }

            ksort($cells);
            $maxIndex = max(array_keys($cells));
            $values = [];

            for ($index = 0; $index <= $maxIndex; $index++) {
                $values[] = isset($cells[$index]) ? trim($cells[$index]) : '';
            }

            $rows[] = ['row' => $rowNumber, 'values' => $values];
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function resolveCellValue(DOMXPath $xpath, DOMElement $cellNode, array $sharedStrings): string
    {
        $type = $cellNode->getAttribute('t');

        if ($type === 'inlineStr') {
            return $this->flattenTextNodes($xpath, $cellNode);
        }

        $valueNode = $xpath->query('./main:v', $cellNode)->item(0);
        $rawValue = $valueNode?->textContent ?? '';

        if ($type === 's') {
            return trim($sharedStrings[(int) $rawValue] ?? '');
        }

        if ($type === 'b') {
            return $rawValue === '1' ? 'TRUE' : 'FALSE';
        }

        return trim($rawValue);
    }

    private function flattenTextNodes(DOMXPath $xpath, DOMElement $contextNode): string
    {
        $textParts = [];

        foreach ($xpath->query('.//main:t', $contextNode) as $textNode) {
            $textParts[] = $textNode->textContent;
        }

        return trim(implode('', $textParts));
    }

    private function loadDocument(string $xmlContent, string $label): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;

        if (!@$document->loadXML($xmlContent)) {
            throw new RuntimeException("Khong the phan tich {$label} trong file Excel.");
        }

        return $document;
    }

    private function columnToIndex(string $column): int
    {
        $column = strtoupper($column);
        $length = strlen($column);
        $index = 0;

        for ($pointer = 0; $pointer < $length; $pointer++) {
            $index = ($index * 26) + (ord($column[$pointer]) - 64);
        }

        return $index - 1;
    }
}
