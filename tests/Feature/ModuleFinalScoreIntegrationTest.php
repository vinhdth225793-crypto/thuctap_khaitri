<?php

namespace Tests\Feature;

use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\DiemDanh;
use App\Models\GiangVien;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KetQuaHocTapChotLog;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ModuleFinalScoreIntegrationTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private int $sequence = 1;

    public function test_full_module_grading_and_finalization_flow(): void
    {
        // 1. Setup Data
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $studentUser = $this->createStudent();
        
        $course = $this->createCourse($admin);
        $course->update(['ty_trong_diem_danh' => 20, 'ty_trong_kiem_tra' => 80]);
        
        $module = $this->createModule($course);
        
        // Ghi danh học viên
        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
            'trang_thai' => 'dang_hoc',
        ]);

        // Phân công giảng viên
        $phanCong = PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'trang_thai' => 'da_nhan',
        ]);

        // Tạo 2 buổi học và điểm danh 1 buổi (50%) -> Điểm danh = 5.0
        $session1 = $this->createLichHoc($course, $module, ['buoi_so' => 1]);
        $session2 = $this->createLichHoc($course, $module, ['buoi_so' => 2]);
        
        DiemDanh::create([
            'lich_hoc_id' => $session1->id,
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
            'trang_thai' => 'co_mat',
        ]);

        // Tạo 1 bài kiểm tra nhỏ (8.0) và 1 bài kiểm tra lớn (6.0)
        // TB kiểm tra = (8 + 6) / 2 = 7.0
        $smallExam = BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'Bai nho',
            'loai_bai_kiem_tra' => 'buoi_hoc',
            'pham_vi' => 'module',
            'tong_diem' => 10,
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
        ]);
        
        $largeExam = BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'Bai lon',
            'loai_bai_kiem_tra' => 'cuoi_module',
            'pham_vi' => 'module',
            'tong_diem' => 10,
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
        ]);

        BaiLamBaiKiemTra::create([
            'bai_kiem_tra_id' => $smallExam->id,
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
            'diem_so' => 8.0,
            'trang_thai_cham' => 'da_cham',
            'lan_lam_thu' => 1,
        ]);

        BaiLamBaiKiemTra::create([
            'bai_kiem_tra_id' => $largeExam->id,
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
            'diem_so' => 6.0,
            'trang_thai_cham' => 'da_cham',
            'lan_lam_thu' => 1,
        ]);

        // KHỞI TẠO DỮ LIỆU KẾT QUẢ TẠM TÍNH
        $service = app(\App\Services\KetQuaHocTapService::class);
        $service->refreshForModuleStudent($module->id, $studentUser->ma_nguoi_dung);
        $service->refreshForCourseStudent($course->id, $studentUser->ma_nguoi_dung);
        $service->refreshForExamStudent($smallExam->id, $studentUser->ma_nguoi_dung);
        $service->refreshForExamStudent($largeExam->id, $studentUser->ma_nguoi_dung);

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
            'module_hoc_id' => $module->id,
            'da_chot' => false,
        ]);

        // 2. Giáo viên thực hiện CHỐT ĐIỂM
        // Final Score = (5.0 * 0.2) + (7.0 * 0.8) = 1.0 + 5.6 = 6.6
        try {
            $response = $this->actingAs($teacherUser)
                ->post(route('giang-vien.khoa-hoc.ket-qua.chot', $phanCong->id), [
                    'hoc_vien_id' => $studentUser->ma_nguoi_dung,
                    'ghi_chu_chot' => 'Giao vien chot diem tot',
                ]);
        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
            throw $e;
        }
        
        if ($response->status() !== 302) {
            dump($response->getContent());
        }
        if (session('errors')) {
            dump(['teacher_errors' => session('errors')->getMessages()]);
        }

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        dump('DB state (module):', KetQuaHocTap::where('module_hoc_id', $module->id)
            ->where('hoc_vien_id', $studentUser->ma_nguoi_dung)
            ->whereNull('bai_kiem_tra_id')
            ->first()?->toArray() ?? 'NOT FOUND');

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
            'module_hoc_id' => $module->id,
            'da_chot' => true,
            'diem_giang_vien_chot' => 6.60,
            'trang_thai_duyet' => 'cho_duyet',
        ]);

        $this->assertDatabaseHas('ket_qua_hoc_tap_chot_logs', [
            'hoc_vien_id' => $studentUser->ma_nguoi_dung,
            'hanh_dong' => 'chot',
            'diem_sau' => 6.60,
        ]);

        // 3. Admin thực hiện DUYỆT ĐIỂM
        $result = KetQuaHocTap::where('module_hoc_id', $module->id)
            ->where('hoc_vien_id', $studentUser->ma_nguoi_dung)
            ->whereNull('bai_kiem_tra_id')
            ->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.ket-qua.approve', $result->id), [
                'ghi_chu_duyet' => 'Admin dong y luu ho so',
            ]);
        
        if ($response->status() !== 302) {
            dump($response->getContent());
        }
        if (session('errors')) {
            dump(session('errors')->getMessages());
        }

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        dump('DB state (module):', KetQuaHocTap::where('module_hoc_id', $module->id)
            ->where('hoc_vien_id', $studentUser->ma_nguoi_dung)
            ->whereNull('bai_kiem_tra_id')
            ->first()?->toArray() ?? 'NOT FOUND');

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'id' => $result->id,
            'trang_thai_duyet' => 'da_duyet',
        ]);

        $this->assertDatabaseHas('ket_qua_hoc_tap_chot_logs', [
            'ket_qua_hoc_tap_id' => $result->id,
            'hanh_dong' => 'duyet_luu_ho_so',
        ]);

        // 4. Học viên XEM KẾT QUẢ CHÍNH THỨC
        $response = $this->actingAs($studentUser)
            ->get(route('hoc-vien.ket-qua'));
        
        $response->assertStatus(200);
        $response->assertSee('6.60');
        $response->assertSee('Da duyet, luu ho so');

        // 5. Thử MỞ CHỐT (Teacher re-opens)
        // Lưu ý: Trong logic hiện tại, nếu đã DUYỆT thì Teacher không được mở chốt từ UI (đã check ở Blade)
        // Nhưng ở Service/Controller ta test logic nếu Admin chưa duyệt hoặc quyền admin mở chốt
        $this->actingAs($teacherUser)
            ->post(route('giang-vien.khoa-hoc.ket-qua.mo-chot', $phanCong->id), [
                'result_id' => $result->id,
                'ly_do' => 'Can sua lai diem do nham lan',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'id' => $result->id,
            'da_chot' => false,
            'trang_thai_chot' => 'chua_chot',
        ]);

        $this->assertDatabaseHas('ket_qua_hoc_tap_chot_logs', [
            'ket_qua_hoc_tap_id' => $result->id,
            'hanh_dong' => 'mo_chot',
            'ly_do' => 'Can sua lai diem do nham lan',
        ]);
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
        $giangVien = GiangVien::create(['nguoi_dung_id' => $user->ma_nguoi_dung]);
        return [$user, $giangVien];
    }

    private function createStudent(): NguoiDung
    {
        $user = $this->createUser('hoc_vien');
        HocVien::create(['nguoi_dung_id' => $user->ma_nguoi_dung]);
        return $user;
    }

    private function createCourse(NguoiDung $creator): KhoaHoc
    {
        $index = $this->sequence++;
        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . $index,
            'ten_nhom_nganh' => 'Nhom nganh ' . $index,
            'trang_thai' => true,
        ]);

        return KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-' . $index,
            'ten_khoa_hoc' => 'Khoa hoc ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
            'created_by' => $creator->ma_nguoi_dung,
            'phuong_thuc_danh_gia' => 'cuoi_khoa',
        ]);
    }

    private function createModule(KhoaHoc $course, int $order = 1): ModuleHoc
    {
        return ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => $course->ma_khoa_hoc . '-M' . $order,
            'ten_module' => 'Module ' . $order,
            'thu_tu_module' => $order,
            'so_buoi' => 2,
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
            'trang_thai' => 'hoan_thanh',
        ], $overrides));
    }
}
