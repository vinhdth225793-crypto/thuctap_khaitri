<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaiGiang;
use App\Models\PhongHocLive;
use App\Models\PhongHocLiveBanGhi;
use App\Services\LiveLectureService;
use App\Services\LiveRoomParticipationService;
use Illuminate\Http\Request;

class LiveRoomController extends Controller
{
    public function __construct(
        private readonly LiveRoomParticipationService $participationService,
        private readonly LiveLectureService $liveLectureService
    ) {
    }

    public function show(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        $user = auth()->user();
        [$playerMode, $playerUrl, $playerSupportsEmbed] = $this->resolvePlayerState($phongHocLive, true);

        return view('pages.live-room.show', [
            'mode' => 'teacher',
            'baiGiang' => $baiGiang,
            'phongHocLive' => $phongHocLive,
            'canManageRoom' => $this->canManageRoom($phongHocLive, $user),
            'canJoinRoom' => filled($phongHocLive->start_url),
            'backUrl' => route('giang-vien.bai-giang.index'),
            'playerMode' => $playerMode,
            'playerUrl' => $playerUrl,
            'playerSupportsEmbed' => $playerSupportsEmbed,
        ]);
    }

    public function start(int $id)
    {
        [, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $this->participationService->startRoom($phongHocLive, auth()->user());

        return redirect()
            ->route('giang-vien.live-room.show', ['id' => $id, 'player' => 'host'])
            ->with('success', 'Da bat dau buoi hoc live trong trang.');
    }

    public function join(int $id)
    {
        [, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $this->participationService->joinRoom($phongHocLive, auth()->user());

        return redirect()
            ->route('giang-vien.live-room.show', ['id' => $id, 'player' => 'host'])
            ->with('success', 'Da mo phong hoc ngay trong trang.');
    }

    public function leave(int $id)
    {
        [, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $this->participationService->leaveRoom($phongHocLive, auth()->user());

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Da cap nhat trang thai roi phong.');
    }

    public function end(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $this->participationService->endRoom($phongHocLive, auth()->user());

        if ($baiGiang->trang_thai_cong_bo === BaiGiang::CONG_BO_DA_CONG_BO) {
            $phongHocLive->refresh();
        }

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Da ket thuc buoi hoc live.');
    }

    public function storeRecording(Request $request, int $id)
    {
        [, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $validated = $request->validate([
            'nguon_ban_ghi' => 'required|in:zoom,google_meet,upload',
            'tieu_de' => 'required|string|max:255',
            'link_ngoai' => 'nullable|url|required_without:file_ban_ghi',
            'file_ban_ghi' => 'nullable|file|mimes:mp4,webm,mov,m4v|max:51200|required_without:link_ngoai',
            'thoi_luong' => 'nullable|integer|min:1',
        ]);

        $this->liveLectureService->addRecording($phongHocLive, $validated);

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Da them ban ghi cho buoi hoc live.');
    }

    public function destroyRecording(int $id, int $recordingId)
    {
        [, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $recording = $phongHocLive->banGhis()->whereKey($recordingId)->firstOrFail();
        $this->liveLectureService->deleteRecording($recording);

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Da xoa ban ghi.');
    }

    /**
     * @return array{0: BaiGiang, 1: PhongHocLive}
     */
    private function resolveManagedLectureAndRoom(int $lectureId): array
    {
        $userId = auth()->user()->ma_nguoi_dung;

        $baiGiang = BaiGiang::with([
            'khoaHoc',
            'moduleHoc',
            'lichHoc',
            'taiNguyenChinh',
            'taiNguyenPhu',
            'phongHocLive.moderator',
            'phongHocLive.troGiang',
            'phongHocLive.banGhis',
            'phongHocLive.nguoiThamGia.nguoiDung',
        ])
            ->where(function ($query) use ($userId) {
                $query->where('nguoi_tao_id', $userId)
                    ->orWhereHas('phongHocLive', function ($roomQuery) use ($userId) {
                        $roomQuery->where('moderator_id', $userId)
                            ->orWhere('tro_giang_id', $userId);
                    });
            })
            ->findOrFail($lectureId);

        abort_unless($baiGiang->isLive() && $baiGiang->phongHocLive, 404);

        return [$baiGiang, $baiGiang->phongHocLive];
    }

    private function canManageRoom(PhongHocLive $phongHocLive, $user): bool
    {
        return in_array((int) $user->ma_nguoi_dung, [
            (int) $phongHocLive->moderator_id,
            (int) $phongHocLive->tro_giang_id,
            (int) $phongHocLive->created_by,
        ], true);
    }

    /**
     * @return array{0: string|null, 1: string|null, 2: bool}
     */
    private function resolvePlayerState(PhongHocLive $phongHocLive, bool $isTeacherContext): array
    {
        $playerMode = request()->query('player');
        if (!in_array($playerMode, ['host', 'participant'], true)) {
            return [null, null, false];
        }

        if (!$isTeacherContext && $playerMode === 'host') {
            $playerMode = 'participant';
        }

        $playerUrl = $playerMode === 'host'
            ? ($phongHocLive->embed_url ?? $phongHocLive->start_url ?? $phongHocLive->join_url)
            : ($phongHocLive->embed_url ?? $phongHocLive->join_url ?? $phongHocLive->start_url);

        $playerSupportsEmbed = filled($phongHocLive->embed_url)
            || (bool) config('live_room.platforms.' . $phongHocLive->nen_tang_live . '.supports_embed', false);

        return [$playerMode, $playerUrl, $playerSupportsEmbed];
    }
}
