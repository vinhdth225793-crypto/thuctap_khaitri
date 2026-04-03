<?php

namespace App\Services\Scheduling;

use App\Models\GiangVienDonXinNghi;
use App\Models\LichHoc;
use App\Services\TeacherAssignmentResolver;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherScheduleViewService
{
    public function __construct(
        private readonly TeacherAssignmentResolver $assignmentResolver,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildTeacherWeek(int $teacherId, Carbon|string|null $anchorDate = null): array
    {
        $anchor = $anchorDate instanceof Carbon
            ? $anchorDate->copy()
            : Carbon::parse($anchorDate ?? today()->toDateString());

        $weekStart = $anchor->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $days = collect(range(0, 6))->map(function (int $offset) use ($weekStart) {
            $date = $weekStart->copy()->addDays($offset);

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('d/m'),
                'full_label' => $date->format('d/m/Y'),
                'thu_label' => LichHoc::$thuLabels[$date->dayOfWeek === Carbon::SUNDAY ? 8 : ($date->dayOfWeek + 1)] ?? $date->translatedFormat('l'),
            ];
        });

        $leaveRequestItems = $this->buildLeaveRequestItems($teacherId, $weekStart, $weekEnd);
        $scheduledItems = $this->buildScheduledItems($teacherId, $weekStart, $weekEnd, $leaveRequestItems);

        return [
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'week_label' => $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y'),
            'days' => $days->all(),
            'periods' => TeachingPeriodCatalog::periods(),
            'grid' => $this->buildGrid($days, $scheduledItems, $leaveRequestItems),
            'scheduled_items' => $scheduledItems->values()->all(),
            'leave_request_items' => $leaveRequestItems->values()->all(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildLeaveRequestItems(int $teacherId, Carbon $weekStart, Carbon $weekEnd): Collection
    {
        return GiangVienDonXinNghi::query()
            ->with(['khoaHoc', 'moduleHoc', 'lichHoc'])
            ->where('giang_vien_id', $teacherId)
            ->whereBetween('ngay_xin_nghi', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('ngay_xin_nghi')
            ->orderBy('tiet_bat_dau')
            ->get()
            ->map(function (GiangVienDonXinNghi $request) {
                return [
                    'id' => $request->id,
                    'date' => $request->ngay_xin_nghi?->toDateString(),
                    'date_label' => $request->ngay_xin_nghi?->format('d/m/Y'),
                    'period_start' => $request->tiet_bat_dau,
                    'period_end' => $request->tiet_ket_thuc,
                    'period_label' => $request->tiet_range_label,
                    'session_label' => $request->buoi_hoc_label,
                    'summary' => $request->schedule_range_label,
                    'status' => $request->trang_thai,
                    'status_label' => $request->trang_thai_label,
                    'status_color' => $request->trang_thai_color,
                    'reason' => $request->ly_do,
                    'feedback' => $request->ghi_chu_phan_hoi,
                    'linked_schedule_id' => $request->lich_hoc_id,
                    'course_code' => $request->khoaHoc?->ma_khoa_hoc ?? $request->lichHoc?->khoaHoc?->ma_khoa_hoc,
                    'course_name' => $request->khoaHoc?->ten_khoa_hoc ?? $request->lichHoc?->khoaHoc?->ten_khoa_hoc,
                    'module_name' => $request->moduleHoc?->ten_module ?? $request->lichHoc?->moduleHoc?->ten_module,
                    'created_at' => $request->created_at?->toIso8601String(),
                ];
            })
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $leaveRequestItems
     * @return Collection<int, array<string, mixed>>
     */
    private function buildScheduledItems(int $teacherId, Carbon $weekStart, Carbon $weekEnd, Collection $leaveRequestItems): Collection
    {
        $schedules = LichHoc::query()
            ->with(['khoaHoc', 'moduleHoc'])
            ->where('giang_vien_id', $teacherId)
            ->where('trang_thai', '!=', 'huy')
            ->whereBetween('ngay_hoc', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('ngay_hoc')
            ->orderBy('gio_bat_dau')
            ->get();

        $assignmentMap = $this->assignmentResolver->mapAcceptedAssignmentsForSchedules($teacherId, $schedules);

        return $schedules
            ->map(function (LichHoc $schedule) use ($leaveRequestItems, $assignmentMap) {
                $range = $this->extractRangeFromSchedule($schedule);
                $matchedLeaveRequests = $leaveRequestItems
                    ->filter(function (array $item) use ($schedule, $range) {
                        if ($item['linked_schedule_id'] !== null) {
                            return (int) $item['linked_schedule_id'] === (int) $schedule->id;
                        }

                        if ($item['date'] !== $schedule->ngay_hoc?->toDateString()) {
                            return false;
                        }

                        if ($range === null || $item['period_start'] === null || $item['period_end'] === null) {
                            return false;
                        }

                        return TeachingPeriodCatalog::overlaps(
                            (int) $range['start'],
                            (int) $range['end'],
                            (int) $item['period_start'],
                            (int) $item['period_end'],
                        );
                    })
                    ->sortByDesc('created_at')
                    ->values();
                $latestLeaveRequest = $matchedLeaveRequests->first();
                $specificKey = (int) $schedule->khoa_hoc_id . ':' . ($schedule->module_hoc_id !== null ? (int) $schedule->module_hoc_id : '*');
                $fallbackKey = (int) $schedule->khoa_hoc_id . ':*';

                $assignmentId = $assignmentMap[$specificKey] ?? $assignmentMap[$fallbackKey] ?? null;

                return [
                    'id' => $schedule->id,
                    'assignment_id' => $assignmentId,
                    'course_id' => $assignmentId ?? $schedule->khoa_hoc_id,
                    'khoa_hoc_id' => $schedule->khoa_hoc_id,
                    'module_id' => $schedule->module_hoc_id,
                    'date' => $schedule->ngay_hoc?->toDateString(),
                    'date_label' => $schedule->ngay_hoc?->format('d/m/Y'),
                    'weekday_label' => $schedule->thu_label,
                    'period_start' => $range['start'] ?? null,
                    'period_end' => $range['end'] ?? null,
                    'session' => $range['session'] ?? null,
                    'period_label' => TeachingPeriodCatalog::rangeLabel($range['start'] ?? null, $range['end'] ?? null),
                    'session_label' => TeachingPeriodCatalog::sessionLabel($range['session'] ?? null),
                    'summary' => $schedule->schedule_range_label,
                    'course_code' => $schedule->khoaHoc?->ma_khoa_hoc,
                    'course_name' => $schedule->khoaHoc?->ten_khoa_hoc,
                    'module_name' => $schedule->moduleHoc?->ten_module,
                    'buoi_so' => $schedule->buoi_so,
                    'status_label' => $schedule->trang_thai_label,
                    'status_color' => $schedule->trang_thai_color,
                    'timeline_status' => $schedule->timeline_trang_thai,
                    'mode_label' => $schedule->hinh_thuc_label,
                    'leave_request_count' => $matchedLeaveRequests->count(),
                    'leave_status_label' => $latestLeaveRequest['status_label'] ?? null,
                    'leave_status_color' => $latestLeaveRequest['status_color'] ?? null,
                    'leave_reason' => $latestLeaveRequest['reason'] ?? null,
                    
                    // Quick Action Links
                    'routes' => [
                        'show_course' => $this->buildTeacherSessionActionUrl($assignmentId, (int) $schedule->khoa_hoc_id, (int) $schedule->id),
                        'attendance' => $this->buildTeacherSessionActionUrl($assignmentId, (int) $schedule->khoa_hoc_id, (int) $schedule->id, 'attendance'),
                        'resources' => $this->buildTeacherSessionActionUrl($assignmentId, (int) $schedule->khoa_hoc_id, (int) $schedule->id, 'resources'),
                        'exams' => $this->buildTeacherSessionActionUrl($assignmentId, (int) $schedule->khoa_hoc_id, (int) $schedule->id, 'exams'),
                        'leave_request' => route('giang-vien.don-xin-nghi.create', ['lich_hoc_id' => $schedule->id]),
                    ],
                    
                    // Interaction Flags
                    'can_attendance' => true,
                    'can_resource' => true,
                    'can_exam' => true,
                    'can_leave' => $schedule->timeline_trang_thai === 'cho',
                ];
            })
            ->values();
    }

    private function buildTeacherSessionActionUrl(?int $assignmentId, int $courseId, int $scheduleId, ?string $quickAction = null): string
    {
        $params = [
            'id' => $assignmentId ?? $courseId,
            'focus_lich_hoc_id' => $scheduleId,
        ];

        if ($quickAction !== null) {
            $params['quick_action'] = $quickAction;
        }

        return route('giang-vien.khoa-hoc.show', $params) . '#session-' . $scheduleId;
    }

    /**
     * @param  Collection<int, array<string, string>>  $days
     * @param  Collection<int, array<string, mixed>>  $scheduledItems
     * @param  Collection<int, array<string, mixed>>  $leaveRequestItems
     * @return array<int, array<string, mixed>>
     */
    private function buildGrid(Collection $days, Collection $scheduledItems, Collection $leaveRequestItems): array
    {
        $grid = [];

        foreach (array_keys(TeachingPeriodCatalog::periods()) as $period) {
            $row = [
                'period' => $period,
                'label' => TeachingPeriodCatalog::periodLabels()[$period],
                'time' => TeachingPeriodCatalog::periods()[$period]['start'] . ' - ' . TeachingPeriodCatalog::periods()[$period]['end'],
                'session' => TeachingPeriodCatalog::sessionLabel(TeachingPeriodCatalog::periods()[$period]['session']),
                'cells' => [],
            ];

            foreach ($days as $day) {
                $scheduled = $scheduledItems
                    ->filter(fn (array $item) => $item['date'] === $day['date']
                        && $item['period_start'] !== null
                        && TeachingPeriodCatalog::containsRange($item['period_start'], $item['period_end'], $period, $period))
                    ->values();

                $leaveRequests = $leaveRequestItems
                    ->filter(fn (array $item) => $item['date'] === $day['date']
                        && $item['period_start'] !== null
                        && TeachingPeriodCatalog::containsRange($item['period_start'], $item['period_end'], $period, $period))
                    ->values();

                $row['cells'][$day['date']] = [
                    'scheduled' => $scheduled->all(),
                    'leave_requests' => $leaveRequests->all(),
                    'occupied' => $scheduled->isNotEmpty(),
                    'has_leave_request' => $leaveRequests->isNotEmpty(),
                ];
            }

            $grid[] = $row;
        }

        return $grid;
    }

    /**
     * @return array{start:int,end:int,session:?string}|null
     */
    private function extractRangeFromSchedule(LichHoc $schedule): ?array
    {
        return TeachingPeriodCatalog::normalizeRange($schedule->tiet_bat_dau, $schedule->tiet_ket_thuc, $schedule->buoi_hoc)
            ?? TeachingPeriodCatalog::periodsFromTimes(
                substr((string) $schedule->gio_bat_dau, 0, 5),
                substr((string) $schedule->gio_ket_thuc, 0, 5),
            );
    }
}
