<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use App\Models\ThongBao;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseNineAttendanceReminderTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->withoutExceptionHandling();
    }

    public function test_teacher_finish_sets_deadline_and_notifies(): void
    {
        // 08:30 - 10:30
        Carbon::setTestNow('2026-04-03 08:30:00');
        [$user, $teacher, $schedule] = $this->createStartedSession();

        // Finish lúc 10:30
        Carbon::setTestNow('2026-04-03 10:30:00');
        $response = $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.finish', $schedule->id));
        
        if ($schedule->fresh()->trang_thai !== 'hoan_thanh') {
            dump("DEBUG: RESPONSE STATUS: " . $response->getStatusCode());
            if ($response->isRedirect()) {
                dump("DEBUG: REDIRECT TO: " . $response->headers->get('Location'));
            }
            // dump($response->getContent()); // Too large
        }

        $schedule->refresh();
        
        // Deadline phải là 10:30 + 15p = 10:45
        if ($schedule->attendance_deadline_at === null) {
            dump("DEBUG: Trạng thái buổi học: " . $schedule->trang_thai);
            dump("DEBUG: Actual started: " . ($schedule->actual_started_at?->toDateTimeString() ?? 'NULL'));
            dump("DEBUG: Actual finished: " . ($schedule->actual_finished_at?->toDateTimeString() ?? 'NULL'));
            dump("DEBUG: Attendance count: " . \App\Models\DiemDanhGiangVien::count());
            $attendance = \App\Models\DiemDanhGiangVien::first();
            if ($attendance) {
                dump("DEBUG: Attendance status: " . $attendance->trang_thai);
                dump("DEBUG: Attendance start: " . $attendance->thoi_gian_bat_dau_day?->toDateTimeString());
                dump("DEBUG: Attendance end: " . $attendance->thoi_gian_ket_thuc_day?->toDateTimeString());
            }
        }
        
        $this->assertNotNull($schedule->attendance_deadline_at);
        $this->assertSame('2026-04-03 10:45:00', $schedule->attendance_deadline_at->toDateTimeString());

        // Kiểm tra thông báo cho giảng viên
        $this->assertDatabaseHas('thong_bao', [
            'nguoi_nhan_id' => $user->ma_nguoi_dung,
            'tieu_de' => 'Nhắc nhở: Điểm danh học viên'
        ]);

        Carbon::setTestNow();
    }

    public function test_teacher_report_on_time(): void
    {
        Carbon::setTestNow('2026-04-03 10:30:00');
        [$user, $teacher, $schedule] = $this->createFinishedSessionWithDeadline('2026-04-03 10:45:00');

        // Báo cáo lúc 10:40 (trong hạn)
        Carbon::setTestNow('2026-04-03 10:40:00');
        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.diem-danh.report', $schedule->id), [
                'bao_cao_giang_vien' => 'Dạy tốt, học viên đầy đủ.'
            ])
            ->assertSessionHasNoErrors();

        $schedule->refresh();
        $this->assertSame('da_bao_cao', $schedule->trang_thai_bao_cao);

        Carbon::setTestNow();
    }

    public function test_teacher_report_late_flags_overdue(): void
    {
        Carbon::setTestNow('2026-04-03 10:30:00');
        [$user, $teacher, $schedule] = $this->createFinishedSessionWithDeadline('2026-04-03 10:45:00');

        // Báo cáo lúc 10:50 (quá hạn 5 phút)
        Carbon::setTestNow('2026-04-03 10:50:00');
        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.diem-danh.report', $schedule->id), [
                'bao_cao_giang_vien' => 'Dạy ổn, nộp muộn tí.'
            ])
            ->assertSessionHasNoErrors();

        $schedule->refresh();
        $this->assertSame('da_bao_cao_muon', $schedule->trang_thai_bao_cao);

        Carbon::setTestNow();
    }

    private function createStartedSession(): array
    {
        $admin = $this->createUser('admin');
        [$user, $teacher] = $this->createTeacher();
        [$course, $module, $schedule] = $this->createAssignedSchedule($admin, $teacher);

        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id));
        
        $schedule->refresh();
        return [$user, $teacher, $schedule];
    }

    private function createFinishedSessionWithDeadline(string $deadlineAt): array
    {
        [$user, $teacher, $schedule] = $this->createStartedSession();
        
        $schedule->update([
            'trang_thai' => 'hoan_thanh',
            'actual_finished_at' => now(),
            'attendance_deadline_at' => Carbon::parse($deadlineAt)
        ]);

        return [$user, $teacher, $schedule];
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

    private function createAssignedSchedule(NguoiDung $admin, GiangVien $teacher): array
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

        $schedule = LichHoc::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_hoc' => '2026-04-03',
            'gio_bat_dau' => '08:30:00',
            'gio_ket_thuc' => '10:30:00',
            'thu_trong_tuan' => 6,
            'buoi_so' => 1,
            'hinh_thuc' => 'truc_tiep',
            'trang_thai' => 'cho',
        ]);

        return [$course, $module, $schedule];
    }
}
