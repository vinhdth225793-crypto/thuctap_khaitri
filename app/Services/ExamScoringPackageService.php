<?php

namespace App\Services;

class ExamScoringPackageService
{
    /**
     * Splits a total score across a number of questions.
     * 
     * @param float $totalScore
     * @param int $questionCount
     * @return array<int, float>
     */
    public function splitPoints(float $totalScore, int $questionCount): array
    {
        if ($questionCount <= 0) {
            return [];
        }

        $basePoint = round($totalScore / $questionCount, 2);
        $points = array_fill(0, $questionCount, $basePoint);

        $currentSum = array_sum($points);
        $difference = round($totalScore - $currentSum, 2);

        if ($difference !== 0.0) {
            $points[$questionCount - 1] = round($points[$questionCount - 1] + $difference, 2);
        }

        return $points;
    }
}
