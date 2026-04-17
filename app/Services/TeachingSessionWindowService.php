<?php

namespace App\Services;

use App\Models\DiemDanhGiangVien;
use App\Models\LichHoc;
use Illuminate\Support\Carbon;

class TeachingSessionWindowService
{
    public const EARLY_FINISH_THRESHOLD_MINUTES = 30;

    public function teacherOpenWindowStartsAt(LichHoc $schedule): ?Carbon
    {
        return $schedule->starts_at?->copy()->subMinutes($this->openBeforeMinutes($schedule));
    }

    public function teacherCheckoutDeadlineAt(LichHoc $schedule): ?Carbon
    {
        return $schedule->ends_at?->copy()->addMinutes($this->closeAfterMinutes($schedule));
    }

    public function teacherEarlyFinishThresholdAt(LichHoc $schedule): ?Carbon
    {
        return $schedule->ends_at?->copy()->subMinutes(self::EARLY_FINISH_THRESHOLD_MINUTES);
    }

    public function attendanceDeadlineAt(LichHoc $schedule): ?Carbon
    {
        if ($schedule->attendance_deadline_at) {
            return $schedule->attendance_deadline_at;
        }

        $finishedAt = $schedule->actual_finished_at;

        return $finishedAt?->copy()->addMinutes($this->attendanceRemindAfterFinishMinutes($schedule));
    }

    public function studentJoinWindowStartsAt(LichHoc $schedule): ?Carbon
    {
        return $schedule->starts_at?->copy()->subMinutes(LichHoc::ONLINE_JOIN_EARLY_MINUTES);
    }

    public function canStudentJoinOnline(LichHoc $schedule, ?Carbon $at = null): bool
    {
        $startsAt = $this->studentJoinWindowStartsAt($schedule);
        $endsAt = $schedule->ends_at;

        if (! $startsAt || ! $endsAt) {
            return $schedule->trang_thai === 'dang_hoc';
        }

        $at ??= now();

        return $at->greaterThanOrEqualTo($startsAt)
            && $at->lessThanOrEqualTo($endsAt);
    }

    public function canStart(LichHoc $schedule, ?Carbon $at = null): bool
    {
        if ($schedule->teaching_session_status !== 'chua_bat_dau') {
            return false;
        }

        $windowStartsAt = $this->teacherOpenWindowStartsAt($schedule);
        $deadlineAt = $this->teacherCheckoutDeadlineAt($schedule);

        if (! $windowStartsAt || ! $deadlineAt) {
            return true;
        }

        $at ??= now();

        return $at->greaterThanOrEqualTo($windowStartsAt)
            && $at->lessThanOrEqualTo($deadlineAt);
    }

    public function canFinish(LichHoc $schedule, ?Carbon $at = null): bool
    {
        if ($schedule->teaching_session_status !== 'dang_dien_ra') {
            return false;
        }

        $deadlineAt = $this->teacherCheckoutDeadlineAt($schedule);

        if (! $deadlineAt) {
            return true;
        }

        $at ??= now();

        return $at->lessThanOrEqualTo($deadlineAt);
    }

    public function checkInStatus(LichHoc $schedule, ?Carbon $at = null): string
    {
        $at ??= now();

        if (! $this->isInsideStartWindow($schedule, $at)) {
            return DiemDanhGiangVien::CHECK_IN_NGOAI_KHUNG;
        }

        if ($this->isLateCheckIn($schedule, $at)) {
            return DiemDanhGiangVien::CHECK_IN_VAO_TRE;
        }

        return DiemDanhGiangVien::CHECK_IN_DUNG_GIO;
    }

    public function checkOutStatus(LichHoc $schedule, ?Carbon $at = null): string
    {
        $at ??= now();

        if ($this->isCheckoutOverdue($schedule, $at)) {
            return DiemDanhGiangVien::CHECK_OUT_QUA_HAN;
        }

        if ($this->isEarlyFinish($schedule, $at)) {
            return DiemDanhGiangVien::CHECK_OUT_DONG_SOM;
        }

        return DiemDanhGiangVien::CHECK_OUT_DUNG_HAN;
    }

