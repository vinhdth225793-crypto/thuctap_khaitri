<?php

namespace Database\Seeders;

use App\Models\GiangVien;
use App\Models\HocVien;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDemoAccounts();
        $this->seedSampleStudents();

        $this->call([
            SampleTeacherSeeder::class,
            // NganHangCauHoiSeeder::class,
        ]);
    }

    /**
     * Tạo 3 tài khoản chính cho hội đồng/giảng viên đánh giá đăng nhập.
     * Email và mật khẩu khớp với README.md mục "Tài khoản demo".
     */
    private function seedDemoAccounts(): void
    {
        $admin = NguoiDung::firstOrCreate(
            ['email' => 'admin@khaitri.local'],
            [
                'ho_ten' => 'Quản trị viên LearnTest',
                'mat_khau' => Hash::make('password'),
                'vai_tro' => 'admin',
                'so_dien_thoai' => '0296351111',
                'dia_chi' => 'Trung tâm Tin học Khai Trí — An Giang',
                'trang_thai' => true,
            ]
        );

        $gv = NguoiDung::firstOrCreate(
            ['email' => 'gv1@khaitri.local'],
            [
                'ho_ten' => 'Nguyễn Thị Lan Quyên',
                'mat_khau' => Hash::make('password'),
                'vai_tro' => 'giang_vien',
                'so_dien_thoai' => '0296352222',
                'dia_chi' => 'Khoa CNTT — Đại học An Giang',
                'trang_thai' => true,
            ]
        );

        GiangVien::firstOrCreate(
            ['nguoi_dung_id' => $gv->id],
            [
                'chuyen_nganh' => 'Công nghệ Thông tin',
                'hoc_vi' => 'Thạc sĩ',
                'so_gio_day' => 0,
                'mo_ta_ngan' => 'Giảng viên hướng dẫn — Khoa CNTT, Đại học An Giang.',
                'hien_thi_trang_chu' => true,
            ]
        );

        $hv = NguoiDung::firstOrCreate(
            ['email' => 'hv1@khaitri.local'],
            [
                'ho_ten' => 'Mai Phạm Phước Vinh',
                'mat_khau' => Hash::make('password'),
                'vai_tro' => 'hoc_vien',
                'so_dien_thoai' => '0296353333',
                'dia_chi' => 'TP. Long Xuyên, An Giang',
                'trang_thai' => true,
            ]
        );

        HocVien::firstOrCreate(
            ['nguoi_dung_id' => $hv->id],
            [
                'lop_niem_khoa' => 'DH22TH',
                'nganh_hoc' => 'Công nghệ Thông tin',
                'diem_trung_binh' => 0.0,
            ]
        );

        $this->command?->info('  ✓ Tài khoản demo: admin@khaitri.local / gv1@khaitri.local / hv1@khaitri.local (mật khẩu: password)');
    }

    /**
     * Thêm 10 học viên mẫu để hội đồng có đủ dữ liệu chấm các tính năng danh sách/lọc/phân trang.
     */
    private function seedSampleStudents(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $email = "hv-sample-{$i}@khaitri.local";

            $hv = NguoiDung::firstOrCreate(
                ['email' => $email],
                [
                    'ho_ten' => "Học viên mẫu {$i}",
                    'mat_khau' => Hash::make('password'),
                    'vai_tro' => 'hoc_vien',
                    'so_dien_thoai' => '09'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'dia_chi' => "Địa chỉ học viên mẫu {$i}",
                    'trang_thai' => true,
                ]
            );

            HocVien::firstOrCreate(
                ['nguoi_dung_id' => $hv->id],
                [
                    'lop_niem_khoa' => 'DH22TH',
                    'nganh_hoc' => 'Công nghệ Thông tin',
                    'diem_trung_binh' => 0.0,
                ]
            );
        }
    }
}
