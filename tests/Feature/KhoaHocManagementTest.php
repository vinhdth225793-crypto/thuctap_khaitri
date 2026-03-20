<?php

namespace Tests\Feature;

use App\Models\KhoaHoc;
use App\Models\NhomNganh;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KhoaHocManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_course_with_modules(): void
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Test',
            'email' => 'admin@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN001',
            'ten_nhom_nganh' => 'Nhom nganh test',
            'trang_thai' => true,
        ]);

        $payload = [
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-TEST-001',
            'ten_khoa_hoc' => 'Khoa hoc so 1',
            'cap_do' => 'co_ban',
            'modules' => [
                ['ten_module' => 'Module 1', 'mo_ta' => 'Mo ta 1', 'thoi_luong_du_kien' => 2],
                ['ten_module' => 'Module 2', 'mo_ta' => 'Mo ta 2', 'thoi_luong_du_kien' => 3],
            ],
        ];

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.khoa-hoc.store'), $payload);

        $course = KhoaHoc::where('ma_khoa_hoc', 'KH-TEST-001')->first();

        $this->assertNotNull($course);
        $response->assertRedirect(route('admin.khoa-hoc.show', $course->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('khoa_hoc', [
            'id' => $course->id,
            'nhom_nganh_id' => $nhomNganh->id,
            'ten_khoa_hoc' => 'Khoa hoc so 1',
            'tong_so_module' => 2,
            'loai' => 'mau',
        ]);

        $this->assertDatabaseHas('module_hoc', [
            'khoa_hoc_id' => $course->id,
            'ten_module' => 'Module 1',
        ]);

        $this->assertDatabaseHas('module_hoc', [
            'khoa_hoc_id' => $course->id,
            'ten_module' => 'Module 2',
        ]);
    }
}
