<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameLegacyTables extends Command
{
    protected $signature = 'app:rename-legacy-tables';

    public function handle()
    {
        $tablesToRename = [
            'nguoi_dung', 'hoc_vien', 'giang_vien', 'khoa_hoc', 'module_hoc',
            'phan_cong_module_giang_vien', 'hoc_vien_khoa_hoc', 'lich_hoc',
            'diem_danh', 'yeu_cau_hoc_vien', 'tai_nguyen_buoi_hoc', 'bai_giangs',
            'bai_giang_tai_nguyen', 'bai_kiem_tra', 'ngan_hang_cau_hoi',
            'dap_an_cau_hoi', 'chi_tiet_bai_kiem_tra', 'bai_lam_bai_kiem_tra',
            'chi_tiet_bai_lam_bai_kiem_tra', 'ket_qua_hoc_tap', 'phong_hoc_live',
            'phong_hoc_live_nguoi_tham_gia', 'phong_hoc_live_ban_ghi',
            'giang_vien_don_xin_nghi', 'diem_danh_giang_vien', 'system_settings',
            'banners', 'thong_bao', 'nhom_nganh', 'tai_khoan_cho_phe_duyet',
            'bai_lam_snapshot_giam_sat', 'bai_lam_vi_pham_giam_sat'
        ];

        foreach ($tablesToRename as $tableName) {
            if (Schema::hasTable($tableName)) {
                $newName = 'cu_' . $tableName;
                if (!Schema::hasTable($newName)) {
                    Schema::rename($tableName, $newName);
                    $this->info("Renamed $tableName to $newName");
                } else {
                    $this->warn("Skipped $tableName: $newName already exists.");
                }
            } else {
                $this->warn("Skipped $tableName: Table not found.");
            }
        }
    }
}
