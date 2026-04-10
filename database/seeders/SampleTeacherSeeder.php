<?php

namespace Database\Seeders;

use App\Models\GiangVien;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345678');

        $teachers = [
            [
                'ho_ten' => 'Nguyen Minh Anh',
                'email' => 'gv.minhanh@example.com',
                'so_dien_thoai' => '0901000001',
                'dia_chi' => 'TP Ho Chi Minh',
                'chuyen_nganh' => 'Lap trinh Web',
                'hoc_vi' => 'Thac si',
                'mo_ta_ngan' => 'Giang vien phu trach cac hoc phan nen tang ve HTML, CSS, JavaScript va Laravel.',
                'hien_thi_trang_chu' => true,
            ],
            [
                'ho_ten' => 'Tran Quoc Bao',
                'email' => 'gv.quocbao@example.com',
                'so_dien_thoai' => '0901000002',
                'dia_chi' => 'Da Nang',
                'chuyen_nganh' => 'Kiem thu phan mem',
                'hoc_vi' => 'Ky su',
                'mo_ta_ngan' => 'Phu trach cac module ve testing, quy trinh dam bao chat luong va kiem thu he thong.',
                'hien_thi_trang_chu' => false,
            ],
            [
                'ho_ten' => 'Le Thu Ha',
                'email' => 'gv.thuha@example.com',
                'so_dien_thoai' => '0901000003',
                'dia_chi' => 'Ha Noi',
                'chuyen_nganh' => 'Phan tich du lieu',
                'hoc_vi' => 'Thac si',
                'mo_ta_ngan' => 'Huong dan hoc vien khai thac du lieu, truc quan hoa va ung dung Python trong phan tich.',
                'hien_thi_trang_chu' => true,
            ],
            [
                'ho_ten' => 'Pham Duc Huy',
                'email' => 'gv.duchuy@example.com',
                'so_dien_thoai' => '0901000004',
                'dia_chi' => 'Can Tho',
                'chuyen_nganh' => 'Co so du lieu',
                'hoc_vi' => 'Tien si',
                'mo_ta_ngan' => 'Chuyen ve thiet ke co so du lieu, toi uu truy van va van hanh he thong quan tri du lieu.',
                'hien_thi_trang_chu' => true,
            ],
            [
                'ho_ten' => 'Vo Ngoc Lan',
                'email' => 'gv.ngoclan@example.com',
                'so_dien_thoai' => '0901000005',
                'dia_chi' => 'Hue',
                'chuyen_nganh' => 'UI UX Design',
                'hoc_vi' => 'Cu nhan',
                'mo_ta_ngan' => 'Dong hanh cung hoc vien trong cac module ve nghien cuu nguoi dung, wireframe va prototyping.',
                'hien_thi_trang_chu' => false,
            ],
        ];

        foreach ($teachers as $teacher) {
            $user = NguoiDung::updateOrCreate(
                ['email' => $teacher['email']],
                [
                    'ho_ten' => $teacher['ho_ten'],
                    'mat_khau' => $password,
                    'vai_tro' => 'giang_vien',
                    'so_dien_thoai' => $teacher['so_dien_thoai'],
                    'dia_chi' => $teacher['dia_chi'],
                    'trang_thai' => true,
                ]
            );

            GiangVien::updateOrCreate(
                ['nguoi_dung_id' => $user->id], // Changed from ma_nguoi_dung to id
                [
                    'chuyen_nganh' => $teacher['chuyen_nganh'],
                    'hoc_vi' => $teacher['hoc_vi'],
                    'hien_thi_trang_chu' => $teacher['hien_thi_trang_chu'],
                    'mo_ta_ngan' => $teacher['mo_ta_ngan'],
                ]
            );
        }
    }
}
