<?php

namespace Database\Seeders;

use App\Models\KhoaHoc;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;

class NganHangCauHoiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = NguoiDung::where('vai_tro', 'admin')->first();
        if (!$admin) return;

        $khoaHocs = KhoaHoc::all();
        if ($khoaHocs->isEmpty()) return;

        foreach ($khoaHocs as $khoaHoc) {
            $questions = [
                [
                    'khoa_hoc_id' => $khoaHoc->id,
                    'noi_dung_cau_hoi' => "Trong khóa học '{$khoaHoc->ten_khoa_hoc}', khái niệm cơ bản nhất là gì?",
                    'dap_an_sai_1' => 'Không có khái niệm nào',
                    'dap_an_sai_2' => 'Khái niệm nâng cao',
                    'dap_an_sai_3' => 'Khái niệm thực hành',
                    'dap_an_dung' => 'Khái niệm nền tảng',
                    'nguoi_tao_id' => $admin->ma_nguoi_dung,
                ],
                [
                    'khoa_hoc_id' => $khoaHoc->id,
                    'noi_dung_cau_hoi' => 'Laravel là một PHP Framework theo mô hình nào?',
                    'dap_an_sai_1' => 'MVP',
                    'dap_an_sai_2' => 'MVVM',
                    'dap_an_sai_3' => 'Singleton',
                    'dap_an_dung' => 'MVC',
                    'nguoi_tao_id' => $admin->ma_nguoi_dung,
                ],
            ];

            foreach ($questions as $q) {
                if (!NganHangCauHoi::isDuplicate($q['khoa_hoc_id'], $q['noi_dung_cau_hoi'])) {
                    NganHangCauHoi::create($q);
                }
            }
        }
    }
}
