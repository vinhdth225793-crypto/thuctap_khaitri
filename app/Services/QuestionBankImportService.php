<?php

namespace App\Services;

use App\Services\QuestionImport\QuestionDocumentImportService;
use App\Services\QuestionImport\QuestionImportPersistenceService;
use Illuminate\Http\UploadedFile;

class QuestionBankImportService
{
    public function __construct(
        private readonly QuestionDocumentImportService $questionDocumentImportService,
        private readonly QuestionImportPersistenceService $questionImportPersistenceService,
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
        return $this->questionDocumentImportService->buildPreview($file, $khoaHocId);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array{created:int, skipped_duplicate_db:int, ids:array<int, int>}
     */
    public function confirmImport(array $preview, int $khoaHocId, int $userId, ?int $moduleHocId = null): array
    {
        return $this->questionImportPersistenceService->confirmImport($preview, $khoaHocId, $userId, $moduleHocId);
    }
}
