<?php

namespace Tests\Feature;

use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
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

    public function test_admin_group_index_counts_all_courses_in_each_group(): void
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Count',
            'email' => 'admin-count@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN002',
            'ten_nhom_nganh' => 'Nhom nganh dem khoa hoc',
            'trang_thai' => true,
        ]);

        KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-COUNT-001',
            'ten_khoa_hoc' => 'Khoa hoc cho mo',
            'cap_do' => 'co_ban',
            'trang_thai' => true,
            'loai' => 'mau',
            'trang_thai_van_hanh' => 'cho_mo',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.nhom-nganh.index'));

        $response->assertOk();
        $response->assertViewHas('nhomNganhs', function ($nhomNganhs) use ($nhomNganh) {
            $item = $nhomNganhs->getCollection()->firstWhere('id', $nhomNganh->id);

            return $item !== null && (int) $item->khoa_hocs_count === 1;
        });
        $response->assertSee('1 khóa');
    }

    public function test_admin_training_index_pages_render_with_shared_filters(): void
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin UI',
            'email' => 'admin-ui@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN003',
            'ten_nhom_nganh' => 'Nhom nganh giao dien',
            'trang_thai' => true,
        ]);

        $khoaHoc = KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-UI-001',
            'ten_khoa_hoc' => 'Khoa hoc giao dien',
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'trang_thai' => true,
            'loai' => 'mau',
            'trang_thai_van_hanh' => 'cho_mo',
        ]);

        ModuleHoc::create([
            'khoa_hoc_id' => $khoaHoc->id,
            'ma_module' => 'KH-UI-001M01',
            'ten_module' => 'Module giao dien',
            'thu_tu_module' => 1,
            'thoi_luong_du_kien' => 120,
            'trang_thai' => true,
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.nhom-nganh.index', [
            'search' => 'giao dien',
            'trang_thai' => '1',
        ]))
            ->assertOk()
            ->assertSee('Lọc dữ liệu')
            ->assertSee('Đặt lại');

        $this->get(route('admin.khoa-hoc.index', [
            'tab' => 'mau',
            'search' => 'KH-UI',
            'nhom_nganh_id' => $nhomNganh->id,
        ]))
            ->assertOk()
            ->assertSee('Lọc dữ liệu')
            ->assertSee('Đặt lại');

        $this->get(route('admin.module-hoc.index', [
            'search' => 'Module giao dien',
            'khoa_hoc_id' => $khoaHoc->id,
        ]))
            ->assertOk()
            ->assertSee('Lọc dữ liệu')
            ->assertSee('Đặt lại');
    }

    public function test_course_creation_defaults_module_duration_to_ninety_minutes_when_blank(): void
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Duration',
            'email' => 'admin-duration@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN004',
            'ten_nhom_nganh' => 'Nhom nganh thoi luong',
            'trang_thai' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.khoa-hoc.store'), [
                'nhom_nganh_id' => $nhomNganh->id,
                'ma_khoa_hoc' => 'KH-DURATION-001',
                'ten_khoa_hoc' => 'Khoa hoc duration',
                'cap_do' => 'co_ban',
                'modules' => [
                    ['ten_module' => 'Module mac dinh 90', 'thoi_luong_du_kien' => '', 'mo_ta' => ''],
                ],
            ]);

        $course = KhoaHoc::where('ma_khoa_hoc', 'KH-DURATION-001')->firstOrFail();

        $response->assertRedirect(route('admin.khoa-hoc.show', $course->id));
        $this->assertDatabaseHas('module_hoc', [
            'khoa_hoc_id' => $course->id,
            'ten_module' => 'Module mac dinh 90',
            'thoi_luong_du_kien' => 90,
        ]);
    }

    public function test_legacy_null_module_duration_is_resolved_to_default_duration_label(): void
    {
        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN005',
            'ten_nhom_nganh' => 'Nhom nganh legacy duration',
            'trang_thai' => true,
        ]);

        $course = KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-LEGACY-001',
            'ten_khoa_hoc' => 'Khoa hoc legacy duration',
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'trang_thai' => true,
            'loai' => 'mau',
            'trang_thai_van_hanh' => 'cho_mo',
        ]);

        $module = ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => 'KH-LEGACY-001M01',
            'ten_module' => 'Module legacy',
            'thu_tu_module' => 1,
            'thoi_luong_du_kien' => null,
            'trang_thai' => true,
        ])->fresh();

        $this->assertSame(90, $module->thoi_luong_du_kien);
        $this->assertSame('1h 30p', $module->thoi_luong_du_kien_label);
    }

    public function test_admin_course_students_page_uses_remote_search_modal(): void
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Enrollment',
            'email' => 'admin-enrollment@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN006',
            'ten_nhom_nganh' => 'Nhom nganh hoc vien',
            'trang_thai' => true,
        ]);

        $course = KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-STUDENT-001',
            'ten_khoa_hoc' => 'Khoa hoc hoc vien',
            'cap_do' => 'co_ban',
            'tong_so_module' => 0,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.khoa-hoc.hoc-vien.index', $course->id))
            ->assertOk()
            ->assertSee('Tìm học viên từ hệ thống')
            ->assertSee('searchHocVien')
            ->assertDontSee('checkAllHocVien');
    }

    public function test_admin_student_search_prioritizes_name_matches_and_excludes_enrolled_students(): void
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Search',
            'email' => 'admin-search@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN007',
            'ten_nhom_nganh' => 'Nhom nganh search',
            'trang_thai' => true,
        ]);

        $course = KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-SEARCH-001',
            'ten_khoa_hoc' => 'Khoa hoc search',
            'cap_do' => 'co_ban',
            'tong_so_module' => 0,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
        ]);

        $nameStartsWith = NguoiDung::create([
            'ho_ten' => 'An Nguyen',
            'email' => 'an-nguyen@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'hoc_vien',
            'trang_thai' => true,
        ]);

        $nameContains = NguoiDung::create([
            'ho_ten' => 'Tran An',
            'email' => 'tran-an@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'hoc_vien',
            'trang_thai' => true,
        ]);

        $emailOnly = NguoiDung::create([
            'ho_ten' => 'Bich Le',
            'email' => 'an-mail@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'hoc_vien',
            'trang_thai' => true,
        ]);

        $enrolled = NguoiDung::create([
            'ho_ten' => 'An Da Ghi Danh',
            'email' => 'an-enrolled@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'hoc_vien',
            'trang_thai' => true,
        ]);

        $course->hocViens()->attach($enrolled->ma_nguoi_dung, [
            'ngay_tham_gia' => now(),
            'trang_thai' => 'dang_hoc',
            'ghi_chu' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.khoa-hoc.hoc-vien.search', [
            'khoaHocId' => $course->id,
            'q' => 'an',
        ]));

        $response->assertOk()->assertJsonCount(3, 'data');

        $ids = array_column($response->json('data'), 'id');

        $this->assertSame([
            $nameStartsWith->ma_nguoi_dung,
            $nameContains->ma_nguoi_dung,
            $emailOnly->ma_nguoi_dung,
        ], $ids);
        $this->assertNotContains($enrolled->ma_nguoi_dung, $ids);
    }
}
