<?php

namespace App\Services\Scheduling;

use App\Models\LichHoc;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TeacherScheduleConflictService
{
    public function findConflicts(
        int $teacherId,
        Carbon|string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreScheduleId = null,
        ?int $startPeriod = null,
        ?int $endPeriod = null,
    ): Collection {
        $date = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $query = LichHoc::query()
            ->with(['khoaHoc', 'moduleHoc', 'giangVien.nguoiDung'])
            ->where('giang_vien_id', $teacherId)
            ->where('trang_thai', '!=', 'huy')
            ->whereDate('ngay_hoc', $date)
            ->orderBy('gio_bat_dau');

        if ($ignoreScheduleId !== null) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        if ($startPeriod !== null && $endPeriod !== null) {
            $query->where(function ($builder) use ($startPeriod, $endPeriod, $startTime, $endTime) {
                $builder
                    ->where(function ($periodQuery) use ($startPeriod, $endPeriod) {
                        $periodQuery
                            ->whereNotNull('tiet_bat_dau')
                            ->whereNotNull('tiet_ket_thuc')
                            ->where('tiet_bat_dau', '<=', $endPeriod)
                            ->where('tiet_ket_thuc', '>=', $startPeriod);
                    })
                    ->orWhere(function ($timeQuery) use ($startTime, $endTime) {
                        $timeQuery
                            ->where(function ($nested) {
                                $nested->whereNull('tiet_bat_dau')->orWhereNull('tiet_ket_thuc');
                            })
                            ->where('gio_bat_dau', '<', $endTime)
                            ->where('gio_ket_thuc', '>', $startTime);
                    });
            });
        } else {
            $query
                ->where('gio_bat_dau', '<', $endTime)
                ->where('gio_ket_thuc', '>', $startTime);
        }

        return $query->get();
    }

    public function buildConflictMessage(Collection $conflicts): ?string
    {
        $firstConflict = $conflicts->first();
        if (!$firstConflict instanceof LichHoc) {
            return null;
        }

        $teacherName = $firstConflict->giangVien?->nguoiDung?->ho_ten ?? 'Giảng viên được chọn';
        $courseCode = $firstConflict->khoaHoc?->ma_khoa_hoc ?? 'N/A';
        $moduleName = $firstConflict->moduleHoc?->ten_module ?? 'N/A';
        $date = $firstConflict->ngay_hoc?->format('d/m/Y') ?? 'N/A';
        $periodLabel = TeachingPeriodCatalog::rangeLabel($firstConflict->tiet_bat_dau, $firstConflict->tiet_ket_thuc);
        $startTime = substr((string) $firstConflict->gio_bat_dau, 0, 5);
        $endTime = substr((string) $firstConflict->gio_ket_thuc, 0, 5);

        return "Giảng viên {$teacherName} đã có lịch dạy ở khóa học {$courseCode} / module {$moduleName} vào ngày {$date}, {$periodLabel} ({$startTime} - {$endTime}).";
    }
}
