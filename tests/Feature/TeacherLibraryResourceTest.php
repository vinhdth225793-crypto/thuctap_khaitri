<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\NguoiDung;
use App\Models\TaiNguyenBuoiHoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherLibraryResourceTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1;

    public function test_teacher_can_upload_valid_library_resource(): void
    {
        Storage::fake('public');
        [$teacherUser] = $this->createTeacher();

        $response = $this->actingAs($teacherUser)
            ->post(route('giang-vien.thu-vien.store'), [
                'tieu_de' => 'Slide bai giang',
                'loai_tai_nguyen' => 'pdf',
                'pham_vi_su_dung' => 'khoa_hoc',
                'file_dinh_kem' => UploadedFile::fake()->create('slides.pdf', 200, 'application/pdf'),
            ]);

        $response->assertRedirect(route('giang-vien.thu-vien.index'));

        $taiNguyen = TaiNguyenBuoiHoc::query()->firstOrFail();

        $this->assertDatabaseHas('tai_nguyen_buoi_hoc', [
            'id' => $taiNguyen->id,
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_NHAP,
            'loai_tai_nguyen' => 'pdf',
        ]);
        Storage::disk('public')->assertExists($taiNguyen->duong_dan_file);
    }

    public function test_teacher_cannot_upload_invalid_library_file_type(): void
    {
        Storage::fake('public');
        [$teacherUser] = $this->createTeacher();

        $response = $this->actingAs($teacherUser)
            ->from(route('giang-vien.thu-vien.create'))
            ->post(route('giang-vien.thu-vien.store'), [
                'tieu_de' => 'File sai dinh dang',
                'loai_tai_nguyen' => 'pdf',
                'pham_vi_su_dung' => 'ca_nhan',
                'file_dinh_kem' => UploadedFile::fake()->create('malware.exe', 50),
            ]);

        $response
            ->assertRedirect(route('giang-vien.thu-vien.create'))
            ->assertSessionHasErrors(['file_dinh_kem']);

        $this->assertDatabaseCount('tai_nguyen_buoi_hoc', 0);
    }

    public function test_updating_approved_resource_with_new_file_resets_approval_status(): void
    {
        Storage::fake('public');
        [$teacherUser] = $this->createTeacher();

        $taiNguyen = TaiNguyenBuoiHoc::create([
            'tieu_de' => 'Tai nguyen da duyet',
            'loai_tai_nguyen' => 'pdf',
            'pham_vi_su_dung' => 'khoa_hoc',
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'vai_tro_nguoi_tao' => 'giang_vien',
            'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET,
            'trang_thai_xu_ly' => TaiNguyenBuoiHoc::STATUS_XU_LY_NONE,
            'ngay_gui_duyet' => now()->subDay(),
            'ngay_duyet' => now()->subHours(6),
            'nguoi_duyet_id' => $teacherUser->ma_nguoi_dung,
            'ghi_chu_admin' => 'Da duyet',
            'duong_dan_file' => 'uploads/thu-vien/old.pdf',
            'file_extension' => 'pdf',
            'file_name' => 'old.pdf',
            'file_size' => 1024,
        ]);

        Storage::disk('public')->put('uploads/thu-vien/old.pdf', 'old-content');

        $response = $this->actingAs($teacherUser)
            ->put(route('giang-vien.thu-vien.update', $taiNguyen->id), [
                'tieu_de' => 'Tai nguyen da cap nhat',
                'loai_tai_nguyen' => 'pdf',
                'pham_vi_su_dung' => 'khoa_hoc',
                'file_dinh_kem' => UploadedFile::fake()->create('updated.pdf', 120, 'application/pdf'),
            ]);

        $response->assertRedirect(route('giang-vien.thu-vien.index'));

        $taiNguyen = $taiNguyen->fresh();

        $this->assertSame(TaiNguyenBuoiHoc::STATUS_DUYET_NHAP, $taiNguyen->trang_thai_duyet);
        $this->assertNull($taiNguyen->ngay_gui_duyet);
        $this->assertNull($taiNguyen->ngay_duyet);
        $this->assertNull($taiNguyen->nguoi_duyet_id);
        $this->assertNull($taiNguyen->ghi_chu_admin);
        Storage::disk('public')->assertMissing('uploads/thu-vien/old.pdf');
        Storage::disk('public')->assertExists($taiNguyen->duong_dan_file);
    }

    public function test_updating_only_library_metadata_keeps_approval_status(): void
    {
        [$teacherUser] = $this->createTeacher();

        $taiNguyen = TaiNguyenBuoiHoc::create([
            'tieu_de' => 'Tai nguyen duoc giu duyet',
            'mo_ta' => 'Mo ta cu',
            'loai_tai_nguyen' => 'pdf',
            'pham_vi_su_dung' => 'khoa_hoc',
            'nguoi_tao_id' => $teacherUser->ma_nguoi_dung,
            'vai_tro_nguoi_tao' => 'giang_vien',
            'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET,
            'trang_thai_xu_ly' => TaiNguyenBuoiHoc::STATUS_XU_LY_NONE,
            'ngay_gui_duyet' => now()->subDay(),
            'ngay_duyet' => now()->subHours(6),
            'duong_dan_file' => 'uploads/thu-vien/approved.pdf',
            'file_extension' => 'pdf',
            'file_name' => 'approved.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($teacherUser)
            ->put(route('giang-vien.thu-vien.update', $taiNguyen->id), [
                'tieu_de' => 'Tai nguyen duoc giu duyet - moi',
                'mo_ta' => 'Mo ta moi',
                'loai_tai_nguyen' => 'pdf',
                'pham_vi_su_dung' => 'khoa_hoc',
                'link_ngoai' => null,
            ]);

        $response->assertRedirect(route('giang-vien.thu-vien.index'));

        $taiNguyen = $taiNguyen->fresh();

        $this->assertSame(TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET, $taiNguyen->trang_thai_duyet);
        $this->assertEquals('Mo ta moi', $taiNguyen->mo_ta);
        $this->assertNotNull($taiNguyen->ngay_duyet);
    }

    private function createTeacher(): array
    {
        $index = $this->sequence++;

        $user = NguoiDung::create([
            'ho_ten' => 'Giang vien ' . $index,
            'email' => 'teacher' . $index . '@example.com',
            'mat_khau' => bcrypt('password123'),
            'vai_tro' => 'giang_vien',
            'trang_thai' => true,
        ]);

        $giangVien = GiangVien::create([
            'nguoi_dung_id' => $user->ma_nguoi_dung,
        ]);

        return [$user, $giangVien];
    }
}
