<?php

namespace App\Services;

use App\Models\LichHoc;
use Illuminate\Support\Str;

class OnlineMeetingProviderService
{
    /**
     * Cấp link online cho buổi học.
     * Ưu tiên dùng link sẵn có, nếu thiếu thì tạo mới.
     *
     * @param LichHoc $lichHoc
     * @return array{link: string, source: string}
     */
    public function provide(LichHoc $lichHoc): array
    {
        // 1. Nếu đã có link, giữ nguyên link và nguồn hiện tại (hoặc mặc định là admin_manual nếu chưa có nguồn)
        if (filled($lichHoc->link_online)) {
            return [
                'link' => $lichHoc->link_online,
                'source' => $lichHoc->online_link_source ?: LichHoc::ONLINE_LINK_SOURCE_ADMIN_MANUAL,
            ];
        }

        // 2. Nếu chưa có link, tạo link Google Meet placeholder
        return $this->generateGoogleMeetPlaceholder($lichHoc);
    }

    /**
     * Tạo link Google Meet giả lập (Placeholder).
     */
    public function generateGoogleMeetPlaceholder(LichHoc $lichHoc): array
    {
        $p1 = Str::lower(Str::random(3));
        $p2 = Str::lower(Str::random(4));
        $p3 = Str::lower(Str::random(3));
        
        $code = "{$p1}-{$p2}-{$p3}";
        $link = "https://meet.google.com/{$code}";

        return [
            'link' => $link,
            'source' => LichHoc::ONLINE_LINK_SOURCE_TEACHER_GENERATED,
        ];
    }

    /**
     * Cập nhật link cho lịch học nếu còn thiếu hoặc thiếu nguồn.
     */
    public function ensureOnlineLink(LichHoc $lichHoc): LichHoc
    {
        // Ép kiểu về string để so sánh chính xác
        if ((string)$lichHoc->hinh_thuc !== 'online') {
            return $lichHoc;
        }

        $needsUpdate = false;

        if (blank($lichHoc->link_online)) {
            $provided = $this->provide($lichHoc);
            $lichHoc->link_online = $provided['link'];
            $lichHoc->online_link_source = $provided['source'];
            $needsUpdate = true;
        } elseif (blank($lichHoc->online_link_source)) {
            $provided = $this->provide($lichHoc);
            $lichHoc->online_link_source = $provided['source'];
            $needsUpdate = true;
        }

        if ($needsUpdate && $lichHoc->exists) {
            $lichHoc->save();
        }

        return $lichHoc;
    }
}
