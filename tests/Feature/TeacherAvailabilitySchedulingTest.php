<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\GiangVienDonXinNghi;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TeacherAvailabilitySchedulingTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-03-30 09:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_can_create_schedule_with_period_inputs_inside_standard_window(): void
    {
        $admin = $this->createUser('admin');
        [, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course, 1);
        $studyDate = $this->nextDbWeekdayDate(2);

        $this->assignTeacher($admin, $teacher, $course, $module);

        $response = $this->actingAs($admin)
            ->from(route('admin.khoa-hoc.lich-hoc.index', $course->id))
            ->post(route('admin.khoa-hoc.lich-hoc.store', $course->id), [
                'module_hoc_id' => $module->id,
                'ngay_hoc' => $studyDate->toDateString(),
                'selected_tiets' => [1, 2],
                'hinh_thuc' => 'truc_tiep',
                'phong_hoc' => 'Phong A1',
                'giang_vien_id' => $teacher->id,
                'ghi_chu' => 'Buoi sang dau tien',
            ]);

        $response->assertRedirect(route('admin.khoa-hoc.lich-hoc.index', $course->id));

        $schedule = LichHoc::query()->firstOrFail();
        $this->assertSame($course->id, $schedule->khoa_hoc_id);
        $this->assertSame($module->id, $schedule->module_hoc_id);
        $this->assertSame($teacher->id, $schedule->giang_vien_id);
        $this->assertSame(1, $schedule->tiet_bat_dau);
        $this->assertSame(2, $schedule->tiet_ket_thuc);
        $this->assertSame('sang', $schedule->buoi_hoc);
        $this->assertSame('08:00', substr((string) $schedule->gio_bat_dau, 0, 5));
        $this->assertSame('09:50', substr((string) $schedule->gio_ket_thuc, 0, 5));
    }

    public function test_admin_cannot_create_schedule_outside_standard_hours(): void
    {
        $admin = $this->createUser('admin');
        [, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course, 1);
        $studyDate = $this->nextDbWeekdayDate(2);

        $this->assignTeacher($admin, $teacher, $course, $module);

        $response = $this->actingAs($admin)
            ->from(route('admin.khoa-hoc.lich-hoc.index', $course->id))
            ->post(route('admin.khoa-hoc.lich-hoc.store', $course->id), [
                'module_hoc_id' => $module->id,
                'ngay_hoc' => $studyDate->toDateString(),
                'gio_bat_dau' => '07:00',
                'gio_ket_thuc' => '09:00',
                'hinh_thuc' => 'truc_tiep',
                'phong_hoc' => 'Phong A1',
                'giang_vien_id' => $teacher->id,
            ]);

        $response
            ->assertRedirect(route('admin.khoa-hoc.lich-hoc.index', $course->id))
            ->assertSessionHasErrors(['ngay_hoc']);

        $this->assertDatabaseCount('lich_hoc', 0);
    }

    public function test_admin_cannot_create_schedule_on_weekend(): void
    {
        $admin = $this->createUser('admin');
        [, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course, 1);
        $studyDate = $this->nextDbWeekdayDate(7);

        $this->assignTeacher($admin, $teacher, $course, $module);

        $response = $this->actingAs($admin)
            ->from(route('admin.khoa-hoc.lich-hoc.index', $course->id))
            ->post(route('admin.khoa-hoc.lich-hoc.store', $course->id), [
                'module_hoc_id' => $module->id,
                'ngay_hoc' => $studyDate->toDateString(),
                'selected_tiets' => [5, 6],
                'hinh_thuc' => 'truc_tiep',
                'phong_hoc' => 'Phong A1',
                'giang_vien_id' => $teacher->id,
            ]);

        $response
            ->assertRedirect(route('admin.khoa-hoc.lich-hoc.index', $course->id))
            ->assertSessionHasErrors(['ngay_hoc']);

        $this->assertDatabaseCount('lich_hoc', 0);
    }

    public function test_admin_cannot_create_overlapping_schedule_for_same_teacher(): void
    {
        $admin = $this->createUser('admin');
        [, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $moduleOne = $this->createModule($course, 1);
        $moduleTwo = $this->createModule($course, 2);
        $studyDate = $this->nextDbWeekdayDate(2);

        $this->assignTeacher($admin, $teacher, $course, $moduleOne);
        $this->assignTeacher($admin, $teacher, $course, $moduleTwo, 2);
        $this->createSchedule($course, $moduleOne, $teacher, $studyDate, 1, 2, 1);

        $response = $this->actingAs($admin)
            ->from(route('admin.khoa-hoc.lich-hoc.index', $course->id))
            ->post(route('admin.khoa-hoc.lich-hoc.store', $course->id), [
                'module_hoc_id' => $moduleTwo->id,
                'ngay_hoc' => $studyDate->toDateString(),
                'selected_tiets' => [2, 3],
                'hinh_thuc' => 'truc_tiep',
                'phong_hoc' => 'Phong B1',
                'giang_vien_id' => $teacher->id,
            ]);

        $response
            ->assertRedirect(route('admin.khoa-hoc.lich-hoc.index', $course->id))
            ->assertSessionHasErrors(['gio_bat_dau']);

        $this->assertDatabaseCount('lich_hoc', 1);
    }

    public function test_admin_cannot_access_or_mutate_schedule_through_wrong_course_route(): void
    {
        $admin = $this->createUser('admin');
        [, $teacher] = $this->createTeacher();
        $courseOne = $this->createCourse($admin);
        $courseTwo = $this->createCourse($admin);
        $module = $this->createModule($courseOne, 1);
        $studyDate = $this->nextDbWeekdayDate(2);

        $this->assignTeacher($admin, $teacher, $courseOne, $module);
        $schedule = $this->createSchedule($courseOne, $module, $teacher, $studyDate, 5, 6, 1);

        $this->actingAs($admin)
            ->get(route('admin.khoa-hoc.lich-hoc.edit', [$courseTwo->id, $schedule->id]))
            ->assertNotFound();

        $this->actingAs($admin)
            ->put(route('admin.khoa-hoc.lich-hoc.update', [$courseTwo->id, $schedule->id]), [
                'ngay_hoc' => $studyDate->toDateString(),
                'selected_tiets' => [5, 6],
                'trang_thai' => 'cho',
                'hinh_thuc' => 'truc_tiep',
                'phong_hoc' => 'Phong 999',
                'giang_vien_id' => $teacher->id,
            ])
            ->assertNotFound();

        $this->actingAs($admin)
            ->delete(route('admin.khoa-hoc.lich-hoc.destroy', [$courseTwo->id, $schedule->id]))
            ->assertNotFound();
    }

    public function test_teacher_can_view_schedule_board_and_list(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course, 1);
        $studyDate = $this->nextDbWeekdayDate(2);

        $this->assignTeacher($admin, $teacher, $course, $module);
        $this->createSchedule($course, $module, $teacher, $studyDate, 9, 10, 1);

        $this->actingAs($teacherUser)
            ->get(route('giang-vien.lich-giang.index'))
            ->assertOk()
            ->assertSeeText('Thoi khoa bieu theo tuan')
            ->assertSeeText('Danh sach lich day trong tuan')
            ->assertSeeText($module->ten_module);
    }

    public function test_teacher_can_submit_leave_request_for_scheduled_class(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course, 1);
        $studyDate = $this->nextDbWeekdayDate(2);

        $this->assignTeacher($admin, $teacher, $course, $module);
        $schedule = $this->createSchedule($course, $module, $teacher, $studyDate, 9, 10, 1);

        $response = $this->actingAs($teacherUser)
            ->post(route('giang-vien.don-xin-nghi.store'), [
                'lich_hoc_id' => $schedule->id,
                'ly_do' => 'Ban dot xuat, can xin nghi buoi nay.',
            ]);

        $response->assertRedirect(route('giang-vien.don-xin-nghi.index'));

        $leaveRequest = GiangVienDonXinNghi::query()->firstOrFail();
        $this->assertSame($teacher->id, $leaveRequest->giang_vien_id);
        $this->assertSame($schedule->id, $leaveRequest->lich_hoc_id);
        $this->assertSame(GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET, $leaveRequest->trang_thai);
        $this->assertSame(9, $leaveRequest->tiet_bat_dau);
        $this->assertSame(10, $leaveRequest->tiet_ket_thuc);
    }

    public function test_teacher_can_submit_manual_leave_request_by_periods(): void
    {
        [$teacherUser, $teacher] = $this->createTeacher();
        $studyDate = $this->nextDbWeekdayDate(4);

        $response = $this->actingAs($teacherUser)
            ->post(route('giang-vien.don-xin-nghi.store'), [
                'ngay_xin_nghi' => $studyDate->toDateString(),
                'selected_tiets' => [5, 6],
                'ly_do' => 'Can xin off mot phan buoi chieu.',
            ]);

        $response->assertRedirect(route('giang-vien.don-xin-nghi.index'));

        $this->assertDatabaseHas('giang_vien_don_xin_nghi', [
            'giang_vien_id' => $teacher->id,
            'tiet_bat_dau' => 5,
            'tiet_ket_thuc' => 6,
            'buoi_hoc' => 'chieu',
            'trang_thai' => GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET,
        ]);
    }

    public function test_teacher_cannot_submit_duplicate_overlapping_leave_request(): void
    {
        [$teacherUser, $teacher] = $this->createTeacher();
        $studyDate = $this->nextDbWeekdayDate(5);
        $this->createLeaveRequest($teacher, $studyDate, 5, 6);

        $response = $this->actingAs($teacherUser)
            ->from(route('giang-vien.don-xin-nghi.create'))
            ->post(route('giang-vien.don-xin-nghi.store'), [
                'ngay_xin_nghi' => $studyDate->toDateString(),
                'selected_tiets' => [6, 7],
                'ly_do' => 'Trung voi don da gui truoc do.',
            ]);

        $response
            ->assertRedirect(route('giang-vien.don-xin-nghi.create'))
            ->assertSessionHasErrors(['ngay_xin_nghi']);

        $this->assertDatabaseCount('giang_vien_don_xin_nghi', 1);
    }

    public function test_admin_can_approve_teacher_leave_request(): void
    {
        $admin = $this->createUser('admin');
        [, $teacher] = $this->createTeacher();
        $studyDate = $this->nextDbWeekdayDate(2);
        $leaveRequest = $this->createLeaveRequest($teacher, $studyDate, 9, 10);

        $response = $this->actingAs($admin)
            ->post(route('admin.giang-vien-don-xin-nghi.approve', $leaveRequest->id), [
                'ghi_chu_phan_hoi' => 'Da duyet. Vui long phoi hop de doi lich.',
            ]);

        $response->assertRedirect(route('admin.giang-vien-don-xin-nghi.show', $leaveRequest->id));

        $leaveRequest->refresh();
        $this->assertSame(GiangVienDonXinNghi::TRANG_THAI_DA_DUYET, $leaveRequest->trang_thai);
        $this->assertSame($admin->ma_nguoi_dung, $leaveRequest->nguoi_duyet_id);
        $this->assertSame('Da duyet. Vui long phoi hop de doi lich.', $leaveRequest->ghi_chu_phan_hoi);
    }

    public function test_admin_can_view_teacher_schedule_and_leave_request_shortcuts(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course, 1);
        $studyDate = $this->nextDbWeekdayDate(2);

        $this->assignTeacher($admin, $teacher, $course, $module);
        $this->createSchedule($course, $module, $teacher, $studyDate, 9, 10, 1);
        $this->createLeaveRequest($teacher, $studyDate, 9, 10);

        $this->actingAs($admin)
            ->get(route('admin.giang-vien.index'))
            ->assertOk()
            ->assertSeeText('Quan ly giang vien, lich day va don xin nghi')
            ->assertSee(route('admin.giang-vien.lich-giang.show', $teacher->id), false)
            ->assertSee(route('admin.giang-vien-don-xin-nghi.index'), false);

        $this->actingAs($admin)
            ->get(route('admin.giang-vien.lich-giang.show', $teacher->id))
            ->assertOk()
            ->assertSeeText($teacherUser->ho_ten)
            ->assertSeeText('Thoi khoa bieu theo tuan')
            ->assertSeeText('Don xin nghi gan day');
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

    /**
     * @return array{0:NguoiDung,1:GiangVien}
     */
    private function createTeacher(): array
    {
        $user = $this->createUser('giang_vien');

        $teacher = GiangVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
            'chuyen_nganh' => 'Lap trinh web',
        ]);

        return [$user, $teacher];
    }

    private function createCourse(NguoiDung $creator): KhoaHoc
    {
        $index = $this->sequence++;

        $group = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_nhom_nganh' => 'Nhom nganh ' . $index,
            'trang_thai' => true,
        ]);

        return KhoaHoc::create([
            'nhom_nganh_id' => $group->id,
            'ma_khoa_hoc' => 'KH-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_khoa_hoc' => 'Khoa hoc ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 2,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
            'created_by' => $creator->ma_nguoi_dung,
        ]);
    }

    private function createModule(KhoaHoc $course, int $order): ModuleHoc
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

    private function assignTeacher(NguoiDung $admin, GiangVien $teacher, KhoaHoc $course, ModuleHoc $module, int $suffix = 1): void
    {
        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_phan_cong' => now()->addMinutes($suffix),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
    }

    private function createSchedule(
        KhoaHoc $course,
        ModuleHoc $module,
        GiangVien $teacher,
        Carbon $studyDate,
        int $startPeriod,
        int $endPeriod,
        int $sessionNumber,
        string $mode = 'truc_tiep',
    ): LichHoc {
        $times = TeachingPeriodCatalog::timeRangeFromPeriods($startPeriod, $endPeriod);

        return LichHoc::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_hoc' => $studyDate->toDateString(),
            'gio_bat_dau' => $times['start_time'],
            'gio_ket_thuc' => $times['end_time'],
            'tiet_bat_dau' => $startPeriod,
            'tiet_ket_thuc' => $endPeriod,
            'buoi_hoc' => TeachingPeriodCatalog::resolveSessionFromRange($startPeriod, $endPeriod),
            'thu_trong_tuan' => $studyDate->dayOfWeek === Carbon::SUNDAY ? 8 : ($studyDate->dayOfWeek + 1),
            'buoi_so' => $sessionNumber,
            'hinh_thuc' => $mode,
            'phong_hoc' => $mode === 'truc_tiep' ? 'Phong 101' : null,
            'link_online' => $mode === 'online' ? 'https://example.com/lop-hoc' : null,
            'trang_thai' => 'cho',
        ]);
    }

    private function createLeaveRequest(
        GiangVien $teacher,
        Carbon $studyDate,
        int $startPeriod,
        int $endPeriod,
        string $status = GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET,
    ): GiangVienDonXinNghi {
        return GiangVienDonXinNghi::create([
            'giang_vien_id' => $teacher->id,
            'ngay_xin_nghi' => $studyDate->toDateString(),
            'buoi_hoc' => TeachingPeriodCatalog::resolveSessionFromRange($startPeriod, $endPeriod),
            'tiet_bat_dau' => $startPeriod,
            'tiet_ket_thuc' => $endPeriod,
            'ly_do' => 'Ban ca nhan',
            'trang_thai' => $status,
        ]);
    }

    private function nextDbWeekdayDate(int $dbWeekday): Carbon
    {
        return match ($dbWeekday) {
            2 => now()->next(Carbon::MONDAY),
            3 => now()->next(Carbon::TUESDAY),
            4 => now()->next(Carbon::WEDNESDAY),
            5 => now()->next(Carbon::THURSDAY),
            6 => now()->next(Carbon::FRIDAY),
            7 => now()->next(Carbon::SATURDAY),
            8 => now()->next(Carbon::SUNDAY),
            default => now()->next(Carbon::MONDAY),
        };
    }
}


