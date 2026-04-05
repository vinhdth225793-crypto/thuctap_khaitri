<?php

namespace Tests\Feature;

use App\Models\DiemDanh;
use App\Models\DiemDanhGiangVien;
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
use Tests\TestCase;

class TeacherAttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_teacher_can_start_online_session_attendance(): void
    {
        Carbon::setTestNow('2026-04-03 08:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, $module, $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $response = $this
            ->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id));

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('diem_danh_giang_vien', [
            'lich_hoc_id' => $schedule->id,
            'giang_vien_id' => $teacher->id,
            'trang_thai' => 'da_checkin',
            'hinh_thuc_hoc' => 'online',
        ]);

        $attendance = DiemDanhGiangVien::first();
        $this->assertSame('2026-04-03 08:00:00', $attendance->thoi_gian_bat_dau_day?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-03 08:00:00', $attendance->thoi_gian_mo_live?->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_teacher_can_finish_online_session_attendance_after_starting(): void
    {
        Carbon::setTestNow('2026-04-03 08:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id));

        Carbon::setTestNow('2026-04-03 10:15:00');

        $response = $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.finish', $schedule->id));

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $attendance = DiemDanhGiangVien::first();

        $this->assertSame('hoan_thanh', $attendance->trang_thai);
        $this->assertSame('2026-04-03 10:15:00', $attendance->thoi_gian_ket_thuc_day?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-03 10:15:00', $attendance->thoi_gian_tat_live?->format('Y-m-d H:i:s'));
        $this->assertSame(135, $attendance->tong_thoi_luong_day_phut);

        Carbon::setTestNow();
    }

    public function test_teacher_can_check_in_and_out_direct_session_without_live_timestamps(): void
    {
        Carbon::setTestNow('2026-04-03 13:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [, , $schedule] = $this->createAssignedSchedule($admin, $teacher, [
            'hinh_thuc' => 'truc_tiep',
            'phong_hoc' => 'Phong A101',
            'link_online' => null,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.check-in', $schedule->id))
            ->assertSessionHas('success');

        Carbon::setTestNow('2026-04-03 15:00:00');

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.check-out', $schedule->id))
            ->assertSessionHas('success');

        $attendance = DiemDanhGiangVien::firstOrFail();

        $this->assertSame('truc_tiep', $attendance->hinh_thuc_hoc);
        $this->assertSame('hoan_thanh', $attendance->trang_thai);
        $this->assertNull($attendance->thoi_gian_mo_live);
        $this->assertNull($attendance->thoi_gian_tat_live);
        $this->assertSame(120, $attendance->tong_thoi_luong_day_phut);

        Carbon::setTestNow();
    }

    public function test_teacher_cannot_finish_online_session_without_starting_first(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $response = $this
            ->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $schedule->khoa_hoc_id))
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.finish', $schedule->id));

        $response
            ->assertRedirect()
            ->assertSessionHasErrors('teacher_attendance');

        $this->assertDatabaseCount('diem_danh_giang_vien', 0);
    }

    public function test_teacher_cannot_check_in_same_session_twice(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $this->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.check-in', $schedule->id))
            ->assertSessionHas('success');

        $response = $this->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.check-in', $schedule->id));

        $response
            ->assertRedirect(route('giang-vien.khoa-hoc.show', $course->id))
            ->assertSessionHasErrors('teacher_attendance');

        $this->assertDatabaseCount('diem_danh_giang_vien', 1);
    }

    public function test_unassigned_teacher_cannot_check_in_session(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$otherTeacherUser] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $response = $this->actingAs($otherTeacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.check-in', $schedule->id));

        $response
            ->assertRedirect(route('giang-vien.khoa-hoc.show', $course->id))
            ->assertSessionHasErrors('teacher_attendance');

        $this->assertDatabaseCount('diem_danh_giang_vien', 0);
    }

    public function test_teacher_cannot_check_in_completed_session(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher, [
            'trang_thai' => 'hoan_thanh',
        ]);

        $response = $this->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.check-in', $schedule->id));

        $response
            ->assertRedirect(route('giang-vien.khoa-hoc.show', $course->id))
            ->assertSessionHasErrors('teacher_attendance');

        $this->assertDatabaseCount('diem_danh_giang_vien', 0);
    }

    public function test_teacher_can_start_teaching_session_from_course_card(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $response = $this
            ->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.start', $schedule->id));

        $response
            ->assertRedirect(route('giang-vien.khoa-hoc.show', $course->id))
            ->assertSessionHas('success');

        $schedule->refresh();

        $this->assertSame('dang_hoc', $schedule->trang_thai);
        $this->assertDatabaseHas('diem_danh_giang_vien', [
            'lich_hoc_id' => $schedule->id,
            'giang_vien_id' => $teacher->id,
            'trang_thai' => 'da_checkin',
        ]);
    }

    public function test_teacher_can_finish_teaching_session_after_starting_it(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $this->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.start', $schedule->id))
            ->assertSessionHas('success');

        $response = $this->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.finish', $schedule->id));

        $response
            ->assertRedirect(route('giang-vien.khoa-hoc.show', $course->id))
            ->assertSessionHas('success');

        $schedule->refresh();

        $this->assertSame('hoan_thanh', $schedule->trang_thai);
        $this->assertDatabaseHas('diem_danh_giang_vien', [
            'lich_hoc_id' => $schedule->id,
            'giang_vien_id' => $teacher->id,
            'trang_thai' => 'hoan_thanh',
        ]);
    }

    public function test_teacher_cannot_finish_teaching_session_before_starting_it(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $response = $this
            ->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.finish', $schedule->id));

        $response
            ->assertRedirect(route('giang-vien.khoa-hoc.show', $course->id))
            ->assertSessionHas('error');

        $schedule->refresh();

        $this->assertSame('cho', $schedule->trang_thai);
    }

    public function test_teacher_course_show_page_renders_new_timeline_clusters(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course] = $this->createAssignedOnlineSchedule($admin, $teacher);

        $response = $this->actingAs($teacherUser)
            ->get(route('giang-vien.khoa-hoc.show', $course->id));

        $response
            ->assertOk()
            ->assertSee('Cụm 1', escape: false)
            ->assertSee('Phòng live nội bộ', escape: false)
            ->assertSee('Bắt đầu buổi học', escape: false)
            ->assertSee('Chưa bắt đầu', escape: false);
    }

    public function test_admin_can_view_teacher_attendance_dashboard(): void
    {
        Carbon::setTestNow('2026-04-03 09:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, $module, $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);

        DiemDanhGiangVien::create([
            'lich_hoc_id' => $schedule->id,
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'hinh_thuc_hoc' => 'online',
            'thoi_gian_bat_dau_day' => now()->subHours(2),
            'thoi_gian_ket_thuc_day' => now(),
            'thoi_gian_mo_live' => now()->subHours(2),
            'thoi_gian_tat_live' => now(),
            'tong_thoi_luong_day_phut' => 120,
            'trang_thai' => 'hoan_thanh',
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.diem-danh.index', ['tab' => 'giang-vien']));

        $response
            ->assertOk()
            ->assertSee('Quản lý theo tuần', escape: false)
            ->assertSee('Danh sách đã điểm danh trong tuần', escape: false)
            ->assertSee($course->ten_khoa_hoc)
            ->assertSee($teacherUser->ho_ten)
            ->assertSee('Hoàn thành', escape: false);

        Carbon::setTestNow();
    }

    public function test_admin_teacher_dashboard_prunes_history_older_than_one_month(): void
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$currentCourse, $currentModule, $currentSchedule] = $this->createAssignedSchedule($admin, $teacher, [
            'ngay_hoc' => '2026-04-14',
            'buoi_so' => 2,
        ]);
        [$oldCourse, $oldModule, $oldSchedule] = $this->createAssignedSchedule($admin, $teacher, [
            'ngay_hoc' => '2026-03-01',
            'buoi_so' => 3,
        ]);

        $currentAttendance = DiemDanhGiangVien::create([
            'lich_hoc_id' => $currentSchedule->id,
            'khoa_hoc_id' => $currentCourse->id,
            'module_hoc_id' => $currentModule->id,
            'giang_vien_id' => $teacher->id,
            'hinh_thuc_hoc' => 'online',
            'thoi_gian_bat_dau_day' => now()->subHour(),
            'thoi_gian_ket_thuc_day' => now(),
            'trang_thai' => 'hoan_thanh',
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
        ]);

        $expiredAttendance = DiemDanhGiangVien::create([
            'lich_hoc_id' => $oldSchedule->id,
            'khoa_hoc_id' => $oldCourse->id,
            'module_hoc_id' => $oldModule->id,
            'giang_vien_id' => $teacher->id,
            'hinh_thuc_hoc' => 'online',
            'thoi_gian_bat_dau_day' => now()->subMonthNoOverflow(),
            'thoi_gian_ket_thuc_day' => now()->subMonthNoOverflow()->addHour(),
            'trang_thai' => 'hoan_thanh',
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.diem-danh.index', ['tab' => 'giang-vien']))
            ->assertOk()
            ->assertSee($currentCourse->ten_khoa_hoc);

        $this->assertDatabaseHas('diem_danh_giang_vien', [
            'id' => $currentAttendance->id,
        ]);

        $this->assertDatabaseMissing('diem_danh_giang_vien', [
            'id' => $expiredAttendance->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_admin_teacher_attendance_detail_keeps_weekly_filters_in_back_link(): void
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, $module, $schedule] = $this->createAssignedSchedule($admin, $teacher, [
            'ngay_hoc' => '2026-04-14',
        ]);

        DiemDanhGiangVien::create([
            'lich_hoc_id' => $schedule->id,
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'hinh_thuc_hoc' => 'online',
            'thoi_gian_bat_dau_day' => now()->subHour(),
            'thoi_gian_ket_thuc_day' => now(),
            'trang_thai' => 'hoan_thanh',
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
        ]);

        $backLink = route('admin.diem-danh.index', [
            'tab' => 'giang-vien',
            'week_start' => '2026-04-14',
            'khoa_hoc_id' => $course->id,
            'giang_vien_id' => $teacher->id,
            'trang_thai' => 'da_ket_thuc',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.diem-danh.giang-vien.show', array_merge(
                [$schedule->id, $teacher->id],
                [
                    'week_start' => '2026-04-14',
                    'khoa_hoc_id' => $course->id,
                    'giang_vien_id' => $teacher->id,
                    'trang_thai' => 'da_ket_thuc',
                ]
            )))
            ->assertOk()
            ->assertSee($backLink);

        Carbon::setTestNow();
    }

    public function test_admin_can_view_student_attendance_dashboard(): void
    {
        $admin = $this->createUser('admin');
        [, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);
        $student = $this->createStudent();

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        DiemDanh::create([
            'lich_hoc_id' => $schedule->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'co_mat',
            'ghi_chu' => 'Den dung gio',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.diem-danh.index', ['tab' => 'hoc-vien']));

        $response
            ->assertOk()
            ->assertSee($student->ho_ten)
            ->assertSee($course->ten_khoa_hoc)
            ->assertSee('Co mat', escape: false);
    }

    public function test_student_attendance_flow_still_works_after_teacher_attendance_is_started(): void
    {
        Carbon::setTestNow('2026-04-03 08:00:00');

        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);
        $student = $this->createStudent();

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id));

        $response = $this
            ->actingAs($teacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $course->id))
            ->post(route('giang-vien.buoi-hoc.diem-danh.store', $schedule->id), [
                'attendance' => [
                    [
                        'hoc_vien_id' => $student->ma_nguoi_dung,
                        'trang_thai' => 'co_mat',
                        'ghi_chu' => 'Co mat day du',
                    ],
                ],
            ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('diem_danh', [
            'lich_hoc_id' => $schedule->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'co_mat',
        ]);

        $this->assertDatabaseHas('diem_danh_giang_vien', [
            'lich_hoc_id' => $schedule->id,
            'giang_vien_id' => $teacher->id,
            'trang_thai' => 'da_checkin',
        ]);

        Carbon::setTestNow();
    }

    public function test_student_attendance_modal_payload_returns_summary_with_excused_students(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedOnlineSchedule($admin, $teacher);
        $student = $this->createStudent();

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        DiemDanh::create([
            'lich_hoc_id' => $schedule->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'co_phep',
            'ghi_chu' => 'Bao truoc voi giang vien',
        ]);

        $this->actingAs($teacherUser)
            ->getJson(route('giang-vien.buoi-hoc.diem-danh.show', $schedule->id))
            ->assertOk()
            ->assertJsonPath('summary.total_students', 1)
            ->assertJsonPath('summary.marked_students', 1)
            ->assertJsonPath('summary.co_phep', 1)
            ->assertJsonPath('data.0.trang_thai', 'co_phep');
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

    private function createAssignedOnlineSchedule(NguoiDung $admin, GiangVien $teacher): array
    {
        return $this->createAssignedSchedule($admin, $teacher, [
            'hinh_thuc' => 'online',
            'link_online' => 'https://example.com/online-room',
        ]);
    }

    private function createAssignedSchedule(NguoiDung $admin, GiangVien $teacher, array $scheduleOverrides = []): array
    {
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);

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
            'link_online' => 'https://example.com/online-room',
            'trang_thai' => 'cho',
        ], $scheduleOverrides));

        return [$course, $module, $schedule];
    }
}
