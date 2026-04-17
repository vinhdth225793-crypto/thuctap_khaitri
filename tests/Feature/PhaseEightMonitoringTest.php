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
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PhaseEightMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    protected function setUp(): void
    {
        parent::setUp();
        // Giả lập có 1 admin để nhận thông báo
        $this->createUser('admin');
    }

    public function test_monitor_detects_late_entry(): void
    {
        // Tiết 2-3: 08:30 - 10:30
        // Giả sử bây giờ là 08:40 (trễ 10 phút)
        Carbon::setTestNow('2026-04-03 08:40:00');
        
        $admin = NguoiDung::where('vai_tro', 'admin')->first();
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher);

        // Chạy command giám sát
        Artisan::call('teaching:monitor');

        $schedule->refresh();
        $this->assertSame(LichHoc::TEACHER_MONITORING_VAO_TRE, $schedule->teacher_monitoring_status);
        $this->assertStringContainsString('Buoi hoc da bat dau duoc 10 phut nhung chua ghi nhan check-in', $schedule->teacher_monitoring_note);
        
        // Kiểm tra có thông báo cho admin
        $this->assertDatabaseHas('thong_bao', [
            'nguoi_nhan_id' => $admin->ma_nguoi_dung,
            'loai' => 'he_thong'
        ]);

        Carbon::setTestNow();
    }

    public function test_monitor_detects_no_show(): void
    {
        // Tiết 2-3: 08:30 - 10:30
        // Deadline checkout là 10:30 + 60p = 11:30
        // Giả sử bây giờ là 11:40 (đã qua deadline mà chưa check-in)
        Carbon::setTestNow('2026-04-03 11:40:00');
        
        $admin = NguoiDung::where('vai_tro', 'admin')->first();
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher);

        Artisan::call('teaching:monitor');

        $schedule->refresh();
        $this->assertSame(LichHoc::TEACHER_MONITORING_KHONG_DAY, $schedule->teacher_monitoring_status);
        
        Carbon::setTestNow();
    }

    public function test_monitor_detects_missing_checkout(): void
    {
        // Tiết 2-3: 08:30 - 10:30
        // Deadline checkout là 11:30
        // Giảng viên đã check-in lúc 08:30 nhưng đến 11:40 vẫn chưa check-out
        Carbon::setTestNow('2026-04-03 08:30:00');
        
        $admin = NguoiDung::where('vai_tro', 'admin')->first();
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher);

        // Giả lập check-in
        $schedule->update([
            'actual_started_at' => now(),
            'trang_thai' => 'dang_hoc'
        ]);

        // Nhảy đến lúc quá deadline
        Carbon::setTestNow('2026-04-03 11:40:00');

        Artisan::call('teaching:monitor');

        $schedule->refresh();
        $this->assertSame(LichHoc::TEACHER_MONITORING_CHUA_CHECKOUT, $schedule->teacher_monitoring_status);
        
        Carbon::setTestNow();
    }

    public function test_monitor_does_not_duplicate_alerts(): void
    {
        Carbon::setTestNow('2026-04-03 08:40:00');
        $admin = NguoiDung::where('vai_tro', 'admin')->first();
        [$teacherUser, $teacher] = $this->createTeacher();
        [$course, , $schedule] = $this->createAssignedSchedule($admin, $teacher);

        // Chạy lần 1
        Artisan::call('teaching:monitor');
        $count1 = ThongBao::count();

        // Chạy lần 2
        Artisan::call('teaching:monitor');
        $count2 = ThongBao::count();

        $this->assertSame($count1, $count2, 'Khong duoc tao thong bao trung lap cho cung mot vi pham');

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
