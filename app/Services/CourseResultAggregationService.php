<?php

namespace App\Services;

use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use Illuminate\Support\Collection;

class CourseResultAggregationService
{
    public const AVERAGE_ALL_MODULES = 'average_all_modules';
    public const AVERAGE_SELECTED_MODULES = 'average_selected_modules';
    public const WEIGHTED_MODULES = 'weighted_modules';
    public const FINAL_EXAM_BASED = 'final_exam_based';
    public const SELECTED_EXAMS_AVERAGE = 'selected_exams_average';

    /**
     * @param  Collection<int, KetQuaHocTap>  $moduleResults
     * @param  Collection<int, KetQuaHocTap>  $finalExamResults
     * @param  Collection<int, KetQuaHocTap>  $allExamResults
     * @return array{score: float|null, completed_count: int, source_result_ids: array<int, int>, source_attempt_ids: array<int, int>, strategy: string, metadata: array<string, mixed>}
     */
    public function aggregate(
        KhoaHoc $course,
        Collection $moduleResults,
        Collection $finalExamResults,
        Collection $allExamResults,
        ?array $config = null
    ): array {
        $config = $config ?? ($course->ket_qua_config ?: []);
        $strategy = $this->normalizeStrategy(
            $config['aggregation_strategy'] ?? $config['course_aggregation_strategy'] ?? null,
            (string) $course->phuong_thuc_danh_gia
        );

        $sourceResults = match ($strategy) {
            self::AVERAGE_ALL_MODULES => $this->eligibleModuleResults($moduleResults),
            self::AVERAGE_SELECTED_MODULES => $this->selectedModuleResults($moduleResults, $config),
            self::WEIGHTED_MODULES => $this->selectedModuleResults($moduleResults, $config),
            self::SELECTED_EXAMS_AVERAGE => $this->selectedExamResults($allExamResults, $config),
            default => $this->selectedExamResults($finalExamResults, $config),
        };

        if ($strategy === self::FINAL_EXAM_BASED) {
            $sourceResults = $sourceResults
                ->sortByDesc(fn (KetQuaHocTap $result) => (float) $result->diem_kiem_tra)
                ->take(1)
                ->values();
        }

        if ($sourceResults->isEmpty()) {
            return [
                'score' => null,
                'completed_count' => 0,
                'source_result_ids' => [],
                'source_attempt_ids' => [],
                'strategy' => $strategy,
                'metadata' => [
                    'formula' => $strategy,
                    'config' => $config,
                    'source_count' => 0,
                ],
            ];
        }

        $score = $strategy === self::WEIGHTED_MODULES
            ? $this->weightedModuleAverage($sourceResults, $config)
            : round((float) $sourceResults->avg(fn (KetQuaHocTap $result) => $this->scoreOf($result, $strategy)), 2);

        return [
            'score' => $score,
            'completed_count' => $sourceResults->count(),
            'source_result_ids' => $sourceResults->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'source_attempt_ids' => $this->collectAttemptIds($sourceResults),
            'strategy' => $strategy,
            'metadata' => [
                'formula' => $strategy,
                'config' => $config,
                'source_count' => $sourceResults->count(),
                'sources' => $this->summarizeResults($sourceResults),
            ],
        ];
    }

    public function normalizeStrategy(?string $strategy, string $courseAssessmentMethod = 'cuoi_khoa'): string
    {
        if (in_array($strategy, $this->supportedStrategies(), true)) {
            return $strategy;
        }

        return $courseAssessmentMethod === 'theo_module'
            ? self::AVERAGE_ALL_MODULES
            : self::FINAL_EXAM_BASED;
    }

    public function label(?string $strategy, string $courseAssessmentMethod = 'cuoi_khoa'): string
    {
        return match ($this->normalizeStrategy($strategy, $courseAssessmentMethod)) {
            self::AVERAGE_ALL_MODULES => 'Trung binh tat ca module',
            self::AVERAGE_SELECTED_MODULES => 'Trung binh module duoc chon',
            self::WEIGHTED_MODULES => 'Trung binh module co trong so',
            self::SELECTED_EXAMS_AVERAGE => 'Trung binh bai duoc chon trong khoa',
            default => 'Theo bai kiem tra cuoi khoa',
        };
    }

