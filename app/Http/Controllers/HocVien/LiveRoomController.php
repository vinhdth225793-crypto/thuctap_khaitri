<?php

namespace App\Http\Controllers\HocVien;

use App\Http\Controllers\Controller;
use App\Models\BaiGiang;
use App\Models\HocVienKhoaHoc;
use App\Services\LiveRoomParticipationService;

class LiveRoomController extends Controller
{
    public function __construct(
        private readonly LiveRoomParticipationService $participationService
    ) {
    }

    public function show(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveAccessibleLectureAndRoom($id);
        [$playerMode, $playerUrl, $playerSupportsEmbed] = $this->resolvePlayerState($phongHocLive);

        return view('pages.live-room.show', [
            'mode' => 'student',
            'baiGiang' => $baiGiang,
            'phongHocLive' => $phongHocLive,
            'canManageRoom' => false,
            'canJoinRoom' => $phongHocLive->can_student_join,
            'backUrl' => $baiGiang->lich_hoc_id
                ? route('hoc-vien.buoi-hoc.show', $baiGiang->lich_hoc_id)
                : route('hoc-vien.chi-tiet-khoa-hoc', $baiGiang->khoa_hoc_id),
            'playerMode' => $playerMode,
            'playerUrl' => $playerUrl,
            'playerSupportsEmbed' => $playerSupportsEmbed,
        ]);
    }

    public function join(int $id)
    {
        [, $phongHocLive] = $this->resolveAccessibleLectureAndRoom($id);
        abort_unless($phongHocLive->can_student_join, 403, 'Phong hoc live chua san sang cho hoc vien tham gia.');

        $this->participationService->joinRoom($phongHocLive, auth()->user(), 'student');

        return redirect()
            ->route('hoc-vien.live-room.show', ['id' => $id, 'player' => 'participant'])
            ->with('success', 'Da mo phong hoc ngay trong trang.');
    }

    public function leave(int $id)
    {
        [, $phongHocLive] = $this->resolveAccessibleLectureAndRoom($id);
        $this->participationService->leaveRoom($phongHocLive, auth()->user());

        return redirect()->route('hoc-vien.live-room.show', $id)->with('success', 'Da cap nhat trang thai roi phong.');
    }

    /**
     * @return array{0: BaiGiang, 1: \App\Models\PhongHocLive}
     */
    private function resolveAccessibleLectureAndRoom(int $lectureId): array
    {
        $baiGiang = BaiGiang::with([
            'khoaHoc',
            'moduleHoc',
            'lichHoc',
            'taiNguyenChinh',
            'taiNguyenPhu',
            'phongHocLive.moderator',
            'phongHocLive.troGiang',
            'phongHocLive.banGhis',
        ])
            ->where('trang_thai_duyet', BaiGiang::STATUS_DUYET_DA_DUYET)
            ->where('trang_thai_cong_bo', BaiGiang::CONG_BO_DA_CONG_BO)
            ->findOrFail($lectureId);

        abort_unless($baiGiang->isLive() && $baiGiang->phongHocLive, 404);

        $enrolled = HocVienKhoaHoc::query()
            ->where('khoa_hoc_id', $baiGiang->khoa_hoc_id)
            ->where('hoc_vien_id', auth()->user()->ma_nguoi_dung)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->exists();

        abort_unless($enrolled, 403, 'Ban khong co quyen truy cap phong hoc live nay.');

        return [$baiGiang, $baiGiang->phongHocLive];
    }

    /**
     * @return array{0: string|null, 1: string|null, 2: bool}
     */
    private function resolvePlayerState($phongHocLive): array
    {
        $playerMode = request()->query('player');
        if ($playerMode !== 'participant') {
            return [null, null, false];
        }

        $playerUrl = $phongHocLive->embed_url ?? $phongHocLive->join_url ?? $phongHocLive->start_url;
        $playerSupportsEmbed = filled($phongHocLive->embed_url)
            || (bool) config('live_room.platforms.' . $phongHocLive->nen_tang_live . '.supports_embed', false);

        return [$playerMode, $playerUrl, $playerSupportsEmbed];
    }
}
