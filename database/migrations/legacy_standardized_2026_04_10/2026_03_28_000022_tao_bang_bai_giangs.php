<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_giangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id');
            $table->unsignedBigInteger('lich_hoc_id')->nullable();
            $table->unsignedBigInteger('nguoi_tao_id')->nullable();
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->string('loai_bai_giang', 50)->default('tai_lieu');
            $table->unsignedBigInteger('tai_nguyen_chinh_id')->nullable();
            $table->integer('thu_tu_hien_thi')->default(0);
            $table->dateTime('thoi_diem_mo')->nullable();
            $table->string('trang_thai_duyet', 50)->default('da_duyet');
            $table->string('trang_thai_cong_bo', 50)->default('an');
            $table->text('ghi_chu_admin')->nullable();
            $table->dateTime('ngay_gui_duyet')->nullable();
            $table->dateTime('ngay_duyet')->nullable();
            $table->unsignedBigInteger('nguoi_duyet_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->cascadeOnDelete();
            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->nullOnDelete();
            $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('nguoi_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('tai_nguyen_chinh_id')->references('id')->on('tai_nguyen_buoi_hoc')->nullOnDelete();
            $table->index(['module_hoc_id', 'thu_tu_hien_thi']);
            $table->index(['trang_thai_duyet', 'trang_thai_cong_bo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_giangs');
    }
};