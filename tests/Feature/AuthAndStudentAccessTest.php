<?php

namespace Tests\Feature;

use App\Models\BaiGiang;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndStudentAccessTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_disabled_user_cannot_log_in(): void
    {
        $user = NguoiDung::create([
            'ho_ten' => 'Hoc vien bi khoa',
            'email' => 'disabled@example.com',
            'mat_khau' => bcrypt('password123'),
            'vai_tro' => 'hoc_vien',
            'trang_thai' => false,
        ]);

        HocVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        $response = $this->post(route('xu-ly-dang-nhap'), [
            'email' => 'disabled@example.com',
            'mat_khau' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_student_can_view_published_lecture_detail(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $module = $this->createModule($course);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
        ]);

        $baiGiang = BaiGiang::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'Bai giang duoc cong bo',
            'loai_bai_giang' => 'tai_lieu',
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_DA_DUYET,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_DA_CONG_BO,
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.bai-giang.show', $baiGiang->id))
            ->assertOk()
            ->assertSeeText('Bai giang duoc cong bo');
    }

    private function createStudent(): NguoiDung
    {
        $index = $this->sequence++;

        $user = NguoiDung::create([
            'ho_ten' => 'Hoc vien ' . $index,
            'email' => 'hocvien' . $index . '@example.com',
            'mat_khau' => bcrypt('password123'),
            'vai_tro' => 'hoc_vien',
            'trang_thai' => true,
        ]);

        HocVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return $user;
    }

    private function createCourse(): KhoaHoc
    {
        $index = $this->sequence++;

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_nhom_nganh' => 'Nhom nganh ' . $index,
            'trang_thai' => true,
        ]);

        return KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_khoa_hoc' => 'Khoa hoc ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
        ]);
    }

    private function createModule(KhoaHoc $course): ModuleHoc
    {
        return ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => $course->ma_khoa_hoc . '-M1',
            'ten_module' => 'Module 1',
            'thu_tu_module' => 1,
            'so_buoi' => 1,
            'trang_thai' => true,
        ]);
    }
}
