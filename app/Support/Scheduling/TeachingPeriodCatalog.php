<?php

namespace App\Support\Scheduling;

class TeachingPeriodCatalog
{
    /**
     * Canonical center timetable used to map period-based scheduling
     * back into the existing time-based training flow.
     *
     * @return array<int, array{label:string,start:string,end:string,session:string}>
     */
    public static function periods(): array
    {
        return [
            1 => ['label' => 'Tiết 1', 'start' => '08:00', 'end' => '08:50', 'session' => 'sang'],
            2 => ['label' => 'Tiết 2', 'start' => '09:00', 'end' => '09:50', 'session' => 'sang'],
            3 => ['label' => 'Tiết 3', 'start' => '10:00', 'end' => '10:50', 'session' => 'sang'],
            4 => ['label' => 'Tiết 4', 'start' => '11:00', 'end' => '11:50', 'session' => 'sang'],
            5 => ['label' => 'Tiết 5', 'start' => '12:00', 'end' => '12:50', 'session' => 'chieu'],
            6 => ['label' => 'Tiết 6', 'start' => '13:00', 'end' => '13:50', 'session' => 'chieu'],
            7 => ['label' => 'Tiết 7', 'start' => '14:00', 'end' => '14:50', 'session' => 'chieu'],
            8 => ['label' => 'Tiết 8', 'start' => '15:00', 'end' => '15:50', 'session' => 'chieu'],
            9 => ['label' => 'Tiết 9', 'start' => '16:00', 'end' => '16:50', 'session' => 'toi'],
            10 => ['label' => 'Tiết 10', 'start' => '17:00', 'end' => '17:50', 'session' => 'toi'],
            11 => ['label' => 'Tiết 11', 'start' => '18:00', 'end' => '18:50', 'session' => 'toi'],
            12 => ['label' => 'Tiết 12', 'start' => '19:00', 'end' => '19:50', 'session' => 'toi'],
        ];
    }

    /**
     * @return array<string, array{label:string,start:int,end:int}>
     */
    public static function sessions(): array
    {
        return [
            'sang' => ['label' => 'Ca sáng', 'start' => 1, 'end' => 4],
            'chieu' => ['label' => 'Ca chiều', 'start' => 5, 'end' => 8],
            'toi' => ['label' => 'Ca tối', 'start' => 9, 'end' => 12],
        ];
    }

    public static function sessionLabel(?string $session): ?string
    {
        return $session !== null ? (self::sessions()[$session]['label'] ?? null) : null;
    }

    /**
     * @return array<int, string>
     */
    public static function periodLabels(): array
    {
        $labels = [];

        foreach (self::periods() as $period => $definition) {
            $labels[$period] = $definition['label'];
        }

        return $labels;
    }

    /**
     * @return array{start:int,end:int,session:?string}|null
     */
    public static function normalizeRange(?int $startPeriod, ?int $endPeriod, ?string $session = null): ?array
    {
        if ($session !== null && isset(self::sessions()[$session])) {
            $startPeriod = self::sessions()[$session]['start'];
            $endPeriod = self::sessions()[$session]['end'];
        }

        if ($startPeriod === null || $endPeriod === null) {
            return null;
        }

        if ($startPeriod < 1 || $endPeriod > 12 || $startPeriod > $endPeriod) {
            return null;
        }

        return [
            'start' => $startPeriod,
            'end' => $endPeriod,
            'session' => self::resolveSessionFromRange($startPeriod, $endPeriod),
        ];
    }

    /**
     * @param  array<int, int|string>  $selectedPeriods
     * @return array{start:int,end:int,session:?string}|null
     */
    public static function normalizeSelectedPeriods(array $selectedPeriods): ?array
    {
        $selected = collect($selectedPeriods)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->sort()
            ->values();

        if ($selected->isEmpty()) {
            return null;
        }

        $expected = range((int) $selected->first(), (int) $selected->last());
        if ($selected->all() !== $expected) {
            return null;
        }

        return self::normalizeRange((int) $selected->first(), (int) $selected->last());
    }

    /**
     * @return array{start_time:string,end_time:string}
     */
    public static function timeRangeFromPeriods(int $startPeriod, int $endPeriod): array
    {
        $periods = self::periods();

        return [
            'start_time' => $periods[$startPeriod]['start'],
            'end_time' => $periods[$endPeriod]['end'],
        ];
    }

    /**
     * Uses overlap-based matching so existing free-form time data can still
     * appear in the new period-based views.
     *
     * @return array{start:int,end:int,session:?string}|null
     */
    public static function periodsFromTimes(?string $startTime, ?string $endTime): ?array
    {
        if (blank($startTime) || blank($endTime)) {
            return null;
        }

        $matches = [];
        foreach (self::periods() as $period => $definition) {
            if ($definition['start'] < substr((string) $endTime, 0, 5) && $definition['end'] > substr((string) $startTime, 0, 5)) {
                $matches[] = $period;
            }
        }

        if ($matches === []) {
            return null;
        }

        return self::normalizeRange(min($matches), max($matches));
    }

    public static function overlaps(int $startA, int $endA, int $startB, int $endB): bool
    {
        return $startA <= $endB && $endA >= $startB;
    }

    public static function containsRange(int $containerStart, int $containerEnd, int $startPeriod, int $endPeriod): bool
    {
        return $containerStart <= $startPeriod && $containerEnd >= $endPeriod;
    }

    public static function resolveSessionFromRange(int $startPeriod, int $endPeriod): ?string
    {
        foreach (self::sessions() as $session => $definition) {
            if ($definition['start'] <= $startPeriod && $definition['end'] >= $endPeriod) {
                return $session;
            }
        }

        return null;
    }

    public static function rangeLabel(?int $startPeriod, ?int $endPeriod): string
    {
        if ($startPeriod === null || $endPeriod === null) {
            return '-';
        }

        return $startPeriod === $endPeriod
            ? 'Tiết ' . $startPeriod
            : 'Tiết ' . $startPeriod . ' - ' . $endPeriod;
    }

    public static function standardStartTime(): string
    {
        return self::periods()[1]['start'];
    }

    public static function standardEndTime(): string
    {
        return self::periods()[12]['end'];
    }
}
