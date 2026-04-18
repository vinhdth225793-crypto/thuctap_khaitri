<?php

namespace App\Services;

use App\Models\BaiLamBaiKiemTra;
use Illuminate\Support\Collection;

class ExamResultAggregationService
{
    public const HIGHEST_SCORE = 'highest_score';
    public const LATEST_ATTEMPT = 'latest_attempt';
    public const AVERAGE_ATTEMPTS = 'average_attempts';
    public const FIRST_ATTEMPT = 'first_attempt';

    /**
     * @param  iterable<int, BaiLamBaiKiemTra>  $attempts
     * @return array{score: float|null, source_attempt_id: int|null, source_attempt_ids: array<int, int>, strategy: string, metadata: array<string, mixed>}
     */
    public function aggregate(iterable $attempts, ?string $strategy = null): array
    {
        $strategy = $this->normalizeStrategy($strategy);
        $allAttempts = collect($attempts)->filter(fn ($attempt) => $attempt instanceof BaiLamBaiKiemTra)->values();
        $eligibleAttempts = $allAttempts
            ->filter(fn (BaiLamBaiKiemTra $attempt) => $attempt->trang_thai_cham === 'da_cham' && $attempt->diem_so !== null)
            ->values();

        if ($eligibleAttempts->isEmpty()) {
            return [
                'score' => null,
                'source_attempt_id' => null,
                'source_attempt_ids' => [],
                'strategy' => $strategy,
                'metadata' => [
                    'eligible_attempt_count' => 0,
                    'ignored_attempt_count' => $allAttempts->count(),
                    'attempts' => $this->summarizeAttempts($allAttempts),
                ],
            ];
        }

        if ($strategy === self::AVERAGE_ATTEMPTS) {
            return [
                'score' => round((float) $eligibleAttempts->avg(fn (BaiLamBaiKiemTra $attempt) => (float) $attempt->diem_so), 2),
                'source_attempt_id' => null,
                'source_attempt_ids' => $eligibleAttempts->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'strategy' => $strategy,
                'metadata' => [
                    'eligible_attempt_count' => $eligibleAttempts->count(),
                    'ignored_attempt_count' => $allAttempts->count() - $eligibleAttempts->count(),
                    'attempts' => $this->summarizeAttempts($allAttempts),
                    'formula' => 'average scored attempts',
                ],
            ];
        }

        $selectedAttempt = match ($strategy) {
            self::LATEST_ATTEMPT => $this->latestAttempt($eligibleAttempts),
            self::FIRST_ATTEMPT => $this->firstAttempt($eligibleAttempts),
            default => $this->highestScoreAttempt($eligibleAttempts),
        };

        return [
            'score' => $selectedAttempt ? round((float) $selectedAttempt->diem_so, 2) : null,
            'source_attempt_id' => $selectedAttempt?->id ? (int) $selectedAttempt->id : null,
            'source_attempt_ids' => $selectedAttempt?->id ? [(int) $selectedAttempt->id] : [],
            'strategy' => $strategy,
            'metadata' => [
                'eligible_attempt_count' => $eligibleAttempts->count(),
                'ignored_attempt_count' => $allAttempts->count() - $eligibleAttempts->count(),
                'attempts' => $this->summarizeAttempts($allAttempts),
                'selected_attempt' => $selectedAttempt ? $this->summarizeAttempt($selectedAttempt) : null,
            ],
        ];
    }

    public function normalizeStrategy(?string $strategy): string
    {
        return in_array($strategy, $this->supportedStrategies(), true)
            ? $strategy
            : self::HIGHEST_SCORE;
    }

    public function label(?string $strategy): string
    {
        return match ($this->normalizeStrategy($strategy)) {
            self::LATEST_ATTEMPT => 'Lan nop moi nhat',
            self::AVERAGE_ATTEMPTS => 'Trung binh cac lan da cham',
            self::FIRST_ATTEMPT => 'Lan nop dau tien',
            default => 'Diem cao nhat',
        };
    }

    /**
     * @return array<int, string>
     */
    public function supportedStrategies(): array
    {
        return [
            self::HIGHEST_SCORE,
            self::LATEST_ATTEMPT,
            self::AVERAGE_ATTEMPTS,
            self::FIRST_ATTEMPT,
        ];
    }

    /**
     * @param  Collection<int, BaiLamBaiKiemTra>  $attempts
     */
    private function highestScoreAttempt(Collection $attempts): ?BaiLamBaiKiemTra
    {
        return $attempts
            ->sortByDesc(fn (BaiLamBaiKiemTra $attempt) => sprintf(
                '%013.2f-%013d-%013d',
                (float) $attempt->diem_so,
                (int) ($attempt->nop_luc?->timestamp ?? 0),
                (int) $attempt->id
            ))
            ->first();
    }

    /**
     * @param  Collection<int, BaiLamBaiKiemTra>  $attempts
     */
    private function latestAttempt(Collection $attempts): ?BaiLamBaiKiemTra
    {
        return $attempts
            ->sortByDesc(fn (BaiLamBaiKiemTra $attempt) => sprintf(
                '%013d-%013d-%013d',
                (int) ($attempt->nop_luc?->timestamp ?? $attempt->updated_at?->timestamp ?? 0),
                (int) ($attempt->lan_lam_thu ?? 0),
                (int) $attempt->id
            ))
            ->first();
    }

    /**
     * @param  Collection<int, BaiLamBaiKiemTra>  $attempts
     */
    private function firstAttempt(Collection $attempts): ?BaiLamBaiKiemTra
    {
        return $attempts
            ->sortBy(fn (BaiLamBaiKiemTra $attempt) => sprintf(
                '%013d-%013d-%013d',
                (int) ($attempt->lan_lam_thu ?? PHP_INT_MAX),
                (int) ($attempt->nop_luc?->timestamp ?? $attempt->created_at?->timestamp ?? PHP_INT_MAX),
                (int) $attempt->id
            ))
            ->first();
    }

    /**
     * @param  Collection<int, BaiLamBaiKiemTra>  $attempts
     * @return array<int, array<string, mixed>>
     */
    private function summarizeAttempts(Collection $attempts): array
    {
        return $attempts
            ->sortBy(fn (BaiLamBaiKiemTra $attempt) => (int) ($attempt->lan_lam_thu ?? 0))
            ->map(fn (BaiLamBaiKiemTra $attempt) => $this->summarizeAttempt($attempt))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeAttempt(BaiLamBaiKiemTra $attempt): array
    {
        return [
            'id' => (int) $attempt->id,
            'lan_lam_thu' => (int) $attempt->lan_lam_thu,
            'diem_so' => $attempt->diem_so !== null ? round((float) $attempt->diem_so, 2) : null,
            'trang_thai' => $attempt->trang_thai,
            'trang_thai_cham' => $attempt->trang_thai_cham,
            'nop_luc' => optional($attempt->nop_luc)->toDateTimeString(),
        ];
    }
}
