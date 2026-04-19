<?php

namespace App\Services;

use App\Models\DiemDanh;
use App\Models\LichHoc;
use Illuminate\Support\Collection;

class CourseAttendanceScoreService
{
    /**
     * @return array{tong_so_buoi: int, so_buoi_tham_du: int, ty_le_tham_du: float|null, diem_diem_danh: float|null}
     */
    public function calculateForCourse(int $khoaHocId, int $hocVienId): array
    {
        $scheduleIds = LichHoc::query()
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('trang_thai', '!=', 'huy')
            ->pluck('id');

        return $this->calculateFromScheduleIds($scheduleIds, $hocVienId);
    }

    /**
     * @return array{tong_so_buoi: int, so_buoi_tham_du: int, ty_le_tham_du: float|null, diem_diem_danh: float|null}
     */
    public function calculateForModule(int $moduleHocId, int $hocVienId): array
    {
        $scheduleIds = LichHoc::query()
            ->where('module_hoc_id', $moduleHocId)
            ->where('trang_thai', '!=', 'huy')
            ->pluck('id');

        return $this->calculateFromScheduleIds($scheduleIds, $hocVienId);
    }

    /**
     * @param  Collection<int, int>  $scheduleIds
     * @return array{tong_so_buoi: int, so_buoi_tham_du: int, ty_le_tham_du: float|null, diem_diem_danh: float|null}
     */
    public function calculateFromScheduleIds(Collection $scheduleIds, int $hocVienId): array
    {
        $tongSoBuoi = $scheduleIds->count();

        if ($tongSoBuoi === 0) {
            return [
                'tong_so_buoi' => 0,
                'so_buoi_tham_du' => 0,
                'ty_le_tham_du' => null,
                'diem_diem_danh' => null,
            ];
        }

        $soBuoiThamDu = DiemDanh::query()
            ->where('hoc_vien_id', $hocVienId)
            ->whereIn('lich_hoc_id', $scheduleIds->all())
            ->whereIn('trang_thai', ['co_mat', 'vao_tre'])
            ->distinct('lich_hoc_id')
            ->count('lich_hoc_id');

        $tyLeThamDu = round(($soBuoiThamDu / $tongSoBuoi) * 100, 2);

        return [
            'tong_so_buoi' => $tongSoBuoi,
            'so_buoi_tham_du' => $soBuoiThamDu,
            'ty_le_tham_du' => $tyLeThamDu,
            'diem_diem_danh' => round(($tyLeThamDu / 100) * 10, 2),
        ];
    }
}
