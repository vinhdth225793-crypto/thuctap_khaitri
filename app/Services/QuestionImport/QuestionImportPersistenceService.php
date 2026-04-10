<?php

namespace App\Services\QuestionImport;

use App\Models\NganHangCauHoi;
use Illuminate\Support\Facades\DB;

class QuestionImportPersistenceService
{
    /**
     * @param  array<string, mixed>  $preview
     * @return array{created:int, skipped_duplicate_db:int, ids:array<int, int>}
     */
    public function confirmImport(array $preview, int $khoaHocId, int $userId, ?int $moduleHocId = null): array
    {
        $createdCount = 0;
        $skippedDuplicateDbCount = 0;
        $createdIds = [];

        DB::transaction(function () use ($preview, $khoaHocId, $moduleHocId, $userId, &$createdCount, &$skippedDuplicateDbCount, &$createdIds) {
            foreach (($preview['data'] ?? []) as $item) {
                if (($item['status'] ?? null) !== 'hop_le') {
                    continue;
                }

                $noiDungCauHoi = trim((string) ($item['noi_dung_cau_hoi'] ?? ''));
                if ($noiDungCauHoi === '') {
                    continue;
                }

                if (NganHangCauHoi::isDuplicate($khoaHocId, $noiDungCauHoi)) {
                    $skippedDuplicateDbCount++;
                    continue;
                }

                $questionType = (string) ($item['loai_cau_hoi'] ?? NganHangCauHoi::LOAI_TRAC_NGHIEM);
                if (!in_array($questionType, [NganHangCauHoi::LOAI_TRAC_NGHIEM, NganHangCauHoi::LOAI_TU_LUAN], true)) {
                    $questionType = NganHangCauHoi::LOAI_TRAC_NGHIEM;
                }

                $cauHoi = NganHangCauHoi::create([
                    'khoa_hoc_id' => $khoaHocId,
                    'module_hoc_id' => $moduleHocId,
                    'nguoi_tao_id' => $userId,
                    'ma_cau_hoi' => NganHangCauHoi::generateQuestionCode(),
                    'noi_dung' => $noiDungCauHoi,
                    'loai_cau_hoi' => $questionType,
                    'kieu_dap_an' => $questionType === NganHangCauHoi::LOAI_TU_LUAN ? null : NganHangCauHoi::KIEU_MOT_DAP_AN,
                    'muc_do' => 'trung_binh',
                    'diem_mac_dinh' => 1,
                    'goi_y_tra_loi' => $item['goi_y_tra_loi'] ?? null,
                    'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
                    'co_the_tai_su_dung' => true,
                ]);

                $answers = collect($item['import_answers'] ?? [])
                    ->values()
                    ->map(function (array $answer, int $index) {
                        return [
                            'ky_hieu' => $answer['ky_hieu'] ?? $answer['thu_tu_hien_thi'] ?? chr(65 + $index),
                            'noi_dung' => $answer['noi_dung'],
                            'is_dap_an_dung' => (bool) ($answer['is_dap_an_dung'] ?? false),
                            'thu_tu' => $index + 1,
                        ];
                    })
                    ->all();

                if ($questionType === NganHangCauHoi::LOAI_TRAC_NGHIEM && $answers !== []) {
                    $cauHoi->dapAns()->createMany($answers);
                }

                $createdCount++;
                $createdIds[] = (int) $cauHoi->id;
            }
        });

        return [
            'created' => $createdCount,
            'skipped_duplicate_db' => $skippedDuplicateDbCount,
            'ids' => $createdIds,
        ];
    }
}
