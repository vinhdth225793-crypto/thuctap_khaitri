<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diem_danh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lich_hoc_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->string('trang_thai'); // co_mat, vang_mat, vao_tre
            $table->string('ghi_chu')->nullable();
            $table->timestamps();

            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->onDelete('cascade');
            $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('cascade');
            $table->unique(['lich_hoc_id', 'hoc_vien_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_danh');
    }
};
