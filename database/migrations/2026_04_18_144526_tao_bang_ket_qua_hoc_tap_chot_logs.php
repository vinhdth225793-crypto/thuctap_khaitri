<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ket_qua_hoc_tap_chot_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ket_qua_hoc_tap_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->unsignedBigInteger('module_hoc_id')->nullable();
            $table->unsignedBigInteger('khoa_hoc_id')->nullable();
            $table->unsignedBigInteger('nguoi_thuc_hien_id');
            $table->string('hanh_dong'); // chot, mo_chot, cap_nhat
            $table->decimal('diem_truoc', 8, 2)->nullable();
            $table->decimal('diem_sau', 8, 2)->nullable();
            $table->string('ly_do', 1000)->nullable();
            $table->json('metadata')->nullable(); // Snapshot dữ liệu tại thời điểm đó
            $table->timestamps();

            $table->foreign('ket_qua_hoc_tap_id')->references('id')->on('ket_qua_hoc_tap')->onDelete('cascade');
            $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('nguoi_thuc_hien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ket_qua_hoc_tap_chot_logs');
    }
};
