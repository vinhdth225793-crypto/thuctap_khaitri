<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use App\Models\DiemDanhGiangVien;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSevenCheckOutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    private int $sequence = 1;

    public function test_teacher_finish_session_on_time(): void
    {
        // Tiết 2-3: 08:30 - 10:30
        Carbon::setTestNow('2026-04-03 08:30:00');
        [$user, $teacher, $schedule] = $this->createStartedSession();

        // Finish đúng 10:30
        Carbon::setTestNow('2026-04-03 10:30:00');
        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.finish', $schedule->id))
            ->assertSessionHasNoErrors();

        $schedule->refresh();
        $attendance = DiemDanhGiangVien::where('lich_hoc_id', $schedule->id)->first();

        $this->assertSame('hoan_thanh', $schedule->trang_thai);
        $this->assertSame('2026-04-03 10:30:00', $schedule->actual_finished_at->toDateTimeString());
        $this->assertSame(DiemDanhGiangVien::CHECK_OUT_DUNG_HAN, $attendance->check_out_status);
        $this->assertSame('binh_thuong', $schedule->teacher_monitoring_status);

        Carbon::setTestNow();
    }

    public function test_teacher_finish_session_late_but_within_window(): void
    {
        // 08:30 - 10:30
        Carbon::setTestNow('2026-04-03 08:30:00');
        [$user, $teacher, $schedule] = $this->createStartedSession();

        // Finish 11:15 (trễ 45p, vẫn trong khung +60p)
        Carbon::setTestNow('2026-04-03 11:15:00');
        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.finish', $schedule->id))
            ->assertSessionHasNoErrors();

        $schedule->refresh();
        $attendance = DiemDanhGiangVien::where('lich_hoc_id', $schedule->id)->first();

        $this->assertSame('hoan_thanh', $schedule->trang_thai);
        $this->assertSame(DiemDanhGiangVien::CHECK_OUT_DUNG_HAN, $attendance->check_out_status);

        Carbon::setTestNow();
    }

    public function test_teacher_finish_session_early_flags_violation(): void
    {
        // 08:30 - 10:30, threshold sớm là 10:00
        Carbon::setTestNow('2026-04-03 08:30:00');
        [$user, $teacher, $schedule] = $this->createStartedSession();

        // Finish 09:50 (sớm 40 phút > 30 phút threshold)
        Carbon::setTestNow('2026-04-03 09:50:00');
        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.finish', $schedule->id))
            ->assertSessionHasNoErrors();

        $schedule->refresh();
        $attendance = DiemDanhGiangVien::where('lich_hoc_id', $schedule->id)->first();

        $this->assertSame(DiemDanhGiangVien::CHECK_OUT_DONG_SOM, $attendance->check_out_status);
        $this->assertSame(40, $attendance->early_leave_minutes);
        $this->assertSame(LichHoc::TEACHER_MONITORING_DONG_SOM, $schedule->teacher_monitoring_status);
        $this->assertStringContainsString('Giao vien dong buoi som 40 phut', $schedule->teacher_monitoring_note);

        Carbon::setTestNow();
    }

    public function test_teacher_finish_session_after_deadline_fails(): void
    {
        // 08:30 - 10:30, deadline là 11:30 (+60p)
        Carbon::setTestNow('2026-04-03 08:30:00');
        [$user, $teacher, $schedule] = $this->createStartedSession();

        // Cố tình finish lúc 11:40
        Carbon::setTestNow('2026-04-03 11:40:00');
        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.finish', $schedule->id));

        $schedule->refresh();
        // Trạng thái vẫn là dang_hoc vì bị chặn
        $this->assertSame('dang_hoc', $schedule->trang_thai);

        Carbon::setTestNow();
    }

    private function createStartedSession(): array
    {
        $admin = $this->createUser('admin');
        [$user, $teacher] = $this->createTeacher();
        [$course, $module, $schedule] = $this->createAssignedSchedule($admin, $teacher);

        // Giả lập đã check-in lúc 08:30
        $this->actingAs($user)
            ->post(route('giang-vien.buoi-hoc.teacher-attendance.start', $schedule->id));
        
        $schedule->refresh();
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
