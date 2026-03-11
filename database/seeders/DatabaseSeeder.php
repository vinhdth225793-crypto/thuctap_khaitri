<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Tạo admin
        NguoiDung::create([
            'ho_ten' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'mat_khau' => Hash::make('12345678'),
            'vai_tro' => 'admin',
            'so_dien_thoai' => '0912345678',
            'dia_chi' => 'Hà Nội',
            'trang_thai' => true,
        ]);

        // Tạo giảng viên
        $gv = NguoiDung::create([
            'ho_ten' => 'Nguyễn Văn Giảng',
            'email' => 'giangvien@example.com',
            'mat_khau' => Hash::make('12345678'),
            'vai_tro' => 'giang_vien',
            'so_dien_thoai' => '0923456789',
            'dia_chi' => 'TP Hồ Chí Minh',
            'trang_thai' => true,
        ]);
        // tạo bản ghi trong bảng giang_vien
        if ($gv) {
            \App\Models\GiangVien::create([
                'nguoi_dung_id' => $gv->ma_nguoi_dung,
                'chuyen_nganh' => 'Công nghệ phần mềm',
                'hoc_vi' => 'Thạc sĩ',
                'so_gio_day' => '120',
            ]);
        }

        // Tạo học viên
        $hocvien = NguoiDung::create([
            'ho_ten' => 'Trần Thị Học',
            'email' => 'hocvien@example.com',
            'mat_khau' => Hash::make('12345678'),
            'vai_tro' => 'hoc_vien',
            'so_dien_thoai' => '0934567890',
            'dia_chi' => 'Đà Nẵng',
            'trang_thai' => true,
        ]);
        // tạo bản ghi trong bảng hoc_vien
        if ($hocvien) {
            \App\Models\HocVien::create([
                'nguoi_dung_id' => $hocvien->ma_nguoi_dung,
                'lop' => 'Công nghệ thông tin',
                'nganh' => 'Khoa học máy tính',
                'diem_trung_binh' => 0.0,
            ]);
        }

        // Thêm 10 học viên mẫu
        for ($i = 1; $i <= 10; $i++) {
            $hv = NguoiDung::create([
                'ho_ten' => 'Học viên ' . $i,
                'email' => 'hocvien' . $i . '@example.com',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'hoc_vien',
                'so_dien_thoai' => '09' . rand(10000000, 99999999),
                'dia_chi' => 'Địa chỉ ' . $i,
                'trang_thai' => true,
            ]);
            if ($hv) {
                \App\Models\HocVien::create([
                    'nguoi_dung_id' => $hv->ma_nguoi_dung,
                    'lop' => 'Lớp ' . $i,
                    'nganh' => 'Ngành ' . $i,
                    'diem_trung_binh' => 0.0,
                ]);
            }
        }
    }
}