<?php

namespace Tests\Feature;

use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\DiemDanh;
use App\Models\GiangVien;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use App\Services\ExamResultAggregationService;
use App\Services\KetQuaHocTapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningResultAggregationStrategyTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_result_can_use_latest_attempt_strategy(): void
    {
        [$admin, $student, $course, $module] = $this->createBaseContext();
        $exam = $this->createExam($admin, $course, $module, [
            'attempt_strategy' => ExamResultAggregationService::LATEST_ATTEMPT,
            'so_lan_duoc_lam' => 3,
        ]);

        $firstAttempt = $exam->baiLams()->create([
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 1,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'bat_dau_luc' => now()->subHours(2),
            'nop_luc' => now()->subHours(2),
            'diem_so' => 9.00,
        ]);
        $latestAttempt = $exam->baiLams()->create([
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 2,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'bat_dau_luc' => now()->subHour(),
            'nop_luc' => now()->subHour(),
            'diem_so' => 6.00,
        ]);

        $result = app(KetQuaHocTapService::class)->refreshForExamStudent($exam->id, $student->ma_nguoi_dung);

        $this->assertNotNull($result);
        $this->assertSame('6.00', (string) $result->diem_kiem_tra);
        $this->assertSame(ExamResultAggregationService::LATEST_ATTEMPT, $result->attempt_strategy_used);
        $this->assertSame($latestAttempt->id, $result->source_attempt_id);
        $this->assertNotSame($firstAttempt->id, $result->source_attempt_id);
    }

    public function test_module_result_can_average_selected_exams_and_admin_can_drill_down(): void
    {
        [$admin, $student, $course, $module] = $this->createBaseContext([
            'phuong_thuc_danh_gia' => 'theo_module',
        ]);
        $examOne = $this->createExam($admin, $course, $module, ['tieu_de' => 'Exam ignored by config']);
        $examTwo = $this->createExam($admin, $course, $module, ['tieu_de' => 'Exam selected by config']);

        $module->update([
            'ket_qua_config' => [
                'aggregation_strategy' => 'selected_exams_average',
                'selected_exam_ids' => [$examTwo->id],
            ],
        ]);

        $examOne->baiLams()->create([
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 1,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'nop_luc' => now()->subMinutes(30),
            'diem_so' => 9.00,
        ]);
        $examTwo->baiLams()->create([
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 1,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'nop_luc' => now()->subMinutes(20),
            'diem_so' => 5.00,
        ]);

        app(KetQuaHocTapService::class)->refreshAllForCourseStudent($course->id, $student->ma_nguoi_dung);

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'module_hoc_id' => $module->id,
            'bai_kiem_tra_id' => null,
            'diem_kiem_tra' => 5.00,
            'aggregation_strategy_used' => 'selected_exams_average',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.ket-qua.show', $course->id))
            ->assertOk()
            ->assertSeeText('Exam selected by config')
            ->assertSeeText('Diem chinh thuc')
            ->assertSeeText('selected_exams_average');
    }

    public function test_student_can_see_attempt_history_and_official_attempt(): void
    {
        [$admin, $student, $course, $module] = $this->createBaseContext();
        $exam = $this->createExam($admin, $course, $module, [
            'attempt_strategy' => ExamResultAggregationService::LATEST_ATTEMPT,
            'so_lan_duoc_lam' => 3,
        ]);

        $exam->baiLams()->create([
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 1,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'nop_luc' => now()->subHour(),
            'diem_so' => 8.00,
        ]);
        $exam->baiLams()->create([
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 2,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'nop_luc' => now(),
            'diem_so' => 6.00,
        ]);

        app(KetQuaHocTapService::class)->refreshForExamStudent($exam->id, $student->ma_nguoi_dung);

        $this->actingAs($student)
            ->get(route('hoc-vien.bai-kiem-tra.show', $exam->id))
            ->assertOk()
            ->assertSeeText('Lich su lam bai')
            ->assertSeeText('Lan 1')
            ->assertSeeText('Lan 2')
            ->assertSeeText('Diem chinh thuc')
            ->assertSeeText(ExamResultAggregationService::LATEST_ATTEMPT);
    }

    public function test_module_result_groups_session_exam_average_as_one_major_component(): void
    {
        [$admin, $student, $course, $module] = $this->createBaseContext();
        $sessionExamOne = $this->createExam($admin, $course, $module, [
            'tieu_de' => 'Session quiz one',
            'pham_vi' => 'buoi_hoc',
            'loai_bai_kiem_tra' => 'buoi_hoc',
        ]);
        $sessionExamTwo = $this->createExam($admin, $course, $module, [
            'tieu_de' => 'Session quiz two',
            'pham_vi' => 'buoi_hoc',
            'loai_bai_kiem_tra' => 'buoi_hoc',
        ]);
        $moduleFinalExam = $this->createExam($admin, $course, $module, [
            'tieu_de' => 'Module final exam',
            'pham_vi' => 'module',
            'loai_bai_kiem_tra' => 'module',
        ]);

        $this->createGradedAttempt($sessionExamOne, $student, 4.00);
        $this->createGradedAttempt($sessionExamTwo, $student, 6.00);
        $this->createGradedAttempt($moduleFinalExam, $student, 9.00);

        $result = app(KetQuaHocTapService::class)->refreshForModuleStudent($module->id, $student->ma_nguoi_dung);

        $this->assertSame('7.00', (string) $result->diem_kiem_tra);
        $this->assertSame('module_exam_with_session_average', $result->aggregation_strategy_used);
        $this->assertSame(3, $result->so_bai_kiem_tra_hoan_thanh);
    }

    public function test_teacher_can_finalize_module_result_and_admin_can_archive_it(): void
    {
        [$admin, $student, $course, $module] = $this->createBaseContext([
            'ty_trong_diem_danh' => 40,
            'ty_trong_kiem_tra' => 60,
        ]);
        [$teacherUser, $assignment] = $this->createTeacherAssignment($admin, $course, $module);

        $firstSession = $this->createSchedule($course, $module, $assignment->giang_vien_id, 1);
        $this->createSchedule($course, $module, $assignment->giang_vien_id, 2);
        DiemDanh::create([
            'lich_hoc_id' => $firstSession->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'co_mat',
        ]);

        $exam = $this->createExam($admin, $course, $module);
        $this->createGradedAttempt($exam, $student, 10.00);
        app(KetQuaHocTapService::class)->refreshForModuleStudent($module->id, $student->ma_nguoi_dung);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.khoa-hoc.ket-qua.chot', $assignment->id), [
                'hoc_vien_id' => $student->ma_nguoi_dung,
                'ghi_chu_chot' => 'Dat module',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'bai_kiem_tra_id' => null,
            'diem_diem_danh' => 5.00,
            'diem_kiem_tra' => 10.00,
            'diem_tong_ket' => 8.00,
            'diem_giang_vien_chot' => 8.00,
            'trang_thai_chot' => 'da_chot',
            'trang_thai_duyet' => 'cho_duyet',
            'chot_boi' => $teacherUser->ma_nguoi_dung,
        ]);

        $resultId = KetQuaHocTap::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('module_hoc_id', $module->id)
            ->where('hoc_vien_id', $student->ma_nguoi_dung)
            ->whereNull('bai_kiem_tra_id')
            ->value('id');

        $this->actingAs($admin)
            ->post(route('admin.ket-qua.approve', $resultId), [
                'ghi_chu_duyet' => 'Luu ho so',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'id' => $resultId,
            'trang_thai_duyet' => 'da_duyet',
            'admin_duyet_id' => $admin->ma_nguoi_dung,
            'ghi_chu_duyet' => 'Luu ho so',
        ]);
        $this->assertNotNull(KetQuaHocTap::find($resultId)?->luu_ho_so_luc);

        $this->actingAs($student)
            ->get(route('hoc-vien.ket-qua'))
            ->assertOk()
            ->assertSeeText('Diem giang vien chot')
            ->assertSeeText('8.00');

        $this->actingAs($admin)
            ->get(route('admin.ket-qua.show', $course->id))
            ->assertOk()
            ->assertSeeText('Diem GV chot')
            ->assertSeeText('Da duyet, luu ho so');
    }

    /**
     * @return array{0: NguoiDung, 1: NguoiDung, 2: KhoaHoc, 3: ModuleHoc}
     */
    private function createBaseContext(array $courseOverrides = []): array
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Test',
            'email' => 'admin-' . uniqid() . '@example.com',
            'mat_khau' => 'password',
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);
        $student = NguoiDung::create([
            'ho_ten' => 'Student Test',
            'email' => 'student-' . uniqid() . '@example.com',
            'mat_khau' => 'password',
            'vai_tro' => 'hoc_vien',
            'trang_thai' => true,
        ]);
        HocVien::create([
            'nguoi_dung_id' => $student->ma_nguoi_dung,
        ]);
        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . uniqid(),
            'ten_nhom_nganh' => 'Nhom nganh test ' . uniqid(),
            'trang_thai' => true,
        ]);
        $course = KhoaHoc::create(array_merge([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH' . uniqid(),
            'ten_khoa_hoc' => 'Khoa hoc diem',
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'phuong_thuc_danh_gia' => 'theo_module',
            'ty_trong_diem_danh' => 0,
            'ty_trong_kiem_tra' => 100,
            'trang_thai' => true,
            'created_by' => $admin->ma_nguoi_dung,
        ], $courseOverrides));
        $module = ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => 'MD' . uniqid(),
            'ten_module' => 'Module diem',
            'thu_tu_module' => 1,
            'so_buoi' => 1,
            'trang_thai' => true,
        ]);
        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        return [$admin, $student, $course, $module];
    }

    /**
     * @return array{0: NguoiDung, 1: PhanCongModuleGiangVien}
     */
    private function createTeacherAssignment(NguoiDung $admin, KhoaHoc $course, ModuleHoc $module): array
    {
        $teacherUser = NguoiDung::create([
            'ho_ten' => 'Teacher Test',
            'email' => 'teacher-' . uniqid() . '@example.com',
            'mat_khau' => 'password',
            'vai_tro' => 'giang_vien',
            'trang_thai' => true,
        ]);
        $teacher = GiangVien::create([
            'nguoi_dung_id' => $teacherUser->ma_nguoi_dung,
            'chuyen_nganh' => 'Testing',
        ]);
        $assignment = PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        return [$teacherUser, $assignment];
    }

    private function createSchedule(KhoaHoc $course, ModuleHoc $module, int $teacherId, int $sessionNumber): LichHoc
    {
        return LichHoc::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacherId,
            'ngay_hoc' => now()->addDays($sessionNumber)->toDateString(),
            'gio_bat_dau' => '08:00',
            'gio_ket_thuc' => '09:30',
            'buoi_so' => $sessionNumber,
            'hinh_thuc' => 'truc_tiep',
            'trang_thai' => 'hoan_thanh',
        ]);
    }

    private function createExam(NguoiDung $admin, KhoaHoc $course, ModuleHoc $module, array $overrides = []): BaiKiemTra
    {
        return BaiKiemTra::create(array_merge([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'Exam strategy ' . uniqid(),
            'mo_ta' => 'Exam for strategy test',
            'thoi_gian_lam_bai' => 30,
            'pham_vi' => 'module',
            'loai_bai_kiem_tra' => 'module',
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 2,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'trang_thai' => true,
        ], $overrides));
    }

    private function createGradedAttempt(BaiKiemTra $exam, NguoiDung $student, float $score): BaiLamBaiKiemTra
    {
        return $exam->baiLams()->create([
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 1,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'nop_luc' => now(),
            'diem_so' => $score,
        ]);
    }
}
