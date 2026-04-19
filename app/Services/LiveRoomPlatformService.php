<?php

namespace App\Services;

use App\Models\PhongHocLive;
use App\Support\OnlineMeetingUrl;

class LiveRoomPlatformService
{
    public function platformOptions(): array
    {
        return config('live_room.platforms', []);
    }

    public function platformLabel(?string $platform): string
    {
        if (!$platform) {
            return 'Chua xac dinh';
        }

        return $this->platformOptions()[$platform]['label'] ?? ucfirst(str_replace('_', ' ', $platform));
    }

    public function buildPlatformPayload(?string $platform, array $input): array
    {
        $payload = [
            'join_url' => $input['join_url'] ?? null,
            'start_url' => $input['start_url'] ?? null,
            'embed_url' => $input['embed_url'] ?? null,
            'room_code' => $input['room_code'] ?? null,
            'room_scope' => $input['room_scope'] ?? null,
            'meeting_id' => $input['meeting_id'] ?? null,
            'meeting_code' => $input['meeting_code'] ?? null,
            'passcode' => $input['passcode'] ?? null,
            'host_email' => $input['host_email'] ?? null,
            'host_name' => $input['host_name'] ?? null,
            'waiting_room' => (bool) ($input['waiting_room'] ?? false),
            'security_note' => $input['security_note'] ?? null,
        ];

        if ($platform === PhongHocLive::PLATFORM_INTERNAL) {
            unset($payload['join_url'], $payload['start_url'], $payload['embed_url'], $payload['meeting_id'], $payload['meeting_code'], $payload['passcode']);
        }

        if ($platform === PhongHocLive::PLATFORM_GOOGLE_MEET) {
            unset($payload['embed_url'], $payload['meeting_id']);
        }

        if ($platform === PhongHocLive::PLATFORM_ZOOM) {
            unset($payload['meeting_code']);
        }

        return array_filter(
            $payload,
            static fn ($value) => $value !== null && $value !== ''
        );
    }

    public function getJoinUrl(PhongHocLive $phongHocLive): ?string
    {
        return OnlineMeetingUrl::normalize($phongHocLive->external_meeting_url)
            ?? OnlineMeetingUrl::normalize($phongHocLive->du_lieu_nen_tang_json['join_url'] ?? null)
            ?? OnlineMeetingUrl::normalize($phongHocLive->du_lieu_nen_tang_json['start_url'] ?? null)
            ?? null;
    }

    public function getStartUrl(PhongHocLive $phongHocLive): ?string
    {
        return OnlineMeetingUrl::normalize($phongHocLive->external_meeting_url)
            ?? OnlineMeetingUrl::normalize($phongHocLive->du_lieu_nen_tang_json['start_url'] ?? null)
            ?? OnlineMeetingUrl::normalize($phongHocLive->du_lieu_nen_tang_json['join_url'] ?? null)
            ?? null;
    }

    public function getEmbedUrl(PhongHocLive $phongHocLive): ?string
    {
        return $phongHocLive->du_lieu_nen_tang_json['embed_url'] ?? null;
    }
}