    public function lateMinutes(LichHoc $schedule, ?Carbon $at = null): int
    {
        $startsAt = $schedule->starts_at;
        $at ??= now();

        if (! $startsAt || $at->lessThanOrEqualTo($startsAt)) {
            return 0;
        }

        return max(0, (int) $startsAt->diffInMinutes($at, false));
    }

    public function earlyLeaveMinutes(LichHoc $schedule, ?Carbon $at = null): int
    {
        $endsAt = $schedule->ends_at;
        $at ??= now();

        if (! $endsAt || $at->greaterThanOrEqualTo($endsAt)) {
            return 0;
        }

        return max(0, (int) $at->diffInMinutes($endsAt, false));
    }

    public function isLateCheckIn(LichHoc $schedule, ?Carbon $at = null): bool
    {
        $startsAt = $schedule->starts_at;
        $at ??= now();

        return $startsAt !== null && $at->greaterThan($startsAt);
    }

    public function isEarlyFinish(LichHoc $schedule, ?Carbon $at = null): bool
    {
        $thresholdAt = $this->teacherEarlyFinishThresholdAt($schedule);
        $at ??= now();

        return $thresholdAt !== null && $at->lessThan($thresholdAt);
    }

    public function isCheckoutOverdue(LichHoc $schedule, ?Carbon $at = null): bool
    {
        $deadlineAt = $this->teacherCheckoutDeadlineAt($schedule);
        $at ??= now();

        return $deadlineAt !== null && $at->greaterThan($deadlineAt);
    }

    public function shouldFlagNoShow(LichHoc $schedule, bool $hasCheckedIn, ?Carbon $at = null): bool
    {
        return ! $hasCheckedIn && $this->isCheckoutOverdue($schedule, $at);
    }

    public function shouldFlagMissingCheckout(LichHoc $schedule, bool $hasCheckedOut, ?Carbon $at = null): bool
    {
        return ! $hasCheckedOut && $schedule->teaching_session_status === 'dang_dien_ra'
            && $this->isCheckoutOverdue($schedule, $at);
    }

    public function windows(LichHoc $schedule): array
    {
        return [
            'teacher_open_window_starts_at' => $this->teacherOpenWindowStartsAt($schedule),
            'teacher_checkout_deadline' => $this->teacherCheckoutDeadlineAt($schedule),
            'teacher_early_finish_threshold' => $this->teacherEarlyFinishThresholdAt($schedule),
            'student_join_window_starts_at' => $this->studentJoinWindowStartsAt($schedule),
            'attendance_deadline_at' => $this->attendanceDeadlineAt($schedule),
            'allow_open_before_minutes' => $this->openBeforeMinutes($schedule),
            'allow_close_after_minutes' => $this->closeAfterMinutes($schedule),
            'attendance_remind_after_finish_minutes' => $this->attendanceRemindAfterFinishMinutes($schedule),
        ];
    }

    public function isInsideStartWindow(LichHoc $schedule, ?Carbon $at = null): bool
    {
        $windowStartsAt = $this->teacherOpenWindowStartsAt($schedule);
        $deadlineAt = $this->teacherCheckoutDeadlineAt($schedule);

        if (! $windowStartsAt || ! $deadlineAt) {
            return true;
        }

        $at ??= now();

        return $at->greaterThanOrEqualTo($windowStartsAt)
            && $at->lessThanOrEqualTo($deadlineAt);
    }

    private function openBeforeMinutes(LichHoc $schedule): int
    {
        return max(0, (int) ($schedule->allow_open_before_minutes ?? LichHoc::DEFAULT_TEACHER_OPEN_BEFORE_MINUTES));
    }

    private function closeAfterMinutes(LichHoc $schedule): int
    {
        return max(0, (int) ($schedule->allow_close_after_minutes ?? LichHoc::DEFAULT_TEACHER_CLOSE_AFTER_MINUTES));
    }

    private function attendanceRemindAfterFinishMinutes(LichHoc $schedule): int
    {
        return max(0, (int) ($schedule->attendance_remind_after_finish_minutes ?? LichHoc::DEFAULT_ATTENDANCE_REMIND_AFTER_FINISH_MINUTES));
    }
}
