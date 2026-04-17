<?php

namespace Tests\Feature;

use App\Models\BaiGiang;
use App\Models\BaiKiemTra;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\YeuCauHocVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentLearningFlowTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_student_can_self_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post(route('xu-ly-dang-ky'), [
            'ho_ten' => 'Hoc vien moi',
            'email' => 'hocvien-moi@example.com',
            'mat_khau' => 'password123',
            'mat_khau_confirmation' => 'password123',
            'vai_tro' => 'hoc_vien',
            'so_dien_thoai' => '0900000001',
        ]);

        $response->assertRedirect(route('hoc-vien.dashboard'));
        $this->assertAuthenticated();

        $user = NguoiDung::query()->where('email', 'hocvien-moi@example.com')->firstOrFail();

        $this->assertSame('hoc_vien', $user->vai_tro);
        $this->assertDatabaseHas('hoc_vien', [
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);
    }

    public function test_student_can_request_join_course_only_once(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();

        $this->actingAs($student)
            ->get(route('hoc-vien.khoa-hoc-tham-gia'))
            ->assertOk()
            ->assertSeeText($course->ten_khoa_hoc);

        $this->actingAs($student)
            ->post(route('hoc-vien.khoa-hoc.gui-yeu-cau-tham-gia', $course->id), [
                'ly_do' => 'Em muốn tham gia để theo kịp lộ trình học tập.',
            ])
            ->assertRedirect(route('hoc-vien.khoa-hoc-tham-gia'));

        $this->assertDatabaseHas('yeu_cau_hoc_vien', [
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'loai_yeu_cau' => 'them',
            'trang_thai' => 'cho_duyet',
        ]);

        $this->actingAs($student)
            ->from(route('hoc-vien.khoa-hoc-tham-gia'))
            ->post(route('hoc-vien.khoa-hoc.gui-yeu-cau-tham-gia', $course->id), [
                'ly_do' => 'Em gửi lại lần nữa.',
            ])
            ->assertRedirect(route('hoc-vien.khoa-hoc-tham-gia'))
            ->assertSessionHas('error');

        $this->assertSame(1, YeuCauHocVien::query()->count());
    }

    public function test_student_can_open_course_detail_and_session_detail_with_related_content(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $module = $this->createModule($course);
        $schedule = $this->createSchedule($course, $module);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
        ]);

        TaiNguyenBuoiHoc::create([
            'lich_hoc_id' => $schedule->id,
            'loai_tai_nguyen' => 'pdf',
            'tieu_de' => 'Tai lieu buoi hoc 1',
            'trang_thai_hien_thi' => 'hien',
            'ngay_mo_hien_thi' => now()->subMinute(),
            'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET,
            'trang_thai_xu_ly' => TaiNguyenBuoiHoc::STATUS_XU_LY_SAN_SANG,
        ]);

        BaiGiang::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'lich_hoc_id' => $schedule->id,
            'tieu_de' => 'Bai giang buoi 1',
            'loai_bai_giang' => 'tai_lieu',
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_DA_DUYET,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_DA_CONG_BO,
        ]);

        BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'lich_hoc_id' => $schedule->id,
            'tieu_de' => 'Kiem tra buoi 1',
            'thoi_gian_lam_bai' => 20,
            'ngay_mo' => now()->subHour(),
            'ngay_dong' => now()->addHour(),
            'pham_vi' => 'buoi_hoc',
            'loai_bai_kiem_tra' => 'buoi_hoc',
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'trang_thai' => true,
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.khoa-hoc-cua-toi'))
            ->assertOk()
            ->assertSeeText($course->ten_khoa_hoc);

        $this->actingAs($student)
            ->get(route('hoc-vien.chi-tiet-khoa-hoc', $course->id))
            ->assertOk()
            ->assertSeeText($course->ten_khoa_hoc)
            ->assertSeeText('Bai giang buoi 1');

        $this->actingAs($student)
            ->get(route('hoc-vien.buoi-hoc.show', $schedule->id))
            ->assertOk()
            ->assertSeeText('Tai lieu buoi hoc 1')
            ->assertSeeText('Bai giang buoi 1')
            ->assertSeeText('Kiem tra buoi 1');
    }

    public function test_student_session_detail_shows_google_meet_join_area_from_online_link(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $module = $this->createModule($course);
        $schedule = $this->createSchedule($course, $module, [
            'nen_tang' => 'Google Meet',
            'link_online' => 'https://meet.google.com/abc-defg-hij?pli=1',
            'meeting_id' => 'abc-defg-hij',
            'mat_khau_cuoc_hop' => '246810',
            'ghi_chu' => 'On tap quy trinh lam bai va thao tac trong phong live.',
        ]);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.buoi-hoc.show', $schedule->id))
            ->assertOk()
            ->assertSee('Tham gia lớp học online', false)
            ->assertSee('Mở Google Meet', false)
            ->assertSee('https://meet.google.com/abc-defg-hij', false)
            ->assertDontSee('?pli=1', false)
            ->assertSee('abc-defg-hij', false)
            ->assertSee('246810', false)
            ->assertSee('Nội dung trọng tâm của buổi học', false);
    }

    public function test_student_cannot_open_session_detail_without_active_enrollment(): void
    {
        $student = $this->createStudent();
        $course = $this->createCourse();
        $module = $this->createModule($course);
        $schedule = $this->createSchedule($course, $module);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'ngung_hoc',
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.buoi-hoc.show', $schedule->id))
            ->assertRedirect(route('hoc-vien.khoa-hoc-cua-toi'));
    }

    public function test_student_can_update_profile_information(): void
    {
        $student = $this->createStudent();

        $this->actingAs($student)
            ->post(route('hoc-vien.profile.update'), [
                'ho_ten' => 'Hoc vien cap nhat',
                'email' => 'updated-student@example.com',
                'so_dien_thoai' => '0911222333',
                'ngay_sinh' => '2000-01-01',
                'dia_chi' => 'TP Ho Chi Minh',
                'mat_khau' => 'newpassword123',
                'mat_khau_confirmation' => 'newpassword123',
                'lop' => '12A1',
                'nganh' => 'Cong nghe thong tin',
                'diem_trung_binh' => '8.5',
            ])
            ->assertRedirect(route('hoc-vien.profile'));

        $updatedUser = $student->fresh();

        $this->assertSame('Hoc vien cap nhat', $updatedUser->ho_ten);
        $this->assertSame('updated-student@example.com', $updatedUser->email);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->mat_khau));
        $this->assertDatabaseHas('hoc_vien', [
            'nguoi_dung_id' => $student->ma_nguoi_dung,
            'lop' => '12A1',
            'nganh' => 'Cong nghe thong tin',
        ]);
    }

    private function createStudent(): NguoiDung
    {
        $index = $this->sequence++;

        $user = NguoiDung::create([
            'ho_ten' => 'Hoc vien ' . $index,
            'email' => 'student' . $index . '@example.com',
            'mat_khau' => bcrypt('password123'),
            'vai_tro' => 'hoc_vien',
            'trang_thai' => true,
        ]);

        HocVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return $user;
    }

    private function createCourse(): KhoaHoc
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
        ]);
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

    private function createSchedule(KhoaHoc $course, ModuleHoc $module, array $overrides = []): LichHoc
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
