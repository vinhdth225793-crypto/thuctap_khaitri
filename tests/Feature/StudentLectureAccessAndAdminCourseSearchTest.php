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
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class StudentLectureAccessAndAdminCourseSearchTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_stopped_student_cannot_view_published_lecture_but_completed_student_can(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin, [
            'ten_khoa_hoc' => 'Khoa hoc bai giang',
            'trang_thai_van_hanh' => 'dang_day',
        ]);
        $module = $this->createModule($course);
        $stoppedStudent = $this->createStudent();
        $completedStudent = $this->createStudent();

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $stoppedStudent->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'ngung_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $completedStudent->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'hoan_thanh',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $baiGiang = BaiGiang::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'Bai giang da cong bo',
            'loai_bai_giang' => 'tai_lieu',
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_DA_DUYET,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_DA_CONG_BO,
        ]);

        $this->actingAs($stoppedStudent)
            ->get(route('hoc-vien.bai-giang.show', $baiGiang->id))
            ->assertRedirect(route('hoc-vien.khoa-hoc-cua-toi'));

        $this->actingAs($completedStudent)
            ->get(route('hoc-vien.bai-giang.show', $baiGiang->id))
            ->assertOk()
            ->assertSeeText('Bai giang da cong bo');
    }

    public function test_admin_course_search_keeps_each_status_bucket_isolated(): void
    {
        $admin = $this->createUser('admin');

        $matchingDangDay = $this->createCourse($admin, [
            'ma_khoa_hoc' => 'KH-MATCH-DANG-DAY',
            'ten_khoa_hoc' => 'Search Leak Course',
            'trang_thai_van_hanh' => 'dang_day',
        ]);

        $matchingSanSang = $this->createCourse($admin, [
            'ma_khoa_hoc' => 'KH-MATCH-SAN-SANG',
            'ten_khoa_hoc' => 'Search Leak Course',
            'trang_thai_van_hanh' => 'san_sang',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.khoa-hoc.index', [
                'search' => 'Search Leak Course',
                'tab' => 'dang_day',
            ]));

        $response->assertOk();

        $response->assertViewHas('khoaHocDangDay', function (LengthAwarePaginator $paginator) use ($matchingDangDay, $matchingSanSang) {
            $ids = collect($paginator->items())->pluck('id')->all();

            return in_array($matchingDangDay->id, $ids, true)
                && !in_array($matchingSanSang->id, $ids, true);
        });

        $response->assertViewHas('khoaHocSanSang', function (LengthAwarePaginator $paginator) use ($matchingDangDay, $matchingSanSang) {
            $ids = collect($paginator->items())->pluck('id')->all();

            return in_array($matchingSanSang->id, $ids, true)
                && !in_array($matchingDangDay->id, $ids, true);
        });
    }

    private function createUser(string $role, array $overrides = []): NguoiDung
    {
        $index = $this->sequence++;

        return NguoiDung::create(array_merge([
            'ho_ten' => strtoupper($role) . ' ' . $index,
            'email' => $role . $index . '@example.com',
            'mat_khau' => bcrypt('password123'),
            'vai_tro' => $role,
            'trang_thai' => true,
        ], $overrides));
    }

    private function createStudent(): NguoiDung
    {
        $user = $this->createUser('hoc_vien');

        HocVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return $user;
    }

    private function createCourse(NguoiDung $creator, array $overrides = []): KhoaHoc
    {
        $index = $this->sequence++;
        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_nhom_nganh' => 'Nhom nganh ' . $index,
            'trang_thai' => true,
        ]);

        return KhoaHoc::create(array_merge([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_khoa_hoc' => 'Khoa hoc ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
            'created_by' => $creator->ma_nguoi_dung,
        ], $overrides));
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
