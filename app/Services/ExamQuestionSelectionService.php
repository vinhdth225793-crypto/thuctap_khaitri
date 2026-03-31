<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\NganHangCauHoi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ExamQuestionSelectionService
{
    private const MIN_QUESTION_SCORE = 0.25;

    public function buildDisplayQuery(BaiKiemTra $baiKiemTra, array $filters = []): Builder
    {
        $selectedIds = $this->selectedQuestionIds($baiKiemTra);

        $query = NganHangCauHoi::query()
            ->with(['moduleHoc:id,khoa_hoc_id,ten_module,ma_module'])
            ->where('khoa_hoc_id', $baiKiemTra->khoa_hoc_id);

        $this->applyScope($query, $baiKiemTra);

        $query->dungChoFlowRaDeHienTai()
            ->where(function (Builder $visibilityQuery) use ($selectedIds) {
                $visibilityQuery->where(function (Builder $eligibleQuery) {
                    $eligibleQuery->where('trang_thai', NganHangCauHoi::TRANG_THAI_SAN_SANG)
                        ->where('co_the_tai_su_dung', true);
                });

                if ($selectedIds !== []) {
                    $visibilityQuery->orWhereIn('id', $selectedIds);
                }
            });

        $this->applyFilters($query, $baiKiemTra, $filters);

        return $query;
    }

    public function buildSelectableQuery(BaiKiemTra $baiKiemTra): Builder
    {
        $query = NganHangCauHoi::query()
            ->where('khoa_hoc_id', $baiKiemTra->khoa_hoc_id)
            ->where('trang_thai', NganHangCauHoi::TRANG_THAI_SAN_SANG)
            ->where('co_the_tai_su_dung', true)
            ->dungChoFlowRaDeHienTai();

        $this->applyScope($query, $baiKiemTra);

        return $query;
    }

    /**
     * @return array<int, int>
     */
    public function selectableQuestionIds(BaiKiemTra $baiKiemTra): array
    {
        return $this->buildSelectableQuery($baiKiemTra)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array<int, int>  $questionIds
     * @param  array<int|string, mixed>  $questionScores
     * @return array{0: float, 1: string}
     */
    public function syncQuestions(BaiKiemTra $baiKiemTra, array $questionIds, array $questionScores): array
    {
        $normalizedQuestionIds = array_values(array_unique(array_map('intval', $questionIds)));

        if ($normalizedQuestionIds === []) {
            $baiKiemTra->chiTietCauHois()->delete();

            return [0.0, 'tu_luan'];
        }

        $availableQuestions = $this->buildSelectableQuery($baiKiemTra)
            ->whereIn('id', $normalizedQuestionIds)
            ->get()
            ->keyBy('id');

        if ($availableQuestions->count() !== count($normalizedQuestionIds)) {
            throw ValidationException::withMessages([
                'question_ids' => 'Danh sách câu hỏi có mục không hợp lệ, ngoài phạm vi đề hoặc không còn ở trạng thái sẵn sàng.',
            ]);
        }

        $baiKiemTra->chiTietCauHois()->delete();

        $tongDiem = 0.0;
        $types = [];

        foreach ($normalizedQuestionIds as $index => $questionId) {
            $cauHoi = $availableQuestions->get($questionId);
            $diemSo = (float) ($questionScores[$questionId] ?? $questionScores[$index] ?? 0);

            if ($diemSo < self::MIN_QUESTION_SCORE) {
                throw ValidationException::withMessages([
                    'question_scores.' . $questionId => 'Điểm mỗi câu phải từ ' . number_format(self::MIN_QUESTION_SCORE, 2) . ' trở lên.',
                ]);
            }

            $diemSo = round($diemSo, 2);

            $baiKiemTra->chiTietCauHois()->create([
                'ngan_hang_cau_hoi_id' => $cauHoi->id,
                'thu_tu' => $index + 1,
                'diem_so' => $diemSo,
                'bat_buoc' => true,
            ]);

            $tongDiem += $diemSo;
            $types[] = $cauHoi->loai_cau_hoi;
        }

        $loaiNoiDung = count(array_unique($types)) > 1
            ? 'hon_hop'
            : ($types[0] ?? 'tu_luan');

        return [round($tongDiem, 2), $loaiNoiDung];
    }

    private function applyScope(Builder $query, BaiKiemTra $baiKiemTra): void
    {
        if ($baiKiemTra->loai_bai_kiem_tra !== 'cuoi_khoa' && $baiKiemTra->module_hoc_id) {
            $query->where(function (Builder $scopeQuery) use ($baiKiemTra) {
                $scopeQuery->where('module_hoc_id', $baiKiemTra->module_hoc_id)
                    ->orWhereNull('module_hoc_id');
            });
        }
    }

    private function applyFilters(Builder $query, BaiKiemTra $baiKiemTra, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery->where('noi_dung', 'like', '%' . $search . '%')
                    ->orWhere('ma_cau_hoi', 'like', '%' . $search . '%');
            });
        }

        $loaiCauHoi = trim((string) ($filters['loai_cau_hoi'] ?? ''));
        if (in_array($loaiCauHoi, [NganHangCauHoi::LOAI_TRAC_NGHIEM, NganHangCauHoi::LOAI_TU_LUAN], true)) {
            $query->where('loai_cau_hoi', $loaiCauHoi);
        }

        $mucDo = trim((string) ($filters['muc_do'] ?? ''));
        if (in_array($mucDo, ['de', 'trung_binh', 'kho'], true)) {
            $query->where('muc_do', $mucDo);
        }

        $trangThai = trim((string) ($filters['trang_thai'] ?? ''));
        if (in_array($trangThai, [
            NganHangCauHoi::TRANG_THAI_NHAP,
            NganHangCauHoi::TRANG_THAI_SAN_SANG,
            NganHangCauHoi::TRANG_THAI_TAM_AN,
        ], true)) {
            $query->where('trang_thai', $trangThai);
        }

        $moduleHocId = isset($filters['module_hoc_id']) ? (int) $filters['module_hoc_id'] : null;
        if ($baiKiemTra->loai_bai_kiem_tra === 'cuoi_khoa' && $moduleHocId) {
            $query->where('module_hoc_id', $moduleHocId);
        }
    }

    /**
     * @return array<int, int>
     */
    private function selectedQuestionIds(BaiKiemTra $baiKiemTra): array
    {
        if ($baiKiemTra->relationLoaded('chiTietCauHois')) {
            return $baiKiemTra->chiTietCauHois
                ->pluck('ngan_hang_cau_hoi_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        return $baiKiemTra->chiTietCauHois()
            ->pluck('ngan_hang_cau_hoi_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
