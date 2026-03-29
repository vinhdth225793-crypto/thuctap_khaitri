<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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

            // Foreign keys
            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->onDelete('cascade');
            $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('created_by')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('set null');

            // Unique constraint
            $table->unique(['khoa_hoc_id', 'hoc_vien_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoc_vien_khoa_hoc');
    }
};
