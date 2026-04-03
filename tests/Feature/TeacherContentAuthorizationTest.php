<?php

namespace Tests\Feature;

use App\Models\BaiGiang;
use App\Models\GiangVien;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\YeuCauHocVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherContentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_teacher_cannot_create_lecture_with_another_teachers_assignment(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser] = $this->createTeacher();
        [$otherTeacherUser, $otherTeacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $otherTeacher, $course, $module);

        $response = $this->actingAs($actingTeacherUser)
            ->from(route('giang-vien.bai-giang.create'))
            ->post(route('giang-vien.bai-giang.store'), [
                'tieu_de' => 'Bai giang khong hop le',
                'phan_cong_id' => $assignment->id,
                'loai_bai_giang' => 'tai_lieu',
            ]);

        $response
            ->assertRedirect(route('giang-vien.bai-giang.create'))
            ->assertSessionHasErrors(['phan_cong_id']);

        $this->assertDatabaseCount('bai_giangs', 0);
    }

    public function test_teacher_cannot_attach_foreign_library_resource_to_lecture(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser, $actingTeacher] = $this->createTeacher();
        [$otherTeacherUser] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $actingTeacher, $course, $module);

        $foreignResource = TaiNguyenBuoiHoc::create([
            'tieu_de' => 'Tai nguyen cua nguoi khac',
            'loai_tai_nguyen' => 'pdf',
            'pham_vi_su_dung' => TaiNguyenBuoiHoc::PHAM_VI_CA_NHAN,
            'nguoi_tao_id' => $otherTeacherUser->ma_nguoi_dung,
            'vai_tro_nguoi_tao' => 'giang_vien',
            'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET,
            'trang_thai_xu_ly' => TaiNguyenBuoiHoc::STATUS_XU_LY_NONE,
            'duong_dan_file' => 'uploads/thu-vien/foreign.pdf',
        ]);

        $response = $this->actingAs($actingTeacherUser)
            ->from(route('giang-vien.bai-giang.create'))
            ->post(route('giang-vien.bai-giang.store'), [
                'tieu_de' => 'Bai giang co tai nguyen sai quyen',
                'phan_cong_id' => $assignment->id,
                'loai_bai_giang' => 'tai_lieu',
                'tai_nguyen_chinh_id' => $foreignResource->id,
            ]);

        $response
            ->assertRedirect(route('giang-vien.bai-giang.create'))
            ->assertSessionHasErrors(['tai_nguyen_chinh_id']);

        $this->assertDatabaseCount('bai_giangs', 0);
    }

    public function test_teacher_cannot_update_lecture_with_another_teachers_assignment(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser, $actingTeacher] = $this->createTeacher();
        [$otherTeacherUser, $otherTeacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $ownAssignment = $this->assignTeacher($admin, $actingTeacher, $course, $module);
        $foreignAssignment = $this->assignTeacher($admin, $otherTeacher, $course, $module, 2);

        $lecture = BaiGiang::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'nguoi_tao_id' => $actingTeacherUser->ma_nguoi_dung,
            'tieu_de' => 'Bai giang ban dau',
            'loai_bai_giang' => 'tai_lieu',
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_NHAP,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
        ]);

        $response = $this->actingAs($actingTeacherUser)
            ->from(route('giang-vien.bai-giang.edit', $lecture->id))
            ->put(route('giang-vien.bai-giang.update', $lecture->id), [
                'tieu_de' => 'Bai giang bi doi assignment',
                'phan_cong_id' => $foreignAssignment->id,
                'loai_bai_giang' => 'tai_lieu',
            ]);

        $response
            ->assertRedirect(route('giang-vien.bai-giang.edit', $lecture->id))
            ->assertSessionHasErrors(['phan_cong_id']);

        $this->assertDatabaseHas('bai_giangs', [
            'id' => $lecture->id,
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'Bai giang ban dau',
        ]);
    }

    public function test_teacher_cannot_fetch_schedule_for_another_teachers_assignment(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser] = $this->createTeacher();
        [$otherTeacherUser, $otherTeacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $otherTeacher, $course, $module);

        $this->createLichHoc($course, $module);

        $response = $this->actingAs($actingTeacherUser)
            ->getJson(route('giang-vien.bai-giang.get-lich-hoc', ['phan_cong_id' => $assignment->id]));

        $response->assertForbidden();
    }

    public function test_teacher_can_create_lecture_with_their_own_approved_resources(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser, $actingTeacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $actingTeacher, $course, $module);
        $lichHoc = $this->createLichHoc($course, $module);

        $mainResource = $this->createApprovedLibraryResource($actingTeacherUser, 'Tai nguyen chinh');
        $extraResource = $this->createApprovedLibraryResource($actingTeacherUser, 'Tai nguyen phu');

        $response = $this->actingAs($actingTeacherUser)
            ->post(route('giang-vien.bai-giang.store'), [
                'tieu_de' => 'Bai giang hop le',
                'mo_ta' => 'Mo ta',
                'phan_cong_id' => $assignment->id,
                'lich_hoc_id' => $lichHoc->id,
                'loai_bai_giang' => 'tai_lieu',
                'tai_nguyen_chinh_id' => $mainResource->id,
                'tai_nguyen_phu_ids' => [$extraResource->id],
                'thu_tu_hien_thi' => 2,
            ]);

        $response->assertRedirect(route('giang-vien.bai-giang.index'));

        $lecture = BaiGiang::query()->firstOrFail();

        $this->assertDatabaseHas('bai_giangs', [
            'id' => $lecture->id,
            'nguoi_tao_id' => $actingTeacherUser->ma_nguoi_dung,
            'tai_nguyen_chinh_id' => $mainResource->id,
            'lich_hoc_id' => $lichHoc->id,
        ]);
        $this->assertDatabaseHas('bai_giang_tai_nguyen', [
            'bai_giang_id' => $lecture->id,
            'tai_nguyen_id' => $extraResource->id,
        ]);
    }

    public function test_teacher_can_create_regular_lecture_and_submit_for_approval(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser, $actingTeacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $actingTeacher, $course, $module);
        $lichHoc = $this->createLichHoc($course, $module);

        $response = $this->actingAs($actingTeacherUser)
            ->post(route('giang-vien.bai-giang.store'), [
                'tieu_de' => 'Bai giang gui duyet',
                'mo_ta' => 'Noi dung can admin duyet',
                'phan_cong_id' => $assignment->id,
                'lich_hoc_id' => $lichHoc->id,
                'loai_bai_giang' => 'tai_lieu',
                'hanh_dong' => 'gui_duyet',
            ]);

        $response->assertRedirect(route('giang-vien.bai-giang.index'));

        $lecture = BaiGiang::query()->firstOrFail();

        $this->assertDatabaseHas('bai_giangs', [
            'id' => $lecture->id,
            'nguoi_tao_id' => $actingTeacherUser->ma_nguoi_dung,
            'lich_hoc_id' => $lichHoc->id,
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_CHO,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
        ]);
        $this->assertNotNull($lecture->fresh()->ngay_gui_duyet);
    }

    public function test_unassigned_teacher_cannot_send_student_request_for_course(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser] = $this->createTeacher();
        $course = $this->createCourse($admin);

        $response = $this->actingAs($actingTeacherUser)
            ->from(route('home'))
            ->post(route('giang-vien.khoa-hoc.gui-yeu-cau-hoc-vien', $course->id), [
                'loai_yeu_cau' => 'them',
                'ly_do' => 'Can them hoc vien',
                'email_hoc_vien' => 'student@example.com',
                'ten_hoc_vien' => 'Student Test',
            ]);

        $response
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('yeu_cau_hoc_vien', 0);
    }

    public function test_assigned_teacher_can_send_student_request_and_admin_can_process_it(): void
    {
        $admin = $this->createUser('admin');
        [$actingTeacherUser, $actingTeacher] = $this->createTeacher();
        $student = $this->createStudent();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $actingTeacher, $course, $module);

        $response = $this->actingAs($actingTeacherUser)
            ->from(route('giang-vien.khoa-hoc.show', $assignment->id))
            ->post(route('giang-vien.khoa-hoc.gui-yeu-cau-hoc-vien', $course->id), [
                'loai_yeu_cau' => 'them',
                'ly_do' => 'Hoc vien can vao lop',
                'email_hoc_vien' => $student->email,
                'ten_hoc_vien' => $student->ho_ten,
            ]);

        $response
            ->assertRedirect(route('giang-vien.khoa-hoc.show', $assignment->id))
            ->assertSessionHas('success');

        $yeuCau = YeuCauHocVien::query()->firstOrFail();

        $this->assertIsArray($yeuCau->du_lieu_yeu_cau);
        $this->assertSame($student->email, $yeuCau->du_lieu_yeu_cau['email']);

        $this->actingAs($admin)
            ->post(route('admin.yeu-cau-hoc-vien.xac-nhan', $yeuCau->id), [
                'hanh_dong' => 'da_duyet',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hoc_vien_khoa_hoc', [
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'dang_hoc',
        ]);
        $this->assertSame('da_duyet', $yeuCau->fresh()->trang_thai);
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

    private function createCourse(NguoiDung $creator, array $overrides = []): KhoaHoc
    {
        $index = $this->sequence++;
        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_nhom_nganh' => 'Nhom nganh ' . $index,
            'trang_thai' => true,
        ]);

        return KhoaHoc::create(array_merge([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_khoa_hoc' => 'Khoa hoc ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
            'created_by' => $creator->ma_nguoi_dung,
        ], $overrides));
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

    private function createLichHoc(KhoaHoc $course, ModuleHoc $module): LichHoc
    {
        return LichHoc::create([
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
        ]);
    }

    private function assignTeacher(NguoiDung $admin, GiangVien $teacher, KhoaHoc $course, ModuleHoc $module, int $suffix = 1): PhanCongModuleGiangVien
    {
        return PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_phan_cong' => now()->addMinutes($suffix),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
    }

    private function createApprovedLibraryResource(NguoiDung $owner, string $title): TaiNguyenBuoiHoc
    {
        return TaiNguyenBuoiHoc::create([
            'tieu_de' => $title,
            'loai_tai_nguyen' => 'pdf',
            'pham_vi_su_dung' => TaiNguyenBuoiHoc::PHAM_VI_KHOA_HOC,
            'nguoi_tao_id' => $owner->ma_nguoi_dung,
            'vai_tro_nguoi_tao' => 'giang_vien',
            'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET,
            'trang_thai_xu_ly' => TaiNguyenBuoiHoc::STATUS_XU_LY_NONE,
            'duong_dan_file' => 'uploads/thu-vien/' . str_replace(' ', '-', strtolower($title)) . '.pdf',
        ]);
    }
}
