<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id')->nullable();
            $table->unsignedBigInteger('lich_hoc_id')->nullable();
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->integer('thoi_gian_lam_bai')->default(60); // Phut
            $table->dateTime('ngay_mo')->nullable();
            $table->dateTime('ngay_dong')->nullable();
            $table->string('pham_vi'); // module, buoi_hoc
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->onDelete('cascade');
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->onDelete('set null');
            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_kiem_tra');
    }
};
