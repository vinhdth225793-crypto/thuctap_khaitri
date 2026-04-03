<?php

namespace App\Services;

use App\Models\BaiGiang;
use App\Models\DiemDanhGiangVien;
use App\Models\GiangVien;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;
use App\Models\PhongHocLive;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceService
{
    public function startTeaching(LichHoc $lichHoc, GiangVien $giangVien, NguoiDung $actor): DiemDanhGiangVien
    {
        $this->ensureTeacherCanManage($lichHoc, $giangVien);
        $this->ensureOnlineSchedule($lichHoc);

        return DB::transaction(function () use ($lichHoc, $giangVien, $actor) {
            $attendance = DiemDanhGiangVien::query()->firstOrNew([
                'lich_hoc_id' => $lichHoc->id,
                'giang_vien_id' => $giangVien->id,
            ]);

            if ($attendance->thoi_gian_bat_dau_day !== null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Buổi học này đã được xác nhận bắt đầu trước đó.',
                ]);
            }

            $startedAt = now();
            [$liveStartedAt, $liveNote] = $this->resolveLiveStart($lichHoc, $giangVien, $startedAt);

            $attendance->fill([
                'khoa_hoc_id' => $lichHoc->khoa_hoc_id,
                'module_hoc_id' => $lichHoc->module_hoc_id,
                'hinh_thuc_hoc' => (string) $lichHoc->hinh_thuc,
                'thoi_gian_bat_dau_day' => $startedAt,
                'thoi_gian_mo_live' => $liveStartedAt,
                'trang_thai' => 'dang_day',
                'nguoi_tao_id' => $actor->ma_nguoi_dung,
                'ghi_chu' => $this->appendNotes($attendance->ghi_chu, [
                    'Bắt đầu buổi học lúc ' . $startedAt->format('d/m/Y H:i'),
                    $liveNote,
                ]),
            ]);

            $attendance->save();

            return $attendance->fresh([
                'giangVien.nguoiDung',
                'khoaHoc',
                'moduleHoc',
                'lichHoc',
            ]);
        });
    }

    public function finishTeaching(LichHoc $lichHoc, GiangVien $giangVien, NguoiDung $actor): DiemDanhGiangVien
    {
        $this->ensureTeacherCanManage($lichHoc, $giangVien);
        $this->ensureOnlineSchedule($lichHoc);

        return DB::transaction(function () use ($lichHoc, $giangVien, $actor) {
            $attendance = DiemDanhGiangVien::query()
                ->where('lich_hoc_id', $lichHoc->id)
                ->where('giang_vien_id', $giangVien->id)
                ->first();

            if (!$attendance || $attendance->thoi_gian_bat_dau_day === null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Bạn cần bắt đầu buổi học trước khi xác nhận kết thúc.',
                ]);
            }

            if ($attendance->thoi_gian_ket_thuc_day !== null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Buổi học này đã được xác nhận kết thúc trước đó.',
                ]);
            }

            $endedAt = now();
            [$liveEndedAt, $liveNote] = $this->resolveLiveEnd($lichHoc, $giangVien, $endedAt);
            $teachingMinutes = max(0, $attendance->thoi_gian_bat_dau_day->diffInMinutes($endedAt));

            $attendance->fill([
                'thoi_gian_ket_thuc_day' => $endedAt,
                'thoi_gian_tat_live' => $liveEndedAt ?? $endedAt,
                'tong_thoi_luong_day_phut' => $teachingMinutes,
                'trang_thai' => 'da_ket_thuc',
                'nguoi_tao_id' => $actor->ma_nguoi_dung,
                'ghi_chu' => $this->appendNotes($attendance->ghi_chu, [
                    'Kết thúc buổi học lúc ' . $endedAt->format('d/m/Y H:i'),
                    'Thời lượng giảng dạy thực tế: ' . $teachingMinutes . ' phút',
                    $liveNote,
                ]),
            ]);

            if ($attendance->thoi_gian_mo_live === null) {
                [$liveStartedAt] = $this->resolveLiveStart($lichHoc, $giangVien, $attendance->thoi_gian_bat_dau_day);
                $attendance->thoi_gian_mo_live = $liveStartedAt;
            }

            $attendance->save();

            return $attendance->fresh([
                'giangVien.nguoiDung',
                'khoaHoc',
                'moduleHoc',
                'lichHoc',
            ]);
        });
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

    public function ensureOnlineSchedule(LichHoc $lichHoc): void
    {
        if ($lichHoc->hinh_thuc === 'online') {
            return;
        }

        throw ValidationException::withMessages([
            'teacher_attendance' => 'Điểm danh giảng viên chỉ áp dụng cho buổi học online.',
        ]);
    }

    private function resolveLiveStart(LichHoc $lichHoc, GiangVien $giangVien, Carbon $fallbackAt): array
    {
        $room = $this->resolveLinkedLiveRoom($lichHoc);

        if (!$room) {
            if (filled($lichHoc->link_online)) {
                return [$fallbackAt, 'Chưa có phòng live nội bộ, dùng thời điểm bắt đầu dạy làm giờ mở live.'];
            }

            return [null, 'Buổi online chưa gắn phòng live nội bộ nên chỉ ghi nhận giờ bắt đầu dạy.'];
        }

        $participant = $this->resolveTeacherParticipant($room, $giangVien);
        $startedAt = $participant?->joined_at ?? ($room->isDangDienRa() ? $fallbackAt : $fallbackAt);

        return [$startedAt, 'Đồng bộ giờ mở live từ phòng ' . $room->platform_label . '.'];
    }

    private function resolveLiveEnd(LichHoc $lichHoc, GiangVien $giangVien, Carbon $fallbackAt): array
    {
        $room = $this->resolveLinkedLiveRoom($lichHoc);

        if (!$room) {
            return [filled($lichHoc->link_online) ? $fallbackAt : null, 'Dùng thời điểm giảng viên xác nhận kết thúc làm giờ tắt live.'];
        }

        $participant = $this->resolveTeacherParticipant($room, $giangVien);
        $endedAt = $participant?->left_at
            ?? ($room->trang_thai_phong === PhongHocLive::ROOM_STATE_DA_KET_THUC ? $fallbackAt : $fallbackAt);

        return [$endedAt, 'Đồng bộ giờ tắt live từ phòng ' . $room->platform_label . '.'];
    }

    private function resolveLinkedLiveRoom(LichHoc $lichHoc): ?PhongHocLive
    {
        $lecture = $lichHoc->baiGiangs()
            ->where('loai_bai_giang', BaiGiang::TYPE_LIVE)
            ->with(['phongHocLive.nguoiThamGia'])
            ->latest('id')
            ->first();

        return $lecture?->phongHocLive;
    }

    private function resolveTeacherParticipant(PhongHocLive $room, GiangVien $giangVien)
    {
        return $room->nguoiThamGia
            ->where('nguoi_dung_id', (int) $giangVien->nguoi_dung_id)
            ->sortByDesc(function ($participant) {
                return $participant->joined_at?->timestamp ?? $participant->created_at?->timestamp ?? 0;
            })
            ->first();
    }

    private function appendNotes(?string $existingNotes, array $lines): string
    {
        $merged = array_filter([
            trim((string) $existingNotes) !== '' ? trim((string) $existingNotes) : null,
            ...array_map(fn ($line) => filled($line) ? trim((string) $line) : null, $lines),
        ]);

        return trim(implode(PHP_EOL, $merged));
    }
}
