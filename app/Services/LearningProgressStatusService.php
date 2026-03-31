<?php

namespace App\Services;

use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;

class LearningProgressStatusService
{
    /**
     * @param  LichHoc|int  $schedule
     * @return array{module: array<string, mixed>|null, course: array<string, mixed>|null}
     */
    public function syncFromSchedule(LichHoc|int $schedule): array
    {
        $scheduleModel = $schedule instanceof LichHoc
            ? $schedule
            : LichHoc::query()->findOrFail($schedule);

        $moduleSnapshot = null;
        $courseSnapshot = null;

        if ($scheduleModel->module_hoc_id) {
            $moduleSnapshot = $this->syncModuleStatus((int) $scheduleModel->module_hoc_id);
        }

        if ($scheduleModel->khoa_hoc_id) {
            $courseSnapshot = $this->syncCourseStatus((int) $scheduleModel->khoa_hoc_id);
        }

        return [
            'module' => $moduleSnapshot,
            'course' => $courseSnapshot,
        ];
    }

    /**
     * @param  ModuleHoc|int  $module
     * @return array<string, mixed>
     */
    public function syncModuleStatus(ModuleHoc|int $module): array
    {
        $moduleModel = $module instanceof ModuleHoc
            ? $module->loadMissing('lichHocs')
            : ModuleHoc::query()
                ->with('lichHocs')
                ->findOrFail($module);

        $moduleModel->forgetLearningProgressSnapshot();

        return $moduleModel->learning_progress_snapshot;
    }

    /**
     * @param  KhoaHoc|int  $course
     * @return array<string, mixed>
     */
    public function syncCourseStatus(KhoaHoc|int $course): array
    {
        $courseModel = $course instanceof KhoaHoc
            ? $course->loadMissing('moduleHocs.lichHocs')
            : KhoaHoc::query()
                ->with('moduleHocs.lichHocs')
                ->findOrFail($course);

        $courseModel->forgetLearningProgressSnapshot();
        foreach ($courseModel->moduleHocs as $module) {
            $module->forgetLearningProgressSnapshot();
        }

        $snapshot = $courseModel->learning_progress_snapshot;
        $targetOperationalStatus = $this->resolveOperationalStatus($courseModel, $snapshot['status']);

        if ($targetOperationalStatus !== null && $courseModel->trang_thai_van_hanh !== $targetOperationalStatus) {
            $courseModel->updateQuietly([
                'trang_thai_van_hanh' => $targetOperationalStatus,
            ]);
            $courseModel->trang_thai_van_hanh = $targetOperationalStatus;
        }

        return $snapshot;
    }

    /**
     * @param  iterable<int|string>  $courseIds
     */
    public function syncCourses(iterable $courseIds): void
    {
        $uniqueIds = collect($courseIds)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        foreach ($uniqueIds as $courseId) {
            $this->syncCourseStatus($courseId);
        }
    }

    private function resolveOperationalStatus(KhoaHoc $course, string $learningStatus): ?string
    {
        if ($course->loai !== 'hoat_dong') {
            return null;
        }

        if ($learningStatus === KhoaHoc::LEARNING_STATUS_HOAN_THANH) {
            return 'ket_thuc';
        }

        if ($course->trang_thai_van_hanh === 'ket_thuc') {
            return 'dang_day';
        }

        return null;
    }
}
