<?php

namespace App\Services\Scheduling;

use App\Models\GiangVienLichRanh;
use App\Models\LichHoc;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TeacherAvailabilityService
{
    public function findOverlappingAvailabilities(
        int $teacherId,
        string $type,
        ?int $weekday,
        ?string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreAvailabilityId = null,
    ): Collection {
        $query = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacherId)
            ->where('loai_lich_ranh', $type)
            ->where('gio_bat_dau', '<', $endTime)
            ->where('gio_ket_thuc', '>', $startTime);

        if ($ignoreAvailabilityId !== null) {
            $query->where('id', '!=', $ignoreAvailabilityId);
        }

        if ($type === GiangVienLichRanh::LOAI_THEO_NGAY) {
            $query->whereDate('ngay_cu_the', $date);
        } else {
            $query->where('thu_trong_tuan', $weekday);
        }

        return $query->orderBy('gio_bat_dau')->get();
    }

    public function findMatchingAvailabilities(int $teacherId, Carbon|string $date, string $startTime, string $endTime): Collection
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $weekday = $this->resolveThuTrongTuan($date);

        return GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacherId)
            ->where('trang_thai', GiangVienLichRanh::TRANG_THAI_HOAT_DONG)
            ->where(function ($query) use ($date, $weekday) {
                $query
                    ->where(function ($subQuery) use ($date) {
                        $subQuery->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_NGAY)
                            ->whereDate('ngay_cu_the', $date->toDateString());
                    })
                    ->orWhere(function ($subQuery) use ($weekday) {
                        $subQuery->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_TUAN)
                            ->where('thu_trong_tuan', $weekday);
                    });
            })
            ->where('gio_bat_dau', '<=', $startTime)
            ->where('gio_ket_thuc', '>=', $endTime)
            ->orderByRaw("CASE WHEN loai_lich_ranh = 'theo_ngay' THEN 0 ELSE 1 END")
            ->orderBy('gio_bat_dau')
            ->get();
    }

    public function isAvailable(int $teacherId, Carbon|string $date, string $startTime, string $endTime): bool
    {
        return $this->findMatchingAvailabilities($teacherId, $date, $startTime, $endTime)->isNotEmpty();
    }

    /**
     * @return array{weekly:array<int, array<string, mixed>>, specific:array<int, array<string, mixed>>, active_count:int}
     */
    public function availabilitySummaryForTeacher(int $teacherId): array
    {
        $availabilities = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacherId)
            ->where('trang_thai', GiangVienLichRanh::TRANG_THAI_HOAT_DONG)
            ->orderByRaw("CASE WHEN loai_lich_ranh = 'theo_tuan' THEN 0 ELSE 1 END")
            ->orderBy('thu_trong_tuan')
            ->orderBy('ngay_cu_the')
            ->orderBy('gio_bat_dau')
            ->get();

        return [
            'weekly' => $availabilities
                ->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_TUAN)
                ->map(fn (GiangVienLichRanh $item) => [
                    'id' => $item->id,
                    'label' => $item->thu_label,
                    'time' => $item->time_range,
                    'schedule' => $item->schedule_range_label,
                    'note' => $item->ghi_chu,
                ])
                ->values()
                ->all(),
            'specific' => $availabilities
                ->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_NGAY)
                ->filter(fn (GiangVienLichRanh $item) => $item->ngay_cu_the !== null && $item->ngay_cu_the->gte(today()))
                ->take(8)
                ->map(fn (GiangVienLichRanh $item) => [
                    'id' => $item->id,
                    'label' => $item->display_date_or_day,
                    'time' => $item->time_range,
                    'schedule' => $item->schedule_range_label,
                    'note' => $item->ghi_chu,
                ])
                ->values()
                ->all(),
            'active_count' => $availabilities->count(),
        ];
    }

    public function coversWindow(array|GiangVienLichRanh $availability, Carbon|string $date, string $startTime, string $endTime): bool
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $type = (string) data_get($availability, 'loai_lich_ranh');

        if ((string) data_get($availability, 'trang_thai', GiangVienLichRanh::TRANG_THAI_HOAT_DONG) !== GiangVienLichRanh::TRANG_THAI_HOAT_DONG) {
            return false;
        }

        if ($type === GiangVienLichRanh::LOAI_THEO_NGAY) {
            if ((string) data_get($availability, 'ngay_cu_the') !== $date->toDateString()) {
                return false;
            }
        } else {
            if ((int) data_get($availability, 'thu_trong_tuan') !== $this->resolveThuTrongTuan($date)) {
                return false;
            }
        }

        return (string) data_get($availability, 'gio_bat_dau') <= $startTime
            && (string) data_get($availability, 'gio_ket_thuc') >= $endTime;
    }

    public function findImpactedSchedulesAfterChange(GiangVienLichRanh $availability, array $newAttributes = []): Collection
    {
        $candidateAttributes = array_merge($availability->toArray(), $newAttributes);
        $teacherId = (int) $availability->giang_vien_id;

        $futureSchedules = LichHoc::query()
            ->with(['khoaHoc', 'moduleHoc'])
            ->where('giang_vien_id', $teacherId)
            ->where('trang_thai', '!=', 'huy')
            ->whereDate('ngay_hoc', '>=', today()->toDateString())
            ->orderBy('ngay_hoc')
            ->orderBy('gio_bat_dau')
            ->get();

        $activeOtherAvailabilities = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacherId)
            ->where('id', '!=', $availability->id)
            ->where('trang_thai', GiangVienLichRanh::TRANG_THAI_HOAT_DONG)
            ->get();

        return $futureSchedules->filter(function (LichHoc $schedule) use ($availability, $candidateAttributes, $activeOtherAvailabilities) {
            $date = $schedule->ngay_hoc?->toDateString();
            if ($date === null) {
                return false;
            }

            $startTime = substr((string) $schedule->gio_bat_dau, 0, 5);
            $endTime = substr((string) $schedule->gio_ket_thuc, 0, 5);

            $currentlyCovered = $this->coversWindow($availability, $date, $startTime, $endTime);
            if (!$currentlyCovered) {
                return false;
            }

            $coveredByOtherAvailability = $activeOtherAvailabilities->contains(function (GiangVienLichRanh $otherAvailability) use ($date, $startTime, $endTime) {
                return $this->coversWindow($otherAvailability, $date, $startTime, $endTime);
            });

            if ($coveredByOtherAvailability) {
                return false;
            }

            return !$this->coversWindow($candidateAttributes, $date, $startTime, $endTime);
        })->values();
    }

    public function resolveThuTrongTuan(Carbon $date): int
    {
        return $date->dayOfWeek === Carbon::SUNDAY ? 8 : ($date->dayOfWeek + 1);
    }
}
