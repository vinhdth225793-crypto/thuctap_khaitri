<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Support\Collection;

class TeacherAssignmentResolver
{
    /**
     * @param  Collection<int, LichHoc>  $schedules
     * @return array<string, int>
     */
    public function mapAcceptedAssignmentsForSchedules(int $teacherId, Collection $schedules): array
    {
        $pairs = $schedules
            ->map(fn (LichHoc $schedule) => [
                'khoa_hoc_id' => (int) $schedule->khoa_hoc_id,
                'module_hoc_id' => $schedule->module_hoc_id !== null ? (int) $schedule->module_hoc_id : null,
            ])
            ->values()
            ->all();

        return $this->mapAcceptedAssignments($teacherId, $pairs);
    }

    public function resolveForSchedule(int $teacherId, LichHoc $schedule): ?int
    {
        return $this->resolveAcceptedAssignmentId(
            $teacherId,
            (int) $schedule->khoa_hoc_id,
            $schedule->module_hoc_id !== null ? (int) $schedule->module_hoc_id : null,
        );
    }

    public function resolveForExam(int $teacherId, BaiKiemTra $exam): ?int
    {
        return $this->resolveAcceptedAssignmentId(
            $teacherId,
            (int) $exam->khoa_hoc_id,
            $exam->module_hoc_id !== null ? (int) $exam->module_hoc_id : null,
        );
    }

    public function resolveAcceptedAssignmentId(int $teacherId, int $courseId, ?int $moduleId = null): ?int
    {
        $assignmentMap = $this->mapAcceptedAssignments($teacherId, [[
            'khoa_hoc_id' => $courseId,
            'module_hoc_id' => $moduleId,
        ]]);

        return $assignmentMap[$this->buildKey($courseId, $moduleId)]
            ?? $assignmentMap[$this->buildKey($courseId, null)]
            ?? null;
    }

    /**
     * @param  array<int, array{khoa_hoc_id:int,module_hoc_id:int|null}>  $pairs
     * @return array<string, int>
     */
    private function mapAcceptedAssignments(int $teacherId, array $pairs): array
    {
        if ($pairs === []) {
            return [];
        }

        $courseIds = collect($pairs)
            ->pluck('khoa_hoc_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $moduleIds = collect($pairs)
            ->pluck('module_hoc_id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $assignments = PhanCongModuleGiangVien::query()
            ->select(['id', 'khoa_hoc_id', 'module_hoc_id'])
            ->where('giang_vien_id', $teacherId)
            ->where('trang_thai', 'da_nhan')
            ->whereIn('khoa_hoc_id', $courseIds->all())
            ->when($moduleIds->isNotEmpty(), fn ($query) => $query->where(function ($builder) use ($moduleIds) {
                $builder
                    ->whereIn('module_hoc_id', $moduleIds->all())
                    ->orWhereNull('module_hoc_id');
            }))
            ->orderByDesc('id')
            ->get();

        $resolved = [];

        foreach ($assignments as $assignment) {
            $specificKey = $this->buildKey((int) $assignment->khoa_hoc_id, $assignment->module_hoc_id !== null ? (int) $assignment->module_hoc_id : null);
            $courseFallbackKey = $this->buildKey((int) $assignment->khoa_hoc_id, null);

            if (!isset($resolved[$specificKey])) {
                $resolved[$specificKey] = (int) $assignment->id;
            }

            if (!isset($resolved[$courseFallbackKey])) {
                $resolved[$courseFallbackKey] = (int) $assignment->id;
            }
        }

        return $resolved;
    }

    private function buildKey(int $courseId, ?int $moduleId): string
    {
        return $courseId . ':' . ($moduleId ?? '*');
    }
}
