<?php

namespace App\Observers;

use App\Models\ModuleHoc;
use App\Models\KhoaHoc;

class ModuleHocObserver
{
    /**
     * Sau khi tạo module mới → cập nhật tong_so_module của khóa học
     */
    public function created(ModuleHoc $moduleHoc): void
    {
        $this->syncTongSoModule($moduleHoc->khoa_hoc_id);
    }

    /**
     * Sau khi xóa module → cập nhật tong_so_module của khóa học
     */
    public function deleted(ModuleHoc $moduleHoc): void
    {
        $this->syncTongSoModule($moduleHoc->khoa_hoc_id);
    }

    /**
     * Đồng bộ tong_so_module = số module thực tế trong DB
     */
    private function syncTongSoModule(int $khoaHocId): void
    {
        $soModule = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->count();

        KhoaHoc::where('id', $khoaHocId)
            ->update(['tong_so_module' => $soModule]);
    }
}
