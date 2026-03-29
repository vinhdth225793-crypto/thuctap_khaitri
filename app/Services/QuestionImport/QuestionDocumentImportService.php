<?php

namespace App\Services\QuestionImport;

use App\Services\QuestionImport\Parsers\DocxQuestionParser;
use App\Services\QuestionImport\Parsers\ExcelQuestionParser;
use App\Services\QuestionImport\Parsers\PdfQuestionParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class QuestionDocumentImportService
{
    public function __construct(
        private readonly ExcelQuestionParser $excelQuestionParser,
        private readonly DocxQuestionParser $docxQuestionParser,
        private readonly PdfQuestionParser $pdfQuestionParser,
        private readonly ParsedQuestionValidator $parsedQuestionValidator,
    ) {
    }

    /**
     * @return array{
     *     source_format:string,
     *     profile:string,
     *     original_name:string,
     *     data:array<int, array<string, mixed>>,
     *     summary:array<string, int>
     * }
     */
    public function buildPreview(UploadedFile $file, int $khoaHocId): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $parsed = $this->resolveParser($extension)->parse($file);
        $validated = $this->parsedQuestionValidator->buildPreviewData($parsed['questions'], $khoaHocId);

        return [
            'source_format' => $parsed['source_format'],
            'profile' => $parsed['profile'],
            'original_name' => $parsed['original_name'],
            'data' => $validated['data'],
            'summary' => $validated['summary'],
        ];
    }

    private function resolveParser(string $extension)
    {
        foreach ([
            $this->excelQuestionParser,
            $this->docxQuestionParser,
            $this->pdfQuestionParser,
        ] as $parser) {
            if ($parser->supports($extension)) {
                return $parser;
            }
        }

        throw ValidationException::withMessages([
            'file_import' => 'Dinh dang file khong duoc ho tro. Vui long dung file .docx, .pdf, .xlsx, .csv hoac .txt.',
        ]);
    }
}
