<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yeu_cau_hoc_vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('giang_vien_id')->nullable();
            $table->unsignedBigInteger('hoc_vien_id')->nullable();
            $table->string('loai_yeu_cau', 50);
            $table->json('du_lieu_yeu_cau')->nullable();
            $table->text('ly_do')->nullable();
            $table->string('trang_thai', 50)->default('cho_duyet');
            $table->unsignedBigInteger('admin_duyet_id')->nullable();
            $table->timestamp('thoi_gian_duyet')->nullable();
            $table->text('phan_hoi_admin')->nullable();
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->nullOnDelete();
            $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('admin_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->index(['khoa_hoc_id', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_hoc_vien');
    }
};