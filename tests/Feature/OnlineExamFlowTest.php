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

    public function test_teacher_submit_admin_approve_publish_and_student_can_start_exam(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $student = $this->createStudent();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module);

        $this->assignTeacher($admin, $teacher, $course, $module);
        $this->enrollStudent($admin, $student, $course);

        $exam = BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'lich_hoc_id' => $lichHoc->id,
            'tieu_de' => 'De thi gui duyet end to end',
            'mo_ta' => 'De thi cho hoc vien lam sau khi admin phat hanh.',
            'thoi_gian_lam_bai' => 20,
            'ngay_mo' => now()->subMinutes(5),
            'ngay_dong' => now()->addHour(),
            'pham_vi' => 'buoi_hoc',
            'loai_bai_kiem_tra' => 'buoi_hoc',
            'loai_noi_dung' => 'trac_nghiem',
            'trang_thai_duyet' => 'nhap',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'trang_thai' => true,
        ]);

        $question = $this->createReadyQuestion(
            $admin,
            $course,
            $module,
            'CH-E2E-001',
            'Hoc vien co lam duoc de sau khi admin phat hanh khong?'
        );

        DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'ky_hieu' => 'A',
            'noi_dung' => 'Co',
            'is_dap_an_dung' => true,
            'thu_tu' => 1,
        ]);

        DapAnCauHoi::create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'ky_hieu' => 'B',
            'noi_dung' => 'Khong',
            'is_dap_an_dung' => false,
            'thu_tu' => 2,
        ]);

        $exam->chiTietCauHois()->create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'thu_tu' => 1,
            'diem_so' => 10,
            'bat_buoc' => true,
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.bai-kiem-tra'))
            ->assertOk()
            ->assertDontSeeText($exam->tieu_de);

        $this->actingAs($teacherUser)
            ->from(route('giang-vien.bai-kiem-tra.edit', $exam->id))
            ->post(route('giang-vien.bai-kiem-tra.submit', $exam->id))
            ->assertRedirect(route('giang-vien.bai-kiem-tra.edit', $exam->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bai_kiem_tra', [
            'id' => $exam->id,
            'trang_thai_duyet' => 'cho_duyet',
            'trang_thai_phat_hanh' => 'nhap',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.kiem-tra-online.phe-duyet.show', $exam->id))
            ->post(route('admin.kiem-tra-online.phe-duyet.approve', $exam->id), [
                'ghi_chu_duyet' => 'De hop le, du dieu kien phat hanh.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bai_kiem_tra', [
            'id' => $exam->id,
            'trang_thai_duyet' => 'da_duyet',
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.bai-kiem-tra'))
            ->assertOk()
            ->assertDontSeeText($exam->tieu_de);

        $this->actingAs($admin)
            ->from(route('admin.kiem-tra-online.phe-duyet.show', $exam->id))
            ->post(route('admin.kiem-tra-online.phe-duyet.publish', $exam->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bai_kiem_tra', [
            'id' => $exam->id,
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
            'trang_thai' => 1,
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.bai-kiem-tra'))
            ->assertOk()
            ->assertSeeText($exam->tieu_de);

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.bat-dau', $exam->id))
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.show', $exam->id));

        $this->assertDatabaseHas('bai_lam_bai_kiem_tra', [
            'bai_kiem_tra_id' => $exam->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'dang_lam',
        ]);
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

    public function test_student_must_pass_precheck_before_starting_supervised_exam(): void
    {
        $context = $this->buildMixedExamContext();

        $student = $context['student'];
        $exam = $context['exam'];

        $exam->update([
            'co_giam_sat' => true,
            'bat_buoc_fullscreen' => true,
            'bat_buoc_camera' => true,
            'so_lan_vi_pham_toi_da' => 3,
            'chu_ky_snapshot_giay' => 30,
        ]);

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.bat-dau', $exam->id))
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.precheck', $exam->id));

        $payload = json_encode([
            'browser_supported' => true,
            'camera_supported' => true,
            'camera_ok' => true,
            'fullscreen_supported' => true,
            'fullscreen_ok' => true,
            'visibility_supported' => true,
            'user_agent' => 'PHPUnit',
            'platform' => 'Testing',
            'captured_at' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.precheck.submit', $exam->id), [
                'precheck_payload' => $payload,
            ])
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.show', $exam->id));

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.bat-dau', $exam->id))
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.show', $exam->id));

        $this->assertDatabaseHas('bai_lam_bai_kiem_tra', [
            'bai_kiem_tra_id' => $exam->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'dang_lam',
            'trang_thai_giam_sat' => 'binh_thuong',
        ]);
    }

    public function test_surveillance_violation_marks_attempt_for_review_and_teacher_can_update_review(): void
    {
        $context = $this->buildMixedExamContext();

        $student = $context['student'];
        $teacherUser = $context['teacher_user'];
        $exam = $context['exam'];

        $exam->update([
            'co_giam_sat' => true,
            'bat_buoc_fullscreen' => true,
            'bat_buoc_camera' => true,
            'so_lan_vi_pham_toi_da' => 1,
            'chu_ky_snapshot_giay' => 30,
            'tu_dong_nop_khi_vi_pham' => false,
        ]);

        $payload = json_encode([
            'browser_supported' => true,
            'camera_supported' => true,
            'camera_ok' => true,
            'fullscreen_supported' => true,
            'fullscreen_ok' => true,
            'visibility_supported' => true,
            'user_agent' => 'PHPUnit',
            'platform' => 'Testing',
            'captured_at' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.precheck.submit', $exam->id), [
                'precheck_payload' => $payload,
            ]);

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.bat-dau', $exam->id))
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.show', $exam->id));

        $baiLam = $exam->fresh()->baiLams()->where('hoc_vien_id', $student->ma_nguoi_dung)->firstOrFail();

        $this->actingAs($student)
            ->postJson(route('hoc-vien.bai-lam.giam-sat.log', $baiLam->id), [
                'event_type' => 'tab_switch',
                'description' => 'Student switched tab during exam.',
                'meta' => ['source' => 'phpunit'],
            ])
            ->assertOk()
            ->assertJson([
                'violation_count' => 1,
                'max_violations' => 1,
                'should_review' => true,
            ]);

        $this->assertDatabaseHas('bai_lam_bai_kiem_tra', [
            'id' => $baiLam->id,
            'tong_so_vi_pham' => 1,
            'trang_thai_giam_sat' => 'can_xem_xet',
        ]);

        $this->assertDatabaseHas('bai_lam_vi_pham_giam_sat', [
            'bai_lam_bai_kiem_tra_id' => $baiLam->id,
            'loai_su_kien' => 'tab_switch',
            'la_vi_pham' => 1,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.cham-diem.surveillance', $baiLam->id), [
                'trang_thai_giam_sat' => 'da_xac_nhan',
                'ghi_chu_giam_sat' => 'Da xem log va xac nhan co vi pham.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('bai_lam_bai_kiem_tra', [
            'id' => $baiLam->id,
            'trang_thai_giam_sat' => 'da_xac_nhan',
            'ghi_chu_giam_sat' => 'Da xem log va xac nhan co vi pham.',
            'nguoi_hau_kiem_id' => $teacherUser->ma_nguoi_dung,
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

    public function test_teacher_can_filter_questions_in_exam_builder_by_search(): void
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

        $this->createReadyQuestion($admin, $course, $module, 'CH-FILTER-001', 'Laravel route helper la gi?');
        $this->createReadyQuestion($admin, $course, $module, 'CH-FILTER-002', 'Docker compose dung de lam gi?');

        $response = $this->actingAs($teacherUser)->get(route('giang-vien.bai-kiem-tra.edit', [
            'id' => $exam->id,
            'question_search' => 'Laravel',
            'tab' => 'questions',
        ]));

        $response->assertOk();
        $response->assertSeeText('Laravel route helper la gi?');
        $response->assertDontSeeText('Docker compose dung de lam gi?');
    }

    public function test_teacher_exam_builder_renders_questions_tab_active_from_query_param(): void
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

        $question = $this->createReadyQuestion(
            $admin,
            $course,
            $module,
            'CH-TAB-001',
            'Question visible ngay khi mo tab questions'
        );

        $response = $this->actingAs($teacherUser)->get(route('giang-vien.bai-kiem-tra.edit', [
            'id' => $exam->id,
            'tab' => 'questions',
            'question_loai_cau_hoi' => 'trac_nghiem',
        ]));

        $response->assertOk();
        $response->assertSeeText($question->noi_dung);
        $response->assertSee('<button class="nav-link active py-3 fw-bold" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button" role="tab" aria-selected="true">', false);
        $response->assertSee('<div class="tab-pane fade show active" id="questions" role="tabpanel">', false);
    }

    public function test_teacher_exam_builder_includes_course_level_questions_for_module_exam(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $otherModule = $this->createModule($course, 2);

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

        $courseLevelQuestion = $this->createReadyQuestion(
            $admin,
            $course,
            null,
            'CH-COURSE-001',
            'Cau hoi cap khoa hoc'
        );

        $moduleLevelQuestion = $this->createReadyQuestion(
            $admin,
            $course,
            $module,
            'CH-MODULE-001',
            'Cau hoi dung module hien tai'
        );

        $otherModuleQuestion = $this->createReadyQuestion(
            $admin,
            $course,
            $otherModule,
            'CH-MODULE-999',
            'Cau hoi module khac'
        );

        $response = $this->actingAs($teacherUser)->get(route('giang-vien.bai-kiem-tra.edit', [
            'id' => $exam->id,
            'tab' => 'questions',
        ]));

        $response->assertOk();
        $response->assertSeeText($courseLevelQuestion->noi_dung);
        $response->assertSeeText($moduleLevelQuestion->noi_dung);
        $response->assertDontSeeText($otherModuleQuestion->noi_dung);
    }

    public function test_teacher_root_url_redirects_to_teaching_courses(): void
    {
        [$teacherUser] = $this->createTeacher();

        $this->actingAs($teacherUser)
            ->get('/giang-vien')
            ->assertRedirect(route('giang-vien.khoa-hoc'));
    }

    public function test_teacher_can_open_and_update_dedicated_surveillance_settings(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);

        $this->assignTeacher($admin, $teacher, $course, $module);

        $exam = BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'De chinh giám sát',
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

        $this->actingAs($teacherUser)
            ->get(route('giang-vien.bai-kiem-tra.surveillance.edit', $exam->id))
            ->assertOk();

        $this->actingAs($teacherUser)
            ->put(route('giang-vien.bai-kiem-tra.surveillance.update', $exam->id), [
                'co_giam_sat' => 1,
                'bat_buoc_fullscreen' => 1,
                'bat_buoc_camera' => 1,
                'so_lan_vi_pham_toi_da' => 2,
                'chu_ky_snapshot_giay' => 25,
                'tu_dong_nop_khi_vi_pham' => 1,
                'chan_copy_paste' => 1,
                'chan_chuot_phai' => 1,
            ])
            ->assertRedirect(route('giang-vien.bai-kiem-tra.surveillance.edit', $exam->id));

        $this->assertDatabaseHas('bai_kiem_tra', [
            'id' => $exam->id,
            'co_giam_sat' => 1,
            'bat_buoc_fullscreen' => 1,
            'bat_buoc_camera' => 1,
            'so_lan_vi_pham_toi_da' => 2,
            'chu_ky_snapshot_giay' => 25,
            'tu_dong_nop_khi_vi_pham' => 1,
            'chan_copy_paste' => 1,
            'chan_chuot_phai' => 1,
        ]);
    }

    public function test_teacher_cannot_update_surveillance_settings_while_attempt_is_active(): void
    {
        $context = $this->buildMixedExamContext();

        $student = $context['student'];
        $teacherUser = $context['teacher_user'];
        $exam = $context['exam'];

        $this->actingAs($student)
            ->post(route('hoc-vien.bai-kiem-tra.bat-dau', $exam->id))
            ->assertRedirect(route('hoc-vien.bai-kiem-tra.show', $exam->id));

        $this->actingAs($teacherUser)
            ->from(route('giang-vien.bai-kiem-tra.surveillance.edit', $exam->id))
            ->put(route('giang-vien.bai-kiem-tra.surveillance.update', $exam->id), [
                'co_giam_sat' => 1,
                'bat_buoc_fullscreen' => 1,
                'so_lan_vi_pham_toi_da' => 2,
                'chu_ky_snapshot_giay' => 20,
            ])
            ->assertRedirect(route('giang-vien.bai-kiem-tra.surveillance.edit', $exam->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bai_kiem_tra', [
            'id' => $exam->id,
            'co_giam_sat' => 0,
        ]);
    }

    public function test_teacher_can_open_exam_index_and_only_see_accessible_exams(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();

        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $this->assignTeacher($admin, $teacher, $course, $module);

        $otherCourse = $this->createCourse($admin);
        $otherModule = $this->createModule($otherCourse);

        BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'tieu_de' => 'De module co the cau hinh',
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

        BaiKiemTra::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => null,
            'tieu_de' => 'De cuoi khoa cung nhin thay',
            'thoi_gian_lam_bai' => 45,
            'pham_vi' => 'cuoi_khoa',
            'loai_bai_kiem_tra' => 'cuoi_khoa',
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'cho_duyet',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 20,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'trang_thai' => true,
        ]);

        BaiKiemTra::create([
            'khoa_hoc_id' => $otherCourse->id,
            'module_hoc_id' => $otherModule->id,
            'tieu_de' => 'De ngoai pham vi phan cong',
            'thoi_gian_lam_bai' => 30,
            'pham_vi' => 'module',
            'loai_bai_kiem_tra' => 'module',
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'nhap',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'trang_thai' => true,
        ]);

        $this->actingAs($teacherUser)
            ->get(route('giang-vien.bai-kiem-tra.index'))
            ->assertOk()
            ->assertSeeText('De module co the cau hinh')
            ->assertSeeText('De cuoi khoa cung nhin thay')
            ->assertDontSeeText('De ngoai pham vi phan cong');
    }

    public function test_teacher_can_remove_all_selected_questions_from_exam_configuration(): void
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
            'mo_ta' => 'Original description',
            'thoi_gian_lam_bai' => 30,
            'pham_vi' => 'module',
            'loai_bai_kiem_tra' => 'module',
            'loai_noi_dung' => 'trac_nghiem',
            'trang_thai_duyet' => 'nhap',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 5,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'trang_thai' => true,
        ]);

        $question = $this->createReadyQuestion($admin, $course, $module, 'CH-CLEAR-001', 'Question to be removed');
        $exam->chiTietCauHois()->create([
            'ngan_hang_cau_hoi_id' => $question->id,
            'thu_tu' => 1,
            'diem_so' => 5,
            'bat_buoc' => true,
        ]);

        $this->actingAs($teacherUser)
            ->put(route('giang-vien.bai-kiem-tra.update', $exam->id), [
                'tieu_de' => 'Editable exam',
                'mo_ta' => 'Essay-only description',
                'thoi_gian_lam_bai' => 45,
                'so_lan_duoc_lam' => 2,
                'che_do_tinh_diem' => 'thu_cong',
            ])
            ->assertRedirect();

        $exam->refresh();

        $this->assertSame(0, $exam->chiTietCauHois()->count());
        $this->assertSame('tu_luan', $exam->loai_noi_dung);
        $this->assertSame('10.00', (string) $exam->tong_diem);
    }

    public function test_teacher_cannot_apply_invalid_scoring_package_below_minimum_point_threshold(): void
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

        $questionIds = [];
        foreach (range(1, 5) as $index) {
            $questionIds[] = $this->createReadyQuestion(
                $admin,
                $course,
                $module,
                'CH-PACKAGE-00' . $index,
                'Package question ' . $index
            )->id;
        }

        $this->actingAs($teacherUser)
            ->from(route('giang-vien.bai-kiem-tra.edit', $exam->id))
            ->put(route('giang-vien.bai-kiem-tra.update', $exam->id), [
                'tieu_de' => 'Editable exam',
                'thoi_gian_lam_bai' => 30,
                'so_lan_duoc_lam' => 1,
                'che_do_tinh_diem' => 'goi_diem',
                'so_cau_goi_diem' => 5,
                'tong_diem_goi_diem' => 1,
                'question_ids' => $questionIds,
            ])
            ->assertRedirect(route('giang-vien.bai-kiem-tra.edit', $exam->id))
            ->assertSessionHasErrors('tong_diem_goi_diem');

        $this->assertSame(0, $exam->fresh()->chiTietCauHois()->count());
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

    private function createReadyQuestion(
        NguoiDung $creator,
        KhoaHoc $course,
        ?ModuleHoc $module,
        string $code,
        string $content
    ): NganHangCauHoi {
        return NganHangCauHoi::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module?->id,
            'nguoi_tao_id' => $creator->ma_nguoi_dung,
            'ma_cau_hoi' => $code,
            'noi_dung' => $content,
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
            'muc_do' => 'trung_binh',
            'diem_mac_dinh' => 1,
            'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            'co_the_tai_su_dung' => true,
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
            'giang_vien_id' => $teacher->id,
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

