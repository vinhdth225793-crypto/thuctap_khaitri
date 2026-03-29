<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan_cong_module_giang_vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id');
            $table->unsignedBigInteger('giang_vien_id');
            $table->dateTime('ngay_phan_cong')->useCurrent();
            $table->enum('trang_thai', ['cho_xac_nhan', 'da_nhan', 'tu_choi'])->default('cho_xac_nhan');
            $table->text('ghi_chu')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->cascadeOnDelete();
            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->cascadeOnDelete();
            $table->foreign('created_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->unique(['module_hoc_id', 'giang_vien_id'], 'uq_module_giang_vien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan_cong_module_giang_vien');
    }
};