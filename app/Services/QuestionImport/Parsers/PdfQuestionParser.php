<?php

namespace App\Services\QuestionImport\Parsers;

use App\Services\QuestionImport\Support\QuestionTextPatternParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PdfQuestionParser implements QuestionFileParser
{
    public function __construct(
        private readonly QuestionTextPatternParser $textPatternParser,
    ) {
    }

    public function supports(string $extension): bool
    {
        return strtolower($extension) === 'pdf';
    }

    public function parse(UploadedFile $file): array
    {
        $content = @file_get_contents($file->getRealPath());
        if ($content === false || trim($content) === '') {
            throw ValidationException::withMessages([
                'file_import' => 'Khong the doc file PDF da tai len.',
            ]);
        }

        $text = $this->extractTextFromPdf($content);
        if (trim($text) === '') {
            throw ValidationException::withMessages([
                'file_import' => 'PDF hien chi ho tro file text-based. File nay co the la PDF scan/image-only hoac khong trich xuat duoc text.',
            ]);
        }

        $blocks = [];
        foreach (preg_split("/\r\n|\n|\r/u", $text) ?: [] as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $blocks[] = [
                'text' => $line,
                'line' => $index + 1,
                'has_bold' => false,
                'has_highlight' => false,
            ];
        }

        $questions = $this->textPatternParser->parseBlocks($blocks, 'pdf');
        if ($questions === []) {
            throw ValidationException::withMessages([
                'file_import' => 'Khong nhan dien duoc cau hoi trac nghiem tu PDF text. PDF scan/image-only hien chua duoc ho tro OCR.',
            ]);
        }

        return [
            'profile' => 'question_document_pdf_text',
            'source_format' => 'pdf',
            'original_name' => $file->getClientOriginalName(),
            'questions' => $questions,
        ];
    }

    private function extractTextFromPdf(string $content): string
    {
        if (!str_contains($content, '%PDF')) {
            return '';
        }

        // We only support text-based PDFs here by extracting literal text operators from content streams.
        preg_match_all('/<<(.*?)>>\s*stream\r?\n(.*?)\r?\nendstream/s', $content, $matches, PREG_SET_ORDER);

        $segments = [];
        foreach ($matches as $match) {
            $dictionary = $match[1] ?? '';
            $stream = $match[2] ?? '';
            $decodedStream = $this->decodeStream($stream, $dictionary);
            if ($decodedStream === '') {
                continue;
            }

            $segment = $this->extractTextOperators($decodedStream);
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        if ($segments === []) {
            return '';
        }

        $text = implode("\n", $segments);
        $text = preg_replace("/\n{2,}/u", "\n", $text) ?? $text;

        return trim($text);
    }

    private function decodeStream(string $stream, string $dictionary): string
    {
        if (!str_contains($dictionary, '/FlateDecode')) {
            return $stream;
        }

        foreach ([
            @gzuncompress($stream),
            @gzinflate($stream),
            @gzinflate(substr(ltrim($stream, "\r\n"), 2)),
        ] as $decoded) {
            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        return '';
    }

    private function extractTextOperators(string $decodedStream): string
    {
        $lines = [];

        if (preg_match_all('/\((?:\\\\.|[^()\\\\])*\)\s*Tj/s', $decodedStream, $singleMatches) > 0) {
            foreach ($singleMatches[0] as $match) {
                if (preg_match('/(\((?:\\\\.|[^()\\\\])*\))\s*Tj/s', $match, $parts) === 1) {
                    $lines[] = $this->decodePdfLiteralString($parts[1]);
                }
            }
        }

        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $decodedStream, $arrayMatches) > 0) {
            foreach ($arrayMatches[1] as $arrayContent) {
                $parts = [];
                if (preg_match_all('/\((?:\\\\.|[^()\\\\])*\)/s', $arrayContent, $stringMatches) > 0) {
                    foreach ($stringMatches[0] as $rawLiteral) {
                        $parts[] = $this->decodePdfLiteralString($rawLiteral);
                    }
                }

                $joined = trim(implode('', $parts));
                if ($joined !== '') {
                    $lines[] = $joined;
                }
            }
        }

        return trim(implode("\n", array_filter($lines, fn ($line) => trim($line) !== '')));
    }

    private function decodePdfLiteralString(string $rawLiteral): string
    {
        $value = substr($rawLiteral, 1, -1);
        $value = preg_replace_callback('/\\\\([0-7]{1,3})/', function (array $matches) {
            return chr(octdec($matches[1]));
        }, $value) ?? $value;

        return trim(strtr($value, [
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\b' => "\x08",
            '\\f' => "\f",
            '\\(' => '(',
            '\\)' => ')',
            '\\\\' => '\\',
        ]));
    }
}
