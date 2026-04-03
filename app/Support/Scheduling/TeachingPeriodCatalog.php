<?php

namespace App\Support\Scheduling;

class TeachingPeriodCatalog
{
    /**
     * Define the center's standard shifts.
     */
    public const SHIFT_SANG = 'sang';
    public const SHIFT_CHIEU = 'chieu';
    public const SHIFT_TOI = 'toi';

    /**
     * Common study patterns (Day of week).
     * 2 = Monday, ..., 7 = Saturday, 8 = Sunday (as used in existing system)
     */
    public const PATTERN_246 = [2, 4, 6];
    public const PATTERN_357 = [3, 5, 7];
    public const PATTERN_CUOI_TUAN = [7, 8];

    /**
     * Canonical center timetable used to map period-based scheduling
     * back into the existing time-based training flow.
     * 
     * Adjusted to match center's real hours:
     * Sáng: 07:30 - 11:30 (4 tiêt)
     * Chiều: 13:30 - 16:30 (3 tiết)
     * Tối: 18:30 - 20:45 (2 tiết lớn hoặc quy đổi)
     * 
     * To keep 1-12 range for compatibility:
     * 1-4: Sáng
     * 5-8: Chiều
     * 9-12: Tối
     *
     * @return array<int, array{label:string,start:string,end:string,session:string}>
     */
    public static function periods(): array
    {
        return [
            // Sáng: 07:30 - 11:30 (4h -> 4 tiết, mỗi tiết 1h hoặc 50p+10p nghỉ)
            1 => ['label' => 'Tiết 1', 'start' => '07:30', 'end' => '08:20', 'session' => self::SHIFT_SANG],
            2 => ['label' => 'Tiết 2', 'start' => '08:30', 'end' => '09:20', 'session' => self::SHIFT_SANG],
            3 => ['label' => 'Tiết 3', 'start' => '09:40', 'end' => '10:30', 'session' => self::SHIFT_SANG],
            4 => ['label' => 'Tiết 4', 'start' => '10:40', 'end' => '11:30', 'session' => self::SHIFT_SANG],
            
            // Chiều: 13:30 - 16:30 (3h -> dùng tiết 5-7, tiết 8 để trống hoặc dùng dự phòng)
            5 => ['label' => 'Tiết 5', 'start' => '13:30', 'end' => '14:20', 'session' => self::SHIFT_CHIEU],
            6 => ['label' => 'Tiết 6', 'start' => '14:30', 'end' => '15:20', 'session' => self::SHIFT_CHIEU],
            7 => ['label' => 'Tiết 7', 'start' => '15:40', 'end' => '16:30', 'session' => self::SHIFT_CHIEU],
            8 => ['label' => 'Tiết 8', 'start' => '16:30', 'end' => '17:20', 'session' => self::SHIFT_CHIEU], // Backup
            
            // Tối: 18:30 - 20:45 (2h15p -> dùng tiết 9-11)
            9 => ['label' => 'Tiết 9', 'start' => '18:30', 'end' => '19:15', 'session' => self::SHIFT_TOI],
            10 => ['label' => 'Tiết 10', 'start' => '19:15', 'end' => '20:00', 'session' => self::SHIFT_TOI],
            11 => ['label' => 'Tiết 11', 'start' => '20:00', 'end' => '20:45', 'session' => self::SHIFT_TOI],
            12 => ['label' => 'Tiết 12', 'start' => '20:45', 'end' => '21:30', 'session' => self::SHIFT_TOI], // Backup
        ];
    }

    /**
     * @return array<string, array{label:string,start:int,end:int,start_time:string,end_time:string}>
     */
    public static function sessions(): array
    {
        return [
            self::SHIFT_SANG => [
                'label' => 'Ca sáng',
                'start' => 1,
                'end' => 4,
                'start_time' => '07:30',
                'end_time' => '11:30',
            ],
            self::SHIFT_CHIEU => [
                'label' => 'Ca chiều',
                'start' => 5,
                'end' => 7,
                'start_time' => '13:30',
                'end_time' => '16:30',
            ],
            self::SHIFT_TOI => [
                'label' => 'Ca tối',
                'start' => 9,
                'end' => 11,
                'start_time' => '18:30',
                'end_time' => '20:45',
            ],
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
        
        // Safety check if period doesn't exist in our redefined list
        if (!isset($periods[$startPeriod]) || !isset($periods[$endPeriod])) {
             // Fallback to session boundaries if it's a full session
             foreach (self::sessions() as $session => $def) {
                 if ($def['start'] === $startPeriod && $def['end'] === $endPeriod) {
                     return ['start_time' => $def['start_time'], 'end_time' => $def['end_time']];
                 }
             }
             return ['start_time' => '00:00', 'end_time' => '00:00'];
        }

        return [
            'start_time' => $periods[$startPeriod]['start'],
            'end_time' => $periods[$endPeriod]['end'],
        ];
    }

    /**
     * @return array{start:int,end:int,session:?string}|null
     */
    public static function periodsFromTimes(?string $startTime, ?string $endTime): ?array
    {
        if (blank($startTime) || blank($endTime)) {
            return null;
        }

        $s = substr((string) $startTime, 0, 5);
        $e = substr((string) $endTime, 0, 5);

        // First check if it matches a exact session
        foreach (self::sessions() as $session => $def) {
            if ($def['start_time'] === $s && $def['end_time'] === $e) {
                return ['start' => $def['start'], 'end' => $def['end'], 'session' => $session];
            }
        }

        $matches = [];
        foreach (self::periods() as $period => $definition) {
            if ($definition['start'] < $e && $definition['end'] > $s) {
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
        return '07:30';
    }

    public static function standardEndTime(): string
    {
        return '20:45';
    }
}
