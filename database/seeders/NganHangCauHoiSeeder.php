<?php

namespace Database\Seeders;

use App\Models\KhoaHoc;
use App\Models\NganHangCauHoi;
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

        $khoaHocs = KhoaHoc::with('moduleHocs')->get();
        if ($khoaHocs->isEmpty()) {
            return;
        }

        foreach ($khoaHocs as $khoaHoc) {
            $module = $khoaHoc->moduleHocs->first();
            $questions = [
                [
                    'noi_dung' => "Trong khóa học '{$khoaHoc->ten_khoa_hoc}', khái niệm cơ bản nhất là gì?",
                    'answers' => [
                        ['ky_hieu' => 'A', 'noi_dung' => 'Khái niệm nền tảng', 'is_dap_an_dung' => true],
                        ['ky_hieu' => 'B', 'noi_dung' => 'Không có khái niệm nào', 'is_dap_an_dung' => false],
                        ['ky_hieu' => 'C', 'noi_dung' => 'Khái niệm nâng cao', 'is_dap_an_dung' => false],
                        ['ky_hieu' => 'D', 'noi_dung' => 'Khái niệm thực hành', 'is_dap_an_dung' => false],
                    ],
                ],
                [
                    'noi_dung' => 'Laravel là một PHP Framework theo mô hình nào?',
                    'answers' => [
                        ['ky_hieu' => 'A', 'noi_dung' => 'MVC', 'is_dap_an_dung' => true],
                        ['ky_hieu' => 'B', 'noi_dung' => 'MVP', 'is_dap_an_dung' => false],
                        ['ky_hieu' => 'C', 'noi_dung' => 'MVVM', 'is_dap_an_dung' => false],
                        ['ky_hieu' => 'D', 'noi_dung' => 'Singleton', 'is_dap_an_dung' => false],
                    ],
                ],
            ];

            foreach ($questions as $index => $item) {
                if (NganHangCauHoi::isDuplicate($khoaHoc->id, $item['noi_dung'])) {
                    continue;
                }

                $cauHoi = NganHangCauHoi::create([
                    'khoa_hoc_id' => $khoaHoc->id,
                    'module_hoc_id' => $module?->id,
                    'nguoi_tao_id' => $admin->ma_nguoi_dung,
                    'ma_cau_hoi' => 'SEED-' . $khoaHoc->id . '-' . ($index + 1),
                    'noi_dung' => $item['noi_dung'],
                    'loai_cau_hoi' => 'trac_nghiem',
                    'muc_do' => 'de',
                    'diem_mac_dinh' => 1,
                    'trang_thai' => 'san_sang',
                    'co_the_tai_su_dung' => true,
                ]);

                $cauHoi->dapAns()->createMany(
                    collect($item['answers'])->values()->map(function (array $answer, int $answerIndex) {
                        return [
                            'ky_hieu' => $answer['ky_hieu'],
                            'noi_dung' => $answer['noi_dung'],
                            'is_dap_an_dung' => $answer['is_dap_an_dung'],
                            'thu_tu' => $answerIndex + 1,
                        ];
                    })->all()
                );
            }
        }
    }
}
