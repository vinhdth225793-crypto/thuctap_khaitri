<?php

namespace Database\Seeders;

use App\Models\KhoaHoc;
use App\Models\CauHoi;
use App\Models\DapAnCauHoi;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;

class NganHangCauHoiSeeder extends Seeder
{
    public function run(): void
    {
        $admin = NguoiDung::where('vai_tro', 'admin')->first();
        if (!$admin) {
            return;
        }

        // Tạo một nhóm ngành và khóa học mẫu nếu chưa có để có dữ liệu seed câu hỏi
        $nhomNganh = \App\Models\NhomNganh::firstOrCreate(['ten_nhom_nganh' => 'Công nghệ thông tin']);
        
        $khoaHoc = KhoaHoc::firstOrCreate(
            ['ma_khoa_hoc' => 'PROG101'],
            [
                'nhom_nganh_id' => $nhomNganh->id,
                'ten_khoa_hoc' => 'Lập trình căn bản',
                'mo_ta_ngan' => 'Khóa học dành cho người mới bắt đầu.',
                'trang_thai' => true,
                'created_by' => $admin->id
            ]
        );

        $questions = [
            [
                'noi_dung' => "Laravel là một PHP Framework theo mô hình nào?",
                'answers' => [
                    ['noi_dung' => 'MVC', 'la_dap_an_dung' => true],
                    ['noi_dung' => 'MVP', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'MVVM', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'Singleton', 'la_dap_an_dung' => false],
                ],
            ],
            [
                'noi_dung' => 'Trong Laravel, Eloquent là gì?',
                'answers' => [
                    ['noi_dung' => 'Một hệ quản trị CSDL', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'Một ORM (Object-Relational Mapper)', 'la_dap_an_dung' => true],
                    ['noi_dung' => 'Một Template Engine', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'Một Router', 'la_dap_an_dung' => false],
                ],
            ],
        ];

        foreach ($questions as $item) {
            $cauHoi = CauHoi::create([
                'khoa_hoc_id' => $khoaHoc->id,
                'nguoi_tao_id' => $admin->id,
                'noi_dung' => $item['noi_dung'],
                'loai_cau_hoi' => 'trac_nghiem',
                'muc_do' => 'de',
            ]);

            foreach ($item['answers'] as $answer) {
                DapAnCauHoi::create([
                    'cau_hoi_id' => $cauHoi->id,
                    'noi_dung_dap_an' => $answer['noi_dung'],
                    'la_dap_an_dung' => $answer['la_dap_an_dung'],
                ]);
            }
        }
    }
}
