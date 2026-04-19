<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaiGiang;
use App\Models\PhongHocLive;
use App\Services\LiveRoomParticipationService;
use App\Support\OnlineMeetingUrl;

class LiveRoomController extends Controller
{
    public function __construct(
        private readonly LiveRoomParticipationService $participationService
    ) {
    }

    public function show(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveLectureAndRoom($id);
        [$playerMode, $playerUrl, $playerSupportsEmbed] = $this->resolvePlayerState($phongHocLive);

        return view('pages.live-room.show', [
            'mode' => 'admin',
            'baiGiang' => $baiGiang,
            'phongHocLive' => $phongHocLive,
            'canManageRoom' => false,
            'canJoinRoom' => $this->canSupervisorJoin($phongHocLive),
            'backUrl' => route('admin.bai-giang.show', $baiGiang->id),
            'showRoute' => route('admin.live-room.show', $baiGiang->id),
            'joinRoute' => route('admin.live-room.join', $baiGiang->id),
            'leaveRoute' => route('admin.live-room.leave', $baiGiang->id),
            'playerMode' => $playerMode,
            'playerUrl' => $playerUrl,
            'playerSupportsEmbed' => $playerSupportsEmbed,
        ]);
    }

    public function join(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveLectureAndRoom($id);
        abort_unless($this->canSupervisorJoin($phongHocLive), 403);

        $this->participationService->joinRoom($phongHocLive, auth()->user(), 'admin_supervisor');

        $externalUrl = $this->resolveExternalLaunchUrl($phongHocLive, $baiGiang);
        if ($externalUrl && ! $this->supportsEmbed($phongHocLive)) {
            return redirect()->away($externalUrl);
        }

        return redirect()
            ->route('admin.live-room.show', ['id' => $id, 'player' => 'participant'])
            ->with('success', 'Da ghi nhan admin vao phong voi vai tro giam sat.');
    }

    public function leave(int $id)
    {
        [, $phongHocLive] = $this->resolveLectureAndRoom($id);

        $this->participationService->leaveRoom($phongHocLive, auth()->user());

        return redirect()
            ->route('admin.live-room.show', $id)
            ->with('success', 'Da cap nhat trang thai roi phong giam sat.');
    }

    /**
     * @return array{0:BaiGiang,1:PhongHocLive}
     */
    private function resolveLectureAndRoom(int $lectureId): array
    {
        $baiGiang = BaiGiang::with([
            'khoaHoc',
            'moduleHoc',
            'lichHoc',
            'taiNguyenChinh',
            'taiNguyenPhu',
            'phongHocLive.baiGiang.lichHoc',
            'phongHocLive.moderator',
            'phongHocLive.troGiang',
            'phongHocLive.banGhis',
            'phongHocLive.nguoiThamGia.nguoiDung',
        ])->findOrFail($lectureId);

        abort_unless($baiGiang->isLive() && $baiGiang->phongHocLive, 404);

        return [$baiGiang, $baiGiang->phongHocLive];
    }

    private function canSupervisorJoin(PhongHocLive $phongHocLive): bool
    {
        if (in_array($phongHocLive->timeline_trang_thai, ['da_ket_thuc', 'da_huy'], true)) {
            return false;
        }

        return $phongHocLive->nen_tang_live === PhongHocLive::PLATFORM_INTERNAL
            || filled($phongHocLive->effective_external_meeting_url)
            || filled($phongHocLive->join_url)
            || filled($phongHocLive->start_url);
    }

    /**
     * @return array{0:string|null,1:string|null,2:bool}
     */
    private function resolvePlayerState(PhongHocLive $phongHocLive): array
    {
        $playerMode = request()->query('player');
        if ($playerMode !== 'participant') {
            return [null, null, false];
        }

        $playerUrl = $this->resolveExternalLaunchUrl($phongHocLive, $phongHocLive->baiGiang);
        $supportsEmbed = $this->supportsEmbed($phongHocLive);

        return [$playerMode, $supportsEmbed ? $playerUrl : null, $supportsEmbed];
    }

    private function resolveExternalLaunchUrl(PhongHocLive $phongHocLive, ?BaiGiang $baiGiang): ?string
    {
        return OnlineMeetingUrl::normalize(
            $phongHocLive->effective_external_meeting_url
                ?: ($phongHocLive->join_url ?: ($phongHocLive->start_url ?: $baiGiang?->lichHoc?->link_online))
        );
    }

    private function supportsEmbed(PhongHocLive $phongHocLive): bool
    {
        return filled($phongHocLive->embed_url)
            || (bool) config('live_room.platforms.' . $phongHocLive->platform_type . '.supports_embed', false);
    }
}
