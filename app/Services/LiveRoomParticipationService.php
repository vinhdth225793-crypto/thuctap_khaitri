<?php

namespace App\Services;

use App\Models\NguoiDung;
use App\Models\PhongHocLive;
use App\Models\PhongHocLiveNguoiThamGia;

class LiveRoomParticipationService
{
    public function startRoom(PhongHocLive $phongHocLive, NguoiDung $user): void
    {
        $phongHocLive->update([
            'trang_thai_phong' => PhongHocLive::ROOM_STATE_DANG_DIEN_RA,
        ]);

        $this->logParticipation($phongHocLive, $user, $this->resolveRole($phongHocLive, $user));
    }

    public function joinRoom(PhongHocLive $phongHocLive, NguoiDung $user, ?string $forcedRole = null): PhongHocLiveNguoiThamGia
    {
        return $this->logParticipation($phongHocLive, $user, $forcedRole ?? $this->resolveRole($phongHocLive, $user));
    }

    public function leaveRoom(PhongHocLive $phongHocLive, NguoiDung $user): void
    {
        PhongHocLiveNguoiThamGia::query()
            ->where('phong_hoc_live_id', $phongHocLive->id)
            ->where('nguoi_dung_id', $user->ma_nguoi_dung)
            ->latest('id')
            ->first()?->update([
                'left_at' => now(),
                'trang_thai' => 'da_roi_phong',
            ]);
    }

    public function endRoom(PhongHocLive $phongHocLive, NguoiDung $user): void
    {
        $phongHocLive->update([
            'trang_thai_phong' => PhongHocLive::ROOM_STATE_DA_KET_THUC,
        ]);

        $this->leaveRoom($phongHocLive, $user);
    }

    public function resolveRole(PhongHocLive $phongHocLive, NguoiDung $user): string
    {
        if ((int) $phongHocLive->moderator_id === (int) $user->ma_nguoi_dung) {
            return 'moderator';
        }

        if ((int) $phongHocLive->tro_giang_id === (int) $user->ma_nguoi_dung) {
            return 'assistant';
        }

        if ($user->isAdmin()) {
            return 'host';
        }

        return 'student';
    }

    private function logParticipation(PhongHocLive $phongHocLive, NguoiDung $user, string $role): PhongHocLiveNguoiThamGia
    {
        $participant = PhongHocLiveNguoiThamGia::query()
            ->firstOrNew([
                'phong_hoc_live_id' => $phongHocLive->id,
                'nguoi_dung_id' => $user->ma_nguoi_dung,
                'vai_tro' => $role,
            ]);

        $participant->joined_at = $participant->joined_at ?? now();
        $participant->left_at = null;
        $participant->trang_thai = 'dang_tham_gia';
        $participant->save();

        return $participant;
    }
}
