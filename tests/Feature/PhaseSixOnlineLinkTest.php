<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSixOnlineLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    private int $sequence = 1;

    public function test_teacher_attendance_start_uses_existing_admin_link(): void
    {
        Carbon::setTestNow('2026-04-03 08:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $adminLink = 'https://meet.google.com/admin-manually-set-link';
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher, [
            'hinh_thuc' => 'online',
            'link_online' => $adminLink,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id))
            ->assertSessionHasNoErrors();

        $schedule->refresh();

        $this->assertSame($adminLink, $schedule->link_online);
        $this->assertSame(LichHoc::ONLINE_LINK_SOURCE_ADMIN_MANUAL, $schedule->online_link_source);

        Carbon::setTestNow();
    }

    public function test_teacher_attendance_start_generates_google_meet_link_if_missing(): void
    {
        Carbon::setTestNow('2026-04-03 08:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher, [
            'hinh_thuc' => 'online',
            'link_online' => null,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id))
            ->assertSessionHasNoErrors();

        $schedule->refresh();

        $this->assertNotNull($schedule->link_online);
        $this->assertStringContainsString('https://meet.google.com/', $schedule->link_online);
        $this->assertSame(LichHoc::ONLINE_LINK_SOURCE_TEACHER_GENERATED, $schedule->online_link_source);

        Carbon::setTestNow();
    }

    public function test_teacher_attendance_start_does_not_generate_link_for_offline_session(): void
    {
        Carbon::setTestNow('2026-04-03 08:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher, [
            'hinh_thuc' => 'truc_tiep',
            'link_online' => null,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id))
            ->assertSessionHasNoErrors();

        $schedule->refresh();

        $this->assertNull($schedule->link_online);
        $this->assertNull($schedule->online_link_source);

        Carbon::setTestNow();
    }

    private function createUser(string $role): NguoiDung
    {
        $index = $this->sequence++;

        return NguoiDung::create([
            'ho_ten' => strtoupper($role) . ' ' . $index,
            'email' => $role . $index . '@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => $role,
            'trang_thai' => true,
        ]);
    }

    private function createTeacher(): array
    {
        $user = $this->createUser('giang_vien');
        $giangVien = GiangVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return [$user, $giangVien];
    }

    private function createAssignedSchedule(NguoiDung $admin, GiangVien $teacher, array $scheduleOverrides = []): array
    {
        $index = $this->sequence++;
        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . $index,
            'ten_nhom_nganh' => 'Nhom nganh ' . $index,
            'trang_thai' => true,
        ]);

        $course = KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-' . $index,
            'ten_khoa_hoc' => 'Khoa hoc ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $module = ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => 'M-' . $index,
            'ten_module' => 'Module ' . $index,
            'thu_tu_module' => 1,
            'so_buoi' => 1,
            'trang_thai' => true,
        ]);

        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $schedule = LichHoc::create(array_merge([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_hoc' => '2026-04-03',
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '10:00:00',
            'thu_trong_tuan' => 6,
            'buoi_so' => 1,
            'hinh_thuc' => 'online',
            'trang_thai' => 'cho',
        ], $scheduleOverrides));

        return [$course, $module, $schedule];
    }
}
