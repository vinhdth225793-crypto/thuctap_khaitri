<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LearningLogicTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_unassigned_teacher_cannot_view_attendance_for_another_teachers_session(): void
    {
        $admin = $this->createUser('admin');
        [$assignedTeacherUser, $assignedTeacher] = $this->createTeacher();
        [$otherTeacherUser] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module);

        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giao_vien_id' => $assignedTeacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $response = $this
            ->actingAs($otherTeacherUser)
            ->getJson(route('giang-vien.buoi-hoc.diem-danh.show', $lichHoc->id));

        $response->assertForbidden();
    }

    public function test_attendance_modal_only_returns_active_students_and_does_not_default_to_present(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module);
        $activeStudent = $this->createStudent();
        $stoppedStudent = $this->createStudent();

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $activeStudent->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $stoppedStudent->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'ngung_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giao_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $response = $this
            ->actingAs($teacherUser)
            ->getJson(route('giang-vien.buoi-hoc.diem-danh.show', $lichHoc->id));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.ma_nguoi_dung', $activeStudent->ma_nguoi_dung)
            ->assertJsonPath('data.0.trang_thai', null);
    }

    public function test_teacher_cannot_store_attendance_for_student_who_is_not_actively_enrolled(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module);
        $activeStudent = $this->createStudent();
        $stoppedStudent = $this->createStudent();

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $activeStudent->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $stoppedStudent->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'ngung_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giao_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $response = $this
            ->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', 1))
            ->post(route('giang-vien.buoi-hoc.diem-danh.store', $lichHoc->id), [
                'attendance' => [
                    [
                        'hoc_vien_id' => $stoppedStudent->ma_nguoi_dung,
                        'trang_thai' => 'co_mat',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors(['attendance.0.hoc_vien_id']);
        $this->assertDatabaseCount('diem_danh', 0);
    }

    public function test_stopped_student_cannot_access_course_detail_but_completed_student_can(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
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

        $this->actingAs($stoppedStudent)
            ->get(route('hoc-vien.chi-tiet-khoa-hoc', $course->id))
            ->assertRedirect(route('hoc-vien.khoa-hoc-cua-toi'));

        $this->actingAs($completedStudent)
            ->get(route('hoc-vien.chi-tiet-khoa-hoc', $course->id))
            ->assertOk();
    }

    public function test_online_session_can_be_joined_fifteen_minutes_before_start(): void
    {
        Carbon::setTestNow('2026-03-20 09:50:00');

        $lichHoc = new LichHoc([
            'ngay_hoc' => '2026-03-20',
            'gio_bat_dau' => '10:00:00',
            'gio_ket_thuc' => '11:00:00',
            'hinh_thuc' => 'online',
            'link_online' => 'https://example.com/meeting',
            'trang_thai' => 'cho',
        ]);

        $this->assertTrue($lichHoc->can_join_online);
        $this->assertSame('info', $lichHoc->online_join_state_color);

        Carbon::setTestNow();
    }

    public function test_sync_lich_hoc_status_command_updates_sessions_by_time(): void
    {
        Carbon::setTestNow('2026-03-20 10:00:00');

        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);

        $inProgress = $this->createLichHoc($course, $module, [
            'ngay_hoc' => '2026-03-20',
            'gio_bat_dau' => '09:30:00',
            'gio_ket_thuc' => '10:30:00',
            'trang_thai' => 'cho',
        ]);

        $finished = $this->createLichHoc($course, $module, [
            'ngay_hoc' => '2026-03-20',
            'gio_bat_dau' => '07:00:00',
            'gio_ket_thuc' => '08:00:00',
            'trang_thai' => 'cho',
            'buoi_so' => 2,
        ]);

        $future = $this->createLichHoc($course, $module, [
            'ngay_hoc' => '2026-03-20',
            'gio_bat_dau' => '12:00:00',
            'gio_ket_thuc' => '13:00:00',
            'trang_thai' => 'cho',
            'buoi_so' => 3,
        ]);

        Artisan::call('lich-hoc:sync-status');

        $this->assertSame('dang_hoc', $inProgress->fresh()->trang_thai);
        $this->assertSame('hoan_thanh', $finished->fresh()->trang_thai);
        $this->assertSame('cho', $future->fresh()->trang_thai);

        Carbon::setTestNow();
    }

    private function createUser(string $role, array $overrides = []): NguoiDung
    {
        $index = $this->sequence++;

        return NguoiDung::create(array_merge([
            'ho_ten' => strtoupper($role) . ' ' . $index,
            'email' => $role . $index . '@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => $role,
            'trang_thai' => true,
        ], $overrides));
    }

    private function createTeacher(): array
    {
        $user = $this->createUser('giang_vien');
        $giangVien = GiangVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return [$user, $giangVien];
    }

    private function createStudent(): NguoiDung
    {
        $user = $this->createUser('hoc_vien');

        HocVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return $user;
    }

    private function createCourse(NguoiDung $creator): KhoaHoc
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
            'created_by' => $creator->ma_nguoi_dung,
        ]);
    }

    private function createModule(KhoaHoc $course, int $order = 1): ModuleHoc
    {
        return ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => $course->ma_khoa_hoc . '-M' . $order,
            'ten_module' => 'Module ' . $order,
            'thu_tu_module' => $order,
            'so_buoi' => 3,
            'trang_thai' => true,
        ]);
    }

    private function createLichHoc(KhoaHoc $course, ModuleHoc $module, array $overrides = []): LichHoc
    {
        return LichHoc::create(array_merge([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'ngay_hoc' => now()->toDateString(),
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '11:00:00',
            'thu_trong_tuan' => 2,
            'buoi_so' => 1,
            'hinh_thuc' => 'online',
            'link_online' => 'https://example.com/class',
            'trang_thai' => 'cho',
        ], $overrides));
    }
}
