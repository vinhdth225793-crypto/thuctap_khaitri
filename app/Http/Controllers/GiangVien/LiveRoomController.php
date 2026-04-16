<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaiGiang;
use App\Models\LichHoc;
use App\Models\PhongHocLive;
use App\Services\LiveLectureService;
use App\Services\LiveRoomParticipationService;
use App\Services\TeacherAttendanceService;
use App\Services\TeacherScheduleLiveRoomService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LiveRoomController extends Controller
{
    public function __construct(
        private readonly LiveRoomParticipationService $participationService,
        private readonly LiveLectureService $liveLectureService,
        private readonly TeacherScheduleLiveRoomService $teacherScheduleLiveRoomService,
        private readonly TeacherAttendanceService $teacherAttendanceService,
    ) {
    }

    public function createForSchedule(int $lichHocId): RedirectResponse
    {
        [, $lecture] = $this->resolveManagedScheduleRoom($lichHocId);

        return redirect()
            ->route('giang-vien.live-room.show', ['id' => $lecture->id])
            ->with('success', 'Đã tạo phòng học trực tuyến nội bộ cho buổi học online.');
    }

    public function showScheduleRoom(int $lichHocId): RedirectResponse
    {
        [, $lecture] = $this->resolveManagedScheduleRoom($lichHocId);

        return redirect()->route('giang-vien.live-room.show', ['id' => $lecture->id]);
    }

    public function endScheduleRoom(int $lichHocId): RedirectResponse
    {
        [$lichHoc, $lecture, $phongHocLive] = $this->resolveManagedScheduleRoom($lichHocId);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $this->participationService->endRoom($phongHocLive, auth()->user());

        if (auth()->user()->giangVien) {
            $this->teacherAttendanceService->ensureCheckOutFromRoom(
                $lichHoc,
                auth()->user()->giangVien,
                auth()->user(),
                $phongHocLive
            );
        }

        $this->markScheduleFinished($lichHoc);

        return redirect()
            ->to($this->buildTeacherCourseSessionUrl($lichHoc))
            ->with('success', 'Đã kết thúc phòng học trực tuyến nội bộ của buổi học.');
    }

    public function show(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        $user = auth()->user();
        [$playerMode, $playerUrl, $playerSupportsEmbed] = $this->resolvePlayerState($phongHocLive, true);
        $sessionLinks = $this->buildScheduleActionLinks($baiGiang->lichHoc);

        return view('pages.giang-vien.live-room.show', [
            'mode' => 'teacher',
            'lectureId' => $baiGiang->id,
            'baiGiang' => $baiGiang,
            'phongHocLive' => $phongHocLive,
            'canManageRoom' => $this->canManageRoom($phongHocLive, $user),
            'canJoinRoom' => filled($phongHocLive->start_url),
            'backUrl' => $sessionLinks['show_course'] ?? route('giang-vien.bai-giang.index'),
            'attendanceUrl' => $sessionLinks['attendance'] ?? null,
            'resourceUrl' => $sessionLinks['resources'] ?? null,
            'examUrl' => $sessionLinks['exams'] ?? null,
            'playerMode' => $playerMode,
            'playerUrl' => $playerUrl,
            'playerSupportsEmbed' => $playerSupportsEmbed,
        ]);
    }

    public function start(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);
        $this->ensureScheduleRoomActionAllowed($baiGiang, 'start');

        $this->participationService->startRoom($phongHocLive, auth()->user());

        if ($baiGiang->lichHoc && auth()->user()->giangVien) {
            $this->markScheduleInProgress($baiGiang->lichHoc);

            $this->teacherAttendanceService->ensureCheckInFromRoom(
                $this->freshScheduleWithOptionalParticipants($baiGiang->lichHoc),
                auth()->user()->giangVien,
                auth()->user(),
                $this->freshRoomWithOptionalParticipants($phongHocLive)
            );
        }

        return redirect()
            ->route('giang-vien.live-room.show', ['id' => $id, 'player' => 'host'])
            ->with('success', 'Đã bắt đầu buổi học trực tuyến ngay trong trang.');
    }

    public function join(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);
        $this->ensureScheduleRoomActionAllowed($baiGiang, 'join');

        $this->participationService->joinRoom($phongHocLive, auth()->user());

        if ($baiGiang->lichHoc && auth()->user()->giangVien) {
            $this->markScheduleInProgress($baiGiang->lichHoc);

            $this->teacherAttendanceService->ensureCheckInFromRoom(
                $this->freshScheduleWithOptionalParticipants($baiGiang->lichHoc),
                auth()->user()->giangVien,
                auth()->user(),
                $this->freshRoomWithOptionalParticipants($phongHocLive)
            );
        }

        return redirect()
            ->route('giang-vien.live-room.show', ['id' => $id, 'player' => 'host'])
            ->with('success', 'Đã mở phòng học ngay trong trang.');
    }

    public function leave(int $id)
    {
        [, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $this->participationService->leaveRoom($phongHocLive, auth()->user());

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Đã cập nhật trạng thái rời phòng.');
    }

    public function end(int $id)
    {
        [$baiGiang, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $this->participationService->endRoom($phongHocLive, auth()->user());

        if ($baiGiang->lichHoc && auth()->user()->giangVien) {
            $this->teacherAttendanceService->ensureCheckOutFromRoom(
                $baiGiang->lichHoc,
                auth()->user()->giangVien,
                auth()->user(),
                $this->freshRoomWithOptionalParticipants($phongHocLive)
            );

            $this->markScheduleFinished($baiGiang->lichHoc);
        }

        if ($baiGiang->trang_thai_cong_bo === BaiGiang::CONG_BO_DA_CONG_BO) {
            $phongHocLive->refresh();
        }

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Đã kết thúc buổi học trực tuyến.');
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

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Đã thêm bản ghi cho buổi học trực tuyến.');
    }

    public function destroyRecording(int $id, int $recordingId)
    {
        [, $phongHocLive] = $this->resolveManagedLectureAndRoom($id);
        abort_unless($this->canManageRoom($phongHocLive, auth()->user()), 403);

        $recording = $phongHocLive->banGhis()->whereKey($recordingId)->firstOrFail();
        $this->liveLectureService->deleteRecording($recording);

        return redirect()->route('giang-vien.live-room.show', $id)->with('success', 'Đã xóa bản ghi.');
    }

    /**
     * @return array{0:LichHoc,1:BaiGiang,2:PhongHocLive}
     */
    private function resolveManagedScheduleRoom(int $lichHocId): array
    {
        $user = auth()->user();
        $giangVien = $user?->giangVien;

        abort_if(!$user || !$giangVien, 403);

        $lichHoc = LichHoc::with(['khoaHoc', 'moduleHoc', 'baiGiangs.phongHocLive'])->findOrFail($lichHocId);
        [$lecture, $room] = $this->teacherScheduleLiveRoomService->ensureInternalRoom($lichHoc, $giangVien, $user);

        return [$lichHoc, $lecture, $room];
    }

    /**
     * @return array{0:BaiGiang,1:PhongHocLive}
     */
    private function resolveManagedLectureAndRoom(int $lectureId): array
    {
        $user = auth()->user();
        $userId = (int) $user->ma_nguoi_dung;
        $giangVienId = (int) ($user->giangVien?->id ?? 0);
        $hasRoomTeacherColumn = Schema::hasColumn('phong_hoc_live', 'giang_vien_id');
        $hasRoomModeratorColumn = Schema::hasColumn('phong_hoc_live', 'moderator_id');
        $hasRoomAssistantColumn = Schema::hasColumn('phong_hoc_live', 'tro_giang_id');

        $relations = [
            'khoaHoc',
            'moduleHoc',
            'lichHoc',
            'taiNguyenChinh',
            'taiNguyenPhu',
            'phongHocLive.moderator',
            'phongHocLive.troGiang',
        ];

        if (Schema::hasTable('phong_hoc_live_ban_ghi')) {
            $relations[] = 'phongHocLive.banGhis';
        }

        if (Schema::hasTable('phong_hoc_live_nguoi_tham_gia')) {
            $relations[] = 'phongHocLive.nguoiThamGia.nguoiDung';
        }

        $baiGiang = BaiGiang::with($relations)
            ->where(function ($query) use ($userId, $giangVienId, $hasRoomTeacherColumn, $hasRoomModeratorColumn, $hasRoomAssistantColumn) {
                $query->where('nguoi_tao_id', $userId)
                    ->orWhereHas('phongHocLive', function ($roomQuery) use ($userId, $giangVienId, $hasRoomTeacherColumn, $hasRoomModeratorColumn, $hasRoomAssistantColumn) {
                        $roomQuery->where(function ($managerQuery) use ($userId, $giangVienId, $hasRoomTeacherColumn, $hasRoomModeratorColumn, $hasRoomAssistantColumn) {
                            $hasCondition = false;

                            if ($hasRoomTeacherColumn && $giangVienId > 0) {
                                $managerQuery->where('giang_vien_id', $giangVienId);
                                $hasCondition = true;
                            }

                            if ($hasRoomModeratorColumn) {
                                $hasCondition
                                    ? $managerQuery->orWhere('moderator_id', $userId)
                                    : $managerQuery->where('moderator_id', $userId);
                                $hasCondition = true;
                            }

                            if ($hasRoomAssistantColumn) {
                                $hasCondition
                                    ? $managerQuery->orWhere('tro_giang_id', $userId)
                                    : $managerQuery->where('tro_giang_id', $userId);
                                $hasCondition = true;
                            }

                            if (! $hasCondition) {
                                $managerQuery->whereRaw('0 = 1');
                            }
                        });
                    });
            })
            ->findOrFail($lectureId);

        abort_unless($baiGiang->isLive() && $baiGiang->phongHocLive, 404);
        $this->ensureOptionalRoomRelations($baiGiang->phongHocLive);

        return [$baiGiang, $baiGiang->phongHocLive];
    }

    private function ensureOptionalRoomRelations(PhongHocLive $phongHocLive): void
    {
        if (! Schema::hasTable('phong_hoc_live_ban_ghi') && ! $phongHocLive->relationLoaded('banGhis')) {
            $phongHocLive->setRelation('banGhis', new EloquentCollection());
        }

        if (! Schema::hasTable('phong_hoc_live_nguoi_tham_gia') && ! $phongHocLive->relationLoaded('nguoiThamGia')) {
            $phongHocLive->setRelation('nguoiThamGia', new EloquentCollection());
        }
    }

    private function freshScheduleWithOptionalParticipants(LichHoc $lichHoc): LichHoc
    {
        if (Schema::hasTable('phong_hoc_live_nguoi_tham_gia')) {
            return $lichHoc->fresh('baiGiangs.phongHocLive.nguoiThamGia') ?? $lichHoc;
        }

        $freshSchedule = $lichHoc->fresh('baiGiangs.phongHocLive') ?? $lichHoc;

        $freshSchedule->baiGiangs?->each(function (BaiGiang $lecture): void {
            if ($lecture->phongHocLive) {
                $lecture->phongHocLive->setRelation('nguoiThamGia', new EloquentCollection());
            }
        });

        return $freshSchedule;
    }

    private function freshRoomWithOptionalParticipants(PhongHocLive $phongHocLive): PhongHocLive
    {
        if (Schema::hasTable('phong_hoc_live_nguoi_tham_gia')) {
            return $phongHocLive->fresh('nguoiThamGia') ?? $phongHocLive;
        }

        $freshRoom = $phongHocLive->fresh() ?? $phongHocLive;
        $freshRoom->setRelation('nguoiThamGia', new EloquentCollection());

        return $freshRoom;
    }

    private function canManageRoom(PhongHocLive $phongHocLive, $user): bool
    {
        $userId = (int) $user->ma_nguoi_dung;
        $giangVienId = (int) ($user->giangVien?->id ?? 0);

        if ($giangVienId > 0 && (int) $phongHocLive->giang_vien_id === $giangVienId) {
            return true;
        }

        if ($giangVienId > 0 && (int) ($phongHocLive->lichHoc?->giang_vien_id ?? 0) === $giangVienId) {
            return true;
        }

        return in_array($userId, [
            (int) $phongHocLive->moderator_id,
            (int) $phongHocLive->tro_giang_id,
            (int) $phongHocLive->created_by,
        ], true);
    }

    /**
     * @return array<string, string>
     */
    private function buildScheduleActionLinks(?LichHoc $lichHoc): array
    {
        if (!$lichHoc) {
            return [];
        }

        return [
            'show_course' => $this->buildTeacherCourseSessionUrl($lichHoc),
            'attendance' => $this->buildTeacherCourseSessionUrl($lichHoc, 'attendance'),
            'resources' => $this->buildTeacherCourseSessionUrl($lichHoc, 'resources'),
            'exams' => $this->buildTeacherCourseSessionUrl($lichHoc, 'exams'),
        ];
    }

    private function buildTeacherCourseSessionUrl(LichHoc $lichHoc, ?string $quickAction = null): string
    {
        $params = [
            'id' => $lichHoc->khoa_hoc_id,
            'focus_lich_hoc_id' => $lichHoc->id,
        ];

        if ($quickAction !== null) {
            $params['quick_action'] = $quickAction;
        }

        return route('giang-vien.khoa-hoc.show', $params) . '#session-' . $lichHoc->id;
    }
    private function ensureScheduleRoomActionAllowed(BaiGiang $baiGiang, string $action): void
    {
        $lichHoc = $baiGiang->lichHoc;

        if (!$lichHoc) {
            return;
        }

        if ($lichHoc->teaching_session_status === 'da_huy') {
            throw ValidationException::withMessages([
                'live_room' => 'Buổi học đã bị hủy, không thể tiếp tục thao tác với phòng học trực tuyến.',
            ]);
        }

        if ($lichHoc->teaching_session_status === 'da_ket_thuc') {
            throw ValidationException::withMessages([
                'live_room' => 'Buổi học đã kết thúc, không thể mở lại hoặc tham gia phòng học trực tuyến.',
            ]);
        }
    }

    private function markScheduleInProgress(LichHoc $lichHoc): void
    {
        if ($lichHoc->trang_thai === 'dang_hoc' || $lichHoc->trang_thai === 'hoan_thanh') {
            return;
        }

        $lichHoc->forceFill([
            'trang_thai' => 'dang_hoc',
        ])->save();
    }

    private function markScheduleFinished(LichHoc $lichHoc): void
    {
        if (in_array($lichHoc->trang_thai, ['hoan_thanh', 'huy'], true)) {
            return;
        }

        $lichHoc->forceFill([
            'trang_thai' => 'hoan_thanh',
        ])->save();
    }

    /**
     * @return array{0:string|null,1:string|null,2:bool}
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
