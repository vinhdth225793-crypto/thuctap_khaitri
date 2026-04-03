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
use App\Models\PhongHocLive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LiveRoomWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_teacher_can_create_live_lecture_and_submit_for_approval(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $teacher, $course, $module);
        $lichHoc = $this->createLichHoc($course, $module, [
            'ngay_hoc' => '2026-04-01',
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '10:30:00',
        ]);

        $response = $this->actingAs($teacherUser)
            ->post(route('giang-vien.bai-giang.store'), [
                'tieu_de' => 'Phong hoc live bai 1',
                'mo_ta' => 'Mo ta live room',
                'phan_cong_id' => $assignment->id,
                'lich_hoc_id' => $lichHoc->id,
                'loai_bai_giang' => BaiGiang::TYPE_LIVE,
                'hanh_dong' => 'gui_duyet',
                'live' => [
                    'nen_tang_live' => PhongHocLive::PLATFORM_ZOOM,
                    'moderator_id' => $teacherUser->ma_nguoi_dung,
                    'thoi_gian_bat_dau' => '2026-04-01 09:00:00',
                    'thoi_luong_phut' => 90,
                    'join_url' => 'https://example.com/live/join',
                    'start_url' => 'https://example.com/live/start',
                ],
            ]);

        $response->assertRedirect(route('giang-vien.bai-giang.index'));

        $lecture = BaiGiang::query()->firstOrFail();
        $room = $lecture->phongHocLive()->firstOrFail();

        $this->assertSame(BaiGiang::STATUS_DUYET_CHO, $lecture->trang_thai_duyet);
        $this->assertSame(PhongHocLive::APPROVAL_CHO_DUYET, $room->trang_thai_duyet);
        $this->assertSame($teacherUser->ma_nguoi_dung, $room->moderator_id);
        $this->assertSame('https://example.com/live/join', $room->du_lieu_nen_tang_json['join_url']);
    }

    public function test_admin_can_create_and_approve_live_lecture_immediately(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $teacher, $course, $module);

        $response = $this->actingAs($admin)
            ->post(route('admin.bai-giang.store'), [
                'tieu_de' => 'Admin tao live room',
                'phan_cong_id' => $assignment->id,
                'loai_bai_giang' => BaiGiang::TYPE_LIVE,
                'hanh_dong' => 'duyet_ngay',
                'live' => [
                    'nen_tang_live' => PhongHocLive::PLATFORM_GOOGLE_MEET,
                    'moderator_id' => $teacherUser->ma_nguoi_dung,
                    'thoi_gian_bat_dau' => '2026-04-02 14:00:00',
                    'thoi_luong_phut' => 60,
                    'join_url' => 'https://meet.google.com/abc-defg-hij',
                    'meeting_code' => 'abc-defg-hij',
                ],
            ]);

        $response->assertRedirect(route('admin.bai-giang.index'));

        $lecture = BaiGiang::query()->firstOrFail();
        $room = $lecture->phongHocLive()->firstOrFail();

        $this->assertSame(BaiGiang::STATUS_DUYET_DA_DUYET, $lecture->trang_thai_duyet);
        $this->assertSame(PhongHocLive::APPROVAL_DA_DUYET, $room->trang_thai_duyet);
        $this->assertSame($admin->ma_nguoi_dung, $room->approved_by);
    }

    public function test_student_is_redirected_to_live_room_for_live_lecture(): void
    {
        [$teacherUser] = $this->createTeacher();
        $student = $this->createStudent();
        $lecture = $this->createApprovedPublishedLiveLecture($teacherUser, [
            'thoi_gian_bat_dau' => '2026-04-03 09:00:00',
        ]);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $lecture->khoa_hoc_id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $teacherUser->ma_nguoi_dung,
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.bai-giang.show', $lecture->id))
            ->assertRedirect(route('hoc-vien.live-room.show', $lecture->id));
    }

    public function test_admin_can_review_publish_teacher_live_lecture_and_student_can_open_room_page(): void
    {
        $admin = $this->createUser('admin');
        [$teacherUser, $teacher] = $this->createTeacher();
        $student = $this->createStudent();
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $assignment = $this->assignTeacher($admin, $teacher, $course, $module);
        $lichHoc = $this->createLichHoc($course, $module, [
            'ngay_hoc' => '2026-04-06',
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '10:30:00',
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.bai-giang.store'), [
                'tieu_de' => 'Live room cho admin duyet',
                'phan_cong_id' => $assignment->id,
                'lich_hoc_id' => $lichHoc->id,
                'loai_bai_giang' => BaiGiang::TYPE_LIVE,
                'hanh_dong' => 'gui_duyet',
                'live' => [
                    'nen_tang_live' => PhongHocLive::PLATFORM_ZOOM,
                    'moderator_id' => $teacherUser->ma_nguoi_dung,
                    'thoi_gian_bat_dau' => '2026-04-06 09:00:00',
                    'thoi_luong_phut' => 90,
                    'join_url' => 'https://example.com/live/review/join',
                    'start_url' => 'https://example.com/live/review/start',
                ],
            ])
            ->assertRedirect(route('giang-vien.bai-giang.index'));

        $lecture = BaiGiang::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.bai-giang.duyet', $lecture->id), [
                'trang_thai_duyet' => 'da_duyet',
                'ghi_chu_admin' => 'Hop le va san sang cong bo.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->post(route('admin.bai-giang.cong-bo', $lecture->id))
            ->assertSessionHas('success');

        $lecture->refresh();
        $lecture->load('phongHocLive');

        $this->assertSame(BaiGiang::STATUS_DUYET_DA_DUYET, $lecture->trang_thai_duyet);
        $this->assertSame(BaiGiang::CONG_BO_DA_CONG_BO, $lecture->trang_thai_cong_bo);
        $this->assertSame(PhongHocLive::APPROVAL_DA_DUYET, $lecture->phongHocLive->trang_thai_duyet);
        $this->assertSame(PhongHocLive::PUBLISH_DA_CONG_BO, $lecture->phongHocLive->trang_thai_cong_bo);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $course->id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $admin->ma_nguoi_dung,
        ]);

        $this->actingAs($student)
            ->get(route('hoc-vien.live-room.show', $lecture->id))
            ->assertOk()
            ->assertSee('Live room cho admin duyet');
    }

    public function test_student_cannot_join_before_moderator_starts_but_can_join_after_start_and_leave(): void
    {
        Carbon::setTestNow('2026-04-04 09:05:00');

        [$teacherUser] = $this->createTeacher();
        $student = $this->createStudent();
        $lecture = $this->createApprovedPublishedLiveLecture($teacherUser, [
            'thoi_gian_bat_dau' => '2026-04-04 09:00:00',
            'thoi_luong_phut' => 90,
            'join_url' => 'https://example.com/join-live',
            'start_url' => 'https://example.com/start-live',
        ]);

        HocVienKhoaHoc::create([
            'khoa_hoc_id' => $lecture->khoa_hoc_id,
            'hoc_vien_id' => $student->ma_nguoi_dung,
            'ngay_tham_gia' => now()->toDateString(),
            'trang_thai' => 'dang_hoc',
            'created_by' => $teacherUser->ma_nguoi_dung,
        ]);

        $this->actingAs($student)
            ->post(route('hoc-vien.live-room.join', $lecture->id))
            ->assertForbidden();

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.live-room.start', $lecture->id))
            ->assertRedirect(route('giang-vien.live-room.show', ['id' => $lecture->id, 'player' => 'host']));

        $this->assertSame(PhongHocLive::ROOM_STATE_DANG_DIEN_RA, $lecture->phongHocLive()->firstOrFail()->trang_thai_phong);

        $this->actingAs($student)
            ->post(route('hoc-vien.live-room.join', $lecture->id))
            ->assertRedirect(route('hoc-vien.live-room.show', ['id' => $lecture->id, 'player' => 'participant']));

        $this->assertDatabaseHas('phong_hoc_live_nguoi_tham_gia', [
            'phong_hoc_live_id' => $lecture->phongHocLive->id,
            'nguoi_dung_id' => $student->ma_nguoi_dung,
            'vai_tro' => 'student',
            'trang_thai' => 'dang_tham_gia',
        ]);

        $this->actingAs($student)
            ->post(route('hoc-vien.live-room.leave', $lecture->id))
            ->assertRedirect(route('hoc-vien.live-room.show', $lecture->id));

        $this->assertDatabaseHas('phong_hoc_live_nguoi_tham_gia', [
            'phong_hoc_live_id' => $lecture->phongHocLive->id,
            'nguoi_dung_id' => $student->ma_nguoi_dung,
            'trang_thai' => 'da_roi_phong',
        ]);

        Carbon::setTestNow();
    }

    public function test_teacher_can_end_live_room_and_attach_recording(): void
    {
        Carbon::setTestNow('2026-04-05 11:00:00');

        [$teacherUser] = $this->createTeacher();
        $lecture = $this->createApprovedPublishedLiveLecture($teacherUser, [
            'thoi_gian_bat_dau' => '2026-04-05 09:00:00',
            'thoi_luong_phut' => 90,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.live-room.end', $lecture->id))
            ->assertRedirect(route('giang-vien.live-room.show', $lecture->id));

        $this->assertSame(PhongHocLive::ROOM_STATE_DA_KET_THUC, $lecture->phongHocLive()->firstOrFail()->trang_thai_phong);

        $this->actingAs($teacherUser)
            ->post(route('giang-vien.live-room.recordings.store', $lecture->id), [
                'nguon_ban_ghi' => 'zoom',
                'tieu_de' => 'Ban ghi buoi hoc',
                'link_ngoai' => 'https://example.com/recordings/1',
                'thoi_luong' => 5400,
            ])
            ->assertRedirect(route('giang-vien.live-room.show', $lecture->id));

        $this->assertDatabaseHas('phong_hoc_live_ban_ghi', [
            'phong_hoc_live_id' => $lecture->phongHocLive->id,
            'tieu_de' => 'Ban ghi buoi hoc',
            'nguon_ban_ghi' => 'zoom',
        ]);

        Carbon::setTestNow();
    }

    public function test_teacher_live_room_page_shows_start_action_when_room_reaches_start_time(): void
    {
        Carbon::setTestNow('2026-04-04 09:05:00');

        [$teacherUser] = $this->createTeacher();
        $lecture = $this->createApprovedPublishedLiveLecture($teacherUser, [
            'thoi_gian_bat_dau' => '2026-04-04 09:00:00',
            'thoi_luong_phut' => 90,
            'join_url' => 'https://example.com/join-live',
            'start_url' => 'https://example.com/start-live',
        ]);

        $this->actingAs($teacherUser)
            ->get(route('giang-vien.live-room.show', $lecture->id))
            ->assertOk()
            ->assertSee(route('giang-vien.live-room.start', $lecture->id), false)
            ->assertSee('Bat dau buoi hoc', false);

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

    private function createStudent(): NguoiDung
    {
        $user = $this->createUser('hoc_vien');

        HocVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return $user;
    }

    private function createCourse(NguoiDung $creator): KhoaHoc
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
            'created_by' => $creator->ma_nguoi_dung,
        ]);
    }

    private function createModule(KhoaHoc $course): ModuleHoc
    {
        return ModuleHoc::create([
            'khoa_hoc_id' => $course->id,
            'ma_module' => $course->ma_khoa_hoc . '-M1',
            'ten_module' => 'Module 1',
            'thu_tu_module' => 1,
            'so_buoi' => 3,
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
            'gio_ket_thuc' => '10:30:00',
            'thu_trong_tuan' => 2,
            'buoi_so' => 1,
            'hinh_thuc' => 'online',
            'nen_tang' => 'Zoom',
            'link_online' => 'https://example.com/class',
            'trang_thai' => 'cho',
        ], $overrides));
    }

    private function assignTeacher(NguoiDung $admin, GiangVien $teacher, KhoaHoc $course, ModuleHoc $module): PhanCongModuleGiangVien
    {
        return PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'giang_vien_id' => $teacher->id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'da_nhan',
            'created_by' => $admin->ma_nguoi_dung,
        ]);
    }

    private function createApprovedPublishedLiveLecture(NguoiDung $teacherUser, array $roomOverrides = []): BaiGiang
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module, [
            'ngay_hoc' => '2026-04-04',
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '10:30:00',
        ]);

        $lecture = BaiGiang::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'lich_hoc_id' => $lichHoc->id,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'tieu_de' => 'Live lecture',
            'loai_bai_giang' => BaiGiang::TYPE_LIVE,
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_DA_DUYET,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_DA_CONG_BO,
        ]);

        $platformPayload = [
            'join_url' => 'https://example.com/live/join',
            'start_url' => 'https://example.com/live/start',
        ];

        foreach (['join_url', 'start_url', 'embed_url', 'meeting_id', 'meeting_code', 'passcode'] as $payloadKey) {
            if (array_key_exists($payloadKey, $roomOverrides)) {
                $platformPayload[$payloadKey] = $roomOverrides[$payloadKey];
                unset($roomOverrides[$payloadKey]);
            }
        }

        $lecture->phongHocLive()->create(array_merge([
            'nen_tang_live' => PhongHocLive::PLATFORM_ZOOM,
            'loai_live' => PhongHocLive::TYPE_CLASS,
            'tieu_de' => 'Live lecture',
            'moderator_id' => $teacherUser->ma_nguoi_dung,
            'thoi_gian_bat_dau' => '2026-04-04 09:00:00',
            'thoi_luong_phut' => 90,
            'mo_phong_truoc_phut' => 15,
            'nhac_truoc_phut' => 10,
            'cho_phep_chat' => true,
            'cho_phep_thao_luan' => true,
            'tat_mic_khi_vao' => true,
            'tat_camera_khi_vao' => true,
            'trang_thai_duyet' => PhongHocLive::APPROVAL_DA_DUYET,
            'trang_thai_cong_bo' => PhongHocLive::PUBLISH_DA_CONG_BO,
            'trang_thai_phong' => PhongHocLive::ROOM_STATE_CHUA_MO,
            'du_lieu_nen_tang_json' => $platformPayload,
            'created_by' => $teacherUser->ma_nguoi_dung,
            'approved_by' => $admin->ma_nguoi_dung,
            'approved_at' => now(),
        ], $roomOverrides));

        return $lecture->fresh(['phongHocLive']);
    }
}