    /**
     * @return array<int, string>
     */
    public function supportedStrategies(): array
    {
        return [
            self::AVERAGE_ALL_MODULES,
            self::AVERAGE_SELECTED_MODULES,
            self::WEIGHTED_MODULES,
            self::FINAL_EXAM_BASED,
            self::SELECTED_EXAMS_AVERAGE,
        ];
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     * @return Collection<int, KetQuaHocTap>
     */
    private function eligibleModuleResults(Collection $results): Collection
    {
        return $results
            ->filter(fn (KetQuaHocTap $result) => $result->module_hoc_id !== null && $result->bai_kiem_tra_id === null && $this->moduleScore($result) !== null)
            ->values();
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     * @return Collection<int, KetQuaHocTap>
     */
    private function selectedModuleResults(Collection $results, array $config): Collection
    {
        $results = $this->eligibleModuleResults($results);
        $selectedModuleIds = collect($config['selected_module_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedModuleIds->isEmpty()) {
            return $results;
        }

        return $results
            ->filter(fn (KetQuaHocTap $result) => $selectedModuleIds->contains((int) $result->module_hoc_id))
            ->values();
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     * @return Collection<int, KetQuaHocTap>
     */
    private function selectedExamResults(Collection $results, array $config): Collection
    {
        $results = $results
            ->filter(fn (KetQuaHocTap $result) => $result->bai_kiem_tra_id !== null && $result->diem_kiem_tra !== null)
            ->values();

        $selectedExamIds = collect($config['selected_exam_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedExamIds->isEmpty()) {
            return $results;
        }

        return $results
            ->filter(fn (KetQuaHocTap $result) => $selectedExamIds->contains((int) $result->bai_kiem_tra_id))
            ->values();
    }

    /**
     * @param  Collection<int, KetQuaHocTap>  $results
     */
    private function weightedModuleAverage(Collection $results, array $config): ?float
    {
        $weights = collect($config['module_weights'] ?? [])
            ->mapWithKeys(fn ($weight, $moduleId) => [(int) $moduleId => max(0, (float) $weight)]);

        if ($weights->isEmpty()) {
            return round((float) $results->avg(fn (KetQuaHocTap $result) => (float) $this->moduleScore($result)), 2);
        }

        $totalWeight = 0.0;
        $weightedScore = 0.0;

        foreach ($results as $result) {
            $weight = (float) ($weights[(int) $result->module_hoc_id] ?? 0);
            if ($weight <= 0) {
                continue;
            }

            $totalWeight += $weight;
            $weightedScore += ((float) $this->moduleScore($result)) * $weight;
        }

        return $totalWeight > 0 ? round($weightedScore / $totalWeight, 2) : null;
    }

    private function scoreOf(KetQuaHocTap $result, string $strategy): float
    {
        return $strategy === self::FINAL_EXAM_BASED || $strategy === self::SELECTED_EXAMS_AVERAGE
            ? (float) $result->diem_kiem_tra
            : (float) $this->moduleScore($result);
    }

    private function moduleScore(KetQuaHocTap $result): ?float
    {
        $score = $result->diem_giang_vien_chot ?? $result->diem_tong_ket;

        return $score !== null ? (float) $score : null;
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
                'module_id' => $result->module_hoc_id ? (int) $result->module_hoc_id : null,
                'exam_id' => $result->bai_kiem_tra_id ? (int) $result->bai_kiem_tra_id : null,
                'diem_kiem_tra' => $result->diem_kiem_tra !== null ? round((float) $result->diem_kiem_tra, 2) : null,
                'diem_tong_ket' => $result->diem_tong_ket !== null ? round((float) $result->diem_tong_ket, 2) : null,
                'diem_giang_vien_chot' => $result->diem_giang_vien_chot !== null ? round((float) $result->diem_giang_vien_chot, 2) : null,
                'status' => $result->trang_thai,
                'source_attempt_id' => $result->source_attempt_id ? (int) $result->source_attempt_id : null,
            ])
            ->values()
            ->all();
    }
}
