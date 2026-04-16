<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use Database\Seeders\NganHangCauHoiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompatibilityFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_model_uses_standard_table_and_columns(): void
    {
        $banner = Banner::create([
            'tieu_de' => 'Banner test',
            'mo_ta' => 'Mo ta',
            'duong_dan_anh' => 'images/banners/test.jpg',
            'link' => 'https://example.com',
            'thu_tu' => 1,
            'trang_thai' => true,
        ]);

        $this->assertSame('banners', $banner->getTable());
        $this->assertSame('images/banners/test.jpg', $banner->duong_dan_anh);
        $this->assertSame('images/banners/test.jpg', $banner->hinh_anh);
        $this->assertSame('https://example.com', $banner->link);
        $this->assertSame('https://example.com', $banner->lien_ket);

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'duong_dan_anh' => 'images/banners/test.jpg',
            'link' => 'https://example.com',
        ]);
    }

    public function test_question_bank_seeder_uses_standard_question_tables(): void
    {
        NguoiDung::create([
            'ho_ten' => 'Admin',
            'email' => 'admin@example.com',
            'mat_khau' => 'password',
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $this->seed(NganHangCauHoiSeeder::class);

        $this->assertDatabaseHas('nhom_nganh', [
            'ma_nhom_nganh' => 'NN-CNTT',
            'ten_nhom_nganh' => 'Cong nghe thong tin',
        ]);

        $this->assertSame(2, NganHangCauHoi::count());
        $this->assertDatabaseCount('dap_an_cau_hoi', 8);
        $this->assertDatabaseHas('ngan_hang_cau_hoi', [
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
            'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
        ]);
    }

    public function test_repair_legacy_schema_fails_fast_on_unsupported_driver(): void
    {
        $this->artisan('app:repair-legacy-schema')
            ->expectsOutput('This command only supports MySQL legacy databases.')
            ->assertExitCode(1);
    }
}
