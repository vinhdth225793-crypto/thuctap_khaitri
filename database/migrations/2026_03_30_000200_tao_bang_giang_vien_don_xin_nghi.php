<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giang_vien_don_xin_nghi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('giang_vien_id');
            $table->unsignedBigInteger('khoa_hoc_id')->nullable();
            $table->unsignedBigInteger('module_hoc_id')->nullable();
            $table->unsignedBigInteger('lich_hoc_id')->nullable();
            $table->date('ngay_xin_nghi');
            $table->string('buoi_hoc', 20)->nullable();
            $table->tinyInteger('tiet_bat_dau')->nullable();
            $table->tinyInteger('tiet_ket_thuc')->nullable();
            $table->text('ly_do');
            $table->text('ghi_chu_phan_hoi')->nullable();
            $table->enum('trang_thai', ['cho_duyet', 'da_duyet', 'tu_choi'])->default('cho_duyet');
            $table->unsignedBigInteger('nguoi_duyet_id')->nullable();
            $table->timestamp('ngay_duyet')->nullable();
            $table->timestamps();

            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->cascadeOnDelete();
            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->nullOnDelete();
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->nullOnDelete();
            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->nullOnDelete();
            $table->foreign('nguoi_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();

            $table->index(['giang_vien_id', 'ngay_xin_nghi'], 'idx_gv_don_xin_nghi_ngay');
            $table->index(['trang_thai', 'created_at'], 'idx_gv_don_xin_nghi_trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giang_vien_don_xin_nghi');
    }
};
