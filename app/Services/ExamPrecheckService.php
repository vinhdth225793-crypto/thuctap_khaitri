<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ExamPrecheckService
{
    private const SESSION_KEY = 'exam_surveillance_precheck';
    private const TTL_MINUTES = 15;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function validatePayload(BaiKiemTra $baiKiemTra, array $payload): array
    {
        if (!$baiKiemTra->co_giam_sat) {
            return [];
        }

        $normalized = [
            'browser_supported' => (bool) Arr::get($payload, 'browser_supported', false),
            'camera_supported' => (bool) Arr::get($payload, 'camera_supported', false),
            'camera_ok' => (bool) Arr::get($payload, 'camera_ok', false),
            'fullscreen_supported' => (bool) Arr::get($payload, 'fullscreen_supported', false),
            'fullscreen_ok' => (bool) Arr::get($payload, 'fullscreen_ok', false),
            'visibility_supported' => (bool) Arr::get($payload, 'visibility_supported', false),
            'user_agent' => mb_substr((string) Arr::get($payload, 'user_agent', ''), 0, 500),
            'platform' => mb_substr((string) Arr::get($payload, 'platform', ''), 0, 255),
            'captured_at' => Arr::get($payload, 'captured_at'),
        ];

        $errors = [];

        if (!$normalized['browser_supported'] || !$normalized['visibility_supported']) {
            $errors['browser'] = 'Trình duyệt hiện tại chưa hỗ trợ đầy đủ API cần thiết cho bài thi giám sát.';
        }

        if ($baiKiemTra->bat_buoc_camera) {
            if (!$normalized['camera_supported']) {
                $errors['camera'] = 'Thiết bị hoặc trình duyệt chưa hỗ trợ camera cho bài thi này.';
            } elseif (!$normalized['camera_ok']) {
                $errors['camera'] = 'Không thể bật camera. Vui lòng cấp quyền và thử lại.';
            }
        }

        if ($baiKiemTra->bat_buoc_fullscreen) {
            if (!$normalized['fullscreen_supported']) {
                $errors['fullscreen'] = 'Trình duyệt hiện tại không hỗ trợ chế độ toàn màn hình.';
            } elseif (!$normalized['fullscreen_ok']) {
                $errors['fullscreen'] = 'Không thể bật chế độ toàn màn hình. Vui lòng thử lại.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function storePassedPrecheck(BaiKiemTra $baiKiemTra, int $hocVienId, array $payload): void
    {
        session()->put($this->sessionKey($baiKiemTra->id, $hocVienId), [
            'passed_at' => now()->toIso8601String(),
            'payload' => $payload,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPassedPrecheck(BaiKiemTra $baiKiemTra, int $hocVienId): ?array
    {
        $state = session()->get($this->sessionKey($baiKiemTra->id, $hocVienId));
        if (!is_array($state)) {
            return null;
        }

        $passedAt = Carbon::parse($state['passed_at'] ?? null);
        if ($passedAt->lt(now()->subMinutes(self::TTL_MINUTES))) {
            session()->forget($this->sessionKey($baiKiemTra->id, $hocVienId));

            return null;
        }

        return $state;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function consumePassedPrecheck(BaiKiemTra $baiKiemTra, int $hocVienId): ?array
    {
        $state = $this->getPassedPrecheck($baiKiemTra, $hocVienId);
        session()->forget($this->sessionKey($baiKiemTra->id, $hocVienId));

        return $state;
    }

    private function sessionKey(int $baiKiemTraId, int $hocVienId): string
    {
        return self::SESSION_KEY . '.' . $baiKiemTraId . '.' . $hocVienId;
    }
}
