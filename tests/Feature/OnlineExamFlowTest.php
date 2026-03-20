<?php

namespace Tests\Feature;

use App\Models\BaiKiemTra;
use App\Models\DapAnCauHoi;
use App\Models\DiemDanh;
use App\Models\GiangVien;
use App\Models\HocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class OnlineExamFlowTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private int $sequence = 100;

    public function test_student_only_sees_published_and_approved_exams(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $student = $this->createStudent();
        $course = $this->createCourse($admin, ['phuong_thuc_danh_gia' => 'theo_module']);
        $module = $this->createModule($course);
        $this->assignTeacher($admin, $teacher, $course, $module);
        $this->enrollStudent($admin, $student, $course);

        BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'De duoc phat hanh',
            'thoi_gian_lam_bai' => 30,
            'pham_vi' => 'module',
            'loai_bai_kiem_tra' => 'module',
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'trang_thai' => true,
        ]);

        BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'De nhap chua phat hanh',
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

        $this->actingAs($student)
            ->get(route('hoc-vien.bai-kiem-tra'))
            ->assertOk()
            ->assertSeeText('De duoc phat hanh')
            ->assertDontSeeText('De nhap chua phat hanh');
    }

    public function test_student_can_submit_mixed_exam_and_mcq_is_auto_graded(): void
    {
        $context = $this->buildMixedExamContext();

        $student = $context['student'];
        $exam = $context['exam'];
        $mcqQuestion = $context['mcq_question'];
        $essayQuestion = $context['essay_question'];
        $correctAnswer = $context['correct_answer'];

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.bat-dau', $exam->id))
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.show', $exam->id));

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.nop', $exam->id), [
                'answers' => [
                    $mcqQuestion->pivot_detail_id => [
                        'dap_an_cau_hoi_id' => $correctAnswer->id,
                    ],
                    $essayQuestion->pivot_detail_id => [
                        'cau_tra_loi_text' => 'Day la cau tra loi tu luan cua hoc vien.',
                    ],
                ],
            ])
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.show', $exam->id));

        $this->assertDatabaseHas('bai_lam_bai_kiem_tra', [
            'bai_kiem_tra_id' => $exam->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'cho_cham',
            'trang_thai_cham' => 'cho_cham',
            'tong_diem_trac_nghiem' => 4.00,
        ]);

        $this->assertDatabaseHas('chi_tiet_bai_lam_bai_kiem_tra', [
            'dap_an_cau_hoi_id' => $correctAnswer->id,
            'is_dung' => 1,
            'diem_tu_dong' => 4.00,
        ]);
    }

    public function test_teacher_can_grade_essay_and_learning_result_is_aggregated(): void
    {
        $context = $this->buildMixedExamContext();

        $student = $context['student'];
        $teacherUser = $context['teacher_user'];
        $exam = $context['exam'];
        $course = $context['course'];
        $mcqQuestion = $context['mcq_question'];
        $essayQuestion = $context['essay_question'];
        $correctAnswer = $context['correct_answer'];
        $lichHoc = $context['lich_hoc'];

        DiemDanh::create([
            'lich_hoc_id' => $lichHoc->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'co_mat',
        ]);

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.bat-dau', $exam->id));

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.nop', $exam->id), [
                'answers' => [
                    $mcqQuestion->pivot_detail_id => [
                        'dap_an_cau_hoi_id' => $correctAnswer->id,
                    ],
                    $essayQuestion->pivot_detail_id => [
                        'cau_tra_loi_text' => 'Hoc vien phan tich vai tro cua migration.',
                    ],
                ],
            ]);

        $baiLam = $exam->fresh()->baiLams()->where('hoc_vien_id', $student->ma_nguoi_dung)->firstOrFail();
        $essayDetail = $baiLam->chiTietTraLois()->where('chi_tiet_bai_kiem_tra_id', $essayQuestion->pivot_detail_id)->firstOrFail();

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.cham-diem.store', $baiLam->id), [
                'grades' => [
                    $essayDetail->id => [
                        'diem_tu_luan' => 6,
                        'nhan_xet' => 'Tra loi day du va ro y.',
                    ],
                ],
            ])
            ->assertRedirect(route('giang-vien.cham-diem.show', $baiLam->id));

        $this->assertDatabaseHas('bai_lam_bai_kiem_tra', [
            'id' => $baiLam->id,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'diem_so' => 10.00,
        ]);

        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'diem_diem_danh' => 10.00,
            'diem_kiem_tra' => 10.00,
            'diem_tong_ket' => 10.00,
        ]);
    }

    public function test_admin_can_create_question_bank_question_with_answers(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);

        $response = $this->actingAs($admin)->post(route('admin.kiem-tra-online.cau-hoi.store'), [
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'noi_dung' => 'Laravel duoc viet bang ngon ngu nao?',
            'loai_cau_hoi' => 'trac_nghiem',
            'muc_do' => 'de',
            'diem_mac_dinh' => 1,
            'trang_thai' => 'san_sang',
            'dap_an_dung' => 'A',
            'dap_ans' => [
                ['ky_hieu' => 'A', 'noi_dung' => 'PHP'],
                ['ky_hieu' => 'B', 'noi_dung' => 'Ruby'],
            ],
        ]);

        $response->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'));
        $this->assertDatabaseHas('ngan_hang_cau_hoi', [
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'loai_cau_hoi' => 'trac_nghiem',
        ]);
        $this->assertDatabaseHas('dap_an_cau_hoi', [
            'ky_hieu' => 'A',
            'is_dap_an_dung' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMixedExamContext(): array
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $student = $this->createStudent();
        $course = $this->createCourse($admin, [
            'phuong_thuc_danh_gia' => 'theo_module',
            'ty_trong_diem_danh' => 20,
            'ty_trong_kiem_tra' => 80,
        ]);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module);

        $this->assignTeacher($admin, $teacher, $course, $module);
        $this->enrollStudent($admin, $student, $course);

        $exam = BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'lich_hoc_id' => $lichHoc->id,
            'tieu_de' => 'De kiem tra hon hop',
            'mo_ta' => 'Ket hop cau hoi trac nghiem va tu luan.',
            'thoi_gian_lam_bai' => 30,
            'ngay_mo' => now()->subHour(),
            'ngay_dong' => now()->addHour(),
            'pham_vi' => 'module',
            'loai_bai_kiem_tra' => 'module',
            'loai_noi_dung' => 'hon_hop',
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'trang_thai' => true,
        ]);

        $mcqQuestion = NganHangCauHoi::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'ma_cau_hoi' => 'CH-MCQ-' . $this->sequence++,
            'noi_dung' => 'Laravel duoc viet bang ngon ngu nao?',
            'loai_cau_hoi' => 'trac_nghiem',
            'muc_do' => 'de',
            'diem_mac_dinh' => 4,
            'trang_thai' => 'san_sang',
            'co_the_tai_su_dung' => true,
        ]);

        $correctAnswer = DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $mcqQuestion->id,
            'ky_hieu' => 'A',
            'noi_dung' => 'PHP',
            'is_dap_an_dung' => true,
            'thu_tu' => 1,
        ]);

        DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $mcqQuestion->id,
            'ky_hieu' => 'B',
            'noi_dung' => 'Java',
            'is_dap_an_dung' => false,
            'thu_tu' => 2,
        ]);

        $essayQuestion = NganHangCauHoi::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'ma_cau_hoi' => 'CH-ESSAY-' . $this->sequence++,
            'noi_dung' => 'Trinh bay vai tro cua migration trong Laravel.',
            'loai_cau_hoi' => 'tu_luan',
            'muc_do' => 'trung_binh',
            'diem_mac_dinh' => 6,
            'trang_thai' => 'san_sang',
            'co_the_tai_su_dung' => true,
        ]);

        $mcqDetail = $exam->chiTietCauHois()->create([
            'ngan_hang_cau_hoi_id' => $mcqQuestion->id,
            'thu_tu' => 1,
            'diem_so' => 4,
            'bat_buoc' => true,
        ]);

        $essayDetail = $exam->chiTietCauHois()->create([
            'ngan_hang_cau_hoi_id' => $essayQuestion->id,
            'thu_tu' => 2,
            'diem_so' => 6,
            'bat_buoc' => true,
        ]);

        $mcqQuestion->pivot_detail_id = $mcqDetail->id;
        $essayQuestion->pivot_detail_id = $essayDetail->id;

        return [
            'admin' => $admin,
            'teacher_user' => $teacherUser,
            'teacher' => $teacher,
            'student' => $student,
            'course' => $course,
            'module' => $module,
            'lich_hoc' => $lichHoc,
            'exam' => $exam->fresh('chiTietCauHois'),
            'mcq_question' => $mcqQuestion,
            'essay_question' => $essayQuestion,
            'correct_answer' => $correctAnswer,
        ];
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
            'phuong_thuc_danh_gia' => 'cuoi_khoa',
            'ty_trong_diem_danh' => 20,
            'ty_trong_kiem_tra' => 80,
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

    private function assignTeacher(NguoiDung $admin, GiangVien $teacher, KhoaHoc $course, ModuleHoc $module): void
    {
        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giao_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
    }

    private function enrollStudent(NguoiDung $admin, NguoiDung $student, KhoaHoc $course): void
    {
        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
    }
}
