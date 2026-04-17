<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use App\Models\TeachingSessionAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTenAdminResolveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_admin_can_resolve_monitoring_violation()
    {
        $admin = $this->createUser('admin');
        [$user, $teacher, $schedule] = $this->createStartedSession();

        // Giả lập vi phạm
        $schedule->update([
            'teacher_monitoring_status' => 'vi_pham',
            'teacher_monitoring_note' => 'Vào trễ quá 15 phút.',
        ]);

        // Tạo alert
        TeachingSessionAlert::create([
            'lich_hoc_id' => $schedule->id,
            'giang_vien_id' => $teacher->id,
            'alert_key' => 'vao_tre_' . $schedule->id,
            'alert_type' => 'vao_tre',
            'severity' => 'danger',
            'status' => 'open',
            'tieu_de' => 'Giảng viên vào trễ',
            'noi_dung' => 'Vào trễ quá 15 phút.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.diem-danh.giang-vien.resolve', $schedule->id))
            ->assertRedirect();

        $schedule->refresh();
        $this->assertEquals('binh_thuong', $schedule->teacher_monitoring_status);
        $this->assertStringContainsString('Admin đã xác nhận xử lý', $schedule->teacher_monitoring_note);

        $alert = TeachingSessionAlert::where('lich_hoc_id', $schedule->id)->first();
        $this->assertEquals('resolved', $alert->status);
        $this->assertNotNull($alert->resolved_at);
    }

    private function createUser(string $role): NguoiDung
    {
        $id = uniqid();
        return NguoiDung::create([
            'ho_ten' => 'User ' . $role,
            'email' => $role . $id . '@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => $role,
            'trang_thai' => 1,
            'ma_nguoi_dung' => $id,
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

    private function createStartedSession(): array
    {
        $admin = $this->createUser('admin');
        [$user, $teacher] = $this->createTeacher();
        
        $nhomNganh = NhomNganh::create(['ten_nhom_nganh' => 'Test', 'ma_nhom_nganh' => 'TEST' . uniqid()]);
        $course = KhoaHoc::create([
            'ten_khoa_hoc' => 'Test Course',
            'ma_khoa_hoc' => 'TEST' . uniqid(),
            'nhom_nganh_id' => $nhomNganh->id,
            'loai' => 'hoat_dong',
        ]);
        $module = ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => 'MOD' . uniqid(),
            'ten_module' => 'Test Module',
            'so_buoi' => 10,
            'thu_tu_module' => 1,
        ]);
        PhanCongModuleGiangVien::create([
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'khoa_hoc_id' => $course->id,
            'trang_thai' => 'da_nhan',
        ]);
        $schedule = LichHoc::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_hoc' => now()->toDateString(),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '10:00:00',
            'trang_thai' => 'cho',
            'buoi_so' => 1,
            'hinh_thuc' => 'truc_tiep',
        ]);

        return [$user, $teacher, $schedule];
    }
}
