<?php

namespace Database\Seeders;

use App\Models\BaiKiemTra;
use App\Models\DapAnCauHoi;
use App\Models\GiangVien;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KiemTraOnlineDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = NguoiDung::firstOrCreate(
            ['email' => 'admin.demo-kiemtra@example.com'],
            [
                'ho_ten' => 'Admin Demo Kiem Tra',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'admin',
                'trang_thai' => true,
            ]
        );

        $teacherUser = NguoiDung::firstOrCreate(
            ['email' => 'giangvien.demo-kiemtra@example.com'],
            [
                'ho_ten' => 'Giang vien Demo Kiem Tra',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'giang_vien',
                'trang_thai' => true,
            ]
        );

        $teacher = GiangVien::firstOrCreate([
            'nguoi_dung_id' => $teacherUser->ma_nguoi_dung,
        ]);

        $studentUser = NguoiDung::firstOrCreate(
            ['email' => 'hocvien.demo-kiemtra@example.com'],
            [
                'ho_ten' => 'Hoc vien Demo Kiem Tra',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'hoc_vien',
                'trang_thai' => true,
            ]
        );

        HocVien::firstOrCreate([
            'nguoi_dung_id' => $studentUser->ma_nguoi_dung,
        ]);

        $nhomNganh = NhomNganh::firstOrCreate(
            ['ma_nhom_nganh' => 'DEMO-KT'],
            ['ten_nhom_nganh' => 'Nhom nganh demo kiem tra online', 'trang_thai' => true]
        );

        $course = KhoaHoc::firstOrCreate(
            ['ma_khoa_hoc' => 'DEMO-KT-ONLINE'],
            [
                'nhom_nganh_id' => $nhomNganh->id,
                'ten_khoa_hoc' => 'Demo hoc tap va kiem tra online',
                'cap_do' => 'co_ban',
                'tong_so_module' => 1,
                'phuong_thuc_danh_gia' => 'theo_module',
                'ty_trong_diem_danh' => 20,
                'ty_trong_kiem_tra' => 80,
                'trang_thai' => true,
                'loai' => 'hoat_dong',
                'trang_thai_van_hanh' => 'dang_day',
                'created_by' => $admin->ma_nguoi_dung,
            ]
        );

        $module = ModuleHoc::firstOrCreate(
            ['ma_module' => 'DEMO-KT-ONLINE-M01'],
            [
                'khoa_hoc_id' => $course->id,
                'ten_module' => 'Module demo kiem tra',
                'thu_tu_module' => 1,
                'so_buoi' => 2,
                'trang_thai' => true,
            ]
        );

        $lichHoc = LichHoc::firstOrCreate(
            [
                'khoa_hoc_id' => $course->id,
                'module_hoc_id' => $module->id,
                'buoi_so' => 1,
            ],
            [
                'ngay_hoc' => now()->toDateString(),
                'gio_bat_dau' => '09:00:00',
                'gio_ket_thuc' => '11:00:00',
                'thu_trong_tuan' => 2,
                'hinh_thuc' => 'online',
                'link_online' => 'https://example.com/demo-kiem-tra',
                'trang_thai' => 'cho',
            ]
        );

        PhanCongModuleGiangVien::firstOrCreate([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giao_vien_id' => $teacher->id,
        ], [
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        HocVienKhoaHoc::firstOrCreate([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
        ], [
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $mcqQuestion = NganHangCauHoi::firstOrCreate(
            ['ma_cau_hoi' => 'DEMO-MCQ-001'],
            [
                'khoa_hoc_id' => $course->id,
                'module_hoc_id' => $module->id,
                'nguoi_tao_id' => $admin->ma_nguoi_dung,
                'noi_dung' => 'Laravel duoc viet bang ngon ngu nao?',
                'loai_cau_hoi' => 'trac_nghiem',
                'muc_do' => 'de',
                'diem_mac_dinh' => 4,
                'trang_thai' => 'san_sang',
                'co_the_tai_su_dung' => true,
            ]
        );

        if ($mcqQuestion->dapAns()->count() === 0) {
            DapAnCauHoi::insert([
                [
                    'ngan_hang_cau_hoi_id' => $mcqQuestion->id,
                    'ky_hieu' => 'A',
                    'noi_dung' => 'PHP',
                    'is_dap_an_dung' => true,
                    'thu_tu' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'ngan_hang_cau_hoi_id' => $mcqQuestion->id,
                    'ky_hieu' => 'B',
                    'noi_dung' => 'Python',
                    'is_dap_an_dung' => false,
                    'thu_tu' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $essayQuestion = NganHangCauHoi::firstOrCreate(
            ['ma_cau_hoi' => 'DEMO-ESSAY-001'],
            [
                'khoa_hoc_id' => $course->id,
                'module_hoc_id' => $module->id,
                'nguoi_tao_id' => $admin->ma_nguoi_dung,
                'noi_dung' => 'Trinh bay vai tro cua migration trong Laravel.',
                'loai_cau_hoi' => 'tu_luan',
                'muc_do' => 'trung_binh',
                'diem_mac_dinh' => 6,
                'trang_thai' => 'san_sang',
                'co_the_tai_su_dung' => true,
            ]
        );

        $exam = BaiKiemTra::firstOrCreate(
            ['tieu_de' => 'De demo hon hop module 1', 'khoa_hoc_id' => $course->id],
            [
                'module_hoc_id' => $module->id,
                'lich_hoc_id' => $lichHoc->id,
                'mo_ta' => 'De demo phuc vu bao ve do an.',
                'thoi_gian_lam_bai' => 20,
                'ngay_mo' => now()->subHour(),
                'ngay_dong' => now()->addDays(7),
                'pham_vi' => 'module',
                'loai_bai_kiem_tra' => 'module',
                'loai_noi_dung' => 'hon_hop',
                'trang_thai_duyet' => 'da_duyet',
                'trang_thai_phat_hanh' => 'phat_hanh',
                'tong_diem' => 10,
                'so_lan_duoc_lam' => 1,
                'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
                'nguoi_duyet_id' => $admin->ma_nguoi_dung,
                'duyet_luc' => now()->subMinutes(30),
                'phat_hanh_luc' => now()->subMinutes(25),
                'trang_thai' => true,
            ]
        );

        if ($exam->chiTietCauHois()->count() === 0) {
            $exam->chiTietCauHois()->createMany([
                [
                    'ngan_hang_cau_hoi_id' => $mcqQuestion->id,
                    'thu_tu' => 1,
                    'diem_so' => 4,
                    'bat_buoc' => true,
                ],
                [
                    'ngan_hang_cau_hoi_id' => $essayQuestion->id,
                    'thu_tu' => 2,
                    'diem_so' => 6,
                    'bat_buoc' => true,
                ],
            ]);
        }
    }
}
