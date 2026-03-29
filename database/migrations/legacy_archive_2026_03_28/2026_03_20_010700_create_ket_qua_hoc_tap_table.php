<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ket_qua_hoc_tap', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->string('phuong_thuc_danh_gia', 50)->default('cuoi_khoa');
            $table->decimal('diem_diem_danh', 8, 2)->nullable();
            $table->decimal('diem_kiem_tra', 8, 2)->nullable();
            $table->decimal('diem_tong_ket', 8, 2)->nullable();
            $table->unsignedInteger('tong_so_buoi')->default(0);
            $table->unsignedInteger('so_buoi_tham_du')->default(0);
            $table->decimal('ty_le_tham_du', 5, 2)->nullable();
            $table->unsignedInteger('so_bai_kiem_tra_hoan_thanh')->default(0);
            $table->json('chi_tiet')->nullable();
            $table->dateTime('cap_nhat_luc')->nullable();
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->cascadeOnDelete();
            $table->unique(['khoa_hoc_id', 'hoc_vien_id'], 'uniq_ket_qua_hoc_tap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ket_qua_hoc_tap');
    }
};
