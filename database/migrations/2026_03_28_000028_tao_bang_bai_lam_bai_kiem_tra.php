<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_lam_bai_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_kiem_tra_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->unsignedInteger('lan_lam_thu')->default(1);
            $table->longText('noi_dung_bai_lam')->nullable();
            $table->string('trang_thai', 50)->default('dang_lam');
            $table->timestamp('bat_dau_luc')->nullable();
            $table->timestamp('nop_luc')->nullable();
            $table->decimal('diem_so', 8, 2)->nullable();
            $table->decimal('tong_diem_trac_nghiem', 8, 2)->nullable();
            $table->decimal('tong_diem_tu_luan', 8, 2)->nullable();
            $table->string('trang_thai_cham', 50)->default('chua_cham');
            $table->dateTime('auto_graded_at')->nullable();
            $table->dateTime('manual_graded_at')->nullable();
            $table->unsignedBigInteger('nguoi_cham_id')->nullable();
            $table->text('nhan_xet')->nullable();
            $table->timestamps();

            $table->foreign('bai_kiem_tra_id')->references('id')->on('bai_kiem_tra')->cascadeOnDelete();
            $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('nguoi_cham_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->unique(['bai_kiem_tra_id', 'hoc_vien_id', 'lan_lam_thu'], 'uniq_bai_lam_hoc_vien_lan');
            $table->index(['hoc_vien_id', 'trang_thai_cham']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_lam_bai_kiem_tra');
    }
};