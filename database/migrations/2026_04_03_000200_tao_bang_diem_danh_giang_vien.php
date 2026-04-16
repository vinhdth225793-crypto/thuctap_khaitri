<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diem_danh_giang_vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lich_hoc_id');
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id');
            $table->unsignedBigInteger('giang_vien_id');
            $table->string('hinh_thuc_hoc', 50)->default('online');
            $table->timestamp('thoi_gian_bat_dau_day')->nullable();
            $table->timestamp('thoi_gian_ket_thuc_day')->nullable();
            $table->timestamp('thoi_gian_mo_live')->nullable();
            $table->timestamp('thoi_gian_tat_live')->nullable();
            $table->unsignedInteger('tong_thoi_luong_day_phut')->nullable();
            $table->string('trang_thai', 50)->default('chua_bat_dau');
            $table->text('ghi_chu')->nullable();
            $table->unsignedBigInteger('nguoi_tao_id')->nullable();
            $table->timestamps();

            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->cascadeOnDelete();
            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->cascadeOnDelete();
            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->cascadeOnDelete();
            $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();

            $table->unique(['lich_hoc_id', 'giang_vien_id'], 'uq_diem_danh_gv_lich_gv');
            $table->index(['khoa_hoc_id', 'module_hoc_id'], 'idx_diem_danh_gv_course_module');
            $table->index(['giang_vien_id', 'trang_thai'], 'idx_diem_danh_gv_teacher_status');
            $table->index('thoi_gian_bat_dau_day', 'idx_diem_danh_gv_started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_danh_giang_vien');
    }
};
