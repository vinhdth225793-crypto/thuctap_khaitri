<?php

namespace App\Services;

use App\Models\BaiLamBaiKiemTra;
use App\Models\BaiLamSnapshotGiamSat;
use App\Models\BaiLamViPhamGiamSat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExamSnapshotService
{
    public function __construct(
        private readonly ExamSurveillanceLogService $logService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function handleSnapshot(
        BaiLamBaiKiemTra $baiLam,
        string $status,
        ?string $imageData,
        ?string $message = null,
        array $meta = []
    ): BaiLamSnapshotGiamSat {
        if ($status === 'failed') {
            return $this->recordFailure($baiLam, $message ?: 'Không thể chụp snapshot từ camera.', $meta);
        }

        if (blank($imageData)) {
            throw ValidationException::withMessages([
                'image_data' => 'Thiếu dữ liệu ảnh snapshot.',
            ]);
        }

        if (!preg_match('/^data:image\/(?P<extension>png|jpe?g|webp);base64,/', $imageData, $matches)) {
            throw ValidationException::withMessages([
                'image_data' => 'Định dạng snapshot không hợp lệ.',
            ]);
        }

        $binary = base64_decode(substr($imageData, strpos($imageData, ',') + 1), true);
        if ($binary === false) {
            throw ValidationException::withMessages([
                'image_data' => 'Không thể giải mã dữ liệu snapshot.',
            ]);
        }

        $extension = $matches['extension'] === 'jpeg' ? 'jpg' : $matches['extension'];
        $path = sprintf(
            'exam-surveillance/exam-%d/attempt-%d/%s-%s.%s',
            $baiLam->bai_kiem_tra_id,
            $baiLam->id,
            now()->format('YmdHis'),
            Str::random(8),
            $extension
        );

        Storage::disk('public')->put($path, $binary);

        $snapshot = $baiLam->giamSatSnapshots()->create([
            'duong_dan_file' => $path,
            'captured_at' => now(),
            'status' => 'captured',
            'meta' => $meta === [] ? null : $meta,
        ]);

        $this->logService->recordEvent(
            $baiLam,
            BaiLamViPhamGiamSat::SU_KIEN_SNAPSHOT_CAPTURED,
            'Đã lưu snapshot camera định kỳ.',
            array_merge($meta, ['snapshot_id' => $snapshot->id])
        );

        return $snapshot;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function recordFailure(BaiLamBaiKiemTra $baiLam, string $message, array $meta = []): BaiLamSnapshotGiamSat
    {
        $snapshot = $baiLam->giamSatSnapshots()->create([
            'duong_dan_file' => null,
            'captured_at' => now(),
            'status' => 'failed',
            'meta' => $meta === [] ? null : $meta,
        ]);

        $this->logService->recordEvent(
            $baiLam,
            BaiLamViPhamGiamSat::SU_KIEN_SNAPSHOT_FAILED,
            $message,
            array_merge($meta, ['snapshot_id' => $snapshot->id])
        );

        return $snapshot;
    }
}
