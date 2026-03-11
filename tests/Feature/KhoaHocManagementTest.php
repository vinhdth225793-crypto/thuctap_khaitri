<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\MonHoc;
use App\Models\KhoaHoc;

class KhoaHocManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // disable auth middleware to simplify posting
        $this->withoutMiddleware();
    }

    /** @test */
    public function admin_can_create_a_course_with_modules()
    {
        // prepare a base subject
        $monHoc = MonHoc::create([
            'ma_mon_hoc' => 'TEST',
            'ten_mon_hoc' => 'Môn kiểm thử',
            'trang_thai' => 1,
        ]);

        $payload = [
            'mon_hoc_id' => $monHoc->id,
            'ten_khoa_hoc' => 'Khoá học số 1',
            'cap_do' => 'co_ban',
            'modules' => [
                ['ten_module' => 'Module 1', 'mo_ta' => 'Mô tả 1', 'thoi_luong_du_kien' => 2],
                ['ten_module' => 'Module 2', 'mo_ta' => 'Mô tả 2', 'thoi_luong_du_kien' => 3],
            ],
        ];

        $response = $this->post(route('admin.khoa-hoc.store'), $payload);

        $response->assertRedirect(route('admin.khoa-hoc.index'));
        $response->assertSessionHas('success');

        // verify database entries
        $this->assertDatabaseHas('khoa_hoc', [
            'ten_khoa_hoc' => 'Khoá học số 1',
            'mon_hoc_id' => $monHoc->id,
            'tong_so_module' => 2,
        ]);

        $course = KhoaHoc::where('ten_khoa_hoc', 'Khoá học số 1')->first();
        $this->assertNotNull($course);

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
