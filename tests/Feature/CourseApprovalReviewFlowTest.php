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
use App\Models\PhieuXetDuyetKetQua;
use App\Services\CourseApprovalReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseApprovalReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_submit_final_exam_ticket_and_admin_finalize_official_result(): void
    {
        [$admin, $teacherUser, $student, $course, $module, $assignment] = $this->baseContext();
        [$sessionOne] = $this->createSchedules($course, $module, $assignment->giang_vien_id);
        DiemDanh::create([
            'lich_hoc_id' => $sessionOne->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'co_mat',
        ]);

        $finalExam = $this->createExam($admin, $course, null, [
            'tieu_de' => 'Final exam approval',
            'pham_vi' => 'cuoi_khoa',
            'loai_bai_kiem_tra' => 'cuoi_khoa',
        ]);
        $this->createAttempt($finalExam, $student, 8.00);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.xet-duyet-ket-qua.submit', $course->id), [
                'phuong_an' => PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE,
                'bai_kiem_tra_ids' => [$finalExam->id],
                'ghi_chu' => 'Gui xet duyet cuoi khoa',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $ticket = PhieuXetDuyetKetQua::query()->firstOrFail();
        $this->assertSame(PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED, $ticket->trang_thai);
        $this->assertDatabaseHas('chi_tiet_phieu_xet_duyet_ket_qua', [
            'phieu_id' => $ticket->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'diem_chuyen_can' => 5.00,
            'diem_kiem_tra' => 8.00,
            'diem_xet_duyet' => 7.40,
            'ket_qua' => 'dat',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.xet-duyet-ket-qua.finalize', $ticket), [
                'ghi_chu_duyet' => 'Chot ho so',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('phieu_xet_duyet_ket_qua', [
            'id' => $ticket->id,
            'trang_thai' => PhieuXetDuyetKetQua::TRANG_THAI_FINALIZED,
            'finalized_by_id' => $admin->ma_nguoi_dung,
        ]);
        $this->assertDatabaseHas('ket_qua_hoc_tap', [
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'module_hoc_id' => null,
            'bai_kiem_tra_id' => null,
            'diem_tong_ket' => 7.40,
            'diem_giang_vien_chot' => 7.40,
            'trang_thai' => 'dat',
            'trang_thai_duyet' => KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET,
            'admin_duyet_id' => $admin->ma_nguoi_dung,
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.ket-qua'))
            ->assertOk()
            ->assertSeeText('Diem xet duyet chinh thuc')
            ->assertSeeText('7.40');
    }

    public function test_selected_exam_mode_averages_selected_exam_scores_with_attendance(): void
    {
        [$admin, $teacherUser, $student, $course, $module] = $this->baseContext();
        [$sessionOne, $sessionTwo] = $this->createSchedules($course, $module, 1);
        foreach ([$sessionOne, $sessionTwo] as $session) {
            DiemDanh::create([
                'lich_hoc_id' => $session->id,
                'hoc_vien_id' => $student->ma_nguoi_dung,
                'trang_thai' => 'co_mat',
            ]);
        }

        $moduleExam = $this->createExam($admin, $course, $module, [
            'tieu_de' => 'Module selected exam',
            'loai_bai_kiem_tra' => 'module',
            'pham_vi' => 'module',
        ]);
        $sessionExam = $this->createExam($admin, $course, $module, [
            'tieu_de' => 'Session selected exam',
            'loai_bai_kiem_tra' => 'buoi_hoc',
            'pham_vi' => 'buoi_hoc',
            'lich_hoc_id' => $sessionOne->id,
        ]);
        $this->createAttempt($moduleExam, $student, 6.00);
        $this->createAttempt($sessionExam, $student, 10.00);

        $preview = app(CourseApprovalReviewService::class)->buildPreview(
            $course,
            PhieuXetDuyetKetQua::PHUONG_AN_SELECTED_EXAMS_ATTENDANCE,
            [$moduleExam->id, $sessionExam->id]
        );
        $row = $preview['students']->first();

        $this->assertSame(8.00, $row['diem_kiem_tra']);
        $this->assertSame(8.40, $row['diem_xet_duyet']);
        $this->assertSame('dat', $row['ket_qua']);
        $this->assertSame(2, count($row['exam_rows']));
        $this->assertSame($teacherUser->vai_tro, 'giang_vien');
    }

    /**
     * @return array{0: NguoiDung, 1: NguoiDung, 2: NguoiDung, 3: KhoaHoc, 4: ModuleHoc, 5: PhanCongModuleGiangVien}
     */
    private function baseContext(): array
    {
        $admin = $this->createUser('admin');
        $teacherUser = $this->createUser('giang_vien');
        $teacher = GiangVien::create([
            'nguoi_dung_id' => $teacherUser->ma_nguoi_dung,
            'chuyen_nganh' => 'Testing',
        ]);
        $student = $this->createUser('hoc_vien');
        HocVien::create(['nguoi_dung_id' => $student->ma_nguoi_dung]);

        $nhomNganh = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . uniqid(),
            'ten_nhom_nganh' => 'Nhom nganh ' . uniqid(),
            'trang_thai' => true,
        ]);
        $course = KhoaHoc::create([
            'nhom_nganh_id' => $nhomNganh->id,
            'ma_khoa_hoc' => 'KH' . uniqid(),
            'ten_khoa_hoc' => 'Khoa hoc xet duyet',
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'phuong_thuc_danh_gia' => 'cuoi_khoa',
            'ty_trong_diem_danh' => 20,
            'ty_trong_kiem_tra' => 80,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
        $module = ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => 'MD' . uniqid(),
            'ten_module' => 'Module xet duyet',
            'thu_tu_module' => 1,
            'so_buoi' => 2,
            'trang_thai' => true,
        ]);
        $assignment = PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        return [$admin, $teacherUser, $student, $course, $module, $assignment];
    }

    private function createUser(string $role): NguoiDung
    {
        return NguoiDung::create([
            'ho_ten' => ucfirst($role) . ' ' . uniqid(),
            'email' => $role . '-' . uniqid() . '@example.com',
            'mat_khau' => 'password',
            'vai_tro' => $role,
            'trang_thai' => true,
        ]);
    }

    /**
     * @return array{0: LichHoc, 1: LichHoc}
     */
    private function createSchedules(KhoaHoc $course, ModuleHoc $module, int $teacherId): array
    {
        return [
            LichHoc::create([
                'khoa_hoc_id' => $course->id,
                'module_hoc_id' => $module->id,
                'giang_vien_id' => $teacherId,
                'ngay_hoc' => now()->subDays(2)->toDateString(),
                'gio_bat_dau' => '08:00',
                'gio_ket_thuc' => '09:30',
                'buoi_so' => 1,
                'hinh_thuc' => 'truc_tiep',
                'trang_thai' => 'hoan_thanh',
            ]),
            LichHoc::create([
                'khoa_hoc_id' => $course->id,
                'module_hoc_id' => $module->id,
                'giang_vien_id' => $teacherId,
                'ngay_hoc' => now()->subDay()->toDateString(),
                'gio_bat_dau' => '08:00',
                'gio_ket_thuc' => '09:30',
                'buoi_so' => 2,
                'hinh_thuc' => 'truc_tiep',
                'trang_thai' => 'hoan_thanh',
            ]),
        ];
    }

    private function createExam(NguoiDung $creator, KhoaHoc $course, ?ModuleHoc $module, array $overrides = []): BaiKiemTra
    {
        return BaiKiemTra::create(array_merge([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module?->id,
            'tieu_de' => 'Exam ' . uniqid(),
            'thoi_gian_lam_bai' => 30,
            'pham_vi' => $module ? 'module' : 'cuoi_khoa',
            'loai_bai_kiem_tra' => $module ? 'module' : 'cuoi_khoa',
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'da_duyet',
            'trang_thai_phat_hanh' => 'phat_hanh',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => $creator->ma_nguoi_dung,
            'trang_thai' => true,
        ], $overrides));
    }

    private function createAttempt(BaiKiemTra $exam, NguoiDung $student, float $score): BaiLamBaiKiemTra
    {
        return BaiLamBaiKiemTra::create([
            'bai_kiem_tra_id' => $exam->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'lan_lam_thu' => 1,
            'trang_thai' => 'da_cham',
            'trang_thai_cham' => 'da_cham',
            'bat_dau_luc' => now()->subMinutes(45),
            'nop_luc' => now()->subMinutes(15),
            'diem_so' => $score,
        ]);
    }
}
