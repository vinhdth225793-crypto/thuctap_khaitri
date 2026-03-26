<?php

namespace Tests\Feature;

use App\Models\BaiGiang;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhongHocLive;
use App\Models\PhongHocLiveBanGhi;
use App\Models\PhongHocLiveNguoiThamGia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhongHocLiveDataStructureTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_live_room_is_attached_to_lecture_and_can_be_reached_from_course_module_and_schedule(): void
    {
        $creator = $this->createUser('admin');
        $moderator = $this->createUser('giang_vien');
        $course = $this->createCourse($creator);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module);
        $lecture = $this->createLecture($creator, $course, $module, $lichHoc);

        $liveRoom = PhongHocLive::create([
            'bai_giang_id' => $lecture->id,
            'nen_tang_live' => PhongHocLive::PLATFORM_ZOOM,
            'loai_live' => PhongHocLive::TYPE_CLASS,
            'tieu_de' => 'Phong hoc live bai 1',
            'moderator_id' => $moderator->ma_nguoi_dung,
            'thoi_gian_bat_dau' => '2026-04-01 09:00:00',
            'thoi_luong_phut' => 90,
            'du_lieu_nen_tang_json' => [
                'join_url' => 'https://example.com/live-room',
                'meeting_id' => '123456789',
            ],
            'created_by' => $creator->ma_nguoi_dung,
        ]);

        $this->assertTrue($lecture->fresh()->phongHocLive->is($liveRoom));
        $this->assertSame('123456789', $liveRoom->du_lieu_nen_tang_json['meeting_id']);
        $this->assertCount(1, $course->fresh()->phongHocLives);
        $this->assertTrue($course->fresh()->phongHocLives->first()->is($liveRoom));
        $this->assertTrue($module->fresh()->phongHocLives->first()->is($liveRoom));
        $this->assertTrue($lichHoc->fresh()->phongHocLives->first()->is($liveRoom));
    }

    public function test_live_room_participants_and_recordings_link_back_to_authenticated_users(): void
    {
        $creator = $this->createUser('admin');
        $moderator = $this->createUser('giang_vien');
        $assistant = $this->createUser('giang_vien');
        $student = $this->createUser('hoc_vien');
        $course = $this->createCourse($creator);
        $module = $this->createModule($course);
        $lichHoc = $this->createLichHoc($course, $module, 2);
        $lecture = $this->createLecture($creator, $course, $module, $lichHoc, 2);

        $liveRoom = PhongHocLive::create([
            'bai_giang_id' => $lecture->id,
            'nen_tang_live' => PhongHocLive::PLATFORM_GOOGLE_MEET,
            'loai_live' => PhongHocLive::TYPE_MEETING,
            'tieu_de' => 'Phong hoc live co tro giang',
            'moderator_id' => $moderator->ma_nguoi_dung,
            'tro_giang_id' => $assistant->ma_nguoi_dung,
            'thoi_gian_bat_dau' => '2026-04-02 14:00:00',
            'thoi_luong_phut' => 60,
            'cho_phep_chat' => true,
            'tu_dong_gan_ban_ghi' => true,
            'created_by' => $creator->ma_nguoi_dung,
        ]);

        $participant = PhongHocLiveNguoiThamGia::create([
            'phong_hoc_live_id' => $liveRoom->id,
            'nguoi_dung_id' => $student->ma_nguoi_dung,
            'vai_tro' => 'student',
            'joined_at' => '2026-04-02 13:55:00',
            'left_at' => '2026-04-02 15:01:00',
            'trang_thai' => 'da_roi_phong',
        ]);

        $recording = PhongHocLiveBanGhi::create([
            'phong_hoc_live_id' => $liveRoom->id,
            'nguon_ban_ghi' => 'google_meet',
            'tieu_de' => 'Ban ghi buoi 2',
            'link_ngoai' => 'https://example.com/recording',
            'thoi_luong' => 3600,
            'trang_thai' => 'san_sang',
        ]);

        $this->assertTrue($moderator->fresh()->moderatedPhongHocLives->first()->is($liveRoom));
        $this->assertTrue($assistant->fresh()->assistedPhongHocLives->first()->is($liveRoom));
        $this->assertTrue($creator->fresh()->createdPhongHocLives->first()->is($liveRoom));
        $this->assertTrue($student->fresh()->phongHocLiveThamGia->first()->is($participant));
        $this->assertTrue($liveRoom->fresh()->nguoiThamGia->first()->nguoiDung->is($student));
        $this->assertTrue($liveRoom->fresh()->banGhis->first()->is($recording));
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

    private function createLichHoc(KhoaHoc $course, ModuleHoc $module, int $buoiSo = 1): LichHoc
    {
        return LichHoc::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'ngay_hoc' => now()->toDateString(),
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '10:30:00',
            'thu_trong_tuan' => 2,
            'buoi_so' => $buoiSo,
            'hinh_thuc' => 'online',
            'nen_tang' => 'Zoom',
            'link_online' => 'https://example.com/class-' . $buoiSo,
            'trang_thai' => 'cho',
        ]);
    }

    private function createLecture(
        NguoiDung $creator,
        KhoaHoc $course,
        ModuleHoc $module,
        LichHoc $lichHoc,
        int $order = 1
    ): BaiGiang {
        return BaiGiang::create([
            'khoa_hoc_id' => $course->id,
            'module_hoc_id' => $module->id,
            'lich_hoc_id' => $lichHoc->id,
            'nguoi_tao_id' => $creator->ma_nguoi_dung,
            'tieu_de' => 'Bai giang ' . $order,
            'mo_ta' => 'Mo ta bai giang ' . $order,
            'loai_bai_giang' => BaiGiang::TYPE_LIVE,
            'thu_tu_hien_thi' => $order,
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_DA_DUYET,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
        ]);
    }
}
