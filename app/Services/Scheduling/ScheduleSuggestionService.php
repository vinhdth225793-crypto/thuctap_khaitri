<?php

namespace App\Services\Scheduling;

use App\Models\GiangVienDonXinNghi;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Support\Carbon;

class ScheduleSuggestionService
{
    public function __construct(
        private readonly TeacherScheduleRuleService $ruleService,
        private readonly TeacherScheduleConflictService $conflictService,
        private readonly TeacherLeaveRequestService $leaveRequestService,
    ) {
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    public function suggest(
        int $teacherId,
        int $durationMinutes,
        Carbon|string|null $fromDate = null,
        int $limit = 5,
        ?int $ignoreScheduleId = null,
    ): array {
        if ($durationMinutes <= 0) {
            return [];
        }

        $fromDate = $fromDate instanceof Carbon
            ? $fromDate->copy()->startOfDay()
            : Carbon::parse($fromDate ?? today()->toDateString())->startOfDay();

        $suggestions = [];
        $seenKeys = [];

        for ($offset = 0; $offset <= 30 && count($suggestions) < $limit; $offset++) {
            $date = $fromDate->copy()->addDays($offset);
            if (!$this->ruleService->isAllowedWeekday($date)) {
                continue;
            }

            foreach (TeachingPeriodCatalog::periods() as $startPeriod => $definition) {
                $periodCount = (int) ceil($durationMinutes / 50);
                $endPeriod = $startPeriod + $periodCount - 1;

                if ($endPeriod > 12) {
                    continue;
                }

                $times = TeachingPeriodCatalog::timeRangeFromPeriods($startPeriod, $endPeriod);
                if (!$this->ruleService->isWithinStandardHours($times['start_time'], $times['end_time'])) {
                    continue;
                }

                if ($this->conflictService->findConflicts(
                    $teacherId,
                    $date,
                    $times['start_time'],
                    $times['end_time'],
                    $ignoreScheduleId,
                    $startPeriod,
                    $endPeriod,
                )->isNotEmpty()) {
                    continue;
                }

                if ($this->leaveRequestService->findOverlappingRequests(
                    $teacherId,
                    $date,
                    $startPeriod,
                    $endPeriod,
                    null,
                    [GiangVienDonXinNghi::TRANG_THAI_DA_DUYET],
                )->isNotEmpty()) {
                    continue;
                }

                $key = $date->toDateString() . '|' . $startPeriod . '|' . $endPeriod;
                if (isset($seenKeys[$key])) {
                    continue;
                }

                $seenKeys[$key] = true;
                $suggestions[] = [
                    'date' => $date->toDateString(),
                    'date_label' => $date->format('d/m/Y'),
                    'start_time' => $times['start_time'],
                    'end_time' => $times['end_time'],
                    'period_start' => $startPeriod,
                    'period_end' => $endPeriod,
                    'period_label' => TeachingPeriodCatalog::rangeLabel($startPeriod, $endPeriod),
                    'session_label' => TeachingPeriodCatalog::sessionLabel(TeachingPeriodCatalog::resolveSessionFromRange($startPeriod, $endPeriod)),
                    'source' => 'Gợi ý theo lịch dạy chuẩn',
                ];

                if (count($suggestions) >= $limit) {
                    break;
                }
            }
        }

        return $suggestions;
    }
}
