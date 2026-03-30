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
        return [2, 3, 4, 5, 6];
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

        return $start >= TeachingPeriodCatalog::standardStartTime()
            && $end <= '20:00'
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
                'message' => 'He thong chi cho phep xep lich tu Thu 2 den Thu 6. ' . $this->weekdayLabel($date) . ' mac dinh khong duoc sap lich.',
                'rule_label' => $this->ruleLabel(),
            ];
        }

        if (!$this->isWithinStandardHours($startTime, $endTime)) {
            return [
                'ok' => false,
                'message' => 'Khung gio phai nam trong lich day chuan 08:00 - 20:00.',
                'rule_label' => $this->ruleLabel(),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Khung day nam trong lich day chuan cua he thong.',
            'rule_label' => $this->ruleLabel(),
        ];
    }

    public function ruleLabel(): string
    {
        return 'Thu 2 - Thu 6 | 08:00 - 20:00 | 12 tiet';
    }
}
