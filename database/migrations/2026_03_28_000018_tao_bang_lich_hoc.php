<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_hoc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('lop_hoc_id')->nullable();
            $table->unsignedBigInteger('module_hoc_id');
            $table->unsignedBigInteger('giang_vien_id')->nullable();
            $table->date('ngay_hoc');
            $table->time('gio_bat_dau');
            $table->time('gio_ket_thuc');
            $table->tinyInteger('thu_trong_tuan');
            $table->unsignedInteger('buoi_so');
            $table->string('phong_hoc', 100)->nullable();
            $table->enum('hinh_thuc', ['truc_tiep', 'online'])->default('truc_tiep');
            $table->string('link_online', 500)->nullable();
            $table->string('nen_tang')->nullable();
            $table->string('meeting_id')->nullable();
            $table->string('mat_khau_cuoc_hop')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->text('bao_cao_giang_vien')->nullable();
            $table->timestamp('thoi_gian_bao_cao')->nullable();
            $table->string('trang_thai_bao_cao', 50)->default('chua_bao_cao');
            $table->enum('trang_thai', ['cho', 'dang_hoc', 'hoan_thanh', 'huy'])->default('cho');
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->cascadeOnDelete();
            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->nullOnDelete();
            $table->unique(['module_hoc_id', 'buoi_so']);
            $table->index(['khoa_hoc_id', 'ngay_hoc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_hoc');
    }
};