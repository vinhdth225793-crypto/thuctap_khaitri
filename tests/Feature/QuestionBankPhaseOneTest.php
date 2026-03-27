<?php

namespace Tests\Feature;

use App\Models\BaiKiemTra;
use App\Models\DapAnCauHoi;
use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class QuestionBankPhaseOneTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private int $sequence = 1000;

    public function test_admin_can_create_multiple_correct_question(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);

        $response = $this->actingAs($admin)->post(route('admin.kiem-tra-online.cau-hoi.store'), [
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'ma_cau_hoi' => 'CH-MULTI-001',
            'noi_dung_cau_hoi' => 'Question with multiple correct answers',
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_NHIEU_DAP_AN,
            'muc_do' => 'kho',
            'diem_mac_dinh' => 2,
            'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            'co_the_tai_su_dung' => '1',
            'correct_answer_keys' => ['0', '1'],
            'dap_ans' => [
                ['ky_hieu' => 'A', 'noi_dung' => 'Laravel'],
                ['ky_hieu' => 'B', 'noi_dung' => 'Symfony'],
                ['ky_hieu' => 'C', 'noi_dung' => 'Excel'],
            ],
        ]);

        $response->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'));

        $question = NganHangCauHoi::query()
            ->where('ma_cau_hoi', 'CH-MULTI-001')
            ->with('dapAns')
            ->firstOrFail();

        $this->assertSame(NganHangCauHoi::KIEU_NHIEU_DAP_AN, $question->kieu_dap_an);
        $this->assertCount(3, $question->dapAns);
        $this->assertSame(2, $question->dapAns->where('is_dap_an_dung', true)->count());
    }

    public function test_admin_can_update_question_to_true_false_mode(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $question = $this->createSingleCorrectQuestion($admin, $course, $module, 'CH-UPDATE-001', 'Question to update');

        $response = $this->actingAs($admin)->put(route('admin.kiem-tra-online.cau-hoi.update', $question->id), [
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'ma_cau_hoi' => 'CH-UPDATE-001',
            'noi_dung_cau_hoi' => 'Statement in true false mode',
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_DUNG_SAI,
            'muc_do' => 'de',
            'diem_mac_dinh' => 1,
            'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            'co_the_tai_su_dung' => '1',
            'dap_an_dung_sai' => 'sai',
        ]);

        $response->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'));

        $question->refresh();
        $question->load('dapAns');

        $this->assertSame(NganHangCauHoi::KIEU_DUNG_SAI, $question->kieu_dap_an);
        $this->assertSame('Statement in true false mode', $question->noi_dung);
        $this->assertCount(2, $question->dapAns);
        $this->assertSame(['Đúng', 'Sai'], $question->dapAns->pluck('noi_dung')->all());
        $this->assertSame('Sai', $question->dapAns->firstWhere('is_dap_an_dung', true)?->noi_dung);
    }

    public function test_question_bank_index_filters_by_answer_mode_and_search(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);

        $this->createSingleCorrectQuestion($admin, $course, $module, 'CH-FILTER-001', 'Visible single answer question');
        $this->createMultipleCorrectQuestion($admin, $course, $module, 'CH-FILTER-002', 'Hidden multiple answer question');

        $response = $this->actingAs($admin)->get(route('admin.kiem-tra-online.cau-hoi.index', [
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
            'search' => 'Visible single answer',
        ]));

        $response->assertOk();
        $response->assertSeeText('Visible single answer question');
        $response->assertDontSeeText('Hidden multiple answer question');
    }

    public function test_toggle_status_and_reusable_flags_work(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $question = $this->createSingleCorrectQuestion(
            $admin,
            $course,
            $module,
            'CH-TOGGLE-001',
            'Toggle me',
            NganHangCauHoi::TRANG_THAI_NHAP,
            true,
        );

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.toggle-status', $question->id))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.toggle-reusable', $question->id))
            ->assertRedirect();

        $question->refresh();

        $this->assertSame(NganHangCauHoi::TRANG_THAI_SAN_SANG, $question->trang_thai);
        $this->assertFalse($question->co_the_tai_su_dung);
    }

    public function test_teacher_exam_builder_excludes_multiple_correct_questions_for_now(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);

        $this->assignTeacher($admin, $teacher, $course, $module);

        $exam = BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'Editable exam',
            'thoi_gian_lam_bai' => 30,
            'pham_vi' => 'module',
            'loai_bai_kiem_tra' => 'module',
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'nhap',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'trang_thai' => true,
        ]);

        $this->createSingleCorrectQuestion($admin, $course, $module, 'CH-EDIT-001', 'Visible in exam builder');
        $this->createMultipleCorrectQuestion($admin, $course, $module, 'CH-EDIT-002', 'Hidden in exam builder');
        $this->createTrueFalseQuestion($admin, $course, $module, 'CH-EDIT-003', 'True false still visible');

        $response = $this->actingAs($teacherUser)->get(route('giang-vien.bai-kiem-tra.edit', $exam->id));

        $response->assertOk();
        $response->assertSeeText('Visible in exam builder');
        $response->assertSeeText('True false still visible');
        $response->assertDontSeeText('Hidden in exam builder');
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
        $teacher = GiangVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return [$user, $teacher];
    }

    private function createCourse(NguoiDung $creator): KhoaHoc
    {
        $index = $this->sequence++;
        $group = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_nhom_nganh' => 'Group ' . $index,
            'trang_thai' => true,
        ]);

        return KhoaHoc::create([
            'nhom_nganh_id' => $group->id,
            'ma_khoa_hoc' => 'KH-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_khoa_hoc' => 'Course ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'phuong_thuc_danh_gia' => 'cuoi_khoa',
            'ty_trong_diem_danh' => 20,
            'ty_trong_kiem_tra' => 80,
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

    private function assignTeacher(NguoiDung $admin, GiangVien $teacher, KhoaHoc $course, ModuleHoc $module): void
    {
        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
    }

    private function createSingleCorrectQuestion(
        NguoiDung $admin,
        KhoaHoc $course,
        ModuleHoc $module,
        string $code,
        string $content,
        string $status = NganHangCauHoi::TRANG_THAI_SAN_SANG,
        bool $reusable = true,
    ): NganHangCauHoi {
        $question = NganHangCauHoi::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'ma_cau_hoi' => $code,
            'noi_dung' => $content,
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
            'muc_do' => 'de',
            'diem_mac_dinh' => 1,
            'trang_thai' => $status,
            'co_the_tai_su_dung' => $reusable,
        ]);

        DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'ky_hieu' => 'A',
            'noi_dung' => 'Correct answer',
            'is_dap_an_dung' => true,
            'thu_tu' => 1,
        ]);

        DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'ky_hieu' => 'B',
            'noi_dung' => 'Wrong answer',
            'is_dap_an_dung' => false,
            'thu_tu' => 2,
        ]);

        return $question;
    }

    private function createMultipleCorrectQuestion(
        NguoiDung $admin,
        KhoaHoc $course,
        ModuleHoc $module,
        string $code,
        string $content,
    ): NganHangCauHoi {
        $question = NganHangCauHoi::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'ma_cau_hoi' => $code,
            'noi_dung' => $content,
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_NHIEU_DAP_AN,
            'muc_do' => 'kho',
            'diem_mac_dinh' => 2,
            'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            'co_the_tai_su_dung' => true,
        ]);

        foreach ([
            ['A', 'First correct', true, 1],
            ['B', 'Second correct', true, 2],
            ['C', 'Wrong answer', false, 3],
        ] as [$key, $answer, $isCorrect, $order]) {
            DapAnCauHoi::create([
                'ngan_hang_cau_hoi_id' => $question->id,
                'ky_hieu' => $key,
                'noi_dung' => $answer,
                'is_dap_an_dung' => $isCorrect,
                'thu_tu' => $order,
            ]);
        }

        return $question;
    }

    private function createTrueFalseQuestion(
        NguoiDung $admin,
        KhoaHoc $course,
        ModuleHoc $module,
        string $code,
        string $content,
    ): NganHangCauHoi {
        $question = NganHangCauHoi::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'ma_cau_hoi' => $code,
            'noi_dung' => $content,
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_DUNG_SAI,
            'muc_do' => 'de',
            'diem_mac_dinh' => 1,
            'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            'co_the_tai_su_dung' => true,
        ]);

        DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'ky_hieu' => 'A',
            'noi_dung' => 'Dung',
            'is_dap_an_dung' => true,
            'thu_tu' => 1,
        ]);

        DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'ky_hieu' => 'B',
            'noi_dung' => 'Sai',
            'is_dap_an_dung' => false,
            'thu_tu' => 2,
        ]);

        return $question;
    }
}
