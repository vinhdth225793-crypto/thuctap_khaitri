<?php

namespace App\Services\Scheduling;

use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Support\Carbon;

class TeacherScheduleRuleService
{
    /**
     * @return array<int, int>
     */
    public function allowedWeekdays(): array
    {
        // Cho phép từ Thứ 2 (2) đến Chủ nhật (8)
        return [2, 3, 4, 5, 6, 7, 8];
    }

    public function weekdayLabel(Carbon|string $date): string
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $weekday = $date->dayOfWeek === Carbon::SUNDAY ? 8 : ($date->dayOfWeek + 1);

        return \App\Models\LichHoc::$thuLabels[$weekday] ?? $date->translatedFormat('l');
    }

    public function isAllowedWeekday(Carbon|string $date): bool
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $weekday = $date->dayOfWeek === Carbon::SUNDAY ? 8 : ($date->dayOfWeek + 1);

        return in_array($weekday, $this->allowedWeekdays(), true);
    }

    public function isWithinStandardHours(string $startTime, string $endTime): bool
    {
        $start = substr($startTime, 0, 5);
        $end = substr($endTime, 0, 5);

        // Giới hạn khung giờ chuẩn của trung tâm: 07:30 - 20:45
        return $start >= TeachingPeriodCatalog::standardStartTime()
            && $end <= TeachingPeriodCatalog::standardEndTime()
            && $start < $end;
    }

    /**
     * @return array{ok:bool,message:string,rule_label:string}
     */
    public function inspect(Carbon|string $date, string $startTime, string $endTime): array
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        if (!$this->isAllowedWeekday($date)) {
            return [
                'ok' => false,
                'message' => 'Hệ thống chỉ cho phép xếp lịch từ Thứ 2 đến Chủ nhật.',
                'rule_label' => $this->ruleLabel(),
            ];
        }

        if (!$this->isWithinStandardHours($startTime, $endTime)) {
            return [
                'ok' => false,
                'message' => 'Khung giờ phải nằm trong lịch dạy chuẩn ' . TeachingPeriodCatalog::standardStartTime() . ' - ' . TeachingPeriodCatalog::standardEndTime() . '.',
                'rule_label' => $this->ruleLabel(),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Khung dạy nằm trong lịch dạy chuẩn của hệ thống.',
            'rule_label' => $this->ruleLabel(),
        ];
    }

    public function ruleLabel(): string
    {
        return 'Thứ 2 - Chủ nhật | ' . TeachingPeriodCatalog::standardStartTime() . ' - ' . TeachingPeriodCatalog::standardEndTime() . ' | 12 tiết';
    }
}
