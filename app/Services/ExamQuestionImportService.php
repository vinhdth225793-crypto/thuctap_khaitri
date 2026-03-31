<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use Illuminate\Http\UploadedFile;

class ExamQuestionImportService
{
    public function __construct(
        private readonly QuestionBankImportService $questionBankImportService,
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
    public function previewForExam(UploadedFile $file, BaiKiemTra $baiKiemTra): array
    {
        return $this->questionBankImportService->buildPreview($file, $baiKiemTra->khoa_hoc_id);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array{created:int, skipped_duplicate_db:int, ids:array<int, int>}
     */
    public function importToBank(array $preview, BaiKiemTra $baiKiemTra, int $userId): array
    {
        $result = $this->questionBankImportService->confirmImport(
            $preview, 
            $baiKiemTra->khoa_hoc_id, 
            $userId, 
            $baiKiemTra->module_hoc_id
        );

        return $result;
    }
}
