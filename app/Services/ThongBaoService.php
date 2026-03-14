<?php

namespace App\Services;

use App\Models\ThongBao;
use App\Models\NguoiDung;
use App\Models\GiangVien;
use App\Models\ModuleHoc;
use App\Models\KhoaHoc;

class ThongBaoService
{
    /**
     * Gửi thông báo phân công cho GV
     * Được gọi trong kichHoatMau() ở Phase A
     */
    public static function guiPhanCongGV(
        GiangVien $gv,
        ModuleHoc $module,
        KhoaHoc   $khoaHoc
    ): void {
        ThongBao::create([
            'nguoi_nhan_id' => $gv->nguoi_dung_id,
            'tieu_de'       => "Bạn được phân công dạy module: {$module->ten_module}",
            'noi_dung'      => "Khóa học: {$khoaHoc->ten_khoa_hoc}\n"
                             . "Module: {$module->ten_module} (Mã: {$module->ma_module})\n"
                             . "Dự kiến khai giảng: "
                             . ($khoaHoc->ngay_khai_giang ? $khoaHoc->ngay_khai_giang->format('d/m/Y') : '—')
                             . "\nVui lòng vào mục \"Xác nhận phân công\" để xác nhận dạy.",
            'loai'          => 'phan_cong',
            'url'           => route('giang-vien.khoa-hoc'),
        ]);
    }

    /**
     * Gửi thông báo cho Admin khi TẤT CẢ GV xác nhận
     */
    public static function guiSanSangChoAdmin(KhoaHoc $khoaHoc): void
    {
        $admins = NguoiDung::where('vai_tro', 'admin')->get();
        foreach ($admins as $admin) {
            ThongBao::create([
                'nguoi_nhan_id' => $admin->ma_nguoi_dung,
                'tieu_de'       => "✅ Lớp học sẵn sàng: {$khoaHoc->ten_khoa_hoc}",
                'noi_dung'      => "Tất cả giảng viên đã xác nhận dạy cho khóa học "
                                 . "\"{$khoaHoc->ten_khoa_hoc}\".\n"
                                 . "Bạn có thể xác nhận mở lớp chính thức.",
                'loai'          => 'xac_nhan_gv',
                'url'           => route('admin.khoa-hoc.show', $khoaHoc->id),
            ]);
        }
    }
}
