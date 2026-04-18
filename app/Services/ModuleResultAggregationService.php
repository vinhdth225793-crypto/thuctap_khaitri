<?php

namespace App\Services;

use App\Models\KetQuaHocTap;
use App\Models\ModuleHoc;
use Illuminate\Support\Collection;

class ModuleResultAggregationService
{
    public const ALL_EXAMS_AVERAGE = 'all_exams_average';
    public const MODULE_EXAM_WITH_SESSION_AVERAGE = 'module_exam_with_session_average';
    public const SELECTED_EXAMS_AVERAGE = 'selected_exams_average';
    public const WEIGHTED_AVERAGE = 'weighted_average';
    public const TOP_N_AVERAGE = 'top_n_average';

    /**
     * @param  Collection<int, KetQuaHocTap>  $examResults
     * @return array{score: float|null, source_result_ids: array<int, int>, source_attempt_ids: array<int, int>, strategy: string, metadata: array<string, mixed>}
     */
    public function aggregate(ModuleHoc $module, Collection $examResults, ?array $config = null): array
    {
        $config = $config ?? ($module->ket_qua_config ?: []);
        $strategy = $this->normalizeStrategy($config['aggregation_strategy'] ?? $config['module_aggregation_strategy'] ?? null);
        $eligibleResults = $examResults
            ->filter(fn (KetQuaHocTap $result) => $result->bai_kiem_tra_id !== null && $result->diem_kiem_tra !== null)
            ->values();

        if ($strategy === self::MODULE_EXAM_WITH_SESSION_AVERAGE) {
            return $this->aggregateModuleExamWithSessionAverage($eligibleResults, $config);
        }

        $selectedResults = $this->selectResults($eligibleResults, $strategy, $config);

        if ($selectedResults->isEmpty()) {
            return [
                'score' => null,
                'source_result_ids' => [],
                'source_attempt_ids' => [],
                'strategy' => $strategy,
                'metadata' => [
                    'eligible_exam_count' => $eligibleResults->count(),
                    'selected_exam_count' => 0,
                    'formula' => $strategy,
                    'exams' => $this->summarizeResults($eligibleResults),
                ],
            ];
        }

        $score = $strategy === self::WEIGHTED_AVERAGE
            ? $this->weightedAverage($selectedResults, $config)
            : round((float) $selectedResults->avg(fn (KetQuaHocTap $result) => (float) $result->diem_kiem_tra), 2);

        return [
            'score' => $score,
            'source_result_ids' => $selectedResults->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'source_attempt_ids' => $this->collectAttemptIds($selectedResults),
            'strategy' => $strategy,
            'metadata' => [
                'eligible_exam_count' => $eligibleResults->count(),
                'selected_exam_count' => $selectedResults->count(),
                'formula' => $strategy,
                'config' => $config,
                'exams' => $this->summarizeResults($selectedResults),
            ],
        ];
    }

    public function normalizeStrategy(?string $strategy): string
    {
        return in_array($strategy, $this->supportedStrategies(), true)
            ? $strategy
            : self::MODULE_EXAM_WITH_SESSION_AVERAGE;
    }

    public function label(?string $strategy): string
    {
        return match ($this->normalizeStrategy($strategy)) {
            self::MODULE_EXAM_WITH_SESSION_AVERAGE => 'Bai nho TB + bai cuoi module',
            self::SELECTED_EXAMS_AVERAGE => 'Trung binh bai duoc chon',
            self::WEIGHTED_AVERAGE => 'Trung binh co trong so',
            self::TOP_N_AVERAGE => 'Trung binh top N bai',
            default => 'Trung binh tat ca bai',
        };
    }

