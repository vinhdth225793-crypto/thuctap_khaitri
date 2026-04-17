<?php

namespace App\Services;

use App\Models\BaiGiang;
use App\Models\DiemDanhGiangVien;
use App\Models\GiangVien;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;
use App\Models\PhongHocLive;
use App\Models\ThongBao;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceService
{
    public function __construct(
        private readonly TeachingSessionWindowService $windowService,
        private readonly OnlineMeetingProviderService $onlineMeetingProvider,
    ) {
    }

    public function ensureCheckIn(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        array $context = []
    ): DiemDanhGiangVien {
        $attendance = $this->findAttendance($lichHoc, $giangVien);

        if ($attendance?->thoi_gian_bat_dau_day !== null) {
            return $attendance;
        }

        return $this->checkIn($lichHoc, $giangVien, $actor, $context);
    }

    public function ensureCheckOut(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        array $context = []
    ): ?DiemDanhGiangVien {
        $attendance = $this->findAttendance($lichHoc, $giangVien);

        if (!$attendance || $attendance->thoi_gian_bat_dau_day === null) {
            return null;
        }

        if ($attendance->thoi_gian_ket_thuc_day !== null) {
            return $attendance;
        }

        return $this->checkOut($lichHoc, $giangVien, $actor, $context);
    }

    public function checkIn(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        array $context = []
    ): DiemDanhGiangVien {
        $this->ensureTeacherCanManage($lichHoc, $giangVien);
        $checkedInAt = $this->resolveTimestamp($context['checked_in_at'] ?? now());
        $this->ensureAttendanceCanCheckIn($lichHoc, $checkedInAt);

        // Phase 6: Đảm bảo có link online trước khi bắt đầu buổi học online
        $lichHoc = $this->onlineMeetingProvider->ensureOnlineLink($lichHoc);

        return DB::transaction(function () use ($lichHoc, $giangVien, $actor, $context, $checkedInAt) {
            $attendance = DiemDanhGiangVien::query()->firstOrNew([
                'lich_hoc_id' => $lichHoc->id,
                'giang_vien_id' => $giangVien->id,
            ]);

            if ($attendance->thoi_gian_bat_dau_day !== null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Buổi học này đã được giảng viên check-in trước đó.',
                ]);
            }

            [$liveStartedAt, $liveNote] = $this->resolveLiveStart($lichHoc, $giangVien, $checkedInAt, $context['room'] ?? null);
            $checkInStatus = $this->windowService->checkInStatus($lichHoc, $checkedInAt);
            $lateMinutes = $this->windowService->lateMinutes($lichHoc, $checkedInAt);

            $attendanceData = [
                'khoa_hoc_id' => $lichHoc->khoa_hoc_id,
                'module_hoc_id' => $lichHoc->module_hoc_id,
                'hinh_thuc_hoc' => (string) $lichHoc->hinh_thuc,
                'thoi_gian_bat_dau_day' => $checkedInAt,
                'thoi_gian_mo_live' => $lichHoc->hinh_thuc === 'online' ? $liveStartedAt : null,
                'trang_thai' => DiemDanhGiangVien::STATUS_DA_CHECKIN,
                'nguoi_tao_id' => $actor->ma_nguoi_dung,
                'ghi_chu' => $this->appendNotes($attendance->ghi_chu, [
                    'Check-in lúc ' . $checkedInAt->format('d/m/Y H:i'),
                    $liveNote,
                    $checkInStatus === DiemDanhGiangVien::CHECK_IN_VAO_TRE
                        ? 'Ghi nhan vao tre ' . $lateMinutes . ' phut so voi gio bat dau du kien.'
                        : null,
                    $context['note'] ?? null,
                ]),
            ];

            $this->mergeOptionalAttendanceData($attendanceData, [
                'expected_start_at' => $lichHoc->starts_at,
                'expected_end_at' => $lichHoc->ends_at,
                'check_in_status' => $checkInStatus,
                'late_minutes' => $lateMinutes > 0 ? $lateMinutes : null,
                'flag_reason' => $checkInStatus === DiemDanhGiangVien::CHECK_IN_VAO_TRE
                    ? LichHoc::TEACHER_MONITORING_VAO_TRE
                    : null,
                'flagged_at' => $checkInStatus === DiemDanhGiangVien::CHECK_IN_VAO_TRE ? $checkedInAt : null,
            ]);

            $attendance->fill($attendanceData);

            $attendance->save();
            $this->markScheduleStarted($lichHoc, $checkedInAt, $checkInStatus, $lateMinutes);

            return $attendance->fresh([
                'giangVien.nguoiDung',
                'khoaHoc',
                'moduleHoc',
                'lichHoc',
            ]);
        });
    }

    public function checkOut(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        array $context = []
    ): DiemDanhGiangVien {
        $this->ensureTeacherCanManage($lichHoc, $giangVien);
        $this->ensureAttendanceCanCheckOut($lichHoc);

        return DB::transaction(function () use ($lichHoc, $giangVien, $actor, $context) {
            $attendance = DiemDanhGiangVien::query()
                ->where('lich_hoc_id', $lichHoc->id)
                ->where('giang_vien_id', $giangVien->id)
                ->first();

            if (!$attendance || $attendance->thoi_gian_bat_dau_day === null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Bạn cần check-in trước khi check-out buổi học này.',
                ]);
            }

            if ($attendance->thoi_gian_ket_thuc_day !== null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Buổi học này đã được giảng viên check-out trước đó.',
                ]);
            }

            $checkedOutAt = $this->resolveTimestamp($context['checked_out_at'] ?? now());
            [$liveEndedAt, $liveNote] = $this->resolveLiveEnd($lichHoc, $giangVien, $checkedOutAt, $context['room'] ?? null);
            $teachingMinutes = max(0, $attendance->thoi_gian_bat_dau_day->diffInMinutes($checkedOutAt));

            $checkOutStatus = $this->windowService->checkOutStatus($lichHoc, $checkedOutAt);
            $earlyLeaveMinutes = $this->windowService->earlyLeaveMinutes($lichHoc, $checkedOutAt);

            $attendanceData = [
                'thoi_gian_ket_thuc_day' => $checkedOutAt,
                'thoi_gian_tat_live' => $lichHoc->hinh_thuc === 'online' ? $liveEndedAt : null,
                'tong_thoi_luong_day_phut' => $teachingMinutes,
                'trang_thai' => DiemDanhGiangVien::STATUS_HOAN_THANH,
                'nguoi_tao_id' => $actor->ma_nguoi_dung,
                'ghi_chu' => $this->appendNotes($attendance->ghi_chu, [
                    'Check-out lúc ' . $checkedOutAt->format('d/m/Y H:i'),
                    'Tổng thời lượng giảng dạy: ' . $teachingMinutes . ' phút',
                    $checkOutStatus === DiemDanhGiangVien::CHECK_OUT_DONG_SOM
                        ? 'Ghi nhận đóng buổi sớm ' . $earlyLeaveMinutes . ' phút so với giờ kết thúc dự kiến.'
                        : null,
                    $liveNote,
                    $context['note'] ?? null,
                ]),
            ];

            $this->mergeOptionalAttendanceData($attendanceData, [
                'check_out_status' => $checkOutStatus,
                'early_leave_minutes' => $earlyLeaveMinutes > 0 ? $earlyLeaveMinutes : null,
            ]);

            if ($checkOutStatus === DiemDanhGiangVien::CHECK_OUT_DONG_SOM && Schema::hasColumn('diem_danh_giang_vien', 'flag_reason')) {
                $attendanceData['flag_reason'] = LichHoc::TEACHER_MONITORING_DONG_SOM;
                $attendanceData['flagged_at'] = $checkedOutAt;
            }

            $attendance->fill($attendanceData);

            if ($lichHoc->hinh_thuc === 'online' && $attendance->thoi_gian_mo_live === null) {
                [$liveStartedAt] = $this->resolveLiveStart($lichHoc, $giangVien, $attendance->thoi_gian_bat_dau_day, $context['room'] ?? null);
                $attendance->thoi_gian_mo_live = $liveStartedAt;
            }

            $attendance->save();
            $this->markScheduleFinished($lichHoc, $checkedOutAt, $checkOutStatus, $earlyLeaveMinutes);

            // Cập nhật lại tổng số giờ dạy của giảng viên
            $giangVien->recalculateTeachingHours();

            return $attendance->fresh([
                'giangVien.nguoiDung',
                'khoaHoc',
                'moduleHoc',
                'lichHoc',
            ]);
        });
    }

    public function ensureCheckInFromRoom(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        ?PhongHocLive $room = null
    ): DiemDanhGiangVien {
        $attendance = $this->findAttendance($lichHoc, $giangVien);

        if ($attendance?->thoi_gian_bat_dau_day !== null) {
            return $attendance;
        }

        return $this->ensureCheckIn($lichHoc, $giangVien, $actor, [
            'room' => $room,
            'note' => 'Tự động check-in khi giảng viên vào phòng live nội bộ.',
        ]);
    }

    public function ensureCheckOutFromRoom(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        ?PhongHocLive $room = null
    ): ?DiemDanhGiangVien {
        $attendance = $this->findAttendance($lichHoc, $giangVien);

        if (!$attendance || $attendance->thoi_gian_bat_dau_day === null) {
            return null;
        }

        if ($attendance->thoi_gian_ket_thuc_day !== null) {
            return $attendance;
        }

        return $this->ensureCheckOut($lichHoc, $giangVien, $actor, [
            'room' => $room,
            'note' => 'Tự động check-out khi giảng viên kết thúc phòng live nội bộ.',
        ]);
    }

    public function startTeaching(LichHoc $lichHoc, GiangVien $giangVien, NguoiDung $actor): DiemDanhGiangVien
    {
        return $this->ensureCheckIn($lichHoc, $giangVien, $actor);
    }

    public function finishTeaching(LichHoc $lichHoc, GiangVien $giangVien, NguoiDung $actor): DiemDanhGiangVien
    {
        return $this->checkOut($lichHoc, $giangVien, $actor);
    }

    public function ensureTeacherCanManage(LichHoc $lichHoc, GiangVien $giangVien): void
    {
        $matchesDirectTeacher = $lichHoc->giang_vien_id !== null
            && (int) $lichHoc->giang_vien_id === (int) $giangVien->id;

        if ($matchesDirectTeacher) {
            return;
        }

        $isAssigned = PhanCongModuleGiangVien::query()
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        if ($isAssigned) {
            return;
        }

        throw ValidationException::withMessages([
            'teacher_attendance' => 'Bạn không được phân công giảng dạy buổi học này.',
        ]);
    }

    public function findAttendance(LichHoc $lichHoc, GiangVien $giangVien): ?DiemDanhGiangVien
    {
        return DiemDanhGiangVien::query()
            ->where('lich_hoc_id', $lichHoc->id)
            ->where('giang_vien_id', $giangVien->id)
            ->first();
    }

    private function ensureAttendanceCanCheckIn(LichHoc $lichHoc, Carbon $checkedInAt): void
    {
        if ($lichHoc->teaching_session_status === 'da_ket_thuc') {
            throw ValidationException::withMessages([
                'teacher_attendance' => 'Buổi học này đã kết thúc nên không thể check-in thêm.',
            ]);
        }

        if ($lichHoc->teaching_session_status === 'da_huy') {
            throw ValidationException::withMessages([
                'teacher_attendance' => 'Buổi học này đã bị hủy nên không thể thực hiện điểm danh.',
            ]);
        }

        if (! $this->windowService->isInsideStartWindow($lichHoc, $checkedInAt)) {
            throw ValidationException::withMessages([
                'teacher_attendance' => $this->buildCheckInWindowMessage($lichHoc, $checkedInAt),
            ]);
        }
    }

    private function ensureAttendanceCanCheckOut(LichHoc $lichHoc): void
    {
        if ($lichHoc->teaching_session_status === 'da_huy') {
            throw ValidationException::withMessages([
                'teacher_attendance' => 'Buổi học này đã bị hủy nên không thể thực hiện điểm danh.',
            ]);
        }

        if ($this->windowService->isCheckoutOverdue($lichHoc)) {
            $deadlineAt = $this->windowService->teacherCheckoutDeadlineAt($lichHoc);
            throw ValidationException::withMessages([
                'teacher_attendance' => 'Đã quá hạn check-out buổi học này lúc ' . ($deadlineAt ? $deadlineAt->format('d/m/Y H:i') : 'N/A') . '.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attendanceData
     * @param  array<string, mixed>  $optionalData
     */
    private function mergeOptionalAttendanceData(array &$attendanceData, array $optionalData): void
    {
        foreach ($optionalData as $column => $value) {
            if (Schema::hasColumn('diem_danh_giang_vien', $column)) {
                $attendanceData[$column] = $value;
            }
        }
    }

    private function markScheduleStarted(
        LichHoc $lichHoc,
        Carbon $checkedInAt,
        string $checkInStatus,
        int $lateMinutes
    ): void {
        $payload = [
            'trang_thai' => 'dang_hoc',
        ];

        if (Schema::hasColumn('lich_hoc', 'actual_started_at') && blank($lichHoc->actual_started_at)) {
            $payload['actual_started_at'] = $checkedInAt;
        }

        if ($checkInStatus === DiemDanhGiangVien::CHECK_IN_VAO_TRE) {
            if (Schema::hasColumn('lich_hoc', 'teacher_monitoring_status')) {
                $payload['teacher_monitoring_status'] = LichHoc::TEACHER_MONITORING_VAO_TRE;
            }

            if (Schema::hasColumn('lich_hoc', 'teacher_monitoring_note')) {
                $payload['teacher_monitoring_note'] = trim(implode(PHP_EOL, array_filter([
                    $lichHoc->teacher_monitoring_note,
                    'Giao vien check-in tre ' . $lateMinutes . ' phut luc ' . $checkedInAt->format('d/m/Y H:i') . '.',
                ])));
            }

            if (Schema::hasColumn('lich_hoc', 'teacher_monitoring_flagged_at')) {
                $payload['teacher_monitoring_flagged_at'] = $checkedInAt;
            }
        }

        $lichHoc->forceFill($payload)->save();
    }

    private function markScheduleFinished(
        LichHoc $lichHoc,
        Carbon $checkedOutAt,
        string $checkOutStatus,
        int $earlyLeaveMinutes
    ): void {
        $payload = [
            'trang_thai' => 'hoan_thanh',
        ];

        if (Schema::hasColumn('lich_hoc', 'actual_finished_at')) {
            $payload['actual_finished_at'] = $checkedOutAt;
        }

        // Thiết lập deadline điểm danh (15 phút sau khi kết thúc)
        $remindMinutes = $lichHoc->attendance_remind_after_finish_minutes ?? LichHoc::DEFAULT_ATTENDANCE_REMIND_AFTER_FINISH_MINUTES;
        $deadlineAt = $checkedOutAt->copy()->addMinutes($remindMinutes);
        
        if (Schema::hasColumn('lich_hoc', 'attendance_deadline_at')) {
            $payload['attendance_deadline_at'] = $deadlineAt;
        }

        if ($checkOutStatus === DiemDanhGiangVien::CHECK_OUT_DONG_SOM) {
            if (Schema::hasColumn('lich_hoc', 'teacher_monitoring_status')) {
                $payload['teacher_monitoring_status'] = LichHoc::TEACHER_MONITORING_DONG_SOM;
            }

            if (Schema::hasColumn('lich_hoc', 'teacher_monitoring_note')) {
                $payload['teacher_monitoring_note'] = trim(implode(PHP_EOL, array_filter([
                    $lichHoc->teacher_monitoring_note,
                    'Giao vien dong buoi som ' . $earlyLeaveMinutes . ' phut luc ' . $checkedOutAt->format('d/m/Y H:i') . '.',
                ])));
            }

            if (Schema::hasColumn('lich_hoc', 'teacher_monitoring_flagged_at')) {
                $payload['teacher_monitoring_flagged_at'] = $checkedOutAt;
            }
        }

        $lichHoc->forceFill($payload)->save();

        // Gửi thông báo nhắc giảng viên điểm danh
        $this->notifyTeacherToAttendance($lichHoc, $deadlineAt);
    }

    private function notifyTeacherToAttendance(LichHoc $lichHoc, Carbon $deadlineAt): void
    {
        if (!$lichHoc->giang_vien_id) {
            return;
        }

        $teacher = $lichHoc->giangVien;
        if (!$teacher || !$teacher->nguoi_dung_id) {
            return;
        }

        ThongBao::create([
            'nguoi_nhan_id' => $teacher->nguoi_dung_id,
            'tieu_de' => 'Nhắc nhở: Điểm danh học viên',
            'noi_dung' => "Buổi học đã kết thúc. Vui lòng hoàn tất điểm danh học viên và chốt báo cáo trước " . $deadlineAt->format('H:i d/m/Y') . " (hạn 15 phút).",
            'loai' => 'he_thong',
            'url' => route('giang-vien.khoa-hoc.show', ['id' => $lichHoc->khoa_hoc_id, 'focus_lich_hoc_id' => $lichHoc->id]),
            'da_doc' => false,
        ]);
    }

    private function buildCheckInWindowMessage(LichHoc $lichHoc, Carbon $checkedInAt): string
    {
        $openAt = $this->windowService->teacherOpenWindowStartsAt($lichHoc);
        $deadlineAt = $this->windowService->teacherCheckoutDeadlineAt($lichHoc);

        if ($openAt && $checkedInAt->lt($openAt)) {
            return 'Chi duoc check-in hoac bat dau buoi hoc tu ' . $openAt->format('d/m/Y H:i') . '.';
        }

        if ($deadlineAt && $checkedInAt->gt($deadlineAt)) {
            return 'Da qua han check-in/bat dau buoi hoc luc ' . $deadlineAt->format('d/m/Y H:i') . '.';
        }

        return 'Thoi diem check-in/bat dau khong nam trong khung cho phep cua buoi hoc.';
    }

    private function resolveLiveStart(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        Carbon $fallbackAt,
        ?PhongHocLive $room = null
    ): array {
        if ($lichHoc->hinh_thuc !== 'online') {
            return [null, 'Buổi học trực tiếp không sử dụng room live nội bộ.'];
        }

        $room = $room ?: $this->resolveLinkedLiveRoom($lichHoc);

        if (!$room) {
            if (filled($lichHoc->link_online)) {
                return [$fallbackAt, 'Dùng mốc check-in làm thời điểm mở live vì buổi học chỉ có link online bên ngoài.'];
            }

            return [null, 'Buổi học online chưa có room live nội bộ, hệ thống chỉ ghi nhận giờ check-in.'];
        }

        $participant = $this->resolveTeacherParticipant($room, $giangVien);
        $startedAt = $participant?->joined_at ?? $fallbackAt;

        return [$startedAt, 'Đồng bộ giờ mở live từ phòng ' . $room->platform_label . '.'];
    }

    private function resolveLiveEnd(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        Carbon $fallbackAt,
        ?PhongHocLive $room = null
    ): array {
        if ($lichHoc->hinh_thuc !== 'online') {
            return [null, 'Buổi học trực tiếp không có room live cần đồng bộ.'];
        }

        $room = $room ?: $this->resolveLinkedLiveRoom($lichHoc);

        if (!$room) {
            if (filled($lichHoc->link_online)) {
                return [$fallbackAt, 'Dùng mốc check-out làm thời điểm tắt live vì buổi học chỉ có link online bên ngoài.'];
            }

            return [null, 'Buổi học online chưa có room live nội bộ nên hệ thống chỉ ghi nhận giờ check-out.'];
        }

        $participant = $this->resolveTeacherParticipant($room, $giangVien);
        $endedAt = $participant?->left_at ?? $fallbackAt;

        return [$endedAt, 'Đồng bộ giờ tắt live từ phòng ' . $room->platform_label . '.'];
    }

    private function resolveLinkedLiveRoom(LichHoc $lichHoc): ?PhongHocLive
    {
        if ($lichHoc->relationLoaded('baiGiangs')) {
            $internalLecture = $lichHoc->baiGiangs
                ->filter(fn (BaiGiang $lecture) => $lecture->isLive() && $lecture->phongHocLive)
                ->sortByDesc(function (BaiGiang $lecture) {
                    $priority = $lecture->phongHocLive?->nen_tang_live === PhongHocLive::PLATFORM_INTERNAL ? 1 : 0;

                    return sprintf('%d-%010d', $priority, $lecture->id);
                })
                ->first();

            return $internalLecture?->phongHocLive;
        }

        $lecture = $lichHoc->baiGiangs()
            ->where('loai_bai_giang', BaiGiang::TYPE_LIVE)
            ->with($this->hasLiveParticipantsTable()
                ? ['phongHocLive.nguoiThamGia']
                : ['phongHocLive'])
            ->latest('id')
            ->first();

        if (! $this->hasLiveParticipantsTable() && $lecture?->phongHocLive) {
            $lecture->phongHocLive->setRelation('nguoiThamGia', new EloquentCollection());
        }

        return $lecture?->phongHocLive;
    }

    private function resolveTeacherParticipant(PhongHocLive $room, GiangVien $giangVien)
    {
        if (! $this->hasLiveParticipantsTable()) {
            if (! $room->relationLoaded('nguoiThamGia')) {
                $room->setRelation('nguoiThamGia', new EloquentCollection());
            }

            return null;
        }

        $room->loadMissing('nguoiThamGia');

        return $room->nguoiThamGia
            ->where('nguoi_dung_id', (int) $giangVien->nguoi_dung_id)
            ->sortByDesc(function ($participant) {
                return $participant->joined_at?->timestamp ?? $participant->created_at?->timestamp ?? 0;
            })
            ->first();
    }

    private function hasLiveParticipantsTable(): bool
    {
        return Schema::hasTable('phong_hoc_live_nguoi_tham_gia');
    }

    private function appendNotes(?string $existingNotes, array $lines): string
    {
        $merged = array_filter([
            trim((string) $existingNotes) !== '' ? trim((string) $existingNotes) : null,
            ...array_map(fn ($line) => filled($line) ? trim((string) $line) : null, $lines),
        ]);

        return trim(implode(PHP_EOL, $merged));
    }

    private function resolveTimestamp(Carbon|string $value): Carbon
    {
        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }
}
