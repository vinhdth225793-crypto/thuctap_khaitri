<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoc_vien_khoa_hoc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->date('ngay_tham_gia')->nullable();
            $table->enum('trang_thai', ['dang_hoc', 'hoan_thanh', 'ngung_hoc'])->default('dang_hoc');
            $table->text('ghi_chu')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('created_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->unique(['khoa_hoc_id', 'hoc_vien_id']);
            $table->index(['hoc_vien_id', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoc_vien_khoa_hoc');
    }
};