<?php

namespace Database\Seeders;

use App\Models\DapAnCauHoi;
use App\Models\KhoaHoc;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use Illuminate\Database\Seeder;

class NganHangCauHoiSeeder extends Seeder
{
    public function run(): void
    {
        $admin = NguoiDung::where('vai_tro', 'admin')->first();

        if (! $admin) {
            return;
        }

        $adminId = $admin->getKey();

        $nhomNganh = NhomNganh::firstOrCreate(
            ['ten_nhom_nganh' => 'Cong nghe thong tin'],
            [
                'ma_nhom_nganh' => 'NN-CNTT',
                'trang_thai' => true,
            ]
        );

        $khoaHoc = KhoaHoc::firstOrCreate(
            ['ma_khoa_hoc' => 'PROG101'],
            [
                'nhom_nganh_id' => $nhomNganh->id,
                'ten_khoa_hoc' => 'Lap trinh can ban',
                'mo_ta_ngan' => 'Khoa hoc danh cho nguoi moi bat dau.',
                'trang_thai' => true,
                'created_by' => $adminId,
            ]
        );

        $questions = [
            [
                'noi_dung' => 'Laravel la mot PHP Framework theo mo hinh nao?',
                'answers' => [
                    ['noi_dung' => 'MVC', 'la_dap_an_dung' => true],
                    ['noi_dung' => 'MVP', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'MVVM', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'Singleton', 'la_dap_an_dung' => false],
                ],
            ],
            [
                'noi_dung' => 'Trong Laravel, Eloquent la gi?',
                'answers' => [
                    ['noi_dung' => 'Mot he quan tri CSDL', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'Mot ORM (Object-Relational Mapper)', 'la_dap_an_dung' => true],
                    ['noi_dung' => 'Mot Template Engine', 'la_dap_an_dung' => false],
                    ['noi_dung' => 'Mot Router', 'la_dap_an_dung' => false],
                ],
            ],
        ];

        foreach ($questions as $item) {
            $cauHoi = NganHangCauHoi::updateOrCreate(
                [
                    'khoa_hoc_id' => $khoaHoc->id,
                    'noi_dung' => $item['noi_dung'],
                ],
                [
                    'nguoi_tao_id' => $adminId,
                    'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
                    'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
                    'muc_do' => 'de',
                    'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
                    'co_the_tai_su_dung' => true,
                ]
            );

            $cauHoi->dapAns()->delete();

            foreach ($item['answers'] as $index => $answer) {
                DapAnCauHoi::create([
                    'ngan_hang_cau_hoi_id' => $cauHoi->id,
                    'ky_hieu' => chr(65 + $index),
                    'noi_dung' => $answer['noi_dung'],
                    'is_dap_an_dung' => $answer['la_dap_an_dung'],
                    'thu_tu' => $index + 1,
                ]);
            }
        }
    }
}
