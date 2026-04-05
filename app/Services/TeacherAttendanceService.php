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
    public function checkIn(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        array $context = []
    ): DiemDanhGiangVien {
        $this->ensureTeacherCanManage($lichHoc, $giangVien);

        return DB::transaction(function () use ($lichHoc, $giangVien, $actor, $context) {
            $attendance = DiemDanhGiangVien::query()->firstOrNew([
                'lich_hoc_id' => $lichHoc->id,
                'giang_vien_id' => $giangVien->id,
            ]);

            if ($attendance->thoi_gian_bat_dau_day !== null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Buoi hoc nay da duoc giang vien check-in truoc do.',
                ]);
            }

            $checkedInAt = $this->resolveTimestamp($context['checked_in_at'] ?? now());
            [$liveStartedAt, $liveNote] = $this->resolveLiveStart($lichHoc, $giangVien, $checkedInAt, $context['room'] ?? null);

            $attendance->fill([
                'khoa_hoc_id' => $lichHoc->khoa_hoc_id,
                'module_hoc_id' => $lichHoc->module_hoc_id,
                'hinh_thuc_hoc' => (string) $lichHoc->hinh_thuc,
                'thoi_gian_bat_dau_day' => $checkedInAt,
                'thoi_gian_mo_live' => $lichHoc->hinh_thuc === 'online' ? $liveStartedAt : null,
                'trang_thai' => DiemDanhGiangVien::STATUS_DA_CHECKIN,
                'nguoi_tao_id' => $actor->ma_nguoi_dung,
                'ghi_chu' => $this->appendNotes($attendance->ghi_chu, [
                    'Check-in luc ' . $checkedInAt->format('d/m/Y H:i'),
                    $liveNote,
                    $context['note'] ?? null,
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

    public function checkOut(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        NguoiDung $actor,
        array $context = []
    ): DiemDanhGiangVien {
        $this->ensureTeacherCanManage($lichHoc, $giangVien);

        return DB::transaction(function () use ($lichHoc, $giangVien, $actor, $context) {
            $attendance = DiemDanhGiangVien::query()
                ->where('lich_hoc_id', $lichHoc->id)
                ->where('giang_vien_id', $giangVien->id)
                ->first();

            if (!$attendance || $attendance->thoi_gian_bat_dau_day === null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Ban can check-in truoc khi check-out buoi hoc nay.',
                ]);
            }

            if ($attendance->thoi_gian_ket_thuc_day !== null) {
                throw ValidationException::withMessages([
                    'teacher_attendance' => 'Buoi hoc nay da duoc giang vien check-out truoc do.',
                ]);
            }

            $checkedOutAt = $this->resolveTimestamp($context['checked_out_at'] ?? now());
            [$liveEndedAt, $liveNote] = $this->resolveLiveEnd($lichHoc, $giangVien, $checkedOutAt, $context['room'] ?? null);
            $teachingMinutes = max(0, $attendance->thoi_gian_bat_dau_day->diffInMinutes($checkedOutAt));

            $attendance->fill([
                'thoi_gian_ket_thuc_day' => $checkedOutAt,
                'thoi_gian_tat_live' => $lichHoc->hinh_thuc === 'online' ? $liveEndedAt : null,
                'tong_thoi_luong_day_phut' => $teachingMinutes,
                'trang_thai' => DiemDanhGiangVien::STATUS_HOAN_THANH,
                'nguoi_tao_id' => $actor->ma_nguoi_dung,
                'ghi_chu' => $this->appendNotes($attendance->ghi_chu, [
                    'Check-out luc ' . $checkedOutAt->format('d/m/Y H:i'),
                    'Tong thoi luong giang day: ' . $teachingMinutes . ' phut',
                    $liveNote,
                    $context['note'] ?? null,
                ]),
            ]);

            if ($lichHoc->hinh_thuc === 'online' && $attendance->thoi_gian_mo_live === null) {
                [$liveStartedAt] = $this->resolveLiveStart($lichHoc, $giangVien, $attendance->thoi_gian_bat_dau_day, $context['room'] ?? null);
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

        return $this->checkIn($lichHoc, $giangVien, $actor, [
            'room' => $room,
            'note' => 'Tu dong check-in khi giang vien vao phong live noi bo.',
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

        return $this->checkOut($lichHoc, $giangVien, $actor, [
            'room' => $room,
            'note' => 'Tu dong check-out khi giang vien ket thuc phong live noi bo.',
        ]);
    }

    public function startTeaching(LichHoc $lichHoc, GiangVien $giangVien, NguoiDung $actor): DiemDanhGiangVien
    {
        return $this->checkIn($lichHoc, $giangVien, $actor);
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
            'teacher_attendance' => 'Ban khong duoc phan cong giang day buoi hoc nay.',
        ]);
    }

    public function findAttendance(LichHoc $lichHoc, GiangVien $giangVien): ?DiemDanhGiangVien
    {
        return DiemDanhGiangVien::query()
            ->where('lich_hoc_id', $lichHoc->id)
            ->where('giang_vien_id', $giangVien->id)
            ->first();
    }

    private function resolveLiveStart(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        Carbon $fallbackAt,
        ?PhongHocLive $room = null
    ): array {
        if ($lichHoc->hinh_thuc !== 'online') {
            return [null, 'Buoi hoc truc tiep khong su dung room live noi bo.'];
        }

        $room = $room ?: $this->resolveLinkedLiveRoom($lichHoc);

        if (!$room) {
            if (filled($lichHoc->link_online)) {
                return [$fallbackAt, 'Su dung moc check-in lam thoi diem mo live vi buoi hoc chi co link online ben ngoai.'];
            }

            return [null, 'Buoi hoc online chua co room live noi bo, he thong chi ghi nhan gio check-in.'];
        }

        $participant = $this->resolveTeacherParticipant($room, $giangVien);
        $startedAt = $participant?->joined_at ?? $fallbackAt;

        return [$startedAt, 'Dong bo gio mo live tu phong ' . $room->platform_label . '.'];
    }

    private function resolveLiveEnd(
        LichHoc $lichHoc,
        GiangVien $giangVien,
        Carbon $fallbackAt,
        ?PhongHocLive $room = null
    ): array {
        if ($lichHoc->hinh_thuc !== 'online') {
            return [null, 'Buoi hoc truc tiep khong co room live can dong bo.'];
        }

        $room = $room ?: $this->resolveLinkedLiveRoom($lichHoc);

        if (!$room) {
            if (filled($lichHoc->link_online)) {
                return [$fallbackAt, 'Su dung moc check-out lam thoi diem tat live vi buoi hoc chi co link online ben ngoai.'];
            }

            return [null, 'Buoi hoc online chua co room live noi bo nen he thong chi ghi nhan gio check-out.'];
        }

        $participant = $this->resolveTeacherParticipant($room, $giangVien);
        $endedAt = $participant?->left_at ?? $fallbackAt;

        return [$endedAt, 'Dong bo gio tat live tu phong ' . $room->platform_label . '.'];
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
            ->with(['phongHocLive.nguoiThamGia'])
            ->latest('id')
            ->first();

        return $lecture?->phongHocLive;
    }

    private function resolveTeacherParticipant(PhongHocLive $room, GiangVien $giangVien)
    {
        $room->loadMissing('nguoiThamGia');

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

    private function resolveTimestamp(Carbon|string $value): Carbon
    {
        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }
}