    /**
     * @return array<int, string>
     */
    public function supportedStrategies(): array
    {
        return [
            self::ALL_EXAMS_AVERAGE,
            self::MODULE_EXAM_WITH_SESSION_AVERAGE,
            self::SELECTED_EXAMS_AVERAGE,
            self::WEIGHTED_AVERAGE,
            self::TOP_N_AVERAGE,
        ];
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     * @return array{score: float|null, source_result_ids: array<int, int>, source_attempt_ids: array<int, int>, strategy: string, metadata: array<string, mixed>}
     */
    private function aggregateModuleExamWithSessionAverage(Collection $results, array $config): array
    {
        $sessionResults = $results
            ->filter(fn (KetQuaHocTap $result) => $this->isSessionExam($result))
            ->values();
        $majorResults = $results
            ->reject(fn (KetQuaHocTap $result) => $this->isSessionExam($result))
            ->values();

        $components = collect();

        if ($sessionResults->isNotEmpty()) {
            $components->push([
                'type' => 'session_exam_average',
                'label' => 'Trung binh bai kiem tra buoi',
                'score' => round((float) $sessionResults->avg(fn (KetQuaHocTap $result) => (float) $result->diem_kiem_tra), 2),
                'result_ids' => $sessionResults->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'exam_ids' => $sessionResults->pluck('bai_kiem_tra_id')->map(fn ($id) => (int) $id)->values()->all(),
            ]);
        }

        foreach ($majorResults as $result) {
            $components->push([
                'type' => $result->baiKiemTra?->loai_bai_kiem_tra === 'cuoi_khoa' ? 'course_final_exam' : 'module_final_exam',
                'label' => $result->baiKiemTra?->tieu_de ?: 'Bai kiem tra lon',
                'score' => round((float) $result->diem_kiem_tra, 2),
                'result_ids' => [(int) $result->id],
                'exam_ids' => [(int) $result->bai_kiem_tra_id],
            ]);
        }

        if ($components->isEmpty()) {
            return [
                'score' => null,
                'source_result_ids' => [],
                'source_attempt_ids' => [],
                'strategy' => self::MODULE_EXAM_WITH_SESSION_AVERAGE,
                'metadata' => [
                    'eligible_exam_count' => 0,
                    'selected_exam_count' => 0,
                    'formula' => self::MODULE_EXAM_WITH_SESSION_AVERAGE,
                    'components' => [],
                    'config' => $config,
                ],
            ];
        }

        return [
            'score' => round((float) $components->avg('score'), 2),
            'source_result_ids' => $results->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'source_attempt_ids' => $this->collectAttemptIds($results),
            'strategy' => self::MODULE_EXAM_WITH_SESSION_AVERAGE,
            'metadata' => [
                'eligible_exam_count' => $results->count(),
                'selected_exam_count' => $results->count(),
                'session_exam_count' => $sessionResults->count(),
                'major_exam_count' => $majorResults->count(),
                'formula' => self::MODULE_EXAM_WITH_SESSION_AVERAGE,
                'config' => $config,
                'components' => $components->values()->all(),
                'exams' => $this->summarizeResults($results),
            ],
        ];
    }

    private function isSessionExam(KetQuaHocTap $result): bool
    {
        $exam = $result->baiKiemTra;

        return $exam?->loai_bai_kiem_tra === 'buoi_hoc'
            || $exam?->pham_vi === 'buoi_hoc'
            || $exam?->lich_hoc_id !== null;
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     * @return Collection<int, KetQuaHocTap>
     */
    private function selectResults(Collection $results, string $strategy, array $config): Collection
    {
        if ($strategy === self::SELECTED_EXAMS_AVERAGE || $strategy === self::WEIGHTED_AVERAGE) {
            $selectedExamIds = collect($config['selected_exam_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();

            if ($selectedExamIds->isNotEmpty()) {
                $results = $results
                    ->filter(fn (KetQuaHocTap $result) => $selectedExamIds->contains((int) $result->bai_kiem_tra_id))
                    ->values();
            }
        }

        if ($strategy === self::TOP_N_AVERAGE) {
            $topN = max(1, (int) ($config['top_n'] ?? $config['top_exam_count'] ?? 1));

            return $results
                ->sortByDesc(fn (KetQuaHocTap $result) => (float) $result->diem_kiem_tra)
                ->take($topN)
                ->values();
        }

        return $results->values();
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     */
    private function weightedAverage(Collection $results, array $config): ?float
    {
        $weights = collect($config['exam_weights'] ?? [])
            ->mapWithKeys(fn ($weight, $examId) => [(int) $examId => max(0, (float) $weight)]);

        if ($weights->isEmpty()) {
            return round((float) $results->avg(fn (KetQuaHocTap $result) => (float) $result->diem_kiem_tra), 2);
        }

        $totalWeight = 0.0;
        $weightedScore = 0.0;

        foreach ($results as $result) {
            $weight = (float) ($weights[(int) $result->bai_kiem_tra_id] ?? 0);
            if ($weight <= 0) {
                continue;
            }

            $totalWeight += $weight;
            $weightedScore += ((float) $result->diem_kiem_tra) * $weight;
        }

        return $totalWeight > 0 ? round($weightedScore / $totalWeight, 2) : null;
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     * @return array<int, int>
     */
    private function collectAttemptIds(Collection $results): array
    {
        return $results
            ->flatMap(function (KetQuaHocTap $result) {
                $ids = $result->source_attempt_ids ?: [];
                if ($result->source_attempt_id) {
                    $ids[] = (int) $result->source_attempt_id;
                }

                if (isset($result->chi_tiet['bai_lam_id'])) {
                    $ids[] = (int) $result->chi_tiet['bai_lam_id'];
                }

                return $ids;
            })
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     * @return array<int, array<string, mixed>>
     */
    private function summarizeResults(Collection $results): array
    {
        return $results
            ->map(fn (KetQuaHocTap $result) => [
                'result_id' => (int) $result->id,
                'exam_id' => (int) $result->bai_kiem_tra_id,
                'score' => $result->diem_kiem_tra !== null ? round((float) $result->diem_kiem_tra, 2) : null,
                'status' => $result->trang_thai,
                'attempt_strategy_used' => $result->attempt_strategy_used,
                'source_attempt_id' => $result->source_attempt_id ? (int) $result->source_attempt_id : null,
            ])
            ->values()
            ->all();
    }
}
