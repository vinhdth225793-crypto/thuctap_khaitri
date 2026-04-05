<?php

namespace App\Services;

use App\Models\BaiGiang;
use App\Models\GiangVien;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\PhongHocLive;
use Illuminate\Validation\ValidationException;

class TeacherScheduleLiveRoomService
{
    public function __construct(
        private readonly TeacherAttendanceService $teacherAttendanceService,
        private readonly LiveLectureService $liveLectureService,
    ) {
    }

    /**
     * @return array{0:BaiGiang,1:PhongHocLive}
     */
    public function ensureInternalRoom(LichHoc $lichHoc, GiangVien $giangVien, NguoiDung $actor): array
    {
        $this->teacherAttendanceService->ensureTeacherCanManage($lichHoc, $giangVien);
        $this->ensureOnlineSchedule($lichHoc);

        $lecture = $this->resolveInternalLecture($lichHoc);

        if (
            !$lecture
            || !$lecture->phongHocLive
            || $lecture->phongHocLive->nen_tang_live !== PhongHocLive::PLATFORM_INTERNAL
        ) {
            $this->ensureRoomCanBeProvisioned($lichHoc);
        }

        if (!$lecture) {
            $lecture = BaiGiang::create([
                'khoa_hoc_id' => $lichHoc->khoa_hoc_id,
                'module_hoc_id' => $lichHoc->module_hoc_id,
                'lich_hoc_id' => $lichHoc->id,
                'nguoi_tao_id' => $actor->ma_nguoi_dung,
                'tieu_de' => $this->buildLectureTitle($lichHoc),
                'mo_ta' => $this->buildLectureDescription($lichHoc),
                'loai_bai_giang' => BaiGiang::TYPE_LIVE,
                'thu_tu_hien_thi' => $lichHoc->buoi_so ?? 0,
                'thoi_diem_mo' => $lichHoc->starts_at,
                'trang_thai_duyet' => BaiGiang::STATUS_DUYET_NHAP,
                'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
            ]);
        }

        $room = $lecture->phongHocLive;

        if (!$room || $room->nen_tang_live !== PhongHocLive::PLATFORM_INTERNAL) {
            $room = $this->liveLectureService->syncLiveRoom($lecture, [
                'loai_bai_giang' => BaiGiang::TYPE_LIVE,
                'hanh_dong' => 'luu_nhap',
                'live' => [
                    'nen_tang_live' => PhongHocLive::PLATFORM_INTERNAL,
                    'loai_live' => PhongHocLive::TYPE_CLASS,
                    'tieu_de' => $this->buildRoomTitle($lichHoc),
                    'mo_ta' => $this->buildRoomDescription($lichHoc),
                    'moderator_id' => $giangVien->nguoi_dung_id,
                    'thoi_gian_bat_dau' => $lichHoc->starts_at ?? now(),
                    'thoi_luong_phut' => $this->resolveRoomDuration($lichHoc),
                    'mo_phong_truoc_phut' => config('live_room.defaults.open_before_minutes', 15),
                    'nhac_truoc_phut' => config('live_room.defaults.reminder_minutes', 10),
                    'cho_phep_chat' => true,
                    'cho_phep_thao_luan' => true,
                    'tat_mic_khi_vao' => true,
                    'tat_camera_khi_vao' => true,
                    'room_code' => $this->buildRoomCode($lichHoc),
                    'room_scope' => 'teacher_schedule',
                    'security_note' => 'Phong live noi bo danh cho buoi hoc nay. Co the nang cap sang WebRTC/Jitsi trong giai doan sau.',
                ],
            ], $actor);
        }

        return [$lecture->fresh(['phongHocLive']), $room->fresh()];
    }

    public function resolveInternalLecture(LichHoc $lichHoc): ?BaiGiang
    {
        if ($lichHoc->relationLoaded('baiGiangs')) {
            return $lichHoc->baiGiangs
                ->filter(function (BaiGiang $lecture) {
                    return $lecture->isLive()
                        && $lecture->phongHocLive
                        && $lecture->phongHocLive->nen_tang_live === PhongHocLive::PLATFORM_INTERNAL;
                })
                ->sortByDesc('id')
                ->first();
        }

        return $lichHoc->baiGiangs()
            ->with('phongHocLive')
            ->where('loai_bai_giang', BaiGiang::TYPE_LIVE)
            ->whereHas('phongHocLive', function ($query) {
                $query->where('nen_tang_live', PhongHocLive::PLATFORM_INTERNAL);
            })
            ->latest('id')
            ->first();
    }

    private function ensureOnlineSchedule(LichHoc $lichHoc): void
    {
        if ($lichHoc->hinh_thuc === 'online') {
            return;
        }

        throw ValidationException::withMessages([
            'live_room' => 'Chi buoi hoc online moi duoc tao phong live noi bo.',
        ]);
    }

    private function ensureRoomCanBeProvisioned(LichHoc $lichHoc): void
    {
        if ($lichHoc->teaching_session_status === 'da_huy') {
            throw ValidationException::withMessages([
                'live_room' => 'Buoi hoc da bi huy nen khong the tao moi phong live noi bo.',
            ]);
        }

        if ($lichHoc->teaching_session_status === 'da_ket_thuc') {
            throw ValidationException::withMessages([
                'live_room' => 'Buoi hoc da ket thuc nen khong the tao moi phong live noi bo.',
            ]);
        }
    }

    private function buildLectureTitle(LichHoc $lichHoc): string
    {
        return 'Phong live noi bo - Buoi ' . ($lichHoc->buoi_so ?? $lichHoc->id);
    }

    private function buildLectureDescription(LichHoc $lichHoc): string
    {
        return 'Phong live noi bo duoc tao tu dong cho buoi hoc online ngay ' . optional($lichHoc->ngay_hoc)->format('d/m/Y') . '.';
    }

    private function buildRoomTitle(LichHoc $lichHoc): string
    {
        return 'Lop hoc live buoi ' . ($lichHoc->buoi_so ?? $lichHoc->id);
    }

    private function buildRoomDescription(LichHoc $lichHoc): string
    {
        return 'Phong hoc noi bo gan voi lich hoc #' . $lichHoc->id . ' de giang vien demo va dieu hanh buoi hoc ngay tren he thong.';
    }

    private function buildRoomCode(LichHoc $lichHoc): string
    {
        return 'LH-' . $lichHoc->id . '-B' . ($lichHoc->buoi_so ?? 0);
    }

    private function resolveRoomDuration(LichHoc $lichHoc): int
    {
        if ($lichHoc->starts_at && $lichHoc->ends_at) {
            return max(15, $lichHoc->starts_at->diffInMinutes($lichHoc->ends_at));
        }

        return 90;
    }
}
