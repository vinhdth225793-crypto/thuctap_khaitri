<?php

namespace App\Services;

use App\Models\DiemDanh;
use App\Models\DiemDanhGiangVien;
use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AttendanceReportService
{
    public function teacherWeeklyDashboard(array $filters = []): array
    {
        $currentWeekStart = now()->startOfWeek(Carbon::MONDAY);
        $retentionStart = $currentWeekStart->copy()->subMonthNoOverflow()->startOfWeek(Carbon::MONDAY);

        $this->pruneExpiredTeacherAttendanceHistory($retentionStart);

        $selectedWeekStart = $this->resolveSelectedWeekStart(
            $filters['week_start'] ?? null,
            $currentWeekStart,
            $retentionStart,
        );
        $selectedWeekEnd = $selectedWeekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $schedules = $this->teacherAttendanceBaseQuery($filters)
            ->whereDate('ngay_hoc', '>=', $retentionStart->toDateString())
            ->orderBy('ngay_hoc')
            ->orderBy('gio_bat_dau')
            ->get();

        $selectedWeekSchedules = $schedules->filter(function (LichHoc $schedule) use ($selectedWeekStart, $selectedWeekEnd) {
            return $schedule->ngay_hoc
                && $schedule->ngay_hoc->betweenIncluded($selectedWeekStart, $selectedWeekEnd);
        })->values();

        $pendingSchedules = $selectedWeekSchedules
            ->filter(fn (LichHoc $schedule) => $this->isTeacherAttendancePending($schedule))
            ->values();

        $completedSchedules = $selectedWeekSchedules
            ->reject(fn (LichHoc $schedule) => $this->isTeacherAttendancePending($schedule))
            ->values();

        return [
            'selected_week' => [
                'start_date' => $selectedWeekStart->toDateString(),
                'end_date' => $selectedWeekEnd->toDateString(),
                'label' => $this->buildWeekLabel($selectedWeekStart, $selectedWeekEnd),
                'is_current' => $selectedWeekStart->equalTo($currentWeekStart),
            ],
            'summary' => [
                'total' => $selectedWeekSchedules->count(),
                'pending' => $pendingSchedules->count(),
                'completed' => $completedSchedules->count(),
                'retention_days' => 31,
            ],
            'pending_schedules' => $pendingSchedules,
            'completed_schedules' => $completedSchedules,
            'history_weeks' => $this->buildTeacherHistoryWeeks($schedules, $currentWeekStart, $retentionStart, $selectedWeekStart),
            'retention_start' => $retentionStart->toDateString(),
        ];
    }

    public function teacherAttendanceReport(array $filters = []): LengthAwarePaginator
    {
        return $this->teacherAttendanceBaseQuery($filters)
            ->when(filled($filters['khoa_hoc_id'] ?? null), fn ($query) => $query->where('khoa_hoc_id', (int) $filters['khoa_hoc_id']))
            ->when(filled($filters['giang_vien_id'] ?? null), function ($query) use ($filters) {
                $teacherId = (int) $filters['giang_vien_id'];
                $query->where(function ($builder) use ($teacherId) {
                    $builder
                        ->where('giang_vien_id', $teacherId)
                        ->orWhereHas('teacherAttendanceLogs', fn ($attendanceQuery) => $attendanceQuery->where('giang_vien_id', $teacherId));
                });
            })
            ->when(filled($filters['ngay_hoc'] ?? null), fn ($query) => $query->whereDate('ngay_hoc', $filters['ngay_hoc']))
            ->when(filled($filters['trang_thai'] ?? null), function ($query) use ($filters) {
                $status = (string) $filters['trang_thai'];

                if ($status === 'chua_bat_dau') {
                    $query->whereDoesntHave('teacherAttendanceLogs');

                    return;
                }

                $mappedStatuses = match ($status) {
                    'da_checkin', 'dang_day' => ['da_checkin', 'dang_day'],
                    'da_checkout' => ['da_checkout'],
                    'hoan_thanh', 'da_ket_thuc' => ['hoan_thanh', 'da_ket_thuc'],
                    default => [$status],
                };

                $query->whereHas('teacherAttendanceLogs', fn ($attendanceQuery) => $attendanceQuery->whereIn('trang_thai', $mappedStatuses));
            })
            ->orderByDesc('ngay_hoc')
            ->orderByDesc('gio_bat_dau')
            ->paginate(12)
            ->withQueryString();
    }

    public function studentAttendanceReport(array $filters = []): LengthAwarePaginator
    {
        return DiemDanh::query()
            ->with([
                'hocVien',
                'lichHoc.khoaHoc',
                'lichHoc.giangVien.nguoiDung',
                'lichHoc.moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
            ])
            ->when(filled($filters['khoa_hoc_id'] ?? null), fn ($query) => $query->whereHas('lichHoc', fn ($scheduleQuery) => $scheduleQuery->where('khoa_hoc_id', (int) $filters['khoa_hoc_id'])))
            ->when(filled($filters['lich_hoc_id'] ?? null), fn ($query) => $query->where('lich_hoc_id', (int) $filters['lich_hoc_id']))
            ->when(filled($filters['ngay_hoc'] ?? null), fn ($query) => $query->whereHas('lichHoc', fn ($scheduleQuery) => $scheduleQuery->whereDate('ngay_hoc', $filters['ngay_hoc'])))
            ->when(filled($filters['trang_thai'] ?? null), fn ($query) => $query->where('trang_thai', (string) $filters['trang_thai']))
            ->orderByDesc(
                LichHoc::query()
                    ->select('ngay_hoc')
                    ->whereColumn('lich_hoc.id', 'diem_danh.lich_hoc_id')
                    ->limit(1)
            )
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function teacherAttendanceDetail(LichHoc $lichHoc, GiangVien $giangVien): LichHoc
    {
        return LichHoc::query()
            ->with([
                'khoaHoc',
                'moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
                'giangVien.nguoiDung',
                'teacherAttendanceLogs' => fn ($query) => $query
                    ->where('giang_vien_id', $giangVien->id)
                    ->with('giangVien.nguoiDung'),
                'baiGiangs.phongHocLive.nguoiThamGia.nguoiDung',
            ])
            ->findOrFail($lichHoc->id);
    }

    public function filterOptions(): array
    {
        return [
            'courses' => KhoaHoc::query()
                ->orderBy('ten_khoa_hoc')
                ->get(['id', 'ten_khoa_hoc', 'ma_khoa_hoc']),
            'teachers' => GiangVien::query()
                ->with('nguoiDung')
                ->whereHas('nguoiDung')
                ->orderBy('id')
                ->get(),
        ];
    }

    private function teacherAttendanceBaseQuery(array $filters = [])
    {
        return LichHoc::query()
            ->with([
                'khoaHoc',
                'moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
                'giangVien.nguoiDung',
                'teacherAttendanceLogs.giangVien.nguoiDung',
            ])
            ->when(filled($filters['khoa_hoc_id'] ?? null), fn ($query) => $query->where('khoa_hoc_id', (int) $filters['khoa_hoc_id']))
            ->when(filled($filters['giang_vien_id'] ?? null), function ($query) use ($filters) {
                $teacherId = (int) $filters['giang_vien_id'];
                $query->where(function ($builder) use ($teacherId) {
                    $builder
                        ->where('giang_vien_id', $teacherId)
                        ->orWhereHas('teacherAttendanceLogs', fn ($attendanceQuery) => $attendanceQuery->where('giang_vien_id', $teacherId))
                        ->orWhereHas('moduleHoc.phanCongGiangViens', fn ($assignmentQuery) => $assignmentQuery
                            ->where('giang_vien_id', $teacherId)
                            ->where('trang_thai', 'da_nhan'));
                });
            })
            ->when(filled($filters['trang_thai'] ?? null), function ($query) use ($filters) {
                $status = (string) $filters['trang_thai'];

                if ($status === 'chua_bat_dau') {
                    $query->whereDoesntHave('teacherAttendanceLogs');

                    return;
                }

                $mappedStatuses = match ($status) {
                    'da_checkin', 'dang_day' => ['da_checkin', 'dang_day'],
                    'da_checkout' => ['da_checkout'],
                    'hoan_thanh', 'da_ket_thuc' => ['hoan_thanh', 'da_ket_thuc'],
                    default => [$status],
                };

                $query->whereHas('teacherAttendanceLogs', fn ($attendanceQuery) => $attendanceQuery->whereIn('trang_thai', $mappedStatuses));
            });
    }

    private function pruneExpiredTeacherAttendanceHistory(Carbon $retentionStart): void
    {
        DiemDanhGiangVien::query()
            ->where(function ($query) use ($retentionStart) {
                $query
                    ->whereHas('lichHoc', fn ($scheduleQuery) => $scheduleQuery->whereDate('ngay_hoc', '<', $retentionStart->toDateString()))
                    ->orWhereDate('created_at', '<', $retentionStart->toDateString());
            })
            ->delete();
    }

    private function resolveSelectedWeekStart(?string $weekStart, Carbon $currentWeekStart, Carbon $retentionStart): Carbon
    {
        if (!filled($weekStart)) {
            return $currentWeekStart->copy();
        }

        try {
            $selected = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY);
        } catch (\Throwable) {
            return $currentWeekStart->copy();
        }

        if ($selected->lt($retentionStart)) {
            return $retentionStart->copy();
        }

        if ($selected->gt($currentWeekStart)) {
            return $currentWeekStart->copy();
        }

        return $selected;
    }

    private function isTeacherAttendancePending(LichHoc $schedule): bool
    {
        if (!$schedule->assigned_teacher) {
            return true;
        }

        $attendance = $schedule->teacher_attendance_log;

        if (!$attendance) {
            return true;
        }

        return !in_array($attendance->display_status, [
            DiemDanhGiangVien::STATUS_DA_CHECKOUT,
            DiemDanhGiangVien::STATUS_HOAN_THANH,
        ], true);
    }

    private function buildWeekLabel(Carbon $weekStart, Carbon $weekEnd): string
    {
        return 'Tuần ' . $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m/Y');
    }

    private function buildTeacherHistoryWeeks($schedules, Carbon $currentWeekStart, Carbon $retentionStart, Carbon $selectedWeekStart)
    {
        return collect(range(0, 4))
            ->map(function (int $offset) use ($currentWeekStart, $retentionStart, $schedules, $selectedWeekStart) {
                $weekStart = $currentWeekStart->copy()->subWeeks($offset);
                $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

                if ($weekStart->lt($retentionStart)) {
                    return null;
                }

                $weekSchedules = $schedules->filter(function (LichHoc $schedule) use ($weekStart, $weekEnd) {
                    return $schedule->ngay_hoc
                        && $schedule->ngay_hoc->betweenIncluded($weekStart, $weekEnd);
                })->values();

                $pending = $weekSchedules->filter(fn (LichHoc $schedule) => $this->isTeacherAttendancePending($schedule))->count();
                $completed = max(0, $weekSchedules->count() - $pending);

                return [
                    'start_date' => $weekStart->toDateString(),
                    'end_date' => $weekEnd->toDateString(),
                    'label' => $this->buildWeekLabel($weekStart, $weekEnd),
                    'is_current' => $offset === 0,
                    'is_selected' => $weekStart->equalTo($selectedWeekStart),
                    'total' => $weekSchedules->count(),
                    'pending' => $pending,
                    'completed' => $completed,
                ];
            })
            ->filter()
            ->values();
    }
}
