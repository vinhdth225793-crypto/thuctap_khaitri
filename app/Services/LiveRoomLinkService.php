<?php

namespace App\Services;

use App\Models\LichHoc;
use App\Models\LiveRoomLinkHistory;
use App\Models\NguoiDung;
use App\Models\PhongHocLive;
use App\Support\OnlineMeetingUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiveRoomLinkService
{
    public function updateGoogleMeetLink(
        PhongHocLive $phongHocLive,
        ?LichHoc $lichHoc,
        NguoiDung $actor,
        string $url,
        ?string $reason = null
    ): LiveRoomLinkHistory {
        $newUrl = OnlineMeetingUrl::normalize($url);

        if (! OnlineMeetingUrl::isGoogleMeetUrl($newUrl)) {
            throw ValidationException::withMessages([
                'google_meet_url' => 'Link phai la Google Meet hop le, vi du https://meet.google.com/abc-defg-hij.',
            ]);
        }

        $meetingCode = OnlineMeetingUrl::meetingCode($newUrl);
        $oldUrl = OnlineMeetingUrl::normalize(
            $phongHocLive->effective_external_meeting_url
                ?: ($lichHoc?->link_online)
        );

        return DB::transaction(function () use ($phongHocLive, $lichHoc, $actor, $newUrl, $meetingCode, $oldUrl, $reason) {
            $payload = $phongHocLive->du_lieu_nen_tang_json;
            $payload['join_url'] = $newUrl;
            $payload['start_url'] = $newUrl;
            $payload['meeting_code'] = $meetingCode;
            $payload['room_scope'] = $payload['room_scope'] ?? 'external_provider';

            $phongHocLive->forceFill([
                'platform_type' => PhongHocLive::PLATFORM_GOOGLE_MEET,
                'external_meeting_url' => $newUrl,
                'external_meeting_code' => $meetingCode,
                'external_link_updated_at' => now(),
                'external_link_updated_by' => $actor->ma_nguoi_dung,
                'du_lieu_nen_tang_json' => $payload,
            ])->save();

            if ($lichHoc) {
                $lichHoc->forceFill([
                    'hinh_thuc' => 'online',
                    'nen_tang' => 'Google Meet',
                    'link_online' => $newUrl,
                    'online_link_source' => LichHoc::ONLINE_LINK_SOURCE_TEACHER_MANUAL,
                    'meeting_id' => $meetingCode ?: $lichHoc->meeting_id,
                ])->save();
            }

            return LiveRoomLinkHistory::create([
                'phong_hoc_live_id' => $phongHocLive->id,
                'lich_hoc_id' => $lichHoc?->id,
                'provider' => PhongHocLive::PLATFORM_GOOGLE_MEET,
                'old_url' => $oldUrl,
                'new_url' => $newUrl,
                'updated_by' => $actor->ma_nguoi_dung,
                'reason' => $reason,
                'metadata_json' => [
                    'meeting_code' => $meetingCode,
                    'source' => 'teacher_live_room',
                ],
            ]);
        });
    }
}
