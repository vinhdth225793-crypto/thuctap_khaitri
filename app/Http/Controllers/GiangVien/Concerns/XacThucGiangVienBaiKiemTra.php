<?php

namespace App\Http\Controllers\GiangVien\Concerns;

use App\Models\BaiKiemTra;
use App\Models\GiangVien;
use App\Models\PhanCongModuleGiangVien;

trait XacThucGiangVienBaiKiemTra
{
    protected function authorizeTeacherForScope(GiangVien $giangVien, int $khoaHocId, ?int $moduleId, string $loaiBaiKiemTra): void
    {
        $query = PhanCongModuleGiangVien::query()
            ->where('giang_vien_id', $giangVien->id)
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('trang_thai', 'da_nhan');

        if ($loaiBaiKiemTra !== 'cuoi_khoa') {
            $query->where('module_hoc_id', $moduleId);
        }

        abort_unless($query->exists(), 403, 'Bạn không được phân công cho bài kiểm tra này.');
    }

    protected function authorizeTeacherForExam(?GiangVien $giangVien, BaiKiemTra $baiKiemTra): void
    {
        abort_if(! $giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        $this->authorizeTeacherForScope(
            $giangVien,
            (int) $baiKiemTra->khoa_hoc_id,
            $baiKiemTra->module_hoc_id ? (int) $baiKiemTra->module_hoc_id : null,
            $baiKiemTra->loai_bai_kiem_tra
        );
    }

    /**
     * @return array<int, int>
     */
    protected function getAcceptedModuleIds(GiangVien $giangVien): array
    {
        return PhanCongModuleGiangVien::query()
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->pluck('module_hoc_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function getAcceptedCourseIds(GiangVien $giangVien): array
    {
        return PhanCongModuleGiangVien::query()
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->pluck('khoa_hoc_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function buildAccessibleExamQuery(array $moduleIds, array $courseIds)
    {
        return BaiKiemTra::query()
            ->where(function ($query) use ($moduleIds, $courseIds) {
                $hasCondition = false;

                if ($moduleIds !== []) {
                    $query->whereIn('module_hoc_id', $moduleIds);
                    $hasCondition = true;
                }

                if ($courseIds !== []) {
                    $method = $hasCondition ? 'orWhere' : 'where';

                    $query->{$method}(function ($courseQuery) use ($courseIds) {
                        $courseQuery->whereIn('khoa_hoc_id', $courseIds)
                            ->where(function ($examQuery) {
                                $examQuery->whereNull('module_hoc_id')
                                    ->orWhere('loai_bai_kiem_tra', 'cuoi_khoa');
                            });
                    });

                    $hasCondition = true;
                }

                if (! $hasCondition) {
                    $query->whereRaw('1 = 0');
                }
            });
    }
}
