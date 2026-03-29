<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngan_hang_cau_hoi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id')->nullable();
            $table->unsignedBigInteger('nguoi_tao_id')->nullable();
            $table->string('ma_cau_hoi', 60)->unique();
            $table->longText('noi_dung');
            $table->string('loai_cau_hoi', 50)->default('trac_nghiem');
            $table->string('muc_do', 50)->default('trung_binh');
            $table->decimal('diem_mac_dinh', 8, 2)->default(1);
            $table->text('goi_y_tra_loi')->nullable();
            $table->text('giai_thich_dap_an')->nullable();
            $table->string('trang_thai', 50)->default('san_sang');
            $table->boolean('co_the_tai_su_dung')->default(true);
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->nullOnDelete();
            $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngan_hang_cau_hoi');
    }
};
