<?php

namespace App\Services\QuestionImport\Parsers;

use App\Services\QuestionImport\Support\QuestionTextPatternParser;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class DocxQuestionParser implements QuestionFileParser
{
    private const NS_WORD = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function __construct(
        private readonly QuestionTextPatternParser $textPatternParser,
    ) {
    }

    public function supports(string $extension): bool
    {
        return strtolower($extension) === 'docx';
    }

    public function parse(UploadedFile $file): array
    {
        $zip = new ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            throw ValidationException::withMessages([
                'file_import' => 'Khong the mo file Word de phan tich.',
            ]);
        }

        try {
            $documentXml = $zip->getFromName('word/document.xml');
            if ($documentXml === false) {
                throw ValidationException::withMessages([
                    'file_import' => 'File .docx khong hop le hoac thieu noi dung van ban.',
                ]);
            }

            $document = new DOMDocument();
            if (!@$document->loadXML($documentXml)) {
                throw ValidationException::withMessages([
                    'file_import' => 'Khong the doc noi dung file Word.',
                ]);
            }

            $xpath = new DOMXPath($document);
            $xpath->registerNamespace('w', self::NS_WORD);

            $numberingDefinitions = $this->extractNumberingDefinitions($zip);
            $blocks = [];
            $line = 0;
            $listState = [];

            // Read paragraph/run granularity so we can preserve style hints like bold/highlight.
            foreach ($xpath->query('//w:body/w:p') as $paragraphNode) {
                if (!$paragraphNode instanceof DOMElement) {
                    continue;
                }

                $line++;
                $textParts = [];
                $hasBold = false;
                $hasHighlight = false;

                foreach ($xpath->query('.//w:r', $paragraphNode) as $runNode) {
                    if (!$runNode instanceof DOMElement) {
                        continue;
                    }

                    $texts = [];
                    foreach ($xpath->query('.//w:t', $runNode) as $textNode) {
                        $texts[] = $textNode->textContent;
                    }

                    $runText = implode('', $texts);
                    if ($runText === '') {
                        continue;
                    }

                    $textParts[] = $runText;
                    $hasBold = $hasBold || $xpath->query('./w:rPr/w:b', $runNode)->length > 0;
                    $hasHighlight = $hasHighlight || $xpath->query('./w:rPr/w:highlight', $runNode)->length > 0;
                }

                $paragraphText = trim(implode('', $textParts));
                if ($paragraphText === '') {
                    continue;
                }

                if ($this->isDecorativeCounterLine($paragraphText)) {
                    continue;
                }

                $listPrefix = $this->resolveListPrefix($xpath, $paragraphNode, $numberingDefinitions, $listState);
                if ($listPrefix !== '' && !$this->hasVisibleQuestionOrAnswerPrefix($paragraphText)) {
                    $paragraphText = trim($listPrefix . ' ' . $paragraphText);
                }

                $blocks[] = [
                    'text' => $paragraphText,
                    'line' => $line,
                    'has_bold' => $hasBold,
                    'has_highlight' => $hasHighlight,
                ];
            }

            $questions = $this->textPatternParser->parseBlocks($blocks, 'docx');
            if ($questions === []) {
                throw ValidationException::withMessages([
                    'file_import' => 'Khong nhan dien duoc cau hoi trac nghiem trong file Word.',
                ]);
            }

            return [
                'profile' => 'question_document_docx',
                'source_format' => 'docx',
                'original_name' => $file->getClientOriginalName(),
                'questions' => $questions,
            ];
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<string, array<int, array{format:string, pattern:string}>>
     */
    private function extractNumberingDefinitions(ZipArchive $zip): array
    {
        $numberingXml = $zip->getFromName('word/numbering.xml');
        if ($numberingXml === false) {
            return [];
        }

        $document = new DOMDocument();
        if (!@$document->loadXML($numberingXml)) {
            return [];
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', self::NS_WORD);

        $abstractMap = [];
        foreach ($xpath->query('//w:num') as $numNode) {
            if (!$numNode instanceof DOMElement) {
                continue;
            }

            $numId = $xpath->evaluate('string(@w:numId)', $numNode);
            $abstractNumId = $xpath->evaluate('string(./w:abstractNumId/@w:val)', $numNode);

            if ($numId !== '' && $abstractNumId !== '') {
                $abstractMap[$numId] = $abstractNumId;
            }
        }

        $definitions = [];
        foreach ($xpath->query('//w:abstractNum') as $abstractNode) {
            if (!$abstractNode instanceof DOMElement) {
                continue;
            }

            $abstractNumId = $xpath->evaluate('string(@w:abstractNumId)', $abstractNode);
            if ($abstractNumId === '') {
                continue;
            }

            $levelDefinitions = [];
            foreach ($xpath->query('./w:lvl', $abstractNode) as $levelNode) {
                if (!$levelNode instanceof DOMElement) {
                    continue;
                }

                $level = (int) $xpath->evaluate('string(@w:ilvl)', $levelNode);
                $levelDefinitions[$level] = [
                    'format' => $xpath->evaluate('string(./w:numFmt/@w:val)', $levelNode) ?: 'decimal',
                    'pattern' => $xpath->evaluate('string(./w:lvlText/@w:val)', $levelNode) ?: '%' . ($level + 1) . '.',
                ];
            }

            foreach ($abstractMap as $numId => $mappedAbstractId) {
                if ($mappedAbstractId === $abstractNumId) {
                    $definitions[$numId] = $levelDefinitions;
                }
            }
        }

        return $definitions;
    }

    /**
     * @param  array<string, array<int, array{format:string, pattern:string}>>  $numberingDefinitions
     * @param  array<string, array<int, int>>  $listState
     */
    private function resolveListPrefix(DOMXPath $xpath, DOMElement $paragraphNode, array $numberingDefinitions, array &$listState): string
    {
        $numId = trim((string) $xpath->evaluate('string(./w:pPr/w:numPr/w:numId/@w:val)', $paragraphNode));
        if ($numId === '') {
            return '';
        }

        $level = (int) ($xpath->evaluate('string(./w:pPr/w:numPr/w:ilvl/@w:val)', $paragraphNode) ?: 0);
        $listState[$numId] ??= [];
        $listState[$numId][$level] = ($listState[$numId][$level] ?? 0) + 1;

        foreach (array_keys($listState[$numId]) as $trackedLevel) {
            if ($trackedLevel > $level) {
                unset($listState[$numId][$trackedLevel]);
            }
        }

        $pattern = $numberingDefinitions[$numId][$level]['pattern'] ?? '%' . ($level + 1) . '.';

        return preg_replace_callback('/%([1-9])/u', function (array $matches) use ($listState, $numberingDefinitions, $numId) {
            $targetLevel = ((int) $matches[1]) - 1;
            $value = $listState[$numId][$targetLevel] ?? 1;
            $format = $numberingDefinitions[$numId][$targetLevel]['format'] ?? 'decimal';

            return $this->formatListValue($value, $format);
        }, $pattern) ?? '';
    }

    private function formatListValue(int $value, string $format): string
    {
        return match ($format) {
            'lowerLetter' => strtolower($this->toAlphabeticSequence($value)),
            'upperLetter' => $this->toAlphabeticSequence($value),
            'lowerRoman' => strtolower($this->toRoman($value)),
            'upperRoman' => $this->toRoman($value),
            default => (string) $value,
        };
    }

    private function toAlphabeticSequence(int $value): string
    {
        $result = '';
        $current = max(1, $value);

        while ($current > 0) {
            $current--;
            $result = chr(65 + ($current % 26)) . $result;
            $current = intdiv($current, 26);
        }

        return $result;
    }

    private function toRoman(int $value): string
    {
        $romanMap = [
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I',
        ];

        $current = max(1, $value);
        $result = '';

        foreach ($romanMap as $number => $roman) {
            while ($current >= $number) {
                $result .= $roman;
                $current -= $number;
            }
        }

        return $result;
    }

    private function hasVisibleQuestionOrAnswerPrefix(string $text): bool
    {
        return preg_match('/^\s*(\d+[\.\)]|[A-Za-z][\.\)])\s*/u', $text) === 1;
    }

    private function isDecorativeCounterLine(string $text): bool
    {
        return preg_match('/^\s*\d+\s*\/\s*\d+\s*$/u', $text) === 1;
    }
}
